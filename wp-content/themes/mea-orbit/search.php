<?php
/**
 * Hasil pencarian.
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
				/* translators: %s: kata kunci pencarian. */
				printf( esc_html__( 'Hasil pencarian: %s', 'mea-orbit' ), esc_html( get_search_query() ) );
				?>
			</h1>
			<?php get_search_form(); ?>
		</header>
		<?php get_template_part( 'template-parts/global/post-list' ); ?>
	</div>
</main>
<?php
get_footer();
