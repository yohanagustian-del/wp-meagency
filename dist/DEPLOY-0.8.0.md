# DEPLOY 0.8.0 · meagency.co.id

Tema saja. Isi: laporan klien tampil tanpa Elementor (lihat `CHANGELOG.md` dan `docs/LAPORAN-HTML.md`).

## Pasang (pilih satu)

- **wp-admin**: Tampilan → Tema → MEA Orbit → **Update now** (muncul setelah PR rilis ini
  di-merge ke branch utama; WordPress mengecek paling lama tiap 12 jam, buka Dasbor → Pembaruan →
  **Periksa lagi** untuk cek sekarang).
- **Unggah zip**: Tampilan → Tema → Tambah Baru → Unggah Tema → `mea-orbit-0.8.0.zip` →
  **Ganti yang terpasang dengan yang diunggah**.

Lalu bersihkan cache halaman.

## Cek setelah pasang (±5 menit)

1. Buka satu weekly report terbaru (berpassword) di jendela incognito → form password tema muncul.
2. Masukkan password → laporan tampil satu halaman penuh **tanpa header/footer MEA**, isi sama.
3. Klik kanan → View Source → baris ke-1–3 berisi `<meta name="robots" content="noindex, nofollow">`
   dan tidak ada file `elementor` di source.
4. Buka `/jasa-kelola-tiktok-shop/` dan `/study-case/` → tampil seperti sebelumnya (tidak tersentuh).
5. Buka satu monthly report yang punya gambar tambahan → tampil seperti sebelumnya (lewat Elementor).

Rollback: unggah `mea-orbit-0.7.1.zip` dengan **Ganti yang terpasang**. Tidak ada data yang diubah.
