<?php
/**
 * Test galeri studi kasus mode bulanan (tema mea-orbit): label tab, field galeri_mode, dan
 * HTML template-parts/studi-kasus/galeri.php. Jalankan: php tests/galeri-bulanan.php
 *
 * @package MEA_Orbit
 */

define( 'ABSPATH', __DIR__ . '/' );
function add_action() {}
function add_filter() {}
function __( $text ) {
	return $text;
}
function esc_html( $text ) {
	return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' );
}
function esc_attr( $text ) {
	return esc_html( $text );
}
function esc_url( $url ) {
	return esc_html( $url );
}
function esc_html_e( $text ) {
	echo esc_html( $text );
}
function esc_attr_e( $text ) {
	echo esc_attr( $text );
}
function sanitize_key( $key ) {
	return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( (string) $key ) );
}
function wp_parse_args( $args, $defaults ) {
	return array_merge( $defaults, $args );
}
function wp_attachment_is_image( $id ) {
	return $id < 900;
}
function get_post_mime_type( $id ) {
	return $id < 900 ? 'image/webp' : 'video/mp4';
}
function wp_get_attachment_url( $id ) {
	return "https://contoh.id/$id.mp4";
}
function get_the_title( $id ) {
	return "Judul $id";
}
function wp_get_attachment_image( $id, $size, $icon, $attr ) {
	$GLOBALS['img_calls'][] = array( $id, $size, $attr );
	$attr = array_merge( array( 'alt' => "alt-media-$id" ), $attr );
	$html = '<img src="https://contoh.id/' . $id . '.webp" width="1600" height="900"';
	foreach ( $attr as $k => $v ) {
		$html .= ' ' . $k . '="' . esc_attr( $v ) . '"';
	}
	return $html . '>';
}
$GLOBALS['meta'] = array();
function get_queried_object_id() {
	return 1;
}
function get_post_meta( $id, $key ) {
	$key = substr( $key, 4 );
	$default = array(
		'galeri_mode' => 'grid',
		'galeri'      => array(),
	);
	return $GLOBALS['meta'][ $key ] ?? ( $default[ $key ] ?? '' );
}
require dirname( __DIR__ ) . '/wp-content/themes/mea-orbit/inc/orbit.php';
require dirname( __DIR__ ) . '/wp-content/themes/mea-orbit/inc/fields.php';

$failed = 0;
$total  = 0;
function t( $name, $expected, $actual ) {
	global $failed, $total;
	++$total;
	if ( $expected === $actual ) {
		echo "  ok   $name\n";
		return;
	}
	++$failed;
	echo "  GAGAL $name\n    harap: " . var_export( $expected, true ) . "\n    dapat: " . var_export( $actual, true ) . "\n";
}
function render() {
	$GLOBALS['img_calls'] = array();
	ob_start();
	include dirname( __DIR__ ) . '/wp-content/themes/mea-orbit/template-parts/studi-kasus/galeri.php';
	return ob_get_clean();
}

echo "Label tab\n";
t( 'em dash', 'Februari 2026', mea_orbit_galeri_label( 'Februari 2026 — panel Kriteria Utama dan Iklan', 1 ) );
t( 'titik tengah', 'Maret 2026', mea_orbit_galeri_label( 'Maret 2026 · panel Iklan', 2 ) );
t( 'pemisah pertama yang dipakai', 'April 2026', mea_orbit_galeri_label( 'April 2026 · Iklan — catatan', 3 ) );
t( 'tanpa pemisah', 'Mei 2026', mea_orbit_galeri_label( ' Mei 2026 ', 4 ) );
t( 'tanda hubung biasa bukan pemisah', 'Juni 2026 - panel', mea_orbit_galeri_label( 'Juni 2026 - panel', 5 ) );
t( 'kosong', 'Bagian 6', mea_orbit_galeri_label( '', 6 ) );
t( 'hanya pemisah', 'Bagian 7', mea_orbit_galeri_label( ' — panel', 7 ) );

echo "Field galeri_mode\n";
$def = mea_orbit_all_field_defs()['galeri_mode'];
t( 'default grid', 'grid', $def['default'] );
t( 'bulanan diterima', 'bulanan', mea_orbit_sanitize_field( 'bulanan', $def ) );
t( 'huruf besar dinormalkan', 'bulanan', mea_orbit_sanitize_field( 'Bulanan', $def ) );
t( 'nilai asing → default', 'grid', mea_orbit_sanitize_field( 'karusel', $def ) );
t( 'kosong tetap kosong', '', mea_orbit_sanitize_field( '', $def ) );
$motion = mea_orbit_all_field_defs()['motion'];
t( 'select lain (motion) tidak berubah: live', 'live', mea_orbit_sanitize_field( 'live', $motion ) );
t( 'select lain (motion): asing → opsi pertama (kosong)', '', mea_orbit_sanitize_field( 'xyz', $motion ) );

$rows = array();
foreach ( array( 'Februari 2026 — panel Kriteria Utama', 'Maret 2026 — panel', 'April 2026 — panel' ) as $i => $ket ) {
	$rows[] = array(
		'media_id'   => 100 + $i,
		'keterangan' => $ket,
		'alt'        => 0 === $i ? 'Alt dari field' : '',
	);
}

echo "Template mode grid (default)\n";
$GLOBALS['meta'] = array( 'galeri' => $rows );
$html            = render();
t( 'judul Galeri', true, false !== strpos( $html, '>Galeri</h2>' ) );
t( 'kelas galeri grid', true, false !== strpos( $html, 'class="galeri"' ) );
t( 'tanpa tablist', false, strpos( $html, 'tablist' ) );
t( 'ukuran large', 'large', $GLOBALS['img_calls'][0][1] );

echo "Template mode bulanan\n";
$GLOBALS['meta'] = array(
	'galeri'      => $rows,
	'galeri_mode' => 'bulanan',
);
$html            = render();
t( 'judul section', true, false !== strpos( $html, '>Data per bulan dari Seller Center</h2>' ) );
t( 'satu tablist', 1, substr_count( $html, 'role="tablist"' ) );
t( '3 tab', 3, substr_count( $html, 'role="tab"' ) );
t( '3 panel di HTML', 3, substr_count( $html, 'role="tabpanel"' ) );
t( 'tab 1 terpilih', 1, substr_count( $html, 'aria-selected="true"' ) );
t( 'tab lain tidak terpilih', 2, substr_count( $html, 'aria-selected="false"' ) );
t( 'label tab', true, false !== strpos( $html, '>Maret 2026</a>' ) );
t( 'tab → panel', true, false !== strpos( $html, 'href="#bulan-2" aria-controls="bulan-2"' ) );
t( 'panel ← tab', true, false !== strpos( $html, 'id="bulan-2" aria-labelledby="bulan-tab-2"' ) );
t( 'keterangan utuh di bawah gambar', true, false !== strpos( $html, '<figcaption>Februari 2026 — panel Kriteria Utama</figcaption>' ) );
t( 'ukuran full (tanpa crop)', array( 'full', 'full', 'full' ), array_column( $GLOBALS['img_calls'], 1 ) );
t( 'panel 1 eager, sisanya lazy', array( 'eager', 'lazy', 'lazy' ), array_map( fn( $c ) => $c[2]['loading'], $GLOBALS['img_calls'] ) );
t( 'tanpa fetchpriority tinggi', array( 'auto', 'auto', 'auto' ), array_map( fn( $c ) => $c[2]['fetchpriority'], $GLOBALS['img_calls'] ) );
t( 'alt dari field', 'Alt dari field', $GLOBALS['img_calls'][0][2]['alt'] );
t( 'alt kosong → alt Media Library', false, isset( $GLOBALS['img_calls'][1][2]['alt'] ) );
t( 'tombol sebelumnya nonaktif di awal', true, 1 === preg_match( '/data-arah="-1"[^>]*disabled/', $html ) );
t( 'tombol berikutnya aktif', 0, preg_match( '/data-arah="1"[^>]*disabled/', $html ) );
t( 'tanpa kelas photo (crop 1:1)', false, strpos( $html, 'class="photo"' ) );

echo "Template mode bulanan, satu bulan + video\n";
$GLOBALS['meta']['galeri'] = array( $rows[0] );
$html                      = render();
t( 'satu bulan: tombol berikutnya nonaktif', 1, preg_match( '/data-arah="1"[^>]*disabled/', $html ) );
$GLOBALS['meta']['galeri'] = array(
	$rows[0],
	array(
		'media_id'   => 901,
		'keterangan' => 'Video',
		'alt'        => '',
	),
);
$html                      = render();
t( 'video di panel kedua tidak memuat dulu', true, false !== strpos( $html, 'preload="none"' ) );

echo "Template kosong\n";
$GLOBALS['meta'] = array( 'galeri_mode' => 'bulanan' );
t( 'tanpa section', '', trim( render() ) );

echo "\n$total kasus, $failed gagal\n";
exit( $failed ? 1 : 0 );
