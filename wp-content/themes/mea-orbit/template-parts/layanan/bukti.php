<?php
/**
 * Layanan · bukti dari studi kasus terkait. Angka dibaca dari studi kasus
 * (tidak diketik ulang) dan hilang bila izin publikasinya nonaktif.
 *
 * @package MEA_Orbit
 */

defined( 'ABSPATH' ) || exit;

$mea_orbit_case_id = (int) mea_orbit_meta( 'kasus_terkait_id' );
$mea_orbit_numbers = mea_orbit_case_numbers( $mea_orbit_case_id, (array) mea_orbit_meta( 'bukti_angka' ) );
$mea_orbit_title   = (string) mea_orbit_meta( 'bukti_judul' );
if ( ! $mea_orbit_numbers || '' === $mea_orbit_title ) {
	return;
}
?>
<section class="sec" aria-labelledby="judul-bukti">
	<div class="wrap proof-card">
		<div class="sec-head" style="margin:0"><p class="small"><?php esc_html_e( 'Bukti dari layanan ini', 'mea-orbit' ); ?></p><h2 id="judul-bukti"><?php echo esc_html( $mea_orbit_title ); ?></h2><div class="btn-row"><a class="btn btn-ghost" href="<?php echo esc_url( get_permalink( $mea_orbit_case_id ) ); ?>"><?php esc_html_e( 'Baca studi kasus', 'mea-orbit' ); ?></a></div></div>
		<div class="figures" style="grid-template-columns:repeat(<?php echo (int) min( 3, count( $mea_orbit_numbers ) ); ?>,1fr)">
			<?php foreach ( $mea_orbit_numbers as $mea_orbit_number ) : ?>
				<div><div class="figure"<?php echo mea_orbit_attrs( mea_orbit_count_attrs( $mea_orbit_number['nilai'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- di-escape di mea_orbit_attrs(). ?>><?php echo esc_html( $mea_orbit_number['nilai'] ); ?></div><p class="figure-label"><?php echo esc_html( $mea_orbit_number['label'] ); ?></p></div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
