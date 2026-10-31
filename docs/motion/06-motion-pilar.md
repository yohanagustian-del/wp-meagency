# Motion per pilar layanan (31 Okt 2026)

Bahasa motion tetap konsep A "Orbit Tenang" (`00-peta-motion.md`). Semua motion pilar memakai
primitif `assets/js/motion.js` yang sudah ada, tanpa library baru. Kubus tetap hanya di hero
homepage. Reduced motion, Hemat Data, dan 2g/3g: poster/keadaan akhir statis, angka tampil penuh.
Aturan "nol bukti palsu" berlaku: angka hanya dari studi kasus terbit dengan izin publikasi; grafik
ilustratif tidak memuat angka.

Dipasang lewat field `motion` di template layanan (`template-parts/layanan/motion.php`), section
tanpa judul tepat setelah hero (bukan di dalam hero, supaya hitung naik dan pemutaran terjadi saat
pengunjung sampai, dan gambar LCP hero tidak berubah).

| Pilar | Halaman | Motion | Primitif | Status |
|---|---|---|---|---|
| E-commerce | `/ecommerce-service/` | Kurva omzet Bellarosa tergambar (video M4, diputar sekali saat 60% terlihat, menahan frame akhir) + angka Bellarosa hitung naik | `bellarosa()`, `counters()` | **0.9.0** |
| Live | `/jasa-live-streaming/`, `/jasa-live-streaming-tiktok/` | Mockup HP memutar video live portofolio tim (diputar saat 50% terlihat, berhenti saat keluar) | `konten()` | **0.9.0** |
| Hub | `/services/` | Empat pilar mengorbit, tiap pilar menyala saat di-scroll | `reveals()` (`data-reveal` + `.is-in`) pada `.hub-pilar`, lintasan orbit SVG/CSS | Berikutnya |
| Upgrade Shopee Mall | `/jasa-upgrade-shopee-mall/` | Badge toko biasa → Star → Mall bertahap mengikuti scroll | pola `proses()` (langkah aktif dari progress scroll) | Berikutnya |
| Performance Ads | `/jasa-performance-ads/` + anak | Corong iklan → ROAS: garis tergambar + teks per baris | `reveals()` (`data-reveal-lines`, garis) | Berikutnya |
| Konten / SMM | `/jasa-konten/`, `/jasa-konten-tiktok/`, `/social-media-management/` | Mockup HP + crossfade foto studio | `konten()` + crossfade `proses()` (M3) | Berikutnya |
| Affiliate | `/affiliate/` | Node kreator terhubung ke toko (garis tergambar) | `reveals()` | Berikutnya |
| SEO | `/seo/` | Grafik posisi naik, ilustratif, tanpa angka klaim | `reveals()` (garis) | Berikutnya |

Pilihan `motion` yang belum dibuat tidak didaftarkan di field, supaya tidak ada nilai yang
tampil kosong. Tiap pilar berikutnya menambah satu nilai `motion` + satu cabang di
`template-parts/layanan/motion.php`.
