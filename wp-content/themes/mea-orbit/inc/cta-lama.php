<?php
/**
 * CTA lama di luar field isi Orbit (menggantikan Code Snippet #14 "MEA CTA lama -> form
 * konsultasi Orbit", dipasang 1 Nov 2026 atas persetujuan Yohan).
 *
 * 1. URL `/klik/*` (CTA artikel dan halaman lama) dialihkan 302 ke form konsultasi homepage.
 * 2. Link `/klik/*`, `meagency.co.id/klik/*`, `sebari.co.id/form/*` di konten artikel,
 *    hasil render Elementor, dan widget teks diarahkan ke form konsultasi
 *    (`mea_orbit_consult_url()`); target/rel dibuang. Field isi template Orbit diurus
 *    `mea_orbit_isi_html()`.
 *
 * @package MEA_Orbit
 */

defined( 'ABSPATH' ) || exit;

/**
 * Path permintaan termasuk CTA lama `/klik` atau `/klik/*`?
 *
 * @param string $uri REQUEST_URI.
 * @return bool
 */
function mea_orbit_cta_lama_path( $uri ) {
	$path = (string) wp_parse_url( (string) $uri, PHP_URL_PATH );
	return 0 === strpos( $path, '/klik/' ) || '/klik' === rtrim( $path, '/' );
}

add_action(
	'init',
	static function () {
		if ( is_admin() || wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			return;
		}
		$uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- hanya path yang dibaca.
		if ( mea_orbit_cta_lama_path( $uri ) ) {
			wp_safe_redirect( home_url( '/#konsultasi' ), 302 );
			exit;
		}
	},
	0
);

/**
 * Filter konten: link CTA lama diarahkan ke form konsultasi.
 *
 * @param mixed $html Konten.
 * @return mixed
 */
function mea_orbit_cta_lama_konten( $html ) {
	if ( ! is_string( $html ) || '' === $html || isset( $_GET['elementor-preview'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- hanya cek mode pratinjau.
		return $html;
	}
	return mea_orbit_isi_cta_lama( $html, mea_orbit_consult_url() );
}
add_filter( 'the_content', 'mea_orbit_cta_lama_konten', PHP_INT_MAX );
add_filter( 'elementor/frontend/the_content', 'mea_orbit_cta_lama_konten', PHP_INT_MAX );
add_filter( 'widget_text', 'mea_orbit_cta_lama_konten', PHP_INT_MAX );
