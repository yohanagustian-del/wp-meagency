#!/usr/bin/env python3
"""MEA hero cube v2: Playwright renderer for source/cube.html.

  python3 render.py probe          motion checks without pixels (facing, roll, mirroring, frame bounds, tile overlaps)
  python3 render.py test [--draft] frames 0, 30, 60, 90, 119 (+ mobile 0, 15, 30, 45, 59) and a 360 px contact sheet
  python3 render.py full [--force] all frames: out/png/desktop/000-119.png (1200) and out/png/mobile/000-059.png (720)

Desktop frame i is rendered at p = i/119, mobile frame i at p = i/59, so both sequences start and end on the same pose.
Needs: pip install playwright pillow numpy (a preinstalled Chromium is used if Playwright cannot download one).
"""
import argparse, base64, glob, hashlib, http.server, json, os, shutil, socketserver, sys, threading, time
from pathlib import Path

SRC = Path(__file__).resolve().parent
PKG = SRC.parent
OUT = SRC / 'out'
DESK = dict(name='desktop', size=1200, count=120, samples=16)
MOB = dict(name='mobile', size=720, count=60, samples=16)
TEST_D = [0, 30, 60, 90, 119]
TEST_M = [0, 15, 30, 45, 59]
BG = (26, 26, 26)  # page colour #1A1A1A
TD, EXT = 0.14, 0.88 + 0.12 * 0.7071067811865476


class Handler(http.server.SimpleHTTPRequestHandler):
    def __init__(self, *a, **k):
        super().__init__(*a, directory=str(SRC), **k)

    def log_message(self, *a):
        pass


def serve():
    srv = socketserver.ThreadingTCPServer(('127.0.0.1', 0), Handler)
    srv.daemon_threads = True
    threading.Thread(target=srv.serve_forever, daemon=True).start()
    return srv, srv.server_address[1]


ARGS = ['--use-angle=swiftshader', '--enable-unsafe-swiftshader', '--ignore-gpu-blocklist',
        '--force-color-profile=srgb', '--disable-gpu-watchdog']


def launch(pw):
    try:
        return pw.chromium.launch(args=ARGS)
    except Exception as err:  # no downloaded browser: use a preinstalled Chromium
        for exe in sorted(glob.glob('/opt/pw-browsers/chromium-*/chrome-linux/chrome')) + \
                ['/usr/bin/chromium', '/usr/bin/chromium-browser', '/usr/bin/google-chrome']:
            if os.path.exists(exe):
                return pw.chromium.launch(executable_path=exe, args=ARGS)
        raise err


def open_page(browser, port, size, samples):
    pg = browser.new_page(viewport={'width': size, 'height': size}, device_scale_factor=1)
    pg.on('pageerror', lambda e: print('pageerror:', e, file=sys.stderr))
    pg.goto(f'http://127.0.0.1:{port}/cube.html?size={size}&samples={samples}')
    pg.wait_for_function('window.READY === true', timeout=600000)
    return pg


def grab(pg, p):
    url = pg.evaluate(f'window.seek({p:.10f})')
    return base64.b64decode(url.split(',', 1)[1])


def stamp(size, samples):
    h = hashlib.sha1((SRC / 'cube.html').read_bytes())
    for f in sorted((SRC / 'logos').glob('*')):
        h.update(f.name.encode()); h.update(f.read_bytes())
    return f'{h.hexdigest()[:12]}-{size}-{samples}'


# ---------------------------------------------------------------- contact sheet
def font(sz):
    from PIL import ImageFont
    for f in ['/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf', '/usr/share/fonts/dejavu/DejaVuSans.ttf']:
        if os.path.exists(f):
            return ImageFont.truetype(f, sz)
    return ImageFont.load_default()


def on_bg(img):
    from PIL import Image
    img = img.convert('RGBA')
    bg = Image.new('RGBA', img.size, BG + (255,))
    bg.alpha_composite(img)
    return bg.convert('RGB')


def contact_sheet(rows, path, title, cell=360):
    """rows: [(row label, [(image path or PIL image, caption), ...]), ...] -> one PNG on #1A1A1A."""
    from PIL import Image, ImageDraw
    gap, pad, cap = 12, 20, 22
    ncol = max(len(r[1]) for r in rows)
    W = pad * 2 + ncol * cell + (ncol - 1) * gap
    Hh = pad + 30 + len(rows) * (20 + cell + cap + 8) + pad - 8
    sheet = Image.new('RGB', (W, Hh), BG)
    d = ImageDraw.Draw(sheet)
    d.text((pad, pad), title, fill=(210, 210, 210), font=font(15))
    y = pad + 30
    for label, items in rows:
        d.text((pad, y), label, fill=(150, 150, 150), font=font(13))
        y += 20
        for j, (im, caption) in enumerate(items):
            from PIL import Image as _I
            img = _I.open(im) if isinstance(im, (str, Path)) else im
            th = on_bg(img).resize((cell, cell), Image.LANCZOS)
            x = pad + j * (cell + gap)
            sheet.paste(th, (x, y))
            d.text((x, y + cell + 4), caption, fill=(140, 140, 140), font=font(12))
        y += cell + cap + 8
    Path(path).parent.mkdir(parents=True, exist_ok=True)
    sheet.save(path, optimize=True)
    return path


# ---------------------------------------------------------------- probe
def obb(t):
    import numpy as np
    m = np.array(t['m']).reshape(4, 4).T
    R, c = m[:3, :3], m[:3, 3]
    return c + R @ np.array([0, 0, -TD / 2]), R, np.array([EXT, EXT, TD / 2])


def sat(A, B):
    import numpy as np
    (ca, Ra, ea), (cb, Rb, eb) = A, B
    axes = [Ra[:, i] for i in range(3)] + [Rb[:, i] for i in range(3)]
    for i in range(3):
        for j in range(3):
            v = np.cross(Ra[:, i], Rb[:, j]); n = np.linalg.norm(v)
            if n > 1e-6:
                axes.append(v / n)
    d, pen = cb - ca, 1e9
    for ax in axes:
        ra = sum(ea[i] * abs(Ra[:, i] @ ax) for i in range(3))
        rb = sum(eb[i] * abs(Rb[:, i] @ ax) for i in range(3))
        pen = min(pen, ra + rb - abs(d @ ax))
        if pen < 0:
            return 0.0
    return pen


def probe():
    from playwright.sync_api import sync_playwright
    srv, port = serve()
    with sync_playwright() as pw:
        b = launch(pw)
        pg = open_page(b, port, 400, 1)
        info = pg.evaluate('window.INFO')
        fr = [pg.evaluate(f'window.probe({f})') for f in range(120)]
        b.close()
    OUT.mkdir(exist_ok=True)
    (OUT / 'probe.json').write_text(json.dumps({'info': info, 'frames': fr}))
    srv.shutdown()
    print(json.dumps({k: info[k] for k in ('three', 'used', 'plan')}, indent=1))
    print('\nframe | camera dist | visible faces: facing / roll on screen (deg)')
    prev, swaps, bad_mirror, worst_edge = None, [], [], 0
    for r in fr:
        vis = {t['key']: t for t in r['tiles'] if t['facing'] > 0.02}
        names = set(vis)
        if prev is not None and names != prev and r['f'] <= 80:
            swaps.append((r['f'], sorted(prev - names), sorted(names - prev)))
        prev = names
        for t in vis.values():
            if t['logo'] and t['handed'] <= 0:
                bad_mirror.append((r['f'], t['key']))
        for t in r['tiles']:
            worst_edge = max(worst_edge, max(abs(x) for x in t['ndc']))
        if r['f'] % 6 == 0 or r['f'] == 119:
            print(f"{r['f']:4d} | {r['dist']:5.2f} | " + '  '.join(
                f"{k} {t['facing']:.2f}/{t['roll']:+.0f}" for k, t in sorted(vis.items(), key=lambda kv: -kv[1]['facing'])))
    print('\nface changes (cube phase):', '; '.join(f"f{f} -{'/'.join(a) or '0'} +{'/'.join(c) or '0'}" for f, a, c in swaps))
    print('mirrored logos seen:', bad_mirror or 'none')
    print(f'max |NDC| of any tile corner: {worst_edge:.3f} (1.0 = frame edge)')
    overl = []
    for r in fr:
        if r['f'] < 79:
            continue
        bs = [(t['key'], obb(t)) for t in r['tiles']]
        for i in range(len(bs)):
            for j in range(i + 1, len(bs)):
                p = sat(bs[i][1], bs[j][1])
                if p > 0.005:
                    overl.append((r['f'], bs[i][0], bs[j][0], round(p, 3)))
    last = max((o[0] for o in overl), default=None)
    print('tile overlaps after f79 (bounding boxes, >0.005 u):', f'{len(overl)} pairs, last at f{last}' if overl else 'none')
    if overl:
        print('  ', overl[:12])
    fin = {t['key']: t for t in fr[-1]['tiles']}
    print('final frame:', {k: (round(t['facing'], 3), round(t['roll'], 1)) for k, t in fin.items()})


# ---------------------------------------------------------------- rendering
def render_set(browser, port, cfg, indices, outdir, reuse=None, log=None):
    outdir.mkdir(parents=True, exist_ok=True)
    pg, t_all = None, time.time()
    for n, i in enumerate(indices):
        dst = outdir / f'{i:03d}.png'
        if reuse and (reuse / f'{i:03d}.png').exists():
            shutil.copy(reuse / f'{i:03d}.png', dst)
            continue
        if pg is None:
            pg = open_page(browser, port, cfg['size'], cfg['samples'])
        t = time.time()
        png = grab(pg, i / (cfg['count'] - 1))
        tmp = dst.with_suffix('.tmp')
        tmp.write_bytes(png); tmp.replace(dst)
        msg = f"{cfg['name']} {i:03d} {time.time() - t:5.1f}s ({n + 1}/{len(indices)}, {time.time() - t_all:6.0f}s total)"
        print(msg, flush=True)
        if log:
            with open(log, 'a') as fh:
                fh.write(msg + '\n')
    if pg:
        pg.close()


def test(draft):
    from playwright.sync_api import sync_playwright
    d = dict(DESK, size=600, samples=4) if draft else dict(DESK)
    m = dict(MOB, size=360, samples=4) if draft else dict(MOB)
    root = OUT / ('draft' if draft else 'test')
    srv, port = serve()
    with sync_playwright() as pw:
        b = launch(pw)
        render_set(b, port, d, TEST_D, root / 'desktop')
        render_set(b, port, m, TEST_M, root / 'mobile')
        b.close()
    srv.shutdown()
    (root / 'stamp.json').write_text(json.dumps({'desktop': stamp(d['size'], d['samples']), 'mobile': stamp(m['size'], m['samples'])}))
    rows = [(f"desktop {d['size']} px -> 360 (frame / p)", [(root / 'desktop' / f'{i:03d}.png', f'frame {i}  p={i / 119:.3f}') for i in TEST_D]),
            (f"mobile {m['size']} px -> 360 (frame / p)", [(root / 'mobile' / f'{i:03d}.png', f'm{i:02d}  p={i / 59:.3f}') for i in TEST_M])]
    sheet = root / 'check-360.png'
    contact_sheet(rows, sheet, 'MEA hero cube v2 - pre-render check' + (' (DRAFT 4 samples)' if draft else ''))
    if not draft:
        shutil.copy(sheet, PKG / 'checks' / 'check-360-prerender.png') if (PKG / 'checks').exists() else None
    print('sheet:', sheet)


def full(force):
    from playwright.sync_api import sync_playwright
    log = OUT / 'render.log'
    srv, port = serve()
    st = {}
    tj = OUT / 'test' / 'stamp.json'
    if tj.exists():
        st = json.loads(tj.read_text())
    with sync_playwright() as pw:
        b = launch(pw)
        for cfg in (DESK, MOB):
            outdir = OUT / 'png' / cfg['name']
            if force and outdir.exists():
                shutil.rmtree(outdir)
            todo = [i for i in range(cfg['count']) if not (outdir / f'{i:03d}.png').exists()]
            reuse = OUT / 'test' / cfg['name'] if st.get(cfg['name']) == stamp(cfg['size'], cfg['samples']) else None
            render_set(b, port, cfg, todo, outdir, reuse=reuse, log=log)
        b.close()
    srv.shutdown()
    with open(log, 'a') as fh:
        fh.write('DONE\n')
    print('DONE')


if __name__ == '__main__':
    ap = argparse.ArgumentParser()
    ap.add_argument('mode', choices=['probe', 'test', 'full'])
    ap.add_argument('--draft', action='store_true')
    ap.add_argument('--force', action='store_true')
    a = ap.parse_args()
    if a.mode == 'probe':
        probe()
    elif a.mode == 'test':
        test(a.draft)
    else:
        full(a.force)
