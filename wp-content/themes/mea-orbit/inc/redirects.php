<?php
/**
 * Redirect 301 lewat Rank Math > Redirections, dan URL ber-redirect di luar sitemap.
 *
 * - Daftar redirect berbasis URL (sumber = path, bukan objek) disimpan di sini sebagai
 *   catatan yang bisa diaudit. Tema tidak pernah me-redirect sendiri: redirect dibuat
 *   sebagai entri Rank Math lewat `POST /wp-json/mea-orbit/v1/redirects` (admin), jadi
 *   tetap terlihat dan bisa diubah di Rank Math > Redirections. Entri yang sumbernya sudah
 *   punya redirect aktif dilewati, tidak ditimpa.
 * - `GET /wp-json/mea-orbit/v1/redirects` melaporkan, per sumber (redirect baru dan
 *   12 redirect objek lama), redirect Rank Math yang cocok dan apakah URL sumber masih
 *   akan masuk sitemap.
 * - Rank Math tidak mengeluarkan post yang punya redirect dari sitemap. Filter
 *   `rank_math/sitemap/entry` di sini membuang entri yang URL-nya cocok dengan redirect
 *   aktif, supaya sitemap tidak berisi URL yang menjawab 301.
 *
 * Diam bila modul Redirections Rank Math tidak aktif.
 *
 * @package MEA_Orbit
 */

defined( 'ABSPATH' ) || exit;

/**
 * Redirect 301 berbasis URL (31 Okt 2026). Satu entri Rank Math per baris; baris dengan
 * beberapa sumber menjadi satu entri bersumber banyak.
 *
 * @return array<int, array{sources: string[], to: string}>
 */
function mea_orbit_redirect_rules() {
	return array(
		array(
			'sources' => array( '/mea-jasa-ads-tik-tok/' ),
			'to'      => '/jasa-tiktok-ads/',
		),
		array(
			'sources' => array( '/jasa-traffic-iklan-dan-affiliate-mea/' ),
			'to'      => '/affiliate/',
		),
		array(
			'sources' => array( '/a-affiliate-mcn-mea-vk/' ),
			'to'      => '/gabung-mcn-mea/',
		),
		array(
			'sources' => array( '/content-service/' ),
			'to'      => '/jasa-konten/',
		),
		array(
			'sources' => array( '/jasa-optimasi-ads/' ),
			'to'      => '/jasa-performance-ads/',
		),
		array(
			'sources' => array( '/jasa-marketing-e-commerce/' ),
			'to'      => '/ecommerce-service/',
		),
		array(
			'sources' => array( '/home-page/', '/homepage-mea/', '/en/' ),
			'to'      => '/',
		),
		array(
			'sources' => array( '/about-us-2-0/' ),
			'to'      => '/about-us/',
		),
	);
}

/**
 * Sumber redirect objek yang sudah ada sebelum 31 Okt 2026 (dibuat dari editor halaman/
 * artikel di Rank Math). Hanya dicek, tidak diubah.
 *
 * @return string[]
 */
function mea_orbit_redirect_legacy_sources() {
	return array(
		'/jasa-live-shopee/',
		'/marketplace-eccomerce-management/',
		'/jasa-meta-ads-cpas/',
		'/fotografi-dan-videografi-produk/',
		'/a-program-matchmaking-top-creator-shopee/',
		'/search-engine-marketing-management/',
		'/jasa-webinar/',
		'/email-marketing-management/',
		'/jasa-iklan-tiktok-ads/',
		'/jasa-affiliate-tiktok-shop/',
		'/jasa-kelola-sosial-media-maksimalkan-branding-bisnis/',
		'/mengapa-anda-harus-memilih-jasa-iklan-shopee-untuk-meningkatkan-visibilitas-produk-anda/',
	);
}

/**
 * Apakah modul Redirections Rank Math tersedia.
 *
 * @return bool
 */
function mea_orbit_redirections_ready() {
	return class_exists( '\RankMath\Redirections\DB' ) && class_exists( '\RankMath\Redirections\Redirection' );
}

/**
 * Path situs menjadi URI pembanding Rank Math (tanpa garis miring di ujung, sama dengan
 * `Redirector::$uri`).
 *
 * @param string $url URL penuh atau path.
 * @return string
 */
function mea_orbit_redirect_uri( $url ) {
	$path = (string) wp_parse_url( (string) $url, PHP_URL_PATH );
	$home = (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH );
	if ( '' !== $home && '/' !== $home && 0 === strpos( $path, $home ) ) {
		$path = substr( $path, strlen( $home ) - 1 );
	}
	return trim( rawurldecode( $path ), '/' );
}

/**
 * Semua redirect aktif Rank Math, dibaca sekali per request (sitemap memanggil filter
 * untuk setiap entri).
 *
 * @param bool $refresh Baca ulang dari database.
 * @return array<int, array<string, mixed>>
 */
function mea_orbit_active_redirections( $refresh = false ) {
	static $rows = null;
	if ( null !== $rows && ! $refresh ) {
		return $rows;
	}
	$rows = array();
	if ( ! mea_orbit_redirections_ready() ) {
		return $rows;
	}
	$result = \RankMath\Redirections\DB::get_redirections(
		array(
			'status' => 'active',
			'limit'  => 5000,
		)
	);
	foreach ( (array) ( $result['redirections'] ?? array() ) as $row ) {
		$row['sources'] = maybe_unserialize( $row['sources'] );
		if ( is_array( $row['sources'] ) && $row['sources'] ) {
			$rows[] = $row;
		}
	}
	return $rows;
}

/**
 * Redirect aktif yang cocok dengan sebuah URI, memakai pembanding Rank Math sendiri.
 *
 * @param string $uri URI tanpa garis miring di ujung.
 * @return array<string, mixed>|null
 */
function mea_orbit_redirect_match( $uri ) {
	if ( '' === $uri || ! mea_orbit_redirections_ready() ) {
		return null;
	}
	foreach ( mea_orbit_active_redirections() as $row ) {
		if ( \RankMath\Redirections\DB::compare_sources( $row['sources'], $uri ) ) {
			return $row;
		}
	}
	return null;
}

/**
 * URL yang menjawab 301 tidak boleh ada di sitemap: buang entri yang cocok dengan
 * redirect aktif (302/307 juga, karena URL-nya tidak menampilkan isi sendiri).
 *
 * @param array<string, mixed>|false $url  Entri sitemap.
 * @param string                     $type Jenis entri.
 * @return array<string, mixed>|false
 */
function mea_orbit_redirect_sitemap_entry( $url, $type ) {
	if ( ! is_array( $url ) || empty( $url['loc'] ) || 'user' === $type ) {
		return $url;
	}
	$uri = mea_orbit_redirect_uri( $url['loc'] );
	if ( '' === $uri ) {
		return $url;
	}
	$match = mea_orbit_redirect_match( $uri );
	if ( $match && in_array( (string) $match['header_code'], array( '301', '302', '307' ), true ) ) {
		return false;
	}
	return $url;
}
add_filter( 'rank_math/sitemap/entry', 'mea_orbit_redirect_sitemap_entry', 100, 2 );

/**
 * Status satu sumber: redirect yang cocok dan apakah objeknya masih calon sitemap.
 *
 * @param string $source Path sumber.
 * @param string $expect Tujuan yang diharapkan (path) atau kosong.
 * @return array<string, mixed>
 */
function mea_orbit_redirect_report_row( $source, $expect = '' ) {
	$uri     = mea_orbit_redirect_uri( $source );
	$match   = mea_orbit_redirect_match( $uri );
	$post_id = url_to_postid( home_url( $source ) );
	$row     = array(
		'sumber'        => $source,
		'redirect_id'   => $match ? (int) $match['id'] : 0,
		'kode'          => $match ? (int) $match['header_code'] : 0,
		'tujuan'        => $match ? (string) $match['url_to'] : '',
		'objek_id'      => $post_id,
		'objek_status'  => $post_id ? (string) get_post_status( $post_id ) : '',
		// Sitemap memakai filter di atas: URL dengan redirect aktif selalu keluar.
		'masuk_sitemap' => ! $match && $post_id && 'publish' === get_post_status( $post_id ) && '' === (string) get_post_field( 'post_password', $post_id ),
	);
	if ( '' !== $expect ) {
		$row['tujuan_harap'] = home_url( $expect );
		$row['cocok']        = $match && 301 === $row['kode'] && untrailingslashit( $row['tujuan'] ) === untrailingslashit( home_url( $expect ) );
	} else {
		$row['cocok'] = $match && 301 === $row['kode'];
	}
	return $row;
}

/**
 * Laporan semua sumber redirect.
 *
 * @return array<string, mixed>
 */
function mea_orbit_redirect_report() {
	$baru = array();
	foreach ( mea_orbit_redirect_rules() as $rule ) {
		foreach ( $rule['sources'] as $source ) {
			$baru[] = mea_orbit_redirect_report_row( $source, $rule['to'] );
		}
	}
	$lama = array();
	foreach ( mea_orbit_redirect_legacy_sources() as $source ) {
		$lama[] = mea_orbit_redirect_report_row( $source );
	}
	return array(
		'rank_math_redirections' => mea_orbit_redirections_ready(),
		'baru'                   => $baru,
		'lama'                   => $lama,
	);
}

/**
 * Buat redirect yang belum ada di Rank Math. Sumber yang sudah punya redirect aktif
 * dilewati dan dilaporkan (tidak ditimpa).
 *
 * @return array<string, mixed>
 */
function mea_orbit_redirect_apply() {
	if ( ! mea_orbit_redirections_ready() ) {
		return array( 'error' => 'Modul Redirections Rank Math tidak aktif.' );
	}
	$out = array();
	foreach ( mea_orbit_redirect_rules() as $rule ) {
		$sources = array();
		$skip    = array();
		foreach ( $rule['sources'] as $source ) {
			$match = mea_orbit_redirect_match( mea_orbit_redirect_uri( $source ) );
			if ( $match ) {
				$skip[] = array(
					'sumber'      => $source,
					'redirect_id' => (int) $match['id'],
					'tujuan'      => (string) $match['url_to'],
				);
				continue;
			}
			$sources[] = array(
				'pattern'    => $source,
				'comparison' => 'exact',
				'ignore'     => '',
			);
		}
		$id = 0;
		if ( $sources ) {
			$redirection = \RankMath\Redirections\Redirection::from(
				array(
					'sources'     => $sources,
					'url_to'      => home_url( $rule['to'] ),
					'header_code' => '301',
				)
			);
			$id          = (int) $redirection->save();
		}
		$out[] = array(
			'tujuan'      => $rule['to'],
			'dibuat'      => wp_list_pluck( $sources, 'pattern' ),
			'redirect_id' => $id,
			'dilewati'    => $skip,
		);
	}
	return array( 'hasil' => $out );
}

/**
 * Rute REST laporan dan penerapan redirect (khusus admin).
 *
 * @return void
 */
function mea_orbit_redirect_routes() {
	$admin = static function () {
		return current_user_can( 'manage_options' );
	};
	register_rest_route(
		'mea-orbit/v1',
		'/redirects',
		array(
			array(
				'methods'             => 'GET',
				'permission_callback' => $admin,
				'callback'            => static function () {
					return rest_ensure_response( mea_orbit_redirect_report() );
				},
			),
			array(
				'methods'             => 'POST',
				'permission_callback' => $admin,
				'callback'            => static function () {
					$result = mea_orbit_redirect_apply();
					// Laporan dibaca ulang dari database setelah entri baru disimpan.
					if ( isset( $result['hasil'] ) ) {
						mea_orbit_active_redirections( true );
						$result['laporan'] = mea_orbit_redirect_report();
					}
					return rest_ensure_response( $result );
				},
			),
		)
	);
}
add_action( 'rest_api_init', 'mea_orbit_redirect_routes' );
