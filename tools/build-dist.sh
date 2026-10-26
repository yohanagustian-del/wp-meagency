#!/bin/sh
# Bangun dist/mea-orbit.zip dan dist/mea-leads.zip dari wp-content/.
# Folder root di dalam zip = nama tema/plugin, supaya bisa diunggah lewat wp-admin.
set -eu
cd "$(dirname "$0")/.."
mkdir -p dist
rm -f dist/mea-orbit.zip dist/mea-leads.zip
(cd wp-content/themes && zip -rqX ../../dist/mea-orbit.zip mea-orbit -x '*/.DS_Store')
(cd wp-content/plugins && zip -rqX ../../dist/mea-leads.zip mea-leads -x '*/.DS_Store')
ls -l dist/*.zip
