# CHANGELOG

Keputusan di balik setiap perubahan: `docs/DECISIONS.md`.

## mea-orbit 0.2.0 + mea-leads 0.2.0 · 28 Okt 2026

### Tema mea-orbit 0.2.0
- **Hero kubus v2**: empat logo platform (Shopee, TikTok, Tokopedia, Meta) dan dua sisi video layanan MEA (ulasan kreator dan live shopping, ilustrasi buatan AI). Poster frame 0 tetap gambar LCP; desktop memuat sampai tiap frame keempat, mobile semua 60 frame. Rumus progress dari README paket v2. Alt text baru.
- **Strip "Partner resmi"** di bawah hero, diisi lewat Tampilan → Homepage MEA → Partner resmi. Tersembunyi selama daftar kosong.
- **Tracking dari tema**: snippet GTM (`MEA_GTM_ID`), event Lead server ke Meta Conversions API dan TikTok Events API dengan `event_id` sama dengan dataLayer, halaman Pengaturan → Tracking MEA. Diam tanpa konstanta. Panduan: `docs/tracking/GTM-SETUP.md`, `docs/tracking/MIGRASI.md`.
- Nama badan hukum di footer, JSON-LD, dan header tema: **PT MEA Digital Marketing**.
- Termasuk 0.1.1: section opsional "Layanan terkait" di template layanan.

### Plugin mea-leads 0.2.0
- **Hapus lead** berstatus menunggu atau gagal dari Leads MEA (aksi baris dan aksi massal, dengan konfirmasi). Lead terkirim tidak bisa dihapus.
- Hook `mea_leads_lead_saved` dan `event_id` di respons form dan dataLayer `generate_lead` (untuk tracking tema).
- Author: PT MEA Digital Marketing.
- Termasuk 0.1.1: menu Leads MEA bisa dibuka Editor.

### Tidak termasuk
- Mockup HP "Konten" (Prompt C): ditunda sampai `motion-src/konten/` berisi video.
- Gambar badge Shopee dan plakat TikTok Shop: belum ada; diunggah tim lalu diisi di opsi Homepage.

## mea-orbit 0.1.1 + mea-leads 0.1.1 · 27 Okt 2026
- Tema: section "Layanan terkait".
- Plugin: menu Leads MEA untuk Editor.

## mea-orbit 0.1.0 + mea-leads 0.1.0 · 26 Okt 2026
- Rilis pertama di produksi (lihat `docs/PLAN.md` bagian 15–19).
