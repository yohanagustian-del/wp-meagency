<?php
/**
 * Arsip: kategori, tag, penulis, tanggal.
 *
 * Selama Elementor Pro aktif, arsip memakai template Blog Archive Theme
 * Builder. File ini dipakai saat template itu nonaktif.
 *
 * @package MEA_Orbit
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="konten" class="site-main">
	<div class="wrap">
		<header class="archive-head">
			<?php mea_orbit_breadcrumb( mea_orbit_breadcrumb_items() ); ?>
			<h1 class="entry-title">
				<?php
				if ( is_category() || is_tag() || is_tax() ) {
					single_term_title();
				} else {
					echo esc_html( wp_strip_all_tags( get_the_archive_title() ) );
				}
				?>
			</h1>
			<?php
			$mea_orbit_desc = get_the_archive_description();
			if ( $mea_orbit_desc ) :
				?>
				<div class="archive-desc"><?php echo wp_kses_post( $mea_orbit_desc ); ?></div>
			<?php endif; ?>
		</header>
		<?php get_template_part( 'template-parts/global/post-list' ); ?>
	</div>
</main>
<?php
get_footer();
