# Paket prompt 27 Okt 2026 — tracking di tema, hero kubus v2, mockup HP konten

Urutan pakai:
1. **Prompt A** → Claude Code (repo wp-meagency). Tracking pindah ke tema.
2. **Prompt B** → Motion Lab, chat baru. Render ulang kubus hero (M1 v2) dengan logo platform.
3. **Prompt D** → Grok Imagine atau Symphony. 3 video konten AI 10 detik.
4. **Prompt C** → Claude Code (repo wp-meagency), setelah file M1 v2 dan video konten siap.

---

## Prompt A — Claude Code: semua tracking dari tema, tanpa plugin

```text
## Objective
Pindahkan seluruh tracking meagency.co.id ke tema mea-orbit, supaya situs tidak lagi bergantung pada plugin Meta pixel, Code Snippets, WPCode, atau penempatan tag Site Kit, tanpa ada event yang hilang atau tercatat dobel.

## Context
- Hasil audit sebelumnya: docs/audit/2026-10-27-tracking.md (baca seluruhnya).
- Kondisi sekarang: GTM-5XWWJBW dan GA4 G-NDEDBPBJYL dipasang Site Kit. Meta Pixel + CAPI dari plugin Meta (dikonfigurasi Code Snippets #9). TikTok Pixel dari Code Snippets #11. TikTok Events API tidak jalan. Tema belum memasang apa pun.
- Event generate_lead sudah di-push oleh plugin mea-leads saat form sukses. Setiap lead punya idempotency_key (UUID).
- Keputusan pemilik: tracking dari tema, tanpa plugin tracking.

## Target State
1. Modul inc/tracking.php di tema, aktif sitewide. Semua ID dan token dibaca dari konstanta wp-config.php, tidak pernah dari database atau kode:
   MEA_GTM_ID, MEA_META_PIXEL_ID, MEA_META_CAPI_TOKEN, MEA_TIKTOK_PIXEL_ID, MEA_TIKTOK_EVENTS_TOKEN.
   Konstanta tidak ada → bagian itu diam, tanpa error.
2. Browser: tema hanya mencetak snippet GTM (head + noscript setelah body). Meta Pixel, TikTok Pixel, dan GA4 dipasang sebagai tag di dalam GTM, bukan di tema. Tulis docs/tracking/GTM-SETUP.md berisi tag, trigger, dan variabel yang harus dibuat di GTM, termasuk trigger generate_lead.
3. Server: saat lead berhasil disimpan, kirim event Lead ke Meta Conversions API dan TikTok Events API secara async (antrian yang sama dengan pengiriman ke CDPS, retry dengan backoff).
   - event_id = idempotency_key lead. Nilai yang sama ikut di dataLayer.push generate_lead, supaya GTM meneruskannya ke pixel browser dan platform bisa menghapus duplikat.
   - Data pengguna di-hash SHA-256 sesuai spesifikasi masing-masing platform (nomor dinormalisasi ke format 62…). Hanya dikirim bila persetujuan di form dicentang.
   - Kirim juga IP, user agent, fbp/fbc, dan ttclid/ttp dari cookie bila ada.
4. Halaman admin kecil (Pengaturan → Tracking MEA): status tiap konstanta (terisi/kosong, tanpa menampilkan nilai), log 50 kiriman server terakhir per platform, tombol kirim event uji ke mode test masing-masing platform.
5. Tidak ada tracking di halaman wp-admin, halaman login, dan preview.
6. docs/tracking/MIGRASI.md: urutan mematikan tracking lama tanpa celah data (lihat bagian "Setelah deploy").

## Scope
- Kerja di wp-content/themes/mea-orbit/ (inc/tracking.php, admin, enqueue) dan integrasi kecil di wp-content/plugins/mea-leads/ untuk memicu event server.
- JANGAN menyentuh situs live. Hasil dipaketkan ke dist/ seperti biasa.

## Constraints
- Token tidak boleh tercetak di HTML, log, atau pesan error.
- Only make changes directly requested. Do not add features, abstractions, or files beyond what was asked.
- Tanpa dependency baru.
- Unit test: hashing, normalisasi nomor, bentuk payload Meta dan TikTok, event_id sama di browser dan server.

## Acceptance Criteria
- [ ] Dengan konstanta diisi nilai contoh, view-source memuat tepat satu snippet GTM dan tidak ada pixel lain dari tema.
- [ ] Lead uji menghasilkan satu event Lead di Meta Test Events dan TikTok test mode, dengan event_id sama seperti di dataLayer.
- [ ] Tanpa konstanta, situs dan form tetap jalan tanpa notice.
- [ ] Test lama dan baru hijau.

## Stop Conditions
Stop and ask before menyentuh situs live, menambah dependency, atau mengubah perilaku form selain memicu event.

## Session Strategy
Fase 0: rencana singkat di docs/tracking/PLAN.md, lalu STOP sampai saya ketik "lanjut". Fase 1: kerjakan Target State 1–6, laporkan, STOP.
```

### Setelah deploy (dikerjakan Yohan, berurutan)
1. Buat **token baru** Meta CAPI dan TikTok Events API. Token lama sempat tersimpan sebagai teks biasa di snippet #9.
2. Isi kelima konstanta di `wp-config.php`.
3. Buat tag Meta, TikTok, dan GA4 di GTM sesuai `GTM-SETUP.md`, lalu Publish.
4. Site Kit → matikan **penempatan tag** GTM dan GA4 (laporan Site Kit tetap bisa dipakai).
5. Nonaktifkan Code Snippets #9 dan #11, lalu plugin **Meta pixel for WordPress**.
6. Pindahkan redirect #12 ke Rank Math (lihat ringkasan di chat), lalu nonaktifkan #12 dan plugin **Code Snippets**.
7. Cek kotak Header & Footer di **WPCode** kosong, lalu nonaktifkan **WPCode Lite**.
8. Tes 1 lead dan cek di GTM Preview, Meta Test Events, dan TikTok Events Manager.

Catatan: karena tracking ada di tema, rollback ke Hello Elementor berarti tracking ikut mati. Ini dicatat di `MIGRASI.md`.

---

## Prompt B — Motion Lab: hero kubus v2 (premium, logo platform)

Tempel di **chat baru**. Upload:
- Logo **resmi** dari brand kit partner (SVG atau PNG transparan), satu file per sisi. Jangan pakai logo hasil gambar ulang.
- Logo MEA (SVG).
- Style lock dan poster akhir M1 lama, sebagai acuan warna dan tata letak.

```text
<inputs>
Ask me for anything below I haven't given you. If I skip it, use the default:
- Official logo or partner-badge files, one per face (faces: Shopee, TikTok Shop, Tokopedia, Meta, Google, MEA; a face whose file I do not upload becomes a plain graphite face with only the orange rim). Use only the files I upload; never redraw, trace, recolour, or approximate a logo.
- Logo treatment: original full colour, exactly as in the official file, on a light rounded plate inset into each face so every logo keeps the background its brand guidelines require
- Previous M1 style lock and final poster
</inputs>

<context>
MEA Agency (meagency.co.id) homepage hero, dark background #1A1A1A. The H1 and buttons are HTML on the left; this animation fills the square on the right and is scrubbed by scroll (frame 0 = top of page). This replaces the previous cube, which used text labels. The new cube must read as a premium physical object: each face shows one platform MEA works on, as a logo, with no text.
</context>

<direction>
Premium product render, quiet luxury.
- Object: a cube with softly bevelled edges (radius about 6% of the edge), faces in deep graphite #202022 with a satin finish, thin orange rim light (#EC7832 → #F69541) catching the bevels. No outline strokes.
- Each logo sits flat and unaltered on a light rounded plate (#FAFAFA) inset into the face, centred, about 45% of the face width, perfectly front-facing in the final arrangement. The logo itself stays a flat 2D image: no extrusion, bevel, gloss, or lighting on the logo artwork.
- Lighting: soft key from top left, faint orange rim from the back right, subtle contact shadow under the cube, very gentle reflections. Real depth: perspective camera, slight depth of field on the far faces.
- Orbit rings from the old version stay, thinner and subtler (1 px, 12–30% opacity), with one or two travelling dots.
- Story: frames 0–30 cube slowly rotating in 3/4 view from above, showing three faces. Frames 30–80 cube turns so every face passes the camera once, staying in 3/4 view (never edge-on). Frames 80–120 faces separate into six floating tiles around a small glowing MEA core, logos facing the camera.
- Exponential ease-out, no overshoot.
Banned: text labels, redrawn or approximated logos, glows larger than the rim light, particle bursts, lens flares, camera shake, crossfades, bouncy easing, emoji.
</direction>

<structure>
Write the frame map and a new style lock first, then build exactly that.
</structure>

<build>
1. One HTML file with three.js (pinned version, local), PBR materials (MeshPhysicalMaterial), logos loaded as textures from my files. Every transform computed from progress p (0–1) in window.seek(p).
2. Render with Playwright: 120 frames 1200×1200 and 60 frames 720×720, transparent background, WebP quality ~75.
3. Budget: desktop ≤ 2.5 MB, mobile ≤ 1 MB. Posters first and last frame ≤ 60 KB each. Fallback MP4/WebM ≤ 900 KB.
4. Same file names and folder structure as the previous M1 package, plus README and manifest, so it can replace the old package directly.
5. Before the full render, export frames 0, 30, 60, 90, 119 at 360 px wide and check every logo is crisp and none appears mirrored.
</build>

<gotchas>
- Logos must keep their official proportions, colours, and clear space. Never stretch, recolour, outline, or make the logo itself 3D.
- Textures at least 1024 px so logos stay sharp at 1200 px output.
- If Playwright cannot download its browser, use a preinstalled Chromium.
</gotchas>

<start>
Show me the frame map and style lock, then build it without asking anything else. Give me one zip.
</start>
```

---

## Prompt C — Claude Code: pasang hero v2 dan mockup HP berisi video konten

```text
## Objective
Ganti aset hero homepage dengan paket kubus v2, dan tambahkan tampilan mockup HP yang memutar video konten asli MEA di homepage, tanpa melanggar budget performa.

## Context
- Paket kubus v2 ada di motion-src/hero-v2/ (struktur sama dengan paket hero lama).
- Video konten ada di motion-src/konten/ (format 9:16).
- Aturan motion: docs/motion/00-peta-motion.md dan 05-claude-code-integrasi-motion.md. Budget: 0 byte motion sebelum load, desktop ≤ 5 MB, mobile ≤ 2,5 MB.

## Target State
1. Hero memakai sequence v2. Komponen scroll-scrub, rumus progress, dan fallback tidak berubah.
2. Section baru "Konten yang dijual, bukan sekadar ditonton" tepat setelah section Enam layanan: 1 mockup HP di mobile, 3 mockup berjajar di desktop.
   - Bingkai HP generik dari CSS (sudut membulat, notch sederhana), bukan tiruan merek HP tertentu.
   - Video: muted, loop, playsinline, preload="none", poster wajib. Diputar hanya saat terlihat, berhenti saat keluar layar. Reduced motion atau saveData → poster saja.
   - Setiap mockup bisa diberi label kecil (kategori, dari field) dan link ke /jasa-konten/.
   - Di bawah section ada keterangan kecil dari field (default: "Ilustrasi format konten, dibuat dengan AI"), yang bisa dikosongkan saat video asli klien sudah dipakai.
3. Field baru di opsi Homepage: daftar video (media ID video, media ID poster, label, link). Section disembunyikan bila daftar kosong.
4. Ukur ulang total byte homepage desktop dan mobile, laporkan di PR.

## Constraints
- Satu video maksimal 1,5 MB (720p, 10–15 detik, H.264 + WebM). Kalau lebih besar, stop dan beri tahu saya. Jangan encode ulang tanpa izin.
- Only make changes directly requested. Do not add features, abstractions, or files beyond what was asked.

## Acceptance Criteria
- [ ] Hero v2 tampil benar di 1366 px dan 390 px, logo tajam, tidak ada teks label.
- [ ] Video tidak diunduh sebelum section mendekati layar (cek tab Network).
- [ ] Budget total tetap lolos. LCP homepage mobile < 2,5 dtk.

## Stop Conditions
Stop and ask before menyentuh situs live, menghapus file, atau menambah dependency.
```

---

## Prompt D — 3 video konten AI 10 detik untuk mockup HP (fashion, beauty, home living)

Pakai **Grok Imagine** (mode Normal, bukan Spicy) atau **Symphony Creative Studio**. Satu generate per kategori. Audio tidak dipakai karena video diputar tanpa suara di website.

Aturan untuk ketiganya:
- Rasio 9:16, 10 detik, 720p, tanpa teks, tanpa logo, tanpa merek produk yang dikenal, tanpa anak.
- Wajah model generik buatan AI, bukan figur publik, klien, atau karyawan.
- Tidak menampilkan angka, dashboard, testimoni, atau chat pembeli (aturan "nol bukti palsu").
- Di website, section ini diberi label kecil **"Ilustrasi format konten, dibuat dengan AI"**, supaya calon klien tidak mengira ini hasil kerja untuk klien tertentu. Setelah ada video asli klien dengan izin, ganti video AI ini.

### D1 — Fashion
| Detik | Shot |
|---|---|
| 0–2 | Close-up tangan merapikan kerah kemeja linen krem, cahaya pagi dari jendela |
| 2–5 | Model berputar pelan di depan cermin ruang ganti minimalis, kain bergerak lembut |
| 5–8 | Detail tekstur kain dan kancing, kamera bergerak pelan (slow push-in) |
| 8–10 | Model tersenyum ke kamera sambil memegang tas belanja kertas polos |

```text
Vertical 9:16 smartphone-style fashion video, 10 seconds, 720p, natural morning window light, warm neutral palette (cream, sand, soft white). Shot 1 (0–2s): close-up of hands adjusting the collar of a cream linen shirt. Shot 2 (2–5s): a young Indonesian woman in a modest, elegant outfit turns slowly in front of a mirror in a minimalist fitting room, fabric moving softly. Shot 3 (5–8s): slow push-in on fabric texture and buttons. Shot 4 (8–10s): she smiles at the camera holding a plain unbranded paper shopping bag. Handheld but steady, shallow depth of field, clean modern look, realistic skin. No text, no logos, no brand names, no watermarks, no children.
```

### D2 — Beauty
| Detik | Shot |
|---|---|
| 0–2 | Botol serum kaca bening polos di atas meja marmer, tetesan jatuh dari pipet |
| 2–5 | Tekstur serum di punggung tangan, cahaya lembut |
| 5–8 | Model mengaplikasikan ke pipi, kulit tampak sehat (bukan efek ajaib) |
| 8–10 | Flat-lay produk polos dengan daun hijau dan handuk putih |

```text
Vertical 9:16 skincare video, 10 seconds, 720p, soft diffused daylight, palette of white marble, pale peach, and green leaves. Shot 1 (0–2s): a plain clear glass serum bottle with no label on a marble counter, a single drop falling from the dropper in slow motion. Shot 2 (2–5s): macro of the serum texture spread on the back of a hand. Shot 3 (5–8s): a young Indonesian woman gently applies it to her cheek, natural healthy skin, no dramatic before-after effect. Shot 4 (8–10s): top-down flat lay of unbranded bottles with green leaves and a folded white towel. Calm, premium, realistic. No text, no logos, no brand names, no medical claims, no children.
```

### D3 — Home living
| Detik | Shot |
|---|---|
| 0–2 | Tangan meletakkan vas keramik polos di rak kayu |
| 2–5 | Selimut rajut dilipat di sofa, cahaya sore hangat |
| 5–8 | Kamera menyapu pelan ruang tamu rapi dengan tanaman |
| 8–10 | Lilin aromaterapi polos dinyalakan, nyala kecil yang tenang |

```text
Vertical 9:16 home living video, 10 seconds, 720p, warm late-afternoon light, palette of oak wood, linen white, terracotta, and soft green. Shot 1 (0–2s): hands placing a plain matte ceramic vase on a wooden shelf. Shot 2 (2–5s): a knitted throw blanket being folded on a linen sofa. Shot 3 (5–8s): slow gentle pan across a tidy, cozy living room with indoor plants. Shot 4 (8–10s): a plain unbranded aromatherapy candle being lit, small calm flame. Cozy, premium, realistic, steady camera. No text, no logos, no brand names, no people's faces, no children.
```

Setelah render: kompres masing-masing ke ≤ 1,5 MB (H.264 + WebM), ambil frame terbaik sebagai poster JPG, lalu taruh di `motion-src/konten/` dengan nama `fashion`, `beauty`, `home-living`. Upload juga ke Media Library supaya bisa dipilih di opsi Homepage.
