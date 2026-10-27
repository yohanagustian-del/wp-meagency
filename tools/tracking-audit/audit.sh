#!/bin/sh
# Audit tracking meagency.co.id dari HTML publik (tanpa login, hanya GET).
# Hasil: tabel markdown jumlah kemunculan per halaman.
#
#   sh tools/tracking-audit/audit.sh > audit-tracking.md
#   BASE=https://www.meagency.co.id sh tools/tracking-audit/audit.sh   (default)
#
# Butuh: sh, curl, grep, sed. Jalankan dari komputer biasa (bukan saat login wp-admin;
# curl memang tidak membawa cookie login, jadi Site Kit tidak menyaring pengguna login).
set -eu

BASE="${BASE:-https://www.meagency.co.id}"
UA='Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0 Safari/537.36'

# path|keterangan
PAGES='/|Homepage
/jasa-performance-ads/|Layanan (Orbit)
/bellarosa/|Studi kasus (Orbit)
/blog/|Arsip blog
/alat-yang-dibutuhkan-untuk-jualan-di-shopee/|Artikel
/urung-bantal-weekly-report-19-25-okt/|Laporan berpassword
/dapur-roca-shop-shopee-weekly-report-19-25-okt/|Laporan TANPA password'

# label|pola (grep -E, tanpa membedakan huruf besar kecil)
CHECKS="fbq init|fbq\\(['\"]init
ID Meta 1133774585771727|1133774585771727
ID Meta lama 451423923748567|451423923748567
ttq.load|ttq\\.load\\(
ID TikTok DBAODNJC77U9NS8CML30|DBAODNJC77U9NS8CML30
ID TikTok lama C38O78JTAIRUEDS4KPO0|C38O78JTAIRUEDS4KPO0
ID TikTok lama CI4NCS3C77UFB57TQ9O0|CI4NCS3C77UFB57TQ9O0
gtm.js|gtm\\.js
GTM-5XWWJBW|GTM-5XWWJBW
gtag/js (Google tag)|googletagmanager\\.com/gtag/js
G-NDEDBPBJYL|G-NDEDBPBJYL
GT-M6B9W446|GT-M6B9W446
meta robots noindex|<meta[^>]+name=['\"]robots['\"][^>]+noindex"

tmp=$(mktemp -d)
trap 'rm -rf "$tmp"' EXIT

header='| Pola |'
sep='|---|'
i=0
echo "$PAGES" | while IFS='|' read -r path label; do
	i=$((i + 1))
	code=$(curl -s -L -A "$UA" -o "$tmp/$i.html" -w '%{http_code}' "$BASE$path" || echo 000)
	echo "$path|$label|$code" >> "$tmp/index"
done

printf '# Audit tracking %s\n\nDiambil %s. Angka = jumlah kemunculan di HTML halaman.\n\n' "$BASE" "$(date '+%Y-%m-%d %H:%M %Z')"
while IFS='|' read -r path label code; do
	header="$header $label |"
	sep="$sep---|"
done < "$tmp/index"
echo "$header"
echo "$sep"
row_status='| HTTP |'
while IFS='|' read -r path label code; do row_status="$row_status $code |"; done < "$tmp/index"
echo "$row_status"
echo "$CHECKS" | while IFS='|' read -r name pattern; do
	row="| $name |"
	n=0
	while IFS='|' read -r path label code; do
		n=$((n + 1))
		c=$(grep -o -i -E "$pattern" "$tmp/$n.html" 2>/dev/null | wc -l | tr -d ' ')
		row="$row $c |"
	done < "$tmp/index"
	echo "$row"
done

printf '\nHalaman yang diperiksa:\n\n'
while IFS='|' read -r path label code; do printf -- '- %s: `%s%s`\n' "$label" "$BASE" "$path"; done < "$tmp/index"
