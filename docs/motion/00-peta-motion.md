# Paket Motion MEA Agency — Peta, Konsep, dan Urutan

Situs: meagency.co.id · Tema: `mea-orbit` · Acuan layout: `mea-orbit-prototype/` (index.html, layanan.html, studi-kasus.html)
Brand terkunci: oranye `#EC7832 → #F69541` (bidang & aksen saja), teks `#1A1A1A`, netral `#46474B #E4E4E7 #FAFAFA`, Instrument Serif (judul) + Inter (badan). Kepadatan rendah, dekorasi mendekati nol: motion harus **menjelaskan**, bukan menghias.

---

## 1. Dibuat di mana

| Jenis motion | Dibuat di | Alasan |
|---|---|---|
| Ilustrasi pre-render (scroll-scrub sequence, loop video pendek) | **Claude chat "MEA Agency Motion Lab"**, satu chat baru per prompt (pola Ordal & MCN MEA) | Iterasi visual cepat, render Playwright + ffmpeg, hasil bisa dicek di HP sebelum dipasang |
| Motion yang bergantung data atau interaksi (hitung angka, reveal, hover, angka studi kasus per klien, transisi) | **Claude Code** di tema `mea-orbit` | Harus membaca field CMS, ringan, dan ikut aturan aksesibilitas |
| Pemasangan semua aset ke tema + uji performa | **Claude Code** (prompt `05`) | Satu tempat untuk budget, lazy-load, fallback |

Aturan umum semua motion:
- Teks penting (H1, angka, CTA) selalu HTML, tidak pernah dibakar ke dalam video/frame.
- `prefers-reduced-motion` → poster statis. Hemat data / koneksi lambat → poster statis.
- Video loop: tanpa audio, `muted playsinline`, diputar hanya saat terlihat, berhenti saat keluar layar.
- Tidak ada logo platform (TikTok, Shopee) di dalam motion. Cukup label teks.
- Dilarang: partikel meledak, glow, lens flare, goyangan kamera, RGB split, konfeti, ikon uang/koin, easing memantul.

---

## 2. Tiga alternatif konsep motion

| | A. Orbit Tenang (**rekomendasi**) | B. Kubus Terbuka | C. Garis Pertumbuhan |
|---|---|---|---|
| Inti | Lintasan orbit tipis oranye, gerak lambat, angka sebagai bintang utama | Kubus enam layanan jadi objek utama yang terbuka sepanjang scroll, seperti product reveal | Satu garis oranye kontinu yang "menggambar" dirinya dari hero sampai form |
| Kesan | Premium, kalem, berbukti | Paling dramatis, paling "wah" | Paling editorial dan paling ringan |
| Beban | Sedang | Paling berat (sequence panjang) | Paling ringan (SVG, dibuat Claude Code) |
| Risiko | Rendah | Bisa terasa gimmick kalau berlebihan | Bisa terasa terlalu sepi |
| Cocok dengan guideline | Paling cocok | Perlu dijaga ketat | Cocok |

Rekomendasi: **A sebagai bahasa utama**, dengan satu momen **B** hanya di hero. Paket prompt di bawah disusun untuk kombinasi ini. Kalau memilih C, prompt 01–04 tidak dipakai dan semuanya dikerjakan Claude Code.

---

## 3. Draft homepage — letak motion

| # | Section (prototipe) | Motion | Kode | Dibuat di | Alternatif |
|---|---|---|---|---|---|
| H1 | Hero: H1, lede, 2 CTA, visual kanan | **Kubus Terbuka** scroll-scrub: kubus tertutup → berputar → enam sisi terlepas jadi enam tile layanan di sekitar inti oranye | M1 | Motion Lab | (a) Three.js real-time (sudah ada di prototipe) · (b) loop 6 dtk autoplay tanpa scroll |
| H2 | Angka bukti (Rp 8,2 miliar, 21x, 75%) | Hitung naik sekali saat terlihat + garis orbit tipis tergambar di bawah angka | — | Claude Code | Angka statis, hanya fade |
| H3 | Blok pimpinan (foto Yohan & Nerissa) + kutipan 1 | **Tanpa motion besar.** Foto reveal halus (mask naik 12 px, 600 ms), kutipan muncul per baris | — | Claude Code | Parallax foto 4% (hanya desktop) |
| H4 | Mosaik foto tim | Reveal berurutan per foto (stagger 80 ms) | — | Claude Code | Statis |
| H5 | Enam layanan | **Enam Sisi**: loop mini 3 dtk per layanan, diputar saat hover (desktop) atau saat masuk layar (mobile) | M2 | Motion Lab | Ikon garis statis + tilt saat hover |
| H6 | Cara kerja (5 langkah) + kutipan 5 | **Orbit Proses** scroll-scrub sticky: titik oranye berpindah stasiun Riset → Strategi → Rencana → Eksekusi → Rekap | M3 | Motion Lab | (a) loop 8 dtk · (b) SVG garis tergambar (Claude Code) |
| H7 | Studi kasus unggulan Bellarosa | **Kurva Bellarosa**: garis naik ke Rp 8,2 miliar, cincin terbelah 75/25, lalu ROAS 21x | M4 | Motion Lab | Angka statis + foto |
| H8 | Form konsultasi | Tanpa motion. Hanya status terima kasih muncul halus | — | Claude Code | — |

## 4. Draft studi kasus — letak motion

| # | Section | Motion | Dibuat di | Alternatif |
|---|---|---|---|---|
| S1 | Header (nama brand, meta) | Judul muncul per baris, garis meta tergambar kiri ke kanan | Claude Code | Statis |
| S2 | Pita hasil (angka + bar 75:25) | **Panel Hasil** berbasis data: angka hitung naik, bar komposisi mengisi dari kiri, semua dari field CMS supaya berlaku untuk semua studi kasus | Claude Code | Klip M4 khusus per klien (hanya untuk studi kasus unggulan) |
| S3 | Cerita (Tantangan / Yang kami kerjakan / Hasil) | Foto samping sticky; penanda bab aktif di samping teks | Claude Code | Statis |
| S4 | Kutipan 2 | Garis oranye kiri tergambar, kutipan muncul | Claude Code | Statis |
| S5 | Studi kasus lain | Hover: garis bawah oranye bergerak, nama bergeser 4 px | Claude Code | — |

Template layanan memakai ulang M3 (versi pendek) di "Cara kerja" dan M2 untuk ikon layanan terkait. Tidak perlu motion baru.

---

## 5. Urutan pembuatan

| Urutan | Prompt | Tempat | Butuh |
|---|---|---|---|
| 1 | `01-hero-kubus-terbuka.md` | Chat baru | Logo MEA Agency (PNG/SVG). **Mengunci gaya** (tebal garis, warna sisi, gaya label) untuk semua motion berikutnya |
| 2 | `02-enam-sisi-loop.md` | Chat baru | Poster akhir M1 sebagai acuan gaya |
| 3 | `03-orbit-proses-scroll.md` | Chat baru | Poster akhir M1 |
| 4 | `04-kurva-bellarosa.md` | Chat baru | Poster akhir M1 |
| 5 | `05-claude-code-integrasi-motion.md` | Claude Code, setelah Fase 3 (tema) selesai | Semua folder hasil M1–M4 di `assets/motion/` |

Budget total motion homepage: ≤ 4 MB, dan **tidak ada yang dimuat sebelum konten utama tampil** (LCP tetap teks + poster).
