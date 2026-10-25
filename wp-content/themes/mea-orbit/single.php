<?php
/**
 * Artikel blog.
 *
 * Selama Elementor Pro aktif, artikel memakai template Single Post Theme
 * Builder (dengan header/footer mea-orbit). File ini dipakai saat template itu
 * nonaktif, dan menjadi template artikel setelah Fase 6.
 *
 * @package MEA_Orbit
 */

defined( 'ABSPATH' ) || exit;

get_header();

$mea_orbit_sales = mea_orbit_sales();

while ( have_posts() ) :
	the_post();
	$mea_orbit_categories = get_the_category();
	?>
	<main id="konten" <?php post_class( 'site-main' ); ?>>
		<article class="wrap">
			<header class="entry-head">
				<?php mea_orbit_breadcrumb( mea_orbit_breadcrumb_items() ); ?>
				<?php if ( $mea_orbit_categories ) : ?>
					<p class="entry-kicker"><a href="<?php echo esc_url( get_category_link( $mea_orbit_categories[0] ) ); ?>"><?php echo esc_html( $mea_orbit_categories[0]->name ); ?></a></p>
				<?php endif; ?>
				<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
				<p class="entry-meta"><?php mea_orbit_posted_on(); ?></p>
			</header>

			<?php if ( has_post_thumbnail() ) : ?>
				<figure class="entry-thumb">
					<?php
					the_post_thumbnail(
						'large',
						array(
							'fetchpriority' => 'high',
							'loading'       => 'eager',
						)
					);
					?>
				</figure>
			<?php endif; ?>

			<div class="prose">
				<?php
				the_content();
				wp_link_pages();
				?>
			</div>

			<footer class="entry-foot">
				<?php if ( has_tag() ) : ?>
					<p class="post-tags"><?php the_tags( '', '' ); ?></p>
				<?php endif; ?>

				<?php
				$mea_orbit_prev = get_previous_post();
				$mea_orbit_next = get_next_post();
				if ( $mea_orbit_prev || $mea_orbit_next ) :
					?>
					<nav class="post-nav" aria-label="<?php esc_attr_e( 'Artikel lain', 'mea-orbit' ); ?>">
						<div>
							<?php if ( $mea_orbit_prev ) : ?>
								<a class="prev" href="<?php echo esc_url( get_permalink( $mea_orbit_prev ) ); ?>"><span class="small"><?php esc_html_e( 'Sebelumnya', 'mea-orbit' ); ?></span><?php echo esc_html( get_the_title( $mea_orbit_prev ) ); ?></a>
							<?php endif; ?>
						</div>
						<div>
							<?php if ( $mea_orbit_next ) : ?>
								<a class="next" href="<?php echo esc_url( get_permalink( $mea_orbit_next ) ); ?>"><span class="small"><?php esc_html_e( 'Berikutnya', 'mea-orbit' ); ?></span><?php echo esc_html( get_the_title( $mea_orbit_next ) ); ?></a>
							<?php endif; ?>
						</div>
					</nav>
				<?php endif; ?>
			</footer>

			<aside class="entry-cta" aria-label="<?php esc_attr_e( 'Konsultasi', 'mea-orbit' ); ?>">
				<p class="display"><?php esc_html_e( 'Ceritakan kondisi toko Anda', 'mea-orbit' ); ?></p>
				<p><?php esc_html_e( 'Tim kami membaca data toko Anda sebelum menghubungi. Konsultasi pertama tidak dipungut biaya dan tidak mengikat.', 'mea-orbit' ); ?></p>
				<div class="btn-row">
					<a class="btn btn-primary" href="<?php echo esc_url( mea_orbit_consult_url() ); ?>"><?php esc_html_e( 'Jadwalkan konsultasi', 'mea-orbit' ); ?></a>
					<a class="btn btn-ghost" href="<?php echo esc_url( $mea_orbit_sales['wa'] ); ?>"><?php esc_html_e( 'Chat tim sales', 'mea-orbit' ); ?></a>
				</div>
			</aside>

			<?php
			if ( comments_open() || get_comments_number() ) {
				comments_template();
			}
			?>
		</article>
	</main>
	<?php
endwhile;

get_footer();
