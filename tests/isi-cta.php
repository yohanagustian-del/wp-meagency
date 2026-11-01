<?php
/**
 * Test mea_orbit_isi_cta_lama() (tema mea-orbit, inc/orbit.php). Jalankan: php tests/isi-cta.php
 *
 * @package MEA_Orbit
 */

define( 'ABSPATH', __DIR__ . '/' );
function add_action() {}
function add_filter() {}
function wp_parse_url( $url, $component = -1 ) {
	return parse_url( $url, $component );
}
require dirname( __DIR__ ) . '/wp-content/themes/mea-orbit/inc/orbit.php';
require dirname( __DIR__ ) . '/wp-content/themes/mea-orbit/inc/cta-lama.php';

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

$k = '<a href="#konsultasi">';

t( 'relatif /klik/', '<p>' . $k . 'Konsultasi</a></p>', mea_orbit_isi_cta_lama( '<p><a href="/klik/wa-optimasi">Konsultasi</a></p>' ) );
t( 'absolut + target/rel dibuang', $k . 'CTA</a>', mea_orbit_isi_cta_lama( '<a href="https://meagency.co.id/klik/x/?a=1&amp;b=2" target="_blank" rel="noopener noreferrer">CTA</a>' ) );
t( 'www, http, target sebelum href', $k . 'CTA</a>', mea_orbit_isi_cta_lama( '<a target="_blank" href="http://www.meagency.co.id/klik/x">CTA</a>' ) );
t( 'protokol relatif, kutip tunggal', $k . 'CTA</a>', mea_orbit_isi_cta_lama( "<a href='//meagency.co.id/klik/x' rel='nofollow'>CTA</a>" ) );
t( 'sebari form', $k . 'Daftar</a>', mea_orbit_isi_cta_lama( '<a href="https://sebari.co.id/form/konsultasi-mea" target="_blank">Daftar</a>' ) );
t( 'href rusak berisi &quot;', $k . 'X</a>', mea_orbit_isi_cta_lama( '<a href="/klik/optimasi&quot;">X</a>' ) );
t( 'huruf besar', '<A href="#konsultasi">X</a>', mea_orbit_isi_cta_lama( '<A HREF="HTTPS://MEAGENCY.CO.ID/KLIK/X" TARGET="_blank">X</a>' ) );

$tetap = array(
	'link lain'                 => '<a href="https://meagency.co.id/contactus/" target="_blank" rel="noopener">Kontak</a>',
	'sebari bukan form'         => '<a href="https://sebari.co.id/" target="_blank">Sebari</a>',
	'domain lain /klik/'        => '<a href="https://contoh.id/klik/x" target="_blank">x</a>',
	'path mirip /klikmea/'      => '<a href="/klikmea/x">x</a>',
	'klik di tengah path'       => '<a href="/blog/klik/x">x</a>',
	'subdomain sebari palsu'    => '<a href="https://sebari.co.id.evil.id/form/x">x</a>',
	'teks /klik/ di luar link'  => '<p>Buka /klik/ atau sebari.co.id/form/x</p>',
);
foreach ( $tetap as $name => $html ) {
	t( 'tidak disentuh: ' . $name, $html, mea_orbit_isi_cta_lama( $html ) );
}

t(
	'campuran: hanya CTA lama yang berubah',
	'<p><a href="/jasa-website/">Web</a> dan ' . $k . 'WA</a></p>',
	mea_orbit_isi_cta_lama( '<p><a href="/jasa-website/">Web</a> dan <a href="/klik/wa" target="_blank" rel="noopener">WA</a></p>' )
);

// Artikel (pengganti Code Snippet #14): tujuan = form konsultasi homepage.
t(
	'artikel: tujuan homepage',
	'<p><a href="https://www.meagency.co.id/#konsultasi">Konsultasi</a></p>',
	mea_orbit_isi_cta_lama( '<p><a href="https://sebari.co.id/form/mea" target="_blank" rel="noopener">Konsultasi</a></p>', 'https://www.meagency.co.id/#konsultasi' )
);
t( 'artikel: idempoten', '<a href="https://www.meagency.co.id/#konsultasi">x</a>', mea_orbit_isi_cta_lama( '<a href="https://www.meagency.co.id/#konsultasi">x</a>', 'https://www.meagency.co.id/#konsultasi' ) );

foreach ( array( '/klik/wa' => true, '/klik' => true, '/klik/' => true, '/klik/x?a=1' => true, '/klikmea/' => false, '/blog/klik/x' => false, '/' => false ) as $uri => $harap ) {
	t( 'path ' . $uri, $harap, mea_orbit_cta_lama_path( $uri ) );
}

echo "\n$total kasus, $failed gagal\n";
exit( $failed ? 1 : 0 );
