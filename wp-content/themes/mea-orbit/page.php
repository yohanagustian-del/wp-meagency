<?php
/**
 * Halaman umum.
 *
 * Halaman yang dibangun Elementor: lebar penuh, tanpa gaya prosa, judul H1
 * mengikuti pengaturan "Hide Title" seperti Hello Elementor. Halaman klasik:
 * kolom baca dengan gaya prosa.
 *
 * @package MEA_Orbit
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$mea_orbit_elementor = mea_orbit_is_elementor_content();
	?>
	<main id="konten" <?php post_class( $mea_orbit_elementor ? 'site-main is-elementor' : 'site-main' ); ?>>
		<?php if ( $mea_orbit_elementor ) : ?>
			<?php if ( mea_orbit_show_title() ) : ?>
				<div class="wrap entry-head">
					<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
				</div>
			<?php endif; ?>
			<?php the_content(); ?>
		<?php else : ?>
			<div class="wrap">
				<?php if ( mea_orbit_show_title() ) : ?>
					<header class="entry-head">
						<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
					</header>
				<?php endif; ?>
				<div class="prose page-body">
					<?php
					the_content();
					wp_link_pages();
					?>
				</div>
				<?php
				if ( comments_open() || get_comments_number() ) {
					comments_template();
				}
				?>
			</div>
		<?php endif; ?>
	</main>
	<?php
endwhile;

get_footer();
