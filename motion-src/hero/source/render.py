#!/usr/bin/env python3
"""MEA hero cube renderer: drives cube.html (window.seek) in headless Chromium via Playwright.
   python3 render.py test         -> out/test_sheet.png, prints info / continuity audit / timing
   python3 render.py full [names] -> out/png/{desktop,mobile,video}/NNN.png  (resumable)
"""
import base64, functools, http.server, io, json, os, sys, threading, time
from PIL import Image, ImageDraw, ImageFont
from playwright.sync_api import sync_playwright

HERE = os.path.dirname(os.path.abspath(__file__))
SRC = os.environ.get('CUBE_ROOT', HERE)
OUT = os.environ.get('CUBE_OUT', os.path.join(HERE, 'out'))
ARGS = ['--use-angle=swiftshader', '--enable-unsafe-swiftshader', '--ignore-gpu-blocklist', '--enable-webgl']
LAST, BG = 119, (26, 26, 26)
JOBS = [  # name, output px, supersample, frame count, opaque #1A1A1A background
    ('desktop', 1200, 2, 120, False),
    ('mobile', 720, 2, 120, False),
    ('video', 1200, float(os.environ.get('VIDEO_SS', 2)), 180, True),
]

class Quiet(http.server.SimpleHTTPRequestHandler):
    def log_message(self, *a): pass

def serve():
    srv = http.server.ThreadingHTTPServer(('127.0.0.1', 0), functools.partial(Quiet, directory=SRC))
    threading.Thread(target=srv.serve_forever, daemon=True).start()
    return srv.server_address[1]

def open_page(browser, port, out, ss, bg=False):
    page = browser.new_page(viewport={'width': 640, 'height': 640})
    errs = []
    page.on('pageerror', lambda e: errs.append(str(e)))
    page.on('console', lambda m: errs.append(m.text) if m.type == 'error' else None)
    page.goto(f'http://127.0.0.1:{port}/cube.html?out={out}&ss={ss}' + ('&bg=1' if bg else ''))
    info = page.evaluate('window.READY')
    if errs: print('PAGE ERRORS', errs[:5], flush=True)
    return page, info

def grab(page, p, out):
    data = page.evaluate(f'(window.seek({p:.7f}), window.capture())')
    im = Image.open(io.BytesIO(base64.b64decode(data.split(',', 1)[1]))); im.load()
    im = im.convert('RGBA')
    if im.size[0] != out:   # supersampled: premultiplied Lanczos downscale
        im = im.convert('RGBa').resize((out, out), Image.LANCZOS).convert('RGBA')
    return im

def on_bg(im, size=None):
    b = Image.new('RGBA', im.size, BG + (255,)); b.alpha_composite(im); b = b.convert('RGB')
    return b.resize((size, size), Image.LANCZOS) if size and size != b.size[0] else b

def font(size):
    try: return ImageFont.load_default(size=size)
    except TypeError: return ImageFont.load_default()

def test():
    os.makedirs(OUT, exist_ok=True)
    port = serve()
    with sync_playwright() as pw:
        br = pw.chromium.launch(args=ARGS)
        page, info = open_page(br, port, 1200, 1)
        print('INFO', json.dumps(info), flush=True)
        print('AUDIT', json.dumps(page.evaluate('window.audit()')), flush=True)
        fr = [0, 20, 30, 45, 60, 70, 80, 90, 100, 110, 119]
        t0 = time.time(); ims = {f: grab(page, f / LAST, 1200) for f in fr}
        print(f'ss1 1200: {(time.time()-t0)/len(fr):.2f}s/frame', flush=True); page.close()
        page, _ = open_page(br, port, 1200, 2)
        t0 = time.time(); hi = {f: grab(page, f / LAST, 1200) for f in (0, 119)}
        print(f'ss2 1200: {(time.time()-t0)/2:.2f}s/frame', flush=True); page.close()
        page, _ = open_page(br, port, 720, 2)
        t0 = time.time(); mob = {f: grab(page, f / LAST, 720) for f in (0, 119)}
        print(f'ss2 720: {(time.time()-t0)/2:.2f}s/frame', flush=True); page.close()
        br.close()
    th, gap, W = 300, 26, 1800
    sheet = Image.new('RGB', (W, gap + th + gap + th + gap + 360 + gap + 900), (10, 10, 10))
    d = ImageDraw.Draw(sheet); fnt = font(17)
    def put(im, x, y, size, text):
        sheet.paste(on_bg(im, size), (x, y)); d.text((x + 6, y - 21), text, fill=(200, 200, 200), font=fnt)
    y = gap
    for j, f in enumerate([0, 20, 30, 45, 60, 70]): put(ims[f], j * th, y, th, f'f{f}')
    y += th + gap
    for j, f in enumerate([80, 90, 100, 110, 119]): put(ims[f], j * th, y, th, f'f{f}')
    put(mob[119], 5 * th, y, th, 'mobile f119 (720)')
    y += th + gap
    for j, f in enumerate([0, 30, 60, 90, 119]): put(ims[f], j * 360, y, 360, f'f{f} at 360 px')
    y += 360 + gap
    put(hi[0], 0, y, 900, 'f0 supersampled'); put(hi[119], 900, y, 900, 'f119 supersampled')
    sheet.save(os.path.join(OUT, 'test_sheet.png')); print('SHEET', os.path.join(OUT, 'test_sheet.png'))

def full(only=None):
    port = serve()
    with sync_playwright() as pw:
        br = pw.chromium.launch(args=ARGS)
        for name, out, ss, n, bg in JOBS:
            if only and name not in only: continue
            d = os.path.join(OUT, 'png', name); os.makedirs(d, exist_ok=True)
            todo = [i for i in range(n) if not os.path.exists(os.path.join(d, f'{i:03d}.png'))]
            if not todo: print(name, 'complete', flush=True); continue
            page, _ = open_page(br, port, out, ss, bg); t0 = time.time()
            for k, i in enumerate(todo):
                im = grab(page, i / (n - 1), out)
                if bg: im = im.convert('RGB')
                tmp = os.path.join(d, f'{i:03d}.tmp.png'); im.save(tmp, compress_level=1)
                os.replace(tmp, os.path.join(d, f'{i:03d}.png'))
                if k % 20 == 0: print(f'{name} {i}/{n-1} {(time.time()-t0)/(k+1):.2f}s/frame', flush=True)
            page.close()
        br.close()
    print('DONE', flush=True)

if __name__ == '__main__':
    cmd = sys.argv[1] if len(sys.argv) > 1 else 'test'
    test() if cmd == 'test' else full(sys.argv[2:] or None)
