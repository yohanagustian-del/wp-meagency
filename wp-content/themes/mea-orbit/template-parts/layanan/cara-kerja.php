<?php
/**
 * Layanan · cara kerja (versi ringkas) + kutipan.
 *
 * @package MEA_Orbit
 */

defined( 'ABSPATH' ) || exit;

$mea_orbit_steps = (array) mea_orbit_meta( 'langkah' );
if ( ! $mea_orbit_steps ) {
	return;
}
?>
<section class="sec paper" id="cara-kerja" aria-labelledby="judul-proses">
	<div class="wrap">
		<div class="sec-head"><h2 id="judul-proses"><?php esc_html_e( 'Cara kami bekerja', 'mea-orbit' ); ?></h2></div>
		<?php
		get_template_part( 'template-parts/global/proses', null, array( 'langkah' => $mea_orbit_steps ) );
		get_template_part( 'template-parts/global/kutipan', null, array( 'id' => (string) mea_orbit_meta( 'kutipan_id' ) ) );
		?>
	</div>
</section>
