# DEPLOY 0.2.0 · meagency.co.id

Rilis **mea-orbit 0.2.0** dan **mea-leads 0.2.0**. Isinya: hero kubus v2, strip "Partner resmi",
tracking dari tema, nama PT MEA Digital Marketing, "Layanan terkait", dan tombol hapus lead.
Rinciannya ada di `CHANGELOG.md`, alasannya di `docs/DECISIONS.md`.

Lama pengerjaan: ±20 menit untuk langkah 1–4. Langkah tracking (6) bisa dikerjakan di hari lain.

## 0. Berkas

| Berkas | Untuk |
|---|---|
| `mea-orbit-0.2.0.zip` | Tema baru |
| `mea-leads-0.2.0.zip` | Plugin baru |
| `mea-orbit-0.1.0.zip`, `mea-leads-0.1.0.zip` | Rollback (versi yang dipasang 26 Okt) |
| `tracking/MIGRASI.md`, `tracking/GTM-SETUP.md` | Pemindahan tracking (langkah 6) |

## 1. Backup

1. UpdraftPlus → **Backup Now** (database + file). Tunggu sampai selesai.
2. Simpan `mea-orbit-0.1.0.zip` dan `mea-leads-0.1.0.zip` di komputer untuk rollback.

## 2. Upload

1. Plugin → Tambah Baru → Unggah Plugin → `mea-leads-0.2.0.zip` → **Ganti yang terpasang dengan yang diunggah**.
2. Tampilan → Tema → Tambah Baru → Unggah Tema → `mea-orbit-0.2.0.zip` → **Ganti yang terpasang dengan yang diunggah**.
3. hPanel → Cache Manager → **Purge All**.

Memasang zip ini **tidak** mengubah tracking apa pun. Tracking baru diam sampai konstantanya diisi (langkah 6).

## 3. Lead uji dan sambungan CDPS

Penerima CDPS sudah aktif (tabel dan endpoint sudah di produksi, `WEBSITE_LEAD_SECRET` sudah di Vercel).

1. wp-admin → **Leads MEA**. Centang 5 lead uji (test, text4, Tat3, test2, Test1, 27 Okt 11:15–11:20 WIB) → Aksi massal **Hapus** → Terapkan → OK. Tombol Hapus hanya ada untuk lead berstatus menunggu atau gagal.
2. Vercel → project `agency-app-api` → Settings → Environment Variables → `WEBSITE_LEAD_SECRET` → ikon mata → salin nilainya.
3. hPanel → File Manager → `wp-config.php`, tambahkan di atas baris "That's all, stop editing":

   ```php
   define( 'MEA_LEADS_ENDPOINT', 'https://agency-app-api.vercel.app/api/v1/internal/website-leads' );
   define( 'MEA_LEADS_SECRET', '…' ); // tempel nilai WEBSITE_LEAD_SECRET
   ```

4. Kirim satu lead dari form homepage. Dalam 1–2 menit: Leads MEA menampilkan **terkirim** dengan ID `LEAD-…`; di CDPS lead itu ada di Pool dengan badge "Lead Website".

## 4. Isi konten baru

1. **Partner resmi**: unggah badge Shopee-Certified Enablers Program (PNG) dan foto plakat TikTok Shop Platinum Certified Partner (JPG) ke Media Library, tanpa diedit. Lalu Tampilan → **Homepage MEA** → Partner resmi → Tambah dua baris:

   | Gambar | Alt | Label | Periode |
   |---|---|---|---|
   | badge Shopee | Badge Shopee-Certified Enablers Program Q3 2026 untuk MEA | Shopee-Certified Enablers Program | Q3 2026 |
   | plakat TikTok Shop | Plakat TikTok Shop Platinum Certified Partner Q3 2026 untuk PT MEA Digital Marketing | TikTok Shop Platinum Certified Partner | Q3 2026 |

   Simpan, lalu Purge All. Badge PNG tampil apa adanya; foto plakat otomatis dalam kartu berbingkai. Selama daftar kosong, section tidak tampil.
2. **Layanan terkait**: edit `/jasa-performance-ads/` (14144) → Layanan terkait → tambahkan `/jasa-iklan-shopee/` (7467) dan `/jasa-tiktok-ads/` (16545). Lakukan hal yang sama di `/jasa-live-streaming/` → `/jasa-live-streaming-tiktok/`.

Tiap kuartal: unggah badge/plakat baru, ganti gambar dan periode di Homepage MEA → Partner resmi, Purge All.

## 5. Cek setelah live

- [ ] Homepage 1366 px dan HP: hero kubus v2 tampil (Shopee di atas, dua sisi video), bergerak saat di-scroll.
- [ ] Strip "Partner resmi" tampil di bawah hero (setelah langkah 4.1), gambar utuh.
- [ ] Footer: "© 2026 PT MEA Digital Marketing". Rich Results Test homepage: `legalName` = PT MEA Digital Marketing.
- [ ] `/jasa-performance-ads/` menampilkan section "Layanan terkait".
- [ ] Form: satu lead uji masuk Leads MEA dan CDPS (langkah 3.4).
- [ ] Halaman laporan berpassword tetap `noindex`.

## 6. Tracking (boleh di hari lain)

Ikuti `tracking/MIGRASI.md` langkah 2–9: siapkan GTM sesuai `tracking/GTM-SETUP.md`, lalu pindahkan GTM, GA4, Meta, dan TikTok satu per satu. Konstanta yang diisi di `wp-config.php` (nilainya **tidak** pernah dikirim lewat chat):

```php
define( 'MEA_GTM_ID', 'GTM-5XWWJBW' );
define( 'MEA_META_PIXEL_ID', '1133774585771727' );
define( 'MEA_META_CAPI_TOKEN', '…' );        // token BARU dari Meta Events Manager
define( 'MEA_TIKTOK_PIXEL_ID', 'DBAODNJC77U9NS8CML30' );
define( 'MEA_TIKTOK_EVENTS_TOKEN', '…' );    // token BARU dari TikTok Events Manager
```

Status tiap konstanta dan log kiriman: Pengaturan → **Tracking MEA**.

## 7. Rollback

| Masalah | Langkah |
|---|---|
| Tampilan atau error setelah upload | Upload `mea-orbit-0.1.0.zip` dan `mea-leads-0.1.0.zip` dengan **Ganti yang terpasang**, Purge All. Isian halaman dan opsi tetap (diuji: field dan opsi identik sebelum dan sesudah upgrade) |
| Tracking bermasalah setelah langkah 6 | Rollback per platform sesuai `tracking/MIGRASI.md`. Catatan: rollback tema ke 0.1.0 **mematikan tracking dari tema**, jadi nyalakan lagi sumber lama (Site Kit, plugin Meta, snippet) bila tema di-rollback setelah migrasi tracking |
| Darurat total | Pulihkan backup UpdraftPlus dari langkah 1 |

Data yang ditambahkan rilis ini dan aman ditinggal saat rollback: tabel `wp_mea_orbit_tracking`, opsi `partner_resmi` di Homepage MEA.

## 8. Angka byte homepage (diukur di WordPress lokal, 28 Okt 2026)

| | Desktop 1366 px | HP 390 px | Batas |
|---|---|---|---|
| Motion sebelum `load` | 0 | 0 | 0 |
| Sebelum hero terlewati (poster + frame hero) | 850 KB (poster 50 + 31 frame 800) | 1.087 KB (poster 26 + 60 frame 1.061) | ≤ 1,5 MB |
| Total motion homepage | 3.349 KB | 2.340 KB | ≤ 5 MB / ≤ 2,5 MB |
| CLS | 0,0009 | 0 | — |
| Reduced motion / hemat data | hanya poster (111 KB) | hanya poster (87–89 KB) | — |

Desktop memuat hero sampai tiap frame keempat supaya tetap di bawah 1,5 MB sebelum hero terlewati. Pada tiap frame kedua angkanya 1,66 MB. Itu melewati batas, jadi butuh izin pemilik dulu (`docs/DECISIONS.md` 2026-10-28).
