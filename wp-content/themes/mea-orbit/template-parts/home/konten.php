<?php
/**
 * Homepage · mockup HP "Konten" setelah Enam layanan (Prompt C, rilis 0.2.0).
 *
 * Isi dari opsi Homepage `konten_video`. Bingkai HP generik dari CSS. HTML hanya
 * memuat poster (lazy); video bisu dipasang assets/js/motion.js saat section
 * mendekati layar, diputar saat terlihat dan berhenti saat keluar. HP menampilkan
 * baris pertama saja. Kosong = tidak tampil.
 *
 * @package MEA_Orbit
 */

defined( 'ABSPATH' ) || exit;

$mea_orbit_clips = array();
foreach ( (array) mea_orbit_home( 'konten_video' ) as $mea_orbit_clip ) {
	$mea_orbit_poster_id = (int) ( $mea_orbit_clip['poster'] ?? 0 );
	$mea_orbit_mp4       = (int) ( $mea_orbit_clip['video_mp4'] ?? 0 ) ? (string) wp_get_attachment_url( (int) $mea_orbit_clip['video_mp4'] ) : '';
	$mea_orbit_webm      = (int) ( $mea_orbit_clip['video_webm'] ?? 0 ) ? (string) wp_get_attachment_url( (int) $mea_orbit_clip['video_webm'] ) : '';
	$mea_orbit_label     = (string) ( $mea_orbit_clip['label'] ?? '' );
	$mea_orbit_poster    = mea_orbit_img(
		$mea_orbit_poster_id,
		'full',
		array(
			'alt'   => '' !== $mea_orbit_label ? sprintf( /* translators: %s: kategori. */ __( 'Contoh konten %s', 'mea-orbit' ), $mea_orbit_label ) : __( 'Contoh konten', 'mea-orbit' ),
			'sizes' => '(max-width: 767px) 240px, 260px',
		)
	);
	if ( '' === $mea_orbit_poster || ( '' === $mea_orbit_mp4 && '' === $mea_orbit_webm ) ) {
		continue;
	}
	$mea_orbit_meta    = wp_get_attachment_metadata( $mea_orbit_poster_id );
	$mea_orbit_tall    = ! empty( $mea_orbit_meta['width'] ) && $mea_orbit_meta['height'] / $mea_orbit_meta['width'] > 1.5;
	$mea_orbit_link    = trim( (string) ( $mea_orbit_clip['link'] ?? '' ) );
	$mea_orbit_clips[] = array(
		'poster' => $mea_orbit_poster,
		'mp4'    => $mea_orbit_mp4,
		'webm'   => $mea_orbit_webm,
		'tall'   => $mea_orbit_tall,
		'label'  => $mea_orbit_label,
		'link'   => mea_orbit_url( '' !== $mea_orbit_link ? $mea_orbit_link : '/jasa-konten/' ),
	);
}
if ( ! $mea_orbit_clips ) {
	return;
}
$mea_orbit_note = (string) mea_orbit_home( 'konten_keterangan' );
?>
<section class="sec" id="contoh-konten" aria-labelledby="judul-contoh-konten">
	<div class="wrap">
		<div class="sec-head">
			<h2 id="judul-contoh-konten"><?php esc_html_e( 'Konten yang dijual, bukan sekadar ditonton', 'mea-orbit' ); ?></h2>
		</div>
		<ul class="phones">
			<?php foreach ( $mea_orbit_clips as $mea_orbit_clip ) : ?>
				<li>
					<a class="phone-card" href="<?php echo esc_url( $mea_orbit_clip['link'] ); ?>">
						<span class="phone">
							<span class="phone-screen<?php echo $mea_orbit_clip['tall'] ? '' : ' is-contain'; ?>" data-motion="konten" data-mp4="<?php echo esc_url( $mea_orbit_clip['mp4'] ); ?>" data-webm="<?php echo esc_url( $mea_orbit_clip['webm'] ); ?>"><?php echo $mea_orbit_clip['poster']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- dari wp_get_attachment_image(). ?></span>
						</span>
						<?php if ( '' !== $mea_orbit_clip['label'] ) : ?>
							<span class="phone-label"><?php echo esc_html( $mea_orbit_clip['label'] ); ?></span>
						<?php endif; ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
		<?php if ( '' !== trim( $mea_orbit_note ) ) : ?>
			<p class="small phones-note"><?php echo esc_html( $mea_orbit_note ); ?></p>
		<?php endif; ?>
	</div>
</section>
