<?php
/**
 * Update tema dari repo GitHub.
 *
 * WordPress menampilkan "Update now" untuk mea-orbit (Tampilan → Tema, atau alat update
 * tema di konektor) bila manifest rilis memuat versi lebih baru dari yang terpasang.
 * Jalurnya: header `Update URI` di style.css → filter `update_themes_github.com`
 * (WordPress 6.1+). Paket zip dicocokkan dengan SHA-256 di manifest sebelum dipasang;
 * paket yang tidak cocok ditolak.
 *
 * Manifest default ada di branch utama repo; ganti lewat konstanta MEA_ORBIT_UPDATE_JSON
 * di wp-config.php bila perlu.
 *
 * @package MEA_Orbit
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'MEA_ORBIT_UPDATE_JSON' ) ) {
	define( 'MEA_ORBIT_UPDATE_JSON', 'https://raw.githubusercontent.com/yohanagustian-del/wp-meagency/HEAD/dist/mea-orbit-update.json' );
}

/**
 * Manifest rilis yang valid: version, package (zip di repo yang sama), sha256,
 * requires, requires_php. Array kosong bila tidak ada atau tidak valid.
 *
 * @param bool $fresh Ambil ulang dari GitHub (saat WordPress mengecek update).
 * @return array<string, string>
 */
function mea_orbit_update_manifest( $fresh = false ) {
	$cached = get_site_transient( 'mea_orbit_update_manifest' );
	if ( ! $fresh && is_array( $cached ) ) {
		return $cached;
	}

	$data     = array();
	$response = wp_remote_get( MEA_ORBIT_UPDATE_JSON, array( 'timeout' => 10 ) );
	if ( ! is_wp_error( $response ) && 200 === (int) wp_remote_retrieve_response_code( $response ) ) {
		$json = json_decode( (string) wp_remote_retrieve_body( $response ), true );
		if (
			is_array( $json )
			&& preg_match( '/^\d+\.\d+\.\d+$/', (string) ( $json['version'] ?? '' ) )
			&& 0 === strpos( (string) ( $json['package'] ?? '' ), 'https://raw.githubusercontent.com/yohanagustian-del/wp-meagency/' )
			&& preg_match( '/^[a-f0-9]{64}$/', (string) ( $json['sha256'] ?? '' ) )
		) {
			$data = array(
				'version'      => (string) $json['version'],
				'package'      => (string) $json['package'],
				'sha256'       => (string) $json['sha256'],
				'requires'     => (string) ( $json['requires'] ?? '' ),
				'requires_php' => (string) ( $json['requires_php'] ?? '' ),
			);
		}
	}

	set_site_transient( 'mea_orbit_update_manifest', $data, 12 * HOUR_IN_SECONDS );
	return $data;
}

/**
 * Data update mea-orbit untuk pengecekan update tema WordPress.
 *
 * @param array|false          $update     Data update dari filter sebelumnya.
 * @param array<string, mixed> $theme_data Header tema terpasang.
 * @param string               $stylesheet Folder tema.
 * @return array|false
 */
function mea_orbit_update_check( $update, $theme_data, $stylesheet ) {
	if ( 'mea-orbit' !== $stylesheet ) {
		return $update;
	}
	$manifest = mea_orbit_update_manifest( true );
	if ( ! $manifest ) {
		return $update;
	}
	return array(
		'theme'        => 'mea-orbit',
		'version'      => $manifest['version'],
		'new_version'  => $manifest['version'],
		'url'          => 'https://github.com/yohanagustian-del/wp-meagency',
		'package'      => $manifest['package'],
		'requires'     => $manifest['requires'],
		'requires_php' => $manifest['requires_php'],
	);
}
add_filter( 'update_themes_github.com', 'mea_orbit_update_check', 10, 3 );

/**
 * Unduh paket mea-orbit sendiri dan cocokkan SHA-256-nya dengan manifest.
 *
 * @param mixed  $reply   Nilai filter (false = biarkan WordPress mengunduh).
 * @param string $package URL paket.
 * @return mixed Path berkas sementara, WP_Error, atau $reply apa adanya.
 */
function mea_orbit_update_verify( $reply, $package ) {
	if ( false !== $reply ) {
		return $reply;
	}
	$manifest = mea_orbit_update_manifest();
	if ( ! $manifest || $package !== $manifest['package'] ) {
		return $reply;
	}
	$file = download_url( $package, 300 );
	if ( is_wp_error( $file ) ) {
		return $file;
	}
	if ( ! hash_equals( $manifest['sha256'], (string) hash_file( 'sha256', $file ) ) ) {
		wp_delete_file( $file );
		return new WP_Error( 'mea_orbit_update_hash', __( 'Paket tema tidak cocok dengan hash di manifest rilis; update dibatalkan.', 'mea-orbit' ) );
	}
	return $file;
}
add_filter( 'upgrader_pre_download', 'mea_orbit_update_verify', 10, 2 );
