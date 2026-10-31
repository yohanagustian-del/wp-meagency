<?php
/**
 * Layanan · hub semua layanan (halaman /services/). Empat pilar dengan anaknya
 * (termasuk anak khusus footer/hub) dan layanan MEA Agency lain, dari sumber yang sama
 * dengan header dan footer. Tampil bila field "hub_layanan" aktif.
 *
 * @package MEA_Orbit
 */

defined( 'ABSPATH' ) || exit;

if ( ! mea_orbit_meta( 'hub_layanan' ) ) {
	return;
}
$mea_orbit_groups = mea_orbit_pilar();
$mea_orbit_groups[] = array( __( 'Layanan lain', 'mea-orbit' ), '', mea_orbit_layanan_lain() );
?>
<section class="sec" id="semua-layanan" aria-labelledby="judul-hub">
	<div class="wrap">
		<div class="sec-head"><h2 id="judul-hub"><?php esc_html_e( 'Semua layanan MEA Agency', 'mea-orbit' ); ?></h2></div>
		<ul class="hub" data-hub>
			<?php foreach ( $mea_orbit_groups as $mea_orbit_i => $mea_orbit_group ) : ?>
				<li class="hub-pilar" data-hub-pilar="<?php echo (int) $mea_orbit_i; ?>">
					<h3>
						<?php if ( '' !== $mea_orbit_group[1] ) : ?>
							<a href="<?php echo esc_url( home_url( $mea_orbit_group[1] ) ); ?>"><?php echo esc_html( $mea_orbit_group[0] ); ?></a>
						<?php else : ?>
							<?php echo esc_html( $mea_orbit_group[0] ); ?>
						<?php endif; ?>
					</h3>
					<?php if ( $mea_orbit_group[2] ) : ?>
						<ul>
							<?php foreach ( $mea_orbit_group[2] as $mea_orbit_anak ) : ?>
								<li><a href="<?php echo esc_url( home_url( $mea_orbit_anak[1] ) ); ?>"><?php echo esc_html( $mea_orbit_anak[0] ); ?></a></li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
