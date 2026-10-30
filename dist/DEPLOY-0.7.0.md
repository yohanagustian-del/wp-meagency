# DEPLOY 0.7.0 · meagency.co.id

Tema saja (mea-leads tetap 0.2.0). Berisi semua perubahan 0.3.0 dan 0.4.0, ditambah section
**Galeri** untuk studi kasus. Versi 0.7.0: blok portofolio tim kreatif di section Konten homepage (0.6.0: link Studi kasus di header, foto proses tim untuk studi kasus, kutipan formal). Langsung pasang 0.7.0.

---

## A. Pasang tema (±10 menit)

1. wp-admin → Tampilan → Tema. Catat versi MEA Orbit yang aktif (saat ini 0.5.2) untuk rollback;
   zip-nya ada di repo: `dist/mea-orbit-0.5.2.zip`.
2. Tampilan → Tema → **Tambah Baru** → **Unggah Tema** → pilih `mea-orbit-0.7.0.zip` → **Install Now**.
3. WordPress menanyakan tema yang sudah ada → klik **Ganti yang terpasang dengan yang diunggah**.
4. hPanel → Websites → meagency.co.id → **Cache Manager** → **Purge All**. Bila CDN aktif:
   hPanel → **CDN** → **Purge cache**.
5. wp-admin → Rank Math → Sitemap Settings → klik **Save Changes** tanpa mengubah apa pun
   (supaya sitemap dibangun ulang).
6. Galeri Bellarosa sudah terisi (12 gambar + 1 video); buka /bellarosa/ dan cek.

Rollback: unggah `mea-orbit-0.5.2.zip` dengan **Ganti yang terpasang**, lalu Purge All.

---

## B. Tugas 5: ganti link internal lewat SSH (±15 menit)

### B1. Aktifkan SSH di Hostinger
1. hPanel → **Websites** → meagency.co.id → **Manage**.
2. Menu kiri **Advanced** → **SSH Access** → **Enable** (bila belum).
3. Catat dari halaman itu: **IP**, **Port** (biasanya `65002`), **Username** (mis. `u123456789`).
4. Bila belum punya password SSH: klik **Change** di bagian password, buat password baru.

### B2. Unggah script
1. hPanel → **File Manager** → buka folder `domains/meagency.co.id/public_html`
   (folder yang berisi `wp-config.php`).
2. Klik **Upload** → pilih `meagency-fix-internal-links.sh`.

### B3. Masuk ke server
- **Windows**: tekan Win, ketik `PowerShell`, buka. **Mac**: buka aplikasi **Terminal**.
- Ketik (ganti port, username, IP dengan data B1), tekan Enter, ketik `yes` bila ditanya,
  lalu masukkan password SSH (tidak terlihat saat diketik, itu normal):
  ```
  ssh -p 65002 u123456789@IP-SERVER
  ```

### B4. Masuk ke folder WordPress dan cek WP-CLI
```
cd ~/domains/meagency.co.id/public_html
wp core version
```
Harus muncul nomor versi WordPress (mis. `6.8.x`). Bila `No such file or directory`, jalankan
`ls ~/domains` untuk melihat nama folder yang benar.

### B5. Backup database
```
wp db export ~/backup-sebelum-link-fix-$(date +%Y%m%d-%H%M).sql
```
Harus muncul `Success: Exported to ...`.

### B6. Dry run (tidak mengubah apa pun)
```
bash meagency-fix-internal-links.sh | tee ~/link-fix-dry-run.txt
```
Hitung total sel yang akan berubah:
```
grep -Eo '[0-9]+ replacements? to be made' ~/link-fix-dry-run.txt | awk '{s+=$1} END {print s+0}'
```
- **≤ 500**: lanjut B7.
- **> 500**: berhenti, kirim angka itu + file `~/link-fix-dry-run.txt` ke Claude dulu
  (unduh lewat File Manager, folder home).

### B7. Eksekusi
```
bash meagency-fix-internal-links.sh --apply | tee ~/link-fix-apply.txt
```
Script otomatis menjalankan `wp cache flush`. Setelah selesai, Purge All lagi di hPanel.
Ketik `exit` untuk keluar dari SSH.

---

## C. Verifikasi dengan curl (±3 menit)

Paling mudah dijalankan di sesi SSH yang sama (langkah B3), karena server Linux sudah punya
`curl` dan `grep`. Jalankan satu per satu, salin hasilnya ke Claude:

```
curl -s https://www.meagency.co.id/sevenbee-19-25-oktober-weekly-report/ | grep -i 'name="robots"'
```
Target: berisi `noindex, nofollow`.

```
curl -s https://www.meagency.co.id/page-sitemap.xml | grep -c 'weekly-report'
```
Target: `0` (semua laporan sekarang berpassword).

```
curl -s https://www.meagency.co.id/jasa-iklan-shopee/ | grep -i 'name="robots"'
```
Target: berisi `index` (bukan `noindex`).

```
curl -s https://www.meagency.co.id/jasa-iklan-shopee/ | grep -c 'AggregateRating'
```
Target: `0`.

**Tanpa SSH (Windows PowerShell)**: pakai `curl.exe` dan `findstr`, contoh:
```
curl.exe -s https://www.meagency.co.id/jasa-iklan-shopee/ | findstr /i "robots"
```

**Tanpa terminal sama sekali**: buka halaman di Chrome → klik kanan → **View page source**
(Ctrl+U) → Ctrl+F `robots` (atau `AggregateRating`) dan lihat isinya.

---

## D. Cek tampilan setelah deploy
- Homepage: foto di kartu Enam sisi, "Juga tersedia: Social Media Management · SEO",
  Cara kami bekerja berganti foto saat di-scroll.
- Header: 4 pilar, dropdown saat hover dan saat Tab. HP: Menu → anak terindentasi.
- Footer: kolom Layanan (4 pilar) dan Perusahaan.
- /bellarosa/: panel Hasil 4 angka (termasuk 1.200% traffic organik TikTok); foto di samping cerita berganti 4 kali saat scroll (desktop); Galeri 12 gambar + video SC18.
