# MEA Agency hero cube v2 (platform logos + MEA services)

Scroll-scrubbed image sequence for the meagency.co.id homepage hero: the square to the right of
"Toko online Anda, kami yang jalankan" on the #1A1A1A page. Frame 0 = page top; the site maps progress
so the last frame is reached at 60% of the hero height. Same folders and file names as the M1 package,
so it drops in as `motion-src/hero-v2/`. Built @@DATE@@.

Four faces carry the official platform logos (Shopee, TikTok, Tokopedia, Meta). The two faces without a
logo carry MEA's own services as silent video: two creator reviews (content) and a live-shopping session.
Per Yohan, the services are what the closed cube shows first; Tokopedia and Meta stay hidden until it turns.

## Contents

- frames/desktop/000-119.webp: 120 frames, 1200x1200, transparent, @@D_KB@@ KB total (lossy WebP q@@D_Q@@, @@D_ALPHA@@; PSNR inside the content box @@D_PSNR@@ dB mean, @@D_PSNRMIN@@ dB worst, against the PNG render)
- frames/mobile/000-059.webp: 60 frames, 720x720, transparent, @@M_KB@@ KB total (lossy WebP q@@M_Q@@, @@M_ALPHA@@; @@M_PSNR@@ dB mean, @@M_PSNRMIN@@ dB worst). Mobile frame i is rendered at p = i/59 (desktop frame i at p = i/119), so both sets start and end on the same pose
- frames/manifest.json: counts, sizes, encode settings, faces and the scroll formula (paths relative to the manifest)
- posters/poster-first-1200.webp (@@PF1200@@ KB) and poster-first-720.webp (@@PF720@@ KB): frame 0, the LCP-safe image
- posters/poster-last-1200.webp (@@PL1200@@ KB) and poster-last-720.webp (@@PL720@@ KB): the final frame (WebP quality @@PQ@@ in that order)
- video/hero-cube.mp4: H.264 High, 1200x1200, 6 s, 20 fps (the 120 frames), keyframe every 20 frames for scrubbing, BT.709, no audio track, on #1A1A1A (@@MP4_KB@@ KB, CRF @@MP4_CRF@@; decoded background @@MP4_BG@@)
- video/hero-cube.webm: VP9, same format, no audio track (@@WEBM_KB@@ KB, CRF @@WEBM_CRF@@; decoded background @@WEBM_BG@@)
- demo/index.html: scroll-scrub test page (canvas + IntersectionObserver + requestAnimationFrame) with Scroll away, Pinned and Video fallback modes
- checks/check-360.png: frames 0, 30, 60, 90, 119 (desktop row) and 0, 15, 30, 45, 59 (mobile row), decoded from the shipped WebPs, 360 px wide
- source/cube.html: the three.js r170 scene (source/vendor/three.module.min.js, loaded locally); window.seek(p) renders any p from 0 to 1
- source/faces.py: builds the per-frame video textures from the three clips; source/render.py, prep.py, encode.py, README.template.md: render, size-only pre-processing, encode, package
- source/logos/: the logo files exactly as supplied; source/photos/: frame 0 of each video face (used only if source/seq/ is missing)

KB = 1024 bytes. Budgets, checked by encode.py against strict decimal limits: desktop <= 3,200,000 bytes,
mobile <= 1,300,000, each poster <= 60,000, each video <= 900,000.

@@WEBP_NOTE@@

## Faces

@@FACES@@

Logos: each is a texture of the file's own pixels on an unlit plane (MeshBasicMaterial, sRGB, mipmaps, 16x
anisotropic) on a #FAFAFA plate: original colours and proportions, no redraw, recolour or AI upscaling, and no
lighting, gloss, bevel or shadow on the logo itself. The files are opaque, so each keeps its own background on
the plate (white for Shopee, Tokopedia and Meta, black for TikTok).

Video faces: an inset window of 1.60 u (80% of the face) with rounded corners, unlit like a screen. Content shows
both 9:16 clips whole as two phone screens side by side on the #FAFAFA plate (rounded screen corners and a small
camera pill touch only background, never a face). Live shows the whole 1:1 frame. Both are silent: the clips
are decoded video-only (`ffmpeg -map 0:v:0 -an`, which also skips the cover-art stream), and nothing in the page
or the files carries audio. Sequence frame i shows clip frame i, so each clip advances one frame per scroll
frame (5 s of footage across the scroll) and plays backwards when the visitor scrolls up. Live uses seconds
4.5-9.5 of its clip: the host already holds the bag with both assistants clapping on the closed cube, and the
sequence ends on a calm smile.

## Wiring it into the site

1. Put poster-first in the square as a normal img with fetchpriority="high". It is the LCP image.
2. After window load, fetch frames progressively (0, last, every 16th, 8th, 4th, 2nd, then the rest)
   and draw the nearest loaded frame on a canvas. demo/index.html does exactly this.
3. Use the mobile set below a 768 px viewport, desktop above.
4. progress = scrollY / (heroTop + 0.6 x heroHeight), clamped to 0-1; frame = round(progress x (count - 1)).
   Ease the drawn frame toward the target inside requestAnimationFrame for a smooth scrub.
5. prefers-reduced-motion: show poster-last and skip the sequence.
6. Video fallback: video.currentTime = progress x duration. Keep it muted; the files have no audio track.

The demo's Pinned mode keeps the hero sticky so the whole sequence plays while the square stays in view;
compare it with Scroll away before choosing. To test on a phone, serve the folder (`npx serve .`), put the
phone on the same Wi-Fi and open http://<computer-ip>:3000/demo/ .

## Style lock

```text
UNITS   u = scene units, cube edge = 2.00 u
CUBE    six tiles mitred at 45 deg, seams hidden in the bevel · bevel radius 0.12 u (6% of the edge) · tile depth 0.14 u
FACE    #202022 satin: MeshPhysical roughness 0.50, specular 0.50, clearcoat 0.12 (roughness 0.45)
BEVEL   #202022 polished chamfer: roughness 0.28, clearcoat 0.40 (roughness 0.25); takes the key and the rim as thin lines
BACK    #1C1C1E matte (roughness 0.85): tile backs and mitre walls, seen only while the tiles turn
RIM     #F69541 light from back-right, just below the horizon (direction 0.41, -0.52, -0.75), 0.7, plus a #F69541
        reflection strip on the same line: a thin #EC7832 -> #F69541 line on the right-hand silhouette bevels
PLATE   logos: #FAFAFA, inset 0.02 u, 1.24 u square (Meta 1.60 x 0.86 u), corner radius 0.15 u
LOGO    file pixels, unlit · square logos 0.90 u = 45% of the face · Meta 1.40 u wide = 70% · clear space >= 18.9%
VIDEO   inset window 1.60 u = 80% of the face, corner radius 0.16 u, unlit, inset 0.02 u
        content: two 456 x 811 px (9:16) screens per 1024 px texture, bezel #161619 with a 2 px #4E4E54 rim, camera pill
LIGHT   warm white key from top-left (-0.66, 0.71, 0.25), 5.0 · cool lateral fill from the right (0.99, 0.06, 0.12), 2.6
        soft contact shadow under the closed cube, fading out over frames 78-100 (shipped tighter and lighter, peak 16%: see the WebP note)
CAMERA  perspective, 28 deg FOV, 35 deg above · dolly 11.80 -> 11.51 u (frames 0-76), then -> 16.00 u (80-119)
        16-sample thin-lens depth of field (aperture 0.05 u) and jittered anti-aliasing; highlights clipped hue-preserving
RINGS   3 · 1 px at 1200 and 720 · #F69541 at 30% / 20% / 12% · always behind the 3D layer
DOTS    2 · 8 px at 1200, 6 px at 720 · #F69541 · travel 20-30 deg in total, rest in open gaps
CORE    sphere 0.84 u across · emissive #EC7832, key-lit side towards #F69541 · no logo, no glow
LAYOUT  hexagon R = 2.45 u (368 px at 1200) · tiles about 290 px · gaps about 0.19 u
MOTION  critically damped ease-out on every move: starts from rest, exponential tail, no overshoot
```

## Frame map (120 frames, 0-119)

- 0-30 · Closed cube, 3/4 view from above: content (two creators) front-right, live shopping front-left, Shopee on the lid. TikTok, Meta and Tokopedia are hidden. Both videos run with the scroll; slow turntable (+8 deg); the mid and outer rings orbit; the core hides inside.
- 30-50 · Turntable +90 deg: content turns away to the right (gone by frame 37), TikTok comes in from the left (frame 39), live moves to the front.
- 42-64 · Turntable +90 deg: live turns away (frame 49), Meta comes in (frame 51), TikTok moves to the front.
- 50-76 · One 90 deg tumble about the front-right axis: Shopee rolls away (frame 58), Tokopedia rises from below (frame 61), Meta tips onto the top. From frame 76 the cube rests: Meta on top, Tokopedia front-left, TikTok front-right.
- 78-96 · The six faces move straight out along their own normals and become tiles (2.05 u from the centre); the orange core shows through the opening seams.
- 84-112 · Every tile turns to face the camera, logo or video upright. Tiles that faced away show their matte back while they turn, never a mirrored logo.
- 88-118 · The tiles glide into one plane on the hexagon: four swing 60 deg clockwise, live stays at the bottom, and TikTok swings 120 deg, passing in front of live, so content lands top-centre and live bottom-centre. The camera eases back, the rings widen and flatten, the dots come to rest.

```text
              content
  Tokopedia            Meta
             (core)
  TikTok               Shopee
               live
```

The final layout is the same as the M1 package: the four logos keep their places and the two video faces
fill the former empty top-centre and bottom-centre tiles.

## Checks done

- Motion probe (`render.py probe`) on every frame: no logo is ever seen mirrored, no two tiles intersect after frame 80, every tile stays inside 94.5% of the frame, and in the last frame every tile faces the camera upright.
- Upright while visible: content and live 0-3 deg; Shopee on the lid follows the turntable like any top face; Tokopedia and Meta within 25 deg as side faces; TikTok sits on the tumble axis, so it turns with the tumble (up to about 57 deg at the resting pose, frames 64-84) until the tiles turn upright.
- Face changes: a rigid cube cannot change which faces it shows without passing an instant where only two faces are visible. Here that happens three times, 2 frames each, at full turning speed (frames 37-38, 49-50, 58-60).
- Video frames: the clips are shown whole, so nothing is cropped by design; in the frames checked by eye (0, 60 and 119 of each texture) the rounded screen corners and the camera pill cover only background, and no stray logo or text appears.
- Contact sheets before and after encoding: logos crisp and readable, original colours, plate and window edges clean.
- Contact shadow, desktop: libwebp's 12-level alpha pre-quantisation (needed for the 3.2 MB budget) leaves the shadow in 2 alpha levels (7.5% and 17.6% black), so it shows faint steps of up to 2.7 8-bit levels on #1A1A1A instead of a smooth fade. Mobile keeps the full gradient (lossless alpha). A smooth desktop shadow needs lossless alpha, which does not fit 3.2 MB.

## Updating a face

- New or edited clips: `python3 faces.py CLIP_5A CLIP_5B CLIP_6` (any audio is ignored), then `python3 render.py full --force` and `python3 encode.py`.
- TikTok Shop: replace source/logos/tiktok.png with the official TikTok Shop file and re-render the same way.

## Re-render

From source/: `pip install playwright pillow numpy`, then `python3 faces.py ...` (video textures),
`python3 render.py probe` (motion checks), `python3 render.py test` (pre-render contact sheet),
`python3 render.py full --force` (PNGs into source/out/png) and `python3 encode.py` (WebP, posters, video,
manifest, checks, README, zip). ffmpeg with libx264 and libvpx-vp9 is needed. If Playwright cannot download its
browser, render.py uses a preinstalled Chromium. Rendering runs on the CPU here (SwiftShader): about 7 s per
desktop frame and 3 s per mobile frame on one core, much faster on a GPU.
