# 05 — Prompt Claude Code: integrasi motion ke tema mea-orbit

Jalankan **setelah** Fase 3 (tema `mea-orbit`) selesai di staging dan hasil M1–M4 sudah ada.
Sebelum menempel: taruh hasil M1–M4 di `wp-content/themes/mea-orbit/assets/motion/` dengan subfolder `hero/`, `sisi/`, `proses/`, `bellarosa/`, dan salin `00-peta-motion.md` ke `docs/mea-orbit/`.

```text
## Objective
Pasang semua motion MEA Agency ke tema WordPress mea-orbit di staging, sesuai docs/mea-orbit/00-peta-motion.md, tanpa menurunkan skor Core Web Vitals.

## Context
- Tema mea-orbit sudah berjalan di staging (front-page.php, template-layanan.php, template-studi-kasus.php).
- Aset pre-render ada di wp-content/themes/mea-orbit/assets/motion/: hero/ (sequence WebP desktop + mobile, poster, fallback video), sisi/ (6 loop WebM/MP4 + poster), proses/ (sequence + poster), bellarosa/ (video + poster).
- Peta letak motion: docs/mea-orbit/00-peta-motion.md bagian 3 (homepage) dan 4 (studi kasus). Baca seluruhnya sebelum mulai.
- Brand: teks penting selalu HTML; oranye #EC7832 → #F69541 hanya aksen.

## Target State
1. Komponen scroll-scrub tunggal (canvas + IntersectionObserver + requestAnimationFrame) dipakai untuk hero (H1) dan Cara kerja (H6). Poster frame 0 tampil dulu sebagai img fetchpriority="high"; sequence dimuat hanya setelah event load, progresif (frame 0, terakhir, tiap 16, 8, 4, 2, lalu sisanya), dan hanya saat section mendekati layar. Di bawah 768 px pakai sequence mobile (60 frame).
   Hero TIDAK di-pin (tidak ada scroll hijack). Progress hero = scrollY / (tinggi hero × 0,6), dibatasi 0–1, supaya susunan akhir enam tile (frame 119) sempat terlihat sebelum hero keluar layar. Reduced motion: tampilkan poster-last, bukan poster-first. Acuan: README.md dan demo/index.html di paket hero.
2. Loop Enam Sisi (H5): video muted playsinline, diputar saat hover (desktop) atau saat terlihat (mobile), berhenti saat keluar layar.
3. Kurva Bellarosa (H7): diputar sekali saat 60% terlihat, lalu tahan frame akhir.
4. Motion kode (H2, H3, H4, H8, S1–S5): hitung naik, reveal, stagger, garis tergambar, dan Panel Hasil studi kasus yang membaca angka dari field CMS (berlaku untuk semua studi kasus, tidak hardcode Bellarosa).
5. prefers-reduced-motion, navigator.connection.saveData, dan koneksi 2g/3g → hanya poster, tanpa memuat sequence/video.
6. Satu modul JS motion kecil (tanpa library animasi tambahan), dimuat dengan defer, hanya di halaman yang memakainya.

## Scope
- Kerja hanya di: wp-content/themes/mea-orbit/ (template-parts, assets/js, assets/css, functions.php bagian enqueue).
- JANGAN sentuh: plugin mea-leads, plugin lain, database, halaman Elementor lama, produksi.

## Constraints
- Only make changes directly requested. Do not add features, abstractions, or files beyond what was asked.
- Tidak ada dependency baru tanpa izin. three.js tidak dimuat sama sekali bila hero memakai sequence.
- Tidak ada CLS: setiap kanvas/video punya aspect-ratio tetap sebelum aset dimuat.

## Acceptance Criteria
- [ ] PageSpeed mobile homepage staging: LCP < 2,5 dtk, CLS < 0,1, INP < 200 ms.
- [ ] Total motion yang dimuat di homepage ≤ 4 MB, dan 0 byte motion sebelum event load.
- [ ] Dengan reduced motion aktif: semua section menampilkan poster, tidak ada video/sequence yang diunduh (cek tab Network).
- [ ] Scroll hero dan Cara kerja mulus di iPhone Safari dan Android Chrome (tanpa patah frame yang terlihat).
- [ ] H1, angka, dan CTA tetap terbaca dan bisa diklik tanpa JavaScript.

## Stop Conditions
Stop and ask before:
- Menghapus file apa pun.
- Menambah dependency.
- Menyentuh database atau plugin.
- Deploy ke produksi.
- Mengubah copy atau struktur section di luar pemasangan motion.

## Progress
Setelah tiap langkah: ✅ [yang dikerjakan] — [file]
```
