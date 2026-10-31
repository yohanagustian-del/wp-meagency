<?php
/**
 * Studi kasus · kepala halaman daftar (/study-case/): label, H1, lede.
 *
 * @package MEA_Orbit
 */

defined( 'ABSPATH' ) || exit;

$mea_orbit_h1    = (string) mea_orbit_meta( 'h1' );
$mea_orbit_label = (string) mea_orbit_meta( 'label_hero' );
$mea_orbit_lede  = (string) mea_orbit_meta( 'lede' );
?>
<section class="sec svc-hero" aria-labelledby="judul-kasus">
	<div class="wrap">
		<div class="sec-head" style="margin:0">
			<?php if ( '' !== $mea_orbit_label ) : ?>
				<p class="small"><?php echo esc_html( $mea_orbit_label ); ?></p>
			<?php endif; ?>
			<h1 id="judul-kasus"><?php echo esc_html( '' !== $mea_orbit_h1 ? $mea_orbit_h1 : get_the_title() ); ?></h1>
			<?php if ( '' !== $mea_orbit_lede ) : ?>
				<p class="lede"><?php echo esc_html( $mea_orbit_lede ); ?></p>
			<?php endif; ?>
			<div class="btn-row"><a class="btn btn-primary" href="#konsultasi"><?php esc_html_e( 'Jadwalkan konsultasi', 'mea-orbit' ); ?></a></div>
		</div>
	</div>
</section>
