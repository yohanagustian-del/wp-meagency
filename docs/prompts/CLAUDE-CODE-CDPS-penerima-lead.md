# Prompt Claude Code — CDPS: lead website langsung jadi prospek

Menggantikan prompt "Fase 0–1 CDPS" sebelumnya. Jalankan dari **root repo CDPS**, dengan `mea-orbit-prototype.zip` diekstrak ke `docs/mea-orbit/`.

```text
## Objective
Setiap pengisian form di website MEA (mulai dari meagency.co.id) harus langsung menjadi lead di CDPS, siap diklaim tim Sales sebagai prospek, tanpa input atau persetujuan manual, dan selalu terlihat jelas sebagai LEAD WEBSITE.

## Context
- Brief: docs/mea-orbit/BRIEF-CLAUDE-CODE.md bagian 6, 6.2 (revisi 24 Okt 2026), 6.2a, dan 6.3. Baca seluruhnya.
- Supabase "CDPS SG", ref egddxfcnrtecheiykhlf. INI PRODUKSI. Tidak ada proyek CDPS staging.
- public.leads: id LEAD-YYYYMM-NNNN dibuat kode aplikasi lewat id_sequences; kolom lead_name, phone_number, phone_norm, email, source, origin_division, origin_campaign_id, last_touch_campaign_id, record_status, winning_attempt_id, manual_review_flag, created_at, created_by. Tanpa CHECK constraint dan tanpa unique phone_norm: validasi dan deteksi duplikat ada di packages/core.
- Mesin lead_record punya state [Pool] ([Pool] → active saat diklaim). Mesin prospect_attempt mulai dari New Lead (atau Pending Validation → New Lead). prospect_attempts: lead_id, owner_employee_id, status, claimed_at, created_by.
- Nilai source 'Website' sudah dipakai (92 lead, semuanya sudah punya prospect attempt, sebagian besar diinput manual). Jangan membuat nilai source baru.
- Pola yang wajib ditiru: inbox external_orders (payload immutable, status lewat sm_transition), complaint_rate_limit_attempts (RLS aktif, nol policy), registry entity_prefix yang sinkron dengan packages/core/src/ident.ts, katalog notifikasi notif_events + notif_catalog_versions (aturan O55).
- Belum ada Edge Function di proyek ini.
- Pengirimnya plugin WordPress mea-leads (repo lain): POST JSON schema mea.website_lead.v1, header X-MEA-Signature (HMAC-SHA256), X-MEA-Idempotency-Key, X-MEA-Timestamp.

## Target State
1. Migrasi baru (file saja): tabel website_lead_inbox (prefix baru, idempotency_key UNIQUE, site, kanal, payload jsonb immutable, received_at, status lewat sm_transition dengan mesin baru diterima → dibuat | duplikat | gagal, lead_id FK ke leads.id). RLS aktif, nol policy. Prefix terdaftar di entity_prefix dan ident.ts. Event notifikasi "Lead website baru — {site}, siap diklaim" di katalog sesuai O55.
2. Penerima di tempat yang bisa memakai packages/core apa adanya (route API web-internal, atau Edge Function bila core bisa diimpor; putuskan di Fase 0 dan jelaskan alasannya). Verifikasi HMAC, tolak timestamp lebih dari 5 menit, validasi payload v1, idempotensi (key sama → 200 dengan hasil pertama), rate limit per IP.
3. Setelah dicatat di inbox, penerima LANGSUNG memanggil jalur createLead yang sudah ada: source 'Website', origin_division 'Sales', record_status '[Pool]', created_by = aktor sistem sesuai konvensi repo. Duplikat phone_norm mengikuti aturan domain yang berlaku dan memberi manual_review_flag = true. inbox.lead_id terisi, notifikasi terkirim ke Sales.
4. Penanda lead website di web-internal: badge "Lead Website" di daftar Pool, daftar lead, dan detail lead, beserta site, sumber_form, halaman_asal, omzet, layanan, link toko, dan UTM. Filter "Hanya lead website (otomatis)". Klaim dari Pool tetap memakai alur yang sudah ada (active + prospect attempt New Lead).
5. Halaman pemantauan inbox (read-only) untuk admin: tanda terima yang gagal atau duplikat beserta alasannya.
6. docs/WEBSITE_LEAD_CONTRACT.md v1 (payload, header, kode respons, contoh).
7. Test: HMAC valid/invalid/kedaluwarsa, idempotensi, lead masuk [Pool] dengan source Website, duplikat ter-flag, registry dan katalog notifikasi tetap hijau.

## Scope
- Kerja hanya di: supabase/migrations (file baru), lokasi penerima yang dipilih di Fase 0, packages/core, packages/db, halaman lead/Pool di web-internal, docs/.
- JANGAN sentuh: migrasi lama, kolom tabel yang sudah ada (tidak ada ALTER leads atau prospect_attempts), mesin status yang sudah ada, modul lain, .env, konfigurasi deploy.

## Constraints
- Baca dulu: DECISIONS.md, STATE_MACHINES.md, docs/BRIDGE_MSDPS_CONTRACT.md, migrasi external_orders dan complaint_rate_limit_attempts, ident.ts, dan kode createLead serta klaim Pool.
- NEVER insert ke public.leads di luar jalur domain createLead. NEVER menulis ulang logika createLead.
- NEVER menaruh service-role key di klien atau di WordPress. WordPress hanya memegang HMAC secret.
- Only make changes directly requested. Do not add features, abstractions, or files beyond what was asked.
- Tanpa dependency baru tanpa izin.

## Acceptance Criteria
- [ ] Migrasi lolos di Supabase lokal (supabase start + db reset).
- [ ] Semua test lama dan baru hijau.
- [ ] POST contoh dengan HMAC benar → tanda terima di inbox DAN lead baru LEAD-YYYYMM-NNNN berstatus [Pool], source Website, badge "Lead Website" tampil, notifikasi tercatat.
- [ ] Payload sama dikirim ulang → tidak ada lead atau tanda terima kedua.
- [ ] HMAC salah atau timestamp kedaluwarsa → 401, tidak ada baris apa pun.
- [ ] Nomor yang sudah ada → manual_review_flag = true sesuai aturan domain.
- [ ] Sales mengklaim lead dari Pool → active + prospect attempt New Lead, sama seperti lead lain.

## Stop Conditions
Stop and ask before:
- Menerapkan migrasi ke Supabase remote mana pun (produksi egddxfcnrtecheiykhlf: NEVER tanpa izin tertulis).
- Deploy penerima atau mengatur secret di remote.
- Repo belum punya aktor sistem untuk created_by (usulkan opsi, jangan membuat karyawan palsu).
- Menghapus file, menambah dependency, atau mengubah tabel/mesin yang sudah ada.
- Menyentuh apa pun di luar Scope.

## Session Strategy
- Fase 0: baca semua file di Constraints, tulis docs/mea-orbit/PLAN-cdps-penerima.md (lokasi penerima dan alasannya, aktor created_by, nama prefix dan mesin status, daftar file, risiko). STOP sampai saya mengetik "lanjut".
- Fase 1: kerjakan Target State 1–7 berurutan, laporkan, lalu STOP.

## Progress
Setelah tiap langkah: ✅ [yang dikerjakan] — [file]
```

Setelah selesai: berikan URL penerima dan HMAC secret kepada orang yang mengisi `MEA_LEADS_ENDPOINT` dan `MEA_LEADS_SECRET` di `wp-config.php` staging. Secret dikirim lewat saluran privat, jangan lewat chat ini.
