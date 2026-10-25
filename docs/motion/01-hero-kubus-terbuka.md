# M1 — Hero "Kubus Terbuka" (homepage, scroll-scrub)

Tempel ke chat baru. Upload logo MEA Agency (PNG transparan atau SVG).
Prompt ini **mengunci gaya** untuk M2–M4: simpan poster akhirnya.

```text
<inputs>
Ask me for anything below I haven't given you. If I skip it, use the default:
- MEA Agency logo file (default: none; use a plain orange gradient square as the brand mark)
- Frame count (default: 120 frames)
- Size (default: 1200x1200 desktop sequence, 720x720 mobile sequence)
</inputs>

<context>
MEA Agency (meagency.co.id, PT MEA Agensi Digital, Bandung) runs Shopee and TikTok Shop operations for brands with Rp 250 million to 1 billion monthly revenue: ads, store management, content, live, and affiliate.
The homepage hero is dark (#1A1A1A). The left column holds the H1 "Toko online Anda, kami yang jalankan" and two buttons as HTML. Your animation fills the square on the right. The website scrubs your image sequence with scroll: frame 0 = page top, last frame = when the hero has scrolled away.
The object is a cube whose six faces are MEA Agency's six services: Shopee, TikTok Shop, Iklan, Live, Affiliate, Konten. Scrolling "opens" the operation: the cube comes apart into six service tiles around a small orange core that stands for the team.
</context>

<direction>
Quiet luxury, product-reveal pacing, nothing decorative.
- Background transparent (the page supplies #1A1A1A). Faces #232325 with a 2 px orange gradient edge (#EC7832 → #F69541, 135°). Labels Inter 600, #FAFAFA, centred on each face.
- Two or three thin orbit rings (1.5 px, #F69541 at 15–40% opacity) around the cube, each with one small orange dot travelling slowly.
- Story: frames 0–20 closed cube, three faces visible (Shopee, Iklan, Live), slow rotation. Frames 20–80 the cube turns and the six faces separate outward along their normals while staying parallel to camera at the end. Frames 80–120 the six tiles settle into a calm hexagonal arrangement around a small orange core; rings expand slightly and come to rest.
- Soft, physically plausible light only: a gentle top-left key, no bloom. Exponential ease-out on every move.
- Camera drifts from 1.0 to 1.04 scale across the whole sequence.
Banned: particle bursts, glows, lens flares, camera shake, RGB split, crossfades, generic gradients, bouncy easing, confetti, money or coin icons, platform logos, anything that looks like a template.
</direction>

<structure>
Write a frame map first (frame ranges per phase, what each face does), then build exactly that. Write down the style lock (edge width, face colour, label size, ring opacity, dot size) at the top of your answer; later prompts will reuse it.
</structure>

<build>
1. One HTML file using three.js, pinned version, loaded locally. Every transform is computed from progress p (0–1) inside window.seek(p): no CSS animations, no timers, no state between frames.
2. Render with Playwright: 120 frames at 1200x1200 and 120 frames at 720x720, transparent background.
3. Export WebP frames (quality ~75, alpha). Budget: desktop sequence ≤ 2.5 MB total, mobile ≤ 1 MB total. If over budget, drop to every second frame on mobile.
4. Export posters: frame 0 and the final frame (WebP ≤ 60 KB each). Frame 0 is the LCP-safe image the site shows before the sequence loads.
5. Also export a 6 s MP4 (H.264) and WebM (VP9) of the full sequence, no audio, ≤ 900 KB each, as a fallback.
6. Write a tiny scroll-scrub demo page (canvas + IntersectionObserver + requestAnimationFrame, dark #1A1A1A page with a fake H1 on the left) that loads the sequence, so I can test it on my phone.
7. Before the full render, export frames 0, 30, 60, 90 and 119 and check them at 360 px wide. Fix anything cluttered or unreadable.
</build>

<gotchas>
- Labels min 40 px at 1200x1200 so they survive a 360 px phone.
- "TikTok Shop" is the longest label: size every face's text to fit it, then use that same size on all faces.
- Faces viewed edge-on must never show label text mirrored; keep labels front-facing in the final arrangement.
- If Google Fonts is blocked, download Inter from GitHub (rsms/inter) and load it locally.
- If Playwright cannot download its browser, use a preinstalled Chromium.
</gotchas>

<start>
Show me the frame map and the style lock, then build it without asking me anything else. Give me the two frame folders, the posters, the fallback videos, and the demo page in one zip.
</start>
```
