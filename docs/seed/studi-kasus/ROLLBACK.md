# Rollback migrasi studi kasus (29 Okt 2026)

Sebelum migrasi, 33 halaman studi kasus dirender Elementor:
`_elementor_edit_mode` = `builder` (semua), template:
- `""` (default): 16160 skynet, 16144 tumbleryuk, 15731 sparepart-motor
- `elementor_header_footer`: semua lainnya (17555, 17563, 18639, 18640, 18653, 18659, 18668, 18678,
  18683, 18700, 18705, 18710, 18715, 18723, 18728, 18733, 18738, 19926, 19936, 19937, 19951, 20014,
  20019, 20030, 20046, 20160, 20176, 20204, 20210, 20216)

`_elementor_data` tidak diubah. Untuk mengembalikan satu halaman: set template ke nilai di atas
dan `_elementor_edit_mode` ke `builder` (wp-admin → edit halaman → Template, atau REST
`POST /wp/v2/pages/<id>` dengan `{"template": "...", "meta": {"_elementor_edit_mode": "builder"}}`).
Meta `mea_*` boleh dibiarkan (tidak dipakai template Elementor).

19948 denuber: tidak dimigrasi (isi salinan Allora); hanya `rank_math_robots` → noindex.
