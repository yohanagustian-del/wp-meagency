# Rencana: tracking pindah ke tema mea-orbit

Fase 0 · 27 Okt 2026. Acuan: `docs/audit/2026-10-27-tracking.md`. Keputusan pemilik: tracking
dipasang tema, tanpa plugin tracking. Tidak ada yang disentuh di situs live.

**Status: Fase 1 selesai** (tema 0.2.0, mea-leads 0.1.2). Hasil uji: `docs/qa/tracking-fase-1/QA.md`.
Panduan: `GTM-SETUP.md`, `MIGRASI.md`. Keputusan atas bagian 8 dan penyesuaian kecil saat
Fase 1 ada di bagian 10.

## 1. Arsitektur singkat

```text
Browser                                  Server (WordPress)
───────                                  ──────────────────
tema: snippet GTM (head + noscript)      mea-leads: lead tersimpan
   └─ GTM-5XWWJBW                           └─ do_action('mea_leads_lead_saved', $id, $konteks)
        ├─ GA4                                    └─ tema (inc/tracking.php):
        ├─ Meta Pixel  ─┐                              susun payload Meta + TikTok (data di-hash),
        └─ TikTok Pixel ┤ event_id = idempotency_key   simpan ke antrian, kirim async (WP-Cron),
form.js: dataLayer.push(generate_lead,    ┘            retry dengan backoff yang sama dengan CDPS
          event_id)                               ├─ Meta Conversions API  (event Lead)
                                                  └─ TikTok Events API     (event Lead)
```

Satu event lead = satu `event_id` (UUID `idempotency_key` milik lead, sudah ada di mea-leads)
di tiga tempat: `dataLayer`, pixel browser (diteruskan GTM), dan event server. Meta dan
TikTok membuang duplikat bila `event_name` + `event_id` sama.

## 2. File

| File | Isi |
|---|---|
| `themes/mea-orbit/inc/tracking.php` | Snippet GTM (head + `wp_body_open`), listener `mea_leads_lead_saved`, antrian + pengiriman server, retensi |
| `themes/mea-orbit/inc/tracking-payload.php` | Fungsi murni tanpa WordPress (normalisasi nomor, hash, payload Meta dan TikTok), supaya bisa diuji `tests/run.php` |
| `themes/mea-orbit/inc/tracking-admin.php` | Pengaturan → Tracking MEA |
| `plugins/mea-leads/includes/form.php` | Panggil `do_action( 'mea_leads_lead_saved', … )` setelah lead tersimpan, dan kembalikan `event_id` ke JS |
| `plugins/mea-leads/includes/rest.php` | Respons sukses membawa `event_id` |
| `plugins/mea-leads/assets/js/form.js` | `dataLayer.push` menambah `event_id` |
| `tests/run.php` | Kasus baru (bagian 7) |
| `docs/tracking/GTM-SETUP.md`, `docs/tracking/MIGRASI.md` | Panduan GTM dan urutan migrasi |

Tidak ada dependency baru. Satu tabel baru milik tema (bagian 4).

## 3. Browser (Target 1, 2, 5)

- Konstanta `MEA_GTM_ID` (format `GTM-XXXX`, divalidasi pola). Kosong/tidak ada → tidak
  mencetak apa pun.
- `wp_head` prioritas 1: snippet GTM standar. `wp_body_open`: `<noscript><iframe …></noscript>`.
  `header.php` tema sudah memanggil `wp_body_open`.
- Tidak dicetak di: wp-admin (hook front-end saja), halaman login (`wp-login.php` tidak memanggil
  `wp_head`), preview (`is_preview()`), Customizer (`is_customize_preview()`), embed, dan feed.
- Tema **tidak** mencetak Meta Pixel, TikTok Pixel, atau GA4. Ketiganya jadi tag di GTM
  (`GTM-SETUP.md`).
- `dataLayer` menerima `{ event: 'generate_lead', event_id, form_source, lead_omzet }`.
  `event_id` diambil dari respons REST form. Jalur tanpa JavaScript tidak punya `dataLayer`;
  event server tetap terkirim.

## 4. Server (Target 3)

**Pemicu.** `mea_leads_handle_submission()` memanggil
`do_action( 'mea_leads_lead_saved', $id, $konteks )` tepat setelah `mea_leads_queue()`. Jalur
spam, invalid, sesi, dan batas tidak memicu apa pun. Konteks dikumpulkan saat request (cron
tidak punya data ini):

| Kunci | Sumber |
|---|---|
| `event_id` | `idempotency_key` lead |
| `event_time` | waktu simpan (Unix) |
| `event_source_url` | `home_url( halaman_asal )` |
| `nama`, `whatsapp` | data lead tervalidasi (hanya untuk di-hash di listener, tidak disimpan mentah oleh tema) |
| `persetujuan` | `true` (form menolak tanpa centang) |
| `ip` | `mea_leads_client_ip()` (filter CDN yang sama) |
| `user_agent` | `HTTP_USER_AGENT` |
| `fbp`, `fbc` | cookie `_fbp`, `_fbc` |
| `ttp`, `ttclid` | cookie `_ttp`, `ttclid` |

**Antrian.** Listener tema langsung menyusun payload per platform (data pribadi sudah di-hash)
dan menyimpannya di tabel `{prefix}mea_orbit_tracking`:
`id, platform (meta|tiktok), lead_id, event_id, payload (JSON tanpa token), status (menunggu|terkirim|gagal),
percobaan, berikutnya_at, kode_http, galat (≤200 karakter, tanpa token), created_at, updated_at`.
Satu baris per platform yang konstantanya lengkap. Platform tanpa konstanta tidak dibuatkan baris.

"Antrian yang sama dengan CDPS" saya artikan sebagai **mekanisme yang sama**: WP-Cron single
event per baris + sapuan per jam, jadwal backoff dan klasifikasi respons memakai fungsi
mea-leads (`mea_leads_next_delay()`, `mea_leads_classify_response()`: 1 mnt, 5 mnt, 30 mnt,
2 jam, 12 jam, lalu gagal), dan kunci baris supaya tidak terkirim dua kali. Tabelnya terpisah
dari `wp_mea_leads` supaya plugin tidak tahu soal Meta/TikTok dan status CDPS tidak tercampur.

**Meta Conversions API.** `POST https://graph.facebook.com/{versi}/{MEA_META_PIXEL_ID}/events`,
token di body (`access_token`), bukan di URL, supaya tidak masuk log server/proxy.

```json
{ "data": [ {
    "event_name": "Lead", "event_time": 1761360900, "event_id": "<uuid>",
    "action_source": "website", "event_source_url": "https://www.meagency.co.id/jasa-performance-ads/",
    "user_data": { "ph": ["<sha256 628…>"], "fn": ["<sha256>"], "ln": ["<sha256>"],
                   "external_id": ["<sha256 event_id>"], "client_ip_address": "…",
                   "client_user_agent": "…", "fbp": "…", "fbc": "…" },
    "custom_data": { "lead_source": "layanan-performance-ads", "omzet": "250jt-1m" } } ],
  "test_event_code": "TEST123" }
```

**TikTok Events API.** `POST https://business-api.tiktok.com/open_api/v1.3/event/track/`,
header `Access-Token`.

```json
{ "event_source": "web", "event_source_id": "<MEA_TIKTOK_PIXEL_ID>", "test_event_code": "…",
  "data": [ { "event": "Lead", "event_time": 1761360900, "event_id": "<uuid>",
    "user": { "phone": "<sha256 +628…>", "external_id": "<sha256 event_id>",
              "ttp": "…", "ttclid": "…", "ip": "…", "user_agent": "…" },
    "page": { "url": "https://www.meagency.co.id/jasa-performance-ads/" },
    "properties": { "content_name": "layanan-performance-ads" } } ] }
```

**Hash.** SHA-256 heksadesimal huruf kecil setelah normalisasi:

| Data | Meta | TikTok |
|---|---|---|
| Nomor | `08…` → `628…`, hanya angka | `+628…` (E.164) |
| Nama | `fn` kata pertama, `ln` sisanya; huruf kecil, tanpa tanda baca | tidak dikirim |
| external_id | hash `event_id` | hash `event_id` |
| IP, user agent, fbp/fbc, ttp/ttclid | tidak di-hash (sesuai spesifikasi) | tidak di-hash |

**Retensi.** Baris tracking dihapus 90 hari setelah dibuat, sejalan dengan retensi mea-leads.

**Token.** Hanya dibaca dari konstanta saat request dikirim. Tidak disimpan di tabel, tidak
dicetak di HTML/admin, dan pesan galat dibersihkan dari nilai token sebelum disimpan.

## 5. Admin (Target 4)

Pengaturan → **Tracking MEA** (`manage_options`):

- Status lima konstanta: "terisi" / "kosong" (format GTM juga dicek), tanpa menampilkan nilai.
- Log 50 kiriman server terakhir per platform: waktu, event_id, status, kode HTTP, percobaan,
  galat singkat. Tanpa data pribadi.
- Form "Kirim event uji": pilih platform + isi Test Event Code (Meta Events Manager → Test
  events; TikTok Events Manager → Test events). Mengirim satu event `Lead` sintetis langsung
  (tanpa antrian), hasilnya tampil di halaman. Test code tidak disimpan.

## 6. Konstanta

```php
define( 'MEA_GTM_ID', 'GTM-5XWWJBW' );
define( 'MEA_META_PIXEL_ID', '…' );
define( 'MEA_META_CAPI_TOKEN', '…' );
define( 'MEA_TIKTOK_PIXEL_ID', '…' );
define( 'MEA_TIKTOK_EVENTS_TOKEN', '…' );
```

Meta butuh pixel + token, TikTok butuh pixel + token. Salah satu kosong → platform itu diam.

## 7. Test (`tests/run.php`, tanpa WordPress)

- Normalisasi nomor: `0812…`, `+62 812…`, `62812…`, `812…` → `62812…` (Meta) / `+62812…` (TikTok).
- Hash: vektor tetap SHA-256 (nilai dicek dengan `sha256sum`).
- Nama: `" Rina  Kusuma "` → fn `rina`, ln `kusuma`; satu kata → tanpa `ln`.
- Bentuk payload Meta dan TikTok (kunci, `event_name`/`event`, `action_source`, test code
  hanya bila diisi, field kosong tidak dikirim).
- `event_id` payload Meta = payload TikTok = nilai yang dikembalikan ke `dataLayer`.
- Token tidak pernah muncul di payload tersimpan maupun di pesan galat yang dibersihkan.

Uji lokal tambahan: view-source dengan `MEA_GTM_ID` contoh (tepat satu snippet GTM, tidak ada
`fbq`/`ttq` dari tema), tanpa konstanta (tanpa notice), lead uji ke penerima tiruan Meta/TikTok
di localhost (bentuk request, header, retry 500 → backoff).

## 8. Yang perlu dikonfirmasi saat Fase 1

1. **Versi Graph API.** Default diset ke versi yang masih didukung saat Fase 1 dikerjakan,
   bisa diganti lewat filter `mea_orbit_meta_api_version`.
2. **Nama event TikTok.** Rencana: `Lead`, sama dengan Meta (audit sebelumnya menyebut
   `SubmitForm`). Yang penting: tag TikTok di GTM memakai nama yang sama, karena deduplikasi
   butuh nama event + `event_id` yang sama.
3. **Pengguna login.** Site Kit sekarang tidak melacak pengguna login untuk GA4. Rencana: GTM
   tetap dimuat untuk semua pengunjung front-end; pengecualian pengguna login diatur di GTM
   (variabel `logged_in` di `dataLayer`), supaya HTML tetap sama untuk semua orang.
4. **ttclid.** Cookie `ttclid` hanya ada bila pixel TikTok menyimpannya. Bila perlu lebih
   lengkap, form harus membawa `ttclid` dari URL; itu mengubah form, jadi tidak dilakukan
   tanpa izin.
5. **Jalur form tanpa JavaScript** tidak menghasilkan event browser, hanya event server.
   Itu wajar, karena tanpa JavaScript GTM juga tidak jalan.

## 9. Ringkasan MIGRASI.md (ditulis lengkap di Fase 1)

Prinsip: setiap platform dipindah satu per satu dalam satu jendela waktu singkat, supaya tidak
pernah ada saat tanpa tracking atau dengan tracking ganda.

1. Pasang tema baru **tanpa** konstanta → tidak ada perubahan.
2. Siapkan semua tag di workspace GTM, **belum dipublish**.
3. GTM: isi `MEA_GTM_ID` dan matikan snippet Tag Manager di Site Kit pada menit yang sama.
4. GA4: publish tag GA4 di GTM dan matikan snippet Analytics di Site Kit bersamaan.
5. Meta: publish tag Meta di GTM, isi konstanta Meta, nonaktifkan plugin Meta dan Code
   Snippets #9 bersamaan. Cek Test Events: satu Lead, browser + server tergabung.
6. TikTok: publish tag TikTok, isi konstanta TikTok, nonaktifkan #11. Cek test mode.
7. Pindahkan redirect #12 ke Rank Math, cek WPCode Header & Footer kosong, lalu nonaktifkan
   Code Snippets dan WPCode.
8. Buat token CAPI dan Events API baru (yang lama pernah tersimpan di snippet), isi ulang
   konstanta, cabut token lama.

## 10. Keputusan Fase 1

1. **Versi Graph API:** `v24.0` (konstanta `MEA_ORBIT_META_API_VERSION`), bisa diganti lewat
   filter `mea_orbit_meta_api_version` tanpa rilis tema.
2. **Nama event TikTok:** `Lead`, sama dengan Meta. `GTM-SETUP.md` memakai nama yang sama.
3. **Pengguna login:** GTM dimuat untuk semua pengunjung front-end. Tema mendorong
   `{ logged_in: true|false }` ke dataLayer sebelum snippet GTM; GA4 di GTM memakai trigger
   "bukan login" (perilaku Site Kit tetap), Meta dan TikTok untuk semua pengunjung.
4. **ttclid:** hanya dari cookie `ttclid`. Form tidak diubah.
5. **Tanpa JavaScript:** event server tetap terkirim (diuji lewat admin-post).

Penyesuaian kecil terhadap rancangan:

- **Urutan hook.** `mea_leads_lead_saved` dipanggil tepat **sebelum** `mea_leads_queue()`, bukan
  sesudahnya, supaya event tracking sudah terjadwal saat antrian CDPS memicu WP-Cron. Tema
  juga memicu WP-Cron di `shutdown` (kunci bawaan WordPress mencegah pemicuan dobel).
- **Cookie** `_fbp`, `_fbc`, `_ttp`, `ttclid` dibaca tema, bukan plugin, supaya mea-leads tetap
  tidak tahu soal Meta/TikTok. Konteks dari plugin: `event_id`, `event_time`,
  `event_source_url`, `nama`, `whatsapp`, `sumber_form`, `omzet`, `layanan`, `ip`, `user_agent`.
- **Nama** untuk Meta: tanda baca (termasuk tanda hubung) jadi pemisah kata, lalu dibuang;
  `Nur-Aini` → `nur aini`.
- **Klasifikasi respons** per platform (`mea_orbit_tracking_classify()`), bukan
  `mea_leads_classify_response()`: TikTok selalu menjawab HTTP 200 dan menaruh galat di `code`,
  dan Meta memberi tanda rate limit di body 400. Jadwal backoff sama persis dengan mea-leads
  (diuji).
- **Tabel** punya kunci unik `(platform, event_id)` supaya satu lead tidak pernah menghasilkan
  dua baris untuk platform yang sama.
- **Filter uji** `mea_orbit_tracking_endpoint` mengganti alamat tujuan, hanya untuk penerima
  tiruan lokal.
- **Perilaku yang berubah setelah migrasi:** plugin Meta selama ini juga mengirim PageView lewat
  Conversions API. Sesudah migrasi, event server hanya Lead (dicatat di `MIGRASI.md` langkah 5).

