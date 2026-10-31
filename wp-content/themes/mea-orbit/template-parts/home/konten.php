<?php
/**
 * Homepage · mockup HP "Konten" setelah Enam layanan (Prompt C, rilis 0.2.0; blok
 * portofolio tim kreatif rilis 0.7.0).
 *
 * Dua blok dari opsi Homepage, masing-masing tersembunyi bila kosong:
 * 1. `konten_video` + `konten_keterangan`: ilustrasi format konten buatan AI.
 *    HP menampilkan baris pertama saja.
 * 2. `portofolio_judul` + `portofolio_video` + `portofolio_keterangan`: hasil
 *    produksi tim kreatif MEA. HP menampilkan semua dalam baris yang bisa digeser.
 *
 * Bingkai HP generik dari CSS. HTML hanya memuat poster (lazy); video bisu dipasang
 * assets/js/motion.js saat section mendekati layar, diputar saat terlihat dan
 * berhenti saat keluar.
 *
 * @package MEA_Orbit
 */

defined( 'ABSPATH' ) || exit;

$mea_orbit_blocks = array(
	array(
		'judul' => '',
		'clips' => mea_orbit_konten_clips(
			mea_orbit_home( 'konten_video' ),
			/* translators: %s: kategori. */
			__( 'Contoh konten %s', 'mea-orbit' ),
			__( 'Contoh konten', 'mea-orbit' )
		),
		'note'  => (string) mea_orbit_home( 'konten_keterangan' ),
		'class' => 'phones',
	),
	array(
		'judul' => (string) mea_orbit_home( 'portofolio_judul' ),
		'clips' => mea_orbit_konten_clips(
			mea_orbit_home( 'portofolio_video' ),
			/* translators: %s: kategori. */
			__( 'Hasil produksi konten %s oleh tim kreatif MEA Agency', 'mea-orbit' ),
			__( 'Hasil produksi konten tim kreatif MEA Agency', 'mea-orbit' )
		),
		'note'  => (string) mea_orbit_home( 'portofolio_keterangan' ),
		'class' => 'phones is-scroll',
	),
);
$mea_orbit_blocks = array_values(
	array_filter(
		$mea_orbit_blocks,
		static function ( $block ) {
			return (bool) $block['clips'];
		}
	)
);
if ( ! $mea_orbit_blocks ) {
	return;
}
?>
<section class="sec" id="contoh-konten" aria-labelledby="judul-contoh-konten">
	<div class="wrap">
		<div class="sec-head">
			<h2 id="judul-contoh-konten"><?php esc_html_e( 'Konten yang dijual, bukan sekadar ditonton', 'mea-orbit' ); ?></h2>
		</div>
		<?php foreach ( $mea_orbit_blocks as $mea_orbit_block ) : ?>
			<div class="phones-block">
				<?php if ( '' !== trim( $mea_orbit_block['judul'] ) ) : ?>
					<h3 class="phones-title"><?php echo esc_html( $mea_orbit_block['judul'] ); ?></h3>
				<?php endif; ?>
				<ul class="<?php echo esc_attr( $mea_orbit_block['class'] ); ?>">
					<?php foreach ( $mea_orbit_block['clips'] as $mea_orbit_clip ) : ?>
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
				<?php if ( '' !== trim( $mea_orbit_block['note'] ) ) : ?>
					<p class="small phones-note"><?php echo esc_html( $mea_orbit_block['note'] ); ?></p>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	</div>
</section>
