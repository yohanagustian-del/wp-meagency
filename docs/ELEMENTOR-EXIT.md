# Rencana keluar dari Elementor (meagency.co.id)

Draf Fase 1 · 25 Okt 2026 · dilengkapi di Fase 5 setelah dicek di staging.
Acuan: `docs/PLAN.md` bagian 12 dan 14.

Tujuan akhir: Elementor, Elementor Pro, ElementsKit Lite, dan Unlimited Elements dinonaktifkan tanpa mengganggu crawl Google. Caranya dua langkah terpisah: **langkah 1** ganti tema, **langkah 2** pindahkan konten. Setiap langkah dicek dulu dengan uji paritas sebelum langkah berikutnya.

---

## 1. Inventaris (dibaca dari situs live lewat konektor, read-only)

### 1.1 Template Theme Builder Elementor Pro

| ID | Nama | Peran sekarang | Di langkah 1 |
|---|---|---|---|
| 10093 | Header New | Header seluruh situs | **Tidak tampil lagi.** Header mea-orbit menggantikannya |
| 17281 | Footer New | Footer seluruh situs | **Tidak tampil lagi.** Footer mea-orbit menggantikannya |
| 13811 | Single Post Template | Tata letak artikel blog | **Tetap aktif.** Tata letak artikel tidak berubah, hanya header/footer yang berganti |
| 14400 | Blog Archive Template | Tata letak arsip blog | **Tetap aktif.** Sama seperti di atas |

Kondisi tampil (display conditions) setiap template belum bisa dibaca lewat konektor. Dicek di staging.

Ada 45 template lain di Elementor Library (section tersimpan seperti "Bellarosa Section" dan "FAQ Section", halaman landing lama, dan beberapa template uji). Template ini tidak tampil sendiri. Template yang disisipkan lewat widget Template atau shortcode di halaman tertentu akan ketahuan saat konverter dijalankan dalam mode uji coba (langkah 2).

### 1.2 Konten

| Jenis | Jumlah | Catatan |
|---|---|---|
| Artikel terbit | 212 | Contoh yang dicek (ID 18546, 17265) seluruhnya dibangun Elementor: widget Text Editor dan Heading berisi HTML biasa, kotak CTA dengan style inline, FAQ JSON-LD di dalam konten. 15 artikel memakai template `elementor_theme`, sisanya default |
| Halaman terbit | 1.929 | Sebagian besar laporan klien (berpassword, akan pindah ke CDPS). Halaman pemasaran sekitar 30, sebagian memakai `elementor_theme` atau `elementor_header_footer` |
| Homepage | 13411 | Dipindah ke template Orbit (`front-page.php`) di Fase 2 |
| Halaman blog | 13324 (`/blog/`) | Diatur sebagai halaman artikel |

### 1.3 Menu WordPress

Menu yang ada ("Main Menu", "Header Menu", "Services Menu", dan seterusnya) adalah sisa tema lama dan berisi link ke halaman "Mighti" dan post type `services` lama. Header dan footer sekarang dibangun di template Elementor, bukan dari menu ini.

mea-orbit memakai dua lokasi menu baru: `mea-orbit-utama` (header) dan `mea-orbit-footer` (kolom Layanan). Selama keduanya belum diatur, tema memakai isi cadangan:
- **Header**: Layanan, Studi kasus, Tim, Cara kerja.
- **Footer**: enam halaman layanan, Studi kasus, Blog.

Sebelum langkah 1, link di Header New dan Footer New didata di staging. Link penting yang belum ada di menu baru ditambahkan ke `mea-orbit-footer`, supaya struktur link internal sitewide tidak berkurang.

### 1.4 Plugin terkait

Elementor 3.30, Elementor Pro 3.30, ElementsKit Lite 4.0.1, Unlimited Elements 2.0.16. Classic Editor aktif (artikel baru bisa ditulis tanpa Elementor).

---

## 2. Yang terjadi saat mea-orbit aktif (langkah 1)

| Jenis halaman | Header/footer | Isi |
|---|---|---|
| Artikel blog | mea-orbit | Tidak berubah (Single Post Template Elementor Pro) |
| Arsip blog, kategori | mea-orbit | Tidak berubah (Blog Archive Template Elementor Pro) |
| Halaman Elementor (default / `elementor_theme`) | mea-orbit | Tidak berubah. Judul H1 tampil kecuali "Hide Title" aktif, sama dengan Hello Elementor |
| Halaman `elementor_header_footer` | mea-orbit | Tidak berubah |
| Halaman `elementor_canvas` | Tidak ada (memang begitu) | Tidak berubah |
| Laporan klien berpassword | mea-orbit | Form password bergaya mea-orbit. Proteksi dan noindex dari WPCode tidak disentuh |
| Homepage, layanan 14144, Bellarosa 14512 | mea-orbit | Template Orbit (Fase 2), tanpa aset Elementor |
| 404, pencarian | mea-orbit | Template mea-orbit |

Cara tema menjaga ini:
- Tema mendaftarkan lokasi Theme Builder, jadi Elementor Pro tidak menyisipkan header/footernya sendiri.
- Tema tidak pernah memanggil lokasi `header`/`footer`.
- Halaman Orbit selalu memakai template tema, walaupun ada kondisi Theme Builder yang cocok.
- `elementor-compat.css` menggantikan reset dasar Hello Elementor untuk isi Elementor. Selector-nya memakai `:where()` sehingga spesifisitasnya nol, dan Kit Elementor tetap menang.

---

## 3. Urutan keluar

1. **Staging, langkah 1.** Aktifkan mea-orbit. Elementor dan semua template Theme Builder tetap seperti sekarang.
2. **Uji paritas** seluruh URL di sitemap, produksi vs staging (`tools/seo-parity`). Syarat lanjut: tidak ada beda di status, canonical, robots, title, meta description, H1, teks utama, link di area konten, gambar, dan JSON-LD. Beda di link header/footer boleh, asal sama di semua URL.
3. **Produksi, langkah 1.** Aktifkan mea-orbit di jam sepi, bersihkan cache, uji paritas lagi, kirim ulang sitemap di Search Console, pantau 2–4 minggu.
4. **Langkah 2, per batch** (sekitar 25 URL). Konverter `mea-migrasi-elementor` dijalankan dalam mode uji coba (laporan widget per URL), lalu konversi di staging, uji paritas, terapkan di produksi, dan pantau. URL dengan widget di luar Text Editor, Heading, Image, Spacer, Divider, dan Button ditandai "manual".
5. **Template Theme Builder.** Setelah semua artikel terkonversi: nonaktifkan Single Post Template dan Blog Archive Template, lalu uji paritas. Mulai saat itu artikel memakai `single.php` dan arsip memakai `archive.php`/`index.php` milik mea-orbit.
6. **Plugin.** Nonaktifkan Unlimited Elements dan ElementsKit Lite, lalu Elementor Pro, lalu Elementor, masing-masing diikuti uji paritas.
7. **Bersihkan.** Hapus `inc/elementor.php` dan `assets/css/elementor-compat.css` dari tema.

Syarat menonaktifkan Elementor: laporan konverter menunjukkan **0 URL terindeks** yang masih bergantung Elementor. Laporan klien tidak dihitung karena pindah ke CDPS.

## 4. Cek sebelum setiap langkah

| Langkah | Halaman terdampak | Cara cek | Rollback |
|---|---|---|---|
| Ganti tema | Semua URL (header/footer) | Paritas seluruh sitemap + screenshot contoh artikel, halaman Elementor, halaman berpassword | Aktifkan Hello Elementor |
| Konversi batch | URL di batch itu | Paritas per URL + screenshot | Kembalikan dari `_mea_elementor_cadangan` atau revisi WordPress |
| Nonaktifkan template Single/Archive | Semua artikel dan arsip | Paritas artikel + arsip | Aktifkan lagi template-nya |
| Nonaktifkan addon/plugin | Halaman yang masih memakai widgetnya (harus 0) | Laporan konverter + paritas | Aktifkan lagi plugin-nya |

## 5. Rollback tema

Tampilan → Tema → aktifkan Hello Elementor. Data field mea-orbit, opsi, dan lead di plugin `mea-leads` tetap tersimpan. Isi konten tidak pernah diubah di langkah 1, jadi tidak ada yang perlu dipulihkan.

---

## 6. Masih harus dicek di staging

- Kondisi tampil keempat template Theme Builder.
- Link di Header New dan Footer New, untuk dimasukkan ke menu baru.
- Apakah Single Post Template mencetak breadcrumb, kotak penulis, atau artikel terkait. Ini menentukan paritas saat template itu dinonaktifkan di langkah 2.
- Kit Elementor (warna, font global) dan pengaruhnya ke widget lama tanpa reset Hello Elementor.
- Daftar widget yang benar-benar dipakai di halaman pemasaran.
