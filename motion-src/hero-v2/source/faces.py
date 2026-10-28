#!/usr/bin/env python3
"""Per-frame textures for the two video faces of the MEA hero cube. Silent: audio streams are never decoded.

  python3 faces.py CLIP_5A CLIP_5B CLIP_6

  seq/content/000-119.jpg  clip 5A (fashion, left phone) and 5B (skincare, right phone), seconds 0-5, each shown
                           whole as a 9:16 phone screen (no crop), two phones on the #FAFAFA plate
  seq/live/000-119.jpg     clip 6 (live shopping), seconds 4.5-9.5, the full 1:1 frame
  photos/content.jpg, photos/live.jpg   frame 0 of each, used when seq/ is missing
Sequence frame i uses image i (desktop p = i/119; mobile frame i uses round(i * 119 / 59)). Needs ffmpeg and pillow.
"""
import subprocess, sys, tempfile
from pathlib import Path
from PIL import Image, ImageDraw, ImageFilter

SRC = Path(__file__).resolve().parent
N, FPS, T6 = 120, 24, 4.5
W, SW, SH, BZ, GAP = 1024, 456, 811, 12, 24          # texture, phone screen (9:16), bezel, gap
OW, OH = SW + 2 * BZ, SH + 2 * BZ
X0, Y0 = (W - (2 * OW + GAP)) // 2, (W - OH) // 2


def frames(clip, start, tmp):
    subprocess.run(['ffmpeg', '-hide_banner', '-loglevel', 'error', '-y', '-ss', str(start), '-i', str(clip),
                    '-map', '0:v:0', '-an', '-vf', f'fps={FPS}', '-frames:v', str(N), '-start_number', '0',
                    str(Path(tmp) / '%03d.png')], check=True)
    fs = sorted(Path(tmp).glob('*.png'))
    assert len(fs) == N, f'{clip}: expected {N} frames from {start}s, got {len(fs)}'
    return fs


def build(c5a, c5b, c6):
    shadow = Image.new('L', (W, W), 0); sd = ImageDraw.Draw(shadow)
    for k in range(2):
        x = X0 + k * (OW + GAP); sd.rounded_rectangle((x + 6, Y0 + 18, x + OW - 6, Y0 + OH + 10), radius=64, fill=60)
    shadow = shadow.filter(ImageFilter.GaussianBlur(14))
    screen = Image.new('L', (SW, SH), 0); ImageDraw.Draw(screen).rounded_rectangle((0, 0, SW - 1, SH - 1), radius=52, fill=255)
    out5, out6 = SRC / 'seq' / 'content', SRC / 'seq' / 'live'
    for d in (out5, out6):
        d.mkdir(parents=True, exist_ok=True)
        for p in d.glob('*.jpg'):
            p.unlink()
    with tempfile.TemporaryDirectory() as ta, tempfile.TemporaryDirectory() as tb, tempfile.TemporaryDirectory() as t6:
        fa, fb, f6 = frames(c5a, 0, ta), frames(c5b, 0, tb), frames(c6, T6, t6)
        for i in range(N):
            im = Image.new('RGB', (W, W), (250, 250, 250)); im.paste((0, 0, 0), (0, 0), shadow); d = ImageDraw.Draw(im)
            for k, f in enumerate((fa[i], fb[i])):
                x = X0 + k * (OW + GAP)
                d.rounded_rectangle((x, Y0, x + OW - 1, Y0 + OH - 1), radius=64, fill=(22, 22, 25), outline=(78, 78, 84), width=2)
                im.paste(Image.open(f).convert('RGB').resize((SW, SH), Image.LANCZOS), (x + BZ, Y0 + BZ), screen)
                ix = x + BZ + (SW - 116) // 2; d.rounded_rectangle((ix, Y0 + BZ + 14, ix + 116, Y0 + BZ + 42), radius=14, fill=(8, 8, 10))
            im.save(out5 / f'{i:03d}.jpg', quality=90)
            Image.open(f6[i]).convert('RGB').resize((W, W), Image.LANCZOS).save(out6 / f'{i:03d}.jpg', quality=90)
    (SRC / 'photos').mkdir(exist_ok=True)
    Image.open(out5 / '000.jpg').save(SRC / 'photos' / 'content.jpg', quality=92)
    Image.open(out6 / '000.jpg').save(SRC / 'photos' / 'live.jpg', quality=92)
    print('built', out5, out6)


if __name__ == '__main__':
    build(*sys.argv[1:4])
