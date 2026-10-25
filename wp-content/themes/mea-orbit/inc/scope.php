<?php
/**
 * Lapis Orbit: menentukan halaman mana yang memakai desain prototipe.
 *
 * Lapis umum (header, footer, template umum) berlaku di semua halaman.
 * Lapis Orbit (CSS section, motion, JSON-LD tambahan, tanpa aset Elementor)
 * hanya berlaku bila mea_orbit_is_view() bernilai true.
 *
 * @package MEA_Orbit
 */

defined( 'ABSPATH' ) || exit;

/**
 * Template halaman milik lapis Orbit.
 *
 * @return string[]
 */
function mea_orbit_template_slugs() {
	return array(
		'page-templates/template-layanan.php',
		'page-templates/template-studi-kasus.php',
	);
}

/**
 * Apakah request ini memakai lapis Orbit.
 *
 * Homepage statis memakai front-page.php. Halaman lain hanya bila memilih
 * salah satu template Orbit. Aman dipanggil setelah action `wp`.
 *
 * @return bool
 */
function mea_orbit_is_view() {
	if ( is_front_page() && 'page' === get_option( 'show_on_front' ) && file_exists( MEA_ORBIT_DIR . '/front-page.php' ) ) {
		return true;
	}

	if ( is_page() ) {
		return in_array( get_page_template_slug( get_queried_object_id() ), mea_orbit_template_slugs(), true );
	}

	return false;
}

/**
 * Apakah konten posting/halaman ini dibangun dengan Elementor.
 *
 * Membaca meta langsung supaya tetap benar saat plugin Elementor nonaktif.
 *
 * @param int $post_id ID posting. 0 = posting saat ini.
 * @return bool
 */
function mea_orbit_is_elementor_content( $post_id = 0 ) {
	$post_id = $post_id ? $post_id : get_the_ID();

	return $post_id && 'builder' === get_post_meta( $post_id, '_elementor_edit_mode', true );
}
