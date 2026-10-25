<?php
/**
 * Halaman tidak ditemukan.
 *
 * @package MEA_Orbit
 */

defined( 'ABSPATH' ) || exit;

get_header();

$mea_orbit_blog_id = (int) get_option( 'page_for_posts' );
?>
<main id="konten" class="site-main">
	<div class="wrap not-found">
		<h1 class="entry-title"><?php esc_html_e( 'Halaman tidak ditemukan', 'mea-orbit' ); ?></h1>
		<p><?php esc_html_e( 'Alamat yang Anda buka tidak ada atau sudah dipindah. Cari artikel, atau lanjutkan dari halaman berikut.', 'mea-orbit' ); ?></p>
		<?php get_search_form(); ?>
		<div class="btn-row">
			<a class="btn btn-primary" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Ke beranda', 'mea-orbit' ); ?></a>
			<a class="btn btn-ghost" href="<?php echo esc_url( home_url( '/#layanan' ) ); ?>"><?php esc_html_e( 'Lihat layanan', 'mea-orbit' ); ?></a>
			<a class="btn btn-ghost" href="<?php echo esc_url( $mea_orbit_blog_id ? get_permalink( $mea_orbit_blog_id ) : home_url( '/blog/' ) ); ?>"><?php esc_html_e( 'Baca blog', 'mea-orbit' ); ?></a>
		</div>
	</div>
</main>
<?php
get_footer();
