#!/usr/bin/env python3
"""Encode render.py output into the MEA hero cube deliverables and zip them.
   Frames: 256-colour palette, lossless WebP. (Lossy WebP q75 measured 7.0 MB desktop / 4.1 MB mobile
   for this thin-line artwork, far over budget.) Budgets: desktop <= 2.5 MB, mobile <= 1 MB (every second
   frame if 120 cannot fit), posters <= 60 KB, MP4 (H.264) + WebM (VP9) <= 900 KB.   Usage: python3 encode.py
"""
import glob, json, os, shutil, subprocess, sys, zipfile
import numpy as np
from PIL import Image, ImageDraw, ImageFont

HERE = os.path.dirname(os.path.abspath(__file__))
OUT = os.environ.get('CUBE_OUT', os.path.join(HERE, 'out'))
PNG = os.path.join(OUT, 'png')
NAME = 'mea-hero-cube'
DIST = os.path.join(OUT, 'dist', NAME)
ZIP_TO = os.environ.get('CUBE_ZIP', os.path.join(OUT, NAME + '.zip'))
BG = (26, 26, 26)
BUDGET = {'desktop': 2_500_000, 'mobile': 1_000_000}
POSTER_MAX, VIDEO_MAX = 60_000, 900_000
LADDER = [256, 128, 64]   # palette sizes tried, largest first

def log(*a): print(*a, flush=True)
def pngs(name): return sorted(glob.glob(os.path.join(PNG, name, '[0-9][0-9][0-9].png')))
def kb(n): return f'{n / 1000:.0f} KB'

def _blur(x, n=2):
    for _ in range(n):
        p = np.pad(x, ((2, 2), (2, 2), (0, 0)), mode='edge')
        x = (p[:-4] + 4 * p[1:-3] + 6 * p[2:-2] + 4 * p[3:-1] + p[4:]) / 16
        x = (x[:, :-4] + 4 * x[:, 1:-3] + 6 * x[:, 2:-2] + 4 * x[:, 3:-1] + x[:, 4:]) / 16
    return x

def clean(im):
    """Alpha bleeding: low-alpha pixels take their neighbours' colour (removes unpremultiply noise)."""
    a = np.asarray(im.convert('RGBA'), dtype=np.float32); A = a[..., 3:4] / 255.0
    Pb, Ab = _blur(a[..., :3] * A), _blur(A)
    cl = np.where(Ab > 1e-4, Pb / np.maximum(Ab, 1e-4), 0)
    w = np.clip((A - 0.25) / 0.5, 0, 1)
    rgb = a[..., :3] * w + cl * (1 - w); rgb[A[..., 0] == 0] = 0
    return Image.fromarray(np.concatenate([np.clip(rgb, 0, 255), a[..., 3:]], -1).round().astype(np.uint8), 'RGBA')

def palette(im, colors):
    return clean(im).quantize(colors=colors, method=Image.Quantize.FASTOCTREE, dither=Image.Dither.NONE).convert('RGBA')

def save_webp(im, path, colors):
    palette(im, colors).save(path, 'WEBP', lossless=True, quality=95, method=6, exact=False)

def encode_seq(files, dst, colors):
    shutil.rmtree(dst, ignore_errors=True); os.makedirs(dst)
    total = 0
    for j, f in enumerate(files):
        p = os.path.join(dst, f'{j:03d}.webp'); save_webp(Image.open(f), p, colors); total += os.path.getsize(p)
    return total

def fit(files, dst, budget):
    tmp = dst + '.est'
    try:
        for colors in LADDER:
            sub = files[::6]
            est = encode_seq(sub, tmp, colors) * len(files) / len(sub)
            log(f'  estimate {colors} colours: {est / 1e6:.2f} MB ({len(files)} frames)')
            if est > budget * 0.97: continue
            total = encode_seq(files, dst, colors)
            log(f'  encoded  {colors} colours: {total / 1e6:.3f} MB ({len(files)} frames)')
            if total <= budget:
                return {'format': 'lossless', 'colors': colors, 'frames': len(files), 'bytes': total}
        return None
    finally:
        shutil.rmtree(tmp, ignore_errors=True)

def ff(args): subprocess.run(['ffmpeg', '-hide_banner', '-loglevel', 'error', '-y'] + args, check=True)
VIN = ['-framerate', '30', '-i', os.path.join(PNG, 'video', '%03d.png')]
VF = ['-vf', 'scale=out_color_matrix=bt709:out_range=tv,format=yuv420p']
TAGS = ['-color_primaries', 'bt709', '-color_trc', 'bt709', '-colorspace', 'bt709', '-color_range', 'tv', '-an']
def enc_mp4(dst, crf): ff(VIN + VF + ['-c:v', 'libx264', '-preset', 'medium', '-tune', 'animation', '-crf', str(crf), '-profile:v', 'high', '-movflags', '+faststart'] + TAGS + [dst])
def enc_webm(dst, crf): ff(VIN + VF + ['-c:v', 'libvpx-vp9', '-b:v', '0', '-crf', str(crf), '-deadline', 'good', '-cpu-used', '4', '-row-mt', '1', '-tile-columns', '2'] + TAGS + [dst])

def best_crf(enc, dst, start, lo, hi, step=2):
    """Lowest CRF (best quality) within VIDEO_MAX."""
    tmp = os.path.join(os.path.dirname(dst), '_try_' + os.path.basename(dst))
    crf = start; enc(dst, crf)
    if os.path.getsize(dst) <= VIDEO_MAX:
        while crf - step >= lo:
            enc(tmp, crf - step)
            if os.path.getsize(tmp) > VIDEO_MAX: break
            crf -= step; os.replace(tmp, dst)
    else:
        while os.path.getsize(dst) > VIDEO_MAX and crf + step <= hi:
            crf += step; enc(dst, crf)
    if os.path.exists(tmp): os.remove(tmp)
    return {'crf': crf, 'bytes': os.path.getsize(dst)}

def font(size):
    try: return ImageFont.load_default(size=size)
    except TypeError: return ImageFont.load_default()

def composite(path, size):
    im = Image.open(path).convert('RGBA'); b = Image.new('RGBA', im.size, BG + (255,)); b.alpha_composite(im)
    return b.convert('RGB').resize((size, size), Image.LANCZOS)

def check_sheet(rep, dst):
    fr, W, pad = [0, 30, 60, 90, 119], 360, 30
    sheet = Image.new('RGB', (W * 5, (pad + W) * 2), (12, 12, 12)); d = ImageDraw.Draw(sheet); fnt = font(15)
    for row, k in enumerate(('desktop', 'mobile')):
        n, y = rep[k]['frames'], pad + row * (pad + W)
        for j, f in enumerate(fr):
            idx = round(f * (n - 1) / 119)
            sheet.paste(composite(os.path.join(DIST, 'frames', k, f'{idx:03d}.webp'), W), (j * W, y))
            d.text((j * W + 8, y - 21), f'{k} {rep[k]["size"]} px, frame {f}, at 360 px', fill=(190, 190, 190), font=fnt)
    sheet.save(dst)

LOCK = """FACE    #232325 · tile 292 px · 8 px thick · blank back #1C1C1E
EDGE    2 px @1200 / 1.5 px @720 · #EC7832 → #F69541, 135°, inside the face edge
LABEL   Inter 600 · #FAFAFA · 40 px @1200 (41.6 px in the final frame after the drift) / 24 px @720
        sized so "TikTok Shop" = 80% of tile width · tracking −1% · same size on all faces
RINGS   3 rings · 1.5 px @1200 / 1.25 px @720 · #F69541 at 40% (core), 30% (mid), 15% (outer)
DOTS    one per ring · 10 px @1200 / 7 px @720 · #F69541 100%
CORE    MEA bar mark · 112 px wide · bars #EC7832 → #F69541, folds #BA4231
LAYOUT  hexagon R = 365 px · 24 px gaps · rings always sit behind tiles
LIGHT   one soft key, top-left + sky ambient · no bloom, no glow
MOTION  exponential ease-out on every move · camera 28° FOV, zoom 1.00 → 1.04"""

MAP = """- 0-20 · Closed cube. 3/4 view from above: Shopee on the top face, Live on the left face, Iklan on the right face. Slow turntable rotation (+8°). Mid and outer rings orbit the cube; the core and its ring are hidden inside.
- 20-80 · Turn and separate. The cube turns +77° into a true isometric pose (20-78). Faces move out along their normals (24-80), fanning into a hexagon on screen. From frame 32 each face rotates to face the camera, upright by 80; faces passing edge-on show a blank back, never mirrored text. The core is revealed and starts turning toward camera.
  Shopee (top) rises to the top slot. TikTok Shop (bottom) drops to the bottom slot. Live (left face) swings to lower-right. Iklan (right face) swings away and returns upper-right. Konten (back) comes round to lower-left. Affiliate (back) comes round to upper-left.
- 80-119 · Settle. Tiles glide into one plane on the exact hexagon: near tiles step back, far tiles step forward, all end the same size. The core settles front-on. Rings expand x1.14 and flatten; dots come to rest in open gaps.

```text
             Shopee
  Affiliate          Iklan
             [MEA]
  Konten             Live
          TikTok Shop
```"""

README = r'''# MEA Agency hero cube

Scroll-scrubbed image sequence for the meagency.co.id homepage hero: the square to the right of
"Toko online Anda, kami yang jalankan" on the #1A1A1A page. Frame 0 = page top; the last frame =
the hero has scrolled away.

## Contents

- frames/desktop/000-@@DL@@.webp: @@DN@@ frames, 1200x1200, transparent, @@DKB@@ total (lossless WebP, @@DC@@-colour palette)
- frames/mobile/000-@@ML@@.webp: @@MN@@ frames, 720x720, transparent, @@MKB@@ total (lossless WebP, @@MC@@-colour palette)@@MNOTE@@
- frames/manifest.json: counts, sizes, encode settings and the scroll formula
- posters/poster-first-1200.webp (@@PF1@@) and poster-first-720.webp (@@PF7@@): frame 0, the LCP-safe image
- posters/poster-last-1200.webp (@@PL1@@) and poster-last-720.webp (@@PL7@@): the final frame
- video/hero-cube.mp4: H.264, 1200x1200, 6 s, 30 fps, no audio, on #1A1A1A (@@MP4@@, CRF @@MP4CRF@@)
- video/hero-cube.webm: VP9, same format (@@WEBM@@, CRF @@WEBMCRF@@)
- demo/index.html: scroll-scrub test page (canvas + IntersectionObserver + requestAnimationFrame)
- checks/check-360.png: frames 0, 30, 60, 90 and 119 shown 360 px wide, desktop and mobile rows
- source/cube.html: the three.js r149 scene; window.seek(p) renders any p from 0 to 1
- source/render.py, source/encode.py: Playwright render, then WebP, poster and video encode and zip

Why lossless: lossy WebP at q75 measured 7.0 MB (desktop) and 4.1 MB (mobile) for this thin-line artwork,
far over budget. Each frame is quantised to a palette and saved as lossless WebP instead, which looks the
same here and fits. Mobile still came to about 1.5 MB at 120 frames, so it ships every second frame, as the
brief allows.

## Wiring it into the site

1. Put poster-first in the square as a normal img with fetchpriority="high". It is the LCP image.
2. After window load, fetch frames progressively (0, last, every 16th, 8th, 4th, 2nd, then the rest)
   and draw the nearest loaded frame on a canvas. demo/index.html does exactly this.
3. Use the mobile set below a 768 px viewport, desktop above.
4. progress = scrollY / (heroTop + heroHeight), clamped to 0-1; frame = round(progress x (count - 1)).
   Ease the drawn frame toward the target inside requestAnimationFrame for a smooth scrub.
5. prefers-reduced-motion: show poster-last and skip the sequence.

If the hero scrolls normally (not pinned), frames 80-119 play while the square is already leaving the
viewport. Compare "Scroll away" and "Pinned" in the demo before deciding.

## Test on a phone

The demo loads frames by relative path, so serve the folder instead of opening the file directly:
run `npx serve .` inside the unzipped folder, put the phone on the same Wi-Fi and open
http://<computer-ip>:3000/demo/ . Or upload the whole folder to any static host.

## Re-render

From source/: `pip install playwright pillow numpy`, then `python3 render.py test` (contact sheet),
`python3 render.py full` (PNGs into source/out/png) and `python3 encode.py` (WebP, posters, video, zip).
The fallback videos need ffmpeg with libx264 and libvpx-vp9.

## Style lock

```text
@@LOCK@@
```

## Frame map (120 frames, 0-119)

@@MAP@@

## Logo note

The core is the MEA bar mark taken from MEA_LOGO_ORANGE.ps. That file is an Illustrator file saved
without PDF content, so any other app only sees a "re-save with Create PDF Compatible File" placeholder.
Re-save it with that option, or export an SVG, before using it elsewhere.
'''

def write_readme(rep):
    d, m = rep['desktop'], rep['mobile']
    sub = {
        'DL': f"{d['frames'] - 1:03d}", 'DN': str(d['frames']), 'DKB': kb(d['bytes']), 'DC': str(d['colors']),
        'ML': f"{m['frames'] - 1:03d}", 'MN': str(m['frames']), 'MKB': kb(m['bytes']), 'MC': str(m['colors']),
        'MNOTE': ' (every second frame of the 120)' if m.get('every_second_frame') else '',
        'PF1': kb(rep['poster-first-1200']['bytes']), 'PF7': kb(rep['poster-first-720']['bytes']),
        'PL1': kb(rep['poster-last-1200']['bytes']), 'PL7': kb(rep['poster-last-720']['bytes']),
        'MP4': kb(rep['mp4']['bytes']), 'MP4CRF': str(rep['mp4']['crf']), 'WEBM': kb(rep['webm']['bytes']), 'WEBMCRF': str(rep['webm']['crf']),
        'LOCK': LOCK, 'MAP': MAP,
    }
    text = README
    for k, v in sub.items(): text = text.replace(f'@@{k}@@', v)
    with open(os.path.join(DIST, 'README.md'), 'w', encoding='utf-8') as fh: fh.write(text)

def main():
    desk, mob, vid = pngs('desktop'), pngs('mobile'), pngs('video')
    assert len(desk) == 120 and len(mob) == 120 and len(vid) == 180, (len(desk), len(mob), len(vid))
    shutil.rmtree(DIST, ignore_errors=True)
    for d in ('frames', 'posters', 'video', 'demo', 'checks', 'source/vendor', 'source/demo'): os.makedirs(os.path.join(DIST, d))
    rep = {}
    log('desktop frames'); r = fit(desk, os.path.join(DIST, 'frames', 'desktop'), BUDGET['desktop'])
    if not r: sys.exit('desktop sequence over budget at every ladder step')
    rep['desktop'] = dict(r, size=1200)
    log('mobile frames'); r = fit(mob, os.path.join(DIST, 'frames', 'mobile'), BUDGET['mobile'])
    if not r:
        log('mobile over 1 MB at 120 frames: every second frame (60)')
        r = fit([mob[round(i * 119 / 59)] for i in range(60)], os.path.join(DIST, 'frames', 'mobile'), BUDGET['mobile'])
        if not r: sys.exit('mobile over budget even at 60 frames')
        r['every_second_frame'] = True
    rep['mobile'] = dict(r, size=720)
    man = {k: {'files': f'frames/{k}/000.webp to {v["frames"] - 1:03d}.webp', 'count': v['frames'], 'size': v['size'], 'bytes': v['bytes'],
               'webp': f"lossless, {v['colors']}-colour palette"} for k, v in rep.items()}
    man['scroll'] = 'progress = scrollY / (heroTop + heroHeight), clamped 0..1; frame = round(progress * (count - 1))'
    man['breakpoint'] = 'mobile set below 768 px viewport width'
    with open(os.path.join(DIST, 'frames', 'manifest.json'), 'w') as fh: json.dump(man, fh, indent=2)
    log('posters')
    for size, files in ((1200, desk), (720, mob)):
        for tag, f in (('first', files[0]), ('last', files[-1])):
            dst = os.path.join(DIST, 'posters', f'poster-{tag}-{size}.webp')
            Image.open(f).save(dst, 'WEBP', lossless=True, quality=95, method=6, exact=False); fmt = 'lossless'
            if os.path.getsize(dst) > POSTER_MAX: save_webp(Image.open(f), dst, 256); fmt = 'lossless, 256-colour palette'
            rep[f'poster-{tag}-{size}'] = {'format': fmt, 'bytes': os.path.getsize(dst)}
    log('mp4'); rep['mp4'] = best_crf(enc_mp4, os.path.join(DIST, 'video', 'hero-cube.mp4'), 24, 22, 40)
    log('webm'); rep['webm'] = best_crf(enc_webm, os.path.join(DIST, 'video', 'hero-cube.webm'), 36, 34, 54)
    log('check sheet'); check_sheet(rep, os.path.join(DIST, 'checks', 'check-360.png'))
    tpl = open(os.path.join(HERE, 'demo', 'index.html'), encoding='utf-8').read()
    with open(os.path.join(DIST, 'demo', 'index.html'), 'w', encoding='utf-8') as fh:
        fh.write(tpl.replace('__DESKTOP_COUNT__', str(rep['desktop']['frames'])).replace('__MOBILE_COUNT__', str(rep['mobile']['frames'])))
    shutil.copy(os.path.join(HERE, 'demo', 'index.html'), os.path.join(DIST, 'source', 'demo', 'index.html'))
    for fn in ('inter-600.woff2', 'inter-400.woff2'): shutil.copy(os.path.join(HERE, 'vendor', fn), os.path.join(DIST, 'demo', fn))
    for fn in ('cube.html', 'render.py', 'encode.py'): shutil.copy(os.path.join(HERE, fn), os.path.join(DIST, 'source', fn))
    for fn in ('three.r149.min.js', 'three.LICENSE', 'Inter-LICENSE.txt', 'inter-600.woff2', 'inter-400.woff2'):
        shutil.copy(os.path.join(HERE, 'vendor', fn), os.path.join(DIST, 'source', 'vendor', fn))
    write_readme(rep)
    os.makedirs(os.path.dirname(ZIP_TO), exist_ok=True)
    if os.path.exists(ZIP_TO): os.remove(ZIP_TO)
    with zipfile.ZipFile(ZIP_TO, 'w', zipfile.ZIP_DEFLATED) as z:
        for root, dirs, files in os.walk(DIST):
            dirs.sort()
            for fn in sorted(files):
                full = os.path.join(root, fn)
                stored = fn.endswith(('.webp', '.mp4', '.webm', '.woff2', '.png'))
                z.write(full, os.path.relpath(full, os.path.dirname(DIST)), compress_type=zipfile.ZIP_STORED if stored else zipfile.ZIP_DEFLATED)
    rep['zip'] = {'path': ZIP_TO, 'bytes': os.path.getsize(ZIP_TO)}
    log('REPORT ' + json.dumps(rep))

if __name__ == '__main__':
    main()
