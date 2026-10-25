<?php
/**
 * Studi kasus · cerita: Tantangannya, Yang kami kerjakan, Hasilnya, kutipan,
 * dan foto proses kerja tim di samping.
 *
 * @package MEA_Orbit
 */

defined( 'ABSPATH' ) || exit;

$mea_orbit_parts  = array(
	'tantangan'  => __( 'Tantangannya', 'mea-orbit' ),
	'dikerjakan' => __( 'Yang kami kerjakan', 'mea-orbit' ),
	'hasil'      => __( 'Hasilnya', 'mea-orbit' ),
);
$mea_orbit_photos = (array) mea_orbit_meta( 'foto_proses' );
?>
<section class="sec">
	<div class="wrap story">
		<div class="story-body">
			<?php
			foreach ( $mea_orbit_parts as $mea_orbit_key => $mea_orbit_heading ) :
				$mea_orbit_html = (string) mea_orbit_meta( $mea_orbit_key );
				if ( '' === trim( $mea_orbit_html ) ) {
					continue;
				}
				?>
				<h2 id="bab-<?php echo esc_attr( $mea_orbit_key ); ?>"><?php echo esc_html( $mea_orbit_heading ); ?></h2>
				<?php echo wp_kses( wpautop( $mea_orbit_html ), mea_orbit_allowed_html() ); ?>
			<?php endforeach; ?>
			<?php get_template_part( 'template-parts/global/kutipan', null, array( 'id' => (string) mea_orbit_meta( 'kutipan_id' ) ) ); ?>
		</div>
		<?php if ( $mea_orbit_photos ) : ?>
			<aside class="story-side">
				<?php
				foreach ( $mea_orbit_photos as $mea_orbit_photo ) :
					$mea_orbit_img = mea_orbit_img(
						(int) $mea_orbit_photo['media_id'],
						'large',
						array(
							'alt'   => $mea_orbit_photo['alt'],
							'sizes' => '(max-width: 900px) 100vw, 360px',
						)
					);
					if ( ! $mea_orbit_img ) {
						continue;
					}
					?>
					<figure class="photo" style="aspect-ratio:4/5" data-reveal><?php echo $mea_orbit_img; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- dari wp_get_attachment_image(). ?>
						<?php if ( '' !== $mea_orbit_photo['keterangan'] ) : ?>
							<figcaption><?php echo esc_html( $mea_orbit_photo['keterangan'] ); ?></figcaption>
						<?php endif; ?>
					</figure>
				<?php endforeach; ?>
			</aside>
		<?php endif; ?>
	</div>
</section>
