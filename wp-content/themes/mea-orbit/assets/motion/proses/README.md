# MEA Agency orbit proses (M3)

Scroll-scrubbed image sequence for the meagency.co.id "cara kerja" section: the sticky square to the left of
the five steps, on the #FFFFFF page. Frame 0 = section top at the viewport top; the last frame = section bottom
at the viewport bottom. On mobile the square sits above the steps.

## Contents

- frames/desktop/000-149.webp: 150 frames, 1200x1200, opaque #FFFFFF, 2057 KB total (lossless, 256-colour palette)
- frames/mobile/000-074.webp: 75 frames, 800x800, opaque #FFFFFF, 788 KB total (lossless, 256-colour palette) (every second frame of the 150)
- frames/manifest.json: counts, sizes, encode settings, the scroll formula and the step ranges
- posters/poster-first-1200.webp (12 KB) and poster-first-800.webp (9 KB): frame 0, the LCP-safe image
- posters/poster-last-1200.webp (15 KB) and poster-last-800.webp (11 KB): the final frame
- video/orbit-proses.mp4: H.264, 1200x1200, 6 s, 30 fps, no audio, on #FFFFFF (185 KB, CRF 14)
- video/orbit-proses.webm: VP9, same format (190 KB, CRF 24)
- demo/index.html: scroll-scrub test page, sticky canvas left and five step blocks right (stacked on mobile)
- checks/check-400.png: frames 0, 37, 75, 112 and 149 shown 400 px wide, desktop and mobile rows
- source/orbit.html: the SVG scene; window.seek(p) renders any p from 0 to 1
- source/render.py, source/encode.py: Playwright render, then WebP, poster and video encode and zip

Lossy WebP at q75 (the brief's setting) measured 2.17 MB desktop and 1.38 MB mobile. Palette-quantised lossless WebP, the hero cube's format, came in at 2.06 MB and 0.79 MB and keeps the 3 px lines and the orange edges exact, so it ships instead.

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

## Layout (measured at render time)

Stations sit at equal arc length on the orbit path (getPointAtLength), clockwise from the top: 1 at (600, 334), 2 at (969, 518), 3 at (810, 866), 4 at (390, 866), 5 at (231, 518).
Label boxes clear the orbit by at least 17 px and the frame edge by at least 44 px; no label overlaps another.
Pictograms clear the orbit by at least 81 px and the stations by at least 73 px. The dot lands exactly on each station (0 px error) and moves at most 96 px between two frames.

## Style lock

```text
PAGE     #FFFFFF · ink #1A1A1A · muted #71717A (inactive labels)
ORBIT    ellipse rx 395 / ry 288 @1200 · 2 px @1200 / 1.5 px @800 · #E4E4E7
STATION  40 px circles · 1.5 px #1A1A1A outline @1200 / 1.25 px @800 · white fill · number Inter 600 20 px
ACTIVE   #EC7832 → #F69541, 135° (hero lock) · covers the outline · number #FFFFFF
DOT      12 px @1200 / 9 px @800 · #EC7832
LABEL    Inter 600 · 32 px @1200 · tracking −1% · active #1A1A1A, others #71717A · side labels wrap to 2 lines
PICTO    3 px #1A1A1A @1200 / 2.25 px @800 · round caps · orange only for the active detail
         (gradient fills; the tick is a 5 px gradient stroke) · tile labels 30 px, "Senin" 28 px
MOTION   hero lock: exponential ease-out with a C1 start ramp; the dot's orbit travel uses a longer ramp
         (tau 0.35) so the first scrub step stays small · lines draw on and off in the same direction ·
         no fades, crossfades, glows, particles, bounce or camera moves
```

## Frame map (150 frames, 0-149)

Equal share: each station owns 30 frames. The dot lands on the first frame of a share and leaves 8 frames
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
5. Axes and four week ticks, the line rises through four weeks, the last point lands orange, "Senin" wipes in.
