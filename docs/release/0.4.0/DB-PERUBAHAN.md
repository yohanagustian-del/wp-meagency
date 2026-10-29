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
