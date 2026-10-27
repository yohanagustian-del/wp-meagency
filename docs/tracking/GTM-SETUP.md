# GTM-SETUP · kontainer GTM-5XWWJBW

Panduan isi kontainer GTM setelah tracking pindah ke tema mea-orbit (`docs/tracking/PLAN.md`).
Tema hanya mencetak snippet GTM. Semua tag browser (GA4, Meta Pixel, TikTok Pixel) ada di sini.
Event Lead dari server dikirim tema langsung ke Meta dan TikTok, dengan `event_id` yang sama
dengan yang didorong form ke `dataLayer`. Itu yang membuat event browser dan server digabung
(deduplikasi), bukan terhitung dua kali.

Urutan publish ada di `MIGRASI.md`. Dokumen ini hanya isi kontainernya.

## 0. Sebelum menambah apa pun

Buka GTM → Workspace → **Tags** dan catat yang sudah ada. Bila sudah ada tag Google tag/GA4
untuk G-NDEDBPBJYL, tag Meta Pixel, atau tag TikTok, **jangan buat yang baru**: sesuaikan tag
itu dengan isi di bawah. Satu pixel = satu tag dasar. Tag dasar kedua untuk ID yang sama adalah
sumber PageView dobel.

## 1. Data yang tersedia di dataLayer

Tema dan form mendorong dua objek. Tidak ada data pribadi di dataLayer.

```js
// Di <head>, sebelum snippet GTM (setiap halaman front-end)
{ logged_in: false }

// Setelah form konsultasi sukses terkirim
{ event: 'generate_lead', form_source: 'layanan-performance-ads', lead_omzet: '250jt-1m',
  event_id: 'e96ce060-374a-4d0f-8cc9-624dccdf7a59' }
```

`event_id` adalah `idempotency_key` lead di mea-leads. Nilai yang sama dikirim tema ke Meta
Conversions API dan TikTok Events API.

## 2. Variabel (Variables → User-Defined → New → Data Layer Variable)

| Nama variabel | Data Layer Variable Name | Default |
|---|---|---|
| `DLV - logged_in` | `logged_in` | `false` |
| `DLV - event_id` | `event_id` | kosong |
| `DLV - form_source` | `form_source` | kosong |
| `DLV - lead_omzet` | `lead_omzet` | kosong |

## 3. Trigger

| Nama trigger | Tipe | Kondisi |
|---|---|---|
| `Semua halaman` | Initialization | All Initialization Events |
| `Semua halaman · bukan login` | Initialization | Some events: `DLV - logged_in` equals `false` |
| `Lead terkirim` | Custom Event | Event name `generate_lead` |
| `Lead terkirim · bukan login` | Custom Event | Event name `generate_lead`, `DLV - logged_in` equals `false` |

**Pengguna login.** Site Kit selama ini tidak menghitung pengguna login di GA4. Trigger
"bukan login" menjaga perilaku itu untuk GA4. Meta dan TikTok tetap memakai trigger untuk semua
pengunjung, sama seperti plugin Meta dan snippet TikTok sekarang.

## 4. Tag

### 4.1 GA4

| Tag | Tipe | Isi | Trigger |
|---|---|---|---|
| `GA4 · Google tag` | Google Tag | Tag ID `GT-M6B9W446` (Google tag yang sekarang dipakai Site Kit, tujuannya G-NDEDBPBJYL) | `Semua halaman · bukan login` |
| `GA4 · generate_lead` | Google Analytics: GA4 Event | Measurement ID `G-NDEDBPBJYL`, event name `generate_lead`, parameter `form_source` = `{{DLV - form_source}}`, `lead_omzet` = `{{DLV - lead_omzet}}` | `Lead terkirim · bukan login` |

Cek di Google tag settings bahwa `GT-M6B9W446` memang mengarah ke G-NDEDBPBJYL. Bila Anda lebih
suka ID G- langsung, pakai `G-NDEDBPBJYL` di tag Google tag. Pilih salah satu saja.

### 4.2 Meta Pixel 1133774585771727

**`Meta · Pixel dasar`**: Custom HTML, trigger `Semua halaman`, Tag firing options: *Once per page*.

```html
<script>
!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
document,'script','https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '1133774585771727');
fbq('track', 'PageView');
</script>
```

**`Meta · Lead`**: Custom HTML, trigger `Lead terkirim`. Tag Sequencing → *Fire a tag before*:
`Meta · Pixel dasar`.

```html
<script>
fbq('track', 'Lead',
  { lead_source: {{DLV - form_source}}, omzet: {{DLV - lead_omzet}} },
  { eventID: {{DLV - event_id}} });
</script>
```

Nama event (`Lead`) dan `eventID` harus persis sama dengan event server. Jangan ganti ke
`SubmitForm`, `Contact`, atau nama lain.

### 4.3 TikTok Pixel DBAODNJC77U9NS8CML30

**`TikTok · Pixel dasar`**: Custom HTML, trigger `Semua halaman`, *Once per page*. Isinya
kode dasar TikTok Pixel yang sekarang ada di Code Snippets #11 (salin dari sana; ID pixel
`DBAODNJC77U9NS8CML30`, diakhiri `ttq.page();`). Kode pixel bukan rahasia; yang rahasia hanya
token Events API, dan token itu tidak pernah masuk GTM.

**`TikTok · Lead`**: Custom HTML, trigger `Lead terkirim`. Tag Sequencing → *Fire a tag
before*: `TikTok · Pixel dasar`.

```html
<script>
ttq.track('Lead',
  { content_name: {{DLV - form_source}} },
  { event_id: {{DLV - event_id}} });
</script>
```

Nama event server TikTok juga `Lead`. Audit sebelumnya menyarankan `SubmitForm`; itu batal,
karena deduplikasi TikTok butuh nama event dan `event_id` yang sama di browser dan server.

## 5. Yang tidak boleh ada di kontainer

- Token Meta CAPI atau TikTok Events API (tag "Conversions API Gateway" atau server-side
  tagging tidak dipakai).
- Tag kedua untuk ID pixel atau GA4 yang sama.
- Tag yang memicu Meta `Lead` / TikTok `Lead` dari klik tombol atau kunjungan halaman terima
  kasih. Satu-satunya pemicu Lead adalah event `generate_lead` dari form.

## 6. Cara cek (Preview / Tag Assistant)

1. GTM → **Preview** → buka `https://www.meagency.co.id/jasa-performance-ads/`.
2. Pada *Initialization*: `GA4 · Google tag` (bila tidak login), `Meta · Pixel dasar`,
   `TikTok · Pixel dasar` masing-masing **satu kali**.
3. Kirim form uji. Pada event `generate_lead`: `GA4 · generate_lead`, `Meta · Lead`,
   `TikTok · Lead` masing-masing satu kali, dan variabel `DLV - event_id` berisi UUID.
4. **Event browser** terlihat langsung di Meta Events Manager → Test events (buka situs dari
   tombol di sana) dan TikTok Events Manager → Test events.
5. **Event server** dari lead sungguhan tidak membawa Test Event Code, jadi tidak muncul di
   Test events. Untuk menguji jalur server, pakai wp-admin → Pengaturan → **Tracking MEA** →
   Kirim event uji (isi Test Event Code). Untuk lead sungguhan, cek sekitar 1 jam kemudian:
   Meta → Overview → event Lead → kolom browser + server dan status deduplikasi; TikTok →
   Overview → Lead → sumber Pixel + Events API. wp-admin → Tracking MEA menampilkan status
   kirimannya (terkirim / menunggu / gagal) dan `event_id`-nya.
6. Hapus lead uji di wp-admin → Leads MEA, atau tandai sebagai uji di CDPS.
