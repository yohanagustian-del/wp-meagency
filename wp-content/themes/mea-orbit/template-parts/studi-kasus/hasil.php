<?php
/**
 * Studi kasus · Panel Hasil: angka + bar komposisi GMV, dari field (berlaku untuk
 * semua studi kasus). Disembunyikan bila izin publikasi nonaktif.
 *
 * @package MEA_Orbit
 */

defined( 'ABSPATH' ) || exit;

$mea_orbit_id      = get_queried_object_id();
$mea_orbit_numbers = mea_orbit_case_numbers( $mea_orbit_id, 'studi' );
if ( ! $mea_orbit_numbers ) {
	return;
}
$mea_orbit_mix = (array) mea_orbit_meta( 'komposisi' );
$mea_orbit_a   = (int) ( $mea_orbit_mix['persen_a'] ?? 0 );
$mea_orbit_b   = (int) ( $mea_orbit_mix['persen_b'] ?? 0 );
?>
<section class="dark case-results" aria-label="<?php esc_attr_e( 'Hasil', 'mea-orbit' ); ?>" data-panel-hasil>
	<div class="wrap">
		<div class="figures">
			<?php foreach ( $mea_orbit_numbers as $mea_orbit_number ) : ?>
				<div><div class="figure"<?php echo mea_orbit_attrs( mea_orbit_count_attrs( $mea_orbit_number['nilai'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- di-escape di mea_orbit_attrs(). ?>><?php echo esc_html( $mea_orbit_number['nilai'] ); ?></div><p class="figure-label"><?php echo esc_html( $mea_orbit_number['label'] ); ?></p></div>
			<?php endforeach; ?>
		</div>
		<?php if ( $mea_orbit_a > 0 && $mea_orbit_b > 0 && '' !== $mea_orbit_mix['label_a'] && '' !== $mea_orbit_mix['label_b'] ) : ?>
			<?php
			$mea_orbit_mix_label = sprintf(
				/* translators: 1: persen A, 2: label A, 3: persen B, 4: label B. */
				__( 'Komposisi GMV: %1$d persen %2$s, %3$d persen %4$s', 'mea-orbit' ),
				$mea_orbit_a,
				strtolower( $mea_orbit_mix['label_a'] ),
				$mea_orbit_b,
				strtolower( $mea_orbit_mix['label_b'] )
			);
			?>
			<div class="mix" role="img" aria-label="<?php echo esc_attr( $mea_orbit_mix_label ); ?>">
				<span class="mix-a" style="width:<?php echo (int) $mea_orbit_a; ?>%"><?php echo esc_html( $mea_orbit_mix['label_a'] . ' ' . $mea_orbit_a . '%' ); ?></span><span class="mix-b" style="width:<?php echo (int) $mea_orbit_b; ?>%"><?php echo esc_html( $mea_orbit_mix['label_b'] . ' ' . $mea_orbit_b . '%' ); ?></span>
			</div>
		<?php endif; ?>
	</div>
</section>
