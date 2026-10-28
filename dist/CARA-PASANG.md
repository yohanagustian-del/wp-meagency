# Cara pasang mea-orbit dan mea-leads

Pasang langsung di produksi tanpa staging: ikuti `PASANG-PRODUKSI.md` (di paket produksi:
`README.md`); berkas ini menjadi referensi lengkapnya.

Untuk **staging** meagency.co.id lebih dulu. Produksi hanya setelah staging lolos semua cek
di bagian 6 dan Yohan menyetujui. Acuan lengkap: `docs/PLAN.md` bagian 14 (strategi aman
crawl) dan `docs/ELEMENTOR-EXIT.md`.

| Berkas | Isi |
|---|---|
| `mea-orbit.zip` | Tema mandiri (bukan child theme). Versi terbaru (sama dengan `mea-orbit-<versi>.zip`), WordPress ≥ 6.4, PHP ≥ 8.0 |
| `mea-leads.zip` | Plugin form konsultasi → CDPS. Versi terbaru (sama dengan `mea-leads-<versi>.zip`), WordPress ≥ 6.4, PHP ≥ 8.0 |

Zip dibangun ulang dengan `tools/build-dist.sh`.

---

## 0. Sebelum mulai

1. **Staging.** hPanel → Websites → meagency.co.id → WordPress → Staging → buat staging.
   Di staging, Settings → Reading → **centang** "Discourage search engines".
2. **Backup.** Buat backup produksi di hPanel (file + database).
3. **Snapshot SEO produksi** (sebelum apa pun diubah). Dari komputer dengan PHP 8:
   ```sh
   php tools/seo-parity/parity.php snapshot --base=https://www.meagency.co.id --out=snap/produksi
   ```
   Simpan folder `snap/produksi`. Dipakai lagi di langkah 6 dan 7.
4. Elementor, Elementor Pro, ElementsKit, Unlimited Elements, dan Rank Math **tetap aktif**.
   Tidak ada plugin yang dinonaktifkan di tahap ini.

## 1. Pasang tema dan plugin (staging)

1. Tampilan → Tema → Tambah Baru → Unggah Tema → `mea-orbit.zip` → Pasang → **Aktifkan**.
2. Plugin → Tambah Baru → Unggah Plugin → `mea-leads.zip` → Pasang → **Aktifkan**.
   Aktivasi membuat tabel `wp_mea_leads` dan menjadwalkan cron (sapuan per jam, retensi harian).
3. Bersihkan cache (LiteSpeed Cache → Purge All, dan cache CDN Hostinger bila aktif).

Yang langsung terjadi:

- Header dan footer seluruh situs memakai header/footer mea-orbit. Header New (10093) dan
  Footer New (17281) Elementor Pro tidak tampil lagi.
- Artikel dan arsip blog tetap memakai Single Post Template (13811) dan Blog Archive
  Template (14400) Elementor Pro. Isinya tidak berubah.
- Homepage (halaman depan statis 13411) langsung memakai `front-page.php`. Isi Elementor
  halaman 13411 tidak dipakai lagi, tetapi tidak dihapus.
- Laporan klien berpassword: form password bergaya mea-orbit; proteksi dan noindex WPCode tetap.

## 2. Konstanta di `wp-config.php` (staging)

Tambahkan di atas baris `/* That's all, stop editing! */`:

```php
// mea-leads: penerima lead CDPS (diisi setelah penerima CDPS staging siap).
define( 'MEA_LEADS_ENDPOINT', 'https://…/website-lead' );
define( 'MEA_LEADS_SECRET', '…' ); // Secret staging, dikirim lewat saluran privat. Jangan lewat chat.
```

- Tanpa dua konstanta ini, form tetap jalan: lead tersimpan dengan status **menunggu** dan
  tidak ada request keluar. Setelah konstanta diisi, sapuan per jam mengirim semuanya.
- WordPress hanya memegang HMAC secret. **Jangan pernah** menaruh service-role key Supabase
  di WordPress.
- Opsional:
  - `define( 'MEA_LEADS_SITE', 'meagency.co.id' );` nilai `site` di payload. Default: domain
    situs tanpa `www.` (di staging berarti domain staging, sehingga lead uji terbedakan).
  - `define( 'MEA_LEADS_HAPUS_SAAT_UNINSTALL', true );` hanya bila tabel lead memang boleh
    ikut terhapus saat plugin di-uninstall. Default: tabel **tidak** dihapus.

**WP-Cron.** Bila `DISABLE_WP_CRON` bernilai `true` di `wp-config.php`, buat cron job di hPanel
(Advanced → Cron Jobs), tiap 5 menit:

```sh
wget -q -O - "https://<domain>/wp-cron.php?doing_wp_cron" >/dev/null 2>&1
```

**IP pengunjung di balik CDN.** Batas laju form (5 kiriman per 10 menit per IP) membaca
`REMOTE_ADDR`. Kirim satu form di staging, lalu buka wp-admin → Leads MEA. Bila semua
pengunjung terlihat dari IP CDN yang sama, buat mu-plugin
`wp-content/mu-plugins/mea-leads-ip.php` yang membaca header IP asli dari CDN (nama header
dipastikan dulu di staging, mis. `CF-Connecting-IP` untuk Cloudflare):

```php
<?php
add_filter( 'mea_leads_client_ip', function ( $ip ) {
	$asli = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? '';
	return filter_var( $asli, FILTER_VALIDATE_IP ) ? $asli : $ip;
} );
```

**Cache.** Pastikan `/wp-json/mea-leads/*` dan `/wp-admin/admin-post.php` tidak di-cache
(responsnya sudah mengirim `Cache-Control: no-store`). Halaman yang memuat form boleh di-cache.

## 3. Isi konten

### 3.1 Homepage

Tampilan → **Homepage MEA**. Semua isian sudah berisi copy prototipe sebagai nilai bawaan
(studi kasus unggulan 14512, foto pimpinan 15633, mosaik 15630/15642/16518/16517/16519,
enam layanan, kutipan). Cek, ubah bila perlu, lalu **Simpan**. Daftar kutipan Yohan ada di
halaman yang sama (teks final; jangan diedit selain typo).

### 3.2 Memilih template per halaman

Halaman → edit halaman → panel **Atribut Halaman / Template**:

| Halaman | Template |
|---|---|
| `/jasa-performance-ads/` (14144) | **MEA Orbit · Layanan** |
| `/bellarosa/` (14512) | **MEA Orbit · Studi kasus** |

Simpan dulu, lalu meta box **Konten MEA Orbit** muncul di bawah editor. Isi awalnya ada di
`docs/seed/page-14144-jasa-performance-ads.json` dan `docs/seed/page-14512-bellarosa.json`.
Cara tercepat: REST dengan Application Password (Pengguna → Profil → Application Passwords):

```sh
curl -u 'admin:xxxx xxxx xxxx xxxx' -H 'Content-Type: application/json' \
  -X POST https://<staging>/wp-json/wp/v2/pages/14144 \
  --data @docs/seed/page-14144-jasa-performance-ads.json
```

Halaman layanan dan studi kasus lain bisa dipindah ke template Orbit dengan cara yang sama,
satu per satu. Template layanan harus berisi **5 langkah** "Cara kerja" supaya cocok dengan
lima stasiun motion M3. Angka studi kasus hanya tampil bila **Izin publikasi** dicentang;
angka yang ditulis di H1, lede, dan cerita harus diedit manual bila izin dicabut.

### 3.3 Alt text

`docs/seed/media-alt.json` berisi alt per ID lampiran (Media → edit lampiran). Foto 15633:
"Yohan Agustian, CEO, dan Nerissa, COO MEA Agency, di kantor MEA".

### 3.4 Menu

Tampilan → Menu:

- Lokasi **Menu utama (header)**: Layanan, Studi kasus, Tim, Cara kerja. Selama kosong, tema
  memakai isi cadangan yang sama.
- Lokasi **Menu footer (kolom Layanan)**: enam halaman layanan, Studi kasus, Blog. Sebelum langkah
  ini, data link di Header New dan Footer New Elementor; link penting yang belum ada
  ditambahkan ke menu footer supaya link internal sitewide tidak berkurang.

### 3.5 Rank Math

Title dan meta description 14144 dan 14512 masih teks lama ("brand baru", iklan Google/Bing).
Perbarui di editor halaman → Rank Math. Tema tidak mencetak title, meta, canonical, atau sitemap.

## 4. Form dan CDPS

1. Kirim satu form dari homepage dan satu dari `/jasa-performance-ads/`.
2. wp-admin → **Leads MEA** (Editor dan Administrator; ubah lewat filter `mea_leads_capability`): lead tampil. Tanpa konstanta statusnya **menunggu**; dengan
   konstanta menjadi **terkirim** dan kolom ID lead CDPS (`LEAD-YYYYMM-NNNN`) terisi.
3. Cek di CDPS staging: lead masuk Pool dengan badge "Lead Website".
4. Riwayat pengiriman per lead: klik **Riwayat**. Kirim ulang: per baris atau aksi massal.
5. Kontrak dan vektor uji HMAC: `docs/WEBSITE_LEAD_CONTRACT.md`.

## 5. Uji paritas SEO (staging)

```sh
php tools/seo-parity/parity.php snapshot --base=https://<staging> \
  --urls=snap/produksi/urls.txt --out=snap/staging [--auth=user:pass]
php tools/seo-parity/parity.php compare --before=snap/produksi --after=snap/staging \
  --out=laporan/staging --abaikan-sinyal=lang,og_locale
```

Baca `laporan/staging/ringkasan.md`. Syarat lanjut: **0 URL berbeda** di status, canonical,
robots, title, description, H1, teks, link isi, gambar, dan JSON-LD, kecuali tiga halaman
Orbit (homepage, 14144, 14512) yang memang didesain ulang dan dicek manual. Robots di staging
akan berbeda (`noindex` dari "Discourage search engines"); itu wajar dan tidak ikut ke
produksi.

## 6. Cek sebelum ke produksi

- [ ] PageSpeed mobile homepage: LCP < 2,5 dtk, CLS < 0,1, INP < 200 ms.
- [ ] Rich Results Test: homepage (Organization, WebSite), 14144 (Service, FAQPage,
      BreadcrumbList), 14512 (Article, BreadcrumbList).
- [ ] Form → Leads MEA → CDPS (bagian 4).
- [ ] Scroll hero dan Cara kerja mulus di iPhone Safari dan Android Chrome.
- [ ] Reduced motion (Pengaturan aksesibilitas HP): hanya poster.
- [ ] Contoh artikel, halaman Elementor lama, dan satu laporan berpassword tampil benar.
- [ ] Laporan paritas bagian 5 bersih.
- [ ] Persetujuan Yohan.

## 7. Produksi

1. Snapshot produksi baru (bagian 0 nomor 3) bila snapshot lama sudah lebih dari seminggu.
2. Jam sepi. Ulangi bagian 1–3 di produksi (konstanta produksi dengan secret produksi).
   Pastikan "Discourage search engines" **tidak** dicentang.
3. Purge semua cache.
4. Uji paritas produksi terhadap snapshot "sebelum" (perintah bagian 5 dengan `--base`
   produksi).
5. Search Console: kirim ulang `sitemap_index.xml`; pantau Pages, 404, dan Core Web Vitals
   selama 2–4 minggu sebelum Fase 6 (konversi artikel Elementor).

## 8. Rollback

- **Tema**: Tampilan → Tema → aktifkan **Hello Elementor**. Header/footer Elementor Pro dan
  isi Elementor homepage kembali seperti semula. Field mea-orbit, opsi Homepage MEA, dan
  lead tetap tersimpan. Purge cache.
- **Plugin**: Plugin → nonaktifkan mea-leads. Tabel dan lead tetap ada; cron dihapus. Tanpa
  plugin, bagian konsultasi di tema menampilkan tombol WhatsApp.
- Konstanta di `wp-config.php` boleh dibiarkan; tidak berpengaruh bila plugin nonaktif.

## 9. Memperbarui

Unggah zip versi baru lewat jalur yang sama (Tema/Plugin → Unggah). WordPress menawarkan
"Ganti yang sekarang dengan yang diunggah". Data, opsi, dan lead tidak hilang.
