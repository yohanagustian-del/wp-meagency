# MEA Agency hero cube

Scroll-scrubbed image sequence for the meagency.co.id homepage hero: the square to the right of
"Toko online Anda, kami yang jalankan" on the #1A1A1A page. Frame 0 = page top; the last frame =
the hero has scrolled away.

## Contents

- frames/desktop/000-119.webp: 120 frames, 1200x1200, transparent, 2351 KB total (lossless WebP, 256-colour palette)
- frames/mobile/000-059.webp: 60 frames, 720x720, transparent, 742 KB total (lossless WebP, 256-colour palette) (every second frame of the 120)
- frames/manifest.json: counts, sizes, encode settings and the scroll formula
- posters/poster-first-1200.webp (18 KB) and poster-first-720.webp (36 KB): frame 0, the LCP-safe image
- posters/poster-last-1200.webp (47 KB) and poster-last-720.webp (30 KB): the final frame
- video/hero-cube.mp4: H.264, 1200x1200, 6 s, 30 fps, no audio, on #1A1A1A (563 KB, CRF 22)
- video/hero-cube.webm: VP9, same format (525 KB, CRF 34)
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
FACE    #232325 · tile 292 px · 8 px thick · blank back #1C1C1E
EDGE    2 px @1200 / 1.5 px @720 · #EC7832 → #F69541, 135°, inside the face edge
LABEL   Inter 600 · #FAFAFA · 40 px @1200 (41.6 px in the final frame after the drift) / 24 px @720
        sized so "TikTok Shop" = 80% of tile width · tracking −1% · same size on all faces
RINGS   3 rings · 1.5 px @1200 / 1.25 px @720 · #F69541 at 40% (core), 30% (mid), 15% (outer)
DOTS    one per ring · 10 px @1200 / 7 px @720 · #F69541 100%
CORE    MEA bar mark · 112 px wide · bars #EC7832 → #F69541, folds #BA4231
LAYOUT  hexagon R = 365 px · 24 px gaps · rings always sit behind tiles
LIGHT   one soft key, top-left + sky ambient · no bloom, no glow
MOTION  exponential ease-out on every move · camera 28° FOV, zoom 1.00 → 1.04
```

## Frame map (120 frames, 0-119)

- 0-20 · Closed cube. 3/4 view from above: Shopee on the top face, Live on the left face, Iklan on the right face. Slow turntable rotation (+8°). Mid and outer rings orbit the cube; the core and its ring are hidden inside.
- 20-80 · Turn and separate. The cube turns +77° into a true isometric pose (20-78). Faces move out along their normals (24-80), fanning into a hexagon on screen. From frame 32 each face rotates to face the camera, upright by 80; faces passing edge-on show a blank back, never mirrored text. The core is revealed and starts turning toward camera.
  Shopee (top) rises to the top slot. TikTok Shop (bottom) drops to the bottom slot. Live (left face) swings to lower-right. Iklan (right face) swings away and returns upper-right. Konten (back) comes round to lower-left. Affiliate (back) comes round to upper-left.
- 80-119 · Settle. Tiles glide into one plane on the exact hexagon: near tiles step back, far tiles step forward, all end the same size. The core settles front-on. Rings expand x1.14 and flatten; dots come to rest in open gaps.

```text
             Shopee
  Affiliate          Iklan
             [MEA]
  Konten             Live
          TikTok Shop
```

## Logo note

The core is the MEA bar mark taken from MEA_LOGO_ORANGE.ps. That file is an Illustrator file saved
without PDF content, so any other app only sees a "re-save with Create PDF Compatible File" placeholder.
Re-save it with that option, or export an SVG, before using it elsewhere.
