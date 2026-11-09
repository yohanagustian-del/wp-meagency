<?php
/**
 * Studi kasus · galeri: foto atau video (produk, konten, hasil) dari field `galeri`.
 * Tersembunyi bila kosong. Menggantikan grid gambar halaman Elementor lama.
 *
 * Field `galeri_mode`:
 * - grid (default): kotak 1:1, 4/3/2 kolom.
 * - bulanan: satu panel per bulan (data Seller Center), gambar utuh dengan rasio asli, tab bulan
 *   di atas, tombol ‹ ›, geser di HP (scroll-snap), panah keyboard. Semua panel ada di HTML;
 *   tanpa JavaScript, tab menjadi link ke panelnya dan panel tetap bisa digeser.
 *   JS: assets/js/galeri-bulanan.js (dimuat inc/assets.php hanya untuk mode ini).
 *
 * @package MEA_Orbit
 */

defined( 'ABSPATH' ) || exit;

$mea_orbit_bulanan = 'bulanan' === mea_orbit_meta( 'galeri_mode' );
$mea_orbit_items   = array();
foreach ( (array) mea_orbit_meta( 'galeri' ) as $mea_orbit_row ) {
	$mea_orbit_media = (int) $mea_orbit_row['media_id'];
	if ( $mea_orbit_media && 0 === strpos( (string) get_post_mime_type( $mea_orbit_media ), 'video/' ) ) {
		// Video: tanpa autoplay; preload metadata supaya frame pertama tampil (#t=0.1 untuk Safari).
		$mea_orbit_html = sprintf(
			'<video controls playsinline preload="%4$s" aria-label="%2$s"><source src="%1$s#t=0.1" type="%3$s"></video>',
			esc_url( (string) wp_get_attachment_url( $mea_orbit_media ) ),
			esc_attr( '' !== $mea_orbit_row['alt'] ? $mea_orbit_row['alt'] : get_the_title( $mea_orbit_media ) ),
			esc_attr( (string) get_post_mime_type( $mea_orbit_media ) ),
			$mea_orbit_bulanan && $mea_orbit_items ? 'none' : 'metadata'
		);
	} elseif ( $mea_orbit_bulanan ) {
		// Ukuran asli + srcset, tanpa crop. Panel pertama eager (tanpa fetchpriority tinggi:
		// galeri di bawah lipatan), sisanya lazy.
		$mea_orbit_attr = array(
			'sizes'         => '(max-width: 1180px) calc(100vw - 48px), 1132px',
			'loading'       => $mea_orbit_items ? 'lazy' : 'eager',
			'fetchpriority' => 'auto',
			'decoding'      => 'async',
		);
		if ( '' !== $mea_orbit_row['alt'] ) {
			$mea_orbit_attr['alt'] = $mea_orbit_row['alt'];
		}
		$mea_orbit_html = $mea_orbit_media && wp_attachment_is_image( $mea_orbit_media ) ? wp_get_attachment_image( $mea_orbit_media, 'full', false, $mea_orbit_attr ) : '';
	} else {
		$mea_orbit_html = mea_orbit_img(
			$mea_orbit_media,
			'large',
			array(
				'alt'   => $mea_orbit_row['alt'],
				'sizes' => '(max-width: 560px) 50vw, (max-width: 900px) 33vw, 300px',
			)
		);
	}
	if ( $mea_orbit_html ) {
		$mea_orbit_items[] = array( $mea_orbit_html, (string) $mea_orbit_row['keterangan'] );
	}
}
if ( ! $mea_orbit_items ) {
	return;
}

if ( $mea_orbit_bulanan ) :
	?>
	<section class="sec" aria-labelledby="judul-galeri">
		<div class="wrap">
			<div class="sec-head"><h2 id="judul-galeri"><?php esc_html_e( 'Data per bulan dari Seller Center', 'mea-orbit' ); ?></h2></div>
			<div class="bulanan" data-bulanan data-reveal>
				<div class="bulanan-nav">
					<button type="button" class="bulanan-arah" data-arah="-1" aria-label="<?php esc_attr_e( 'Bulan sebelumnya', 'mea-orbit' ); ?>" disabled><span aria-hidden="true">&lsaquo;</span></button>
					<div class="bulanan-tabs" role="tablist" aria-labelledby="judul-galeri">
						<?php foreach ( $mea_orbit_items as $mea_orbit_i => $mea_orbit_item ) : ?>
							<a class="bulanan-tab" role="tab" id="bulan-tab-<?php echo (int) $mea_orbit_i + 1; ?>" href="#bulan-<?php echo (int) $mea_orbit_i + 1; ?>" aria-controls="bulan-<?php echo (int) $mea_orbit_i + 1; ?>" aria-selected="<?php echo 0 === $mea_orbit_i ? 'true' : 'false'; ?>" tabindex="<?php echo 0 === $mea_orbit_i ? '0' : '-1'; ?>"><?php echo esc_html( mea_orbit_galeri_label( $mea_orbit_item[1], $mea_orbit_i + 1 ) ); ?></a>
						<?php endforeach; ?>
					</div>
					<button type="button" class="bulanan-arah" data-arah="1" aria-label="<?php esc_attr_e( 'Bulan berikutnya', 'mea-orbit' ); ?>"<?php echo count( $mea_orbit_items ) > 1 ? '' : ' disabled'; ?>><span aria-hidden="true">&rsaquo;</span></button>
				</div>
				<div class="bulanan-track">
					<?php foreach ( $mea_orbit_items as $mea_orbit_i => $mea_orbit_item ) : ?>
						<figure class="bulanan-panel" role="tabpanel" id="bulan-<?php echo (int) $mea_orbit_i + 1; ?>" aria-labelledby="bulan-tab-<?php echo (int) $mea_orbit_i + 1; ?>" tabindex="<?php echo 0 === $mea_orbit_i ? '0' : '-1'; ?>">
							<?php echo $mea_orbit_item[0]; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- dari wp_get_attachment_image() atau di-escape di atas. ?>
							<?php if ( '' !== $mea_orbit_item[1] ) : ?>
								<figcaption><?php echo esc_html( $mea_orbit_item[1] ); ?></figcaption>
							<?php endif; ?>
						</figure>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
	</section>
	<?php
	return;
endif;
?>
<section class="sec" aria-labelledby="judul-galeri">
	<div class="wrap">
		<div class="sec-head"><h2 id="judul-galeri"><?php esc_html_e( 'Galeri', 'mea-orbit' ); ?></h2></div>
		<div class="galeri">
			<?php foreach ( $mea_orbit_items as $mea_orbit_item ) : ?>
				<figure class="photo" data-reveal><?php echo $mea_orbit_item[0]; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- dari wp_get_attachment_image() atau di-escape di atas. ?>
					<?php if ( '' !== $mea_orbit_item[1] ) : ?>
						<figcaption><?php echo esc_html( $mea_orbit_item[1] ); ?></figcaption>
					<?php endif; ?>
				</figure>
			<?php endforeach; ?>
		</div>
	</div>
</section>
