<?php
/**
 * Homepage · studi kasus unggulan.
 *
 * Visual: Kurva Bellarosa (M4) bila studi kasus unggulan adalah Bellarosa dan izin
 * publikasinya aktif. Studi kasus lain memakai foto proses kerja pertamanya.
 *
 * @package MEA_Orbit
 */

defined( 'ABSPATH' ) || exit;

$mea_orbit_case_id = (int) mea_orbit_home( 'kasus_unggulan_id' );
if ( ! $mea_orbit_case_id || 'publish' !== get_post_status( $mea_orbit_case_id ) ) {
	return;
}

$mea_orbit_title = (string) mea_orbit_meta( 'kartu_judul', $mea_orbit_case_id );
$mea_orbit_lede  = (string) mea_orbit_meta( 'kartu_lede', $mea_orbit_case_id );
$mea_orbit_m4    = 'bellarosa' === get_post_field( 'post_name', $mea_orbit_case_id ) && mea_orbit_case_published( $mea_orbit_case_id );
$mea_orbit_photo = (array) mea_orbit_meta( 'foto_proses', $mea_orbit_case_id );
?>
<section class="sec paper" aria-labelledby="judul-kasus">
	<div class="wrap case-feature">
		<?php if ( $mea_orbit_m4 ) : ?>
			<figure class="case-visual" data-motion="bellarosa">
				<picture>
					<source media="(max-width: 767px)" srcset="<?php echo esc_url( mea_orbit_motion_url( 'bellarosa/bellarosa-case-mobile-900x900-poster.webp' ) ); ?>" width="900" height="900">
					<img src="<?php echo esc_url( mea_orbit_motion_url( 'bellarosa/bellarosa-case-desktop-1200x900-poster.webp' ) ); ?>" width="1200" height="900" loading="lazy" decoding="async" alt="<?php esc_attr_e( 'Grafik GMV Bellarosa yang naik ke Rp 8,2 miliar: 75% dari organik dan affiliate, 25% dari iklan, ROAS 21x', 'mea-orbit' ); ?>">
				</picture>
			</figure>
		<?php elseif ( $mea_orbit_photo ) : ?>
			<figure class="photo" data-reveal>
				<?php
				echo mea_orbit_img( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- dari wp_get_attachment_image().
					(int) $mea_orbit_photo[0]['media_id'],
					'large',
					array(
						'alt'   => $mea_orbit_photo[0]['alt'],
						'sizes' => '(max-width: 900px) 100vw, 560px',
					)
				);
				?>
			</figure>
		<?php endif; ?>
		<div class="sec-head" style="margin:0">
			<p class="small"><?php esc_html_e( 'Studi kasus', 'mea-orbit' ); ?></p>
			<h2 id="judul-kasus"><?php echo esc_html( '' !== $mea_orbit_title ? $mea_orbit_title : get_the_title( $mea_orbit_case_id ) ); ?></h2>
			<?php if ( '' !== $mea_orbit_lede ) : ?>
				<p class="lede"><?php echo esc_html( $mea_orbit_lede ); ?></p>
			<?php endif; ?>
			<div class="btn-row"><a class="btn btn-ghost" href="<?php echo esc_url( get_permalink( $mea_orbit_case_id ) ); ?>"><?php esc_html_e( 'Baca studi kasus', 'mea-orbit' ); ?></a></div>
		</div>
	</div>
</section>
