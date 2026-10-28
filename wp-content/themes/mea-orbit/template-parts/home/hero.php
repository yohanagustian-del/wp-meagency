<?php
/**
 * Homepage · hero gelap + angka bukti dari studi kasus unggulan.
 *
 * H1, lede, CTA, dan angka selalu HTML. Visual = poster kubus v2 (frame 0, gambar LCP);
 * reduced motion memakai poster akhir. Sequence scroll-scrub: assets/js/motion.js.
 *
 * @package MEA_Orbit
 */

defined( 'ABSPATH' ) || exit;

$mea_orbit_home    = mea_orbit_home();
$mea_orbit_case_id = (int) $mea_orbit_home['kasus_unggulan_id'];
$mea_orbit_numbers = mea_orbit_case_numbers( $mea_orbit_case_id, 'beranda' );
$mea_orbit_case    = $mea_orbit_case_id && get_post( $mea_orbit_case_id ) ? get_permalink( $mea_orbit_case_id ) : home_url( '/study-case/' );
?>
<section class="hero dark" id="hero" aria-labelledby="judul-hero">
	<div class="wrap hero-grid">
		<div class="hero-copy">
			<?php if ( '' !== $mea_orbit_home['hero_kicker'] ) : ?>
				<p class="small hero-kicker"><?php echo esc_html( $mea_orbit_home['hero_kicker'] ); ?></p>
			<?php endif; ?>
			<h1 id="judul-hero"><?php echo esc_html( $mea_orbit_home['hero_judul'] ); ?></h1>
			<p class="lede"><?php echo esc_html( $mea_orbit_home['hero_lede'] ); ?></p>
			<div class="btn-row">
				<a class="btn btn-primary" href="#konsultasi"><?php echo esc_html( $mea_orbit_home['cta_utama'] ); ?></a>
				<a class="btn btn-ghost" href="<?php echo esc_url( $mea_orbit_case ); ?>"><?php echo esc_html( $mea_orbit_home['cta_kedua'] ); ?></a>
			</div>
		</div>
		<div class="hero-visual" data-motion="hero">
			<picture>
				<source media="(prefers-reduced-motion: reduce) and (max-width: 767px)" srcset="<?php echo esc_url( mea_orbit_motion_url( 'hero/posters/poster-last-720.webp' ) ); ?>" width="720" height="720">
				<source media="(prefers-reduced-motion: reduce)" srcset="<?php echo esc_url( mea_orbit_motion_url( 'hero/posters/poster-last-1200.webp' ) ); ?>" width="1200" height="1200">
				<source media="(max-width: 767px)" srcset="<?php echo esc_url( mea_orbit_motion_url( 'hero/posters/poster-first-720.webp' ) ); ?>" width="720" height="720">
				<img src="<?php echo esc_url( mea_orbit_motion_url( 'hero/posters/poster-first-1200.webp' ) ); ?>" width="1200" height="1200" fetchpriority="high" decoding="async" alt="<?php esc_attr_e( 'Kubus MEA Agency dengan logo Shopee, TikTok, Tokopedia, dan Meta, serta dua sisi video ilustrasi buatan AI: ulasan kreator dan live shopping, dikelilingi lintasan orbit', 'mea-orbit' ); ?>">
			</picture>
		</div>
	</div>
	<?php if ( $mea_orbit_numbers ) : ?>
		<div class="wrap">
			<div class="figures proof" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: nama brand. */ __( 'Hasil dari studi kasus %s', 'mea-orbit' ), mea_orbit_case_brand( $mea_orbit_case_id ) ) ); ?>">
				<?php foreach ( $mea_orbit_numbers as $mea_orbit_number ) : ?>
					<div><div class="figure"<?php echo mea_orbit_attrs( mea_orbit_count_attrs( $mea_orbit_number['nilai'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- di-escape di mea_orbit_attrs(). ?>><?php echo esc_html( $mea_orbit_number['nilai'] ); ?></div><p class="figure-label"><?php echo esc_html( $mea_orbit_number['label'] ); ?></p></div>
				<?php endforeach; ?>
			</div>
			<p class="small proof-src"><a href="<?php echo esc_url( $mea_orbit_case ); ?>">
				<?php
				/* translators: %s: nama brand. */
				printf( esc_html__( 'Baca studi kasus %s', 'mea-orbit' ), esc_html( mea_orbit_case_brand( $mea_orbit_case_id ) ) );
				?>
			</a></p>
		</div>
	<?php endif; ?>
</section>
