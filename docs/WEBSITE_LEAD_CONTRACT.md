# Kontrak lead website v1 (`mea.website_lead.v1`)

Pengirim: plugin WordPress `mea-leads` (repo ini). Penerima: CDPS (repo AgencyAPP).
Disepakati pemilik 25 Okt 2026. Bagian 1–3 **mengikat** dan harus sama persis di kedua sisi. Bagian 4 adalah usulan pengirim yang perlu dicocokkan dengan dokumen kontrak di repo CDPS.

## 1. Request

```text
POST {MEA_LEADS_ENDPOINT}
Content-Type: application/json; charset=utf-8
X-MEA-Timestamp: 1761360900
X-MEA-Idempotency-Key: 3f0c8a52-6c1e-4d8a-9b2f-0c4e2a7d9e11
X-MEA-Signature: sha256=<64 karakter hex huruf kecil>
```

| Header | Isi |
|---|---|
| `X-MEA-Timestamp` | Detik Unix (UTC) saat request dikirim. Dibuat baru di setiap percobaan |
| `X-MEA-Idempotency-Key` | UUID v4 per lead. Sama di setiap percobaan untuk lead yang sama, sama dengan `idempotency_key` di body |
| `X-MEA-Signature` | `"sha256=" + hex_huruf_kecil(HMAC-SHA256(secret, "{X-MEA-Timestamp}.{raw_body}"))` |

- `raw_body` adalah byte persis body request (JSON UTF-8). Penerima memverifikasi atas byte mentah, **sebelum** JSON di-parse atau diformat ulang.
- Pengirim membuat `raw_body` sekali saat lead disimpan dan mengirim byte yang sama di setiap percobaan.

## 2. Verifikasi di penerima

1. Tolak bila `|now − X-MEA-Timestamp| > 300` detik.
2. Hitung ulang signature dengan secret yang sama, bandingkan secara constant-time.
3. Gagal di langkah 1 atau 2 → `401`, tidak ada baris apa pun yang ditulis.

## 3. Body

```json
{
  "schema": "mea.website_lead.v1",
  "kanal": "website",
  "idempotency_key": "3f0c8a52-6c1e-4d8a-9b2f-0c4e2a7d9e11",
  "site": "meagency.co.id",
  "submitted_at": "2026-10-19T09:15:00+07:00",
  "nama": "Rina Kusuma",
  "whatsapp": "081234567890",
  "link_toko": "shopee.co.id/namatoko",
  "omzet": "250jt-1m",
  "layanan": "Iklan Shopee dan TikTok Shop",
  "sumber_form": "layanan-performance-ads",
  "halaman_asal": "/jasa-performance-ads/",
  "utm": { "source": null, "medium": null, "campaign": null },
  "persetujuan_dihubungi": true
}
```

| Field | Aturan |
|---|---|
| `schema` | Selalu `mea.website_lead.v1` |
| `kanal` | Selalu `website` |
| `site` | Domain situs pengirim tanpa `www`, misalnya `meagency.co.id` |
| `submitted_at` | ISO 8601 dengan offset `+07:00` |
| `nama` | Minimal 2 karakter setelah trim |
| `whatsapp` | Sudah dinormalisasi: diawali `08`, total 10–13 digit (`^08\d{8,11}$`) |
| `link_toko` | String, boleh kosong `""` |
| `omzet` | Salah satu dari `<100jt`, `100-250jt`, `250jt-1m`, `>1m` |
| `layanan` | String dari daftar form, atau `""` (artinya "Belum tahu, minta saran") |
| `sumber_form` | Misalnya `home`, `layanan-<slug>`, `studi-kasus-<slug>` |
| `halaman_asal` | Path halaman tempat form dikirim |
| `utm` | Objek dengan tiga kunci; nilai string atau `null` |
| `persetujuan_dihubungi` | Selalu `true` (lead tanpa persetujuan tidak dikirim) |

## 4. Respons (usulan pengirim, cocokkan dengan repo CDPS)

| Kode | Arti | Tindakan pengirim |
|---|---|---|
| `200` / `201` | Diterima. Body JSON `{"status": "dibuat" \| "duplikat", "lead_id": "LEAD-YYYYMM-NNNN"}` | Status `terkirim`, simpan `lead_id` bila ada |
| `200` (idempotency key sama) | Hasil pertama dikembalikan lagi | Sama seperti di atas |
| `400`, `422` | Payload tidak valid | Status `gagal`, tanpa retry |
| `401`, `403` | Signature, timestamp, atau secret salah | Status `gagal`, tanpa retry |
| `408`, `409`, `425`, `429`, `5xx`, timeout, galat jaringan | Sementara | Retry: +1 mnt, +5 mnt, +30 mnt, +2 jam, +12 jam, lalu `gagal` |

## 5. Contoh uji (untuk test di kedua sisi)

Vektor uji tetap dengan secret contoh akan ditambahkan di Fase 3 (`tests/run.php`), lengkap dengan `raw_body`, timestamp, dan signature yang diharapkan, supaya penerima bisa memakai vektor yang sama.
