<?php
/**
 * Layanan · hero: H1, lede, CTA, foto tim (gambar LCP).
 *
 * @package MEA_Orbit
 */

defined( 'ABSPATH' ) || exit;

$mea_orbit_h1    = (string) mea_orbit_meta( 'h1' );
$mea_orbit_photo = mea_orbit_img(
	(int) mea_orbit_meta( 'foto_hero_id' ),
	'large',
	array(
		'alt'     => (string) mea_orbit_meta( 'foto_hero_alt' ),
		'loading' => 'eager',
		'sizes'   => '(max-width: 900px) 100vw, 520px',
	)
);
?>
<section class="sec svc-hero" aria-labelledby="judul-layanan">
	<div class="wrap svc-hero-grid">
		<div class="sec-head" style="margin:0">
			<?php if ( '' !== (string) mea_orbit_meta( 'label_hero' ) ) : ?>
				<p class="small"><?php echo esc_html( mea_orbit_meta( 'label_hero' ) ); ?></p>
			<?php endif; ?>
			<h1 id="judul-layanan"><?php echo esc_html( '' !== $mea_orbit_h1 ? $mea_orbit_h1 : get_the_title() ); ?></h1>
			<?php if ( '' !== (string) mea_orbit_meta( 'lede' ) ) : ?>
				<p class="lede"><?php echo esc_html( mea_orbit_meta( 'lede' ) ); ?></p>
			<?php endif; ?>
			<div class="btn-row"><a class="btn btn-primary" href="#konsultasi"><?php esc_html_e( 'Jadwalkan konsultasi', 'mea-orbit' ); ?></a>
				<?php if ( mea_orbit_meta( 'langkah' ) ) : ?>
					<a class="btn btn-ghost" href="#cara-kerja"><?php esc_html_e( 'Lihat cara kerja', 'mea-orbit' ); ?></a>
				<?php elseif ( mea_orbit_meta( 'hub_layanan' ) ) : ?>
					<a class="btn btn-ghost" href="#semua-layanan"><?php esc_html_e( 'Lihat semua layanan', 'mea-orbit' ); ?></a>
				<?php endif; ?>
			</div>
		</div>
		<?php if ( $mea_orbit_photo ) : ?>
			<figure class="photo svc-photo"><?php echo $mea_orbit_photo; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- dari wp_get_attachment_image(). ?></figure>
		<?php endif; ?>
	</div>
</section>
