# seo-parity

Alat kerja untuk PLAN bagian 14: memastikan pergantian tema (langkah 1) dan konversi
Elementor (langkah 2, Fase 6) tidak mengubah sinyal SEO per URL. Tidak ikut zip tema
atau plugin.

- PHP 8.0+ CLI dengan ekstensi `curl`, `dom`, `mbstring` (bawaan PHP). Tanpa Composer.
- Hanya GET. Jeda antar request 500 ms (default) supaya tidak membebani server.

## Pemakaian

```sh
# 1. Snapshot "sebelum" dari produksi (URL dari sitemap Rank Math)
php tools/seo-parity/parity.php snapshot --base=https://www.meagency.co.id --out=snap/produksi

# 2. Snapshot "sesudah" dari staging, memakai daftar URL yang sama
php tools/seo-parity/parity.php snapshot --base=https://staging.meagency.co.id \
  --urls=snap/produksi/urls.txt --out=snap/staging

# 3. Bandingkan
php tools/seo-parity/parity.php compare --before=snap/produksi --after=snap/staging \
  --out=laporan/langkah-1 --abaikan-sinyal=lang,og_locale
```

Hasil: `laporan/.../ringkasan.md` (jumlah lolos/beda per sinyal + detail per URL) dan
`paritas.csv` (satu baris per URL, bisa dibuka di Excel/Sheets). Exit code `0` bila
semua lolos, `2` bila ada yang berbeda.

Simpan folder snapshot produksi **sebelum** tema diganti di produksi. Snapshot yang sama
dipakai lagi setelah tema aktif di produksi (PLAN 14.3 langkah 4).

### Opsi `snapshot`

| Opsi | Arti |
|---|---|
| `--base=URL` | Alamat situs, tanpa garis miring di akhir |
| `--out=folder` | Folder hasil (`index.json`, `urls.txt`, `html/`) |
| `--sitemap=/path` | Sitemap (default `/sitemap_index.xml` milik Rank Math). Sitemap index dibaca rekursif |
| `--urls=berkas` | Daftar URL atau path, satu per baris (baris diawali `#` diabaikan). Menggantikan sitemap |
| `--delay=ms` | Jeda antar request, default 500 |
| `--auth=user:pass` | Basic auth (staging yang dikunci) |
| `--header='Nama: nilai'` | Header tambahan, mis. cookie bypass cache |
| `--insecure` | Lewati verifikasi SSL (hanya untuk staging dengan sertifikat sementara) |

### Opsi `compare`

| Opsi | Arti |
|---|---|
| `--before=folder`, `--after=folder` | Dua hasil snapshot |
| `--out=folder` | Folder laporan, default `laporan` |
| `--ambang=0.98` | Kemiripan teks minimum (Jaccard shingle 3 kata) |
| `--abaikan=kelas1,kelas2` | Kelas CSS yang dibuang dari area isi sebelum dibandingkan |
| `--area=kelas1,kelas2` | Batasi teks, heading, link, dan gambar ke elemen pertama dengan kelas ini. Mis. `--area=elementor-widget-theme-post-content,prose` untuk membandingkan isi artikel saja (Fase 6) |
| `--abaikan-sinyal=a,b` | Sinyal yang memang sengaja berubah. Langkah 1: `lang,og_locale` (keputusan og:locale `id_ID` sitewide, PLAN 10 nomor 3) |

## Yang dibandingkan per URL

| Sinyal | Keterangan |
|---|---|
| `status`, `location` | Kode HTTP dan tujuan redirect (tanpa mengikuti redirect) |
| `title`, `description`, `robots`, `canonical` | Dari `<head>`, dipegang Rank Math |
| `og_locale`, `og_image`, `lang` | |
| `h1` | Semua H1, berurutan |
| `heading` | Urutan H2/H3 di area isi |
| `text` | Teks area isi; kemiripan di bawah ambang = beda |
| `links` | Himpunan link internal di area isi |
| `images` | `src` + `alt` gambar di area isi |
| `jsonld` | Himpunan `@type` JSON-LD (termasuk `@graph`) |

Area isi = `<body>` tanpa `header`, `footer`, `nav`, skrip, header/footer Theme Builder
Elementor, popup Elementor, skip link, dan teks khusus pembaca layar. Host dinormalkan,
jadi produksi dan staging bisa dibandingkan walau domainnya berbeda.

## Beda yang memang direncanakan

- Langkah 1: `lang` dan `og_locale` (`en_US` → `id_ID`). Halaman Orbit (homepage,
  `/jasa-performance-ads/`, `/bellarosa/`) berbeda di isi, H1, dan JSON-LD karena memang
  didesain ulang; periksa manual, jangan dihitung sebagai regresi.
- Selain itu target 0 URL berbeda (PLAN bagian 11, Fase 5).
