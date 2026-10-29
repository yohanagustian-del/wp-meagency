# CHANGELOG

Keputusan di balik setiap perubahan: `docs/DECISIONS.md`.

## mea-orbit 0.5.1 · 29 Okt 2026
- Galeri studi kasus mendukung **video** dari Media Library (kontrol putar, tanpa autoplay, frame pertama sebagai pratinjau). Video SC18 Bellarosa tampil di Galeri.
- Panel Hasil studi kasus: jumlah kolom angka mengikuti jumlah angka (maks. 4; tablet 2; HP 1). Bellarosa kini 4 angka, termasuk **1.200% kenaikan traffic organik TikTok** (data TikTok, dikembalikan dari halaman Elementor lama).

## mea-orbit 0.5.0 · 29 Okt 2026
- **Studi kasus keluar dari Elementor**: section **Galeri** baru (field `galeri`: foto, keterangan, alt) setelah cerita, untuk foto yang dulu hanya ada di grid Elementor. Tersembunyi bila kosong. Bellarosa (14512): mode builder Elementor dimatikan; 12 foto lama siap diisi ke Galeri (`docs/seed/page-14512-bellarosa.json`).
- Termasuk semua isi 0.3.0 dan 0.4.0. mea-leads tetap 0.2.0.

## mea-orbit 0.4.0 · 29 Okt 2026
- **Noindex otomatis untuk konten berpassword** (`inc/robots.php`): semua post/page dengan password mendapat `noindex, nofollow` (filter `rank_math/frontend/robots`) dan dikeluarkan dari sitemap Rank Math (filter `rank_math/sitemap/entry`). Halaman tanpa password tidak berubah.
- **Navigasi 4 pilar layanan**: header berisi E-commerce Service, Performance Ads, Social Media Management, SEO, dengan dropdown anak layanan (hover dan keyboard lewat `:focus-within`, tanpa JavaScript). Menu HP menampilkan anak terindentasi. Header pindah ke menu HP di ≤1080 px.
- **Footer**: kolom Layanan berisi struktur 4 pilar yang sama (satu sumber, `mea_orbit_pilar()`); menu WordPress lokasi footer (sekarang "Single Menu") pindah ke kolom baru **Perusahaan**; Hubungi digabung di bawah Kantor.
- **Homepage, Enam sisi**: baris "Juga tersedia: Social Media Management · SEO". Default kartu Shopee → `/jasa-management-marketplace/`, deskripsi Iklan menyebut Meta Ads (isian live diubah lewat opsi Homepage).
- mea-leads tetap 0.2.0.

## mea-orbit 0.3.0 · 29 Okt 2026
- **Foto realis menggantikan motion di dua section** (disetujui Yohan, brief `paket-mea-orbit-0.3.0`):
  - Kartu "Enam sisi operasional, satu tim": foto 4:3 selebar kartu (`assets/foto/layanan/`, 600w + 1200w), statis, zoom 3% saat hover di desktop. Loop video M2 dihapus.
  - "Cara kami bekerja" (homepage dan semua halaman layanan): lima foto 1:1 bertumpuk (`assets/foto/proses/`) yang berganti dengan crossfade saat langkah aktif pindah; tanpa fade saat reduced motion / Hemat Data. Scroll-scrub 150 frame M3 dihapus. Homepage memakai alt per langkah; halaman layanan `alt=""`, foto berulang bila lebih dari lima langkah.
- Opsi Homepage: label field kartu layanan "Loop Enam Sisi" → **"Foto layanan"**. Nilai tersimpan tidak berubah (slug sama).
- Aset `assets/motion/sisi/` dan `assets/motion/proses/` dihapus dari tema (tetap di riwayat git). Zip tema turun dari 10,7 MB ke 8,0 MB.
- Hero kubus, mockup HP Konten, dan kurva Bellarosa tidak berubah.
- mea-leads tetap 0.2.0.

## mea-orbit 0.2.1 · 29 Okt 2026
- **Perbaikan cache motion**: semua URL aset motion (poster, frame hero dan Cara kerja, video Enam sisi dan Bellarosa) membawa versi tema (`?v=0.2.1`). Setelah 0.2.0 dipasang, pengunjung lama masih melihat kubus hero versi lama, karena nama berkas hero v2 sama dengan v1 sehingga browser/CDN menyajikan salinan cache.
- mea-leads tetap 0.2.0.

## mea-orbit 0.2.0 + mea-leads 0.2.0 · 28 Okt 2026

### Tema mea-orbit 0.2.0
- **Hero kubus v2**: empat logo platform (Shopee, TikTok, Tokopedia, Meta) dan dua sisi video layanan MEA (ulasan kreator dan live shopping, ilustrasi buatan AI). Poster frame 0 tetap gambar LCP; desktop memuat sampai tiap frame keempat, mobile semua 60 frame. Rumus progress dari README paket v2. Alt text baru.
- **Strip "Partner resmi"** di bawah hero, diisi lewat Tampilan → Homepage MEA → Partner resmi. Tersembunyi selama daftar kosong. Badge Shopee dan plakat TikTok Shop (WebP) tersedia di paket.
- **Mockup HP "Konten"** setelah Enam layanan: tiga video ilustrasi AI (fashion, skincare, live shopping) yang diputar saat terlihat, dengan keterangan "Ilustrasi format konten, dibuat dengan AI". HP menampilkan satu mockup; Cara kerja di HP memuat tiap frame kedua supaya total tetap ≤ 2,5 MB.
- **Tracking dari tema**: snippet GTM (`MEA_GTM_ID`), event Lead server ke Meta Conversions API dan TikTok Events API dengan `event_id` sama dengan dataLayer, halaman Pengaturan → Tracking MEA. Diam tanpa konstanta. Panduan: `docs/tracking/GTM-SETUP.md`, `docs/tracking/MIGRASI.md`.
- Nama badan hukum di footer, JSON-LD, dan header tema: **PT MEA Digital Marketing**.
- Termasuk 0.1.1: section opsional "Layanan terkait" di template layanan.

### Plugin mea-leads 0.2.0
- **Hapus lead** berstatus menunggu atau gagal dari Leads MEA (aksi baris dan aksi massal, dengan konfirmasi). Lead terkirim tidak bisa dihapus.
- Hook `mea_leads_lead_saved` dan `event_id` di respons form dan dataLayer `generate_lead` (untuk tracking tema).
- Author: PT MEA Digital Marketing.
- Termasuk 0.1.1: menu Leads MEA bisa dibuka Editor.

### Perlu diisi setelah upload
- Partner resmi dan Mockup HP "Konten": unggah berkas dari paket ke Media Library, lalu isi di Tampilan → Homepage MEA (`DEPLOY-0.2.0.md` langkah 4).

## mea-orbit 0.1.1 + mea-leads 0.1.1 · 27 Okt 2026
- Tema: section "Layanan terkait".
- Plugin: menu Leads MEA untuk Editor.

## mea-orbit 0.1.0 + mea-leads 0.1.0 · 26 Okt 2026
- Rilis pertama di produksi (lihat `docs/PLAN.md` bagian 15–19).
