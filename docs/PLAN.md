# PLAN — Tema `mea-orbit` + plugin `mea-leads` (meagency.co.id)

Fase 0 · ditulis 25 Okt 2026 · **revisi 3** (keluar penuh dari Elementor, aman untuk crawl Google) · status: **disetujui, Fase 1 berjalan**

> **Yang berubah di revisi 3.** Bagian yang berubah ditandai **[rev 3]**.
> 1. Tujuan dipertegas: tema mandiri **dan** meninggalkan page builder Elementor sepenuhnya, dengan risiko paling kecil untuk crawl Google (bagian 2, 14).
> 2. Temuan baru: **ke-212 artikel blog dibangun dengan Elementor** (bukan hanya halaman). Isinya widget Text Editor dan Heading berisi HTML biasa, jadi bisa dikonversi ke HTML klasik secara mekanis (bagian 12).
> 3. Header dan footer **satu kode yang sama di setiap URL** (bagian 3.1, 14.2).
> 4. Keluar dari Elementor dibuat dua langkah terpisah yang masing-masing diverifikasi: ganti tema dulu, konversi konten belakangan (bagian 11 Fase 6, 14).
> 5. Alat baru: pembanding SEO sebelum/sesudah (`tools/seo-parity/`) dan konverter Elementor → HTML klasik (bagian 3.5).
> 6. Laporan klien di luar perhatian karena akan pindah ke CDPS (bagian 2, 12).
> 7. Koreksi angka: artikel terbit **212**, bukan sekitar 780 (angka lama adalah hasil pencarian lintas jenis konten). Halaman terbit 1.929, sebagian besar laporan klien.

Dokumen ini memetakan apa yang akan dibangun di Fase 1–6. Bagian 9 berisi konflik yang ditemukan beserta penyelesaiannya. Bagian 10 berisi jawaban final pemilik.

> **Yang berubah di revisi 2.** Bagian yang berubah ditandai **[rev 2]**.
> 1. Arsitektur: `mea-orbit` sekarang **tema mandiri**, bukan child theme Hello Elementor. Tema ini berlaku untuk seluruh situs dan punya template umum sendiri (page, single, archive, search, 404, password). Ada dokumen baru `docs/ELEMENTOR-EXIT.md` (bagian 2, 3.1, 3.4, 6, 11, 12, 13).
> 2. og:locale `id_ID` diterapkan ke seluruh situs lewat filter tema (bagian 6).
> 3. WebP ditunda sampai setelah live (bagian 9.18, 13).
> 4. Uji lokal, unduhan foto, dan PHPCS diizinkan (bagian 10, 11).
> 5. Konflik 9.1–9.8 disetujui sesuai usulan (bagian 9).
>
> **Catatan:** jawaban 25 Okt nomor 4 sempat memilih child theme. **[rev 3] Dikonfirmasi pemilik: tema mandiri, dan Elementor ditinggalkan sepenuhnya.**

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
| Jawaban pemilik 25 Okt | (pesan sesi) | Kontrak HMAC, retensi, budget motion baru, alt text, staging menyusul |
| Jawaban final bagian 10 **[rev 2]** | (pesan sesi) | Tema mandiri, og:locale sitewide, WebP setelah live, izin uji lokal + foto + PHPCS, konflik 9.1–9.8 disetujui |

Fakta yang diverifikasi di situs live (read-only, sebelum Fase 0): homepage = page 13411, `/jasa-performance-ads/` = 14144, `/bellarosa/` = 14512, `/study-case/` = 14645, `/services/` = 12356. Semua link di prototipe hidup (jasa-kelola-tiktok-shop 365, ecommerce-service 14637, jasa-live-streaming 15567, affiliate 14662, jasa-konten 15559, eleven-basic 18653, skynet 16160, tumbleryuk 16144). Media 15633, 15630, 15642, 16516–16519 ada (JPG, alt kosong). Media Bellarosa 14489–14495 dan 14503–14506, 14508 ada; **14507 tidak ditemukan**. Plugin aktif yang relevan: Elementor + Pro 3.30, ElementsKit Lite, Unlimited Elements, Rank Math, Site Kit, WPCode, Meta Pixel, Classic Editor.

---

## 2. Keputusan yang sudah dikunci (ringkas)

- **[rev 2] Tema mandiri.** `mea-orbit` adalah tema klasik PHP yang berdiri sendiri (tanpa `Template:` induk) dan menggantikan Hello Elementor untuk **seluruh situs**. Ada dua lapis:
  - **Lapis umum** (semua halaman): header, footer, token, tipografi, dan template `page`, `single`, `archive`, `search`, `404`, form password. Artikel blog, laporan klien, dan halaman Elementor lama tampil dengan header/footer mea-orbit. Isi kontennya tetap dirender Elementor seperti sekarang.
  - **Lapis Orbit** (13411, 14144, 14512, dan yang dipindah kemudian): desain prototipe, motion, JSON-LD tambahan. Di halaman ini CSS/JS Elementor dan addon-nya tidak dimuat.
  - Plugin Elementor tetap aktif selama transisi. Rencana keluarnya ditulis di `docs/ELEMENTOR-EXIT.md`.
- **[rev 3] Keluar penuh dari Elementor.** Target akhirnya: Elementor, Elementor Pro, ElementsKit Lite, dan Unlimited Elements dinonaktifkan. Urutannya dua langkah terpisah (bagian 14): (1) ganti tema ke mea-orbit dengan Elementor masih aktif, sehingga isi konten tidak berubah sama sekali; (2) setelah stabil, konversi isi artikel dan halaman dari Elementor ke HTML klasik, lalu nonaktifkan Elementor.
- **[rev 3] Prioritas: aman untuk crawl Google.** URL, title, meta description, canonical, robots, H1, teks utama, link internal, gambar, dan JSON-LD setiap URL dijaga sama. Yang berubah hanya kepala, kaki, dan gaya halaman, dan perubahannya identik di semua URL.
- **[rev 3] Laporan klien** tidak menjadi perhatian karena akan pindah ke CDPS. Tampilannya boleh berubah saat Elementor dinonaktifkan.
- **[rev 2] og:locale** `id_ID` di seluruh situs lewat filter tema. Settings → General tidak diubah.
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
│   ├── ELEMENTOR-EXIT.md               ← [rev 2] (Fase 1, dilengkapi Fase 5) rencana keluar dari Elementor
│   ├── mea-orbit/                      ← brief + prototipe (baca saja)
│   ├── motion/                         ← 00–05 (baca saja)
│   └── seed/                           ← (Fase 2) isi field awal per halaman, JSON, untuk diisi di staging
├── motion-src/                         ← paket motion asli (baca saja)
├── wp-content/
│   ├── themes/mea-orbit/
│   └── plugins/mea-leads/
├── tests/                              ← (Fase 3) test tanpa dependency: php tests/run.php
├── tools/                              ← [rev 3] seo-parity/, elementor-to-classic/ (alat kerja, tidak masuk zip tema/plugin)
└── dist/                               ← (Fase 5) mea-orbit.zip, mea-leads.zip, CARA-PASANG.md
```

### 3.1 Tema `wp-content/themes/mea-orbit/`

| File | Isi |
|---|---|
| `style.css` | **[rev 2]** Header tema mandiri (tanpa `Template:`), Text Domain `mea-orbit`, `Requires PHP: 8.0`. Tidak berisi CSS (CSS di `assets/css/`) |
| `functions.php` | Hanya memuat file `inc/` |
| `inc/scope.php` | `mea_orbit_is_view()`: true hanya di homepage statis dan halaman bertemplate Orbit. Lapis Orbit (CSS section, motion, JSON-LD tambahan, dequeue Elementor) digerbang fungsi ini. Lapis umum berlaku di semua halaman |
| `inc/setup.php` | Theme support (`title-tag`, `post-thumbnails`, `html5` termasuk `search-form`, `comment-form`, `gallery`, `caption`, `style`, `script`), lokasi menu `mea-orbit-utama` dan `mea-orbit-footer`, ukuran gambar tambahan, `content_width` |
| `inc/assets.php` | **[rev 2]** Sitewide: `base.css` (token, reset, header, footer, tipografi) dan `content.css` (page/single/archive). Lapis Orbit: `orbit.css`, `motion.js`. Dequeue aset Elementor, Elementor Pro, ElementsKit, Unlimited Elements hanya di halaman Orbit. Preload Inter 400/600 sitewide, poster LCP di homepage |
| `inc/elementor.php` | **[rev 2, rev 3: sementara, dihapus di akhir Fase 6]** Kompatibilitas selama transisi: dukungan lokasi Theme Builder (`elementor/theme/register_locations`) supaya header/footer Elementor Pro tidak menimpa header/footer mea-orbit, wadah lebar penuh untuk halaman yang dibangun Elementor, reset dasar yang sebelumnya disediakan Hello Elementor (box-sizing, margin) supaya widget lama tidak bergeser |
| `inc/locale.php` | **[rev 2]** Filter og:locale Rank Math → `id_ID` dan `language_attributes` → `lang="id-ID"` di seluruh situs. Settings → General tidak diubah |
| `inc/fields.php` | `register_post_meta` (show_in_rest: true) + meta box per template + sanitasi |
| `inc/options.php` | Halaman opsi "Homepage" (Tampilan → Homepage MEA) dan daftar kutipan |
| `inc/seo.php` | JSON-LD lewat filter Rank Math (halaman Orbit), og:image cadangan, data breadcrumb |
| `inc/media.php` | `mea_orbit_img()`: `wp_get_attachment_image` dengan srcset, sizes, width/height, alt, lazy/eager |
| `inc/template-tags.php` | Render breadcrumb, pull quote, deret angka, panel hasil, kartu layanan |
| `header.php`, `footer.php` | **[rev 2, rev 3]** Satu header/footer untuk seluruh situs (desain prototipe), **kode yang sama di setiap URL**: tidak ada varian per template, tidak bergantung JavaScript, link sama di desktop dan mobile. Satu-satunya yang berbeda adalah tujuan tombol "Jadwalkan konsultasi": ke `#konsultasi` di halaman yang punya form, ke `/#konsultasi` di halaman lain. Tetap memanggil `wp_head`, `wp_body_open`, `wp_footer` supaya GTM, Site Kit, Meta Pixel, WPCode tetap jalan |
| `index.php` | **[rev 2]** Cadangan terakhir hierarki template: daftar posting |
| `page.php` | **[rev 2]** Halaman umum. Halaman yang dibangun Elementor dirender lebar penuh tanpa gaya prosa, supaya tata letak Elementor tidak berubah. Halaman klasik memakai gaya prosa `content.css` |
| `single.php` | **[rev 2]** Artikel blog: breadcrumb, H1, tanggal, gambar unggulan, isi, kategori/tag, navigasi artikel sebelumnya/berikutnya, komentar bila dibuka, ajakan konsultasi singkat (tombol WhatsApp, bukan form) |
| `archive.php`, `search.php`, `searchform.php` | **[rev 2]** Daftar posting (kategori, tag, penulis, tanggal) dan hasil pencarian, dengan paginasi |
| `404.php` | **[rev 2]** Halaman tidak ditemukan: pesan singkat, form cari, tautan ke beranda dan layanan |
| `comments.php` | **[rev 2]** Daftar dan form komentar bawaan WordPress, digaya ringan |
| Form password | **[rev 2]** Filter `the_password_form` dengan markup dan gaya mea-orbit. Perilaku proteksi (cookie, noindex dari WPCode) tidak diubah |
| `front-page.php` | Homepage (Lapis Orbit) |
| `page-templates/template-layanan.php` | Template "MEA Orbit · Layanan" |
| `page-templates/template-studi-kasus.php` | Template "MEA Orbit · Studi kasus" |
| `template-parts/home/*.php` | `hero`, `tim`, `layanan`, `cara-kerja`, `kasus-unggulan` |
| `template-parts/layanan/*.php` | `hero`, `cocok`, `dikerjakan`, `cara-kerja`, `bukti`, `faq` |
| `template-parts/studi-kasus/*.php` | `head`, `hasil`, `cerita`, `lain` |
| `template-parts/global/*.php` | `breadcrumb`, `kutipan`, `konsultasi` (form dari plugin, atau tombol WhatsApp bila plugin tidak aktif) |
| `assets/css/base.css`, `content.css`, `orbit.css` | **[rev 2]** CSS prototipe dipecah tiga: `base.css` sitewide (token, reset, header, footer, tombol, tipografi), `content.css` untuk template umum (gaya prosa diturunkan dari token prototipe, karena prototipe tidak punya desain artikel), `orbit.css` khusus halaman Orbit (section prototipe) |
| `assets/css/admin.css`, `assets/js/admin-fields.js` | Khusus wp-admin: repeater dan pemilih media di meta box (JS vanilla, memakai `wp.media` bawaan WordPress) |
| `assets/js/motion.js` | Satu modul motion, `defer`, hanya di halaman yang memakainya |
| `assets/fonts/` | Inter 400/500/600/700 + Instrument Serif 400, subset Latin, WOFF2, lisensi OFL |
| `assets/motion/{hero,sisi,proses,bellarosa}/` | Salinan file runtime dari `motion-src/` tanpa diubah (frames, posters, video, manifest, README). Folder `source/`, `demo/`, `checks/` tidak ikut ke tema supaya zip ringan |
| `screenshot.png` | Tangkapan homepage 1200×900 |

**[rev 2]** Tema tidak memanggil fungsi Elementor atau Hello Elementor secara langsung. Semua kode yang menyentuh Elementor ada di `inc/elementor.php` dan dijaga `did_action('elementor/loaded')`, sehingga tema tetap jalan normal saat Elementor dinonaktifkan.

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

### 3.4 `docs/ELEMENTOR-EXIT.md` **[rev 2, baru]**

Rencana keluar dari Elementor, ditulis di Fase 1 dan dilengkapi di Fase 5 setelah dicek di staging. Isi:

1. **Inventaris**: halaman yang memakai Elementor per template (`elementor_header_footer`, `elementor_theme`, `elementor_canvas`, default), template Theme Builder Elementor Pro (header, footer, single, archive, popup) beserta kondisinya, widget ElementsKit dan Unlimited Elements yang dipakai, Kit global (warna, font).
2. **Apa yang terjadi saat mea-orbit aktif**, per jenis halaman: artikel blog, laporan klien berpassword, halaman Elementor, halaman Canvas, arsip, pencarian.
3. **Urutan keluar**: pindahkan halaman layanan dan studi kasus ke template Orbit satu per satu → nonaktifkan Theme Builder header/footer → nonaktifkan Unlimited Elements dan ElementsKit Lite → evaluasi Elementor Pro → evaluasi Elementor.
4. **Cek sebelum tiap langkah**: halaman yang terdampak, cara mengecek, cara rollback.
5. **Rollback tema**: aktifkan kembali Hello Elementor dari Tampilan → Tema (data field dan lead tetap tersimpan).
6. **[rev 3] Konversi konten**: daftar artikel dan halaman per jenis widget (yang hanya Text Editor/Heading/Image bisa otomatis, yang memakai widget lain ditandai untuk migrasi manual), hasil uji paritas per URL, dan kapan Elementor boleh dinonaktifkan (syarat: 0 URL terindeks yang masih bergantung Elementor).

### 3.5 Alat bantu keluar dari Elementor **[rev 3, baru]**

Keduanya alat kerja, tidak masuk `dist/mea-orbit.zip` maupun `dist/mea-leads.zip`.

| Alat | Isi |
|---|---|
| `tools/seo-parity/parity.php` | CLI PHP tanpa dependency (DOM + curl bawaan PHP). Input: daftar URL dari sitemap Rank Math, alamat "sebelum" dan "sesudah". Per URL dibandingkan: kode status dan redirect, canonical, meta robots, title, meta description, jumlah dan teks H1, urutan H2/H3, teks utama (jumlah kata dan kemiripan), himpunan link internal, gambar (src dan alt), tipe JSON-LD, og:locale/og:image. Keluaran CSV + ringkasan: URL yang lolos, URL yang berbeda, beserta bedanya |
| `tools/elementor-to-classic/` | Plugin sekali pakai `mea-migrasi-elementor` (zip terpisah, `dist/tools/`). Mode uji coba (laporan saja) dan mode konversi per batch. Konversi: susun HTML klasik dari `_elementor_data` (Text Editor, Heading, Image, Spacer, Divider, Button), simpan ke `post_content`, cadangkan data Elementor asli di meta `_mea_elementor_cadangan` + revisi WordPress, lalu lepaskan mode Elementor untuk URL itu. Bisa dikembalikan per URL. Widget lain → URL ditandai "manual", tidak dikonversi |

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
| 6 | Studi kasus unggulan | Label, H2, lede, link (K: judul kartu dan ringkasan kartu). Visual M4 menggantikan foto Studio-2 (lihat konflik 9.7) |
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
- **[rev 2] og:locale**: filter Rank Math → `id_ID` di **seluruh situs** (`inc/locale.php`), ditambah `lang="id-ID"` di `<html>`. Settings → General tidak diubah, jadi bahasa wp-admin tetap seperti sekarang.
- **[rev 2] Template umum**: satu H1 per halaman juga berlaku untuk `single`, `page`, `archive`, `search`, `404`. JSON-LD halaman umum tetap sepenuhnya milik Rank Math; tema tidak menambah apa pun di sana.
- **og:image**: memakai pilihan Rank Math per halaman. Bila kosong, tema mengisi: homepage → foto pimpinan 15633, layanan → foto hero, studi kasus → foto proses pertama.
- Satu H1 per halaman. Judul section memakai H2/H3 seperti prototipe.
- **[rev 2] Halaman berpassword** (termasuk laporan klien): `page.php`, `single.php`, dan template Orbit memanggil `post_password_required()` dan menampilkan form password mea-orbit. Mekanisme proteksi bawaan WordPress (cookie `wp-postpass_…`) dan kode noindex dari WPCode tidak disentuh. Tema tidak mencetak meta robots apa pun.

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

## 9. Konflik yang ditemukan dan penyelesaiannya

**[rev 2]** Usulan 9.1–9.8 **disetujui pemilik** dan menjadi keputusan. 9.9–9.17 adalah penyelesaian yang saya ambil sesuai aturan "brief menang" atau catatan untuk tim. 9.18 diputuskan di bagian 10.

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

**9.18 Gambar WebP/AVIF.** Foto di Media Library saat ini hanya JPG (dan PNG untuk foto Bellarosa). `srcset` WebP butuh ukuran turunan WebP di server. **[rev 2] Diputuskan:** JPEG/PNG + `srcset` biasa dulu, tanpa filter sitewide. WebP dicatat sebagai pekerjaan setelah live (bagian 13).

---

## 10. Jawaban final pemilik **[rev 2]**

| # | Pertanyaan | Keputusan |
|---|---|---|
| — | Konflik 9.1–9.8 | Disetujui semua sesuai usulan |
| 1 | Lingkungan uji lokal | Boleh. Unduh WordPress core, SQLite Database Integration, Hello Elementor (untuk screenshot "sebelum"), dan plugin Elementor gratis (untuk menguji halaman Elementor lama di tema baru). Sumber resmi (wordpress.org / GitHub). Hanya lokal, tidak masuk repo |
| 2 | Foto untuk uji lokal | Boleh. GET read-only 7 foto (15633, 15630, 15642, 16516–16519) dan foto produk Bellarosa (14489–14495, 14503–14506, 14508). Tidak masuk repo |
| 3 | og:locale | `id_ID` sitewide lewat filter tema. Settings → General tidak diubah |
| 4 | WebP | JPEG + srcset biasa dulu. WebP setelah live |
| 5 | PHPCS | Boleh, dev saja, tidak ikut zip |
| — | Arsitektur | Tema mandiri (bukan child theme), template umum lengkap, `docs/ELEMENTOR-EXIT.md` |
| — | **[rev 3]** Elementor | Ditinggalkan sepenuhnya, dengan risiko crawl Google paling kecil. Header dan footer sama di setiap halaman. Laporan klien tidak jadi masalah (pindah ke CDPS) |

### 10.1 Konfirmasi rev 3 **[rev 3, baru]** — dijawab pemilik: "lanjut pakai usulan" (keempat usulan disetujui)

1. **Fase 6 masuk proyek ini?** Konversi 212 artikel dan halaman pemasaran dari Elementor ke HTML klasik, lalu nonaktifkan Elementor dan addon-nya. Usulan: ya, dikerjakan setelah tema baru live dan stabil sekitar 2 minggu (Search Console bersih).
2. **Menu di HP.** Di prototipe, menu disembunyikan di bawah 900 px (hanya tombol konsultasi). Link-nya tetap ada di HTML, jadi tetap terbaca Google, tetapi pengunjung HP tidak punya menu. Usulan: tombol "Menu" sederhana tanpa JavaScript (`<details>`), link yang sama persis dengan desktop.
3. **Link di footer.** Footer prototipe hanya berisi kontak (WhatsApp, Instagram, TikTok). Footer lama kemungkinan berisi link ke halaman layanan dan blog. Menghapus link sitewide mengubah struktur link internal yang dibaca Google. Usulan: footer memakai desain prototipe, ditambah satu kolom "Layanan" dan link "Blog" berisi halaman penting yang sekarang ditautkan dari header/footer lama (diinventaris di staging, diatur lewat menu WordPress).
4. **Akses baca untuk uji paritas.** `tools/seo-parity` perlu GET halaman publik produksi (sebelum) dan staging (sesudah) di Fase 5 dan Fase 6. Hanya membaca, tidak menulis. Boleh?


---

## 11. Rencana fase **[rev 2, rev 3]**

| Fase | Isi | Hasil yang ditunjukkan |
|---|---|---|
| 1 | Lingkungan lokal (WordPress + SQLite + Hello Elementor + Elementor gratis), screenshot "sebelum" dengan Hello Elementor. Kerangka tema mandiri: `style.css`, `functions.php`, `inc/scope.php`, `inc/setup.php`, `inc/assets.php`, `inc/elementor.php`, `inc/locale.php`, token, header/footer, skip link, font self-host + preload, **template umum** (`index`, `page`, `single`, `archive`, `search`, `404`, `comments`, form password). Draf pertama `docs/ELEMENTOR-EXIT.md` | Screenshot header/footer dan template umum di 1366 dan 390 px. Screenshot sebelum (Hello) vs sesudah (mea-orbit) untuk satu artikel, satu halaman berpassword, satu halaman Elementor contoh. Daftar aset ter-enqueue di halaman Orbit vs halaman lain |
| 2 | Tiga template Orbit, field + meta box, opsi Homepage + kutipan, SEO, gambar, `docs/seed/` | Screenshot pembanding prototipe vs tema (1366 dan 390 px), contoh JSON-LD per halaman |
| 3 | Plugin `mea-leads` + `tests/run.php` | Hasil test, contoh payload + tanda tangan, screenshot admin "Leads MEA" |
| 4 | Salin aset motion, `motion.js`, M1–M4 + motion kode | Rekaman/screenshot per section, tab Network untuk reduced motion dan normal, angka byte terukur |
| 5 | **[rev 3]** `tools/seo-parity`. QA kriteria lulus. Di staging: uji paritas seluruh URL di sitemap (produksi vs staging) dengan Elementor masih aktif, `ELEMENTOR-EXIT.md` versi staging, `dist/` | Laporan QA, laporan paritas (target: 0 URL berbeda di title, meta, canonical, robots, H1, teks utama, JSON-LD), `dist/` |
| 6 | **[rev 3, baru, perlu konfirmasi 10.1 nomor 1]** Setelah tema live dan stabil: `tools/elementor-to-classic`, uji coba (laporan widget per URL), konversi per batch di staging → uji paritas per batch → terapkan ke produksi per batch. Halaman bertanda "manual" dipindah ke template Orbit atau ditulis ulang sebagai HTML klasik. Terakhir: nonaktifkan Elementor Pro, ElementsKit, Unlimited Elements, Elementor; hapus `inc/elementor.php` | Laporan per batch, laporan paritas, daftar URL manual, konfirmasi 0 URL bergantung Elementor |

PHPCS (WordPress Coding Standards) dijalankan lokal di setiap fase. Composer dan PHPCS dipasang di luar repo (scratchpad), jadi tidak ada `vendor/` atau `composer.json` yang ikut ter-commit atau masuk zip.

---

## 12. Konsekuensi tema mandiri **[rev 2, rev 3]**

1. **Header dan footer seluruh situs berubah sekali, serentak, dan identik** begitu mea-orbit aktif: 212 artikel, halaman pemasaran, dan laporan klien. Isi halaman tetap dirender Elementor sampai Fase 6, jadi teks dan struktur konten tidak berubah di langkah ini.
2. **Kriteria uji regresi = paritas SEO**, bukan "tampil identik". Untuk setiap URL di sitemap: status, canonical, robots, title, meta, H1, teks utama, link internal di area konten, gambar, dan JSON-LD harus sama. Yang boleh berbeda hanya link di header/footer (dan perbedaan itu sama di semua URL). Ditambah screenshot sebelum/sesudah untuk contoh artikel dan halaman.
3. **Artikel blog, arsip, pencarian, dan 404 tidak punya prototipe.** Desainnya saya turunkan dari token dan komponen prototipe (font, warna, tombol, breadcrumb, lebar baca), lalu saya tunjukkan lewat screenshot di Fase 1 untuk disetujui sebelum lanjut.
4. **[rev 3] Semua artikel dibangun dengan Elementor.** Contoh yang dicek (ID 18546 dan 17265): seluruh isi ada di widget Text Editor dan Heading, berisi paragraf, H2/H3, gambar dengan srcset, link internal, kotak CTA ber-style inline, dan FAQ JSON-LD di dalam konten. Semua itu bisa dipindah apa adanya ke HTML klasik. 15 artikel memakai template `elementor_theme`; sisanya template default. Widget lain (bila ada) baru ketahuan lewat uji coba konverter di Fase 6.
5. **[rev 3] Isi artikel tidak diedit.** Kotak CTA di artikel memakai warna hijau dan nama "MEA Digital Agency" (tidak sesuai brand MEA Agency). Itu konten, bukan tema. Mengubahnya sekaligus dengan ganti tema menambah risiko crawl, jadi dicatat sebagai pekerjaan setelah live (bagian 13).
6. **[Fase 1] Template Theme Builder Elementor Pro.** Header New (10093) dan Footer New (17281) otomatis tidak tampil lagi, karena tema mendaftarkan lokasi Theme Builder dan tidak pernah memanggil lokasi `header`/`footer`. **Single Post Template (13811) dan Blog Archive Template (14400) tetap aktif di langkah 1**, sehingga tata letak artikel dan arsip tidak berubah sama sekali. Keduanya baru dinonaktifkan di Fase 6, dan sejak itu `single.php`/`archive.php` mea-orbit yang dipakai. Perilaku ini hanya bisa diuji dengan Elementor Pro di staging.
7. **Halaman Elementor Canvas** (`elementor_canvas`) tanpa header/footer sampai dikonversi di Fase 6, karena template itu milik plugin Elementor.
8. **Gaya dasar Hello Elementor hilang.** Widget Elementor mungkin mengandalkan reset CSS Hello Elementor. `inc/elementor.php` menyediakan reset setara sampai Fase 6. Dicek lewat screenshot dan paritas.
9. **Rollback tetap satu langkah** di setiap tahap: aktifkan kembali Hello Elementor (langkah 1), atau kembalikan konversi per URL dari cadangan (langkah 2). Data field, opsi, dan lead tidak hilang.
10. **Laporan klien** tidak diuji paritas (pindah ke CDPS, berpassword, dan noindex).

---

## 13. Pekerjaan setelah live **[rev 2, rev 3]**

- **WebP/AVIF**: hasilkan ukuran turunan WebP untuk foto yang dipakai template, lalu tambahkan ke `srcset`.
- Fase 6 (bila disetujui): konversi konten Elementor dan nonaktifkan Elementor.
- Pindahkan halaman layanan dan studi kasus lain ke template Orbit.
- **[rev 3]** Seragamkan kotak CTA di artikel ke brand MEA Agency (warna, nama, sapaan), satu batch per kali dengan uji paritas.
- Template About Us (kutipan `k3`).

---

## 14. Strategi aman untuk crawl Google **[rev 3, baru]**

### 14.1 Prinsip

Ubah satu hal pada satu waktu, ukur, baru lanjut. Google paling mudah menerima perubahan tampilan bila URL, isi utama, dan sinyal SEO tiap URL tidak bergerak.

| Dijaga sama | Cara |
|---|---|
| URL dan permalink | Tidak ada slug yang diubah. Bila terpaksa: redirect 301 lewat modul Redirections Rank Math, dicatat |
| Title, meta description, canonical, robots, sitemap, OG | Tetap dipegang Rank Math. Tema tidak mencetak tag ini |
| H1 | Artikel: judul posting (sama dengan sekarang). Halaman Orbit: H1 dari field, satu per halaman |
| Teks utama, heading, gambar, link di konten | Langkah 1: tetap dirender Elementor. Langkah 2: dipindah apa adanya ke HTML klasik, diverifikasi alat paritas |
| JSON-LD | Rank Math tetap. FAQ JSON-LD di dalam konten artikel ikut dipindah. Tambahan tema hanya di halaman Orbit |
| Konten mobile | DOM yang sama untuk desktop dan HP (Google memakai versi HP). Tidak ada teks yang hanya muncul di desktop |
| Gambar | URL Media Library tidak berubah |

### 14.2 Header dan footer identik

- Satu `header.php` dan satu `footer.php` untuk setiap URL, termasuk 404, pencarian, dan halaman berpassword.
- Link navigasi berupa HTML statis yang dirender server, sama di desktop dan HP, tidak dibuat oleh JavaScript.
- Isi menu diatur dari dua menu WordPress (`mea-orbit-utama`, `mea-orbit-footer`), jadi perubahan menu berlaku serentak di semua halaman.
- Satu landmark `<header>`, `<nav>`, `<main>`, `<footer>` per halaman.

### 14.3 Urutan peluncuran

1. Staging: tema mea-orbit aktif, Elementor masih aktif. Header/footer Theme Builder otomatis tergantikan. Template Single Post dan Blog Archive tetap aktif.
2. Uji paritas seluruh sitemap (produksi vs staging). Lanjut hanya bila tidak ada beda di luar header/footer.
3. Cek staging tidak terbawa `noindex` ke produksi (Settings → Reading "Discourage search engines" harus mati di produksi setelah push).
4. Produksi: aktifkan tema di jam sepi. Bersihkan cache. Uji paritas ulang terhadap snapshot "sebelum".
5. Search Console: kirim ulang sitemap, pantau Pages (indexing), 404, dan Core Web Vitals selama 2–4 minggu.
6. Setelah stabil: Fase 6 per batch (misalnya 25 URL), setiap batch melewati staging → paritas → produksi → pantau.
7. Nonaktifkan Elementor hanya bila laporan konverter menunjukkan 0 URL terindeks yang masih bergantung Elementor.

### 14.4 Rollback

- Langkah 1 (tema): aktifkan Hello Elementor. Isi tidak pernah berubah, jadi tidak ada yang perlu dipulihkan.
- Langkah 2 (konten): kembalikan URL atau batch dari `_mea_elementor_cadangan` lewat konverter, atau dari revisi WordPress.

---

## 15. Catatan Fase 1 **[baru]**

### 15.1 Batasan jaringan container sesi ini

| Host | Status | Dampak | Penanganan |
|---|---|---|---|
| wordpress.org, downloads.wordpress.org | Diblokir (403) | Tidak bisa unduh zip resmi | WordPress 7.1.3, SQLite Database Integration 3.1.1, dan Hello Elementor 3.5.1 diambil dari repo GitHub resmi (git clone) dan di-build lokal |
| composer.elementor.com | Diblokir (403) | Plugin Elementor 4.0.8 tidak bisa di-build | Konten Elementor disimulasikan dengan markup yang sama persis dengan artikel 18546 di produksi. Uji visual dengan plugin Elementor dan Elementor Pro dilakukan di staging |
| www.meagency.co.id | Diblokir (403) | Foto tidak bisa diunduh; alat paritas tidak bisa membaca produksi dari container ini | Uji lokal memakai gambar pengganti berukuran sama. `tools/seo-parity` dibuat agar bisa dijalankan dari mesin mana pun yang punya PHP (laptop tim atau SSH hosting), lalu hasil CSV-nya dibagikan |

Konektor MEAgency (read-only) tetap bisa dipakai untuk membaca data situs.

### 15.2 Font

Google Fonts menyajikan Inter sebagai satu file variable (bobot 400–700, subset Latin, 48 KB). Preload Inter cukup satu file. Instrument Serif 400 (21 KB) tidak di-preload. Lisensi OFL disertakan di `assets/fonts/`.

### 15.3 Aset per halaman (hasil uji lokal)

Halaman umum hanya memuat `base.css` + `content.css`. Selama Elementor aktif, atau bila kontennya dibangun Elementor, ditambah `elementor-compat.css`. Belum ada JavaScript tema di halaman umum.

---

## 16. Catatan Fase 2 **[baru]**

### 16.1 Yang dibangun

- `front-page.php`, `page-templates/template-layanan.php`, `page-templates/template-studi-kasus.php`, dan `template-parts/` per section. Urutan section dan copy sama dengan prototipe.
- `inc/fields.php`: satu definisi field dipakai untuk `register_post_meta` (show_in_rest + skema, kunci asing ditolak HTTP 400), sanitasi, meta box, dan pembacaan nilai. Meta box muncul setelah template dipilih dan disimpan.
- `inc/options.php`: Tampilan → Homepage MEA (opsi `mea_orbit_home`) dan daftar kutipan (`mea_orbit_kutipan`, teks final brief 3.4). Keduanya terbaca di `/wp/v2/settings`.
- `inc/seo.php`: JSON-LD disisipkan ke graph Rank Math (Organization dilengkapi di homepage; Service + FAQPage + BreadcrumbList di layanan; Article + BreadcrumbList di studi kasus). Hanya node Rank Math bertipe sama yang diganti. Cadangan mandiri bila Rank Math atau modul schema-nya nonaktif.
- `assets/css/orbit.css`, `assets/css/admin.css`, `assets/js/admin-fields.js` (repeater dan pemilih gambar, JS vanilla, hanya di wp-admin).
- `assets/motion/`: salinan file runtime M1–M4 dari `motion-src/` tanpa diubah (frames, posters, video, README). Poster sudah dipakai di layout. Animasinya di Fase 4.
- `docs/seed/`: isi awal 14144 dan 14512 (format body REST) dan draf alt text.

### 16.2 Keputusan kecil selama Fase 2

| # | Keputusan | Alasan |
|---|---|---|
| a | Field halaman layanan dan studi kasus **tanpa default berisi copy** | Template akan dipakai halaman lain; default berisi copy Bellarosa atau iklan akan bocor ke sana. Copy prototipe ada di `docs/seed/` |
| b | Field tambahan `foto_hero_alt` (layanan) dan pilihan loop M2 per kartu layanan (opsi Homepage) | Alt per penempatan dan pemetaan kartu → loop Enam Sisi |
| c | Visual M4 hanya dipakai bila studi kasus unggulan adalah Bellarosa dengan izin aktif. Studi kasus lain (atau izin dicabut) memakai foto proses kerja pertamanya | Angka Bellarosa terbakar di video M4 |
| d | Reduced motion dan ukuran HP untuk poster diatur dengan `<picture><source media>` | Berjalan tanpa JavaScript; tidak ada unduhan sequence |
| e | Di HP, kotak sticky Cara kerja punya latar selebar layar | Teks langkah tidak terlihat di samping kotak saat discroll |
| f | Tinggi blok langkah 28svh (desktop) / 26svh (HP) | Cukup untuk scrub 30 frame per langkah, tanpa ruang kosong berlebihan |
| g | Form konsultasi memanggil `mea_leads_form()` dari plugin (Fase 3). Tanpa plugin: tombol WhatsApp | Tema tetap berfungsi tanpa plugin |
| h | Menu cadangan menandai "Layanan" di template layanan dan "Studi kasus" di template studi kasus (`aria-current`) | Sama dengan prototipe |

### 16.3 Hasil uji lokal

- Prototipe vs tema, 1366 dan 390 px: H1 dan urutan H2 sama, tanpa error JS, tanpa scroll horizontal (`docs/qa/fase-2/banding-*.jpg`). Perbedaan yang memang direncanakan: tile M2, kotak sticky M3, poster M4, dan form (Fase 3).
- Halaman Orbit hanya memuat `base.css` + `orbit.css`. Belum ada JS. Satu gambar `fetchpriority="high"` per halaman (poster hero / foto hero layanan). Tidak ada hotlink ke meagency.co.id.
- Request homepage setelah seluruh halaman discroll: 20 (motion belum dipasang).
- Izin publikasi dimatikan → angka homepage, Panel Hasil, bukti layanan, dan M4 hilang; dinyalakan lagi → kembali.
- Meta box disimpan dari wp-admin tanpa perubahan → isi meta identik dengan seed. Opsi Homepage tersimpan dan notifikasinya tampil.
- PHPCS (WordPress) bersih, tanpa warning/notice PHP dengan WP_DEBUG.

### 16.4 Dicek di staging

- Filter og:image cadangan (`rank_math/opengraph/*/add_additional_images`) dan graph JSON-LD Rank Math yang sebenarnya, lewat Rich Results Test.
- Title dan meta description 14144 dan 14512 di Rank Math masih memakai teks lama (Google/Bing, "brand baru"). Perlu diperbarui tim di wp-admin.
- Alt foto produk Bellarosa di `docs/seed/media-alt.json` masih netral; perlu dicek per foto.

## 17. Catatan Fase 3 **[baru]**

### 17.1 Yang dibangun

- Plugin `wp-content/plugins/mea-leads/` (mandiri, tidak bergantung pada tema):
  - `includes/core.php`: fungsi murni tanpa WordPress (validasi, payload v1, HMAC, klasifikasi respons, backoff, sel CSV aman). Diuji `tests/run.php` tanpa WordPress.
  - `includes/db.php`: tabel `{prefix}mea_leads` (dbDelta, versi skema `MEA_LEADS_DB_VERSION`).
  - `includes/form.php` + `assets/js/form.js`: markup form sama dengan prototipe, validasi klien dan server dengan pesan yang sama, tampilan terima kasih + tombol WhatsApp berisi teks, `dataLayer.push({event:'generate_lead', form_source, lead_omzet})`. Tersedia lewat `mea_leads_form()`, shortcode `[mea_lead_form]`, dan blok `mea/lead-form`.
  - `includes/rest.php`: `GET /wp-json/mea-leads/v1/token`, `POST /wp-json/mea-leads/v1/kirim`, dan `admin-post.php?action=mea_leads_kirim` (form tetap jalan tanpa JavaScript).
  - `includes/sender.php` + `includes/cron.php`: pengiriman sesuai `docs/WEBSITE_LEAD_CONTRACT.md`, retry 1 mnt / 5 mnt / 30 mnt / 2 jam / 12 jam lalu `gagal`, sapuan per jam, retensi harian.
  - `includes/admin.php` + `class-mea-leads-list-table.php`: menu **Leads MEA** (filter status, sumber, tanggal; riwayat per lead; kirim ulang per baris dan massal; ekspor CSV mengikuti filter).
  - `uninstall.php`: hapus jadwal cron. Tabel hanya dihapus bila `MEA_LEADS_HAPUS_SAAT_UNINSTALL` bernilai `true`.
- Tema: form konsultasi memanggil plugin (sudah sejak Fase 2), ditambah gaya pesan galat di `orbit.css`. Tanpa plugin, tema menampilkan tombol WhatsApp.
- `docs/WEBSITE_LEAD_CONTRACT.md` bagian 5: vektor uji HMAC tetap (dicek dengan openssl) untuk dipakai test di sisi CDPS.

### 17.2 Keputusan kecil selama Fase 3

| # | Keputusan | Alasan |
|---|---|---|
| a | Body JSON dibentuk sekali saat lead disimpan dan disimpan byte demi byte. Setiap percobaan memakai body dan idempotency key yang sama; hanya timestamp dan signature yang baru | Penerima bisa membuang duplikat dengan aman |
| b | Tanpa konstanta `MEA_LEADS_ENDPOINT`/`MEA_LEADS_SECRET`, lead disimpan dengan status `menunggu` dan tidak ada request keluar. Sapuan per jam mengirimnya setelah konstanta diisi | Plugin aman diaktifkan sebelum CDPS siap |
| c | Nonce diambil lewat `GET /token` (no-store) tepat sebelum mengirim. Token waktu `mea_t` tetap dari HTML halaman | Halaman yang di-cache (LiteSpeed/CDN) tidak membuat nonce kedaluwarsa. Di halaman yang di-cache, `mea_t` sudah lama sehingga jebakan waktu tidak menyaring apa pun; honeypot, nonce, dan batas laju tetap bekerja. Token waktu sengaja tidak diperbarui saat form disentuh supaya pengunjung yang mengisi cepat dengan autofill tidak dianggap bot dan lead-nya hilang |
| d | Honeypot `website` dan isian di bawah 3 detik mendapat jawaban sukses tetapi tidak disimpan | Bot tidak belajar dari pesan galat |
| e | Batas 5 kiriman per 10 menit per IP (`mea_leads_rate_limit`). IP diambil dari `REMOTE_ADDR`, bisa diganti lewat filter `mea_leads_client_ip` | Tidak mempercayai header `X-Forwarded-For` secara default (mudah dipalsukan) |
| f | Kunci baris 120 detik (`kunci_sampai`) sebelum mengirim | Dua proses cron tidak mengirim lead yang sama |
| g | Kirim ulang manual mengulang hitungan percobaan dari nol; lead `terkirim` tidak bisa dikirim ulang | Mencegah lead ganda di CDPS |
| h | Retensi: 90 hari setelah `terkirim`, nama, WA, link toko, UTM, dan body dikosongkan; status, tanggal, sumber, halaman asal, omzet, layanan, dan ID lead CDPS tetap | Sesuai jawaban 6 |
| i | Ekspor CSV memakai BOM UTF-8 dan sel yang diawali `= + - @` diberi tanda kutip tunggal | Aman dibuka di Excel/Sheets (formula injection) |

### 17.3 Hasil uji lokal

Penerima tiruan di `localhost:8090` memverifikasi signature, timestamp (±300 dtk), skema, dan idempotency key, sama seperti kontrak. Laporan lengkap di `docs/qa/fase-3/`.

- Unit (`php tests/run.php`): 49/49 lolos, termasuk vektor openssl.
- Browser (`uji-browser.json`): form tampil dengan sumber yang benar; empat pesan galat sama persis dengan prototipe; tampilan terima kasih; tautan WhatsApp terisi; `dataLayer` benar; tanpa error JS; jalur tanpa JavaScript kembali ke halaman asal dengan tampilan terima kasih.
- Server (`uji-server.txt`): tanpa konstanta tidak ada request; retry 500 dengan jeda 60/300/1800/7200/43200 lalu `gagal` setelah 6 percobaan; kirim ulang manual menghasilkan tepat satu request sukses; 401 langsung `gagal`; idempotensi; kunci; retensi 91 vs 89 hari; nonce, honeypot, jebakan waktu, token palsu, batas laju.
- Admin (`uji-admin.json`, `admin-*.jpg`): paginasi, filter, ekspor CSV mengikuti filter, riwayat, kirim ulang massal dengan notifikasi.
- PHPCS (WordPress) bersih untuk tema dan plugin; `php -l` bersih.

### 17.4 Dicek di staging

- **IP pengunjung di balik CDN/proxy Hostinger.** Bila semua request terlihat dari IP yang sama, batas laju akan memblokir semua pengunjung. Cek `REMOTE_ADDR` di staging; bila perlu, tambahkan filter `mea_leads_client_ip` yang membaca header CDN yang tepercaya (mu-plugin kecil, bukan di plugin).
- **WP-Cron.** Bila staging/produksi memakai `DISABLE_WP_CRON`, pasang cron hPanel ke `wp-cron.php` tiap 5 menit. Tanpa itu, lead baru menunggu sampai cron berjalan.
- **Cache halaman.** Pastikan `/wp-json/mea-leads/v1/*` dan `admin-post.php` tidak di-cache (respons sudah mengirim `Cache-Control: no-store`). Uji kirim form dari halaman yang sudah di-cache.
- **Konstanta.** `MEA_LEADS_ENDPOINT` dan `MEA_LEADS_SECRET` diisi di `wp-config.php` staging dengan secret staging (dikirim lewat saluran privat). Tidak ada service-role key di WordPress.
- **Uji ujung ke ujung dengan penerima CDPS staging** memakai vektor di kontrak bagian 5, lalu satu lead nyata dari form staging.

## 18. Catatan Fase 4 **[baru]**

### 18.1 Yang dibangun

- `assets/js/motion.js` (satu modul, tanpa library, `defer`, hanya di halaman Orbit, 0 dependency baru). three.js tidak dimuat.
  - **M1 hero dan M3 cara kerja**: satu komponen scroll-scrub (canvas + IntersectionObserver + rAF). Canvas dibuat JS di atas poster dan baru tampil setelah frame pertama tergambar; poster tetap di DOM (alt tetap terbaca). Muat progresif: 0, terakhir, tiap 16, 8, 4, 2, lalu sisanya.
  - **M2 enam sisi**: `<video>` dibuat setelah `load` saat section ≤ 1 layar. Desktop: hover/focus kartu, selesai satu siklus saat keluar. HP: 60% terlihat.
  - **M4 Kurva Bellarosa**: diputar sekali saat 60% terlihat, menahan frame akhir.
  - **Motion kode**: hitung naik (H2, Panel Hasil, bukti layanan), garis orbit tipis di bawah angka hero, foto reveal (mask naik 12 px, 600 ms; mosaik stagger 80 ms), kutipan pimpinan dan judul studi kasus per baris, garis meta, bar komposisi mengisi dari kiri, garis kutipan cerita, penanda bab aktif, hover studi kasus lain (CSS), tampilan terima kasih form (CSS).
- `inc/assets.php`: enqueue `motion.js` (defer) + `window.meaMotion.base`; skrip inline 1 baris di `<head>` yang menambah kelas `js-motion` (tanpa reduced motion).
- `inc/orbit.php`: hitung naik juga mengenali pemisah ribuan titik ("3.000").
- `orbit.css`: bagian "Motion".

### 18.2 Keputusan kecil selama Fase 4

| # | Keputusan | Alasan |
|---|---|---|
| a | Keadaan awal reveal diatur CSS di bawah `html.js-motion`, kelas dipasang skrip inline di `<head>` | Konten tidak tampil lalu hilang lagi (tanpa kedip). Tanpa JS kelas tidak ada, semua tampil. Pengaman: bila `motion.js` gagal dimuat, semua tampil setelah 3 detik; bila skrip baru jalan setelah 2,5 detik, semua langsung tampil tanpa animasi |
| b | Angka yang sudah terlihat saat skrip jalan tidak dihitung ulang dari nol | Tidak berkedip ke 0. Teks asli tetap untuk pembaca layar (`screen-reader-text`), angka berjalan `aria-hidden` |
| c | Progress M3 diukur dari garis acuan (tengah layar; di HP tengah area di bawah kotak sticky) terhadap blok langkah pertama sampai terakhir | Setara rumus README M3 bila padding 50svh, tetapi tetap sinkron dengan jarak langkah tema (16.2 f). Langkah aktif = `min(4, floor(frame150 / 30))`, juga diperbarui saat reduced motion (tanpa animasi) |
| d | Interaksi pertama untuk hero (PLAN 9.2) mengabaikan `pointermove` tanpa perpindahan | Chrome mengirim `pointermove` sintetis saat tata letak berubah di bawah kursor yang diam; tanpa filter ini 61 frame ikut dimuat saat halaman diam |
| e | M4: poster = frame akhir (grafik penuh), video mulai dari grafik kosong. Video baru menggantikan poster saat figure **tidak** terlihat; bila `play()` ditolak, poster kembali | Pengunjung tidak melihat grafik penuh lalu kosong |
| f | Reveal yang memakai `clip-path` diamati lewat elemen induknya | IntersectionObserver di Chrome menganggap elemen ber-`clip-path: inset(100% …)` tidak terlihat, sehingga foto tidak pernah muncul (ditemukan dan diperbaiki saat uji) |
| g | Garis kutipan dan bar komposisi hanya di template studi kasus (S2, S4), sesuai peta motion | Kutipan di homepage dan layanan tidak diberi motion |

### 18.3 Hasil uji lokal (`docs/qa/fase-4/`)

| | Desktop 1366 | HP 390 |
|---|---|---|
| Request homepage tanpa interaksi | 22 | 17 |
| Byte motion sebelum `load` | 0 | 0 |
| Sequence/video tanpa interaksi | 0 | 0 |
| Frame hero setelah interaksi | 61 (1,17 MB) | 60 (0,73 MB) |
| Sebelum hero terlewati (poster + frame) | 1,18 MB | 0,76 MB |
| Total motion homepage, discroll sampai habis | **3,68 MB** (batas 4 MB) | **2,01 MB** |
| Reduced motion / Hemat Data: sequence atau video diunduh | 0 (hanya poster, 0,09–0,11 MB) | 0 |
| CLS (homepage, layanan, studi kasus) | ≤ 0,0003 | 0 |
| Error JS | 0 | 0 |

- LCP dengan throttling mirip Lighthouse mobile (RTT 150 ms, 1,6 Mbps, CPU 4×, server lokal): homepage 1,09 dtk (poster hero), layanan 0,98 dtk, studi kasus 1,07 dtk (H1, +0,14 dtk karena muncul per baris). Angka PageSpeed sebenarnya diukur di staging (Fase 5).
- Langkah aktif Cara kerja sinkron dengan stasiun di visual (langkah 1–5) di desktop dan HP, juga di template layanan.
- Tanpa JavaScript: H1, angka, CTA, dan poster tampil.
- PHPCS bersih, `php -l` bersih.

### 18.4 Dicek di staging

- PageSpeed mobile homepage: LCP < 2,5 dtk, CLS < 0,1, INP < 200 ms, request < 30.
- Scrub hero dan Cara kerja di iPhone Safari dan Android Chrome (tidak bisa diuji di container ini).
- M4 di iPhone: Safari bisa mengabaikan `preload="auto"`. Bila `loadeddata` tidak datang, poster (frame akhir) tetap tampil tanpa animasi; itu perilaku yang disengaja, tetapi perlu dilihat.
- Frame M3 berlatar `#FEFEFE` (palet 256 warna), halaman `#FFFFFF`. Selisih 1 level; di layar biasa tidak terlihat. Bila terlihat di staging, render ulang di Motion Lab (aset tidak diubah di sini).
- Jumlah langkah di template layanan harus 5 supaya cocok dengan lima stasiun M3.
