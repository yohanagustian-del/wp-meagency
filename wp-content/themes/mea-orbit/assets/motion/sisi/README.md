# MEA Agency service loops · "Enam Sisi" (M2)

Six 3-second loops for the services grid on meagency.co.id (homepage and service pages, on #FAFAFA).
Each loop is one face of the M1 hero cube seen straight on, with one pictogram that explains the service.
The service name and description stay in HTML next to it; the only text inside a tile is "LIVE".
They play on hover on desktop and while in view on mobile. Frame 0 is the rest state and the poster.

## Contents

| loop | WebM (VP9) | MP4 (H.264) | poster (WebP, frame 0) |
|---|---|---|---|
| iklan | 17.3 KB | 19.6 KB | 2.7 KB |
| tiktok-shop | 41.8 KB | 51.7 KB | 4.8 KB |
| shopee | 24.1 KB | 30.9 KB | 3.4 KB |
| live | 35.5 KB | 45.5 KB | 4.6 KB |
| affiliate | 37.9 KB | 49.1 KB | 9.2 KB |
| konten | 41.3 KB | 58.0 KB | 3.0 KB |

- All clips: 480x480, 30 fps, 90 frames, 3.000 s, no audio, yuv420p, tagged bt709 / limited range.
  WebM is VP9 profile 0 at CRF 12, MP4 is H.264 High at constant QP 12. Posters are lossless and
  pixel-identical to rendered frame 0.
- demo/index.html: the six tiles in a grid with the playback wiring below. Serve the folder (for example
  `npx serve .`) and open /demo/.
- checks/: qa-120-actual.png (the grid at 120 px, actual size), qa-120-x3.png (the same, enlarged),
  qa-overview.png (frame 0 and the mid frame at 480, 160 and 120 px, including a worst-case
  single-tap bilinear downscale), timeline-every-0.2s.png, encode-report.json.
- source/: enam-sisi.html (all six scenes, window.seek(scene, t)), render.py (render, blend, encode).

## Wiring it into the site

```html
<video muted playsinline preload="none" width="480" height="480" aria-hidden="true"
       disablepictureinpicture disableremoteplayback poster="iklan.webp">
  <source src="iklan.webm" type="video/webm">
  <source src="iklan.mp4" type="video/mp4">
</video>
```

1. Show the tile at 120-160 CSS px. Keep it square with no border-radius: the tile has square corners and
   its orange edge is drawn inside the frame.
2. Desktop (`(hover: hover) and (pointer: fine)`): on pointerenter and on focus of the card, set
   `loop = true` and call `play()`. On pointerleave and blur, set `loop = false`. The clip finishes its
   current cycle and stops on its last frame, which is identical to the poster, so nothing jumps.
   Never reset `currentTime` on leave: that cuts a move in half.
3. Mobile: an IntersectionObserver at threshold 0.6 calls the same two functions.
4. After window load, set `preload = "auto"` and call `load()` only on clips that have not started yet
   (`paused && readyState === 0`): `load()` resets a clip that is already playing. Frame 0 equals the
   poster, so the swap from poster to video is invisible.
5. `prefers-reduced-motion: reduce`: do not play. The poster is the complete pictogram.
6. If `play()` is refused (low-power mode), the poster simply stays.

demo/index.html implements all of this in about 30 lines.

## Storyboards

Every move uses the M1 ease (exponential ease-out with a short C1 start ramp). The hold at the loop
point is 400 ms: 0.0-0.1 s at the start plus 2.7-3.0 s at the end, so frames 81-89 and 0-2 are identical.

- **iklan** · 0.1-1.05 s the orange (middle) bar grows 128 to 208 px while the two white bars shrink 40 px
  each, so the total budget stays the same · 1.05-1.45 hold · 1.45-2.7 rebalance to the starting proportions.
- **tiktok-shop** · rest: orange dot docked on the play button · 0.1-1.1 the dot travels to the store door ·
  1.0-1.2 it shrinks into the door · 1.1-1.95 the store pulses once (1 to 1.06 to 1) · 2.05-2.65 a new dot
  grows on the play button.
- **shopee** · rest: orange product peeking out of the bag · white product #1 drops in at 0.1 s and lands
  on top, the stack sinks one slot (0.56-1.0) · white #2 the same from 0.95 s · orange #3 drops at 1.8 s and
  the stack sinks (2.26-2.7), leaving orange peeking out again.
- **live** · 0.1-0.95 the LIVE pill pulses once (1 to 1.08 to 1) · at 0.3, 0.58, 0.86, 1.14 and 1.42 s a viewer
  dot appears at the lower right of the screen and drifts up 150-170 px while fading over 1.25 s ·
  the last dot is gone at 2.67 s.
- **affiliate** · rest: all five orange connectors drawn · 0.1-0.5 they draw back into the centre node ·
  at 0.55, 0.88, 1.21, 1.54 and 1.87 s they connect again one at a time, clockwise from the top,
  each growing from the outer node to the centre in 0.6 s.
- **konten** · rest: the centre frame is orange-edged · 0.1-1.15 the filmstrip slides left by one frame;
  the frame arriving in the centre turns orange, the one leaving turns white · 1.15-1.65 hold ·
  1.65-2.7 one more frame.

## Style lock (M2)

```text
FACE    #232325, fills the 480 frame edge to edge, square corners
EDGE    3 px inside the frame edge, #EC7832 -> #F69541 at 135 deg, one tile-wide gradient field
        (the hero's 2 px on a 292 px tile = 3.3 px on 480; 3 px is also the floor for 120 px display)
LINES   3 px #FAFAFA, butt caps (round on the speaker slot), miter joins (round on the play triangle)
ORANGE  only the moving element, from the same tile-wide gradient: fills (bar, dot, product, pill),
        or 4 px strokes placed on the 4 px grid (affiliate connectors, konten frame edge)
LIVE    Inter 600, 26 px, +1 px tracking, #232325 knocked out of the orange pill
MOTION  M1 ease on every move · 400 ms hold across the loop point · banned: logos, glow, particles,
        bounce, emoji, coins, any text but LIVE
```

## Where this differs from the brief, and why

- **Orange strokes are 4 px, not 3.** At 120 px a 3 px orange line covers 75% of one pixel on #232325 and
  reads brown; the centre konten frame then looked dimmer than its white neighbours. 4 px on the 4 px grid
  is one full-colour pixel at 120 px. White lines stay 3 px.
- **"LIVE" is #232325, not #FAFAFA.** White on the orange gradient is about 2.2-3.1:1; the dark face colour
  is about 4.9-6.9:1. The MEA Agency brand rule keeps orange as a field, never under light text.
- **WebM has no alpha channel.** The face fills the frame with square corners, so no pixel is transparent and
  an alpha plane would be 100% everywhere. WebM and MP4 therefore look identical; the MP4 needs nothing baked in.
- **Konten has rails but no sprocket holes.** At 120 px the holes read as noise.

## Verification

- Render level: the frame at t = 3.0 equals the frame at t = 0 and every hold frame equals frame 0,
  pixel for pixel, on all six loops. Blended frame 89 equals blended frame 0.
- The ffmpeg tmix output matches a numpy average of the two subframes exactly.
- Decoded level: on all twelve clips, decoded frame 89 equals decoded frame 0 exactly (loop seam 0).
  Frame-to-frame change inside the end hold is at most 3 levels.
- In Chromium the WebM face decodes to exactly #232325; the edge corners decode within 6 levels of
  #EC7832 / #F69541 (4:2:0 chroma on a 3 px line).
- demo/index.html tested in Chromium: hover starts, leaving stops at t = 3.0 on the poster-identical frame,
  re-hover restarts, keyboard focus plays, mobile plays while in view and stops at the loop point when
  scrolled away, reduced motion stays on the poster.

## Re-render

From source/: `pip install playwright pillow numpy`, then

```text
python3 render.py qa       frame 0 + mid frame at 480 / 160 / 120 px and the loop-point audit -> out/checks/
python3 render.py full     180 subframes per loop (60 per second)                            -> out/sub/
python3 render.py encode   tmix to 90 frames, WebM, MP4, WebP poster, decode checks          -> out/dist/
```

ffmpeg needs libvpx-vp9 and libx264. If Playwright cannot download its browser, point it at a
preinstalled Chromium with `CHROMIUM=/path/to/chrome`. Open source/enam-sisi.html in any browser to scrub
a scene by hand (scene picker + time slider; no timers, every style is computed from t).

Two traps the script already handles:

- **Partial raster.** Chromium re-rasters only the rectangles that changed, so anti-aliased edges where
  shapes overlap can land 1-2 levels off between two renders of identical markup. That alone breaks a
  pixel-identical loop point. render.py launches Chromium with `--disable-partial-raster`.
- **Encoder seam.** Keyframes are forced at frames 0, 81 and 89 with a constant quantiser (x264 without
  mbtree and AQ, VP9 without alt-ref). Intra frames coded from the same image reconstruct identically, so
  the last decoded frame equals the first.
