<?php
/**
 * Layanan · yang dikerjakan tim kami (deliverables).
 *
 * @package MEA_Orbit
 */

defined( 'ABSPATH' ) || exit;

$mea_orbit_items = (array) mea_orbit_meta( 'deliverables' );
if ( ! $mea_orbit_items ) {
	return;
}
$mea_orbit_lede = (string) mea_orbit_meta( 'dikerjakan_lede' );
?>
<section class="sec" aria-labelledby="judul-dikerjakan">
	<div class="wrap">
		<div class="sec-head"><h2 id="judul-dikerjakan"><?php esc_html_e( 'Yang dikerjakan tim kami', 'mea-orbit' ); ?></h2>
			<?php if ( '' !== $mea_orbit_lede ) : ?>
				<p class="lede"><?php echo esc_html( $mea_orbit_lede ); ?></p>
			<?php endif; ?>
		</div>
		<ul class="deliver">
			<?php foreach ( $mea_orbit_items as $mea_orbit_item ) : ?>
				<li><h3><?php echo esc_html( $mea_orbit_item['judul'] ); ?></h3><p><?php echo esc_html( $mea_orbit_item['deskripsi'] ); ?></p></li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
