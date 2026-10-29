# DEPLOY 0.2.1 · meagency.co.id

Perbaikan kecil di atas 0.2.0: kubus hero (dan aset motion lain) tidak lagi tertahan di cache.
Hanya tema yang berubah; plugin mea-leads tetap 0.2.0. Isian Homepage tidak berubah.

## Kenapa

Audit 29 Okt 2026 (konektor, hanya baca): tema 0.2.0 aktif dan berkas hero v2 sudah ada di
server, tetapi homepage masih menampilkan kubus lama. Nama berkas hero v2 sama persis dengan v1
(`poster-first-1200.webp`, `frames/desktop/000.webp`, …), jadi browser dan CDN menyajikan salinan
lama. Di 0.2.1 setiap URL motion membawa `?v=0.2.1`, sehingga semua pengunjung mengambil berkas baru.

## Langkah (±5 menit)

1. Tampilan → Tema → Tambah Baru → Unggah Tema → `mea-orbit-0.2.1.zip` → **Ganti yang terpasang dengan yang diunggah**.
2. hPanel → Cache Manager → **Purge All**. Bila CDN Hostinger aktif: hPanel → CDN → **Purge cache**.
3. Buka homepage di jendela incognito. Kubus menampilkan logo Shopee di atas dan dua sisi video
   (ulasan kreator dan live shopping). Klik kanan gambar kubus → Buka gambar di tab baru: URL
   berakhiran `?v=0.2.1`.

## Catatan tampilan

- Sisi video di kubus **bergerak saat halaman di-scroll** (scroll-scrub), bukan diputar otomatis.
  Frame dimuat setelah halaman selesai dimuat dan setelah interaksi pertama (scroll, sentuh,
  gerak mouse). Di desktop yang dimuat tiap frame keempat (31 frame) agar tetap ≤ 1,5 MB sebelum
  hero terlewati, jadi gerakannya lebih patah daripada di HP (60 frame). Tiap frame kedua lebih
  halus tetapi 1,66 MB; itu butuh izin melonggarkan batas.
- Sambil di wp-admin (Tampilan → Homepage MEA), lengkapi isian berikut:
  - Partner resmi: kolom **Periode** kedua baris masih kosong (isi `Q3 2026`).
  - Mockup HP "Konten": label baris 2 masih "skincare" (huruf kecil); label baris 3 (live
    shopping) kosong.

## Rollback

Upload `mea-orbit-0.2.0.zip` dengan **Ganti yang terpasang**, lalu Purge All.
