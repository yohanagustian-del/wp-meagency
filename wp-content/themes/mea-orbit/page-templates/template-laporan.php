<?php
/**
 * Template Name: Laporan HTML
 *
 * Laporan klien: dokumen HTML dicetak apa adanya, satu halaman penuh tanpa header/footer
 * situs dan tanpa Elementor. Juga dipakai otomatis untuk laporan Elementor lama
 * (lihat inc/laporan.php). Halaman berpassword menampilkan form password tema dulu.
 *
 * @package MEA_Orbit
 */

defined( 'ABSPATH' ) || exit;

$mea_orbit_post = get_queried_object();

if ( post_password_required( $mea_orbit_post ) ) {
	get_header();
	?>
	<main id="konten" class="site-main">
		<div class="wrap">
			<?php echo get_the_password_form( $mea_orbit_post ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- form password bawaan WordPress. ?>
		</div>
	</main>
	<?php
	get_footer();
	return;
}

$mea_orbit_html = mea_orbit_laporan_html( $mea_orbit_post );

// Template dipilih tapi laporan belum diisi: tampil sebagai halaman biasa.
if ( '' === $mea_orbit_html ) {
	require MEA_ORBIT_DIR . '/page.php';
	return;
}

if ( ! headers_sent() ) {
	header( 'X-Robots-Tag: noindex, nofollow', true );
}

echo mea_orbit_laporan_document( $mea_orbit_html, get_the_title( $mea_orbit_post ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- laporan disimpan oleh pengguna dengan unfiltered_html (field) atau editor Elementor; dicetak utuh seperti widget HTML Elementor.
