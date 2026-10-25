<?php
/**
 * Kompatibilitas Elementor selama transisi. Dihapus di akhir Fase 6.
 *
 * - Header dan footer selalu milik mea-orbit: lokasi Theme Builder didaftarkan
 *   supaya Elementor Pro tidak menyisipkan header/footernya sendiri, dan tema
 *   tidak pernah memanggil lokasi `header` atau `footer`.
 * - Template Single dan Archive Elementor Pro tetap berjalan seperti sekarang
 *   (dengan header/footer mea-orbit) sampai dinonaktifkan di Fase 6.
 * - Halaman Orbit selalu memakai template tema, tidak bisa ditimpa Theme Builder.
 *
 * Tema tetap berjalan normal saat Elementor nonaktif.
 *
 * @package MEA_Orbit
 */

defined( 'ABSPATH' ) || exit;

/**
 * Daftarkan semua lokasi inti Theme Builder.
 *
 * @param object $manager Locations manager Elementor Pro.
 * @return void
 */
function mea_orbit_elementor_locations( $manager ) {
	if ( is_object( $manager ) && method_exists( $manager, 'register_all_core_location' ) ) {
		$manager->register_all_core_location();
	}
}
add_action( 'elementor/theme/register_locations', 'mea_orbit_elementor_locations' );

/**
 * Halaman Orbit tidak boleh ditimpa template Theme Builder.
 *
 * @param string $template Path template yang dipilih.
 * @return string
 */
function mea_orbit_keep_orbit_template( $template ) {
	if ( ! mea_orbit_is_view() ) {
		return $template;
	}

	if ( is_front_page() ) {
		return MEA_ORBIT_DIR . '/front-page.php';
	}

	$slug = get_page_template_slug( get_queried_object_id() );
	$file = MEA_ORBIT_DIR . '/' . $slug;

	return file_exists( $file ) ? $file : $template;
}
add_filter( 'template_include', 'mea_orbit_keep_orbit_template', 99 );

/**
 * Elementor tidak memuat Google Fonts di halaman Orbit (font sudah di-host tema).
 *
 * @param bool $should_print Nilai bawaan.
 * @return bool
 */
function mea_orbit_elementor_google_fonts( $should_print ) {
	return mea_orbit_is_view() ? false : $should_print;
}
add_filter( 'elementor/frontend/print_google_fonts', 'mea_orbit_elementor_google_fonts' );
