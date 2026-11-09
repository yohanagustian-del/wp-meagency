# DEPLOY 0.10.0 · meagency.co.id

Tema saja. Isi: galeri studi kasus mode bulanan (lihat `CHANGELOG.md`).

## 1. Data live (sudah, lewat konektor)

- Halaman 36702 `/studi-kasus-fashion-muslim-shopee/`: meta `mea_galeri_mode = bulanan`.
  Tema 0.9.3 mengabaikan meta ini, jadi aman dipasang sebelum tema.

## 2. Pasang tema

- **wp-admin / konektor**: Tampilan → Tema → MEA Orbit → **Update now** (muncul setelah PR rilis
  di-merge; Dasbor → Pembaruan → **Periksa lagi**).
- **Unggah zip**: `mea-orbit-0.10.0.zip` → **Ganti yang terpasang dengan yang diunggah**.
- Bersihkan cache halaman 36702.

## 3. Cek

- `/studi-kasus-fashion-muslim-shopee/`: section "Data per bulan dari Seller Center", 7 tab
  (Februari 2026 … Agustus 2026), gambar utuh tanpa crop, ‹ › dan panah keyboard berpindah bulan,
  di HP geser kiri-kanan. Sumber HTML: 7 `role="tabpanel"`, `img` pertama `loading="eager"`,
  sisanya `loading="lazy"`; `galeri-bulanan.js` dimuat.
- Studi kasus lain (mis. `/study-case/` Bellarosa 14512): galeri grid 1:1 sama seperti sebelumnya,
  `galeri-bulanan.js` **tidak** dimuat.
- Edit halaman studi kasus di wp-admin: kotak Konten MEA Orbit punya pilihan "Tampilan galeri".

Rollback: unggah `mea-orbit-0.9.3.zip` (meta `mea_galeri_mode` boleh dibiarkan; 0.9.3 mengabaikannya).
