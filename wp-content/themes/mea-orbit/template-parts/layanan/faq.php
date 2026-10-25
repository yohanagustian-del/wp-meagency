<?php
/**
 * Layanan · FAQ (isi yang sama dipakai schema FAQPage).
 *
 * @package MEA_Orbit
 */

defined( 'ABSPATH' ) || exit;

$mea_orbit_faq = (array) mea_orbit_meta( 'faq' );
if ( ! $mea_orbit_faq ) {
	return;
}
?>
<section class="sec paper faq" aria-labelledby="judul-faq">
	<div class="wrap faq-grid">
		<div class="sec-head"><h2 id="judul-faq"><?php esc_html_e( 'Pertanyaan yang sering diajukan', 'mea-orbit' ); ?></h2></div>
		<div>
			<?php foreach ( $mea_orbit_faq as $mea_orbit_item ) : ?>
				<details><summary><?php echo esc_html( $mea_orbit_item['tanya'] ); ?></summary><p><?php echo esc_html( $mea_orbit_item['jawab'] ); ?></p></details>
			<?php endforeach; ?>
		</div>
	</div>
</section>
