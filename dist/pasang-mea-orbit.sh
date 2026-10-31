#!/usr/bin/env bash
# Pasang / perbarui tema MEA Orbit lewat SSH, tanpa WP-CLI.
#
# Kenapa perlu: Site Health melaporkan folder wp-content/themes (dan plugins)
# "not writable" untuk user PHP-FPM. Upload tema di wp-admin berhasil membongkar
# zip di wp-content/upgrade, tapi gagal menyalin ke themes → "some files could not
# be copied". Tema lama tetap utuh.
#
# Pakai (di server, sebagai user dengan sudo):
#   curl -fsSL -o /tmp/pasang-mea-orbit.sh https://raw.githubusercontent.com/yohanagustian-del/wp-meagency/HEAD/dist/pasang-mea-orbit.sh
#   sudo bash /tmp/pasang-mea-orbit.sh /home/mea/app/meagency.co.id            # cek saja, tidak mengubah apa pun
#   sudo bash /tmp/pasang-mea-orbit.sh /home/mea/app/meagency.co.id --pasang   # backup + pasang + betulkan izin themes
#   tambahkan --juga-plugins agar plugin juga bisa diupdate dari wp-admin.
set -euo pipefail

VERSI=0.7.1
ZIP_URL=https://raw.githubusercontent.com/yohanagustian-del/wp-meagency/3eebc11ad3fb/dist/mea-orbit-0.7.1.zip
ZIP_SHA256=67211bfddb53de5b75d3280b3afca58cda0b52574fc64dfc527416caa41b288d

SITE=${1:-}
shift || true
PASANG=0
PLUGINS=0
for arg in "$@"; do
	case $arg in
		--pasang) PASANG=1 ;;
		--juga-plugins) PLUGINS=1 ;;
		*) echo "Opsi tidak dikenal: $arg" >&2; exit 2 ;;
	esac
done

if [ -z "$SITE" ] || [ ! -f "$SITE/wp-config.php" ]; then
	echo "Folder WordPress tidak ditemukan: '${SITE}'. Contoh: sudo bash $0 /home/mea/app/meagency.co.id" >&2
	exit 2
fi
if [ "$(id -u)" -ne 0 ]; then
	echo "Jalankan dengan sudo." >&2
	exit 2
fi

WPC=$SITE/wp-content
TEMA=$WPC/themes/mea-orbit
# uploads dilaporkan writable oleh Site Health, jadi pemiliknya = user PHP-FPM.
PEMILIK=$(stat -c '%U:%G' "$WPC/uploads")

versi_tema() {
	sed -n 's/^Version:[[:space:]]*//p' "$1/style.css" 2>/dev/null | head -n1
}

echo "== Diagnosis =="
for p in "$WPC" "$WPC/themes" "$TEMA" "$WPC/plugins" "$WPC/upgrade" "$WPC/uploads"; do
	[ -e "$p" ] && stat -c '%U:%G %a  %n' "$p" || echo "(tidak ada)  $p"
done
echo "User PHP-FPM yang berjalan:"
ps -eo user=,args= | grep '[p]hp-fpm: pool' | awk '{print "  " $1 "  pool " $NF}' | sort | uniq -c || true
echo "Pemilik uploads (dipakai sebagai pemilik themes): $PEMILIK"
echo "Versi mea-orbit terpasang: $(versi_tema "$TEMA")"

if [ "$PASANG" -ne 1 ]; then
	echo
	echo "Mode cek saja. Ulangi dengan --pasang untuk memasang $VERSI."
	exit 0
fi

KERJA=$(mktemp -d)
trap 'rm -rf "$KERJA"' EXIT

echo
echo "== Backup tema lama =="
BACKUP=/root/backup-mea-orbit-$(versi_tema "$TEMA")-$(date +%Y%m%d-%H%M%S).tar.gz
tar -czf "$BACKUP" -C "$WPC/themes" mea-orbit
echo "Backup: $BACKUP"

echo
echo "== Unduh dan cek zip $VERSI =="
curl -fsSL -o "$KERJA/tema.zip" "$ZIP_URL"
echo "$ZIP_SHA256  $KERJA/tema.zip" | sha256sum -c -
mkdir "$KERJA/isi"
unzip -q "$KERJA/tema.zip" -d "$KERJA/isi"
[ -f "$KERJA/isi/mea-orbit/style.css" ] || { echo "Isi zip tidak berisi mea-orbit/style.css" >&2; exit 1; }

echo
echo "== Pasang =="
# Salin ke folder yang sama (tidak dipindah/dihapus dulu) supaya tema aktif tidak
# pernah hilang sesaat; WordPress akan pindah ke tema bawaan kalau folder tema aktif tidak ada.
if command -v rsync >/dev/null 2>&1; then
	rsync -a --delete "$KERJA/isi/mea-orbit/" "$TEMA/"
else
	cp -a "$KERJA/isi/mea-orbit/." "$TEMA/"
fi

betulkan_izin() {
	chown -R "$PEMILIK" "$1"
	find "$1" -type d -exec chmod 755 {} +
	find "$1" -type f -exec chmod 644 {} +
	echo "Izin dibetulkan: $1 → $PEMILIK, folder 755, file 644"
}
betulkan_izin "$WPC/themes"
[ -d "$WPC/upgrade" ] && betulkan_izin "$WPC/upgrade"
[ "$PLUGINS" -eq 1 ] && betulkan_izin "$WPC/plugins"

echo
echo "== Muat ulang PHP-FPM (bersihkan OPcache) =="
LAYANAN=$(systemctl list-units --type=service --no-legend 'php*-fpm.service' 2>/dev/null | awk '{print $1}' | head -n1 || true)
if [ -n "$LAYANAN" ]; then
	systemctl reload "$LAYANAN" && echo "Reload $LAYANAN"
else
	echo "Layanan php-fpm tidak terdeteksi; reload manual bila tampilan belum berubah."
fi

echo
echo "Selesai. Versi mea-orbit sekarang: $(versi_tema "$TEMA")"
