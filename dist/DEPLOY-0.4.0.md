# DEPLOY 0.4.0 · meagency.co.id

Tema saja (mea-leads tetap 0.2.0). Berisi semua perubahan 0.3.0 (foto realis) ditambah:
noindex konten berpassword, navigasi 4 pilar (header + footer), baris "Juga tersedia" di homepage.

## Langkah

1. Simpan zip tema yang sedang live sebagai cadangan rollback.
2. Tampilan → Tema → Tambah Baru → Unggah Tema → `mea-orbit-0.4.0.zip` → **Ganti yang terpasang**.
3. hPanel → Cache Manager → **Purge All** (dan CDN bila aktif). Rank Math → Sitemap: simpan ulang
   pengaturan sitemap supaya cache sitemap dibangun ulang.
4. Cek (jendela incognito):
   ```
   curl -s https://www.meagency.co.id/sevenbee-19-25-oktober-weekly-report/ | grep -i 'name="robots"'
   curl -s https://www.meagency.co.id/page-sitemap.xml | grep -c 'weekly-report'
   curl -s https://www.meagency.co.id/jasa-iklan-shopee/ | grep -i 'name="robots"'
   ```
   Target: robots laporan berisi `noindex, nofollow`; jumlah weekly-report di sitemap hanya
   laporan **tanpa** password (lihat laporan Claude); halaman jasa tetap `index`.
5. Header: arahkan kursor dan Tab ke tiap pilar, dropdown muncul. HP: Menu → anak terindentasi.
6. Footer: kolom Layanan (4 pilar) dan Perusahaan.

## Rollback

Unggah zip tema sebelumnya dengan **Ganti yang terpasang**, lalu Purge All.
