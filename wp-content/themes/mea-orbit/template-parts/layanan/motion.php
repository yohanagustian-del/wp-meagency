<?php
/**
 * Layanan · motion pilar setelah hero (field `motion`). Memakai primitif motion.js
 * yang sudah ada; reduced motion, Hemat Data, dan 2g/3g hanya menampilkan poster.
 *
 * - `ecommerce`: Kurva Bellarosa (M4, `data-motion="bellarosa"`) + angka studi kasus
 *   Bellarosa yang dihitung naik (`data-count`). Angka dibaca dari studi kasus (tidak
 *   diketik ulang) dan seluruh blok hilang bila izin publikasinya nonaktif.
 * - `live`: mockup HP (`data-motion="konten"`) memutar video live dari Media Library
 *   saat terlihat. Tidak tampil tanpa poster dan video.
 *
 * @package MEA_Orbit
 */

defined( 'ABSPATH' ) || exit;

$mea_orbit_motion = (string) mea_orbit_meta( 'motion' );

if ( 'ecommerce' === $mea_orbit_motion ) :
	$mea_orbit_case    = get_page_by_path( 'bellarosa' );
	$mea_orbit_case_id = $mea_orbit_case ? (int) $mea_orbit_case->ID : 0;
	$mea_orbit_numbers = mea_orbit_case_numbers( $mea_orbit_case_id, 'beranda' );
	if ( ! $mea_orbit_numbers ) {
		return;
	}
	?>
	<section class="sec pilar-motion pilar-ecommerce" aria-label="<?php esc_attr_e( 'Hasil studi kasus Bellarosa', 'mea-orbit' ); ?>">
		<div class="wrap case-feature">
			<figure class="case-visual" data-motion="bellarosa">
				<picture>
					<source media="(max-width: 767px)" srcset="<?php echo esc_url( mea_orbit_motion_url( 'bellarosa/bellarosa-case-mobile-900x900-poster.webp' ) ); ?>" width="900" height="900">
					<img src="<?php echo esc_url( mea_orbit_motion_url( 'bellarosa/bellarosa-case-desktop-1200x900-poster.webp' ) ); ?>" width="1200" height="900" loading="lazy" decoding="async" alt="<?php esc_attr_e( 'Grafik GMV Bellarosa yang naik ke Rp 8,2 miliar: 75% dari organik dan affiliate, 25% dari iklan, ROAS 21x', 'mea-orbit' ); ?>">
				</picture>
			</figure>
			<div>
				<p class="small"><?php esc_html_e( 'Studi kasus Bellarosa', 'mea-orbit' ); ?></p>
				<div class="figures pilar-figures">
					<?php foreach ( $mea_orbit_numbers as $mea_orbit_number ) : ?>
						<div><div class="figure"<?php echo mea_orbit_attrs( mea_orbit_count_attrs( $mea_orbit_number['nilai'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- di-escape di mea_orbit_attrs(). ?>><?php echo esc_html( $mea_orbit_number['nilai'] ); ?></div><p class="figure-label"><?php echo esc_html( $mea_orbit_number['label'] ); ?></p></div>
					<?php endforeach; ?>
				</div>
				<div class="btn-row"><a class="btn btn-ghost" href="<?php echo esc_url( get_permalink( $mea_orbit_case_id ) ); ?>"><?php esc_html_e( 'Baca studi kasus', 'mea-orbit' ); ?></a></div>
			</div>
		</div>
	</section>
	<?php
	return;
endif;

if ( 'live' === $mea_orbit_motion ) :
	$mea_orbit_poster_id = (int) mea_orbit_meta( 'motion_poster' );
	$mea_orbit_mp4       = (int) mea_orbit_meta( 'motion_mp4' ) ? (string) wp_get_attachment_url( (int) mea_orbit_meta( 'motion_mp4' ) ) : '';
	$mea_orbit_webm      = (int) mea_orbit_meta( 'motion_webm' ) ? (string) wp_get_attachment_url( (int) mea_orbit_meta( 'motion_webm' ) ) : '';
	$mea_orbit_note      = (string) mea_orbit_meta( 'motion_keterangan' );
	$mea_orbit_poster    = mea_orbit_img(
		$mea_orbit_poster_id,
		'full',
		array(
			'alt'   => '' !== $mea_orbit_note ? $mea_orbit_note : __( 'Cuplikan siaran live', 'mea-orbit' ),
			'sizes' => '260px',
		)
	);
	if ( '' === $mea_orbit_poster || ( '' === $mea_orbit_mp4 && '' === $mea_orbit_webm ) ) {
		return;
	}
	$mea_orbit_meta_img = wp_get_attachment_metadata( $mea_orbit_poster_id );
	$mea_orbit_tall     = ! empty( $mea_orbit_meta_img['width'] ) && $mea_orbit_meta_img['height'] / $mea_orbit_meta_img['width'] > 1.5;
	?>
	<section class="sec pilar-motion pilar-live" aria-label="<?php esc_attr_e( 'Cuplikan siaran live', 'mea-orbit' ); ?>">
		<div class="wrap">
			<figure class="pilar-phone">
				<span class="phone">
					<span class="phone-screen<?php echo $mea_orbit_tall ? '' : ' is-contain'; ?>" data-motion="konten" data-mp4="<?php echo esc_url( $mea_orbit_mp4 ); ?>" data-webm="<?php echo esc_url( $mea_orbit_webm ); ?>"><?php echo $mea_orbit_poster; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- dari wp_get_attachment_image(). ?></span>
				</span>
				<?php if ( '' !== $mea_orbit_note ) : ?>
					<figcaption class="small"><?php echo esc_html( $mea_orbit_note ); ?></figcaption>
				<?php endif; ?>
			</figure>
		</div>
	</section>
	<?php
endif;
