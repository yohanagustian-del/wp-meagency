<?php
/**
 * Cara kerja: kotak sticky berisi foto per langkah + daftar langkah.
 *
 * Argumen:
 * - langkah: array judul, deskripsi.
 * - foto (opsional): array file (`langkah-1` … `langkah-5`), alt. Tanpa argumen ini
 *   (halaman layanan) foto berurutan langkah-1 … langkah-5, berulang bila lebih dari
 *   lima langkah, dengan alt kosong.
 *
 * Foto langkah aktif berganti lewat assets/js/motion.js (crossfade CSS). Tanpa
 * JavaScript foto langkah pertama yang tampil. Langkah selalu HTML.
 *
 * @package MEA_Orbit
 */

defined( 'ABSPATH' ) || exit;

$mea_orbit_steps = array_values( (array) ( $args['langkah'] ?? array() ) );
if ( ! $mea_orbit_steps ) {
	return;
}
$mea_orbit_fotos = (array) ( $args['foto'] ?? array() );
?>
<div class="proses" data-motion="proses">
	<div class="proses-visual">
		<div class="proses-stack">
			<?php
			foreach ( $mea_orbit_steps as $mea_orbit_i => $mea_orbit_step ) :
				$mea_orbit_f    = (array) ( $mea_orbit_fotos[ $mea_orbit_i ] ?? array() );
				$mea_orbit_file = (string) ( $mea_orbit_f['file'] ?? '' );
				if ( ! preg_match( '/^langkah-[1-5]$/', $mea_orbit_file ) ) {
					$mea_orbit_file = 'langkah-' . ( ( $mea_orbit_i % 5 ) + 1 );
				}
				$mea_orbit_alt = (string) ( $mea_orbit_f['alt'] ?? '' );
				?>
				<picture<?php echo 0 === $mea_orbit_i ? ' class="is-active"' : ''; ?>>
					<source media="(max-width: 767px)" srcset="<?php echo esc_url( mea_orbit_foto_url( 'proses/' . $mea_orbit_file . '-800.webp' ) ); ?>" width="800" height="800">
					<img src="<?php echo esc_url( mea_orbit_foto_url( 'proses/' . $mea_orbit_file . '-1024.webp' ) ); ?>" width="1024" height="1024" alt="<?php echo esc_attr( $mea_orbit_alt ); ?>" loading="lazy" decoding="async">
				</picture>
			<?php endforeach; ?>
		</div>
	</div>
	<ol class="steps">
		<?php foreach ( $mea_orbit_steps as $mea_orbit_i => $mea_orbit_step ) : ?>
			<li<?php echo 0 === $mea_orbit_i ? ' class="is-active"' : ''; ?>><h3><?php echo esc_html( $mea_orbit_step['judul'] ); ?></h3><p><?php echo esc_html( $mea_orbit_step['deskripsi'] ); ?></p></li>
		<?php endforeach; ?>
	</ol>
</div>
