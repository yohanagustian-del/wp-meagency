<?php
/**
 * Homepage · tim: blok pimpinan (foto Yohan dan Nerissa + kutipan) dan mosaik foto.
 *
 * @package MEA_Orbit
 */

defined( 'ABSPATH' ) || exit;

$mea_orbit_home   = mea_orbit_home();
$mea_orbit_mosaic = array_slice( (array) $mea_orbit_home['mosaik'], 0, 5 );
$mea_orbit_cells  = array(
	array( 'm-a', '(max-width: 760px) 100vw, 580px' ),
	array( 'm-b', '(max-width: 760px) 50vw, 580px' ),
	array( 'm-c', '(max-width: 760px) 50vw, 390px' ),
	array( 'm-d', '(max-width: 760px) 50vw, 190px' ),
	array( 'm-e', '(max-width: 760px) 100vw, 1132px' ),
);
$mea_orbit_leader = mea_orbit_img(
	(int) $mea_orbit_home['pimpinan_foto_id'],
	'large',
	array(
		'alt'   => $mea_orbit_home['pimpinan_alt'],
		'sizes' => '(max-width: 900px) 100vw, 680px',
	)
);
?>
<section class="sec" id="tim" aria-labelledby="judul-tim">
	<div class="wrap">
		<div class="sec-head">
			<h2 id="judul-tim"><?php esc_html_e( 'Orang-orang yang mengerjakan toko Anda', 'mea-orbit' ); ?></h2>
			<p class="lede"><?php esc_html_e( 'Anda tidak berurusan dengan sistem tanpa wajah. Setiap toko dipegang account manager dan tim divisi yang bisa Anda temui langsung di kantor kami di Bandung.', 'mea-orbit' ); ?></p>
		</div>
		<div class="leaders">
			<?php if ( $mea_orbit_leader ) : ?>
				<figure class="photo leaders-photo" data-reveal><?php echo $mea_orbit_leader; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- dari wp_get_attachment_image(). ?>
					<?php if ( '' !== $mea_orbit_home['pimpinan_keterangan'] ) : ?>
						<figcaption><?php echo esc_html( $mea_orbit_home['pimpinan_keterangan'] ); ?></figcaption>
					<?php endif; ?>
				</figure>
			<?php endif; ?>
			<div class="leaders-copy">
				<?php
				get_template_part(
					'template-parts/global/kutipan',
					null,
					array(
						'id'   => $mea_orbit_home['pimpinan_kutipan_id'],
						'gaya' => 'pimpinan',
					)
				);
				?>
				<p><?php esc_html_e( 'Anda bisa bertemu langsung dengan kami dan tim di kantor Bandung.', 'mea-orbit' ); ?></p>
			</div>
		</div>
		<?php if ( $mea_orbit_mosaic ) : ?>
			<div class="mosaic">
				<?php
				foreach ( $mea_orbit_mosaic as $mea_orbit_i => $mea_orbit_photo ) :
					$mea_orbit_img = mea_orbit_img(
						(int) $mea_orbit_photo['media_id'],
						'large',
						array(
							'alt'   => $mea_orbit_photo['alt'],
							'sizes' => $mea_orbit_cells[ $mea_orbit_i ][1],
						)
					);
					if ( ! $mea_orbit_img ) {
						continue;
					}
					?>
					<figure class="photo <?php echo esc_attr( $mea_orbit_cells[ $mea_orbit_i ][0] ); ?>" data-reveal><?php echo $mea_orbit_img; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- dari wp_get_attachment_image(). ?>
						<?php if ( '' !== $mea_orbit_photo['keterangan'] ) : ?>
							<figcaption><?php echo esc_html( $mea_orbit_photo['keterangan'] ); ?></figcaption>
						<?php endif; ?>
					</figure>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
