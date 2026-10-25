#!/usr/bin/env python3
"""MEA Agency M2 "Enam Sisi": drive enam-sisi.html (window.seek(scene, t)) in headless Chromium.

   python3 render.py qa       frame 0 + mid frame of every loop at 480 / 160 / 120 px  -> out/checks/
   python3 render.py full     60 subframes per second (180 per loop)                   -> out/sub/<loop>/
   python3 render.py encode   ffmpeg tmix (2 subframes per frame) -> 90 frames, WebM, MP4, WebP poster

Needs: pip install playwright pillow numpy; ffmpeg with libvpx-vp9 and libx264.
If Playwright cannot download its browser, set CHROMIUM=/path/to/chrome (a preinstalled Chromium).
"""
import io, json, os, shutil, subprocess, sys, time
import numpy as np
from PIL import Image, ImageDraw, ImageFont
from playwright.sync_api import sync_playwright

HERE = os.path.dirname(os.path.abspath(__file__))
HTML = os.path.join(HERE, 'enam-sisi.html')
OUT = os.environ.get('M2_OUT', os.path.join(HERE, 'out'))
LOOPS = ['iklan', 'tiktok-shop', 'shopee', 'live', 'affiliate', 'konten']
SIZE, FPS, SUB, LOOP = 480, 30, 2, 3.0
NSUB, NFR = int(LOOP * FPS * SUB), int(LOOP * FPS)          # 180 subframes -> 90 frames
HOLD_FRAMES = list(range(0, 3)) + list(range(81, 90))        # 0.0-0.1 s and 2.7-3.0 s
SITE_BG = (250, 250, 250)
VIDEO_MAX, POSTER_MAX = 150_000, 20_000

def log(*a): print(*a, flush=True)

# ------------------------------------------------------------------ browser
def open_page(pw):
    # Partial raster re-rasters only invalidated rects, so overlapping anti-aliased edges can land 1-2 levels
    # off between two renders of identical markup. Full-tile raster keeps every frame deterministic, which the
    # pixel-identical loop point depends on.
    exe, args = os.environ.get('CHROMIUM'), ['--disable-partial-raster', '--force-color-profile=srgb']
    br = pw.chromium.launch(executable_path=exe, args=args) if exe else pw.chromium.launch(args=args)
    page = br.new_page(viewport={'width': SIZE, 'height': SIZE}, device_scale_factor=1)
    errs = []
    page.on('pageerror', lambda e: errs.append(str(e)))
    page.goto('file://' + HTML + '?render')
    info = page.evaluate('window.READY')
    if errs: raise SystemExit('PAGE ERRORS: ' + ' | '.join(errs))
    return br, page, info

def grab(page, loop, t):
    page.evaluate(f'seek({json.dumps(loop)}, {t:.7f})')
    png = page.screenshot(clip={'x': 0, 'y': 0, 'width': SIZE, 'height': SIZE})
    return Image.open(io.BytesIO(png)).convert('RGB')

# ------------------------------------------------------------------ downscale previews
def area(im, px):        # box average: what a mipmapped / high-quality scaler shows
    return im.resize((px, px), Image.BOX)

def gpu_bilinear(im, px):  # worst case: one bilinear tap per output pixel, no mipmaps
    a = np.asarray(im, dtype=np.float32); f = a.shape[0] / px
    c = (np.arange(px) + 0.5) * f - 0.5; i0 = np.floor(c).astype(int); w = (c - i0)[:, None]
    i1 = np.clip(i0 + 1, 0, a.shape[0] - 1); i0 = np.clip(i0, 0, a.shape[0] - 1)
    rows = a[i0] * (1 - w[:, :, None]) + a[i1] * w[:, :, None]
    out = rows[:, i0] * (1 - w[None, :, :]) + rows[:, i1] * w[None, :, :]
    return Image.fromarray(np.clip(out + 0.5, 0, 255).astype(np.uint8))

def font(n):
    try: return ImageFont.load_default(size=n)
    except TypeError: return ImageFont.load_default()

def eq(a, b): return np.array_equal(np.asarray(a), np.asarray(b))

# ------------------------------------------------------------------ qa
def qa():
    d = os.path.join(OUT, 'checks'); os.makedirs(d, exist_ok=True)
    shots, audit = {}, {}
    with sync_playwright() as pw:
        br, page, info = open_page(pw); log('INFO', json.dumps(info))
        for L in LOOPS:
            f0, mid, end = grab(page, L, 0), grab(page, L, 1.5), grab(page, L, 3.0)
            holds = [grab(page, L, t) for t in (0.05, 0.0999, 2.7, 2.85, 2.99)]
            audit[L] = {'t3_equals_t0': eq(f0, end), 'hold_equal': all(eq(f0, h) for h in holds)}
            shots[L] = (f0, mid); f0.save(os.path.join(d, f'{L}-f0.png')); mid.save(os.path.join(d, f'{L}-mid.png'))
        br.close()
    log('AUDIT', json.dumps(audit))
    # overview: 480 (shown at 240), 160 area, 120 area, 120 worst-case bilinear
    cols = [('480 px', 240, lambda im: im.resize((240, 240), Image.LANCZOS)), ('160 px', 160, lambda im: area(im, 160)),
            ('120 px', 120, lambda im: area(im, 120)), ('120 px, 1-tap bilinear', 120, lambda im: gpu_bilinear(im, 120))]
    pad, lab, rowh = 16, 20, 240 + 36
    W = pad + sum(2 * (c[1] + pad) for c in cols) + pad
    sheet = Image.new('RGB', (W, pad + rowh * len(LOOPS)), SITE_BG); dr = ImageDraw.Draw(sheet); fnt = font(13)
    for r, L in enumerate(LOOPS):
        y = pad + r * rowh; x = pad
        for name, px, fn in cols:
            for k, im in enumerate(shots[L]):
                sheet.paste(fn(im), (x, y + lab)); dr.text((x, y + 2), f'{L} {"f0" if k == 0 else "mid"} {name}', fill=(26, 26, 26), font=fnt)
                x += px + pad
    sheet.save(os.path.join(d, 'qa-overview.png'))
    # the grid as the site shows it: 120 px tiles on #FAFAFA, actual size, then x3 nearest for inspection
    g = Image.new('RGB', (pad + 6 * (120 + 24), 2 * (120 + 24) + pad), SITE_BG)
    for c, L in enumerate(LOOPS):
        for r, im in enumerate(shots[L]): g.paste(area(im, 120), (pad + c * 144, pad + r * 144))
    g.save(os.path.join(d, 'qa-120-actual.png'))
    g.resize((g.width * 3, g.height * 3), Image.NEAREST).save(os.path.join(d, 'qa-120-x3.png'))
    log('SHEETS', d)
    return audit

# ------------------------------------------------------------------ full render
def full(only=None):
    with sync_playwright() as pw:
        br, page, _ = open_page(pw)
        for L in LOOPS:
            if only and L not in only: continue
            d = os.path.join(OUT, 'sub', L); shutil.rmtree(d, ignore_errors=True); os.makedirs(d)
            t0 = time.time()
            for i in range(NSUB):
                grab(page, L, i / (FPS * SUB)).save(os.path.join(d, f'{i:03d}.png'), compress_level=1)
            log(f'{L}: {NSUB} subframes, {(time.time() - t0) / NSUB * 1000:.0f} ms each')
        br.close()

# ------------------------------------------------------------------ encode
def ff(args): subprocess.run(['ffmpeg', '-hide_banner', '-loglevel', 'error', '-y'] + args, check=True)
TO_YUV = 'scale=out_color_matrix=bt709:out_range=tv,format=yuv420p'
TAGS = ['-color_primaries', 'bt709', '-color_trc', 'bt709', '-colorspace', 'bt709', '-color_range', 'tv', '-an']

def blend(L):
    src, dst = os.path.join(OUT, 'sub', L), os.path.join(OUT, 'frames', L)
    shutil.rmtree(dst, ignore_errors=True); os.makedirs(dst)
    # frame n = mean(subframe 2n, subframe 2n+1): tmix mixes each frame with the one before it, keep odd n
    ff(['-framerate', str(FPS * SUB), '-i', os.path.join(src, '%03d.png'),
        '-vf', "tmix=frames=2:weights='1 1',select='mod(n\\,2)',setpts=N/(30*TB)",
        '-fps_mode', 'passthrough', '-start_number', '0', os.path.join(dst, '%02d.png')])
    fr = [np.asarray(Image.open(os.path.join(dst, f'{n:02d}.png')).convert('RGB')) for n in range(NFR)]
    assert len(os.listdir(dst)) == NFR, 'frame count'
    a, b = (np.asarray(Image.open(os.path.join(src, f'{k:03d}.png')).convert('RGB'), dtype=np.int16) for k in (90, 91))
    tmix_err = int(np.abs(fr[45].astype(np.int16) - (a + b) / 2).max())       # spot-check the tmix maths
    return fr, {'hold_identical': all(np.array_equal(fr[0], fr[n]) for n in HOLD_FRAMES),
                'last_equals_first': bool(np.array_equal(fr[0], fr[-1])), 'tmix_max_err_vs_numpy': tmix_err}

# Keyframes at 0, 81 and 89. Intra frames coded from the same image at the same quantiser reconstruct
# bit-identically, so decoded frame 89 == decoded frame 0 (the loop seam is exact, not just close), and the
# P-frames inside the end hold cannot drift more than a level or two.
KEYS = ['-force_key_frames', 'expr:eq(n,0)+eq(n,81)+eq(n,89)']

def enc_webm(L, dst, crf):
    ff(['-framerate', str(FPS), '-i', os.path.join(OUT, 'frames', L, '%02d.png'), '-vf', TO_YUV, '-c:v', 'libvpx-vp9',
        '-b:v', '0', '-crf', str(crf), '-deadline', 'good', '-cpu-used', '0', '-row-mt', '0', '-g', str(NFR),
        '-auto-alt-ref', '0', '-lag-in-frames', '0'] + KEYS + TAGS + [dst])

def enc_mp4(L, dst, qp):   # constant QP, no mbtree/AQ: the three I-frames get the same quantiser
    ff(['-framerate', str(FPS), '-i', os.path.join(OUT, 'frames', L, '%02d.png'), '-vf', TO_YUV, '-c:v', 'libx264',
        '-preset', 'veryslow', '-qp', str(qp), '-profile:v', 'high',
        '-x264-params', f'keyint={NFR}:min-keyint=1:scenecut=0:mbtree=0:aq-mode=0', '-movflags', '+faststart']
       + KEYS + TAGS + [dst])

def fit(enc, L, dst, crf, step=2, hi=63):
    enc(L, dst, crf)
    while os.path.getsize(dst) > VIDEO_MAX and crf + step <= hi: crf += step; enc(L, dst, crf)
    return crf, os.path.getsize(dst)

def decode(path):
    p = subprocess.run(['ffmpeg', '-hide_banner', '-loglevel', 'error', '-i', path, '-vf',
                        'scale=in_color_matrix=bt709:in_range=tv,format=rgb24', '-f', 'rawvideo', '-'],
                       check=True, capture_output=True).stdout
    return np.frombuffer(p, np.uint8).reshape(-1, SIZE, SIZE, 3)

def poster(L, dst):
    im = Image.open(os.path.join(OUT, 'frames', L, '00.png')).convert('RGB')
    im.save(dst, 'WEBP', lossless=True, method=6, quality=100)
    mode = 'lossless'
    if os.path.getsize(dst) > POSTER_MAX:
        for q in (95, 92, 90, 86, 80):
            im.save(dst, 'WEBP', quality=q, method=6)
            mode = f'lossy q{q}'
            if os.path.getsize(dst) <= POSTER_MAX: break
    return mode, os.path.getsize(dst)

def encode(crf_webm=12, qp_mp4=12):
    dist = os.path.join(OUT, 'dist'); os.makedirs(dist, exist_ok=True)
    report = {}
    for L in LOOPS:
        fr, chk = blend(L)
        rep = dict(chk)
        rep['webm_crf'], rep['webm_bytes'] = fit(enc_webm, L, os.path.join(dist, L + '.webm'), crf_webm)
        rep['mp4_qp'], rep['mp4_bytes'] = fit(enc_mp4, L, os.path.join(dist, L + '.mp4'), qp_mp4)
        rep['poster'], rep['poster_bytes'] = poster(L, os.path.join(dist, L + '.webp'))
        src = np.stack(fr).astype(np.int16)
        for k in ('webm', 'mp4'):
            dec = decode(os.path.join(dist, f'{L}.{k}')).astype(np.int16)
            rep[k + '_frames'] = int(dec.shape[0])
            rep[k + '_mean_err'] = round(float(np.abs(dec - src).mean()), 3)
            rep[k + '_loop_seam_max'] = int(np.abs(dec[-1] - dec[0]).max())   # decoded last vs first frame
            rep[k + '_hold_step_max'] = int(max(np.abs(dec[n + 1] - dec[n]).max() for n in range(81, 89)))
        report[L] = rep
        log(L, json.dumps(rep))
    json.dump(report, open(os.path.join(OUT, 'encode-report.json'), 'w'), indent=1)
    return report

if __name__ == '__main__':
    cmd = sys.argv[1] if len(sys.argv) > 1 else 'qa'
    if cmd == 'qa': qa()
    elif cmd == 'full': full(sys.argv[2:] or None)
    elif cmd == 'encode': encode(*map(int, sys.argv[2:4])) if len(sys.argv) > 3 else encode()
    else: raise SystemExit(__doc__)
