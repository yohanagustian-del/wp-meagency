<?php
/**
 * Studi kasus · kepala: label, H1, meta Kategori / Kanal / Layanan / Periode.
 *
 * @package MEA_Orbit
 */

defined( 'ABSPATH' ) || exit;

$mea_orbit_h1      = (string) mea_orbit_meta( 'h1' );
$mea_orbit_service = array();
foreach ( (array) mea_orbit_meta( 'layanan' ) as $mea_orbit_row ) {
	if ( '' === $mea_orbit_row['label'] ) {
		continue;
	}
	$mea_orbit_service[] = $mea_orbit_row['page_id'] && get_post( (int) $mea_orbit_row['page_id'] )
		? '<a href="' . esc_url( get_permalink( (int) $mea_orbit_row['page_id'] ) ) . '">' . esc_html( $mea_orbit_row['label'] ) . '</a>'
		: esc_html( $mea_orbit_row['label'] );
}
$mea_orbit_meta_rows = array(
	__( 'Kategori', 'mea-orbit' ) => esc_html( (string) mea_orbit_meta( 'kategori' ) ),
	__( 'Kanal', 'mea-orbit' )    => esc_html( (string) mea_orbit_meta( 'kanal' ) ),
	__( 'Layanan', 'mea-orbit' )  => implode( ', ', $mea_orbit_service ),
	__( 'Periode', 'mea-orbit' )  => esc_html( (string) mea_orbit_meta( 'periode' ) ),
);
$mea_orbit_meta_rows = array_filter( $mea_orbit_meta_rows, 'strlen' );
?>
<header class="sec case-head">
	<div class="wrap">
		<p class="small" data-reveal-lines><?php esc_html_e( 'Studi kasus', 'mea-orbit' ); ?></p>
		<h1 id="judul-kasus" data-reveal-lines><?php echo esc_html( '' !== $mea_orbit_h1 ? $mea_orbit_h1 : get_the_title() ); ?></h1>
		<?php if ( $mea_orbit_meta_rows ) : ?>
			<dl class="meta">
				<?php foreach ( $mea_orbit_meta_rows as $mea_orbit_dt => $mea_orbit_dd ) : ?>
					<div><dt><?php echo esc_html( $mea_orbit_dt ); ?></dt><dd><?php echo $mea_orbit_dd; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- di-escape saat disusun di atas. ?></dd></div>
				<?php endforeach; ?>
			</dl>
		<?php endif; ?>
	</div>
</header>
