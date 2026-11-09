# DEPLOY 0.10.1 · meagency.co.id

Tema saja. Isi: galeri bulanan bisa diketuk untuk ukuran penuh, dan tinggi galeri mengikuti bulan
aktif (lihat `CHANGELOG.md`).

## 1. Data live (sudah, lewat konektor)

- `/study-case/` (14645) field isi: link Amigos Pet Shop diperbaiki; ditambah Gerai Henna & Kutek,
  Butik Kemeja Batik, Brand Fashion Muslim (Shopee). Cache halaman sudah dibersihkan.
  Cadangan: `backups/14645-mea_isi-20261109.html`.

## 2. Pasang tema

- **wp-admin / konektor**: Tampilan → Tema → MEA Orbit → **Update now** (muncul setelah PR rilis
  di-merge; Dasbor → Pembaruan → **Periksa lagi**).
- **Unggah zip**: `mea-orbit-0.10.1.zip` → **Ganti yang terpasang dengan yang diunggah**.
- Bersihkan cache halaman 36702.

## 3. Cek

- `/studi-kasus-fashion-muslim-shopee/` di HP: ketuk gambar → file `.webp` asli terbuka di tab baru
  dan bisa di-zoom; petunjuk "Ketuk atau klik gambar…" ada di bawah galeri.
- Pindah dari Agustus ke Februari: area galeri memendek, tidak ada ruang kosong besar di bawah
  gambar.
- `/study-case/`: link Amigos Pet Shop ke `/amigos-pet-shop/`; tiga link baru di akhir daftar brand.

Rollback: unggah `mea-orbit-0.10.0.zip`. Isi `/study-case/`: tulis ulang meta `mea_isi` dari cadangan.
