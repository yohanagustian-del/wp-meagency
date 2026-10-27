#!/bin/sh
# Bangun dist/mea-orbit.zip, dist/mea-leads.zip, dan dist/paket-produksi-mea-agency.zip.
# Folder root di dalam zip tema/plugin = nama tema/plugin, supaya bisa diunggah lewat wp-admin.
set -eu
cd "$(dirname "$0")/.."
mkdir -p dist
rm -f dist/mea-orbit.zip dist/mea-leads.zip dist/paket-produksi-mea-agency.zip
(cd wp-content/themes && zip -rqX ../../dist/mea-orbit.zip mea-orbit -x '*/.DS_Store')
(cd wp-content/plugins && zip -rqX ../../dist/mea-leads.zip mea-leads -x '*/.DS_Store')

# Paket produksi: zip tema + plugin, panduan, seed, kontrak, rencana Elementor, alat paritas,
# panduan migrasi tracking.
tmp=$(mktemp -d)
pkg="$tmp/paket-produksi-mea-agency"
mkdir -p "$pkg/seed" "$pkg/tools" "$pkg/tracking"
cp dist/mea-orbit.zip dist/mea-leads.zip dist/CARA-PASANG.md "$pkg/"
cp dist/PASANG-PRODUKSI.md "$pkg/README.md"
cp docs/seed/*.json docs/seed/README.md "$pkg/seed/"
cp docs/WEBSITE_LEAD_CONTRACT.md docs/ELEMENTOR-EXIT.md "$pkg/"
cp -r tools/seo-parity tools/tracking-audit "$pkg/tools/"
cp docs/tracking/MIGRASI.md docs/tracking/GTM-SETUP.md docs/tracking/PLAN.md "$pkg/tracking/"
(cd "$tmp" && zip -rqX "$OLDPWD/dist/paket-produksi-mea-agency.zip" paket-produksi-mea-agency)
rm -r "$tmp"
ls -l dist/*.zip
