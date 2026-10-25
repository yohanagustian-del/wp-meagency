<?php
/**
 * CSS, JS, dan preload font.
 *
 * Sitewide: base.css. Template umum: content.css (+ elementor-compat.css untuk
 * konten Elementor). Lapis Orbit: orbit.css dan motion.js (Fase 2 dan 4), tanpa
 * aset Elementor atau addon-nya.
 *
 * @package MEA_Orbit
 */

defined( 'ABSPATH' ) || exit;

/**
 * Versi aset dari waktu ubah file, supaya cache pecah setiap kali file berubah.
 *
 * @param string $relative Path relatif dari folder tema.
 * @return string
 */
function mea_orbit_asset_version( $relative ) {
	$file = MEA_ORBIT_DIR . '/' . ltrim( $relative, '/' );
	return file_exists( $file ) ? (string) filemtime( $file ) : MEA_ORBIT_VERSION;
}

/**
 * Daftarkan dan muat stylesheet.
 *
 * @return void
 */
function mea_orbit_enqueue_assets() {
	wp_enqueue_style( 'mea-orbit-base', MEA_ORBIT_URI . '/assets/css/base.css', array(), mea_orbit_asset_version( 'assets/css/base.css' ) );

	if ( mea_orbit_is_view() ) {
		return;
	}

	wp_enqueue_style( 'mea-orbit-content', MEA_ORBIT_URI . '/assets/css/content.css', array( 'mea-orbit-base' ), mea_orbit_asset_version( 'assets/css/content.css' ) );

	if ( did_action( 'elementor/loaded' ) || ( is_singular() && mea_orbit_is_elementor_content() ) ) {
		wp_enqueue_style( 'mea-orbit-elementor-compat', MEA_ORBIT_URI . '/assets/css/elementor-compat.css', array( 'mea-orbit-content' ), mea_orbit_asset_version( 'assets/css/elementor-compat.css' ) );
	}

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'mea_orbit_enqueue_assets' );

/**
 * Preload font Inter (satu file variable untuk bobot 400–700).
 *
 * @return void
 */
function mea_orbit_preload_fonts() {
	printf(
		'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
		esc_url( MEA_ORBIT_URI . '/assets/fonts/inter-latin-var.woff2' )
	);
}
add_action( 'wp_head', 'mea_orbit_preload_fonts', 1 );

/**
 * Pola handle aset yang tidak dimuat di halaman Orbit.
 *
 * @return string
 */
function mea_orbit_foreign_handle_pattern() {
	return '/^(elementor|e-|swiper|font-awesome|elementskit|ekit|unlimited|ue[_-]|uc[_-]|hello-elementor|wp-block-library|classic-theme-styles|global-styles)/';
}

/**
 * Lepas aset Elementor, addon, dan blok bawaan dari halaman Orbit.
 *
 * Dijalankan paling akhir supaya aset yang didaftarkan plugin sudah masuk antrean.
 *
 * @return void
 */
function mea_orbit_dequeue_foreign_assets() {
	if ( ! mea_orbit_is_view() ) {
		return;
	}

	$pattern = mea_orbit_foreign_handle_pattern();

	foreach ( wp_styles()->queue as $handle ) {
		if ( preg_match( $pattern, $handle ) ) {
			wp_dequeue_style( $handle );
		}
	}
	foreach ( wp_scripts()->queue as $handle ) {
		if ( preg_match( $pattern, $handle ) ) {
			wp_dequeue_script( $handle );
		}
	}
}
add_action( 'wp_enqueue_scripts', 'mea_orbit_dequeue_foreign_assets', PHP_INT_MAX );
add_action( 'wp_print_styles', 'mea_orbit_dequeue_foreign_assets', PHP_INT_MAX );
add_action( 'wp_print_footer_scripts', 'mea_orbit_dequeue_foreign_assets', 1 );
