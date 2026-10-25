<?php
/**
 * Daftar artikel (halaman Blog) dan cadangan terakhir hierarki template.
 *
 * @package MEA_Orbit
 */

defined( 'ABSPATH' ) || exit;

get_header();

$mea_orbit_blog_id = (int) get_option( 'page_for_posts' );
?>
<main id="konten" class="site-main">
	<div class="wrap">
		<header class="archive-head">
			<?php mea_orbit_breadcrumb( mea_orbit_breadcrumb_items() ); ?>
			<h1 class="entry-title"><?php echo esc_html( is_home() && $mea_orbit_blog_id ? get_the_title( $mea_orbit_blog_id ) : __( 'Blog', 'mea-orbit' ) ); ?></h1>
		</header>
		<?php get_template_part( 'template-parts/global/post-list' ); ?>
	</div>
</main>
<?php
get_footer();
