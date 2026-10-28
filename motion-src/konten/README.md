# Video mockup HP "Konten"

Ilustrasi format konten, **dibuat dengan AI** (Grok). Tidak menampilkan angka, dashboard,
testimoni, chat pembeli, atau merek nyata (aturan "nol bukti palsu", `docs/DECISIONS.md`).
Ganti dengan video asli klien yang sudah berizin begitu tersedia, lalu kosongkan keterangan
"Ilustrasi format konten, dibuat dengan AI" di Tampilan → Homepage MEA.

| Berkas | Sumber | Format | Ukuran |
|---|---|---|---|
| `fashion.mp4` / `.webm` / `.webp` | grok-klip-5A (fashion) | 480×854, 6 dtk, 24 fps | 342 KB / 353 KB / 28 KB |
| `skincare.mp4` / `.webm` / `.webp` | grok-klip-5B (skincare) | 480×854, 6 dtk, 24 fps | 297 KB / 280 KB / 20 KB |
| `live-shopping.mp4` / `.webm` / `.webp` | grok-klip-6 (live shopping) | 480×480, 10 dtk, 24 fps | 275 KB / 348 KB / 17 KB |

Encode (28 Okt 2026, izin pemilik): hanya trek video (`-map 0:v:0`), **tanpa audio**, tanpa
metadata, skala Lanczos ke lebar 480 px (layar HP di mockup maksimal 260 px CSS). H.264 High
CRF 27 (fashion CRF 29) `+faststart`; VP9 CRF 38 (fashion CRF 42). Poster = frame pertama, WebP
q80. Setiap video jauh di bawah batas 1,5 MB.

Klip sumber 5A dan 5B berdurasi 6 detik (di bawah anjuran 10–15 detik); klip 6 berbentuk
persegi dan ditampilkan utuh di tengah layar HP (tidak dipotong).

Unggah kesembilan berkas ke Media Library, lalu isi Tampilan → Homepage MEA → Mockup HP
"Konten": satu baris per video (MP4, WebM, poster, label, link kosong = `/jasa-konten/`).
