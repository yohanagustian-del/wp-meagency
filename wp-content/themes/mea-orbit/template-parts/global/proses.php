<?php
/**
 * Cara kerja: kotak sticky M3 + lima langkah. Argumen: langkah (array judul, deskripsi).
 *
 * Tanpa JavaScript dan dengan reduced motion, kotak menampilkan poster
 * (reduced motion: poster akhir, lewat <source media>). Langkah selalu HTML.
 *
 * @package MEA_Orbit
 */

defined( 'ABSPATH' ) || exit;

$mea_orbit_steps = (array) ( $args['langkah'] ?? array() );
if ( ! $mea_orbit_steps ) {
	return;
}
?>
<div class="proses" data-motion="proses">
	<div class="proses-visual">
		<picture>
			<source media="(prefers-reduced-motion: reduce) and (max-width: 767px)" srcset="<?php echo esc_url( mea_orbit_motion_url( 'proses/posters/poster-last-800.webp' ) ); ?>" width="800" height="800">
			<source media="(prefers-reduced-motion: reduce)" srcset="<?php echo esc_url( mea_orbit_motion_url( 'proses/posters/poster-last-1200.webp' ) ); ?>" width="1200" height="1200">
			<source media="(max-width: 767px)" srcset="<?php echo esc_url( mea_orbit_motion_url( 'proses/posters/poster-first-800.webp' ) ); ?>" width="800" height="800">
			<img src="<?php echo esc_url( mea_orbit_motion_url( 'proses/posters/poster-first-1200.webp' ) ); ?>" width="1200" height="1200" alt="" loading="lazy" decoding="async">
		</picture>
	</div>
	<ol class="steps">
		<?php foreach ( $mea_orbit_steps as $mea_orbit_i => $mea_orbit_step ) : ?>
			<li<?php echo 0 === $mea_orbit_i ? ' class="is-active"' : ''; ?>><h3><?php echo esc_html( $mea_orbit_step['judul'] ); ?></h3><p><?php echo esc_html( $mea_orbit_step['deskripsi'] ); ?></p></li>
		<?php endforeach; ?>
	</ol>
</div>
