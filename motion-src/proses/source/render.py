#!/usr/bin/env python3
"""MEA orbit proses renderer: drives orbit.html (window.seek) in headless Chromium via Playwright.
   python3 render.py test [frames...] -> out/test/{1200,800}/NNN.png + out/check-400.png, prints info / audit
   python3 render.py full [names]     -> out/png/{desktop,mobile,video}/NNN.png  (resumable)
"""
import functools, http.server, io, json, os, sys, threading, time
from PIL import Image, ImageDraw, ImageFont
from playwright.sync_api import sync_playwright

HERE = os.path.dirname(os.path.abspath(__file__))
SRC = os.environ.get('ORBIT_ROOT', HERE)
OUT = os.environ.get('ORBIT_OUT', os.path.join(HERE, 'out'))
LAST = 149
CHECK = [0, 37, 75, 112, 149]          # brief: check these at 400 px wide before the full render
JOBS = [  # name, output px, supersample, frame count  (all opaque #FFFFFF, the section background)
    ('desktop', 1200, 2, 150),
    ('mobile', 800, 2, 150),
    ('video', 1200, 2, 180),           # 6 s at 30 fps
]

class Quiet(http.server.SimpleHTTPRequestHandler):
    def log_message(self, *a): pass

def serve():
    srv = http.server.ThreadingHTTPServer(('127.0.0.1', 0), functools.partial(Quiet, directory=SRC))
    threading.Thread(target=srv.serve_forever, daemon=True).start()
    return srv.server_address[1]

def open_page(browser, port, out, ss):
    page = browser.new_page(viewport={'width': out, 'height': out}, device_scale_factor=ss)
    errs = []
    page.on('pageerror', lambda e: errs.append(str(e)))
    page.on('console', lambda m: errs.append(m.text) if m.type == 'error' else None)
    page.goto(f'http://127.0.0.1:{port}/orbit.html?out={out}')
    ready = page.evaluate('window.READY')
    if errs: print('PAGE ERRORS', errs[:5], flush=True)
    if not ready.get('fontOK'): raise SystemExit('Inter did not load')
    return page

def grab(page, p, out):
    page.evaluate(f'window.seek({p:.7f})')
    im = Image.open(io.BytesIO(page.screenshot(type='png'))).convert('RGB')
    if im.size[0] != out: im = im.resize((out, out), Image.LANCZOS)   # supersampled
    return im

def font(size):
    try: return ImageFont.load_default(size=size)
    except TypeError: return ImageFont.load_default()

def check_sheet(rows, dst, w=400, pad=34):
    """rows: [(title, {frame: PIL image})]; every image shown w px wide."""
    sheet = Image.new('RGB', (w * len(CHECK) + 4 * 8, (pad + w) * len(rows)), (228, 228, 231))
    d = ImageDraw.Draw(sheet); fnt = font(15)
    for r, (title, ims) in enumerate(rows):
        y = pad + r * (pad + w)
        for j, f in enumerate(CHECK):
            x = j * (w + 8)
            sheet.paste(ims[f].resize((w, w), Image.LANCZOS), (x, y))
            d.text((x + 6, y - 22), f'{title}, frame {f}, at {w} px', fill=(40, 40, 44), font=fnt)
    sheet.save(dst)

def test(frames=None):
    port = serve(); frames = sorted(set((frames or []) + CHECK))
    rows = []
    with sync_playwright() as pw:
        br = pw.chromium.launch()
        for out in (1200, 800):
            page = open_page(br, port, out, 2)
            if out == 1200:
                info, audit = page.evaluate('window.info()'), page.evaluate('window.audit()')
                print('INFO', json.dumps(info), flush=True); print('AUDIT', json.dumps(audit), flush=True)
                os.makedirs(OUT, exist_ok=True)
                with open(os.path.join(OUT, 'info.json'), 'w') as f: json.dump(dict(info, audit=audit), f, indent=1)
            d = os.path.join(OUT, 'test', str(out)); os.makedirs(d, exist_ok=True)
            t0 = time.time(); ims = {}
            for f in frames:
                ims[f] = grab(page, f / LAST, out); ims[f].save(os.path.join(d, f'{f:03d}.png'))
            print(f'{out}: {(time.time() - t0) / len(frames):.2f}s/frame', flush=True)
            rows.append((f'{"desktop" if out == 1200 else "mobile"} {out}', ims)); page.close()
        br.close()
    check_sheet(rows, os.path.join(OUT, 'check-400.png'))
    print('SHEET', os.path.join(OUT, 'check-400.png'))

def full(only=None):
    port = serve()
    with sync_playwright() as pw:
        br = pw.chromium.launch()
        for name, out, ss, n in JOBS:
            if only and name not in only: continue
            d = os.path.join(OUT, 'png', name); os.makedirs(d, exist_ok=True)
            todo = [i for i in range(n) if not os.path.exists(os.path.join(d, f'{i:03d}.png'))]
            if not todo: print(name, 'complete', flush=True); continue
            page = open_page(br, port, out, ss); t0 = time.time()
            for k, i in enumerate(todo):
                im = grab(page, i / (n - 1), out)
                tmp = os.path.join(d, f'{i:03d}.tmp.png'); im.save(tmp, compress_level=1)
                os.replace(tmp, os.path.join(d, f'{i:03d}.png'))
                if k % 25 == 0: print(f'{name} {i}/{n - 1} {(time.time() - t0) / (k + 1):.2f}s/frame', flush=True)
            page.close()
        br.close()
    print('DONE', flush=True)

if __name__ == '__main__':
    cmd = sys.argv[1] if len(sys.argv) > 1 else 'test'
    if cmd == 'test': test([int(a) for a in sys.argv[2:]])
    else: full(sys.argv[2:] or None)
