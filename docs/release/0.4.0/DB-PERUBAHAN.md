# Perubahan database live · 29 Okt 2026 (prompt gabungan, tugas 3 dan 4)

Backup DB dibuat pemilik di hPanel sebelum perubahan. Semua lewat konektor MEAgency (Easy MCP).

## Tugas 3 · opsi Homepage (`mea_orbit_home`, REST `/wp/v2/settings`)
| Kunci | Sebelum | Sesudah |
|---|---|---|
| `layanan[0].deskripsi` (Iklan) | Shopee Ads dan TikTok Shop Ads yang diatur dari data penjualan, bukan dari tebakan. | Shopee Ads, TikTok Shop Ads, dan Meta Ads yang diatur dari data penjualan, bukan dari tebakan. |
| `layanan[2].url` (Shopee) | /ecommerce-service/ | /jasa-management-marketplace/ |

Link kartu lain sudah sesuai tabel prompt. Baris "Juga tersedia" ada di tema 0.4.0.

## Tugas 4 · halaman template layanan
Halaman ber-template `template-layanan.php` (publish): 7467 jasa-iklan-shopee, 14144 jasa-performance-ads, 35658 jasa-meta-ads.

| Page | `_elementor_edit_mode` sebelum | sesudah |
|---|---|---|
| 7467 | builder | "" |
| 14144 | builder | "" |
| 35658 | "" | (tidak diubah) |

`_elementor_data` tidak dihapus.

AggregateRating: hanya ada di widget HTML Elementor 7467 (id 06a0eea), di dalam satu `<script type="application/ld+json">` @graph (Service, FAQPage, AggregateRating). Template layanan tema tidak memanggil `the_content`, jadi blok ini kemungkinan tidak tampil di HTML live, tetapi tetap dihapus supaya tidak muncul lagi bila template berubah. Yang dihapus hanya objek AggregateRating; Service dan FAQPage tetap. Search-replace di `wp_postmeta.meta_value` (5 sel: meta_id 67540, 237457, 237468, 255151, 255162 — data halaman 7467 dan salinan revisinya).

Potongan yang dihapus (byte persis di DB, untuk rollback disisipkan kembali tepat sebelum `\r\n  ]\r\n}\r\n<\/script>`):

```
,\r\n    {\r\n      \"@type\": \"AggregateRating\",\r\n      \"itemReviewed\": {\r\n        \"@type\": \"Service\",\r\n        \"name\": \"Jasa Iklan Shopee MEA Agency\"\r\n      },\r\n      \"ratingValue\": \"4.9\",\r\n      \"reviewCount\": \"312\",\r\n      \"bestRating\": \"5\"\r\n    }
```

Teks tampilan "4.9 / 5.0 ★ Berdasarkan 312+ ulasan" dan "4.9★ Rating klien" juga ada di data Elementor 7467 (tidak dirender template tema, tidak diubah).

## Laporan klien terbuka (permintaan pemilik, 29 Okt 2026)
- 37 Weekly Report tanpa password → diberi password tim (sama dengan laporan lain) + `rank_math_robots` = noindex, nofollow: 35615, 35360, 35317, 34810, 34243, 33768, 33561, 32794, 31896, 31790, 31420, 28746, 28541, 27705, 27717, 27153, 26361, 26358, 25529, 25143, 25000, 24987, 24423, 24009, 23798, 23135, 22947, 22830, 22821, 22191, 22048, 21383, 21361, 21332, 21315, 20852, 20776.
- 6 monthly/test report tanpa password → `rank_math_robots` = noindex, nofollow (password tidak diubah): 30928, 30755, 25796, 23732, 21359, 21357.
- Semua sebelumnya `rank_math_robots` kosong (= index). Terverifikasi lewat REST setelah perubahan.

## Bellarosa (14512)
- `_elementor_edit_mode`: builder → "". `_elementor_data` tidak dihapus.
- Setelah tema 0.5.0 aktif: `mea_galeri` diisi 13 item (12 gambar 14508, 14506, 14505, 14504, 14503, 14495, 14494, 14493, 14492, 14491, 14490, 14489 + video 14496) dengan alt deskriptif dari isi gambar; video tampil mulai tema 0.5.1.
- `mea_angka` ditambah baris ke-4: "1.200%" · "Kenaikan traffic organik TikTok" (tidak tampil di beranda). Sumber: data TikTok (konfirmasi pemilik), angka dari counter halaman Elementor lama.
- `mea_foto_proses` Bellarosa: 1 foto (16519) → 4 foto berurutan 14504, 14505, 16519, 14495 dengan keterangan dan alt (berganti saat scroll mulai tema 0.5.2).
