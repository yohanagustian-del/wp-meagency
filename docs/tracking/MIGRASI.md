# MIGRASI · tracking meagency.co.id pindah ke tema mea-orbit

Tujuan: situs tidak lagi bergantung pada plugin Meta pixel, Code Snippets, WPCode, atau
penempatan tag Site Kit, tanpa event yang hilang atau tercatat dobel.

Prinsip: **satu platform per langkah**, dan di setiap langkah sumber lama dimatikan pada
menit yang sama dengan sumber baru dinyalakan. Setelah setiap langkah: kosongkan cache (situs
tidak memakai plugin cache; cache server dikosongkan di hPanel → Cache Manager → Purge All),
cek, baru lanjut. Langkah 3 sampai 6 bisa dikerjakan dalam satu sesi ±1 jam, atau dipisah per hari.

Acuan: `docs/audit/2026-10-27-tracking.md` (keadaan sekarang), `docs/tracking/PLAN.md`
(rancangan), `docs/tracking/GTM-SETUP.md` (isi kontainer GTM).

## Yang dibutuhkan

| Siapa | Apa |
|---|---|
| Admin WordPress | Akses wp-admin + File Manager hosting (untuk `wp-config.php`) |
| Pemegang GTM | Akses Publish kontainer GTM-5XWWJBW |
| Pemegang Meta Business | Events Manager pixel 1133774585771727 (buat token CAPI baru) |
| Pemegang TikTok Ads | Events Manager pixel DBAODNJC77U9NS8CML30 (buat token Events API baru) |
| Alat | Google Tag Assistant, Meta Pixel Helper, TikTok Pixel Helper, `tools/tracking-audit/audit.sh` |

**Token tidak pernah dikirim lewat chat, email, atau tiket.** Pemegang token mengisinya
sendiri di `wp-config.php` lewat File Manager, atau menyerahkannya lewat pengelola kata sandi
tim.

## Konstanta (di `wp-config.php`, di atas baris "That's all, stop editing")

```php
define( 'MEA_GTM_ID', 'GTM-5XWWJBW' );            // langkah 3
define( 'MEA_META_PIXEL_ID', '1133774585771727' ); // langkah 5
define( 'MEA_META_CAPI_TOKEN', '…' );              // langkah 5, token BARU
define( 'MEA_TIKTOK_PIXEL_ID', 'DBAODNJC77U9NS8CML30' ); // langkah 6
define( 'MEA_TIKTOK_EVENTS_TOKEN', '…' );          // langkah 6, token BARU
```

Konstanta yang tidak ada atau kosong = bagian itu diam. Pixel tanpa token (atau sebaliknya) =
event server platform itu tidak dikirim. Statusnya terlihat di wp-admin → Pengaturan →
**Tracking MEA** (hanya "terisi"/"kosong", nilainya tidak pernah ditampilkan).

## Cek cepat yang dipakai di setiap langkah

```sh
sh tools/tracking-audit/audit.sh > cek.md   # hitungan gtm.js, fbq init, ttq.load per halaman
```

Ditambah GTM Preview / Tag Assistant untuk tag yang dimuat dari dalam GTM (tidak terlihat di
HTML).

---

## Langkah 1 · Pasang tema 0.2.0 dan plugin 0.1.2 (tanpa konstanta)

1. wp-admin → Plugin → Tambah → Unggah `mea-leads.zip` → **Ganti yang terpasang**.
2. Tampilan → Tema → Tambah → Unggah `mea-orbit.zip` → **Ganti yang terpasang**.
3. Kosongkan cache.

**Cek.** `audit.sh`: hitungan sama dengan sebelum pasang (gtm.js 1 dari Site Kit, fbq dari
plugin Meta, ttq dari snippet #11). wp-admin → Pengaturan → Tracking MEA: lima konstanta
"kosong". Kirim satu lead uji: lead masuk Leads MEA dan CDPS seperti biasa.

**Rollback.** Unggah ulang zip versi sebelumnya (0.1.1).

## Langkah 2 · Siapkan kontainer GTM (belum ada perubahan di situs)

1. Isi kontainer sesuai `GTM-SETUP.md`: variabel, trigger, dan tag GA4, Meta, TikTok.
2. **Jeda (Pause)** semua tag baru: klik kanan tag → Pause. Tag yang sudah ada sebelumnya
   tidak disentuh.
3. Submit → Publish, nama versi `Persiapan migrasi tracking (tag baru dijeda)`.

**Cek.** GTM Preview: tag baru berstatus *Paused*, tidak ada yang menyala.

**Rollback.** Tidak perlu; tag dijeda tidak berpengaruh.

## Langkah 3 · GTM dicetak tema, bukan Site Kit

Kerjakan 1 dan 2 berurutan dalam satu menit.

1. `wp-config.php`: tambahkan `define( 'MEA_GTM_ID', 'GTM-5XWWJBW' );`.
2. Site Kit → Settings → Connected services → **Tag Manager** → Edit → matikan
   *Place Tag Manager code* (Site Kit tetap terhubung untuk dashboard).
3. Kosongkan cache.

**Cek.** View-source beranda: komentar `<!-- Google Tag Manager -->` dari tema muncul **satu**
kali, `gtm.js?id=GTM-5XWWJBW` satu kali, komentar Site Kit untuk Tag Manager tidak ada lagi.
`audit.sh`: gtm.js = 1 di semua halaman. Pengaturan → Tracking MEA: `MEA_GTM_ID` "terisi".

**Rollback.** Hapus konstanta, nyalakan lagi *Place Tag Manager code* di Site Kit.

## Langkah 4 · GA4 pindah ke GTM

1. GTM: un-pause `GA4 · Google tag` dan `GA4 · generate_lead` → Publish
   (`GA4 dari GTM`).
2. Site Kit → Settings → **Analytics** → Edit → matikan *Place Google tag*.
3. Kosongkan cache.

**Cek.** Tag Assistant (tidak login): satu `page_view` per halaman ke G-NDEDBPBJYL. GA4 →
Realtime / DebugView: satu kunjungan = satu page_view. View-source: tidak ada lagi
`gtag('config'` dari Site Kit. Saat login sebagai admin: tag GA4 tidak menyala (sama dengan
perilaku Site Kit).

**Rollback.** Pause dua tag GA4 → Publish, nyalakan lagi *Place Google tag* di Site Kit.

## Langkah 5 · Meta: pixel ke GTM, Conversions API ke tema

Persiapan (boleh sehari sebelumnya): Meta Events Manager → pixel 1133774585771727 →
Settings → Conversions API → **Generate access token**. Ini token **baru**; token lama di
Code Snippets #9 tidak dipakai lagi.

Kerjakan 1 sampai 4 berurutan dalam beberapa menit.

1. GTM: un-pause `Meta · Pixel dasar` dan `Meta · Lead` → Publish (`Meta dari GTM`).
2. `wp-config.php`: tambahkan `MEA_META_PIXEL_ID` dan `MEA_META_CAPI_TOKEN` (token baru).
3. Plugin → **nonaktifkan** "Meta pixel for WordPress".
4. Code Snippets → **nonaktifkan** #9 "MEA Pixel Setup (Meta + TikTok)".
5. Kosongkan cache.

**Cek.**

- `audit.sh`: `fbq init` = 0 di HTML (pixel kini dimuat dari GTM).
- Meta Pixel Helper: satu PageView untuk 1133774585771727 per halaman.
- Pengaturan → Tracking MEA → Kirim event uji (Meta, isi Test Event Code dari Events Manager →
  Test events): hasil "Terkirim", event Lead muncul di Test events sebagai event server.
- Kirim satu lead sungguhan dari form. Tracking MEA → log Meta: status **terkirim**, HTTP 200.
  Sekitar 1 jam kemudian: Events Manager → Overview → Lead menampilkan browser + server dengan
  deduplikasi (bukan dua Lead).

**Rollback.** Pause dua tag Meta → Publish, aktifkan lagi plugin Meta dan snippet #9, hapus
dua konstanta Meta.

**Catatan perilaku yang berubah.** Plugin Meta selama ini juga mengirim PageView lewat
Conversions API (server). Sesudah langkah ini PageView hanya dari pixel browser; event server
hanya **Lead**. Bila PageView server tetap dibutuhkan, itu pekerjaan tambahan, bukan bagian
migrasi ini.

## Langkah 6 · TikTok: pixel ke GTM, Events API ke tema

Persiapan: TikTok Events Manager → pixel DBAODNJC77U9NS8CML30 → Settings → Events API →
**Generate Access Token** (token baru).

1. GTM: un-pause `TikTok · Pixel dasar` dan `TikTok · Lead` → Publish (`TikTok dari GTM`).
2. `wp-config.php`: tambahkan `MEA_TIKTOK_PIXEL_ID` dan `MEA_TIKTOK_EVENTS_TOKEN`.
3. Code Snippets → **nonaktifkan** #11 "MEA Browser Pixels (TikTok head)".
4. Kosongkan cache.

**Cek.** `audit.sh`: `ttq.load` = 0 di HTML. TikTok Pixel Helper: satu PageView. Tracking MEA →
Kirim event uji (TikTok) → "Terkirim". Satu lead sungguhan → log TikTok **terkirim**; di
Events Manager → Overview, Lead dari Pixel dan Events API tergabung.

Sebelum langkah ini TikTok Events API memang tidak berjalan (audit bagian 1), jadi event server
TikTok adalah tambahan, bukan pengganti.

**Rollback.** Pause dua tag TikTok → Publish, aktifkan lagi snippet #11, hapus dua konstanta
TikTok.

## Langkah 7 · Lepas Code Snippets dan WPCode

1. Snippet #12 (redirect `/pajak-seller-online-1-juli-2026/` → `/pajak-seller-online-1-oktober-2026/`):
   buat redirect 301 yang sama di Rank Math → Redirections, uji URL lama, lalu nonaktifkan #12.
2. WPCode → Code Snippets → **Header & Footer**: pastikan ketiga kotak kosong. Bila ada isi,
   catat dan putuskan dulu (pindah ke GTM atau buang).
3. Nonaktifkan plugin **Code Snippets** dan **WPCode Lite**.
4. Kosongkan cache, jalankan `audit.sh` sekali lagi: gtm.js 1, fbq init 0, ttq.load 0, ID
   pixel lama 0 di semua halaman.

**Rollback.** Aktifkan lagi plugin yang dibutuhkan.

## Langkah 8 · Bersihkan token lama

Token lama pernah tersimpan sebagai teks biasa di snippet #9, dan disalin snippet itu ke opsi
database plugin Meta serta opsi `tt4b_*`.

1. Hapus snippet #9 (atau seluruh plugin Code Snippets bila sudah tidak dipakai).
2. Hapus plugin "Meta pixel for WordPress" (bukan hanya nonaktif) supaya pengaturannya, termasuk
   token lama, ikut terhapus. Opsi `tt4b_*` tidak dipakai apa pun dan bisa dihapus oleh admin
   (misalnya lewat plugin pengelola opsi, atau minta pengembang).
3. Cabut token lama di Meta Business Settings dan TikTok Events Manager bila opsinya tersedia.

## Langkah 9 · Pantau 7 hari

Bandingkan dengan 7 hari sebelum migrasi:

| Angka | Wajar | Perlu dicek |
|---|---|---|
| GA4 page_view per hari | ±10% | Naik ~2× (tag GA4 dobel) atau turun tajam |
| GA4 `generate_lead` | = jumlah lead di Leads MEA (pengunjung tidak login) | Selisih besar |
| Meta Lead | ≈ jumlah lead, tidak 2× | 2× = deduplikasi gagal (cek nama event dan eventID di tag) |
| TikTok Lead | ≈ jumlah lead | 2× = deduplikasi gagal |
| Tracking MEA → log | Hampir semua **terkirim** | **gagal** HTTP 400/401 = token salah/kedaluwarsa; **menunggu** lama = WP-Cron tidak jalan |

Event server dikirim lewat WP-Cron (percobaan ulang 1 mnt, 5 mnt, 30 mnt, 2 jam, 12 jam, sama
dengan pengiriman lead ke CDPS). Bila hosting memakai `DISABLE_WP_CRON`, pastikan cron server
memanggil `wp-cron.php` minimal setiap 5 menit.
