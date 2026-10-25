<?php
/**
 * Studi kasus · studi kasus lain.
 *
 * @package MEA_Orbit
 */

defined( 'ABSPATH' ) || exit;

$mea_orbit_ids = array_filter(
	(array) mea_orbit_meta( 'kasus_lain' ),
	static function ( $id ) {
		return 'publish' === get_post_status( $id );
	}
);
if ( ! $mea_orbit_ids ) {
	return;
}
?>
<section class="sec paper" aria-labelledby="judul-lain">
	<div class="wrap">
		<div class="sec-head"><h2 id="judul-lain"><?php esc_html_e( 'Studi kasus lain', 'mea-orbit' ); ?></h2></div>
		<ul class="more">
			<?php foreach ( $mea_orbit_ids as $mea_orbit_id ) : ?>
				<li><a href="<?php echo esc_url( get_permalink( $mea_orbit_id ) ); ?>"><span class="display"><?php echo esc_html( mea_orbit_case_brand( $mea_orbit_id ) ); ?></span><span class="small"><?php esc_html_e( 'Baca studi kasus', 'mea-orbit' ); ?></span></a></li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
