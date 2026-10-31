# Migrasi 16 halaman layanan + /study-case/ ke template Orbit (31 Okt 2026)

Seed di folder ini dibuat otomatis dari `_elementor_data` live (konektor `wp_get_post_full`,
context=edit) dengan `tools/elementor-ke-orbit/konversi.py`. Teks halaman dipindah apa adanya
ke field tema; **slug, judul, dan meta Rank Math tidak disentuh**.

| Field | Isi |
|---|---|
| `mea_h1` | H1 pertama halaman lama (teks sama persis). H1 kedua dan seterusnya jadi H2 di isi (dicatat di `catatan`) |
| `mea_label_hero` | Label kecil tepat di atas H1 di widget HTML yang sama (mis. "TIKTOK ADS PARTNER") |
| `mea_lede` | Paragraf tepat setelah H1, hanya bila H1 ada di puncak halaman |
| `mea_foto_hero_id` | Gambar widget Image sebelum H1 |
| `mea_isi` | Semua isi lain, urutan sama: judul, paragraf, daftar, link, tabel, gambar (`[mea-gambar ID]`). Style, class, script, dan kontainer Elementor dibuang |
| `mea_faq` | Accordion Elementor atau JSON-LD FAQPage di widget HTML. Salinan FAQ di isi dan judul section FAQ lama dibuang (section FAQ template punya judul sendiri) |
| `mea_judul_pendek` | Label breadcrumb dan nama schema Service, sama dengan label navigasi |
| `mea_hub_layanan` / `mea_hub_kasus` | `/services/` menampilkan hub semua layanan; `/study-case/` tampil sebagai halaman daftar |

Cek isi: jumlah kata per halaman lama vs seed (`tests/konversi_test.py` untuk konverter;
pemeriksaan per halaman dijalankan saat seed dibuat). Kata yang tidak ikut hanya: komentar HTML
dan `<title>` dokumen di dalam widget HTML (tidak pernah tampil), judul section FAQ lama
("INI JAWABAN YANG KAMU CARI?", "Pertanyaan yang Sering Diajukan seputar Jasa TikTok Ads"), dan
huruf drop cap yang kini menyatu dengan katanya. Rincian per halaman: `catatan` di tiap seed.

## Menerapkan (setelah tema 0.9.0 aktif)

Field `mea_isi`, `mea_label_hero`, `mea_hub_*` baru terdaftar setelah tema 0.9.0 terpasang.
Per seed, lewat konektor:

1. `wp_update_post_meta` (post_type `pages`): semua kunci di `meta`.
2. `wp_update_page`: `template` dari seed.
3. Cek: `wp_rm_get_head` URL halaman → title, description, robots, canonical sama dengan sebelum;
   halaman tampil dengan H1 = `mea_h1`.

`_elementor_data` tidak dihapus. `_elementor_edit_mode` dibiarkan; template Orbit tidak
merender isi Elementor dan aset Elementor tidak dimuat di halaman Orbit.

## Rollback per halaman

Set template ke nilai `sebelum.template` di seed (`default` = template bawaan, kosongkan) lewat
`wp_update_page`. Isi Elementor langsung tampil lagi; meta `mea_*` boleh dibiarkan.

| ID | URL | Template sebelum |
|---|---|---|
| 14662 | `/affiliate/` | `default` |
| 14637 | `/ecommerce-service/` | `default` |
| 30483 | `/jasa-iklan-marketplace/` | `default` |
| 365 | `/jasa-kelola-tiktok-shop/` | `default` |
| 18948 | `/jasa-konten-tiktok/` | `default` |
| 15559 | `/jasa-konten/` | `elementor_theme` |
| 16523 | `/jasa-live-streaming-tiktok/` | `elementor_theme` |
| 15567 | `/jasa-live-streaming/` | `elementor_theme` |
| 287 | `/jasa-management-marketplace/` | `default` |
| 4932 | `/jasa-optimasi-shopee/` | `default` |
| 16545 | `/jasa-tiktok-ads/` | `elementor_theme` |
| 30030 | `/jasa-upgrade-shopee-mall/` | `default` |
| 15549 | `/jasa-website/` | `default` |
| 14624 | `/seo/` | `default` |
| 12356 | `/services/` | `default` |
| 14632 | `/social-media-management/` | `default` |
| 14645 | `/study-case/` | `default` |
