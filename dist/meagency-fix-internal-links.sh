#!/usr/bin/env bash
# =====================================================================
# meagency.co.id — Bersihkan link internal yang menunjuk ke URL lama
# (sudah di-301 lewat Rank Math, Okt 2026).
#
# Cara pakai (dari root WordPress di server, butuh WP-CLI):
#   1. Backup DB dulu:            wp db export backup-sebelum-link-fix.sql
#   2. Dry run (tidak menulis):   bash meagency-fix-internal-links.sh
#   3. Eksekusi sungguhan:        bash meagency-fix-internal-links.sh --apply
#
# Cakupan: hanya tabel posts + postmeta, kolom post_content & meta_value
# (termasuk data Elementor). Tabel redirect Rank Math TIDAK disentuh.
# Setiap URL diganti dalam dua bentuk: biasa (/slug/) dan JSON-escaped
# (\/slug\/) karena Elementor menyimpan link dengan slash di-escape.
# =====================================================================
set -euo pipefail

MODE="--dry-run"
if [[ "${1:-}" == "--apply" ]]; then MODE=""; fi

PREFIX=$(wp db prefix)
TABLES="${PREFIX}posts ${PREFIX}postmeta"
COLS="post_content,meta_value"

# Format: slug_lama|slug_baru
PAIRS=(
  "contoh-balasan-chat-otomatis-shopee|contoh-kata-kata-auto-reply-chat-shopee"
  "23967-2|iklan-ecommerce-tidak-cukup"
  "jasa-meta-ads-cpas|jasa-meta-ads"
  # -> /jasa-iklan-shopee/
  "mengapa-anda-harus-memilih-jasa-iklan-shopee-untuk-meningkatkan-visibilitas-produk-anda|jasa-iklan-shopee"
  "8-cara-memilih-jasa-iklan-shopee-yang-tepat-untuk-mencapai-tujuan-pemasaran-anda|jasa-iklan-shopee"
  "rahasia-sukses-membangun-brand-awareness-dan-meningkatkan-penjualan-melalui-iklan-shopee|jasa-iklan-shopee"
  # -> /jasa-optimasi-shopee/
  "10-manfaat-utama-menggunakan-jasa-optimasi-marketplace|jasa-optimasi-shopee"
  "rahasia-tingkatkan-omzet-dengan-jasa-optimasi-marketplace|jasa-optimasi-shopee"
  "jasa-optimasi-shopee-9-fitur-unggulan-wajib-anda-tahu|jasa-optimasi-shopee"
  "mengapa-butuh-jasa-optimasi-shopee-ini-10-alasannya|jasa-optimasi-shopee"
  "tingkatkan-penjualan-online-dengan-jasa-optimasi-shopee|jasa-optimasi-shopee"
  "kunci-raih-kesuksesan-bisnis-dengan-jasa-optimasi-shopee|jasa-optimasi-shopee"
  "jasa-seo-shopee|jasa-optimasi-shopee"
  "jasa-shopee-star-seller|jasa-optimasi-shopee"
  # -> /jasa-management-marketplace/
  "mengapa-anda-harus-memilih-jasa-pembuatan-toko-online-di-shopee-untuk-bisnis-anda|jasa-management-marketplace"
  "memahami-fitur-fitur-khusus-dan-keuntungan-dalam-membuat-toko-online-di-shopee-2|jasa-management-marketplace"
  "memahami-fitur-fitur-khusus-dan-keuntungan-dalam-membuat-toko-online-di-shopee|jasa-management-marketplace"
  "3-cara-menggunakan-alat-dan-analitik-shopee-untuk-mengoptimalkan-performa-toko-online-anda|jasa-management-marketplace"
  "10-langkah-langkah-praktis-dalam-membuat-toko-online-sukses-di-shopee|jasa-management-marketplace"
  "rahasia-sukses-membangun-kehadiran-online-yang-menguntungkan-dengan-toko-online-shopee|jasa-management-marketplace"
  # -> /jasa-upgrade-shopee-mall/
  "10-langkah-langkah-praktis-untuk-mengoptimalkan-penjualan-dengan-shopee-mall|jasa-upgrade-shopee-mall"
  "strategi-terbaik-meningkatkan-visibilitas-produk-anda-melalui-shopee-mall|jasa-upgrade-shopee-mall"
  # -> /jasa-live-streaming/
  "13-tips-sukses-menggunakan-jasa-live-shopee-untuk-bisnis|jasa-live-streaming"
  "manfaatkan-jasa-live-shopee-untuk-keuntungan-bisnis|jasa-live-streaming"
  "10-strategi-efektif-promosi-produk-via-jasa-live-shopee|jasa-live-streaming"
  # -> /jasa-live-streaming-tiktok/
  "tingkatkan-engagement-dengan-jasa-live-tiktok-profesional|jasa-live-streaming-tiktok"
  "jasa-live-tiktok-terbaik-untuk-dongkrak-engagement|jasa-live-streaming-tiktok"
  "jasa-host-live-streaming-tiktok|jasa-live-streaming-tiktok"
  "10-manfaat-jangka-panjang-penggunaan-jasa-live-tiktok|jasa-live-streaming-tiktok"
  # -> /jasa-kelola-tiktok-shop/, /affiliate/, /jasa-tiktok-ads/, /jasa-konten-tiktok/
  "banjir-orderan-di-tiktok-shop-dengan-bantuan-jasa-kelola-akun-tiktok-shop|jasa-kelola-tiktok-shop"
  "jasa-affiliate-tiktok-shop|affiliate"
  "jasa-iklan-tiktok-ads|jasa-tiktok-ads"
  "jasa-iklan-tiktok|jasa-tiktok-ads"
  "jasa-fyp-video-tiktok-yang-profesional-bisa-bikin-brand-kamu-viral|jasa-konten-tiktok"
  # -> /social-media-management/
  "5-keuntungan-utama-menggunakan-jasa-kelola-media-sosial|social-media-management"
  "jasa-kelola-sosial-media-maksimalkan-branding-bisnis|social-media-management"
  "maksimalkan-reach-dengan-jasa-kelola-sosial-media|social-media-management"
  "jasa-kelola-sosmed|social-media-management"
  # -> /seo/
  "jasa-seo-website|seo"
  # -> /jasa-meta-ads/
  "panduan-lengkap-memaksimalkan-potensi-facebook-ads-untuk-bisnis-anda|jasa-meta-ads"
  "4-memahami-algoritma-terbaru-facebook-ads-langkah-langkah-praktis|jasa-meta-ads"
  "strategi-terbaik-dalam-mengoptimalkan-penggunaan-facebook-ads|jasa-meta-ads"
  "jasa-ads-instagram|jasa-meta-ads"
)

echo "Mode: ${MODE:-APPLY}  |  Tabel: $TABLES"
for pair in "${PAIRS[@]}"; do
  OLD="${pair%%|*}"
  NEW="${pair##*|}"
  echo "== /$OLD/  ->  /$NEW/"
  # Bentuk biasa
  wp search-replace "/$OLD/" "/$NEW/" $TABLES --include-columns=$COLS --precise --report-changed-only $MODE
  # Bentuk JSON-escaped (data Elementor)
  wp search-replace "\\/$OLD\\/" "\\/$NEW\\/" $TABLES --include-columns=$COLS --precise --report-changed-only $MODE
done

if [[ -z "$MODE" ]]; then
  wp cache flush
  wp elementor flush_css 2>/dev/null || true
  echo "Selesai. Cache dibersihkan."
fi
