<?php
/**
 * Daftar artikel + paginasi. Dipakai index.php, archive.php, search.php.
 *
 * @package MEA_Orbit
 */

defined( 'ABSPATH' ) || exit;

if ( have_posts() ) :
	?>
	<ul class="post-list">
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<li <?php post_class( 'post-card' ); ?>>
				<?php if ( has_post_thumbnail() ) : ?>
					<a class="thumb" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true"><?php the_post_thumbnail( 'medium_large' ); ?></a>
				<?php endif; ?>
				<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
				<p class="meta"><?php mea_orbit_posted_on(); ?></p>
				<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 24 ) ); ?></p>
			</li>
		<?php endwhile; ?>
	</ul>
	<?php
	the_posts_pagination(
		array(
			'mid_size'           => 1,
			'prev_text'          => __( 'Sebelumnya', 'mea-orbit' ),
			'next_text'          => __( 'Berikutnya', 'mea-orbit' ),
			'screen_reader_text' => __( 'Navigasi halaman', 'mea-orbit' ),
			'aria_label'         => __( 'Halaman artikel', 'mea-orbit' ),
		)
	);
else :
	?>
	<div class="empty-state">
		<p><?php echo is_search() ? esc_html__( 'Tidak ada artikel yang cocok dengan kata kunci itu. Coba kata kunci lain.', 'mea-orbit' ) : esc_html__( 'Belum ada artikel di sini.', 'mea-orbit' ); ?></p>
		<?php get_search_form(); ?>
	</div>
	<?php
endif;
