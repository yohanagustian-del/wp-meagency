# QA mea-orbit 0.4.0 · lokal (29 Okt 2026)

Harness PHP 8.3 (stub fungsi WordPress) yang merender `header.php`, `footer.php`, dan section
Enam sisi dengan `base.css`/`orbit.css` tema; Chromium (Playwright). Belum diuji di WordPress
staging/produksi.

| Cek | Hasil |
|---|---|
| Header 1280/1100/1081 px | 4 pilar tampil, tinggi header tetap 72 px, tanpa scroll horizontal |
| Dropdown hover | Panel terbuka saat hover dan tetap terbuka saat kursor turun ke anak (jembatan hover) |
| Dropdown keyboard | Tab: E-commerce Service → 6 anak (panel terlihat) → Performance Ads → anaknya; fokus terlihat |
| Menu HP (≤1080, 390 px) | 16 link, anak terindentasi, panel bisa di-scroll (`max-height`), tanpa overflow horizontal |
| Footer 1280/390 | Kolom Layanan 4 pilar (2×2), kolom Perusahaan (menu lokasi footer / cadangan), Kantor + Hubungi |
| "Juga tersedia" | Tampil di bawah grid Enam sisi, link /social-media-management/ dan /seo/ |
| Robots/sitemap | Uji unit dengan stub: post berpassword → `noindex`/`nofollow` + entri sitemap `false`; post tanpa password, non-singular, dan entri term tidak berubah |
| Kode | `php -l` bersih untuk semua file PHP yang diubah |
