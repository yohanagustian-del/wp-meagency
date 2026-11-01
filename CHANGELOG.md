# CHANGELOG

Keputusan di balik setiap perubahan: `docs/DECISIONS.md`.

## mea-orbit 0.9.2 · 1 Nov 2026
- **Semua kontak jasa lewat form konsultasi** (permintaan Yohan): kalimat "Lebih suka langsung bicara? Hubungi tim sales di 0821-1793-2648" di atas form dihapus; footer "Hubungi" kini berisi link **Form konsultasi** (`mea_orbit_consult_url()`), bukan nomor WA; tombol "Chat tim sales" di kotak CTA artikel dihapus (tinggal "Jadwalkan konsultasi").
- Tetap: tombol WA di pesan terima kasih setelah form terkirim (plugin mea-leads) dan tombol WA cadangan bila plugin form nonaktif.
- Data live (konektor, tanpa kode): blok paket lama "Pak Encep dkk" di 4932 diganti CTA ke form; FAQ 287 tidak lagi menyebut nomor +62 812-8083-2339; label "via WhatsApp" di 30030/30483 dibuang; baris WhatsApp di 9289 dihapus. Rincian: `docs/seed/layanan/AUDIT-2026-11-01.md`.

## mea-orbit 0.9.1 · 1 Nov 2026
- **CTA lama di field isi → form konsultasi** (`mea_orbit_isi_cta_lama()`, dipanggil `mea_orbit_isi_html()` sebelum `wp_kses`): link ke `/klik/*`, `meagency.co.id/klik/*`, `sebari.co.id/form/*` (http/https, www, `//`) diganti `href="#konsultasi"`, atribut target/rel dibuang. Berlaku untuk semua field isi (layanan dan halaman daftar studi kasus).
- **Code Snippet #14 pindah ke tema** (`inc/cta-lama.php`): redirect 302 `/klik/*` → `/#konsultasi` dan penulisan ulang CTA lama di konten artikel, render Elementor, dan widget teks (±60 artikel masih memuat link lama). Setelah tema ini terpasang, snippet #14 boleh dimatikan tanpa mengubah perilaku situs.
- **Audit data live 1 Nov** (tanpa perubahan kode, lewat konektor): sisa ikon Elementor dibuang dari field isi (48 paragraf di 4 halaman), gambar eksternal kelas-work di 15567 dan 16523 diganti Media Library 35906, FAQ 15567 dipindah dari isi ke field FAQ (11 tanya-jawab, teks sama). Rincian: `docs/seed/layanan/AUDIT-2026-11-01.md`.
- Test: `php tests/isi-cta.php` (24).

## mea-orbit 0.9.0 · 31 Okt 2026
- **Redirect 301 lewat Rank Math** (`inc/redirects.php`): 8 redirect berbasis URL (10 sumber: `/mea-jasa-ads-tik-tok/`, `/jasa-traffic-iklan-dan-affiliate-mea/`, `/a-affiliate-mcn-mea-vk/`, `/content-service/`, `/jasa-optimasi-ads/`, `/jasa-marketing-e-commerce/`, `/home-page/`, `/homepage-mea/`, `/en/`, `/about-us-2-0/`) dibuat sebagai entri Rank Math > Redirections lewat `POST /wp-json/mea-orbit/v1/redirects`; sumber yang sudah ber-redirect dilewati. `GET` rute yang sama = laporan 10 sumber baru + 12 redirect objek lama. **URL yang cocok dengan redirect aktif keluar dari sitemap Rank Math.** Cek dari luar: `tools/redirect/cek-redirect.sh`.
- **Footer**: Live Streaming TikTok (anak E-commerce, khusus footer/hub), Layanan lain: Jasa Website, About Us selalu ada di Perusahaan, grup baru Ekosistem MEA (Gabung MCN MEA, Sebari). **Hub /services/**: section semua layanan dari sumber data yang sama.
- **Migrasi template**: 16 halaman layanan ke template Layanan dan `/study-case/` ke template Studi kasus (mode daftar). Isi lama dipindah apa adanya ke field baru `isi` (+ `label_hero`, H1, lede, FAQ); slug, judul, dan meta Rank Math tidak berubah. Seed + rollback: `docs/seed/layanan/`, konverter: `tools/elementor-ke-orbit/`.
- **Motion pilar tahap 1** (field `motion`): E-commerce = Kurva Bellarosa (M4) + angka hitung naik; Live = mockup HP memutar video live portofolio tim. Primitif `motion.js` dipakai ulang; reduced motion / hemat data / 2g-3g = poster. Peta pilar lain: `docs/motion/06-motion-pilar.md`.
- **Koreksi artikel Elementor** (`POST /wp-json/mea-orbit/v1/ganti-teks`): ganti teks persis di satu post (post_content + `_elementor_data`), uji dulu, cadangan otomatis. Payload 15889 dan 15879: `docs/konten/2026-10-31-artikel.json`.
- Test: `php tests/redirects.php` (23), `php tests/ganti-teks.php` (7), `python3 -I tests/konversi_test.py` (12).

## mea-orbit 0.8.0 · 31 Okt 2026
- **Laporan klien tanpa Elementor (Fase 1 keluar dari Elementor).** Laporan HTML (weekly, monthly, final report) dicetak tema apa adanya: satu halaman penuh tanpa header/footer situs, tanpa CSS/JS Elementor, `noindex, nofollow` (meta + header `X-Robots-Tag`), form password tema tetap muncul dulu bila berpassword.
  - **Laporan lama otomatis**: halaman Elementor yang isinya hanya widget HTML berisi dokumen utuh (`<!DOCTYPE html>`/`<html>`) **dan** berpassword, private, atau noindex di Rank Math. Isi dibaca dari `_elementor_data`, jadi tetap tampil walau Elementor nanti dinonaktifkan. Halaman terbuka dan terindeks (halaman pemasaran) tidak disentuh; laporan yang memakai widget lain (mis. monthly report dengan gambar tambahan) tetap lewat Elementor.
  - **Laporan baru**: template halaman **Laporan HTML** + kotak "Kode HTML laporan" (meta `mea_laporan_html`, juga lewat REST). Sumber: kotak itu → isi editor (tab Text) → widget HTML Elementor. Disimpan apa adanya hanya oleh pengguna `unfiltered_html` (admin/editor); selain itu disaring `wp_kses_post`. Halaman bertemplate ini keluar dari sitemap.
  - Editor Elementor (`?elementor-preview`) tetap memakai template biasa, jadi laporan lama masih bisa diedit dengan Elementor selama transisi.
- Panduan tim: `docs/LAPORAN-HTML.md`. Test: `php tests/laporan.php` (23 kasus).

## mea-orbit 0.7.1 · 30 Okt 2026
- **Update tema dari WordPress/konektor**: `inc/update.php` + header `Update URI`. WordPress membaca manifest `dist/mea-orbit-update.json` di branch utama repo; bila versinya lebih baru, "Update now" muncul (juga bisa dijalankan lewat konektor). Paket zip diverifikasi SHA-256 dari manifest; paket tidak cocok atau dari URL lain ditolak. Manifest dibuat dengan `tools/release-manifest.sh <commit>`.
- Termasuk semua isi 0.7.0.

## mea-orbit 0.7.0 · 30 Okt 2026
- **Homepage, section Konten: blok kedua "Hasil produksi tim kreatif MEA"** di bawah ilustrasi AI. Diisi di Tampilan → Homepage MEA (Konten · blok 2): judul, video (MP4 + poster, WebM opsional, label, link), keterangan (default "Diproduksi oleh tim kreatif MEA Agency"). Blok kosong tidak tampil; keterangan kosong tidak tampil. Desktop grid 3 kolom; HP semua video dalam baris yang bisa digeser (blok AI tetap 1 video di HP).
- Label opsi blok AI diperjelas ("Konten · blok 1"). Helper `mea_orbit_konten_clips()` dipakai kedua blok.
- Termasuk semua isi 0.6.0. mea-leads tetap 0.2.0.

## mea-orbit 0.6.0 · 29 Okt 2026
- **33 studi kasus pindah dari Elementor ke template Orbit** (data live; seed di `docs/seed/studi-kasus/`, rollback di `ROLLBACK.md` folder itu). Teks dan angka diambil dari halaman lama; bila counter dan teks Result bertentangan, angka teks yang dipakai. Denuber (isi salinan Allora) tidak dimigrasi, diberi noindex.
- **Foto cadangan samping cerita**: studi kasus tanpa foto kasus memakai lima foto realis proses kerja tim MEA (sama dengan "Cara kami bekerja"), berganti saat scroll, judul langkah sebagai keterangan. Sumber satu: `mea_orbit_proses_foto()` (juga dipakai homepage).
- **Kutipan jadi kutipan perusahaan**: lima kutipan (homepage, layanan, studi kasus) ditulis ulang dengan bahasa formal atas nama MEA Agency (atribusi "MEA Agency", bukan Yohan). Data live sudah diperbarui; default tema ikut.
- **Header**: link **Studi kasus** setelah empat pilar (aktif di halaman studi kasus). Label pilar tidak pecah baris; header pindah ke menu HP di ≤1180 px.

## mea-orbit 0.5.2 · 29 Okt 2026
- **Foto samping cerita studi kasus berganti saat scroll** (seperti "Cara kami bekerja" di homepage): dua foto atau lebih di "Foto proses kerja tim" menjadi tumpukan sticky dengan crossfade, foto aktif mengikuti bagian cerita yang sedang dibaca; tanpa fade saat reduced motion. HP: semua foto tampil sebagai grid 2 kolom. Satu foto: tetap statis seperti sebelumnya.
- Bellarosa: 4 foto samping (produk, konten kreator affiliate, produksi konten di studio, materi iklan).

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
