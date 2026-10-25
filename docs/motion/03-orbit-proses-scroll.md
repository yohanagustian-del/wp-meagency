# M3 — "Orbit Proses" (Cara kerja, scroll-scrub sticky)

Tempel ke chat baru. Upload poster akhir M1 dan tempel style lock dari M1.

```text
<inputs>
Ask me for anything below I haven't given you. If I skip it, use the default:
- Style lock and final poster from the hero clip
- Frame count (default: 150 frames)
- Size (default: 1200x1200 desktop sequence, 800x800 mobile sequence)
</inputs>

<context>
MEA Agency (meagency.co.id) explains how every engagement runs in five steps. On desktop the left column is sticky and shows your animation; the right column scrolls through the five steps (text lives in HTML). The website scrubs your sequence with scroll: frame 0 = section top, last frame = section bottom. On mobile the animation sits above the steps at 800x800.
The five steps, in order:
1. Riset awal toko — the team reads the store's Seller Center data before giving any advice.
2. Strategi tertulis — targets, channel priorities and risks are written and agreed.
3. Rencana per periode — strategy becomes a monthly plan with weekly targets.
4. Eksekusi tim — ads, content, live and store are executed by their divisions.
5. Rekap mingguan — last week's results and next steps are reported every week.
</context>

<direction>
Light page (#FFFFFF). One wide elliptical orbit drawn in 2 px #E4E4E7, five stations on it as 40 px circles with 1.5 px #1A1A1A outline and the step number inside (Inter 600). A single orange dot (#EC7832) travels the orbit from station 1 to 5; the active station fills with the orange gradient.
At each station a minimal pictogram builds in the centre of the orbit (3 px #1A1A1A lines, orange only for the active detail), then clears as the dot moves on:
1. A magnifier passing over three small bar-chart columns.
2. A document whose three lines fill in, one orange tick at the end.
3. A calendar grid of four weeks; the four week cells light up in sequence.
4. Four small tiles (labelled Iklan, Konten, Live, Toko in Inter 600) switching on together.
5. A line chart rising across four weeks, the last point orange, with a small "Senin" label.
Station labels next to each circle: the short step name in Inter 600, min 28 px.
Calm pacing: each station gets an equal share of frames, with a short hold where the pictogram is complete.
Banned: particle bursts, glows, lens flares, camera shake, crossfades, bouncy easing, confetti, coins, platform logos.
</direction>

<structure>
Write a frame map first (frame ranges per station and per travel), then build exactly that.
</structure>

<build>
1. One HTML file. Every style is computed from progress p (0–1) inside window.seek(p): no CSS animations, no timers, no state between frames.
2. Render with Playwright: 150 frames at 1200x1200 and 150 frames at 800x800.
3. Export WebP frames (quality ~75). Budget: desktop ≤ 2.5 MB total, mobile ≤ 1.2 MB total (mobile may use every second frame if over).
4. Export posters for frame 0 and the final frame, plus a 6 s MP4/WebM fallback of the full sequence.
5. Write a tiny scroll-scrub demo page (sticky canvas left, five dummy step blocks right) so I can test it in the browser.
6. Before the full render, export frames 0, 37, 75, 112 and 149 and check them at 400 px wide. Fix anything cluttered.
</build>

<gotchas>
- Labels min 28 px at 1200x1200 so they survive the phone size.
- Measure station positions on the ellipse at runtime; never hard-code where the dot meets a station.
- If Google Fonts is blocked, download Inter from GitHub (rsms/inter).
- If Playwright cannot download its browser, use a preinstalled Chromium.
</gotchas>

<start>
Show me the frame map, then build it without asking me anything else. Give me the frame folders, the fallback video, the posters, and the demo page in one zip.
</start>
```
