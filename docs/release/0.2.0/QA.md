# QA · rilis 0.2.0 · 28 Okt 2026

WordPress lokal (WP 7.1.3 + SQLite, `WP_DEBUG` menyala). Situs live tidak disentuh.

## Hasil

| Kriteria | Hasil |
|---|---|
| `php -l`, PHPCS (WordPress, file PHP) tema dan plugin | Bersih |
| `php tests/run.php` | 91/91 lolos |
| Halaman beranda, layanan, studi kasus, artikel, arsip, 404, berpassword di 1366 dan 390 px | 200 (404 untuk halaman 404), tanpa pesan PHP, tanpa scroll horizontal, tanpa galat JS. Screenshot: `<halaman>-1366.png`, `<halaman>-390.png` |
| String "Agensi" di HTML ketujuh halaman, kode, seed, JSON-LD | Tidak ada. `legalName` = "PT MEA Digital Marketing", footer © PT MEA Digital Marketing |
| Upgrade 0.1.0 → 0.2.0 (WordPress bersih, zip 0.1.0 dari commit 7fd0459, lalu zip 0.2.0 dengan "Ganti yang terpasang") | Field halaman layanan (17 kunci) dan studi kasus (19 kunci) serta opsi Homepage identik (hash sama), halaman tampil tanpa notice |
| Field halaman 13411, 14144, 14512, 7467 dan opsi Homepage di WordPress lokal sebelum/sesudah kode 0.2.0 | Identik (hash opsi sama; jumlah meta sama) |
| Tanpa konstanta tracking dan CDPS | Situs dan form jalan, tanpa notice (uji tracking Fase 1 + instal bersih) |
| Budget motion | Lolos, lihat `dist/DEPLOY-0.2.0.md` bagian 8 dan `motion/laporan.json` |
| Hero v2 | Poster frame 0 sebagai LCP; frame mulai dimuat setelah `load` + interaksi; desktop 31 frame, mobile 60 frame; reduced motion dan hemat data → poster saja. Screenshot `motion/desktop-home-hero-0.3.png` (sisi TikTok tajam) |
| Strip Partner resmi | Tampil di bawah hero, gambar utuh, PNG tanpa bingkai, JPG dalam kartu; HP digeser horizontal; CLS desktop 0,0009, HP 0. Screenshot `partner-1366.png`, `partner-390.png` memakai badge dan plakat asli (WebP dari `motion-src/partner/`): badge transparan tanpa bingkai, plakat dalam kartu; CLS 0,0003 / 0,0005 |
| Hapus lead | Aksi baris menghapus lead menunggu (konfirmasi muncul). Aksi massal pada 1 terkirim + 1 menunggu: "1 lead dihapus. 1 lead dilewati…", lead terkirim tetap ada. Link Hapus hanya pada lead non-terkirim. Screenshot `leads-hapus-*.png` |

Galat konsol `ERR_TUNNEL_CONNECTION_FAILED` di laporan motion berasal dari `googletagmanager.com` yang diblokir di lingkungan uji (konstanta GTM contoh diisi di lokal), bukan dari tema.
