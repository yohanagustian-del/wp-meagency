<?php
/**
 * Section konsultasi + form lead. Argumen: sumber (sumber_form).
 *
 * Form dicetak plugin mea-leads. Bila plugin nonaktif, tempat form diisi
 * tombol WhatsApp ke tim sales.
 *
 * @package MEA_Orbit
 */

defined( 'ABSPATH' ) || exit;

$mea_orbit_sales = mea_orbit_sales();
?>
<section class="sec dark" id="konsultasi" aria-labelledby="judul-konsultasi">
	<div class="wrap contact">
		<div class="sec-head">
			<h2 id="judul-konsultasi"><?php esc_html_e( 'Ceritakan kondisi toko Anda', 'mea-orbit' ); ?></h2>
			<p class="lede"><?php esc_html_e( 'Tim kami membaca data toko Anda sebelum menghubungi. Konsultasi pertama tidak dipungut biaya dan tidak mengikat.', 'mea-orbit' ); ?></p>
		</div>
		<?php if ( function_exists( 'mea_leads_form' ) ) : ?>
			<?php echo mea_leads_form( array( 'sumber' => (string) ( $args['sumber'] ?? '' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- markup form sudah di-escape oleh plugin. ?>
		<?php else : ?>
			<div class="lead-form lead-form-wa">
				<p><?php esc_html_e( 'Kirim pesan ke tim sales lewat WhatsApp. Ceritakan nama toko dan kisaran omzet per bulan agar tim bisa membaca kondisinya lebih dulu.', 'mea-orbit' ); ?></p>
				<a class="btn btn-primary" href="<?php echo esc_url( $mea_orbit_sales['wa'] ); ?>"><?php esc_html_e( 'Chat tim sales sekarang', 'mea-orbit' ); ?></a>
			</div>
		<?php endif; ?>
	</div>
</section>
