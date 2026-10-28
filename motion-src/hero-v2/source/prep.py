"""Size-only pre-processing for transparent WebP frames (no visible change on any background):
1) alpha bleeding: fully transparent pixels take the colour of nearby content, so blocks around thin lines
   are flat (cheap, no ringing); alpha still defines every shape;
2) the soft contact shadow (pure black, semi-transparent) is tightened (alpha curve gamma 2.5, peak 16% instead of 35%)
   and quantised to alpha steps of 8 (under one 8-bit level each on #1A1A1A), so it needs only a few alpha levels;
   ring lines, cube edges and logos keep their exact alpha."""
import numpy as np
from PIL import Image

def _box(a, r):
    c = np.cumsum(np.pad(a, ((r + 1, r), (0, 0), (0, 0))), axis=0); a = c[2 * r + 1:] - c[:-2 * r - 1]
    c = np.cumsum(np.pad(a, ((0, 0), (r + 1, r), (0, 0))), axis=1); return c[:, 2 * r + 1:] - c[:, :-2 * r - 1]

def prep(img, step=8, cut=12, gamma=2.5, peak=0.45):
    arr = np.asarray(img.convert('RGBA')).astype(np.float32)
    rgb, a = arr[..., :3], arr[..., 3]
    shadow = (rgb.max(-1) <= 4) & (a < 250)
    ta = 89.0 * np.power(np.minimum(a, 89.0) / 89.0, gamma) * peak   # tighter, lighter contact shadow (peak 35% -> 16%)
    qa = np.where(ta < cut, 0, np.round(ta / step) * step)           # steps of 8: under one 8-bit level each on #1A1A1A
    a = np.where(shadow, qa, a)
    w = (a / 255.0)[..., None]
    num, den = rgb * w, w
    for r in (3, 12, 48):                      # multi-scale fill reaches every block near content
        num, den = _box(num, r), _box(den, r)
        if r == 3: n3, d3 = num, den
    fill = np.where(den > 1e-6, num / np.maximum(den, 1e-6), 0)
    fill = np.where(d3 > 1e-6, n3 / np.maximum(d3, 1e-6), fill)
    rgb = np.where((a == 0)[..., None], fill, rgb)
    out = np.dstack([np.clip(np.round(rgb), 0, 255), np.clip(a, 0, 255)]).astype(np.uint8)
    return Image.fromarray(out, 'RGBA')
