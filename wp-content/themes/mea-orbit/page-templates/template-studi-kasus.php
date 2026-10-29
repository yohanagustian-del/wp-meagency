<?php
/**
 * Template Name: MEA Orbit · Studi kasus
 * Template Post Type: page
 *
 * Urutan section mengikuti prototipe docs/mea-orbit/studi-kasus.html.
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
		<article aria-labelledby="judul-kasus">
			<?php
			get_template_part( 'template-parts/studi-kasus/head' );
			get_template_part( 'template-parts/studi-kasus/hasil' );
			get_template_part( 'template-parts/studi-kasus/cerita' );
			get_template_part( 'template-parts/studi-kasus/galeri' );
			?>
		</article>
		<?php
		get_template_part( 'template-parts/studi-kasus/lain' );
		get_template_part( 'template-parts/global/konsultasi', null, array( 'sumber' => mea_orbit_form_source( 'studi-kasus-' ) ) );
	endif;
	?>
</main>
<?php
get_footer();
