# Pasang langsung di produksi · meagency.co.id

Panduan ini untuk memasang **mea-orbit** (tema) dan **mea-leads** (plugin form) langsung di
www.meagency.co.id tanpa staging. Ikuti urutannya. Setiap langkah bisa dibatalkan dalam satu
klik (bagian 9).

Kondisi produksi saat panduan ditulis (dibaca 26 Okt 2026): tema Hello Elementor 3.4.9,
halaman depan 13411, halaman blog 13324, Elementor + Elementor Pro 3.30, ElementsKit Lite,
Unlimited Elements, Rank Math, Site Kit, Meta Pixel, WPCode, Code Snippets, UpdraftPlus,
WPS Hide Login. Tidak ada plugin cache; cache server diatur di hPanel.

## Isi paket

| Berkas | Untuk apa |
|---|---|
| `mea-orbit.zip` | Tema. Diunggah di Tampilan → Tema |
| `mea-leads.zip` | Plugin form konsultasi. Diunggah di Plugin |
| `README.md` | Panduan ini (di repo: `dist/PASANG-PRODUKSI.md`) |
| `CARA-PASANG.md` | Referensi lengkap (konstanta, cron, CDN, menu, paritas) |
| `seed/` | Isi awal halaman `/jasa-performance-ads/` (14144) dan `/bellarosa/` (14512), alt text foto |
| `WEBSITE_LEAD_CONTRACT.md` | Kontrak pengiriman lead ke CDPS (untuk tim CDPS) |
| `ELEMENTOR-EXIT.md` | Rencana keluar dari Elementor (Fase 6) |
| `tools/seo-parity/` | Alat cek SEO sebelum/sesudah (butuh PHP 8 di komputer) |

---

## 1. H-1: persiapan (30 menit)

- [ ] **Pilih jam sepi**, misalnya pukul 22.00–24.00 WIB. Siapkan satu orang yang memegang
      wp-admin dan satu yang mengecek dari HP.
- [ ] **Backup penuh.** UpdraftPlus → Backup Now (database + file), lalu unduh hasilnya. Buat
      juga backup di hPanel → Files → Backups.
- [ ] **Screenshot header dan footer lama** (desktop dan HP), dan catat semua link di dalamnya.
      Dipakai untuk menyusun menu baru di langkah 6.
- [ ] **Snapshot SEO** (sangat disarankan, perlu PHP 8 di komputer):
      ```sh
      php tools/seo-parity/parity.php snapshot --base=https://www.meagency.co.id --out=snap/produksi
      ```
      Simpan folder `snap/produksi`. Kalau tidak ada PHP, lewati, tetapi kerjakan cek manual di
      langkah 5.
- [ ] **Tentukan siapa yang membuka wp-admin → Leads MEA setiap hari.** Penerima CDPS belum
      tersambung, jadi lead dari form website **hanya tersimpan di WordPress** (status
      "menunggu") dan tidak ada notifikasi email. Pengunjung juga diarahkan ke tombol
      "Chat tim sales sekarang" setelah mengisi form, jadi sebagian lead akan masuk lewat
      WhatsApp.

## 2. Pasang plugin mea-leads (tidak mengubah tampilan)

1. Plugin → Tambah Baru → Unggah Plugin → `mea-leads.zip` → Pasang Sekarang → **Aktifkan**.
2. Muncul menu **Leads MEA** di wp-admin. Tampilan situs belum berubah (form baru tampil
   setelah tema aktif).
3. **Jangan** isi `MEA_LEADS_ENDPOINT` / `MEA_LEADS_SECRET` dulu. Diisi setelah penerima CDPS
   produksi siap (`CARA-PASANG.md` bagian 2).

## 3. Unggah tema dan pratinjau tanpa mengaktifkan

1. Tampilan → Tema → Tambah Baru → Unggah Tema → `mea-orbit.zip` → Pasang Sekarang.
   **Jangan klik Aktifkan.**
2. Klik **Pratinjau Langsung** (Live Preview) pada mea-orbit. Situs tampil dengan tema baru
   **hanya untuk Anda**; pengunjung masih melihat tema lama.
3. Di pratinjau, buka dan periksa: homepage, satu artikel blog, `/blog/`, satu halaman layanan
   lama, satu laporan klien berpassword, dan URL acak (404).
   - Homepage langsung tampil dengan desain baru (nilai bawaan opsi sudah berisi copy
     prototipe).
   - Artikel dan arsip blog: isinya sama seperti sekarang, hanya header/footer yang baru.
4. Tutup pratinjau tanpa menyimpan bila ada yang aneh, lalu kabari tim.

## 4. Aktifkan tema

1. Tampilan → Tema → mea-orbit → **Aktifkan**.
2. hPanel → Websites → meagency.co.id → **Cache Manager → Purge All**.
   Bila memakai CDN Hostinger: purge juga di sana.
3. Buka situs di jendela penyamaran (incognito) dan di HP.

Mulai detik ini:
- Header New (10093) dan Footer New (17281) Elementor Pro tidak tampil lagi; diganti
  header/footer mea-orbit di **semua** URL.
- Artikel dan arsip tetap memakai Single Post Template (13811) dan Blog Archive (14400).
- Homepage memakai desain baru. Isi Elementor halaman 13411 tidak dihapus, hanya tidak dipakai.
- `/jasa-performance-ads/` dan `/bellarosa/` masih tampil versi lama sampai langkah 6.

## 5. Cek 15 menit pertama

Buka di incognito, desktop dan HP. Bila salah satu gagal parah → **rollback** (bagian 9).

| URL | Yang dicek |
|---|---|
| `/` | Hero, angka, enam layanan, Cara kerja, studi kasus, form, footer tampil. Animasi kubus jalan saat discroll |
| Satu artikel, mis. `/berapa-persen-keuntungan-jualan-di-shopee/` | Isi, gambar, FAQ, CTA sama seperti sebelumnya |
| `/blog/` | Daftar artikel |
| Satu laporan klien berpassword | Form password tampil; isi baru muncul setelah password benar |
| `/halaman-yang-tidak-ada/` | Halaman 404 mea-orbit |
| `?s=shopee` | Hasil pencarian |

Cek SEO di view-source homepage dan satu artikel (Ctrl+U):
- [ ] Ada tepat satu `<title>`, satu `meta name="description"`, satu `link rel="canonical"`.
- [ ] Tidak ada `noindex` (kecuali di laporan berpassword).
- [ ] `og:locale` = `id_ID`.
- [ ] Ada `GTM-5XWWJBW` dan Meta Pixel (fbq).

## 6. Isi konten

### 6.1 Halaman layanan dan studi kasus

Pakai REST supaya template dan isian berganti **bersamaan** (halaman tidak sempat tampil kosong).
Buat Application Password: Pengguna → Profil → Application Passwords → nama "mea-orbit" →
Tambah. Lalu dari komputer, di folder paket ini:

```sh
curl -u 'USERNAME:xxxx xxxx xxxx xxxx xxxx xxxx' -H 'Content-Type: application/json' \
  -X POST https://www.meagency.co.id/wp-json/wp/v2/pages/14144 \
  --data @seed/page-14144-jasa-performance-ads.json

curl -u 'USERNAME:xxxx xxxx xxxx xxxx xxxx xxxx' -H 'Content-Type: application/json' \
  -X POST https://www.meagency.co.id/wp-json/wp/v2/pages/14512 \
  --data @seed/page-14512-bellarosa.json
```

Perintah di atas untuk Terminal Mac/Linux atau Git Bash di Windows. Hapus Application
Password setelah selesai. Cara manual: edit halaman → Atribut Halaman →
Template "MEA Orbit · Layanan" (14144) / "MEA Orbit · Studi kasus" (14512) → Perbarui → isi
meta box **Konten MEA Orbit** dari berkas seed.

Purge cache lagi, lalu buka kedua halaman.

### 6.2 Rank Math

Edit halaman 14144 dan 14512 → panel Rank Math: perbarui **title dan meta description**
(isinya masih teks lama: iklan Google/Bing dan "brand baru").

### 6.3 Alt text foto

Media → buka lampiran sesuai `seed/media-alt.json` → isi Teks Alternatif. Foto 15633:
"Yohan Agustian, CEO, dan Nerissa, COO MEA Agency, di kantor MEA".

### 6.4 Menu

Tampilan → Menu:
- Lokasi **Menu utama (header)**: Layanan, Studi kasus, Tim, Cara kerja.
- Lokasi **Menu footer (kolom Layanan)**: enam halaman layanan, Studi kasus, Blog, ditambah
  link penting dari footer lama (screenshot langkah 1) supaya link internal tidak berkurang.

Selama menu belum diatur, tema memakai isi cadangan yang sama, jadi situs tidak pernah tanpa menu.

### 6.5 Opsi homepage

Tampilan → **Homepage MEA**: cek teks, foto, enam layanan, kutipan → **Simpan**.

## 7. Uji form

1. Dari HP (data seluler), isi form di homepage. Isi nama dengan **`TES - abaikan`** supaya
   lead uji mudah dikenali. Tampil "Terima kasih" dan tombol "Chat tim sales sekarang".
2. wp-admin → **Leads MEA**: lead tampil, status **menunggu**.
3. **Cek IP di balik CDN.** Dari HP yang sama kirim form 6 kali berturut-turut: kiriman ke-6
   ditolak ("Terlalu banyak percobaan…"). Lalu langsung kirim dari laptop di jaringan lain.
   - Laptop **berhasil** → aman.
   - Laptop **ikut ditolak** → semua pengunjung terbaca dari IP yang sama. Pasang mu-plugin di
     `CARA-PASANG.md` bagian 2 ("IP pengunjung di balik CDN") hari itu juga.
4. **Lead uji ikut terkirim ke CDPS** begitu konstanta CDPS diisi (semua lead "menunggu"
   dikirim). Sebelum mengisi konstanta, hapus lead uji lewat hPanel → Databases → phpMyAdmin
   (ganti `wp_` dengan prefix tabel situs, lihat `$table_prefix` di `wp-config.php`):
   ```sql
   DELETE FROM wp_mea_leads WHERE nama LIKE 'TES - abaikan%';
   ```

## 8. Setelah live

- [ ] Uji paritas terhadap snapshot (bila ada):
      ```sh
      php tools/seo-parity/parity.php snapshot --base=https://www.meagency.co.id \
        --urls=snap/produksi/urls.txt --out=snap/sesudah
      php tools/seo-parity/parity.php compare --before=snap/produksi --after=snap/sesudah \
        --out=laporan/produksi --abaikan-sinyal=lang,og_locale
      ```
      Target: 0 beda di luar homepage, 14144, 14512.
- [ ] PageSpeed Insights mobile untuk homepage: LCP < 2,5 dtk, CLS < 0,1, INP < 200 ms.
- [ ] Rich Results Test: homepage, `/jasa-performance-ads/`, `/bellarosa/`.
- [ ] Search Console → Sitemaps → kirim ulang `sitemap_index.xml`. Pantau Pages, 404, dan
      Core Web Vitals setiap minggu selama 2–4 minggu.
- [ ] Leads MEA dicek setiap hari sampai CDPS tersambung. Ekspor CSV tersedia di halaman yang sama.
- [ ] Setelah penerima CDPS produksi siap: hapus lead uji (langkah 7 nomor 4), lalu isi
      konstanta di `wp-config.php` (`CARA-PASANG.md` bagian 2). Lead "menunggu" terkirim
      otomatis dalam satu jam.

## 9. Rollback (1 menit)

- **Tema**: Tampilan → Tema → **Hello Elementor → Aktifkan** → purge cache. Situs kembali persis
  seperti sebelumnya (header/footer Elementor, homepage Elementor). Isian mea-orbit dan lead
  tetap tersimpan untuk dicoba lagi.
- **Halaman 14144 / 14512 saja**: edit halaman → Template → **Default** → Perbarui. Isi
  Elementor lamanya tampil lagi.
- **Plugin**: nonaktifkan mea-leads. Lead tetap tersimpan di database.
- **Darurat total**: pulihkan backup UpdraftPlus dari langkah 1.

## 10. Yang belum diuji di situs sungguhan

Semua sudah lolos di WordPress uji lokal (`docs/qa/`), tetapi hal berikut baru terbukti di
produksi. Langkah 5–8 dirancang untuk menangkapnya:

- Tampilan template Elementor Pro (Single Post, Blog Archive) di bawah header/footer baru.
- Kit Elementor (warna/font global) pada halaman lama tanpa reset Hello Elementor.
- IP pengunjung di balik CDN (langkah 7 nomor 3).
- Kecepatan nyata (PageSpeed) dan animasi di iPhone Safari / Android Chrome.
- Penggabungan JSON-LD tema dengan graph Rank Math (Rich Results Test).
