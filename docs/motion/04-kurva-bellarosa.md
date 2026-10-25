# M4 — "Kurva Bellarosa" (studi kasus unggulan, homepage)

Tempel ke chat baru. Upload poster akhir M1 dan tempel style lock dari M1.
Angka di bawah sudah tampil di meagency.co.id dan izin publikasinya sudah dimiliki.

```text
<inputs>
Ask me for anything below I haven't given you. If I skip it, use the default:
- Style lock and final poster from the hero clip
- Size (default: 1200x900 desktop, 900x900 mobile)
- Length (default: 7 s, plays once when visible, then holds the last frame)
</inputs>

<context>
MEA Agency (meagency.co.id) features one case study on its homepage: Bellarosa, a new brand that broke into its category. Results: GMV Rp 8,2 miliar, ROAS 21, and 75% of traffic from organic content and affiliate versus 25% from ads.
The clip sits next to the case-study text on a #FAFAFA section. The headline and button stay in HTML; you render only the data story.
</context>

<direction>
Editorial data animation, quiet and exact.
- 0–3 s: a single 3 px #1A1A1A line draws left to right on a faint #E4E4E7 baseline grid and climbs; the end point turns orange and the label "Rp 8,2 miliar" (Instrument Serif, large) settles above it. No axis numbers.
- 3–5.5 s: the line's end point becomes a ring that splits into two arcs: 75% in the orange gradient, 25% in #46474B. Labels "Organik & affiliate 75%" and "Iklan 25%" in Inter 600.
- 5.5–7 s: "ROAS 21x" appears in Instrument Serif beneath the ring. Hold.
- Exponential ease-out, no overshoot. All text is exactly as written above (Indonesian decimal comma).
Banned: particles, glows, confetti, coins, arrows shooting up, bouncy easing, platform logos, any number not listed here.
</direction>

<structure>
Write a second-by-second storyboard first, then build exactly that.
</structure>

<build>
1. One HTML file; window.seek(t) computes every style from time. No CSS animations, no timers.
2. Render with Playwright at 60 fps, 3 subframes per frame blended with ffmpeg tmix.
3. Deliver MP4 (H.264) and WebM (VP9) for desktop and mobile sizes, no audio, ≤ 600 KB each, plus a WebP poster of the final frame (≤ 60 KB) that the site shows under reduced motion.
4. Before rendering, export one frame per second and check them at 360 px wide.
</build>

<gotchas>
- "Rp 8,2 miliar" must use a comma, never "8.2".
- Labels min 32 px at 1200x900.
- If Google Fonts is blocked, download Instrument Serif and Inter from GitHub (google/fonts, rsms/inter) and load them locally.
- If Playwright cannot download its browser, use a preinstalled Chromium.
</gotchas>

<start>
Show me the storyboard, then build it without asking me anything else. Give me the videos and posters in one zip.
</start>
```
