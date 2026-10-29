<?php
/**
 * Studi kasus · galeri: foto produk, toko, atau hasil dari field `galeri`.
 * Tersembunyi bila kosong. Menggantikan grid gambar halaman Elementor lama.
 *
 * @package MEA_Orbit
 */

defined( 'ABSPATH' ) || exit;

$mea_orbit_items = array();
foreach ( (array) mea_orbit_meta( 'galeri' ) as $mea_orbit_row ) {
	$mea_orbit_img = mea_orbit_img(
		(int) $mea_orbit_row['media_id'],
		'large',
		array(
			'alt'   => $mea_orbit_row['alt'],
			'sizes' => '(max-width: 560px) 50vw, (max-width: 900px) 33vw, 300px',
		)
	);
	if ( $mea_orbit_img ) {
		$mea_orbit_items[] = array( $mea_orbit_img, (string) $mea_orbit_row['keterangan'] );
	}
}
if ( ! $mea_orbit_items ) {
	return;
}
?>
<section class="sec" aria-labelledby="judul-galeri">
	<div class="wrap">
		<div class="sec-head"><h2 id="judul-galeri"><?php esc_html_e( 'Galeri', 'mea-orbit' ); ?></h2></div>
		<div class="galeri">
			<?php foreach ( $mea_orbit_items as $mea_orbit_item ) : ?>
				<figure class="photo" data-reveal><?php echo $mea_orbit_item[0]; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- dari wp_get_attachment_image(). ?>
					<?php if ( '' !== $mea_orbit_item[1] ) : ?>
						<figcaption><?php echo esc_html( $mea_orbit_item[1] ); ?></figcaption>
					<?php endif; ?>
				</figure>
			<?php endforeach; ?>
		</div>
	</div>
</section>
