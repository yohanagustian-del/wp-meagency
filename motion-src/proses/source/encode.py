#!/usr/bin/env python3
"""Encode render.py output into the MEA orbit proses deliverables and zip them.
   Frames: lossy WebP q75 (the brief's setting) and palette-quantised lossless WebP are both measured;
   lossless ships when it fits, as for the hero cube (crisper thin lines, no chroma smear on the orange).
   Budgets: desktop <= 2.5 MB, mobile <= 1.2 MB (every second frame if 150 cannot fit), posters <= 60 KB,
   MP4 (H.264) + WebM (VP9) <= 900 KB.   Usage: python3 encode.py
"""
import glob, json, os, shutil, subprocess, sys, zipfile
from PIL import Image, ImageDraw, ImageFont

HERE = os.path.dirname(os.path.abspath(__file__))
OUT = os.environ.get('ORBIT_OUT', os.path.join(HERE, 'out'))
PNG = os.path.join(OUT, 'png')
NAME = 'mea-orbit-proses'
DIST = os.path.join(OUT, 'dist', NAME)
ZIP_TO = os.environ.get('ORBIT_ZIP', os.path.join(OUT, NAME + '.zip'))
SIZE = {'desktop': 1200, 'mobile': 800}
BUDGET = {'desktop': 2_500_000, 'mobile': 1_200_000}
POSTER_MAX, VIDEO_MAX = 60_000, 900_000
LADDER = [256, 128, 64]          # palette sizes tried, largest first
CHECK = [0, 37, 75, 112, 149]
HOLDS = [17, 47, 77, 107, 149]   # a complete pictogram per step (150-frame scale)

def log(*a): print(*a, flush=True)
def pngs(name): return sorted(glob.glob(os.path.join(PNG, name, '[0-9][0-9][0-9].png')))
def kb(n): return f'{n / 1000:.0f} KB'

def palette(im, colors):
    return im.convert('RGB').quantize(colors=colors, method=Image.Quantize.FASTOCTREE, dither=Image.Dither.NONE).convert('RGB')
def save_lossless(im, path, colors): palette(im, colors).save(path, 'WEBP', lossless=True, quality=95, method=6)
def save_lossy(im, path, q=75): im.convert('RGB').save(path, 'WEBP', quality=q, method=6)

def encode_seq(files, dst, fmt):
    shutil.rmtree(dst, ignore_errors=True); os.makedirs(dst)
    total = 0
    for j, f in enumerate(files):
        p = os.path.join(dst, f'{j:03d}.webp'); im = Image.open(f)
        save_lossy(im, p) if fmt == 'q75' else save_lossless(im, p, fmt)
        total += os.path.getsize(p)
    return total

def estimate(files, fmt):
    tmp = os.path.join(OUT, '_est'); sub = files[::6]
    try: return encode_seq(sub, tmp, fmt) * len(files) / len(sub)
    finally: shutil.rmtree(tmp, ignore_errors=True)

def fit(files, dst, budget):
    """Lossless palette first (largest palette that fits), then lossy q75. Returns a report or None."""
    for fmt in LADDER + ['q75']:
        est = estimate(files, fmt)
        log(f'  estimate {fmt}: {est / 1e6:.2f} MB ({len(files)} frames)')
        if est > budget * 0.97: continue
        total = encode_seq(files, dst, fmt)
        log(f'  encoded  {fmt}: {total / 1e6:.3f} MB ({len(files)} frames)')
        if total <= budget:
            return {'webp': 'lossy q75' if fmt == 'q75' else f'lossless, {fmt}-colour palette', 'frames': len(files), 'bytes': total}
    return None

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

def check_sheet(rep, dst, W=400, pad=34):
    sheet = Image.new('RGB', (W * 5 + 4 * 8, (pad + W) * 2), (228, 228, 231)); d = ImageDraw.Draw(sheet); fnt = font(15)
    for row, k in enumerate(('desktop', 'mobile')):
        n, y = rep[k]['frames'], pad + row * (pad + W)
        for j, f in enumerate(CHECK):
            idx = round(f * (n - 1) / 149); x = j * (W + 8)
            im = Image.open(os.path.join(DIST, 'frames', k, f'{idx:03d}.webp')).convert('RGB').resize((W, W), Image.LANCZOS)
            sheet.paste(im, (x, y))
            d.text((x + 6, y - 22), f'{k} {SIZE[k]} px, frame {f}, at {W} px', fill=(40, 40, 44), font=fnt)
    sheet.save(dst)

LOCK = """PAGE     #FFFFFF · ink #1A1A1A · muted #71717A (inactive labels)
ORBIT    ellipse rx 395 / ry 288 @1200 · 2 px @1200 / 1.5 px @800 · #E4E4E7
STATION  40 px circles · 1.5 px #1A1A1A outline @1200 / 1.25 px @800 · white fill · number Inter 600 20 px
ACTIVE   #EC7832 → #F69541, 135° (hero lock) · covers the outline · number #FFFFFF
DOT      12 px @1200 / 9 px @800 · #EC7832
LABEL    Inter 600 · 32 px @1200 · tracking −1% · active #1A1A1A, others #71717A · side labels wrap to 2 lines
PICTO    3 px #1A1A1A @1200 / 2.25 px @800 · round caps · orange only for the active detail
         (gradient fills; the tick is a 5 px gradient stroke) · tile labels 30 px, "Senin" 28 px
MOTION   hero lock: exponential ease-out with a C1 start ramp; the dot's orbit travel uses a longer ramp
         (tau 0.35) so the first scrub step stays small · lines draw on and off in the same direction ·
         no fades, crossfades, glows, particles, bounce or camera moves"""

MAP = """Equal share: each station owns 30 frames. The dot lands on the first frame of a share and leaves 8 frames
before its end; a pictogram clears before the next one starts building.

| Station | Share | Lands + fills | Build | Hold | Clear | Travel |
|---|---|---|---|---|---|---|
| 1 Riset awal toko | 0-29 | active at frame 0 | 1-13 | 13-20 | 20-25 | 22-30 (1 to 2) |
| 2 Strategi tertulis | 30-59 | 30-33 | 31-43 | 43-50 | 50-55 | 52-60 (2 to 3) |
| 3 Rencana per periode | 60-89 | 60-63 | 61-73 | 73-80 | 80-85 | 82-90 (3 to 4) |
| 4 Eksekusi tim | 90-119 | 90-93 | 91-103 | 103-110 | 110-115 | 112-120 (4 to 5) |
| 5 Rekap mingguan | 120-149 | 120-123 | 121-133 | 133-149 | - | - |

Stations shrink back into the dot over the two frames before each departure (20-22, 50-52, 80-82, 110-112).

1. Baseline, three bars grow, the magnifier draws over bar 1 and passes to bar 3; bars read orange inside the lens.
2. Document outline and fold, three lines fill in one by one, one orange tick.
3. Calendar body and rings, four week cells, then weeks 1-4 light up in sequence (day dots turn white).
4. Four tiles draw (Iklan, Konten, Live, Toko), labels wipe in, all four switch on together.
5. Axes and four week ticks, the line rises through four weeks, the last point lands orange, "Senin" wipes in."""

def main():
    rep = {}
    shutil.rmtree(os.path.join(OUT, 'dist'), ignore_errors=True)
    for k in ('desktop', 'mobile'):
        files = pngs(k); assert len(files) == 150, f'{k}: {len(files)} PNGs, expected 150'
        log(f'{k}: {len(files)} frames, budget {BUDGET[k] / 1e6:.1f} MB')
        rep[k] = {'lossyQ75': round(estimate(files, 'q75'))}
        log(f'  lossy q75 reference: {rep[k]["lossyQ75"] / 1e6:.2f} MB')
        r = fit(files, os.path.join(DIST, 'frames', k), BUDGET[k])
        if not r:
            log('  150 frames do not fit: every second frame (0, 2, ... 148; 148 sits inside the final hold)')
            r = fit(files[::2], os.path.join(DIST, 'frames', k), BUDGET[k]); r['note'] = 'every second frame of the 150'
        assert r, f'{k} cannot meet the budget'
        rep[k].update(r); rep[k]['size'] = SIZE[k]
    os.makedirs(os.path.join(DIST, 'posters'))
    for k in ('desktop', 'mobile'):
        files = pngs(k)
        for tag, src in (('first', files[0]), ('last', files[-1])):
            dst = os.path.join(DIST, 'posters', f'poster-{tag}-{SIZE[k]}.webp')
            for colors in LADDER:
                save_lossless(Image.open(src), dst, colors)
                if os.path.getsize(dst) <= POSTER_MAX: break
            rep[f'poster-{tag}-{SIZE[k]}'] = os.path.getsize(dst)
            log(f'poster-{tag}-{SIZE[k]}: {kb(os.path.getsize(dst))}')
    os.makedirs(os.path.join(DIST, 'video'))
    assert len(pngs('video')) == 180, 'video PNGs missing'
    rep['mp4'] = best_crf(enc_mp4, os.path.join(DIST, 'video', 'orbit-proses.mp4'), 20, 14, 34)
    rep['webm'] = best_crf(enc_webm, os.path.join(DIST, 'video', 'orbit-proses.webm'), 32, 24, 46)
    log('video', rep['mp4'], rep['webm'])
    os.makedirs(os.path.join(DIST, 'checks')); check_sheet(rep, os.path.join(DIST, 'checks', 'check-400.png'))

    manifest = {
        'desktop': {'files': f'frames/desktop/000.webp to {rep["desktop"]["frames"] - 1:03d}.webp', 'count': rep['desktop']['frames'], 'size': 1200, 'bytes': rep['desktop']['bytes'], 'webp': rep['desktop']['webp']},
        'mobile': {'files': f'frames/mobile/000.webp to {rep["mobile"]["frames"] - 1:03d}.webp', 'count': rep['mobile']['frames'], 'size': 800, 'bytes': rep['mobile']['bytes'], 'webp': rep['mobile']['webp']},
        'background': '#FFFFFF (opaque)',
        'scroll': 'progress = clamp(-section.getBoundingClientRect().top / (section.offsetHeight - innerHeight), 0, 1); frame = round(progress * (count - 1))',
        'steps': 'on the 150-frame scale step k (0-4) owns frames 30k..30k+29; active step = min(4, floor(frame150 / 30))',
        'holdFrames150': HOLDS,
        'breakpoint': 'mobile set below 768 px viewport width',
    }
    for k in ('desktop', 'mobile'):
        if rep[k].get('note'): manifest[k]['note'] = rep[k]['note']
    with open(os.path.join(DIST, 'frames', 'manifest.json'), 'w') as f: json.dump(manifest, f, indent=2)

    shutil.copytree(os.path.join(HERE, 'demo'), os.path.join(DIST, 'demo'))
    demo = os.path.join(DIST, 'demo', 'index.html'); s = open(demo).read()
    s = s.replace('@@DCOUNT@@', str(rep['desktop']['frames'])).replace('@@MCOUNT@@', str(rep['mobile']['frames']))
    open(demo, 'w').write(s)
    for w in ('400', '600'): shutil.copy(os.path.join(HERE, 'vendor', f'inter-{w}.woff2'), os.path.join(DIST, 'demo'))
    src = os.path.join(DIST, 'source'); os.makedirs(os.path.join(src, 'vendor'))
    for f in ('orbit.html', 'render.py', 'encode.py'): shutil.copy(os.path.join(HERE, f), src)
    shutil.copytree(os.path.join(HERE, 'demo'), os.path.join(src, 'demo'))
    for f in ('inter-400.woff2', 'inter-600.woff2', 'Inter-LICENSE.txt'): shutil.copy(os.path.join(HERE, 'vendor', f), os.path.join(src, 'vendor'))

    info = json.load(open(os.path.join(OUT, 'info.json'))) if os.path.exists(os.path.join(OUT, 'info.json')) else None
    write_readme(rep, info)
    with open(os.path.join(OUT, 'report.json'), 'w') as f: json.dump(rep, f, indent=2)
    if os.path.exists(ZIP_TO): os.remove(ZIP_TO)
    with zipfile.ZipFile(ZIP_TO, 'w', zipfile.ZIP_DEFLATED) as z:
        for root, _, names in os.walk(DIST):
            for n in sorted(names):
                p = os.path.join(root, n); z.write(p, os.path.relpath(p, os.path.dirname(DIST)))
    log('ZIP', ZIP_TO, kb(os.path.getsize(ZIP_TO)))

def write_readme(rep, info):
    d, m = rep['desktop'], rep['mobile']
    why = (f'Lossy WebP at q75 (the brief\'s setting) measured {d["lossyQ75"] / 1e6:.2f} MB desktop and {m["lossyQ75"] / 1e6:.2f} MB mobile. '
           f'Palette-quantised lossless WebP, the hero cube\'s format, came in at {d["bytes"] / 1e6:.2f} MB and {m["bytes"] / 1e6:.2f} MB and keeps the 3 px lines '
           'and the orange edges exact, so it ships instead.') if 'lossless' in d['webp'] else \
          f'Frames are lossy WebP q75 ({d["bytes"] / 1e6:.2f} MB desktop, {m["bytes"] / 1e6:.2f} MB mobile).'
    lay = ''
    if info:
        st = ', '.join(f'{s["step"]} at ({s["x"]:.0f}, {s["y"]:.0f})' for s in info['stations'])
        gaps = min(l['orbitGap'] for l in info['labels']); edge = min(l['edgeGap'] for l in info['labels'])
        pg = min(p['orbitGap'] for p in info['picto']); ps = min(p['stationGap'] for p in info['picto'])
        lay = (f'\n## Layout (measured at render time)\n\nStations sit at equal arc length on the orbit path (getPointAtLength), clockwise from the top: {st}.\n'
               f'Label boxes clear the orbit by at least {gaps:.0f} px and the frame edge by at least {edge:.0f} px; no label overlaps another.\n'
               f'Pictograms clear the orbit by at least {pg:.0f} px and the stations by at least {ps:.0f} px. '
               f'The dot lands exactly on each station (0 px error) and moves at most {info["audit"]["maxDotStepPx"]:.0f} px between two frames.\n')
    mn = f' ({m["note"]})' if m.get('note') else ''
    txt = f'''# MEA Agency orbit proses (M3)

Scroll-scrubbed image sequence for the meagency.co.id "cara kerja" section: the sticky square to the left of
the five steps, on the #FFFFFF page. Frame 0 = section top at the viewport top; the last frame = section bottom
at the viewport bottom. On mobile the square sits above the steps.

## Contents

- frames/desktop/000-{d["frames"] - 1:03d}.webp: {d["frames"]} frames, 1200x1200, opaque #FFFFFF, {kb(d["bytes"])} total ({d["webp"]})
- frames/mobile/000-{m["frames"] - 1:03d}.webp: {m["frames"]} frames, 800x800, opaque #FFFFFF, {kb(m["bytes"])} total ({m["webp"]}){mn}
- frames/manifest.json: counts, sizes, encode settings, the scroll formula and the step ranges
- posters/poster-first-1200.webp ({kb(rep["poster-first-1200"])}) and poster-first-800.webp ({kb(rep["poster-first-800"])}): frame 0, the LCP-safe image
- posters/poster-last-1200.webp ({kb(rep["poster-last-1200"])}) and poster-last-800.webp ({kb(rep["poster-last-800"])}): the final frame
- video/orbit-proses.mp4: H.264, 1200x1200, 6 s, 30 fps, no audio, on #FFFFFF ({kb(rep["mp4"]["bytes"])}, CRF {rep["mp4"]["crf"]})
- video/orbit-proses.webm: VP9, same format ({kb(rep["webm"]["bytes"])}, CRF {rep["webm"]["crf"]})
- demo/index.html: scroll-scrub test page, sticky canvas left and five step blocks right (stacked on mobile)
- checks/check-400.png: frames 0, 37, 75, 112 and 149 shown 400 px wide, desktop and mobile rows
- source/orbit.html: the SVG scene; window.seek(p) renders any p from 0 to 1
- source/render.py, source/encode.py: Playwright render, then WebP, poster and video encode and zip

{why}

## Wiring it into the site

1. Put poster-first in the square as a normal img with fetchpriority="high". It is the LCP image.
2. After window load, fetch frames progressively (0, last, every 16th, 8th, 4th, 2nd, then the rest)
   and draw the nearest loaded frame on a canvas. demo/index.html does exactly this.
3. Use the mobile set below a 768 px viewport, desktop above.
4. progress = clamp(-section.getBoundingClientRect().top / (section.offsetHeight - innerHeight), 0, 1);
   frame = round(progress x (count - 1)). Ease the drawn frame toward the target inside requestAnimationFrame.
5. Keep the five step blocks in sync with equal shares: give the step column padding-block: 50svh and five blocks
   of the same height. Step k is then centred on screen at progress (k + 0.5) / 5, which is frame 15 + 30k on the
   150-frame scale, inside its hold. Highlight step min(4, floor(frame150 / 30)). On mobile the square is sticky
   above the steps, so use padding-block: (100svh - square height) / 2 instead.
6. prefers-reduced-motion: skip the sequence and show the hold frame of the current step (frames 17, 47, 77, 107,
   149 on the 150-frame scale), swapped instantly as the steps change. The demo does this.

The steps' text lives in HTML; the frames carry only the short station names.

## Test on a phone

The demo loads frames by relative path, so serve the folder instead of opening the file directly:
run `npx serve .` inside the unzipped folder, put the phone on the same Wi-Fi and open
http://<computer-ip>:3000/demo/ . Or upload the whole folder to any static host.

## Re-render

From source/: `pip install playwright pillow`, then `python3 render.py test` (check sheet + info),
`python3 render.py full` (PNGs into source/out/png) and `python3 encode.py` (WebP, posters, video, zip).
The fallback videos need ffmpeg with libx264 and libvpx-vp9.
{lay}
## Style lock

```text
{LOCK}
```

## Frame map (150 frames, 0-149)

{MAP}
'''
    open(os.path.join(DIST, 'README.md'), 'w').write(txt)

if __name__ == '__main__':
    main()
