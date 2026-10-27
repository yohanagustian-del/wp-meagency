# QA · Tracking Fase 1 · 27 Okt 2026

Semua uji di WordPress lokal (WP 7.1.3 + SQLite, `WP_DEBUG` menyala) dengan konstanta berisi
**nilai palsu** dan penerima Meta/TikTok tiruan di localhost (filter
`mea_orbit_tracking_endpoint`, hanya di mu-plugin lokal). Situs live tidak disentuh.

## Unit test (`php tests/run.php`)

91/91 lolos (49 lama + 42 tracking): normalisasi nomor (5 bentuk → `62…`), hash dengan vektor
`sha256sum`, pecah nama, bentuk payload Meta dan TikTok, test code hanya bila diisi, `event_id`
Meta = TikTok = dataLayer, nama/nomor mentah dan `access_token` tidak ada di payload,
klasifikasi respons Meta/TikTok (TikTok selalu HTTP 200 dengan `code` di body), backoff sama
dengan mea-leads, token disamarkan di pesan galat, cookie pixel disaring pola, ID GTM divalidasi,
snippet tanpa `fbq`/`ttq`/`gtag(`.

## Browser

| Kasus | Hasil |
|---|---|
| `/`, `/jasa-performance-ads/` (tidak login) | `gtm.js?id=` 1×, `ns.html?id=` 1× tepat setelah `<body>`, `fbq(` 0, `ttq.` 0, `{"logged_in":false}` sebelum snippet |
| Front-end saat login admin (HTML mentah) | GTM 1×, `{"logged_in":true}` |
| Preview (`/jasa-performance-ads/?preview=true`, login) | GTM 0 |
| Customizer (iframe pratinjau) | GTM 0 |
| `wp-login.php`, `/feed/`, wp-admin | GTM 0 |
| Tanpa konstanta | GTM 0, `logged_in` 0, tanpa notice |
| Token palsu di HTML front-end dan admin (sebelum dan sesudah kirim event uji) | 0 |

## Event server

| Kasus | Hasil |
|---|---|
| Form dengan JavaScript + cookie `_fbp`, `_fbc`, `_ttp` | REST `{"ok":true,"event_id":"e96ce060-…"}`; dataLayer `generate_lead` membawa `event_id` sama; Meta dan TikTok menerima satu event `Lead` masing-masing dengan `event_id` sama; Meta: `ph`/`fn`/`ln`/`external_id` ter-hash (cocok dengan vektor), IP, UA, fbp, fbc; TikTok: `phone` hash E.164, `ttp`, IP, UA; token Meta di body, token TikTok di header `Access-Token` |
| Form tanpa JavaScript (admin-post) | 303 ke halaman asal, event server tetap terbuat untuk kedua platform |
| Penerima 500 | Baris tetap `menunggu`, percobaan 1, `berikutnya_at` +60 dtk, event cron terjadwal |
| Meta 400 code 190 / TikTok 200 code 40001 (pesan memuat token) | `gagal`, galat tersimpan dengan `[token]` menggantikan nilai token |
| Token di tabel antrian (payload + galat) | 0 baris |
| Token di `debug.log` dan log server PHP | 0 |
| Tanpa konstanta | Lead tersimpan dan terkirim ke CDPS seperti biasa, nol baris tracking |
| Admin → Kirim event uji (Meta, TikTok) | Terkirim, `test_event_code` ikut, `event_id` berawalan `uji-` |

Tangkapan layar: `admin-tracking-1440.png` (status konstanta, hasil event uji, log dengan galat
yang sudah disamarkan).

## Paket

- `tools/build-dist.sh`: `mea-orbit.zip` 0.2.0, `mea-leads.zip` 0.1.2, paket produksi kini memuat
  `tracking/` (MIGRASI, GTM-SETUP, PLAN) dan `tools/tracking-audit/`.
- WordPress bersih: kedua zip dipasang ulang lewat upgrader bawaan dengan "Ganti yang
  terpasang". Tema 0.2.0 dan plugin 0.1.2 aktif, tabel `wp_mea_orbit_tracking` dan jadwal sapu
  terbuat, nol notice.
- PHPCS (WordPress standard, file PHP): tema dan plugin bersih.

## Belum terbukti (butuh situs dan akun sungguhan)

- Respons asli Graph API `v24.0` dan TikTok Events API v1.3 terhadap token sungguhan. Admin →
  Kirim event uji adalah cara cek pertamanya (MIGRASI langkah 5 dan 6).
- Deduplikasi browser + server di Events Manager (butuh tag GTM yang sudah dipublish).
- WP-Cron di hosting produksi (sudah dipakai mea-leads untuk CDPS; perilaku sama).
