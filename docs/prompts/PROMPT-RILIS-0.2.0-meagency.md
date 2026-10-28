# Rilis mea-orbit 0.2.0 — prompt Claude Code dan langkah go-live

Live sekarang: tema **MEA Orbit 0.1.0** + plugin **mea-leads**. Rilis 0.2.0 menggabungkan semua yang sudah diputuskan sejak itu.

## A. Siapkan repo wp-meagency sebelum menempel prompt

| Taruh di | Isi |
|---|---|
| `motion-src/hero-v2/` | Isi folder `hero-v2/` dari **mea-hero-cube-v2.zip** (frames, posters, video, README.md, manifest). Folder `source/` boleh ikut, tapi tidak dipaketkan ke tema |
| `motion-src/partner/` | `shopee-certified-enablers-q3-2026.png` dan `tiktok-shop-platinum-partner-q3-2026.jpg` |
| `motion-src/konten/` | *Opsional.* Klip video untuk mockup HP (mis. grok-klip-5A fashion, 5B skincare, 6 live). Kalau kosong, section mockup tidak dibuat dulu |
| `docs/mea-orbit/` | Timpa dengan isi **mea-orbit-prototype.zip** terbaru (brief bagian 10) |
| `docs/prompts/` | **PROMPT-tracking-hero-v2.md** dan **PROMPT-partner-strip-nama-pt.md** sebagai rujukan spesifikasi |

## B. Prompt Claude Code

```text
## Objective
Siapkan rilis mea-orbit 0.2.0 dan mea-leads versi berikutnya untuk produksi meagency.co.id, menggabungkan semua keputusan sejak 0.1.0, lengkap dengan paket instalasi, catatan keputusan di repo, dan panduan deploy serta rollback.

## Context
- Produksi saat ini: tema MEA Orbit 0.1.0 dan plugin mea-leads (versi di repo). Situs aktif dipakai pengunjung.
- Sumber kebenaran: docs/mea-orbit/BRIEF-CLAUDE-CODE.md (terutama bagian 10, keputusan 27 Okt 2026), docs/DECISIONS.md (kalau sudah ada), docs/motion/, dan docs/prompts/ (spesifikasi detail tiap pekerjaan).
- Sebagian pekerjaan mungkin sudah dikerjakan di sesi sebelumnya (mis. section "Layanan terkait" 0.1.1). Periksa repo dulu; jangan mengerjakan ulang yang sudah ada.
- Paket hero baru: motion-src/hero-v2/ (baca README.md dan frames/manifest.json). Empat sisi logo resmi (Shopee, TikTok, Tokopedia, Meta) dan dua sisi video tanpa suara (konten kreator dan live shopping, dibuat dengan AI). Desktop 120 frame ±3,1 MB, mobile 60 frame ±1,0 MB. Budget hero ini sudah disetujui pemilik.
- Penerima lead di CDPS belum aktif. Plugin tetap menyimpan lead lokal dengan status "menunggu" sampai konstanta MEA_LEADS_ENDPOINT dan MEA_LEADS_SECRET diisi.

## Target State (isi rilis 0.2.0)
1. Section "Layanan terkait" di template layanan (bila belum ada): repeater label + page_id, tampil setelah FAQ, tersembunyi bila kosong.
2. Strip "Partner resmi" di bawah hero dan nama badan hukum "PT MEA Digital Marketing" di seluruh tema, plugin, seed, dan JSON-LD (spesifikasi: docs/prompts/PROMPT-partner-strip-nama-pt.md). Author di style.css tema juga diganti.
3. Tracking dari tema, tanpa plugin tracking (spesifikasi: Prompt A di docs/prompts/PROMPT-tracking-hero-v2.md): GTM dari tema, event Lead server-side ke Meta CAPI dan TikTok Events API dengan event_id = idempotency_key, semua ID/token dari konstanta wp-config, halaman status Pengaturan → Tracking MEA, docs/tracking/GTM-SETUP.md dan MIGRASI.md.
4. Hero memakai motion-src/hero-v2 (frames, posters, video fallback), mengikuti bagian "Wiring it into the site" di README paket: poster-first sebagai img fetchpriority="high", frame dimuat progresif setelah load dan interaksi pertama, mobile < 768 px, progress = scrollY / (heroTop + 0.6 × heroHeight), reduced motion → poster-last. Teks alternatif kubus menyebut platform yang tampil dan bahwa dua sisi video adalah ilustrasi.
5. Mockup HP "Konten" (Prompt C di docs/prompts/PROMPT-tracking-hero-v2.md) HANYA bila motion-src/konten/ berisi video. Bila kosong, lewati dan catat di CHANGELOG. Keterangan "Ilustrasi format konten, dibuat dengan AI" wajib tampil di bawah section.
6. Budget homepage: 0 byte motion sebelum load; sebelum hero terlewati ≤ 1,5 MB; total desktop ≤ 5 MB dan mobile ≤ 2,5 MB. Kalau total desktop lewat 5 MB karena hero v2, Cara kerja (M3) desktop memakai setiap frame kedua. Jangan encode ulang aset lain tanpa izin.
7. Versi: tema 0.2.0, plugin versi minor berikutnya. CHANGELOG diperbarui. docs/DECISIONS.md memuat semua keputusan bagian 10 brief beserta tanggal, ditambah satu baris per perubahan rilis ini (tanggal, keputusan, alasan, file terdampak).
8. Paket: dist/mea-orbit-0.2.0.zip, dist/mea-leads-<versi>.zip (tanpa motion-src/source, tanpa file dev), dan dist/DEPLOY-0.2.0.md berisi: backup, urutan upload, konstanta wp-config yang perlu diisi (tanpa nilai), langkah pasca-deploy tracking sesuai MIGRASI.md, cara rollback ke 0.1.0, dan daftar cek setelah live.

## Scope
- Kerja di repo ini saja. Jangan menyentuh situs live, database produksi, atau Supabase.

## Constraints
- Only make changes directly requested. Do not add features, abstractions, or files beyond what was asked.
- Tanpa dependency baru tanpa izin. three.js tidak dimuat di situs.
- Logo, badge, dan foto partner tidak boleh diedit atau diberi efek.
- Brand terkunci: sapaan Anda, tanpa tanda seru, oranye hanya aksen, nama brand tampil "MEA Agency".

## Acceptance Criteria
- [ ] php -l bersih, PHPCS tanpa error baru, semua test lama dan baru hijau.
- [ ] Instalasi bersih di WordPress lokal: homepage, layanan, studi kasus, artikel, arsip, 404, dan halaman berpassword tampil tanpa notice (screenshot 1366 px dan 390 px di docs/release/0.2.0/).
- [ ] Upgrade dari 0.1.0 ke 0.2.0 di lokal tidak menghapus isian field halaman 13411, 14144, 14512, 7467, dan opsi Homepage.
- [ ] Budget poin 6 terpenuhi, angka byte dicatat di DEPLOY-0.2.0.md.
- [ ] Tidak ada string "Agensi" tersisa di kode, seed, dan JSON-LD (kecuali catatan di DECISIONS.md).
- [ ] Tanpa konstanta tracking dan CDPS, situs dan form tetap jalan.

## Stop Conditions
Stop and ask before:
- Menyentuh situs live atau menambah dependency.
- Menghapus file.
- Mengubah perilaku form selain memicu event tracking.
- Melonggarkan budget di luar poin 6.

## Session Strategy
- Fase 0: periksa repo, tulis docs/release/0.2.0/PLAN.md (mana yang sudah ada, mana yang dikerjakan, risiko upgrade). STOP sampai saya ketik "lanjut".
- Fase 1: kerjakan Target State 1–7. STOP.
- Fase 2: QA sesuai Acceptance Criteria dan paket dist/ (Target 8). STOP.

## Progress
Setelah tiap langkah: ✅ [yang dikerjakan] — [file]
```

## C. Langkah go-live (setelah Claude Code selesai)

| # | Langkah | Oleh |
|---|---|---|
| 1 | Backup penuh (file + database) di UpdraftPlus, dan simpan zip tema 0.1.0 untuk rollback | Yohan |
| 2 | Upload `mea-orbit-0.2.0.zip` (Tampilan → Tema → Tambah → Unggah → **Ganti yang terpasang**) dan zip mea-leads baru (Plugin → Tambah → Unggah → Ganti) | Yohan |
| 3 | Isi konstanta tracking di `wp-config.php` dengan **token baru** (token lama pernah tersimpan sebagai teks biasa) | Yohan |
| 4 | Buat tag Meta, TikTok, dan GA4 di GTM sesuai `GTM-SETUP.md`, lalu Publish | Yohan / tim iklan |
| 5 | Ikuti `MIGRASI.md`: matikan penempatan tag Site Kit, nonaktifkan snippet #9 dan #11, plugin Meta pixel, lalu Code Snippets dan WPCode | Yohan |
| 6 | Purge cache hosting | Yohan |
| 7 | Verifikasi lewat konektor: homepage, hero, strip partner, footer PT, data terstruktur, redirect, form, halaman layanan dan studi kasus. Isi "Layanan terkait" di `/jasa-performance-ads/` dan `/jasa-live-streaming/` | Saya (SEO Room) |
| 8 | Tes 1 lead: muncul di Leads MEA, GTM Preview, Meta Test Events, TikTok Events Manager | Yohan + saya |
| 9 | Kalau ada masalah serius: upload ulang zip 0.1.0 (Ganti yang terpasang), purge cache | Yohan |

Penerima lead CDPS masih terpisah: jalankan **CLAUDE-CODE-CDPS-penerima-lead.md** di repo CDPS. Sampai itu live, lead tetap aman tersimpan di wp-admin "Leads MEA".
