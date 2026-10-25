<?php
/**
 * Dukungan tema, menu, dan ukuran konten.
 *
 * @package MEA_Orbit
 */

defined( 'ABSPATH' ) || exit;

/**
 * Daftarkan dukungan tema dan lokasi menu.
 *
 * @return void
 */
function mea_orbit_setup() {
	load_theme_textdomain( 'mea-orbit', MEA_ORBIT_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' )
	);
	add_theme_support( 'mea-leads' );

	register_nav_menus(
		array(
			'mea-orbit-utama'  => __( 'Menu utama (header)', 'mea-orbit' ),
			'mea-orbit-footer' => __( 'Menu footer (kolom Layanan)', 'mea-orbit' ),
		)
	);
}
add_action( 'after_setup_theme', 'mea_orbit_setup' );

/**
 * Lebar konten untuk embed dan gambar.
 *
 * @return void
 */
function mea_orbit_content_width() {
	$GLOBALS['content_width'] = 760;
}
add_action( 'after_setup_theme', 'mea_orbit_content_width', 0 );

/**
 * Kelas body tambahan untuk lapis Orbit dan konten Elementor.
 *
 * @param string[] $classes Kelas body.
 * @return string[]
 */
function mea_orbit_body_class( $classes ) {
	if ( mea_orbit_is_view() ) {
		$classes[] = 'mea-orbit-view';
	}
	if ( is_singular() && mea_orbit_is_elementor_content() ) {
		$classes[] = 'mea-elementor-content';
	}
	return $classes;
}
add_filter( 'body_class', 'mea_orbit_body_class' );
