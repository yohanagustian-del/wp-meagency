# Prompt Claude Code — strip "Partner resmi", nama PT, dan catatan keputusan

Jalankan di repo **wp-meagency**. Sebelum menempel, timpa `docs/mea-orbit/` dengan isi **mea-orbit-prototype.zip** terbaru (brief bagian 10 baru), dan taruh dua file dari folder `partner-strip/` ke `motion-src/partner/`.

```text
## Objective
Tambahkan strip "Partner resmi" di bawah hero homepage yang bisa diperbarui tiap kuartal tanpa render ulang animasi, ganti nama badan hukum di seluruh tema dan plugin menjadi "PT MEA Digital Marketing", dan catat keputusan terbaru di dokumentasi repo.

## Context
- Brief bagian 10 (baru, 27 Okt 2026) berisi keputusan yang wajib diikuti. Baca seluruhnya.
- Aset: motion-src/partner/shopee-certified-enablers-q3-2026.png (badge resmi Shopee, latar transparan, 1667×1417) dan motion-src/partner/tiktok-shop-platinum-partner-q3-2026.jpg (foto plakat penghargaan TikTok Shop, 894×1400, potret).
- Keduanya berlaku per kuartal; file akan diganti tiap kuartal oleh tim lewat Media Library.

## Target State
1. Section "Partner resmi" tepat di bawah hero homepage, sebelum section Tim. Latar terang, tenang, sesuai brand (Instrument Serif untuk judul kecil, Inter untuk label).
   - Isi: daftar item dari opsi Homepage baru `partner_resmi` (repeater): media_id gambar, alt, label (mis. "Shopee-Certified Enablers Program"), periode (mis. "Q3 2026"), link opsional.
   - Tampilan: gambar ditampilkan utuh tanpa dipotong dan tanpa filter (badge apa adanya, foto plakat dalam kartu berbingkai tipis dengan rasio aslinya), label dan periode di bawahnya. Desktop berjajar, mobile bisa digeser horizontal.
   - Section otomatis disembunyikan bila daftar kosong. Gambar lazy-load dengan width/height, tanpa CLS.
   - Default seed: dua item dari motion-src/partner/ (upload ke Media Library saat seed di staging, alt deskriptif: "Badge Shopee-Certified Enablers Program Q3 2026 untuk MEA" dan "Plakat TikTok Shop Platinum Certified Partner Q3 2026 untuk PT MEA Digital Marketing").
2. Nama badan hukum: cari semua "MEA Agensi Digital" / "PT MEA Agensi Digital" di tema, plugin mea-leads, seed, docs, dan JSON-LD, lalu ganti menjadi "PT MEA Digital Marketing". Organization.legalName = "PT MEA Digital Marketing". Nama brand tampil ("MEA Agency") tidak berubah. Jangan mengubah teks yang memang bagian dari file gambar.
3. Dokumentasi keputusan: buat atau perbarui docs/DECISIONS.md di repo ini. Isi minimal: keputusan bagian 10 brief beserta tanggalnya, keputusan tema mandiri (lepas dari Elementor), tracking dari tema, struktur halaman induk-anak layanan, dan aturan "nol bukti palsu" untuk konten AI. Setiap perubahan tema berikutnya wajib menambah baris di file ini (tanggal, keputusan, alasan, file terdampak).
4. Naikkan versi tema (0.1.x → berikutnya), perbarui CHANGELOG, paketkan ke dist/.

## Constraints
- Only make changes directly requested. Do not add features, abstractions, or files beyond what was asked.
- Tanpa dependency baru. Jangan menyentuh situs live.
- Gambar badge dan plakat tidak boleh diedit, dipotong ulang, atau diberi efek.

## Acceptance Criteria
- [ ] Strip tampil benar di 1366 px dan 390 px, gambar utuh, tanpa CLS.
- [ ] Tidak ada lagi string "Agensi" di kode, seed, dan JSON-LD (kecuali di DECISIONS.md sebagai catatan perubahan).
- [ ] docs/DECISIONS.md memuat keputusan bagian 10.
- [ ] Budget homepage tetap lolos.

## Stop Conditions
Stop and ask before menyentuh situs live, menghapus file, atau menambah dependency.
```

## Pembaruan tiap kuartal (tanpa Claude Code)
1. Upload badge Shopee dan foto plakat TikTok Shop kuartal baru ke Media Library.
2. Buka **Tampilan → Homepage MEA → Partner resmi**, ganti gambar dan periodenya.
3. Purge cache.
