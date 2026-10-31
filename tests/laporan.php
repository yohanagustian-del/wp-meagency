<?php
/**
 * Test inc/laporan.php (tema mea-orbit) dengan stub WordPress minimal.
 *
 * Jalankan: php tests/laporan.php
 * Opsional: php tests/laporan.php <file.json> ... — tiap file hasil wp_get_post_full
 * (context=edit, include meta) dicetak klasifikasinya, untuk cek data live tanpa commit.
 *
 * @package MEA_Orbit
 */

define( 'ABSPATH', __DIR__ . '/' );
define( 'MEA_ORBIT_DIR', dirname( __DIR__ ) . '/wp-content/themes/mea-orbit' );

$GLOBALS['mea_meta']     = array();
$GLOBALS['mea_template'] = array();
$GLOBALS['mea_can_html'] = true;

class WP_Post {
	public $ID;
	public $post_type     = 'page';
	public $post_status   = 'publish';
	public $post_password = '';
	public $post_content  = '';
	public function __construct( $props ) {
		foreach ( $props as $k => $v ) {
			$this->$k = $v;
		}
	}
}
function add_filter() {}
function add_action() {}
function get_post( $post ) {
	return $post instanceof WP_Post ? $post : null;
}
function get_post_meta( $id, $key, $single ) {
	return $GLOBALS['mea_meta'][ $id ][ $key ] ?? '';
}
function get_page_template_slug( $post ) {
	return $GLOBALS['mea_template'][ $post instanceof WP_Post ? $post->ID : $post ] ?? '';
}
function esc_html( $s ) {
	return htmlspecialchars( (string) $s, ENT_QUOTES );
}
function current_user_can() {
	return $GLOBALS['mea_can_html'];
}

require MEA_ORBIT_DIR . '/inc/laporan.php';

$mea_tests_failed = 0;
$mea_tests_total  = 0;

/**
 * Bandingkan nilai dan catat hasilnya.
 *
 * @param string $name     Nama kasus.
 * @param mixed  $expected Nilai yang diharapkan.
 * @param mixed  $actual   Nilai sebenarnya.
 * @return void
 */
function mea_test( $name, $expected, $actual ) {
	global $mea_tests_failed, $mea_tests_total;
	++$mea_tests_total;
	if ( $expected === $actual ) {
		echo "  ok   $name\n";
		return;
	}
	++$mea_tests_failed;
	echo "  GAGAL $name\n    harap: " . var_export( $expected, true ) . "\n    dapat: " . var_export( $actual, true ) . "\n";
}

/**
 * Data Elementor: container berisi widget.
 *
 * @param array<int, array{0:string,1?:string}> $widgets [widgetType, html].
 * @return string JSON seperti _elementor_data.
 */
function mea_el( $widgets ) {
	$els = array();
	foreach ( $widgets as $w ) {
		$els[] = array(
			'elType'     => 'widget',
			'widgetType' => $w[0],
			'settings'   => isset( $w[1] ) ? array( 'html' => $w[1] ) : array(),
			'elements'   => array(),
		);
	}
	return wp_json( array( array( 'elType' => 'container', 'elements' => array( array( 'elType' => 'container', 'elements' => $els ) ) ) ) );
}
function wp_json( $v ) {
	return json_encode( $v );
}

/**
 * Halaman uji.
 *
 * @param int                  $id    ID.
 * @param array<string, mixed> $props Properti post.
 * @param array<string, mixed> $meta  Meta.
 * @param string               $tpl   Template.
 * @return WP_Post
 */
function mea_page( $id, $props = array(), $meta = array(), $tpl = '' ) {
	$GLOBALS['mea_meta'][ $id ]     = $meta;
	$GLOBALS['mea_template'][ $id ] = $tpl;
	return new WP_Post( array_merge( array( 'ID' => $id ), $props ) );
}

$doc = "<!DOCTYPE html><html lang=\"id\"><head><meta charset=\"UTF-8\"><title>Weekly</title><style>body{margin:0}</style></head><body><h1>Laporan</h1><script>1</script></body></html>";

if ( $argc > 1 ) {
	foreach ( array_slice( $argv, 1 ) as $file ) {
		$j    = json_decode( file_get_contents( $file ), true );
		$p    = $j['post'];
		$meta = $j['meta'];
		if ( isset( $meta['rank_math_robots'] ) && is_string( $meta['rank_math_robots'] ) ) {
			$meta['rank_math_robots'] = unserialize( $meta['rank_math_robots'] );
		}
		$page = mea_page( $p['id'], array( 'post_password' => $p['password'], 'post_status' => $p['status'] ), $meta, (string) $p['template'] );
		$html = mea_orbit_laporan_html( $page );
		printf( "%-6d %-45s %s\n", $p['id'], $p['slug'], '' === $html ? 'bukan laporan (template biasa)' : 'LAPORAN (' . strlen( $html ) . ' byte)' );
	}
	exit;
}

echo "Deteksi dokumen\n";
mea_test( 'doctype', true, mea_orbit_laporan_is_document( $doc ) );
mea_test( 'komentar + spasi lalu <html>', true, mea_orbit_laporan_is_document( "\n<!-- x -->\n<HTML lang=id>" ) );
mea_test( 'potongan style+div bukan dokumen', false, mea_orbit_laporan_is_document( '<style>.a{}</style><div>x</div>' ) );
mea_test( '<htmlx> bukan dokumen', false, mea_orbit_laporan_is_document( '<htmlx>' ) );

echo "Laporan Elementor lama\n";
$p = mea_page( 1, array( 'post_password' => 'rahasia' ), array( '_elementor_edit_mode' => 'builder', '_elementor_data' => mea_el( array( array( 'html', $doc ) ) ) ) );
mea_test( 'berpassword + satu widget HTML dokumen utuh = laporan', $doc, mea_orbit_laporan_html( $p ) );
$p = mea_page( 2, array(), array( '_elementor_edit_mode' => 'builder', '_elementor_data' => mea_el( array( array( 'html', $doc ) ) ) ) );
mea_test( 'terbuka dan terindeks = tidak disentuh', '', mea_orbit_laporan_html( $p ) );
$p = mea_page( 3, array(), array( '_elementor_edit_mode' => 'builder', 'rank_math_robots' => array( 'noindex', 'nofollow' ), '_elementor_data' => mea_el( array( array( 'html', $doc ) ) ) ) );
mea_test( 'noindex Rank Math = laporan', $doc, mea_orbit_laporan_html( $p ) );
$p = mea_page( 4, array( 'post_status' => 'private' ), array( '_elementor_edit_mode' => 'builder', '_elementor_data' => mea_el( array( array( 'html', $doc ) ) ) ) );
mea_test( 'private = laporan', $doc, mea_orbit_laporan_html( $p ) );
$p = mea_page( 5, array( 'post_password' => 'x' ), array( '_elementor_edit_mode' => 'builder', '_elementor_data' => mea_el( array( array( 'html', $doc ), array( 'heading' ) ) ) ) );
mea_test( 'ada widget lain = bukan laporan', '', mea_orbit_laporan_html( $p ) );
$p = mea_page( 6, array( 'post_password' => 'x' ), array( '_elementor_edit_mode' => 'builder', '_elementor_data' => mea_el( array( array( 'html', '<style>a{}</style><div>x</div>' ) ) ) ) );
mea_test( 'widget HTML potongan (bukan dokumen) = bukan laporan', '', mea_orbit_laporan_html( $p ) );
$p = mea_page( 7, array( 'post_password' => 'x' ), array( '_elementor_edit_mode' => '', '_elementor_data' => mea_el( array( array( 'html', $doc ) ) ) ) );
mea_test( 'mode builder mati = bukan laporan', '', mea_orbit_laporan_html( $p ) );
$p = mea_page( 8, array( 'post_password' => 'x' ), array( '_elementor_edit_mode' => 'builder', '_elementor_data' => '{rusak' ) );
mea_test( 'JSON rusak = bukan laporan', '', mea_orbit_laporan_html( $p ) );
$p = new WP_Post( array( 'ID' => 9, 'post_type' => 'post', 'post_password' => 'x' ) );
mea_test( 'post (bukan page) tidak disentuh', '', mea_orbit_laporan_html( $p ) );

echo "Template Laporan HTML\n";
$p = mea_page( 10, array( 'post_content' => '<p>editor</p>' ), array( 'mea_laporan_html' => $doc ), MEA_ORBIT_LAPORAN_TEMPLATE );
mea_test( 'field diutamakan', $doc, mea_orbit_laporan_html( $p ) );
$p = mea_page( 11, array( 'post_content' => " <p>editor</p> " ), array(), MEA_ORBIT_LAPORAN_TEMPLATE );
mea_test( 'field kosong: isi editor', '<p>editor</p>', mea_orbit_laporan_html( $p ) );
$p = mea_page( 12, array(), array( '_elementor_edit_mode' => 'builder', '_elementor_data' => mea_el( array( array( 'html', '<div>a</div>' ), array( 'html', '<div>b</div>' ) ) ) ), MEA_ORBIT_LAPORAN_TEMPLATE );
mea_test( 'field dan editor kosong: widget HTML Elementor (boleh potongan, digabung)', "<div>a</div>\n<div>b</div>", mea_orbit_laporan_html( $p ) );
$p = mea_page( 13, array(), array(), MEA_ORBIT_LAPORAN_TEMPLATE );
mea_test( 'semua kosong = string kosong', '', mea_orbit_laporan_html( $p ) );

echo "Dokumen yang dicetak\n";
$out = mea_orbit_laporan_document( $doc, 'Judul' );
mea_test( 'meta robots tepat setelah <head>', true, false !== strpos( $out, '<head><meta name="robots" content="noindex, nofollow"><meta charset' ) );
mea_test( 'isi lain tidak berubah', $doc, str_replace( '<meta name="robots" content="noindex, nofollow">', '', $out ) );
mea_test( 'meta robots hanya sekali', 1, substr_count( mea_orbit_laporan_document( '<!DOCTYPE html><html><head></head><body><head></head></body></html>', 'x' ), 'name="robots"' ) );
mea_test( 'dokumen tanpa <head>: head disisipkan setelah <html>', '<!DOCTYPE html><html lang="id"><head><meta name="robots" content="noindex, nofollow"></head><body>x</body></html>', mea_orbit_laporan_document( '<!DOCTYPE html><html lang="id"><body>x</body></html>', 'x' ) );
$out = mea_orbit_laporan_document( '<div>potongan</div>', 'A & B' );
mea_test( 'potongan dibungkus dokumen + judul di-escape', true, 0 === strpos( $out, '<!DOCTYPE html>' ) && false !== strpos( $out, '<title>A &amp; B</title>' ) && false !== strpos( $out, "<body>\n<div>potongan</div>" ) && false !== strpos( $out, 'noindex' ) );
mea_test( '<header> di body tidak dikira <head>', 1, substr_count( mea_orbit_laporan_document( '<!DOCTYPE html><html><body><header>h</header></body></html>', 'x' ), '<head><meta name="robots"' ) );

echo "\n$mea_tests_total kasus, $mea_tests_failed gagal\n";
exit( $mea_tests_failed ? 1 : 0 );
