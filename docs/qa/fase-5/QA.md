# QA Fase 5: kriteria lulus

Diuji 26 Okt 2026 di WordPress lokal (WP 7.1.3 + SQLite, PHP 8.3, tanpa Elementor dan Rank
Math). Yang hanya bisa dibuktikan di staging ditandai **staging**.

| # | Kriteria (prompt induk) | Status lokal | Bukti |
|---|---|---|---|
| 1 | Ketiga template tampil sama dengan prototipe di 1366 dan 390 px | Lolos. H1 dan urutan H2 identik di keenam pasangan, tanpa scroll horizontal, tanpa error JS. Beda yang disetujui: tile M2 (9.6), Cara kerja sticky + M3 (9.5), M4 menggantikan foto Studio-2 (9.7), kolom Layanan + link Blog di footer (10.1), poster hero (9.2) | `banding-*.jpg`, `banding-laporan.json` (screenshot viewport disambung, reduced motion) |
| 2 | PageSpeed mobile: LCP < 2,5 dtk, CLS < 0,1, INP < 200 ms; request homepage < 30; 0 byte motion sebelum load; total motion homepage ≤ 4 MB | Lolos lokal: LCP 0,98–1,09 dtk dengan throttling mirip Lighthouse, CLS ≤ 0,0003, request tanpa interaksi 22 (desktop) / 17 (HP), 0 byte motion sebelum load, total motion 3,68 MB / 2,01 MB. INP dan PageSpeed sebenarnya: **staging** | `../fase-4/uji-*.json` |
| 3 | Rich Results: Organization, Service + FAQPage, Article, BreadcrumbList | JSON-LD valid dan berisi tipe yang benar: homepage Organization + WebSite; layanan Service + FAQPage + BreadcrumbList; studi kasus Article + BreadcrumbList (jalur cadangan tanpa Rank Math). Penggabungan ke graph Rank Math dan Rich Results Test: **staging** | `../fase-2/jsonld-*.json` |
| 4 | Form: validasi, tersimpan, tampil di Leads MEA, payload v1, HMAC benar, retry tercatat | Lolos | `../fase-3/` (unit 49/49, vektor openssl, skenario server, browser, admin) |
| 5 | Reduced motion → semua poster, tanpa unduhan sequence/video | Lolos (desktop dan HP, juga Hemat Data) | `../fase-4/uji-motion.json`, `../fase-4/reduced-motion-*.jpg` |
| 6 | Tidak ada PHP warning/notice dengan WP_DEBUG | Lolos. 30 URL situs uji + 12 URL dan 7 halaman wp-admin di WordPress bersih: 0 pesan dari tema/plugin. Satu-satunya warning datang dari WordPress core yang tidak bisa menghubungi wordpress.org (jaringan container diblokir) | `uji-wp-bersih.json` |
| 7 | `dist/` berisi dua zip yang bisa diinstal di WordPress bersih | Lolos. Dipasang lewat `Theme_Upgrader`/`Plugin_Upgrader` (jalur yang sama dengan unggah di wp-admin), diaktifkan, tabel dan cron terbentuk, halaman Orbit tanpa isian tampil tanpa galat, form menyimpan lead "menunggu" | `uji-wp-bersih.json` |

## Alat paritas SEO

`tools/seo-parity/parity.php` diuji di situs lokal: snapshot dengan Hello Elementor ("sebelum")
dan mea-orbit ("sesudah"), 30 URL dari `wp-sitemap.xml`.

- Sebelum vs sebelum: 30/30 lolos (`contoh-paritas-lokal/ringkasan-sebelum-vs-sebelum.md`).
- Seluruh body: 30/30 berbeda (`ringkasan-seluruh-body.md`). Wajar di lokal: tanpa Elementor,
  Single Post Template dan Blog Archive Template tidak ada, jadi template Hello dibandingkan
  langsung dengan template mea-orbit. Di staging, artikel dan arsip tetap memakai template
  Elementor Pro, sehingga hasilnya harus jauh lebih bersih.
- Isi artikel saja (`--area=page-content,prose`): teks setiap artikel identik; sisa beda adalah
  link tag yang dicetak Hello di dalam area isi (`ringkasan-isi-artikel.md`).
- Title, description, robots, canonical, og:image, status: 0 beda.

Uji paritas produksi vs staging belum dijalankan: staging belum ada, dan container sesi ini
tidak bisa menjangkau www.meagency.co.id. Jalankan dari komputer sendiri sesuai
`dist/CARA-PASANG.md` bagian 0 dan 5.
