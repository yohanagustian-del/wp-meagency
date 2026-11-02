#!/bin/sh
# Cek redirect 301 meagency.co.id dengan curl -I dan pastikan URL sumber tidak ada di sitemap.
# Pakai: sh tools/redirect/cek-redirect.sh [https://www.meagency.co.id]
# Jalankan dari komputer tim (container sesi Claude Code tidak bisa menjangkau domain ini).
# Keluar 1 bila ada sumber yang tidak 301 ke tujuannya, atau masih tercantum di sitemap.
set -u
base="${1:-https://www.meagency.co.id}"
gagal=0

# sumber|tujuan (tujuan kosong = cukup 301; path = di situs ini, http(s)://… = URL absolut).
# Sama dengan inc/redirects.php.
daftar='
/mea-jasa-ads-tik-tok/|/jasa-tiktok-ads/
/jasa-traffic-iklan-dan-affiliate-mea/|/affiliate/
/a-affiliate-mcn-mea-vk/|https://meamcn.com/kreator/
/content-service/|/jasa-konten/
/jasa-optimasi-ads/|/jasa-performance-ads/
/jasa-marketing-e-commerce/|/ecommerce-service/
/home-page/|/
/homepage-mea/|/
/en/|/
/about-us-2-0/|/about-us/
/jasa-live-shopee/|
/marketplace-eccomerce-management/|
/jasa-meta-ads-cpas/|
/fotografi-dan-videografi-produk/|
/a-program-matchmaking-top-creator-shopee/|
/search-engine-marketing-management/|
/jasa-webinar/|
/email-marketing-management/|
/jasa-iklan-tiktok-ads/|
/jasa-affiliate-tiktok-shop/|
/jasa-kelola-sosial-media-maksimalkan-branding-bisnis/|
/mengapa-anda-harus-memilih-jasa-iklan-shopee-untuk-meningkatkan-visibilitas-produk-anda/|
'

# Semua <loc> dari sitemap index Rank Math dan sub-sitemapnya.
tmp=$(mktemp)
curl -fsS "$base/sitemap_index.xml" | grep -o '<loc>[^<]*</loc>' | sed 's#</*loc>##g' | while read -r sm; do
	curl -fsS "$sm" | grep -o '<loc>[^<]*</loc>' | sed 's#</*loc>##g'
done > "$tmp"
echo "Sitemap: $(wc -l < "$tmp") URL"

echo "$daftar" | while IFS='|' read -r src dst; do
	[ -z "$src" ] && continue
	head=$(curl -sS -I -o - "$base$src" | tr -d '\r')
	kode=$(printf '%s\n' "$head" | sed -n '1s/^HTTP[^ ]* \([0-9]*\).*/\1/p')
	lok=$(printf '%s\n' "$head" | sed -n 's/^[Ll]ocation: *//p' | head -1)
	status=ok
	[ "$kode" = 301 ] || status=GAGAL
	case "$dst" in http://* | https://*) url="$dst" ;; *) url="$base$dst" ;; esac
	if [ -n "$dst" ] && [ "${lok%/}" != "${url%/}" ] && [ "$lok" != "$url" ]; then status=GAGAL; fi
	if grep -qx "$base$src" "$tmp"; then status="GAGAL (di sitemap)"; fi
	printf '%-6s %s %s -> %s\n' "$status" "$kode" "$src" "${lok:-?}"
done | tee /dev/stderr | grep -q '^GAGAL' && gagal=1
rm -f "$tmp"
exit "$gagal"
