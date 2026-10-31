<?php
/**
 * Template Name: MEA Orbit · Layanan
 * Template Post Type: page
 *
 * Urutan section mengikuti prototipe docs/mea-orbit/layanan.html.
 *
 * @package MEA_Orbit
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="konten">
	<?php
	if ( post_password_required() ) :
		echo '<div class="wrap">' . get_the_password_form() . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- form password bawaan WordPress.
	else :
		?>
		<div class="wrap"><?php mea_orbit_breadcrumb( mea_orbit_orbit_breadcrumb() ); ?></div>
		<?php
		get_template_part( 'template-parts/layanan/hero' );
		get_template_part( 'template-parts/layanan/motion' );
		get_template_part( 'template-parts/layanan/hub' );
		get_template_part( 'template-parts/global/isi' );
		get_template_part( 'template-parts/layanan/cocok' );
		get_template_part( 'template-parts/layanan/dikerjakan' );
		get_template_part( 'template-parts/layanan/cara-kerja' );
		get_template_part( 'template-parts/layanan/bukti' );
		get_template_part( 'template-parts/layanan/faq' );
		get_template_part( 'template-parts/layanan/terkait' );
		get_template_part( 'template-parts/global/konsultasi', null, array( 'sumber' => mea_orbit_form_source( 'layanan-' ) ) );
	endif;
	?>
</main>
<?php
get_footer();
