<?php
/**
 * Layanan · cocok / belum cocok.
 *
 * @package MEA_Orbit
 */

defined( 'ABSPATH' ) || exit;

$mea_orbit_yes = (array) mea_orbit_meta( 'cocok' );
$mea_orbit_no  = (array) mea_orbit_meta( 'belum_cocok' );
if ( ! $mea_orbit_yes && ! $mea_orbit_no ) {
	return;
}
?>
<section class="sec paper" aria-labelledby="judul-cocok">
	<div class="wrap">
		<div class="sec-head"><h2 id="judul-cocok"><?php esc_html_e( 'Apakah layanan ini cocok untuk Anda', 'mea-orbit' ); ?></h2></div>
		<div class="fit">
			<?php if ( $mea_orbit_yes ) : ?>
				<div><h3><?php esc_html_e( 'Cocok jika', 'mea-orbit' ); ?></h3><ul class="ticks">
					<?php foreach ( $mea_orbit_yes as $mea_orbit_item ) : ?>
						<li><?php echo esc_html( $mea_orbit_item ); ?></li>
					<?php endforeach; ?>
				</ul></div>
			<?php endif; ?>
			<?php if ( $mea_orbit_no ) : ?>
				<div><h3><?php esc_html_e( 'Belum cocok jika', 'mea-orbit' ); ?></h3><ul class="ticks no">
					<?php foreach ( $mea_orbit_no as $mea_orbit_item ) : ?>
						<li><?php echo esc_html( $mea_orbit_item ); ?></li>
					<?php endforeach; ?>
				</ul></div>
			<?php endif; ?>
		</div>
	</div>
</section>
