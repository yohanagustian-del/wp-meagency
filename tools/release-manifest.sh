#!/bin/sh
# Tulis dist/mea-orbit-update.json untuk update tema lewat WordPress (inc/update.php).
# Pakai: sh tools/release-manifest.sh <commit-sha>
# <commit-sha> = commit yang sudah di-push dan memuat dist/mea-orbit-<versi>.zip, supaya URL
# paket menunjuk berkas yang tidak berubah. Commit manifest ini sesudahnya.
set -eu
cd "$(dirname "$0")/.."
sha_commit="${1:?commit sha wajib}"
ver=$(sed -n 's/^Version: *//p' wp-content/themes/mea-orbit/style.css)
zip="dist/mea-orbit-$ver.zip"
git cat-file -e "$sha_commit:$zip"
git show "$sha_commit:$zip" > /tmp/mea-orbit-release.zip
sum=$(sha256sum /tmp/mea-orbit-release.zip | cut -d' ' -f1)
rm -f /tmp/mea-orbit-release.zip
cat > dist/mea-orbit-update.json <<JSON
{
  "version": "$ver",
  "package": "https://raw.githubusercontent.com/yohanagustian-del/wp-meagency/$sha_commit/$zip",
  "sha256": "$sum",
  "requires": "6.4",
  "requires_php": "8.0"
}
JSON
cat dist/mea-orbit-update.json
