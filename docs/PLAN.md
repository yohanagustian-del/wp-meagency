# PLAN — Tema `mea-orbit` + plugin `mea-leads` (meagency.co.id)

Fase 0 · ditulis 25 Okt 2026 · status: **menunggu "lanjut"**

Dokumen ini memetakan apa yang akan dibangun di Fase 1–5. Bagian 9 berisi konflik yang ditemukan beserta usulan penyelesaiannya. Bagian 10 berisi keputusan yang perlu konfirmasi sebelum Fase 1.

---

## 1. Sumber yang dibaca

| Sumber | Lokasi di repo | Catatan |
|---|---|---|
| Brief (versi 25 Okt, bagian 9 baru) | `docs/mea-orbit/BRIEF-CLAUDE-CODE.md` | Sumber kebenaran utama. Menang bila bertentangan dengan prototipe atau dokumen motion |
| Prototipe | `docs/mea-orbit/index.html`, `layanan.html`, `studi-kasus.html` | Acuan layout, copy, CSS. Fakta Bellarosa sudah dikoreksi |
| Peta dan aturan motion | `docs/motion/00-peta-motion.md` s.d. `05-claude-code-integrasi-motion.md` | 01–04 adalah prompt pembuatan aset (riwayat). 00 dan 05 dipakai sebagai acuan pemasangan |
| M1 hero | `motion-src/hero/` | 120 frame desktop 1200 px, 60 frame mobile 720 px, poster, video, README, manifest |
| M2 Enam Sisi | `motion-src/sisi/` | 6 loop WebM/MP4 480 px + poster frame 0 |
| M3 Orbit Proses | `motion-src/proses/` | 150 frame desktop 1200 px, 75 frame mobile 800 px, poster, video, README, manifest |
| M4 Kurva Bellarosa | `motion-src/bellarosa/` | Video desktop 1200×900 dan mobile 900×900 (7 dtk) + poster frame akhir. Tanpa README |
| Jawaban pemilik 25 Okt | (pesan sesi) | Child theme, kontrak HMAC, retensi, budget motion baru, alt text, staging menyusul |

Fakta yang diverifikasi di situs live (read-only, sebelum Fase 0): homepage = page 13411, `/jasa-performance-ads/` = 14144, `/bellarosa/` = 14512, `/study-case/` = 14645, `/services/` = 12356. Semua link di prototipe hidup (jasa-kelola-tiktok-shop 365, ecommerce-service 14637, jasa-live-streaming 15567, affiliate 14662, jasa-konten 15559, eleven-basic 18653, skynet 16160, tumbleryuk 16144). Media 15633, 15630, 15642, 16516–16519 ada (JPG, alt kosong). Media Bellarosa 14489–14495 dan 14503–14506, 14508 ada; **14507 tidak ditemukan**. Plugin aktif yang relevan: Elementor + Pro 3.30, ElementsKit Lite, Unlimited Elements, Rank Math, Site Kit, WPCode, Meta Pixel, Classic Editor.

---

## 2. Keputusan yang sudah dikunci (ringkas)

- **Child theme** dari Hello Elementor. Hanya halaman bertemplate baru (13411, 14144, 14512, dan yang dipindah kemudian) yang berubah. Artikel blog, laporan klien, dan halaman Elementor lain tidak berubah. Template baru tidak memuat CSS/JS Elementor.
- **Kontrak HMAC** sesuai jawaban nomor 5, ditulis di `docs/WEBSITE_LEAD_CONTRACT.md` (sudah dibuat di Fase 0).
- **Retensi** 90 hari setelah `terkirim`: nama, WhatsApp, link toko, UTM dihapus. Baris tetap ada.
- **Budget motion homepage**: 0 byte sebelum `load` · ≤ 1,5 MB sebelum hero terlewati · total desktop ≤ 5 MB · total mobile ≤ 2,5 MB · tiap paket dimuat saat section-nya mendekati layar · aset tidak di-encode ulang tanpa izin.
- **M4**: breakpoint 768 px, putar sekali saat 60% terlihat, tahan frame akhir, hanya di homepage, reduced motion → poster akhir.
- **Bellarosa**: fakta bagian 9 brief. 75% = porsi GMV. Angka "Organic Traffic 1.200%" tidak dipakai. `cdps_client_id` opsional, kosong.
- Staging dibuat pemilik sebelum Fase 5. Fase 0–4 dikerjakan lokal.

---

## 3. Struktur repo dan daftar file

```text
wp-meagency/
├── docs/
│   ├── PLAN.md                         ← dokumen ini
│   ├── WEBSITE_LEAD_CONTRACT.md        ← kontrak v1 (pengirim WordPress ↔ penerima CDPS)
│   ├── mea-orbit/                      ← brief + prototipe (baca saja)
│   ├── motion/                         ← 00–05 (baca saja)
│   └── seed/                           ← (Fase 2) isi field awal per halaman, JSON, untuk diisi di staging
├── motion-src/                         ← paket motion asli (baca saja)
├── wp-content/
│   ├── themes/mea-orbit/
│   └── plugins/mea-leads/
├── tests/                              ← (Fase 3) test tanpa dependency: php tests/run.php
└── dist/                               ← (Fase 5) mea-orbit.zip, mea-leads.zip, CARA-PASANG.md
```

### 3.1 Tema `wp-content/themes/mea-orbit/`

| File | Isi |
|---|---|
| `style.css` | Header tema: `Template: hello-elementor`, Text Domain `mea-orbit`. Tidak berisi CSS (CSS di `assets/css/`) |
| `functions.php` | Hanya memuat file `inc/` |
| `inc/scope.php` | `mea_orbit_is_view()`: true hanya di homepage statis dan halaman dengan template `mea-orbit`. Semua perilaku tema digerbang fungsi ini |
| `inc/setup.php` | Theme support (title-tag dari induk, post-thumbnails, html5), lokasi menu `mea-orbit-utama`, ukuran gambar tambahan |
| `inc/assets.php` | Enqueue CSS/JS hanya di `mea_orbit_is_view()`. Dequeue aset Elementor, Elementor Pro, ElementsKit, Unlimited Elements, dan CSS Hello Elementor di halaman itu. Preload Inter 400/600 + poster LCP |
| `inc/fields.php` | `register_post_meta` (show_in_rest: true) + meta box per template + sanitasi |
| `inc/options.php` | Halaman opsi "Homepage" (Tampilan → Homepage MEA) dan daftar kutipan |
| `inc/seo.php` | JSON-LD lewat filter Rank Math, og:locale, og:image cadangan, breadcrumb data |
| `inc/media.php` | `mea_orbit_img()`: `wp_get_attachment_image` dengan srcset, sizes, width/height, alt, lazy/eager |
| `inc/template-tags.php` | Render breadcrumb, pull quote, deret angka, panel hasil, kartu layanan |
| `header-orbit.php`, `footer-orbit.php` | Header/footer milik mea-orbit (`get_header('orbit')`). Tetap memanggil `wp_head`, `wp_body_open`, `wp_footer` supaya GTM, Site Kit, Meta Pixel, WPCode tetap jalan |
| `front-page.php` | Homepage. Bila homepage statis bukan memakai desain baru (opsi dimatikan), jatuh ke template induk |
| `page-templates/template-layanan.php` | Template "MEA Orbit · Layanan" |
| `page-templates/template-studi-kasus.php` | Template "MEA Orbit · Studi kasus" |
| `template-parts/home/*.php` | `hero`, `tim`, `layanan`, `cara-kerja`, `kasus-unggulan` |
| `template-parts/layanan/*.php` | `hero`, `cocok`, `dikerjakan`, `cara-kerja`, `bukti`, `faq` |
| `template-parts/studi-kasus/*.php` | `head`, `hasil`, `cerita`, `lain` |
| `template-parts/global/*.php` | `breadcrumb`, `kutipan`, `konsultasi` (form dari plugin, atau tombol WhatsApp bila plugin tidak aktif) |
| `assets/css/orbit.css` | Satu stylesheet hasil port CSS ketiga prototipe (digabung, tanpa duplikat) |
| `assets/css/admin.css`, `assets/js/admin-fields.js` | Khusus wp-admin: repeater dan pemilih media di meta box (JS vanilla, memakai `wp.media` bawaan WordPress) |
| `assets/js/motion.js` | Satu modul motion, `defer`, hanya di halaman yang memakainya |
| `assets/fonts/` | Inter 400/500/600/700 + Instrument Serif 400, subset Latin, WOFF2, lisensi OFL |
| `assets/motion/{hero,sisi,proses,bellarosa}/` | Salinan file runtime dari `motion-src/` tanpa diubah (frames, posters, video, manifest, README). Folder `source/`, `demo/`, `checks/` tidak ikut ke tema supaya zip ringan |
| `screenshot.png` | Tangkapan homepage 1200×900 |

Supaya kelak bisa dilepas jadi tema mandiri: tidak ada pemanggilan fungsi Hello Elementor, semua kode di `inc/` dan `template-parts/`. Melepasnya cukup dengan menghapus baris `Template:` dan menambah `index.php`, `page.php`, `single.php`, `archive.php`.

### 3.2 Plugin `wp-content/plugins/mea-leads/`

| File | Isi |
|---|---|
| `mea-leads.php` | Header plugin, konstanta versi, aktivasi (buat tabel, jadwalkan cron), deaktivasi (hapus jadwal cron) |
| `includes/db.php` | Skema `{$wpdb->prefix}mea_leads` (dbDelta), insert, update status, query admin |
| `includes/validate.php` | Sanitasi, validasi, `mea_leads_normalize_wa()` (+62/62 → 0, pola `^08\d{8,11}$`) |
| `includes/form.php` | Shortcode `[mea_lead_form sumber="..."]`, blok dinamis `mea/lead-form`, markup sama dengan prototipe |
| `includes/rest.php` | `POST /wp-json/mea-leads/v1/kirim` (form JS) dan `GET /wp-json/mea-leads/v1/token` (nonce segar, `no-store`). Tanpa JS: `admin-post.php` lalu redirect ke state terima kasih |
| `includes/sender.php` | Bangun payload v1, tanda tangan HMAC, `wp_remote_post`, klasifikasi respons, jadwal retry |
| `includes/cron.php` | `mea_leads_kirim` (kirim satu lead), `mea_leads_sapu` (per jam: kirim yang `menunggu` bila konstanta sudah ada), `mea_leads_retensi` (harian) |
| `includes/admin.php` | Menu "Leads MEA": `WP_List_Table`, filter status/sumber/tanggal, kirim ulang (baris dan massal), ekspor CSV, kolom ID lead CDPS |
| `blocks/lead-form/block.json`, `index.js` | Blok server-rendered, JS vanilla tanpa build step |
| `assets/js/form.js` | Validasi klien, submit fetch, state terima kasih, `dataLayer.push`, tautan WhatsApp terisi otomatis. `defer`, hanya di halaman yang ada form |
| `assets/css/form.css` | Gaya dasar form, hanya dimuat bila tema aktif tidak menyatakan `add_theme_support('mea-leads')` (mea-orbit menyatakannya) |
| `uninstall.php` | Default: tabel **tidak** dihapus. Dihapus hanya bila `MEA_LEADS_HAPUS_SAAT_UNINSTALL` bernilai true |

### 3.3 Test dan paket

- `tests/run.php`: runner tanpa dependency (`php tests/run.php`). Menguji HMAC dengan secret contoh dan vektor uji tetap, normalisasi nomor, bentuk payload v1, klasifikasi respons, jadwal backoff.
- `dist/mea-orbit.zip`, `dist/mea-leads.zip`, `dist/CARA-PASANG.md`.

---

## 4. Peta template, section, dan sumber isi

Kunci sumber: **F** = field halaman, **O** = opsi Homepage, **K** = studi kasus yang dirujuk, **T** = teks tetap di template (copy prototipe), **P** = plugin.

### 4.1 Homepage (`front-page.php`, page 13411)

| # | Section | Isi dan sumber |
|---|---|---|
| 1 | Hero gelap | Kicker, H1, lede, label CTA 1/2 (O). Visual: poster M1 + kanvas (tema). Tanpa field gambar |
| 2 | Angka bukti (di dalam hero, seperti prototipe) | Baris angka dari studi kasus unggulan yang `izin_publikasi` aktif (K). Disembunyikan bila izin nonaktif. Link "Baca studi kasus {brand}" (K) |
| 3 | Tim | H2 + lede (T). Foto pimpinan 15633 + keterangan + kutipan #1 (O). Mosaik 5 foto: media ID + keterangan + alt (O) |
| 4 | Enam layanan | H2 + lede (T). 6 kartu: nama, deskripsi, URL (O, default dari prototipe), tile M2 per kartu |
| 5 | Cara kerja | H2 + lede (T). 5 langkah (T, karena sama dengan alur CDPS). M3. Kutipan #5 (O) |
| 6 | Studi kasus unggulan | Label, H2, lede, link (K: judul kartu dan ringkasan kartu). Visual M4 menggantikan foto Studio-2 (lihat konflik 9.6) |
| 7 | Konsultasi | H2, lede, nomor sales (T). Form (P) dengan `sumber_form = home` |

### 4.2 Template layanan (`/jasa-performance-ads/`, page 14144)

| # | Section | Isi dan sumber |
|---|---|---|
| 1 | Breadcrumb | Beranda › Layanan › {judul pendek} (F) |
| 2 | Hero | H1, lede (F), foto hero (F, default 15630, `fetchpriority="high"`), CTA (T) |
| 3 | Cocok / belum cocok | Dua daftar (F) |
| 4 | Yang dikerjakan | Lede (F), deliverables repeater judul + deskripsi (F) |
| 5 | Cara kerja | 5 langkah versi ringkas (F, default teks prototipe layanan) + M3 + kutipan #4 (F, pilih dari daftar kutipan) |
| 6 | Bukti | Judul bukti (F), angka dari studi kasus terkait (K), link ke studi kasus |
| 7 | FAQ | Repeater tanya + jawab (F) → `FAQPage` |
| 8 | Konsultasi | Form (P), `sumber_form = layanan-<slug>` (F, default otomatis dari slug) |

### 4.3 Template studi kasus (`/bellarosa/`, page 14512)

| # | Section | Isi dan sumber |
|---|---|---|
| 1 | Breadcrumb | Beranda › Studi kasus › {nama brand} (F) |
| 2 | Kepala | Label, H1, meta Kategori / Kanal / Layanan / Periode (F) |
| 3 | Panel hasil | Angka hasil repeater + bar komposisi GMV (F). Disembunyikan bila izin nonaktif |
| 4 | Cerita | Tantangan, Yang kami kerjakan, Hasil (F, rich text terbatas) + kutipan #2 setelah Hasil (F) + foto proses kerja di samping (F) |
| 5 | Studi kasus lain | Daftar ID halaman (F, default 18653, 16160, 16144) |
| 6 | Konsultasi | Form (P), `sumber_form = studi-kasus-<slug>` |

---

## 5. Peta field

Semua meta memakai prefix `mea_`, `register_post_meta('page', …)` dengan `show_in_rest: true`, `single: true`, `auth_callback` = `current_user_can('edit_post', $id)`, dan `sanitize_callback`. Repeater disimpan sebagai `type: array` dengan skema item `object`, sehingga bisa diisi lewat wp-admin dan lewat konektor Easy MCP (`wp_update_post_meta`).

### 5.1 Opsi "Homepage" (`mea_orbit_home`, satu opsi array, `register_setting` show_in_rest)

| Kunci | Tipe | Default |
|---|---|---|
| `hero_kicker`, `hero_judul`, `hero_lede` | string | Teks prototipe |
| `cta_utama`, `cta_kedua` | string | "Jadwalkan konsultasi", "Lihat studi kasus" |
| `kasus_unggulan_id` | int | 14512 |
| `pimpinan_foto_id`, `pimpinan_keterangan`, `pimpinan_alt` | int, string, string | 15633, "Yohan Agustian (CEO) dan Nerissa (COO)", alt dari jawaban nomor 9 |
| `pimpinan_kutipan_id` | string | `k1` |
| `mosaik` | array {media_id, keterangan, alt} × 5 | 15630, 15642, 16518, 16517, 16519 |
| `layanan` | array {nama, deskripsi, url, motion} × 6 | Teks dan URL prototipe; `motion` ∈ iklan, tiktok-shop, shopee, live, affiliate, konten |
| `cara_kerja_kutipan_id` | string | `k5` |

### 5.2 Daftar kutipan (`mea_orbit_kutipan`, opsi)

Lima baris {id `k1`…`k5`, teks, atribusi, penempatan} dengan teks persis dari brief 3.4. Atribusi default "Yohan Agustian, CEO MEA Agency". Template memilih kutipan lewat ID, tidak meng-hardcode teks. `k3` disimpan untuk halaman About (template berikutnya).

### 5.3 Template layanan

| Meta | Tipe | Catatan |
|---|---|---|
| `mea_judul_pendek` | string | Untuk breadcrumb dan `Service.name` ("Iklan Shopee dan TikTok Shop") |
| `mea_h1`, `mea_lede` | string | |
| `mea_foto_hero_id` | int | |
| `mea_cocok`, `mea_belum_cocok` | array string | |
| `mea_dikerjakan_lede` | string | |
| `mea_deliverables` | array {judul, deskripsi} | |
| `mea_langkah` | array {judul, deskripsi} × 5 | Teks ringkas cara kerja |
| `mea_kutipan_id` | string | Default `k4` |
| `mea_kasus_terkait_id` | int | Default 14512 |
| `mea_bukti_judul` | string | **Tambahan** (lihat konflik 9.4) |
| `mea_bukti_angka` | array string | **Tambahan**: label angka studi kasus yang ditampilkan, berurutan. Default "ROAS", "GMV" |
| `mea_faq` | array {tanya, jawab} | |
| `mea_sumber_form` | string | Kosong → `layanan-<slug>`; untuk 14144 diisi `layanan-performance-ads` (sesuai prototipe) |
| `mea_service_type` | string | `Service.serviceType` di JSON-LD |

### 5.4 Template studi kasus

| Meta | Tipe | Catatan |
|---|---|---|
| `mea_brand` | string | "Bellarosa" |
| `mea_h1` | string | Juga `Article.headline` |
| `mea_kartu_judul`, `mea_kartu_lede` | string | **Tambahan**: judul dan ringkasan saat studi kasus ini tampil di homepage (prototipe homepage memakai teks berbeda dari H1) |
| `mea_kategori`, `mea_kanal`, `mea_periode` | string | |
| `mea_layanan` | array {label, page_id} | Label tampil, ditautkan bila `page_id` diisi (prototipe: hanya "Iklan" yang tertaut) |
| `mea_angka` | array {nilai, label, nilai_beranda, label_beranda, tampil_beranda} | `nilai` teks tampil ("Rp 8,2 miliar"). Hitung naik membaca angka dari teks secara otomatis. Field `_beranda` **tambahan** (konflik 9.3) |
| `mea_komposisi` | object {label_a, persen_a, label_b, persen_b} | Opsional, bar komposisi GMV + `aria-label` |
| `mea_tantangan`, `mea_dikerjakan`, `mea_hasil` | string (HTML terbatas: p, ul, li, strong, em, a) | |
| `mea_kutipan_id` | string | Default `k2` |
| `mea_foto_proses` | array {media_id, keterangan, alt} | Default 16519 |
| `mea_kasus_lain` | array int | Default 18653, 16160, 16144 |
| `mea_cdps_client_id` | string | Opsional, kosong untuk Bellarosa |
| `mea_izin_publikasi` | boolean | Default true. False → panel hasil, angka homepage, dan bukti di layanan disembunyikan |
| `mea_sumber_form` | string | Kosong → `studi-kasus-<slug>` |

Isi awal tiap halaman (copy prototipe) disiapkan di `docs/seed/*.json` pada Fase 2. Di staging diisi lewat wp-admin atau lewat konektor.

---

## 6. SEO

- Rank Math tetap memegang `<title>`, meta description, Open Graph, Twitter, sitemap, dan robots. Tema tidak mencetak satu pun tag itu.
- **JSON-LD** disisipkan ke graph Rank Math lewat filter `rank_math/json_ld`, hanya di halaman mea-orbit, supaya tidak ada dua `Organization`:
  - Homepage: lengkapi node `Organization` Rank Math (legalName, telephone, address, founder, foundingDate dari prototipe) dan `WebSite`.
  - Layanan: tambah `Service` (provider → `#org`) dan `FAQPage` dari repeater FAQ.
  - Studi kasus: tambah `Article` (headline = `mea_h1`, author/publisher → `#org`, image = foto proses pertama, datePublished/dateModified dari halaman).
  - `BreadcrumbList` dari data breadcrumb tema. Bila Rank Math sudah mencetak BreadcrumbList, node milik Rank Math diganti, tidak digandakan.
  - Bila Rank Math nonaktif: tema mencetak `<script type="application/ld+json">` sendiri dengan isi yang sama.
- **og:locale**: filter Rank Math → `id_ID` di halaman mea-orbit. Lihat keputusan 10.4 untuk seluruh situs.
- **og:image**: memakai pilihan Rank Math per halaman. Bila kosong, tema mengisi: homepage → foto pimpinan 15633, layanan → foto hero, studi kasus → foto proses pertama.
- Satu H1 per halaman. Judul section memakai H2/H3 seperti prototipe.
- Halaman berpassword: template memanggil `post_password_required()` dan menampilkan form password. Perilaku halaman berpassword lain tidak diubah (tetap template induk).

---

## 7. Plugin `mea-leads`

### 7.1 Tabel `{prefix}mea_leads`

| Kolom | Tipe | Catatan |
|---|---|---|
| `id` | bigint unsigned, PK | |
| `idempotency_key` | char(36), UNIQUE | UUID v4 (`wp_generate_uuid4`), dibuat saat lead disimpan |
| `status` | varchar(20) | `menunggu` · `terkirim` · `gagal` |
| `site`, `kanal`, `sumber_form`, `halaman_asal` | varchar | |
| `nama`, `whatsapp`, `link_toko` | varchar, nullable | Data pribadi, dikosongkan saat retensi |
| `omzet`, `layanan` | varchar | |
| `utm_source`, `utm_medium`, `utm_campaign` | varchar, nullable | Dikosongkan saat retensi |
| `persetujuan` | tinyint | Selalu 1 (lead tanpa persetujuan ditolak) |
| `submitted_at` | datetime | Disimpan dengan offset +07:00 di payload |
| `body` | longtext, nullable | Byte JSON persis yang dikirim. Dibuat sekali, dipakai ulang di setiap percobaan supaya isi tidak berubah. Dikosongkan saat retensi |
| `percobaan`, `berikutnya_at`, `kunci_sampai` | int, datetime, datetime | Retry dan kunci supaya dua cron tidak mengirim lead yang sama |
| `riwayat` | longtext (JSON) | Log setiap percobaan: waktu, kode HTTP, ringkasan galat. "Retry tercatat" |
| `cdps_lead_id` | varchar(32), nullable | `LEAD-YYYYMM-NNNN` dari respons penerima |
| `terkirim_at`, `dianonimkan_at`, `created_at`, `updated_at` | datetime | |

### 7.2 Alur

1. Form dikirim → validasi server (aturan sama dengan prototipe: nama ≥ 2 huruf, WhatsApp dinormalisasi ke `08…` 10–13 digit, omzet wajib dan harus salah satu dari 4 nilai, layanan dari daftar, persetujuan wajib). Pesan galat sama persis dengan prototipe.
2. Penjaga spam: honeypot `website`, nonce (diambil segar lewat `GET /token` sebelum submit, supaya tidak kedaluwarsa di halaman yang di-cache), rate limit per IP (hash, transient, 5 kiriman per 10 menit), minimal 3 detik antara form tampil dan dikirim.
3. Simpan baris `menunggu` + `body` → balas sukses ke browser → jadwalkan `mea_leads_kirim` segera (async lewat WP-Cron).
4. Tanpa konstanta `MEA_LEADS_ENDPOINT` / `MEA_LEADS_SECRET`: tidak ada pengiriman, status tetap `menunggu`, tidak ada galat atau notice. Setelah konstanta diisi, cron per jam `mea_leads_sapu` mengirim semua yang `menunggu`. Tombol "Kirim ulang" di admin juga tersedia.
5. Respons penerima (usulan, harus sama dengan sisi CDPS, lihat kontrak):
   - 2xx → `terkirim`, simpan `lead_id` dari body bila ada.
   - 400, 401, 403, 422 → `gagal` tanpa retry (galat konfigurasi atau payload, perlu dicek manusia).
   - 408, 409 (sedang diproses), 425, 429, 5xx, timeout, galat jaringan → retry.
6. Retry backoff: +1 mnt, +5 mnt, +30 mnt, +2 jam, +12 jam. Setelah percobaan ke-6 gagal → `gagal`. Setiap percobaan memakai timestamp baru dan tanda tangan baru dengan `body` dan idempotency key yang sama.
7. Browser: state terima kasih, tombol "Chat tim sales sekarang" dengan pesan WhatsApp terisi (teks prototipe), `dataLayer.push({event:'generate_lead', form_source, lead_omzet})`.
8. Retensi harian: baris `terkirim` dengan `terkirim_at` lebih dari 90 hari → `nama`, `whatsapp`, `link_toko`, `utm_*`, `body` dikosongkan, `dianonimkan_at` diisi.

Catatan WP-Cron: di Hostinger WP-Cron jalan saat ada kunjungan. Kalau `DISABLE_WP_CRON` aktif di staging, `CARA-PASANG.md` akan menjelaskan cron job hPanel.

### 7.3 Admin "Leads MEA"

Kapabilitas `manage_options`. Kolom: tanggal, nama, WhatsApp, omzet, layanan, sumber form, halaman asal, status, percobaan, ID lead CDPS. Filter status, sumber form, rentang tanggal. Aksi baris dan massal "Kirim ulang". Ekspor CSV sesuai filter aktif (dengan nonce). Detail riwayat percobaan bisa dibuka per baris.

---

## 8. Motion

### 8.1 Peta pemasangan

| Kode | Section | Aset | Perilaku |
|---|---|---|---|
| M1 | Homepage hero | `hero/` frames desktop (≥ 768 px) / mobile (< 768 px), poster-first sebagai `<img fetchpriority="high">` (LCP), poster-last untuk reduced motion | Kanvas scroll-scrub. Tidak di-pin. `progress = scrollY / (tinggi hero × 0,6)`, 0–1. Frame digambar mendekati target dalam rAF |
| H2 | Angka bukti | kode | Hitung naik sekali saat 60% terlihat + garis orbit tipis tergambar di bawah angka |
| H3 | Blok pimpinan | kode | Foto reveal (mask naik 12 px, 600 ms), kutipan muncul per baris |
| H4 | Mosaik | kode | Reveal berurutan, stagger 80 ms |
| M2 | Enam layanan | `sisi/*.webm`, `*.mp4`, poster frame 0 | Tile 120–160 px per kartu. Desktop: putar saat hover/focus kartu, selesai satu siklus saat keluar. Mobile: IntersectionObserver 0,6. `preload="none"`, dimuat setelah `load` |
| M3 | Cara kerja (homepage dan layanan) | `proses/` frames desktop / mobile, poster-first | Kotak sticky + 5 langkah sejajar. `progress = -top / (tinggi section − innerHeight)`. Langkah aktif `min(4, floor(frame150 / 30))` |
| M4 | Studi kasus unggulan homepage | `bellarosa/` desktop 1200×900 / mobile 900×900 | Putar sekali saat 60% terlihat, tahan frame akhir. Reduced motion → poster (frame akhir) |
| S1–S5 | Template studi kasus | kode | Judul per baris + garis meta, Panel Hasil dari field (hitung naik, bar mengisi dari kiri), foto samping sticky + penanda bab aktif, garis oranye kutipan, hover studi kasus lain |
| H8 | Form | kode (CSS) | State terima kasih muncul halus |

Aturan bersama: tidak ada byte motion sebelum `load`. Tiap paket mulai dimuat saat section-nya berjarak ≤ 1 layar dari viewport. Reduced motion, `saveData`, atau `effectiveType` 2g/3g → hanya poster, tidak ada sequence atau video yang diunduh. Tanpa JavaScript: semua teks, angka, dan poster tetap tampil (animasi hanya aktif setelah JS menambah kelas `js-motion` di `<html>`). Setiap kanvas, video, dan gambar punya `aspect-ratio` atau width/height sebelum aset dimuat.

### 8.2 Anggaran (dihitung dari byte file sebenarnya)

| | Desktop | Mobile |
|---|---|---|
| M1 hero, semua frame | 2,35 MB (120) | 0,74 MB (60) |
| M1 hero, tier sampai "tiap frame kedua" | 1,19 MB (61) | — |
| M1 poster | 0,07 MB | 0,04 MB |
| M2 6 loop + poster | 0,23 MB (WebM) | 0,28 MB (MP4, kasus terburuk iOS) |
| M3 frames + poster | 2,07 MB | 0,80 MB |
| M4 video + poster | 0,26 MB | 0,27 MB |
| **Total, hero penuh** | **4,97 MB** | **2,13 MB** |
| **Total, hero 61 frame** | **3,82 MB** | — |
| Sebelum hero terlewati | 2,42 MB penuh / **1,26 MB** dengan 61 frame | 0,78 MB |

Mobile lolos semua batas tanpa perubahan (M3 mobile tidak perlu dipotong ke tiap frame kedua). Desktop: lihat konflik 9.1.

---

## 9. Konflik yang ditemukan dan usulan penyelesaian

**9.1 Hero desktop melebihi 1,5 MB sebelum hero terlewati.** Frame desktop M1 penuh = 2,35 MB. Usulan tanpa encode ulang: desktop memuat tier progresif sampai "tiap frame kedua" (61 frame, 1,19 MB + poster = 1,26 MB) dan berhenti di situ. Penggambaran tetap memakai frame terdekat yang sudah dimuat, sehingga scrub tetap mulus. Total desktop jadi 3,82 MB.

**9.2 Request homepage < 30 vs image sequence.** Setiap frame adalah satu request (61 hero + 150 cara kerja). Lighthouse menunggu jaringan tenang setelah `load`, jadi frame hero yang dimuat otomatis setelah `load` ikut terhitung. Usulan: frame hero mulai dimuat setelah `load` **dan** setelah interaksi pertama (scroll, sentuh, gerak pointer, keyboard). Saat itu poster sudah tampil di kanvas, jadi tidak ada yang terlihat kosong. M2, M3, M4 sudah otomatis tertunda karena menunggu section mendekati layar. Kriteria "< 30" kemudian diukur oleh Lighthouse tanpa interaksi.

**9.3 Angka homepage vs angka studi kasus.** Brief: angka homepage diambil dari studi kasus, bukan diketik ulang. Tetapi label homepage berbeda ("GMV Bellarosa dalam sebulan, April 2023" vs "GMV"), dan angka ketiga berbeda nilainya ("75%" di homepage, "75 : 25" di studi kasus). Usulan: repeater angka di studi kasus punya kolom opsional `nilai_beranda`, `label_beranda`, dan `tampil_beranda`. Sumber tetap satu (halaman studi kasus), dan izin publikasi tetap mengendalikan keduanya.

**9.4 Judul bukti di template layanan.** H2 "Bellarosa: iklan Rp 97 juta menghasilkan Rp 2 miliar" khusus untuk layanan iklan dan tidak ada di daftar field brief. Usulan: field `mea_bukti_judul` di halaman layanan, ditambah `mea_bukti_angka` (daftar label angka yang ditampilkan, berurutan, default "ROAS", "GMV" seperti prototipe). Nilai angkanya tetap dibaca dari studi kasus terkait, tidak diketik ulang.

**9.5 Cara kerja: prototipe tidak punya slot untuk M3.** Prototipe menampilkan 5 langkah sebagai deret horizontal (5 kolom). M3 butuh kotak sticky di kiri dan 5 blok langkah setinggi layar di kanan (mobile: kotak sticky di atas). Ini mengubah layout section, bukan copy-nya. Usulan: layout sticky dipakai di semua kondisi (termasuk reduced motion dan tanpa JS, dengan poster sebagai visual), supaya tata letak tidak berganti-ganti. Isi langkah dan kutipan tetap sama, gaya nomor dan garis mengikuti prototipe.

**9.6 Enam layanan: prototipe tidak punya slot untuk tile M2.** Usulan: tile M2 140 px di atas nama layanan pada setiap kartu, tanpa radius sudut (sesuai README M2). Kartu, teks, dan link tidak berubah.

**9.7 Studi kasus unggulan homepage.** Prototipe memakai foto Studio-2 (rasio 4:3). M4 dirender 1200×900 (rasio 4:3 juga) untuk diletakkan di samping teks studi kasus. Usulan: M4 menggantikan foto Studio-2 di section ini. Foto tim tetap ada di section Tim (pimpinan + mosaik 5 foto).

**9.8 Reduced motion di Cara kerja.** README M3 menyarankan menukar hold frame per langkah (frame 17, 47, 77, 107, 149). Itu berarti mengunduh frame sequence, sedangkan kriteria lulus meminta tab Network tanpa unduhan sequence. Brief menang: reduced motion → hanya poster (poster-last, langkah 5), langkah aktif tetap ditandai di HTML.

**9.9 Rumus progress hero.** README M1: `scrollY / (heroTop + heroHeight)`. Brief dan prompt 05: `scrollY / (tinggi hero × 0,6)`. Brief menang.

**9.10 M3 "versi pendek" untuk template layanan.** Peta motion menyebut versi pendek, tetapi paket M3 hanya punya satu versi. Usulan: template layanan memakai M3 yang sama. M2 "untuk ikon layanan terkait" di layanan tidak dipasang karena prototipe layanan tidak punya section layanan terkait (tidak menambah section).

**9.11 Breadcrumb: tautan tampil vs JSON-LD prototipe.** Layanan: tampil → `index.html#layanan`, schema → `/services/`. Studi kasus: tampil → `index.html`, schema → `/study-case/`. Usulan: tampil dan schema sama, ke halaman yang ada: Layanan → `/services/` (12356), Studi kasus → `/study-case/` (14645).

**9.12 Menu header.** Prototipe: Layanan → `layanan.html`, Studi kasus → `studi-kasus.html` (halaman tunggal). Usulan: lokasi menu WordPress `mea-orbit-utama` yang bisa diatur tim. Bila menu belum diatur, cadangannya: Layanan → `/#layanan`, Studi kasus → `/study-case/`, Tim → `/#tim`, Cara kerja → `/#cara-kerja`.

**9.13 Teks lama di prototipe studi kasus.** Meta description dan `Article.headline` di prototipe masih menyebut "brand baru" / "brand yang baru masuk pasar", bertentangan dengan brief bagian 9. Tema memakai H1 (`mea_h1`) untuk headline. Meta description 14512 dipegang Rank Math dan perlu diperbarui tim di wp-admin (di luar repo ini). Hal yang sama untuk title/description 14144 yang masih membahas iklan Google/Bing.

**9.14 Izin publikasi dan H1.** Saat `mea_izin_publikasi` dimatikan, angka terstruktur (panel hasil, angka homepage, bukti layanan) disembunyikan otomatis. Angka yang tertulis di H1, lede, dan cerita ("GMV Rp 8,2 miliar") tidak bisa disembunyikan otomatis. `CARA-PASANG.md` akan mencatat bahwa teks itu juga harus diedit bila izin dicabut.

**9.15 Teks "2lx" di video M4.** Di poster akhir M4, "ROAS 21x" dalam Instrument Serif terbaca seperti "2lx" karena bentuk angka 1. Angka yang sama tetap tampil benar sebagai HTML di halaman. Tidak saya ubah (encode ulang butuh izin). Kalau ingin diperbaiki, render ulang M4 di Motion Lab.

**9.16 Teks keterangan prototipe.** Kalimat "Prototipe: form ini belum mengirim data…" dan "Foto proses kerja tim. Foto produk Bellarosa tersedia di Media Library (ID 14489–14508)." adalah catatan prototipe dan tidak ditampilkan di WordPress.

**9.17 Media 14507 tidak ada.** Rentang foto produk Bellarosa di brief (14503–14508) ada celah: 14507 tidak ditemukan.

**9.18 Gambar WebP/AVIF.** Foto di Media Library saat ini hanya JPG (dan PNG untuk foto Bellarosa). `srcset` WebP butuh ukuran turunan WebP di server. Lihat keputusan 10.3.

---

## 10. Perlu konfirmasi sebelum Fase 1

1. **Konflik 9.1–9.8** (anggaran hero desktop 61 frame, pemuatan hero setelah interaksi pertama, kolom `_beranda`, `mea_bukti_judul`, layout sticky Cara kerja, tile M2 di kartu, M4 menggantikan Studio-2, reduced motion Cara kerja hanya poster). Setuju semua, atau ada yang diubah?
2. **Lingkungan uji lokal.** Untuk screenshot pembanding dan uji PHP tanpa warning saya perlu WordPress lokal. Di container ini ada PHP 8.3 tanpa MySQL. Usulan: unduh WordPress core, plugin SQLite Database Integration, dan Hello Elementor dari GitHub (repo resmi WordPress dan Elementor), dipakai hanya lokal, tidak masuk repo. Ini akses jaringan di luar Google Fonts, jadi perlu izin.
3. **Foto untuk uji lokal.** Untuk screenshot yang sebanding dengan prototipe, saya perlu 7 foto publik (15633, 15630, 15642, 16516–16519) dari meagency.co.id (GET file gambar publik saja, tanpa login, tanpa menulis apa pun). Kalau tidak boleh, uji lokal memakai gambar pengganti berukuran sama dan pembanding akhir dilakukan di staging.
4. **og:locale seluruh situs.** Tema mengubah og:locale ke `id_ID` hanya di halaman mea-orbit. Untuk seluruh situs, cara yang benar adalah mengganti bahasa situs di Settings → General (ini juga mengubah bahasa wp-admin pengguna yang tidak memilih bahasa sendiri), atau filter sitewide di child theme. Pilih: hanya halaman mea-orbit (default), atau sitewide lewat filter?
5. **WebP.** Usulan: setelah tema aktif di staging, regenerasi ukuran gambar untuk 7 foto yang dipakai dengan output WebP (filter `image_editor_output_format` khusus JPEG → WebP). Filter ini berlaku untuk unggahan baru di seluruh situs. Setuju diterapkan sitewide, atau cukup JPEG + srcset biasa?
6. **PHPCS (WordPress Coding Standards)** sebagai alat cek lokal lewat Composer (dev saja, tidak ikut zip). Boleh, atau cukup `php -l` dan review manual?

---

## 11. Rencana fase

| Fase | Isi | Hasil yang ditunjukkan |
|---|---|---|
| 1 | `style.css`, `functions.php`, `inc/scope.php`, `inc/setup.php`, `inc/assets.php`, token CSS, header/footer, skip link, font self-host + preload | Screenshot header/footer 1366 dan 390 px, daftar aset yang ter-enqueue di halaman mea-orbit vs halaman lain |
| 2 | Tiga template, field + meta box, opsi Homepage + kutipan, SEO, gambar, `docs/seed/` | Screenshot pembanding prototipe vs tema (1366 dan 390 px), contoh JSON-LD per halaman |
| 3 | Plugin `mea-leads` + `tests/run.php` | Hasil test, contoh payload + tanda tangan, screenshot admin "Leads MEA" |
| 4 | Salin aset motion, `motion.js`, M1–M4 + motion kode | Rekaman/screenshot per section, tab Network untuk reduced motion dan normal, angka byte terukur |
| 5 | QA kriteria lulus, uji regresi child theme (artikel blog, laporan klien, halaman Elementor lain: sebelum vs sesudah, di staging), `dist/` | Laporan QA + `dist/` |

Uji regresi child theme (artikel blog, laporan klien, halaman Elementor lain) butuh Elementor Pro dan konten asli, jadi dikerjakan di staging di Fase 5. Di lokal saya hanya bisa memastikan bahwa template induk tetap dipakai untuk halaman non-mea-orbit.
