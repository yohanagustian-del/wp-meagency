# M2 — "Enam Sisi" (loop mini per layanan)

Tempel ke chat baru. Upload poster akhir M1 dan tempel style lock dari M1.

```text
<inputs>
Ask me for anything below I haven't given you. If I skip it, use the default:
- Style lock and final poster from the hero clip (default: faces #232325, 2 px orange gradient edge #EC7832 → #F69541, labels Inter 600 #FAFAFA)
- Size (default: 480x480, displayed at 120–160 px)
</inputs>

<context>
MEA Agency (meagency.co.id) shows its six services as a grid on the homepage and on service pages, on a light #FAFAFA background. Each service gets one tiny looping animation that plays on hover (desktop) or when it enters the screen (mobile). The service name and description stay in HTML next to it; do not render text except where noted.
Each loop is one face of the hero cube, seen straight on, with one minimal pictogram that explains the service.
</context>

<direction>
Same visual language as the hero cube: dark tile #232325 with square corners (as in the hero tiles), 2 px orange gradient edge drawn inside the tile edge, pictogram in 3 px lines of #FAFAFA with orange only for the one moving element.
The six loops (3 s each, seamless):
1. Iklan: three horizontal budget bars; the orange bar grows while the others shrink, then they rebalance.
2. TikTok Shop: a simple storefront tile; a small orange dot travels from a play-button shape to the store and the store tile pulses once.
3. Shopee: a shopping bag outline; three product squares drop into it one by one, the last one orange.
4. Live: a phone outline with a small orange pill labelled "LIVE" (the only text allowed); viewer dots appear and drift up.
5. Affiliate: one centre node and five outer nodes; orange lines connect outer to centre one at a time.
6. Konten: three vertical video frames sliding left in a filmstrip; the centre one turns orange-edged.
Motion is slow and precise, exponential ease, a 400 ms hold at the loop point.
Banned: platform logos, glows, particles, bouncy easing, emoji, coins, any text other than "LIVE".
</direction>

<structure>
Write a one-line second-by-second storyboard for each loop, then build exactly those six.
</structure>

<build>
1. One HTML file with six scenes, each seekable through window.seek(scene, t). Every style is computed from time: no CSS animations, no timers.
2. Render each scene with Playwright at 30 fps, 2 subframes per frame blended with ffmpeg tmix.
3. Deliver per loop: WebM (VP9, alpha if possible) and MP4 (H.264, background #232325 baked in), no audio, ≤ 150 KB each, plus a WebP poster of frame 0 (≤ 20 KB).
4. Before rendering, export frame 0 and the mid frame of each loop and check them at 120 px wide. Simplify anything that reads as noise at that size.
</build>

<gotchas>
- At 120 px wide, lines thinner than 3 px at 480x480 disappear; keep 3 px minimum.
- The loop point must be pixel-identical to frame 0.
- If Playwright cannot download its browser, use a preinstalled Chromium.
</gotchas>

<start>
Show me the six storyboards, then build them without asking me anything else. Give me all loop files and posters in one zip, named iklan, tiktok-shop, shopee, live, affiliate, konten.
</start>
```
