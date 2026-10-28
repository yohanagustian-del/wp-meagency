#!/usr/bin/env python3
"""MEA hero cube v2: turn the rendered PNGs into the web package and zip it.

  python3 encode.py [--zip PATH]

Reads source/out/png/{desktop,mobile}/NNN.png (from `render.py full`) and writes, next to source/:
frames/desktop/*.webp, frames/mobile/*.webp, frames/manifest.json, posters/*.webp, video/hero-cube.{mp4,webm},
checks/check-360.png (decoded from the shipped WebPs), README.md, then a zip with everything under hero-v2/.
WebP quality starts at 75 and steps down only as far as the budget needs. Needs pillow, numpy and ffmpeg
(libx264 + libvpx-vp9).
"""
import argparse, io, json, math, shutil, subprocess, sys, time, zipfile
from pathlib import Path

import numpy as np
from PIL import Image

SRC = Path(__file__).resolve().parent
PKG = SRC.parent
PNG = SRC / 'out' / 'png'
sys.path.insert(0, str(SRC))
from render import contact_sheet, on_bg, TEST_D, TEST_M  # noqa: E402
from prep import prep  # noqa: E402

LIMIT = {'desktop': 3_200_000, 'mobile': 1_300_000, 'poster': 60_000, 'video': 900_000}  # bytes, strict decimal; raised for the two video faces (approved by Yohan)
# (quality, alpha_quality) in order of preference; libwebp pre-quantises alpha to 2 + aq/5 levels when aq <= 70
CANDS = {'desktop': [(52, 50), (50, 50), (52, 30), (50, 30)],
         'mobile': [(75, 100), (75, 70), (72, 70), (70, 50), (66, 50), (62, 50), (58, 30), (54, 30), (50, 30)]}
FPS = 20
VF = 'scale=out_color_matrix=bt709:out_range=tv,format=yuv420p'
TAGS = ['-colorspace', 'bt709', '-color_primaries', 'bt709', '-color_trc', 'bt709']
KB = lambda n: round(n / 1024)


def log(*a):
    print(time.strftime('%H:%M:%S'), *a, flush=True)


def webp(img, q, aq=100, method=6):
    b = io.BytesIO()
    img.save(b, 'WEBP', quality=q, alpha_quality=aq, method=method, exact=False)
    return b.getvalue()


def psnr_content(png_path, webp_path):
    """PSNR on #1A1A1A, inside the bounding box of the rendered content (the empty background would inflate it)."""
    src = Image.open(png_path).convert('RGBA')
    box = src.getchannel('A').getbbox()
    a = np.asarray(on_bg(src).crop(box), dtype=np.float32)
    b = np.asarray(on_bg(Image.open(webp_path)).crop(box), dtype=np.float32)
    mse = float(((a - b) ** 2).mean())
    return 99.0 if mse == 0 else 10 * math.log10(255 ** 2 / mse)


def levels(aq):
    return None if aq >= 100 else (2 + aq // 5 if aq <= 70 else 16 + (aq - 70) * 8)


def prep_all(files, dst):
    dst.mkdir(parents=True, exist_ok=True)
    out = []
    for f in files:
        p = dst / f.name
        prep(Image.open(f)).save(p, compress_level=1)
        out.append(p)
    log(f'prep: {len(out)} frames -> {dst.relative_to(SRC)}')
    return out


def encode_set(name, orig, prepped, outdir):
    limit, cands = LIMIT[name], CANDS[name]
    sample, k, ratio = prepped[::4], 0, None
    while True:
        while k < len(cands) - 1:   # estimate on every 4th frame with method 4, scaled by the measured method 6 / 4 ratio
            q, aq = cands[k]
            s4 = sum(len(webp(Image.open(f), q, aq, method=4)) for f in sample)
            if ratio is None:
                ratio = sum(len(webp(Image.open(f), q, aq, method=6)) for f in sample) / s4
                log(f'{name}: method 6 / method 4 size ratio {ratio:.3f}')
            est = s4 * ratio * len(prepped) / len(sample)
            log(f'{name}: q{q} alpha{aq} estimate {KB(est)} KB (limit {KB(limit)} KB)')
            if est <= limit:   # the full encode below is still checked against the limit
                break
            k += 1
        q, aq = cands[k]
        outdir.mkdir(parents=True, exist_ok=True)
        for old in outdir.glob('*.webp'):
            old.unlink()
        total = 0
        for f in prepped:
            data = webp(Image.open(f), q, aq, method=6)
            (outdir / f'{f.stem}.webp').write_bytes(data)
            total += len(data)
        log(f'{name}: q{q} alpha{aq} -> {KB(total)} KB')
        if total <= limit or k >= len(cands) - 1:
            break
        k += 1
    ps = [psnr_content(orig[i], outdir / f'{orig[i].stem}.webp') for i in range(0, len(orig), 8)]
    return {'quality': q, 'alphaQuality': aq, 'alphaLevels': levels(aq) or 'lossless', 'method': 6, 'bytes': total,
            'psnrMean': round(float(np.mean(ps)), 1), 'psnrMin': round(float(np.min(ps)), 1),
            'preprocess': 'source/prep.py: alpha bleeding + soft-shadow alpha quantisation (at most 2/255 change on #1A1A1A)'}


def poster(png_path, out):
    img = Image.open(png_path)
    for q in (90, 86, 82, 78, 74, 70, 66, 62, 58, 54, 50):
        data = webp(img, q, 100, method=6)
        if len(data) <= LIMIT['poster']:
            out.write_bytes(data)
            return {'quality': q, 'bytes': len(data)}
    out.write_bytes(data)
    return {'quality': q, 'bytes': len(data), 'overBudget': True}


def ffmpeg(args):
    subprocess.run(['ffmpeg', '-hide_banner', '-loglevel', 'error', '-y', *args], check=True)


def enc_mp4(src, out, crf):
    ffmpeg(['-framerate', str(FPS), '-i', str(src / '%03d.png'), '-vf', VF, '-c:v', 'libx264', '-preset', 'slow', '-crf', str(crf),
            '-profile:v', 'high', '-g', str(FPS), '-movflags', '+faststart', '-an', *TAGS, str(out)])


def enc_webm(src, out, crf):
    ffmpeg(['-framerate', str(FPS), '-i', str(src / '%03d.png'), '-vf', VF, '-c:v', 'libvpx-vp9', '-b:v', '0', '-crf', str(crf),
            '-deadline', 'good', '-cpu-used', '2', '-row-mt', '1', '-g', str(FPS), '-an', *TAGS, str(out)])


def fit_video(kind, enc, src, out, crf, crf_max, base):
    limit, tried = LIMIT['video'], []
    for _ in range(6):
        enc(src, out, crf)
        size = out.stat().st_size
        tried.append((crf, size))
        log(f'{kind}: crf {crf} -> {KB(size)} KB')
        if size <= 0.97 * limit:
            if len(tried) == 1 and size < 0.5 * limit and crf > 16:   # far under budget: spend some of it on quality
                better = out.with_suffix('.try' + out.suffix)
                enc(src, better, crf - 3)
                if better.stat().st_size <= 0.97 * limit:
                    better.replace(out)
                    crf, size = crf - 3, out.stat().st_size
                    log(f'{kind}: crf {crf} -> {KB(size)} KB (kept)')
                else:
                    better.unlink()
            break
        crf = min(crf_max, crf + max(1, math.ceil(math.log(size / (0.9 * limit)) / math.log(base))))
    return {'crf': crf, 'bytes': size}


def corner_rgb(video):
    png = video.with_suffix('.f0.png')
    ffmpeg(['-i', str(video), '-frames:v', '1', str(png)])
    px = np.asarray(Image.open(png).convert('RGB'))[8:40, 8:40].reshape(-1, 3).mean(0)
    png.unlink()
    return [int(round(v)) for v in px]


def faces():
    L = SRC / 'logos'
    dims = lambda p: '%dx%d' % Image.open(p).size
    seqd = lambda n: f"source/seq/{n}/000-119.jpg" if (SRC / 'seq' / n / '000.jpg').exists() else f'source/photos/{n}.jpg (still)'
    return [
        {'frame0': 'lid', 'final': 'lower-right', 'name': 'Shopee', 'file': 'source/logos/shopee.png', 'size': dims(L / 'shopee.png'), 'note': 'logo file as supplied'},
        {'frame0': 'front-right', 'final': 'top-centre', 'name': 'Content: two creator reviews', 'file': seqd('content'), 'size': '1024x1024 per frame',
         'note': 'grok-klip-5A (fashion, left phone) + grok-klip-5B (skincare, right phone), seconds 0-5, whole 9:16 frames, no crop, silent'},
        {'frame0': 'front-left', 'final': 'bottom-centre', 'name': 'Live shopping', 'file': seqd('live'), 'size': '1024x1024 per frame',
         'note': 'grok-klip-6, seconds 4.5-9.5, whole 1:1 frame, silent'},
        {'frame0': 'hidden (back-left)', 'final': 'lower-left', 'name': 'TikTok', 'file': 'source/logos/tiktok.png', 'size': dims(L / 'tiktok.png'), 'note': 'logo file as supplied'},
        {'frame0': 'hidden (back-right)', 'final': 'upper-right', 'name': 'Meta', 'file': 'source/logos/meta.png', 'size': dims(L / 'meta.png'), 'note': 'logo file as supplied, fitted by width'},
        {'frame0': 'hidden (bottom)', 'final': 'upper-left', 'name': 'Tokopedia', 'file': 'source/logos/tokopedia.png', 'size': dims(L / 'tokopedia.png'), 'note': 'logo file as supplied'},
    ]


def build_zip(dst):
    dst.parent.mkdir(parents=True, exist_ok=True)
    with zipfile.ZipFile(dst, 'w', zipfile.ZIP_DEFLATED, compresslevel=9) as z:
        for p in sorted(PKG.rglob('*')):
            rel = p.relative_to(PKG)
            if p.is_dir() or rel.parts[:2] in (('source', 'out'), ('source', 'seq')) or '__pycache__' in rel.parts:
                continue
            stored = p.suffix.lower() in ('.webp', '.mp4', '.webm', '.png', '.zip')
            z.write(p, Path('hero-v2') / rel, compress_type=zipfile.ZIP_STORED if stored else zipfile.ZIP_DEFLATED)
    return dst


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument('--zip', default='/mnt/user-data/outputs/mea-hero-cube-v2.zip')
    a = ap.parse_args()
    d = sorted((PNG / 'desktop').glob('*.png'))
    m = sorted((PNG / 'mobile').glob('*.png'))
    assert len(d) == 120 and len(m) == 60, f'need 120 desktop + 60 mobile PNGs, found {len(d)} + {len(m)}'

    pd, pm = prep_all(d, SRC / 'out' / 'prep' / 'desktop'), prep_all(m, SRC / 'out' / 'prep' / 'mobile')
    res = {'desktop': encode_set('desktop', d, pd, PKG / 'frames' / 'desktop'),
           'mobile': encode_set('mobile', m, pm, PKG / 'frames' / 'mobile')}

    (PKG / 'posters').mkdir(exist_ok=True)
    pos = {'poster-first-1200': poster(pd[0], PKG / 'posters' / 'poster-first-1200.webp'),
           'poster-first-720': poster(pm[0], PKG / 'posters' / 'poster-first-720.webp'),
           'poster-last-1200': poster(pd[-1], PKG / 'posters' / 'poster-last-1200.webp'),
           'poster-last-720': poster(pm[-1], PKG / 'posters' / 'poster-last-720.webp')}
    log('posters', {k: (v['quality'], KB(v['bytes'])) for k, v in pos.items()})

    vtmp = SRC / 'out' / 'video_frames'
    if vtmp.exists():
        shutil.rmtree(vtmp)
    vtmp.mkdir(parents=True)
    for i, f in enumerate(d):
        on_bg(Image.open(f)).save(vtmp / f'{i:03d}.png', compress_level=1)
    (PKG / 'video').mkdir(exist_ok=True)
    mp4, webm = PKG / 'video' / 'hero-cube.mp4', PKG / 'video' / 'hero-cube.webm'
    vid = {'mp4': fit_video('mp4', enc_mp4, vtmp, mp4, 22, 40, 1.12), 'webm': fit_video('webm', enc_webm, vtmp, webm, 34, 55, 1.07)}
    vid['mp4']['cornerRGB'], vid['webm']['cornerRGB'] = corner_rgb(mp4), corner_rgb(webm)
    shutil.rmtree(vtmp)

    (PKG / 'checks').mkdir(exist_ok=True)
    pre = SRC / 'out' / 'test' / 'check-360.png'
    if pre.exists() and not (PKG / 'checks' / 'check-360-prerender.png').exists():
        shutil.copy(pre, PKG / 'checks' / 'check-360-prerender.png')
    contact_sheet([('desktop 1200 px -> 360, decoded from the shipped WebP (frame / p)',
                    [(PKG / 'frames' / 'desktop' / f'{i:03d}.webp', f'frame {i}  p={i / 119:.3f}') for i in TEST_D]),
                   ('mobile 720 px -> 360, decoded from the shipped WebP (frame / p)',
                    [(PKG / 'frames' / 'mobile' / f'{i:03d}.webp', f'm{i:02d}  p={i / 59:.3f}') for i in TEST_M])],
                  PKG / 'checks' / 'check-360.png', 'MEA hero cube v2 - shipped frames')

    fc = faces()
    man = {
        'name': 'mea-hero-cube', 'version': '2 (platform logos)', 'generated': time.strftime('%Y-%m-%d'),
        'pathsRelativeTo': 'this file (frames/manifest.json)',
        'background': '#1A1A1A', 'transparent': True,
        'scroll': {'frame0': 'top of page',
                   'progress': 'clamp(scrollY / (heroTop + 0.6 * heroHeight), 0, 1)',
                   'frame': 'round(progress * (count - 1))',
                   'note': 'last frame at 60% of the hero height; ease the drawn frame toward the target inside requestAnimationFrame'},
        'loadOrder': '0, last, every 16th, every 8th, every 4th, every 2nd, then the rest',
        'sets': {
            'desktop': {'pattern': 'desktop/%03d.webp', 'count': 120, 'width': 1200, 'height': 1200, 'use': 'viewport >= 768 px',
                        'frameProgress': 'i / 119', 'bytes': res['desktop']['bytes'], 'webp': res['desktop']},
            'mobile': {'pattern': 'mobile/%03d.webp', 'count': 60, 'width': 720, 'height': 720, 'use': 'viewport < 768 px',
                       'frameProgress': 'i / 59', 'bytes': res['mobile']['bytes'], 'webp': res['mobile']}},
        'posters': {k: {'path': f'../posters/{k}.webp', **v} for k, v in pos.items()},
        'video': {'mp4': {'path': '../video/hero-cube.mp4', 'codec': 'H.264 High, yuv420p, BT.709', **vid['mp4']},
                  'webm': {'path': '../video/hero-cube.webm', 'codec': 'VP9, yuv420p, BT.709', **vid['webm']},
                  'width': 1200, 'height': 1200, 'fps': FPS, 'frames': 120, 'durationSeconds': 120 / FPS,
                  'keyframeEvery': FPS, 'audio': False, 'background': '#1A1A1A',
                  'scrub': 'video.currentTime = progress * duration'},
        'faces': fc, 'audio': 'none: clips decoded video-only (ffmpeg -map 0:v:0 -an); no audio track in any file',
        'source': {'scene': '../source/cube.html', 'three': 'r170 (source/vendor/three.module.min.js)', 'seek': 'window.seek(p), p in [0, 1]'},
    }
    (PKG / 'frames' / 'manifest.json').write_text(json.dumps(man, indent=2))

    def alpha(r):
        return 'lossless alpha' if r['alphaQuality'] >= 100 else f"alpha pre-quantised to {levels(r['alphaQuality'])} levels"
    face_md = '| Closed cube (frame 0) | Final layout | Content | File | Pixels | Notes |\n|---|---|---|---|---|---|\n' + '\n'.join(
        f"| {f['frame0']} | {f['final']} | {f['name']} | {f['file']} | {f['size']} | {f['note']} |" for f in fc)
    rep = {
        'D_KB': KB(res['desktop']['bytes']), 'D_Q': res['desktop']['quality'], 'D_ALPHA': alpha(res['desktop']),
        'D_PSNR': res['desktop']['psnrMean'], 'D_PSNRMIN': res['desktop']['psnrMin'],
        'M_KB': KB(res['mobile']['bytes']), 'M_Q': res['mobile']['quality'], 'M_ALPHA': alpha(res['mobile']),
        'M_PSNR': res['mobile']['psnrMean'], 'M_PSNRMIN': res['mobile']['psnrMin'],
        'PF1200': KB(pos['poster-first-1200']['bytes']), 'PF720': KB(pos['poster-first-720']['bytes']),
        'PL1200': KB(pos['poster-last-1200']['bytes']), 'PL720': KB(pos['poster-last-720']['bytes']),
        'PQ': '/'.join(str(pos[k]['quality']) for k in ('poster-first-1200', 'poster-first-720', 'poster-last-1200', 'poster-last-720')),
        'MP4_KB': KB(vid['mp4']['bytes']), 'MP4_CRF': vid['mp4']['crf'], 'WEBM_KB': KB(vid['webm']['bytes']), 'WEBM_CRF': vid['webm']['crf'],
        'MP4_BG': ','.join(map(str, vid['mp4']['cornerRGB'])), 'WEBM_BG': ','.join(map(str, vid['webm']['cornerRGB'])),
        'FACES': face_md, 'DATE': time.strftime('%Y-%m-%d'),
    }
    note = ('Every frame goes through source/prep.py before encoding. It changes only what costs bytes without being seen: '
            'fully transparent pixels take the colour of nearby content (alpha bleeding, so the thin ring lines code cleanly), and '
            'the soft contact shadow (pure black at partial opacity) is tightened into a smaller contact shadow (alpha curve '
            'gamma 2.5, peak opacity 16% instead of 35%) and quantised in alpha steps of 8, under one 8-bit level each on '
            '#1A1A1A. Logos, video faces, ring lines and cube edges are untouched. The two video faces add about 6-8 KB to every '
            'desktop frame they appear in, so the budgets were raised to 3.2 MB desktop and 1.3 MB mobile (approved by Yohan); '
            'quality and alpha levels are the highest pair that fits.')
    rep['WEBP_NOTE'] = note
    text = (SRC / 'README.template.md').read_text()
    for k, v in rep.items():
        text = text.replace(f'@@{k}@@', str(v))
    assert '@@' not in text, 'unfilled README placeholder'
    (PKG / 'README.md').write_text(text)

    z = build_zip(Path(a.zip))
    shutil.copy(PKG / 'checks' / 'check-360.png', z.parent / 'check-360.png')
    summary = {'desktopKB': KB(res['desktop']['bytes']), 'mobileKB': KB(res['mobile']['bytes']),
               'posterKB': {k: KB(v['bytes']) for k, v in pos.items()}, 'mp4KB': KB(vid['mp4']['bytes']), 'webmKB': KB(vid['webm']['bytes']),
               'webp': {k: (res[k]['quality'], res[k]['alphaQuality'], res[k]['psnrMean'], res[k]['psnrMin']) for k in res},
               'videoCornerRGB': {k: vid[k]['cornerRGB'] for k in vid}, 'zipKB': KB(z.stat().st_size)}
    log('SUMMARY', json.dumps(summary))
    within = (res['desktop']['bytes'] <= LIMIT['desktop'] and res['mobile']['bytes'] <= LIMIT['mobile'] and
              all(v['bytes'] <= LIMIT['poster'] for v in pos.values()) and all(vid[k]['bytes'] <= LIMIT['video'] for k in vid))
    log('ALL BUDGETS MET' if within else 'BUDGET EXCEEDED')
    log('ENCODE DONE')


if __name__ == '__main__':
    main()
