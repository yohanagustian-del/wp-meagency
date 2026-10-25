<?php
/**
 * Bahasa halaman: og:locale id_ID dan lang="id-ID" di seluruh situs.
 *
 * Settings → General tidak diubah, jadi bahasa wp-admin tetap seperti sekarang.
 *
 * @package MEA_Orbit
 */

defined( 'ABSPATH' ) || exit;

/**
 * Atribut lang pada <html>.
 *
 * @param string $output Atribut bawaan, misalnya `lang="en-US"`.
 * @return string
 */
function mea_orbit_language_attributes( $output ) {
	$output = preg_replace( '/lang="[^"]*"/', 'lang="id-ID"', $output, 1, $count );
	return $count ? $output : trim( $output . ' lang="id-ID"' );
}
add_filter( 'language_attributes', 'mea_orbit_language_attributes' );

/**
 * Nilai og:locale yang dicetak Rank Math.
 *
 * @return string
 */
function mea_orbit_og_locale() {
	return 'id_ID';
}
add_filter( 'rank_math/opengraph/facebook/og_locale', 'mea_orbit_og_locale' );
