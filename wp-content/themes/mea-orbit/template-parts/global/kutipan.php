<?php
/**
 * Kutipan Yohan. Argumen: id (k1…k5), gaya (pull | pimpinan).
 *
 * @package MEA_Orbit
 */

defined( 'ABSPATH' ) || exit;

$mea_orbit_quote = mea_orbit_quote( (string) ( $args['id'] ?? '' ) );
if ( ! $mea_orbit_quote ) {
	return;
}

if ( 'pimpinan' === ( $args['gaya'] ?? 'pull' ) ) :
	?>
	<p class="display founder-quote"><?php echo esc_html( $mea_orbit_quote['teks'] ); ?></p>
	<p class="small"><?php echo esc_html( $mea_orbit_quote['atribusi'] ); ?></p>
	<?php
	return;
endif;
?>
<figure class="pull-quote"><blockquote><p class="display"><?php echo esc_html( $mea_orbit_quote['teks'] ); ?></p></blockquote><figcaption class="small"><?php echo esc_html( $mea_orbit_quote['atribusi'] ); ?></figcaption></figure>
