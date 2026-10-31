<?php
/**
 * Test inc/ganti-teks.php (tema mea-orbit). Jalankan: php tests/ganti-teks.php
 *
 * @package MEA_Orbit
 */

define( 'ABSPATH', __DIR__ . '/' );
function add_action() {}
function wp_json_encode( $v ) {
	return json_encode( $v );
}
require dirname( __DIR__ ) . '/wp-content/themes/mea-orbit/inc/ganti-teks.php';

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

// _elementor_data asli menyimpan HTML dalam string JSON: \/ \" dan \uXXXX.
$data    = json_encode( array( array( 'settings' => array( 'editor' => '<p>Bagi pembeli, <a href="https://x.id/lama/">Shopee Mall</a> menawarkan — aman.</p>' ) ) ) );
$content = 'Bagi pembeli, <a href="https://x.id/lama/">Shopee Mall</a> menawarkan — aman.';
$pair    = array(
	'lama' => 'href="https://x.id/lama/">Shopee Mall</a>',
	'baru' => 'href="https://x.id/baru/">Shopee Mall</a>',
);

$h = mea_orbit_ganti_teks_hitung( $content, $data, array( $pair ) );
t( 'cocok di dua tempat', array( true, 1, 1 ), array( $h['ok'], $h['laporan'][0]['post_content'], $h['laporan'][0]['elementor_data'] ) );
t( 'post_content diganti', 'Bagi pembeli, <a href="https://x.id/baru/">Shopee Mall</a> menawarkan — aman.', $h['content'] );
$decoded = json_decode( $h['data'], true );
t( '_elementor_data tetap JSON valid dan terganti', '<p>Bagi pembeli, <a href="https://x.id/baru/">Shopee Mall</a> menawarkan — aman.</p>', $decoded[0]['settings']['editor'] );

$h = mea_orbit_ganti_teks_hitung( $content . ' ' . $content, $data, array( $pair ) );
t( 'muncul dua kali: ditolak, tidak ada yang diganti', array( false, $content . ' ' . $content ), array( $h['ok'], $h['content'] ) );

$h = mea_orbit_ganti_teks_hitung( $content, $data, array( array( 'lama' => 'tidak ada', 'baru' => 'x' ) ) );
t( 'tidak ditemukan: ditolak', false, $h['ok'] );

$h = mea_orbit_ganti_teks_hitung( $content, '', array( $pair ) );
t( 'tanpa data Elementor: cukup post_content', array( true, 0 ), array( $h['ok'], $h['laporan'][0]['elementor_data'] ) );

$h = mea_orbit_ganti_teks_hitung( $content, $data, array( array( 'lama' => 'menawarkan — aman', 'baru' => 'menawarkan "aman"' ) ) );
t( 'karakter unicode dan kutip', array( true, 'menawarkan "aman"' ), array( $h['ok'], substr( json_decode( $h['data'], true )[0]['settings']['editor'], -22, 17 ) ) );

echo "\n$total kasus, $failed gagal\n";
exit( $failed ? 1 : 0 );
