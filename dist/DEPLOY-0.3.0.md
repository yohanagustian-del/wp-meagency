# DEPLOY 0.3.0 · meagency.co.id

Foto realis untuk kartu "Enam sisi operasional, satu tim" dan "Cara kami bekerja" (homepage dan
semua halaman layanan). Hanya tema yang berubah; plugin mea-leads tetap 0.2.0. Isian Homepage
**tidak perlu diubah**: slug foto sama dengan nilai field kartu yang sudah tersimpan.

## Langkah (±10 menit)

1. Simpan `mea-orbit-0.2.1.zip` (versi live sekarang) sebagai cadangan rollback.
2. Tampilan → Tema → Tambah Baru → Unggah Tema → `mea-orbit-0.3.0.zip` →
   **Ganti yang terpasang dengan yang diunggah**.
3. hPanel → Cache Manager → **Purge All**. Bila CDN Hostinger aktif: hPanel → CDN → **Purge cache**.
4. Cek di jendela incognito:
   - **Homepage, Enam sisi**: enam foto 4:3 (desktop 3 kolom, HP 1 kolom); hover di desktop
     memperbesar foto sedikit.
   - **Cara kami bekerja**: scroll pelan, foto berganti mengikuti langkah yang aktif (oranye).
     Di HP kotak foto 300 px di tengah, tidak menutupi teks langkah aktif.
   - **DevTools → Network**, filter `motion/`: tidak ada request ke `motion/sisi/` atau
     `motion/proses/`. Foto berakhiran `?v=0.3.0`.
   - Satu halaman layanan (mis. `/jasa-performance-ads/`): foto muncul di Cara kami bekerja.
5. Catat ukuran total homepage (DevTools → Network → "transferred", scroll sampai bawah) untuk
   laporan sebelum vs sesudah.

## Catatan

- Wp-admin: label field kartu di Tampilan → Homepage MEA berubah dari "Loop Enam Sisi" menjadi
  "Foto layanan". Pilihan dan nilai tersimpan sama.
- Foto alternatif langkah 4 (meja kontrol) tidak dipasang. Bila dipilih: ganti
  `assets/foto/proses/langkah-4-1024.webp` dan `-800.webp` dengan nama yang sama, **naikkan
  versi tema**, bangun zip, lalu deploy (tanpa versi baru, cache menyajikan foto lama).

## Rollback

Upload `mea-orbit-0.2.1.zip` dengan **Ganti yang terpasang**, lalu Purge All.
