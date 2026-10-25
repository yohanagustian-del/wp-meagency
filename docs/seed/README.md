# Isi awal halaman Orbit

File di folder ini berisi copy prototipe untuk halaman yang pertama memakai template Orbit. Isinya diterapkan di **staging**, tidak di situs live.

| File | Halaman | Cara menerapkan |
|---|---|---|
| `page-14144-jasa-performance-ads.json` | `/jasa-performance-ads/` | Pilih template "MEA Orbit · Layanan", simpan, lalu isi meta box "Konten MEA Orbit". Bisa juga lewat REST: `POST /wp-json/wp/v2/pages/14144` dengan body `{"template": …, "meta": …}` dari file ini (konektor Easy MCP: `wp_update_page` + `wp_update_post_meta`) |
| `page-14512-bellarosa.json` | `/bellarosa/` | Sama, dengan template "MEA Orbit · Studi kasus" |
| `media-alt.json` | Media Library | Isi alt text per ID lampiran (Media → edit lampiran, atau `wp_update_media`) |

Homepage (13411) tidak perlu seed. Opsi Tampilan → Homepage MEA dan daftar kutipan sudah berisi copy prototipe sebagai nilai bawaan.

Semua meta memakai prefix `mea_`. Meta bertipe daftar dan objek divalidasi skema REST; kunci yang tidak dikenal ditolak.
