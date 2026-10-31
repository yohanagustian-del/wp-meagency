# DEPLOY 0.9.0 · meagency.co.id

Tema saja. Isi: redirect Rank Math, footer, migrasi 17 halaman ke template Orbit, motion pilar
E-commerce dan Live, koreksi dua artikel (lihat `CHANGELOG.md`). Langkah 2–5 bisa dijalankan
Claude lewat konektor MEAgency; urutannya penting.

## 1. Pasang tema

- **wp-admin / konektor**: Tampilan → Tema → MEA Orbit → **Update now** (muncul setelah PR rilis
  ini di-merge; Dasbor → Pembaruan → **Periksa lagi**).
- **Unggah zip**: `mea-orbit-0.9.0.zip` → **Ganti yang terpasang dengan yang diunggah**.

Tanpa langkah 2–5, tampilan situs hanya berubah di footer.

## 2. Redirect

1. `GET /wp-json/mea-orbit/v1/redirects` → catat laporan (`baru` belum `cocok`, `lama` harus `cocok: true`).
2. `POST /wp-json/mea-orbit/v1/redirects` → 8 entri dibuat; `laporan.baru[*].cocok` semua `true`.
3. Rank Math → Redirections: entri baru terlihat, tipe 301.
4. Dari komputer tim: `sh tools/redirect/cek-redirect.sh` → 22 baris `ok` (301 + tidak di sitemap).
   Bersihkan cache sitemap Rank Math bila sumber lama masih tercantum.

## 3. Migrasi 17 halaman (`docs/seed/layanan/README.md`)

Per seed: `wp_update_post_meta` (pages) semua kunci `meta` + meta di `motion.json`, lalu
`wp_update_page` → `template`. Cek `wp_rm_get_head` tiap URL: title, description, robots,
canonical sama dengan sebelum. Rollback per halaman: template kembali ke `sebelum.template`.

## 4. Artikel (`docs/konten/2026-10-31-artikel.json`)

1. `POST /wp-json/mea-orbit/v1/ganti-teks` dengan payload 15889, `uji: true` → `ok: true`,
   tiap baris `post_content: 1`, `elementor_data: 1`. Ulangi dengan `uji: false`.
2. Sama untuk 15879 (3 baris).
3. `wp_update_post` 15879: `title` = `judul_baru`. Slug dan meta Rank Math tidak diubah.
4. Hapus cache Elementor (`DELETE /wp-json/elementor/v1/cache`) dan cache halaman.

## 5. Cek tampilan (±10 menit)

- `/ecommerce-service/`: hero, kurva Bellarosa + angka menghitung saat di-scroll, isi lama, FAQ.
- `/jasa-live-streaming/`: mockup HP memutar video live saat terlihat.
- `/services/`: hub 4 pilar + Layanan lain (Jasa Website). `/study-case/`: H1 lama, isi, form.
- Footer di halaman mana pun: Live Streaming TikTok, Jasa Website, About Us, Ekosistem MEA.
- Mode reduced motion (DevTools → Rendering): kurva dan HP hanya poster.

Rollback tema: unggah `mea-orbit-0.8.0.zip`. Halaman yang sudah dimigrasi harus dikembalikan
dulu ke template sebelumnya (field `isi` tidak dikenal tema 0.8.0).
