# DECISIONS · wp-meagency (tema mea-orbit + plugin mea-leads)

Catatan keputusan untuk situs meagency.co.id. **Setiap perubahan tema atau plugin berikutnya
wajib menambah satu baris di bagian 3** (tanggal, keputusan, alasan, file terdampak). Rincian
teknis tiap fase tetap di `docs/PLAN.md`; dokumen ini adalah daftar ringkas yang mengikat.

## 1. Keputusan dasar

| Tanggal | Keputusan | Alasan | Sumber |
|---|---|---|---|
| 2026-10-25 | **Tema mandiri** `mea-orbit`, bukan child theme Hello Elementor. Situs meninggalkan page builder Elementor sepenuhnya, dalam dua langkah: ganti tema dulu, konversi konten belakangan (Fase 6) | Header/footer satu kode di setiap URL, risiko crawl Google paling kecil, tidak terikat lisensi builder | `docs/PLAN.md` rev 3, `docs/ELEMENTOR-EXIT.md` |
| 2026-10-25 | Brief menang bila bertentangan dengan prototipe atau dokumen motion | Satu sumber kebenaran | `docs/mea-orbit/BRIEF-CLAUDE-CODE.md` |
| 2026-10-25 | Budget motion homepage: 0 byte sebelum `load`, ≤ 1,5 MB sebelum hero terlewati, total desktop ≤ 5 MB, mobile ≤ 2,5 MB, aset tidak di-encode ulang tanpa izin | Performa dan Core Web Vitals | `docs/PLAN.md` bagian 2 dan 9 |
| 2026-10-25 | Lead form disimpan lokal dulu, lalu dikirim ke CDPS dengan HMAC (kontrak `mea.website_lead.v1`). WordPress hanya memegang HMAC secret, tidak pernah service-role key | Lead tidak hilang saat CDPS mati; kredensial database tidak di WordPress | `docs/WEBSITE_LEAD_CONTRACT.md` |
| 2026-10-27 | **Aturan "nol bukti palsu" untuk konten AI**: video atau gambar buatan AI tidak boleh menampilkan angka, dashboard, testimoni, chat pembeli, logo/merek nyata selain logo platform resmi, atau orang nyata. Setiap konten AI di situs diberi keterangan bahwa itu ilustrasi buatan AI, dan diganti dengan materi asli klien (berizin) begitu tersedia | Calon klien tidak boleh mengira ilustrasi sebagai hasil kerja untuk klien tertentu | `docs/prompts/PROMPT-tracking-hero-v2.md` Prompt D |

## 2. Keputusan brief bagian 10 (27 Okt 2026)

| Tanggal | Keputusan | Diterapkan di |
|---|---|---|
| 2026-10-27 | Nama badan hukum di situs **"PT MEA Digital Marketing"** (sebelumnya tertulis PT MEA Agensi Digital): footer, JSON-LD `Organization.legalName`, halaman kebijakan, teks tema dan plugin. Nama brand tampil tetap "MEA Agency" | Rilis 0.2.0 |
| 2026-10-27 | Kubus hero memakai **logo platform permanen**. Badge dan sertifikat per kuartal **tidak** masuk animasi | Rilis 0.2.0 (paket hero v2: Shopee, TikTok, Tokopedia, Meta + dua sisi video layanan MEA, lihat bagian 3) |
| 2026-10-27 | **Strip "Partner resmi"** tepat di bawah hero: badge Shopee-Certified Enablers Program dan foto plakat TikTok Shop Platinum Certified Partner, gambar biasa dari Media Library, diganti tiap kuartal lewat opsi Homepage | Rilis 0.2.0 |
| 2026-10-27 | **Tracking dari tema** (GTM + event Lead server ke Meta dan TikTok), tanpa plugin tracking | Rilis 0.2.0, `docs/tracking/` |
| 2026-10-27 | Struktur layanan induk-anak: `/jasa-performance-ads/` induk iklan, `/jasa-iklan-shopee/` dan `/jasa-tiktok-ads/` anak; `/jasa-live-streaming/` induk, `/jasa-live-streaming-tiktok/` anak. Hubungan lewat section "Layanan terkait"; **URL tidak diubah** | Tema 0.1.1 (section), isian di wp-admin |

## 3. Log perubahan rilis

| Tanggal | Keputusan | Alasan | File terdampak |
|---|---|---|---|
| 2026-10-27 | Menu Leads MEA bisa dibuka Editor (`edit_others_posts`, filter `mea_leads_capability`) | Permintaan pemilik | `mea-leads/includes/admin.php` (0.1.1) |
| 2026-10-27 | Section opsional "Layanan terkait" di template layanan (repeater label + page_id, setelah FAQ, tersembunyi bila kosong) | Struktur induk-anak layanan | `mea-orbit/inc/fields.php`, `template-parts/layanan/terkait.php`, `page-templates/template-layanan.php` (0.1.1) |
| 2026-10-27 | Tracking dari tema: tema mencetak GTM saja; event Lead server ke Meta CAPI (Graph `v24.0`, filter `mea_orbit_meta_api_version`) dan TikTok Events API v1.3 lewat antrian WP-Cron; `event_id` = `idempotency_key` lead, sama dengan dataLayer; semua ID/token dari konstanta `MEA_*`; tanpa tracking di wp-admin, login, preview, Customizer, embed, feed | Keputusan bagian 10; token tidak lagi tersimpan di snippet | `mea-orbit/inc/tracking*.php`, `mea-leads/includes/form.php`, `rest.php`, `assets/js/form.js`, `docs/tracking/` |
| 2026-10-27 | Nama event TikTok `Lead` (bukan `SubmitForm`); GA4 di GTM tidak menghitung pengguna login (`logged_in` di dataLayer) | Deduplikasi butuh nama event sama di browser dan server; perilaku Site Kit dipertahankan | `docs/tracking/GTM-SETUP.md`, `mea-orbit/inc/tracking.php` |
| 2026-10-28 | Nama badan hukum diganti ke "PT MEA Digital Marketing" | Brief bagian 10 | `mea-orbit/style.css`, `footer.php`, `inc/seo.php`, `mea-leads/mea-leads.php`, `docs/mea-orbit/*` |
| 2026-10-28 | Hero memakai paket kubus v2 (`motion-src/hero-v2/`), nama file sama. Rumus progress mengikuti README v2: `scrollY / (heroTop + 0,6 × tinggi hero)`. Alt kubus menyebut keempat platform dan bahwa dua sisi video adalah ilustrasi buatan AI | Brief bagian 10, paket hero v2 | `mea-orbit/assets/motion/hero/**`, `assets/js/motion.js`, `template-parts/home/hero.php` |
| 2026-10-28 | Hero desktop memuat tier progresif sampai **tiap frame keempat** (31 frame, 0,82 MB + poster 0,05 MB), mobile tetap semua 60 frame (1,09 MB + poster) | Frame v2 lebih berat (desktop 3,16 MB penuh, 1,61 MB pada tiap frame kedua). Tiap frame kedua membuat "sebelum hero terlewati" 1,66 MB, di atas batas 1,5 MB; melonggarkan batas butuh izin pemilik. Bila diizinkan, cukup ubah `stride: mobile ? 1 : 4` menjadi `2` di `motion.js` | `mea-orbit/assets/js/motion.js` |
| 2026-10-28 | Strip "Partner resmi" di bawah hero dari opsi Homepage `partner_resmi` (repeater media, alt, label, periode, link). Gambar berlatar transparan (dibaca dari header PNG/WebP) ditampilkan apa adanya; gambar tanpa transparansi (foto plakat) dalam kartu berbingkai tipis; tinggi tetap, lebar dari rasio asli, tanpa potong atau filter. **Default kosong**: section tidak tampil sampai gambar diunggah dan diisi di opsi Homepage | Brief bagian 10; tidak ada aset yang boleh dibuat atau diedit ulang | `mea-orbit/inc/options.php`, `front-page.php`, `template-parts/home/partner.php`, `assets/css/orbit.css` |
| 2026-10-28 | Mockup HP "Konten" (Prompt C) **ditunda**: `motion-src/konten/` kosong | Section hanya dibuat bila video ada | — |
| 2026-10-28 | Lead berstatus menunggu atau gagal bisa dihapus dari Leads MEA (aksi baris + massal, dengan konfirmasi). Lead terkirim dan lead yang sedang dikirim tidak bisa dihapus; jadwal kirimnya ikut dibatalkan | Lead uji harus bisa dibersihkan sebelum konstanta CDPS diisi | `mea-leads/includes/db.php`, `admin.php`, `class-mea-leads-list-table.php` (0.2.0) |
| 2026-10-28 | Badge Shopee (Engagement Only, Q3 2026) dan foto plakat TikTok Shop Platinum Certified Partner Q3 2026 disimpan sebagai WebP di `motion-src/partner/`: badge 1200×1020 dengan alpha (113 KB), plakat utuh 900×1600 (78 KB, dari HEIC iPhone 1,6 MB; metadata lokasi foto tidak ikut). Tidak dipotong, tidak diberi efek. Teks di badge ("MEA Agensi Digital") adalah bagian gambar resmi Shopee dan tidak diubah | Ukuran unggah lebih kecil; aturan brief: badge dan foto partner tidak diedit | `motion-src/partner/*.webp`, `mea-orbit/inc/orbit.php` (`mea_orbit_image_has_alpha`), `template-parts/home/partner.php` |

