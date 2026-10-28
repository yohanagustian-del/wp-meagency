#!/bin/sh
# Bangun zip tema dan plugin berversi (dist/mea-orbit-<versi>.zip, dist/mea-leads-<versi>.zip),
# salinan tanpa versi (dist/mea-orbit.zip, dist/mea-leads.zip, dirujuk CARA-PASANG.md), dan
# dist/paket-produksi-mea-agency.zip.
# Folder root di dalam zip tema/plugin = nama tema/plugin, supaya bisa diunggah lewat wp-admin.
# motion-src/ (termasuk source/ render hero) tidak pernah ikut: tema hanya membawa assets/.
set -eu
cd "$(dirname "$0")/.."
theme_ver=$(sed -n 's/^Version: *//p' wp-content/themes/mea-orbit/style.css)
plugin_ver=$(sed -n 's/^ \* Version: *//p' wp-content/plugins/mea-leads/mea-leads.php)
theme_zip="dist/mea-orbit-$theme_ver.zip"
plugin_zip="dist/mea-leads-$plugin_ver.zip"
mkdir -p dist
rm -f "$theme_zip" "$plugin_zip" dist/mea-orbit.zip dist/mea-leads.zip dist/paket-produksi-mea-agency.zip
(cd wp-content/themes && zip -rqX "../../$theme_zip" mea-orbit -x '*/.DS_Store')
(cd wp-content/plugins && zip -rqX "../../$plugin_zip" mea-leads -x '*/.DS_Store')
cp "$theme_zip" dist/mea-orbit.zip
cp "$plugin_zip" dist/mea-leads.zip

# Paket produksi: zip tema + plugin, panduan deploy, catatan rilis, seed, kontrak, rencana
# Elementor, alat paritas, panduan migrasi tracking.
tmp=$(mktemp -d)
pkg="$tmp/paket-produksi-mea-agency"
mkdir -p "$pkg/seed" "$pkg/tools" "$pkg/tracking" "$pkg/partner" "$pkg/konten"
cp "$theme_zip" "$plugin_zip" dist/CARA-PASANG.md CHANGELOG.md docs/DECISIONS.md "$pkg/"
cp dist/PASANG-PRODUKSI.md "$pkg/README.md"
if [ -f "dist/DEPLOY-$theme_ver.md" ]; then cp "dist/DEPLOY-$theme_ver.md" "$pkg/"; fi
cp docs/seed/*.json docs/seed/README.md "$pkg/seed/"
cp docs/WEBSITE_LEAD_CONTRACT.md docs/ELEMENTOR-EXIT.md "$pkg/"
cp -r tools/seo-parity tools/tracking-audit "$pkg/tools/"
cp docs/tracking/MIGRASI.md docs/tracking/GTM-SETUP.md docs/tracking/PLAN.md "$pkg/tracking/"
cp motion-src/partner/*.webp "$pkg/partner/"
cp motion-src/konten/* "$pkg/konten/"
(cd "$tmp" && zip -rqX "$OLDPWD/dist/paket-produksi-mea-agency.zip" paket-produksi-mea-agency)
rm -r "$tmp"
ls -l dist/*.zip
