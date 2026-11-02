# DEPLOY 0.9.3 · meagency.co.id

Tema saja. Isi: link footer "Gabung MCN MEA" dan redirect `/a-affiliate-mcn-mea-vk/` langsung ke
`https://meamcn.com/kreator/` (lihat `CHANGELOG.md`).

## 1. Rank Math (data live, tidak bergantung versi tema)

- #154 `a-affiliate-mcn-mea-vk/` → tujuan `https://meamcn.com/kreator/` (301).
- #183 (duplikat, → `/gabung-mcn-mea/`) → **inactive**. Jangan dihapus.

## 2. Pasang tema

- **wp-admin / konektor**: Tampilan → Tema → MEA Orbit → **Update now** (muncul setelah PR rilis
  di-merge; Dasbor → Pembaruan → **Periksa lagi**).
- **Unggah zip**: `mea-orbit-0.9.3.zip` → **Ganti yang terpasang dengan yang diunggah**.

## 3. Cek

- Footer halaman mana pun: "Gabung MCN MEA" → `href="https://meamcn.com/kreator/"`.
- `curl -sI https://www.meagency.co.id/a-affiliate-mcn-mea-vk/` → `301`,
  `Location: https://meamcn.com/kreator/` (satu hop).
- `GET /wp-json/mea-orbit/v1/redirects`: baris `/a-affiliate-mcn-mea-vk/` `cocok: true`.

Rollback: unggah `mea-orbit-0.9.2.zip`; Rank Math: #154 tujuan kembali `https://meamcn.com/`,
#183 aktif lagi.
