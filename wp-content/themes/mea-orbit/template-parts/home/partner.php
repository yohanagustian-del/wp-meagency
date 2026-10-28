<?php
/**
 * Homepage · strip "Partner resmi" di bawah hero (brief bagian 10).
 *
 * Isi dari opsi Homepage `partner_resmi`, diganti tiap kuartal lewat Media Library.
 * Gambar tampil utuh tanpa filter: gambar berlatar transparan (badge PNG/WebP) apa
 * adanya, gambar tanpa transparansi (foto plakat) dalam kartu berbingkai tipis.
 * Kosong = tidak tampil.
 *
 * @package MEA_Orbit
 */

defined( 'ABSPATH' ) || exit;

$mea_orbit_partners = array();
foreach ( (array) mea_orbit_home( 'partner_resmi' ) as $mea_orbit_partner ) {
	$mea_orbit_id  = (int) ( $mea_orbit_partner['media_id'] ?? 0 );
	$mea_orbit_img = mea_orbit_img(
		$mea_orbit_id,
		'medium_large',
		array(
			'alt'   => (string) ( $mea_orbit_partner['alt'] ?? '' ),
			'sizes' => '240px',
		)
	);
	if ( '' === $mea_orbit_img ) {
		continue;
	}
	$mea_orbit_partners[] = array(
		'img'     => $mea_orbit_img,
		'bingkai' => ! mea_orbit_image_has_alpha( $mea_orbit_id ),
		'label'   => (string) ( $mea_orbit_partner['label'] ?? '' ),
		'periode' => (string) ( $mea_orbit_partner['periode'] ?? '' ),
		'link'    => mea_orbit_url( (string) ( $mea_orbit_partner['link'] ?? '' ) ),
	);
}
if ( ! $mea_orbit_partners ) {
	return;
}
?>
<section class="partners paper" aria-labelledby="judul-partner">
	<div class="wrap">
		<h2 class="partners-title" id="judul-partner"><?php esc_html_e( 'Partner resmi', 'mea-orbit' ); ?></h2>
		<ul class="partners-list">
			<?php foreach ( $mea_orbit_partners as $mea_orbit_partner ) : ?>
				<li class="partner">
					<?php if ( '' !== $mea_orbit_partner['link'] ) : ?>
						<a class="partner-inner" href="<?php echo esc_url( $mea_orbit_partner['link'] ); ?>">
					<?php else : ?>
						<div class="partner-inner">
					<?php endif; ?>
					<div class="partner-media<?php echo $mea_orbit_partner['bingkai'] ? ' is-framed' : ''; ?>"><?php echo $mea_orbit_partner['img']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- dari wp_get_attachment_image(). ?></div>
					<?php if ( '' !== $mea_orbit_partner['label'] ) : ?>
						<p class="partner-label"><?php echo esc_html( $mea_orbit_partner['label'] ); ?></p>
					<?php endif; ?>
					<?php if ( '' !== $mea_orbit_partner['periode'] ) : ?>
						<p class="small partner-period"><?php echo esc_html( $mea_orbit_partner['periode'] ); ?></p>
					<?php endif; ?>
					<?php echo '' !== $mea_orbit_partner['link'] ? '</a>' : '</div>'; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
