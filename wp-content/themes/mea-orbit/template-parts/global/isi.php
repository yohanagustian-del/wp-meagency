<?php
/**
 * Isi halaman Orbit (field rich `isi`): teks halaman lama yang dipindah dari Elementor,
 * dicetak sebagai kolom baca. Tidak tampil bila kosong.
 *
 * @package MEA_Orbit
 */

defined( 'ABSPATH' ) || exit;

$mea_orbit_isi = mea_orbit_isi_html( (string) mea_orbit_meta( 'isi' ) );
if ( '' === trim( $mea_orbit_isi ) ) {
	return;
}
?>
<section class="sec isi-sec" aria-label="<?php echo esc_attr( get_the_title() ); ?>">
	<div class="wrap">
		<div class="isi">
			<?php echo $mea_orbit_isi; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- disaring wp_kses di mea_orbit_isi_html(). ?>
		</div>
	</div>
</section>
