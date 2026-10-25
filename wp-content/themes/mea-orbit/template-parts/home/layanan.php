<?php
/**
 * Homepage · enam layanan (enam sisi kubus). Tile M2 per kartu: poster sekarang,
 * loop video dipasang di Fase 4.
 *
 * @package MEA_Orbit
 */

defined( 'ABSPATH' ) || exit;

$mea_orbit_services = (array) mea_orbit_home( 'layanan' );
if ( ! $mea_orbit_services ) {
	return;
}
?>
<section class="sec paper" id="layanan" aria-labelledby="judul-layanan">
	<div class="wrap">
		<div class="sec-head">
			<h2 id="judul-layanan"><?php esc_html_e( 'Enam sisi operasional, satu tim', 'mea-orbit' ); ?></h2>
			<p class="lede"><?php esc_html_e( 'Pilih satu bagian yang paling menahan pertumbuhan toko Anda, atau serahkan semuanya.', 'mea-orbit' ); ?></p>
		</div>
		<ul class="svc">
			<?php foreach ( $mea_orbit_services as $mea_orbit_service ) : ?>
				<li><a href="<?php echo esc_url( mea_orbit_url( $mea_orbit_service['url'] ) ); ?>">
					<?php if ( '' !== $mea_orbit_service['motion'] ) : ?>
						<span class="svc-tile" data-motion="sisi" data-loop="<?php echo esc_attr( $mea_orbit_service['motion'] ); ?>" aria-hidden="true"><img src="<?php echo esc_url( mea_orbit_motion_url( 'sisi/' . $mea_orbit_service['motion'] . '.webp' ) ); ?>" width="480" height="480" alt="" loading="lazy" decoding="async"></span>
					<?php endif; ?>
					<span class="display"><?php echo esc_html( $mea_orbit_service['nama'] ); ?></span><span><?php echo esc_html( $mea_orbit_service['deskripsi'] ); ?></span>
				</a></li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
