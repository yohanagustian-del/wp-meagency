# QA mea-orbit 0.3.0 · lokal (29 Okt 2026)

Lingkungan: harness PHP 8.3 yang merender `template-parts/home/layanan.php`,
`template-parts/home/cara-kerja.php`, dan `template-parts/layanan/cara-kerja.php` (7 langkah)
dengan stub fungsi WordPress, memuat `base.css`, `orbit.css`, dan `motion.js` tema apa adanya.
Diuji dengan Chromium (Playwright) di 1280×800, 800×1000, dan 390×844 (touch, DPR 2).
Belum diuji di staging/produksi WordPress: butir 1, 3, 5, 7 wajib dicek ulang setelah deploy
(lihat `dist/DEPLOY-0.3.0.md`).

| # | Cek | Hasil |
|---|---|---|
| 1 | Kartu Enam sisi | Lulus. 3 kolom (1280), 2 kolom (800), 1 kolom (390). Semua foto 4:3 (320×240 di desktop). Layout shift dari section ini 0 (satu-satunya shift 0,0045 berasal dari tukar font judul di harness tanpa preload font) |
| 2 | Hover kartu | Lulus. Desktop `matrix(1.03…)` saat hover, kembali `none`. HP (tap): `none`. Reduced motion: `none`, transisi 0 s |
| 3 | Cara kerja homepage | Lulus. Langkah aktif 0→4 dan foto aktif 0→4 selalu sama, tepat satu foto aktif; transisi `opacity .6s`. Tanpa JS foto langkah 1 tampil |
| 4 | Reduced motion | Lulus. Foto tetap berganti 0→4, `transition-duration: 0s` |
| 5 | HP ≤767 px | Lulus. Kotak sticky 300×300, tengah (left 45 px di 390), radius 12 px, memakai `langkah-N-800.webp`; langkah aktif berada di bawah kotak, tidak tertutup |
| 6 | Halaman layanan (7 langkah) | Lulus. Foto langkah-1…5, lalu langkah-1, langkah-2; `alt=""`; tanpa error PHP. Slug kartu yang tidak ada di daftar putih → kartu tanpa foto, tanpa error |
| 7 | Network | Lulus. 0 request ke `/assets/motion/sisi/` dan `/assets/motion/proses/`; tidak ada 404 |
| 8 | Hero, Bellarosa | Lulus (markup hero dan figure Bellarosa disalin ke harness): hero memuat 31 frame desktop / 60 frame HP dan `is-live`; video Bellarosa dimuat dan diputar. Mockup Konten tidak diuji di harness (butuh Media Library); kodenya tidak berubah |
| 9 | Console | Lulus. 0 error di desktop, HP, dan reduced motion |
| 10 | Kode | Lulus. `php -l` bersih untuk 6 file PHP yang diubah, `node --check motion.js` bersih; semua output lewat `esc_url`/`esc_attr`/`esc_html` |

## Perkiraan bobot dua section (byte aset tema)

| | Sebelum (0.2.1) | Sesudah (0.3.0) |
|---|---|---|
| Desktop | Enam sisi 27 KB poster + 193 KB WebM; Cara kerja 12 KB poster + 2.009 KB frame → **±2,24 MB** | Kartu 121 KB (600w, DPR 1) s.d. 294 KB (1200w, DPR 2); Cara kerja 294 KB → **±0,42–0,59 MB** |
| HP | Enam sisi 27 KB + 193 KB; Cara kerja 9 KB + 389 KB (tiap frame kedua) → **±0,62 MB** | Kartu 294 KB (1200w, DPR 2); Cara kerja 198 KB → **±0,49 MB** |

Ukuran total halaman homepage sebelum vs sesudah di situs live diukur setelah deploy.

Screenshot: `1-svc-*.jpg`, `3-proses-desktop-langkah3.jpg`, `3-proses-nojs.jpg`, `5-proses-phone.jpg`.
