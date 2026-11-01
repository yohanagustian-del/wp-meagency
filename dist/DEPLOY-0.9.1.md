# DEPLOY 0.9.1 · meagency.co.id

Tema saja. Isi: CTA lama di field isi → `#konsultasi`, fungsi Code Snippet #14 pindah ke tema
(lihat `CHANGELOG.md`). Data audit 1 Nov (ikon, gambar 35906, FAQ 15567) **sudah diterapkan**
di live dan tidak bergantung pada versi tema ini.

## 1. Pasang tema

- **wp-admin / konektor**: Tampilan → Tema → MEA Orbit → **Update now** (muncul setelah PR rilis
  ini di-merge; Dasbor → Pembaruan → **Periksa lagi**).
- **Unggah zip**: `mea-orbit-0.9.1.zip` → **Ganti yang terpasang dengan yang diunggah**.

Snippet #14 boleh tetap aktif selama langkah ini: tema dan snippet menulis ulang link yang sama,
hasilnya tidak dobel (`#konsultasi` tidak cocok lagi dengan pola lama).

## 2. Cek dengan snippet #14 masih aktif

- `/jasa-management-marketplace/`: tombol "Konsultasi Gratis Sekarang" / "Hubungi Sekarang"
  → `href="#konsultasi"`, tanpa `target`. Klik → scroll ke form di halaman yang sama.
- Sama di `/jasa-tiktok-ads/`, `/jasa-upgrade-shopee-mall/`, `/jasa-iklan-marketplace/`,
  `/jasa-live-streaming/`, `/jasa-live-streaming-tiktok/`, `/jasa-optimasi-shopee/`.
- Lihat sumber halaman: tidak ada lagi `/klik/` di HTML isi halaman-halaman itu.

## 3. Matikan Code Snippet #14

Snippets → "MEA CTA lama -> form konsultasi Orbit" → **Deactivate** (atau konektor:
`PUT /code-snippets/v1/snippets/14` dengan `active: false`). Jangan dihapus dulu.

## 4. Cek setelah snippet mati (fungsi kini dari tema)

- `curl -sI https://www.meagency.co.id/klik/jasa-tiktok-ads` → `302`, `Location: …/#konsultasi`.
- Artikel dengan link lama, mis. `/cara-optimasi-shopee/` (sebari.co.id/form) dan
  `/syarat-menjadi-shopee-mall/` (/klik/): tombol CTA → `https://www.meagency.co.id/#konsultasi`,
  tanpa `target`.
- Halaman layanan dari langkah 2: tetap `#konsultasi`.

Rollback: aktifkan lagi snippet #14 (perilaku lama kembali seketika), lalu bila perlu unggah
`mea-orbit-0.9.0.zip`.
