<?php
/**
 * Layanan · layanan terkait (opsional). Tautan ke halaman layanan lain yang
 * dipilih di field; baris tanpa halaman terbit dilewati. Tidak tampil bila kosong.
 *
 * @package MEA_Orbit
 */

defined( 'ABSPATH' ) || exit;

$mea_orbit_related = array();
foreach ( (array) mea_orbit_meta( 'layanan_terkait' ) as $mea_orbit_row ) {
	$mea_orbit_page = (int) ( $mea_orbit_row['page_id'] ?? 0 );
	if ( ! $mea_orbit_page || get_queried_object_id() === $mea_orbit_page || 'publish' !== get_post_status( $mea_orbit_page ) ) {
		continue;
	}
	$mea_orbit_label     = trim( (string) ( $mea_orbit_row['label'] ?? '' ) );
	$mea_orbit_related[] = array(
		'url'   => get_permalink( $mea_orbit_page ),
		'label' => '' !== $mea_orbit_label ? $mea_orbit_label : get_the_title( $mea_orbit_page ),
	);
}
if ( ! $mea_orbit_related ) {
	return;
}
?>
<section class="sec" aria-labelledby="judul-terkait">
	<div class="wrap">
		<div class="sec-head"><h2 id="judul-terkait"><?php esc_html_e( 'Layanan terkait', 'mea-orbit' ); ?></h2></div>
		<ul class="more">
			<?php foreach ( $mea_orbit_related as $mea_orbit_item ) : ?>
				<li><a href="<?php echo esc_url( $mea_orbit_item['url'] ); ?>"><span class="display"><?php echo esc_html( $mea_orbit_item['label'] ); ?></span><span class="small"><?php esc_html_e( 'Lihat layanan', 'mea-orbit' ); ?></span></a></li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
