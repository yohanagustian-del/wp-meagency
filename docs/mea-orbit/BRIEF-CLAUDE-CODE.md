# Brief Claude Code — MEA Agency "MEA Orbit"

Situs: https://www.meagency.co.id (WordPress, Hostinger, tema aktif Hello Elementor)
Pemilik keputusan: Yohan Agustian (CEO). Semua perubahan ke produksi lewat staging dulu.
Prototipe acuan (folder ini): `index.html` (homepage), `layanan.html` (template layanan), `studi-kasus.html` (template studi kasus).

---

## 1. Keputusan yang sudah dikunci

- Konsep visual: gabungan **Growth Orbit + Quiet Luxe + Marketplace Cube**.
- Brand guideline MEA Agency (terkunci, jangan diubah tanpa Yohan):
  - Sapaan **Anda**. Tanpa tanda seru. Tanpa emoji di badan teks. Nada: tenang, berbukti, setara.
  - Warna: oranye gradien `#EC7832 → #F69541` (135°) hanya untuk bidang, tombol, ikon, aksen. **Teks di atas putih selalu `#1A1A1A`.** Oranye boleh jadi teks hanya di atas latar gelap.
  - Netral: `#1A1A1A`, `#46474B`, `#E4E4E7`, `#FAFAFA`. Rasio pakai: 70% netral/putih, 20% gelap, 10% oranye.
  - Font: **Instrument Serif** (judul saja, maks 2 baris) + **Inter** (badan, line-height 1,5–1,6). Skala 16 · 20 · 25 · 31 · 39 · 49 · 61.
  - Kepadatan rendah. Visual utama = **foto kerja nyata tim** dan angka.
  - Anti-scope: tidak ada konten tips/tutorial, tidak menjanjikan angka ROAS sebelum audit, tidak menampilkan angka klien tanpa izin tertulis.
- Foto Yohan dan tim **wajib tetap ada** di homepage, layanan, dan studi kasus untuk kesan dekat dengan calon klien.
- Nomor sales tunggal: **0821-1793-2648** (`https://wa.me/6282117932648`).
- Alur lead: CTA → form → halaman/state terima kasih → tombol "Chat tim sales sekarang" (WhatsApp, pesan terisi otomatis).
- **URL tidak berubah.** Template baru dipasang pada halaman yang sudah ada (`/jasa-performance-ads/`, `/bellarosa/`, dst.). Kalau ada URL yang harus berubah: redirect 301 dan catat di log.

## 2. Arsitektur

| Komponen | Isi |
|---|---|
| Tema `mea-orbit` | Tema klasik PHP ringan. `front-page.php`, `page-templates/template-layanan.php`, `page-templates/template-studi-kasus.php`, header/footer sendiri. Tidak bergantung Elementor. Halaman lama tetap jalan dengan Elementor selama transisi |
| Motion | Hero memakai **image sequence pre-render** (paket motion M1), bukan Three.js real-time. Plugin `mea-hero-3d` **tidak dibuat**. Semua motion dipasang di tema sesuai `docs/motion/00-peta-motion.md` |
| Plugin `mea-leads` | Form, tabel lead lokal, menu wp-admin "Leads MEA", sinkron ke CDPS, event GA4 |
| Field konten | Meta box di plugin (atau `register_post_meta` dengan `show_in_rest: true`) agar bisa diisi dari wp-admin **dan** lewat konektor Easy MCP |

Setelah homepage, layanan, studi kasus, dan form pindah ke tema/plugin baru: nonaktifkan Unlimited Elements, ElementsKit Lite, lalu evaluasi Elementor Pro.

## 3. Template dan field

### 3.1 Homepage (`front-page.php`)
Urutan section mengikuti `index.html`:
1. Hero gelap: H1 teks HTML (bukan gambar/canvas), lede, 2 CTA, kubus 3D.
2. Angka bukti — **diambil dari studi kasus yang izinnya aktif** (lihat 3.3), bukan diketik ulang.
3. Tim: blok pimpinan (foto Yohan CEO dan Nerissa COO + kutipan Yohan), lalu mosaik foto tim dan studio.
4. Enam layanan (= enam sisi kubus): Iklan, TikTok Shop, Shopee, Live, Affiliate, Konten.
5. Cara kerja (5 langkah berurutan, sesuai alur CDPS: Riset Awal → Strategi → Rencana → Eksekusi → Rekap mingguan).
6. Studi kasus unggulan.
7. Form.

Field (option page "Homepage"): judul hero, lede, label CTA, ID studi kasus unggulan, daftar foto tim (media ID + keterangan + alt), kutipan pendiri + foto.

### 3.2 Template layanan
Field: H1, lede, foto hero, daftar "Cocok jika", daftar "Belum cocok jika", deliverables (judul + deskripsi, repeater), studi kasus terkait (ID), FAQ (repeater → schema `FAQPage`), sumber form (`layanan-<slug>`).
Halaman pertama yang dipindah: `/jasa-performance-ads/` (isi lama berbicara iklan Google/Bing; ganti ke Shopee & TikTok Shop sesuai prototipe).

### 3.3 Template studi kasus
Field: nama brand, H1, kategori, kanal, periode, layanan dipakai (relasi ke halaman layanan), angka hasil (repeater: nilai, label), komposisi GMV (opsional, mis. organik & affiliate vs iklan), Tantangan / Yang kami kerjakan / Hasil, foto proses kerja tim, **`cdps_client_id`** (opsional; Bellarosa tidak ada di tabel `clients` CDPS, jadi dikosongkan), **status izin publikasi**.

**Izin publikasi:** Yohan mengonfirmasi (19 Okt 2026) izin publikasi dari klien sudah dimiliki. Template tetap punya field `izin_publikasi` (default: ada) supaya angka bisa disembunyikan cepat kalau izin dicabut. Catatan: ledger CDPS `client_pitch_consents` masih 0 baris; sebaiknya tim Account mencatat izin yang sudah ada di sana agar sumber datanya satu. Ini bukan syarat terbit.

## 3.4 Kutipan Yohan (disetujui 19 Okt 2026, teks final dari Yohan)
Kutipan memakai suara pribadi Yohan (gw/lu) sebagai pengecualian yang disengaja dari sapaan "Anda"; teks di sekitarnya tetap memakai "Anda". Jangan diedit selain typo.

| # | Kutipan | Penempatan |
|---|---|---|
| 1 | Gw mulai jualan online 2013, di zaman orang belum ngeuh masuk ke situ. MEA Agency lahir dari pengalaman itu: pemilik bisnis harusnya memimpin, bukan ngerjain semua kerjaan receh. | Homepage, blok pimpinan (foto Yohan & Nerissa) |
| 2 | Pernah gak kepikiran, kalau lu cuma laku karena perang harga, gimana kalau harganya udah gak bisa turun lagi? Yang kami bangun growth yang tetep ada untungnya. | Template studi kasus, setelah bagian Hasil |
| 3 | MEA lahir di masa pandemi, saat banyak bisnis harus pindah ke online dalam hitungan minggu. Tugas kami sejak itu sama: bantuin kamu jualan online tanpa harus urusin sendiri. | Halaman About Us (template berikutnya) |
| 4 | Kami tidak memberi saran sebelum membaca data toko. Keputusan yang baik dimulai dari angka, bukan dari tebakan. | Template layanan, setelah Cara kerja |
| 5 | Gw dan tim sudah mendampingi lebih dari 3.000 UMKM. Pelajarannya selalu sama: yang bertahan adalah yang tumbuh sehat, bukan yang paling pengen ngebut. | Homepage, setelah Cara kerja |

Simpan kutipan sebagai option/field yang bisa dipilih per template (bukan hardcode), atribusi: "Yohan Agustian, CEO MEA Agency".

## 4. Foto yang dipertahankan (Media Library meagency.co.id)

| Media ID | File | Dipakai di |
|---|---|---|
| 15633 | KOKO-CICI-18.jpg | **Foto utama pimpinan: Yohan Agustian (CEO) dan Nerissa (COO)** di kantor MEA. Blok pimpinan homepage, bisa juga di halaman About |
| 15630 | KOKO-CICI-11.jpg | Homepage mosaik, hero layanan |
| 15642 | KOKO-CICI-34.jpg | Homepage mosaik |
| 16518, 16516, 16517, 16519 | Studio-1 s.d. 4 | Mosaik, studi kasus, layanan |

Foto KOKO-CICI-18 sudah dikonfirmasi Yohan sebagai foto utama (Yohan CEO, Nerissa COO). Keterangan foto tim lain masih netral sampai ada nama dan peran.
Semua foto: alt text deskriptif, `srcset` WebP/AVIF, `width`/`height` eksplisit, lazy-load kecuali gambar LCP. Hapus hotlink lama ke `dev.meagency.co.id` dan `mighti.themewant.com`.

## 5. Kinerja dan SEO (kriteria lulus)

- LCP < 2,5 dtk, CLS < 0,1, INP < 200 ms di mobile (PageSpeed). Request homepage < 30 (sekarang 68).
- three.js **tidak dimuat** di situs. Motion = image sequence dan video pendek dari paket motion, dimuat setelah `load`. Mobile hemat data dan `prefers-reduced-motion` → poster statis.
- Font di-host sendiri (subset Latin), `font-display: swap`, preload Inter 400/600.
- Satu H1 per halaman (sekarang homepage **tidak punya H1**). Breadcrumb + schema `BreadcrumbList`. Homepage: `Organization` + `WebSite`. Layanan: `Service` + `FAQPage`. Studi kasus: `Article`.
- `og:locale` = `id_ID` (sekarang `en_US`), `og:image` per halaman (sekarang kosong di homepage).
- Halaman laporan klien (diproteksi password) tetap `noindex`.

## 6. Plugin `mea-leads` dan integrasi CDPS

### 6.1 Temuan di Supabase "CDPS SG" (ref `egddxfcnrtecheiykhlf`, ap-southeast-1)
- Tabel `public.leads` (1.329 baris). Kolom: `id` (format `LEAD-YYYYMM-NNNN`, dibuat kode aplikasi lewat `id_sequences`), `lead_name`, `phone_number`, `phone_norm`, `email`, `source`, `origin_division`, `origin_campaign_id`, `last_touch_campaign_id`, `record_status`, `winning_attempt_id`, `manual_review_flag`, `created_at`, `created_by` (ID karyawan).
- Nilai `source` yang dipakai: `Leads - Iklan`, `Scouting`, `Leads - Socmed`, `Website` (92 baris, terakhir 24 Sep 2026, hampir semuanya diinput manual oleh satu akun karyawan), `Database`, `Others`, `IG`, `Event`.
- Tidak ada CHECK constraint dan tidak ada unique index pada `phone_norm` → validasi dan deteksi duplikat hidup di domain TypeScript (`packages/core`), bukan di DB.
- Belum ada Edge Function di proyek ini.
- RLS aktif di semua tabel. Pola inbox yang sudah ada: `external_orders` (payload immutable, status lewat `sm_transition`).

### 6.2 Desain: lead website langsung jadi prospek di CDPS (revisi 24 Okt 2026)
Temuan tambahan di CDPS SG: tabel `prospect_attempts` (owner, status, claimed_at) dan mesin status `lead_record` punya state **`[Pool]`** (lead siap diklaim, `[Pool] → active`). Mesin `prospect_attempt` dimulai dari `New Lead` (atau `Pending Validation → New Lead`). Ke-92 lead Website lama semuanya sudah punya prospect attempt.

Alur baru, **tanpa langkah input atau "Terima" manual**:
1. Form website → plugin `mea-leads` menyimpan lokal → kirim ke penerima CDPS (HMAC).
2. Penerima CDPS mencatat tanda terima di `website_lead_inbox` (payload mentah, idempotensi, audit), lalu **langsung** memanggil jalur domain `createLead` yang sudah ada:
   - `source = 'Website'`, `origin_division = 'Sales'`, `record_status = '[Pool]'`, `created_by` = aktor sistem sesuai konvensi repo (kalau belum ada, Claude Code berhenti dan bertanya).
   - Nomor yang sudah ada → ikuti aturan duplikat domain yang berlaku, `manual_review_flag = true`.
   - `website_lead_inbox.lead_id` diisi otomatis.
3. Notifikasi ke tim Sales: "Lead website baru — {site}, siap diklaim".
4. Sales mengklaim dari Pool seperti biasa → `active` + prospect attempt `New Lead` dengan owner = yang mengklaim. Pipeline selanjutnya tidak berubah.
5. Opsi (keputusan Yohan): auto-assign bergiliran ke Sales aktif, langsung membuat prospect attempt `New Lead`, melewati Pool.

Penerima ditempatkan di tempat yang bisa memakai `packages/core` apa adanya (route API web-internal, atau Edge Function bila core bisa diimpor). Jangan menulis ulang logika createLead.

### 6.2a Penanda "lead website" (permintaan Yohan, wajib)
Lead dari website harus langsung terlihat sebagai lead website di CDPS, dan bisa dibedakan dari lead Website yang dulu diinput manual.
- `leads.source = 'Website'` (nilai yang sudah ada, supaya filter dan laporan lama tetap jalan).
- Relasi `website_lead_inbox.lead_id → leads.id` menjadi bukti bahwa lead lahir dari form website (bukan ketikan manual).
- Di CDPS: badge **"Lead Website"** di daftar dan detail lead, beserta `site` (meagency.co.id), `sumber_form`, `halaman_asal`, dan UTM. Filter "Hanya lead website (otomatis)" di daftar lead Sales.
- Notifikasi ke Sales berjudul "Lead website baru — meagency.co.id".
- Payload membawa `kanal: "website"` dan `site`, supaya situs MEA lain (meago.id, meamcn.com) nanti memakai jalur yang sama dan tetap terbedakan per situs.
- Di wp-admin "Leads MEA", kolom ID lead CDPS (`LEAD-…`) tampil setelah Sales menerima lead, sehingga dua sistem bisa dicocokkan.

### 6.3 Kontrak payload v1
```json
{
  "schema": "mea.website_lead.v1",
  "kanal": "website",
  "idempotency_key": "3f0c…",
  "site": "meagency.co.id",
  "submitted_at": "2026-10-19T09:15:00+07:00",
  "nama": "…",
  "whatsapp": "08…",
  "link_toko": "…",
  "omzet": "250jt-1m",
  "layanan": "Iklan Shopee dan TikTok Shop",
  "sumber_form": "layanan-performance-ads",
  "halaman_asal": "/jasa-performance-ads/",
  "utm": { "source": null, "medium": null, "campaign": null },
  "persetujuan_dihubungi": true
}
```
Nilai `omzet`: `<100jt`, `100-250jt`, `250jt-1m`, `>1m`. Data pribadi: retensi di WordPress maks 90 hari setelah status `terkirim`.

## 7. Urutan kerja

1. Staging (salinan situs + Supabase branch atau CDPS Staging).
2. Plugin `mea-leads` + penerima CDPS (lead otomatis ke Pool) → uji ujung ke ujung.
3. Tema `mea-orbit`: homepage → template layanan (`/jasa-performance-ads/`) → template studi kasus (`/bellarosa/`, setelah izin tercatat).
4. Pemasangan motion (paket M1–M4).
5. Uji PageSpeed, Rich Results, form, dan semua link internal. Baru pindah ke produksi dengan persetujuan Yohan.
6. Pindahkan halaman layanan dan studi kasus lain bertahap, lalu nonaktifkan addon Elementor.

## 8. Yang masih harus diputuskan Yohan

- Nama dan peran orang di foto tim selain foto pimpinan.
- Lead website masuk Pool (diklaim Sales) atau auto-assign bergiliran ke Sales aktif.

## 9. Fakta Bellarosa (sumber: halaman live ID 14512, dibaca 24 Okt 2026)
- Produsen dan grosir kue kering dan cokelat sejak 2005; kemasan eksklusif oleh-oleh Lebaran. **Bukan brand baru**: tokonya di marketplace yang dimulai dari nol.
- Kanal: Shopee dan TikTok Shop. Layanan: optimasi toko Shopee dan TikTok Shop, iklan Shopee, TikTok Ads, live streaming Shopee dan TikTok, konten video, pencarian affiliator, campaign TikTok Shop.
- Periode: dua tahun pendampingan; puncak Ramadhan 2023.
- Hasil: GMV April 2023 Rp 8,2 miliar (75% organik & affiliate, 25% iklan); kontribusi iklan sekitar Rp 2 miliar dari modal Rp 97 juta (ROAS 21). Halaman lama juga menampilkan "Organic Traffic 1.200%" tanpa penjelasan dasar perhitungan; **jangan dipakai** sampai tim menjelaskan angkanya.
- Angka 75% adalah porsi **GMV/omzet**, bukan trafik.
- Foto produk Bellarosa di Media Library: 14489–14495, 14503–14508 (alt masih kosong).

## 10. Keputusan tambahan (27 Okt 2026)
- **Nama badan hukum di situs: "PT MEA Digital Marketing"** (bukan PT MEA Agensi Digital). Berlaku untuk footer, JSON-LD `Organization.legalName`, halaman kebijakan, dan teks lain di tema maupun plugin.
- **Kubus hero memakai logo platform permanen** (Shopee, TikTok, Tokopedia, Meta, MEA, satu sisi polos untuk Google). Badge dan sertifikat yang berlaku per kuartal **tidak** dimasukkan ke animasi.
- **Strip "Partner resmi"** tepat di bawah hero: badge Shopee-Certified Enablers Program dan foto plakat TikTok Shop Platinum Certified Partner, sebagai gambar biasa dari Media Library, diganti setiap kuartal lewat opsi Homepage.
- Tracking dipasang dari tema (GTM + event server), tanpa plugin tracking.
- `/jasa-performance-ads/` = induk iklan (Shopee + TikTok Shop); `/jasa-iklan-shopee/` dan `/jasa-tiktok-ads/` = anak. `/jasa-live-streaming/` = induk; `/jasa-live-streaming-tiktok/` = anak. Hubungan lewat section "Layanan terkait", URL tidak diubah.
