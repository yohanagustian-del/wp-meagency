# Laporan HTML (tema mea-orbit 0.8.0+)

Cara membuat laporan klien (weekly, monthly, final report) **tanpa Elementor**.

## Membuat laporan baru

1. wp-admin → Halaman → **Tambah Baru**. Isi judul (mis. "Sevenbee 26 Okt – 1 Nov Weekly Report").
2. Kotak **Atribut Halaman** → **Template** → pilih **Laporan HTML**.
3. Kotak **Publikasikan** → Visibilitas → **Dilindungi Password** → isi password tim.
4. Klik **Simpan Draf**. Kotak **Kode HTML laporan** muncul di bawah editor.
5. Tempel seluruh kode HTML laporan ke kotak itu (dari `<!DOCTYPE html>` sampai `</html>`).
6. Klik **Terbitkan**, lalu buka halamannya dan masukkan password untuk cek.

Hasilnya: laporan tampil satu halaman penuh persis seperti file HTML-nya, tidak diindeks Google,
dan tidak masuk sitemap.

Catatan:
- Yang boleh menyimpan laporan berisi script/grafik: akun **Administrator** atau **Editor**.
  Akun Author/Contributor akan kehilangan `<script>` dan `<style>` saat menyimpan.
- Menyalin laporan minggu lalu: **Duplicate** (Yoast Duplicate Post) lalu ganti isi kotak.
- Jangan menempel HTML di tab **Visual** editor; kotak "Kode HTML laporan" lebih aman.

## Laporan lama (dibuat dengan Elementor)

Tidak perlu diapa-apakan. Laporan lama yang isinya satu widget HTML dan berpassword
(atau private / noindex) otomatis tampil lewat tema. Laporan lama yang punya widget lain
(teks, gambar tambahan) tetap tampil lewat Elementor seperti sebelumnya.

Laporan lama masih bisa dibuka di **Edit dengan Elementor** selama Elementor terpasang.
