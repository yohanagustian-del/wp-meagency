<?php
/**
 * Test inc/redirects.php (tema mea-orbit) dengan stub WordPress dan Rank Math minimal.
 *
 * Jalankan: php tests/redirects.php
 *
 * @package MEA_Orbit
 */

namespace RankMath\Redirections {
	/** Stub DB Rank Math: daftar redirect dari $GLOBALS, pembanding meniru DB::compare_sources. */
	class DB {
		public static function get_redirections( $args ) {
			return array( 'redirections' => $GLOBALS['mea_rm_rows'], 'count' => count( $GLOBALS['mea_rm_rows'] ) );
		}
		public static function compare_sources( $sources, $uri ) {
			foreach ( $sources as $source ) {
				if ( 'exact' === $source['comparison'] && trim( $source['pattern'], '/' ) === untrailingslashit( $uri ) ) {
					return true;
				}
			}
			return false;
		}
	}
	/** Stub Redirection: mencatat entri yang disimpan. */
	class Redirection {
		private $data;
		public static function from( $data ) {
			$o       = new self();
			$o->data = $data;
			return $o;
		}
		public function save() {
			$GLOBALS['mea_rm_saved'][] = $this->data;
			return 100 + count( $GLOBALS['mea_rm_saved'] );
		}
	}
}

namespace {
	define( 'ABSPATH', __DIR__ . '/' );
	$GLOBALS['mea_rm_rows']  = array();
	$GLOBALS['mea_rm_saved'] = array();
	$GLOBALS['mea_posts']    = array();

	function add_filter() {}
	function add_action() {}
	function home_url( $path = '' ) {
		return 'https://www.meagency.co.id' . ( '' === $path ? '' : '/' . ltrim( $path, '/' ) );
	}
	function wp_parse_url( $url, $component = -1 ) {
		return parse_url( $url, $component );
	}
	function untrailingslashit( $s ) {
		return rtrim( $s, '/\\' );
	}
	function maybe_unserialize( $v ) {
		return is_string( $v ) ? unserialize( $v ) : $v;
	}
	function url_to_postid( $url ) {
		return $GLOBALS['mea_posts'][ parse_url( $url, PHP_URL_PATH ) ] ?? 0;
	}
	function get_post_status( $id ) {
		return 'publish';
	}
	function get_post_field( $field, $id ) {
		return '';
	}
	function wp_list_pluck( $list, $key ) {
		return array_column( $list, $key );
	}

	require dirname( __DIR__ ) . '/wp-content/themes/mea-orbit/inc/redirects.php';

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

	echo "Daftar redirect\n";
	$sources = array();
	foreach ( mea_orbit_redirect_rules() as $rule ) {
		foreach ( $rule['sources'] as $s ) {
			$sources[ $s ] = $rule['to'];
		}
	}
	t( '10 sumber baru', 10, count( $sources ) );
	t( '/en/ ke beranda', '/', $sources['/en/'] );
	t( '/a-affiliate-mcn-mea-vk/ langsung ke meamcn.com/kreator', 'https://meamcn.com/kreator/', $sources['/a-affiliate-mcn-mea-vk/'] );
	t( '12 sumber redirect objek lama', 12, count( mea_orbit_redirect_legacy_sources() ) );
	t( 'tidak ada sumber yang juga tujuan', array(), array_values( array_intersect( array_keys( $sources ), array_values( $sources ) ) ) );

	echo "URI pembanding\n";
	t( 'URL penuh', 'jasa-optimasi-ads', mea_orbit_redirect_uri( 'https://www.meagency.co.id/jasa-optimasi-ads/' ) );
	t( 'path', 'en', mea_orbit_redirect_uri( '/en/' ) );
	t( 'beranda kosong', '', mea_orbit_redirect_uri( 'https://www.meagency.co.id/' ) );

	echo "Penerapan (sebelum ada redirect)\n";
	$GLOBALS['mea_rm_rows'] = array(
		array(
			'id'          => 7,
			'sources'     => serialize( array( array( 'pattern' => 'jasa-live-shopee', 'comparison' => 'exact', 'ignore' => '' ) ) ),
			'url_to'      => 'https://www.meagency.co.id/jasa-live-streaming/',
			'header_code' => '301',
		),
		array(
			'id'          => 8,
			'sources'     => serialize( array( array( 'pattern' => 'homepage-mea', 'comparison' => 'exact', 'ignore' => '' ) ) ),
			'url_to'      => 'https://www.meagency.co.id/',
			'header_code' => '301',
		),
	);
	mea_orbit_active_redirections( true );
	$res = mea_orbit_redirect_apply();
	t( '8 entri diproses', 8, count( $res['hasil'] ) );
	t( '8 entri disimpan', 8, count( $GLOBALS['mea_rm_saved'] ) );
	$home = $res['hasil'][6];
	t( 'sumber yang sudah ada dilewati, tidak ditimpa', array( '/home-page/', '/en/' ), $home['dibuat'] );
	t( 'yang dilewati dilaporkan', 8, $home['dilewati'][0]['redirect_id'] );
	t( 'tujuan URL penuh, 301', array( 'https://www.meagency.co.id/jasa-tiktok-ads/', '301' ), array( $GLOBALS['mea_rm_saved'][0]['url_to'], $GLOBALS['mea_rm_saved'][0]['header_code'] ) );
	t( 'sumber exact', 'exact', $GLOBALS['mea_rm_saved'][0]['sources'][0]['comparison'] );
	t( 'tujuan eksternal disimpan apa adanya', 'https://meamcn.com/kreator/', $GLOBALS['mea_rm_saved'][2]['url_to'] );
	t( 'tujuan path jadi URL situs', 'https://www.meagency.co.id/about-us/', mea_orbit_redirect_target( '/about-us/' ) );
	t( 'tujuan URL absolut tidak diubah', 'https://meamcn.com/kreator/', mea_orbit_redirect_target( 'https://meamcn.com/kreator/' ) );

	echo "Sitemap\n";
	$entry = array( 'loc' => 'https://www.meagency.co.id/jasa-live-shopee/' );
	t( 'URL ber-redirect keluar dari sitemap', false, mea_orbit_redirect_sitemap_entry( $entry, 'post' ) );
	$entry = array( 'loc' => 'https://www.meagency.co.id/jasa-live-streaming/' );
	t( 'URL tujuan tetap di sitemap', $entry, mea_orbit_redirect_sitemap_entry( $entry, 'post' ) );
	$entry = array( 'loc' => 'https://www.meagency.co.id/' );
	t( 'beranda tetap di sitemap', $entry, mea_orbit_redirect_sitemap_entry( $entry, 'post' ) );
	t( 'entri false tetap false', false, mea_orbit_redirect_sitemap_entry( false, 'post' ) );

	echo "Laporan\n";
	$GLOBALS['mea_posts'] = array( '/jasa-live-shopee/' => 9299, '/jasa-webinar/' => 224 );
	$rep  = mea_orbit_redirect_report();
	$byid = array_column( $rep['lama'], null, 'sumber' );
	t( 'redirect objek lama cocok 301', true, $byid['/jasa-live-shopee/']['cocok'] );
	t( 'objek ber-redirect tidak masuk sitemap', false, $byid['/jasa-live-shopee/']['masuk_sitemap'] );
	t( 'objek tanpa redirect terdeteksi masuk sitemap', true, $byid['/jasa-webinar/']['masuk_sitemap'] );
	t( 'objek tanpa redirect tidak cocok', false, $byid['/jasa-webinar/']['cocok'] );
	$baru = array_column( $rep['baru'], null, 'sumber' );
	t( 'sumber baru dengan tujuan sesuai', true, $baru['/homepage-mea/']['cocok'] );
	t( 'tujuan harap eksternal dilaporkan apa adanya', 'https://meamcn.com/kreator/', $baru['/a-affiliate-mcn-mea-vk/']['tujuan_harap'] );

	echo "\n$total kasus, $failed gagal\n";
	exit( $failed ? 1 : 0 );
}
