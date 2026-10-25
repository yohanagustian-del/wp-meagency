<?php
/**
 * Form cari.
 *
 * @package MEA_Orbit
 */

defined( 'ABSPATH' ) || exit;

$mea_orbit_search_id = wp_unique_id( 'cari-' );
?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="<?php echo esc_attr( $mea_orbit_search_id ); ?>"><?php esc_html_e( 'Cari artikel', 'mea-orbit' ); ?></label>
	<input type="search" id="<?php echo esc_attr( $mea_orbit_search_id ); ?>" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Cari artikel', 'mea-orbit' ); ?>">
	<button class="btn btn-primary" type="submit"><?php esc_html_e( 'Cari', 'mea-orbit' ); ?></button>
</form>
