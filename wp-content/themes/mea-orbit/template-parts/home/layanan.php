<?php
/**
 * Homepage · enam layanan (enam sisi kubus). Foto 4:3 per kartu dari assets/foto/layanan/,
 * slug = field `motion`. Foto dekoratif: nama dan deskripsi layanan sudah jadi teks link.
 *
 * @package MEA_Orbit
 */

defined( 'ABSPATH' ) || exit;

$mea_orbit_services = (array) mea_orbit_home( 'layanan' );
if ( ! $mea_orbit_services ) {
	return;
}
?>
<section class="sec paper" id="layanan" aria-labelledby="judul-layanan">
	<div class="wrap">
		<div class="sec-head">
			<h2 id="judul-layanan"><?php esc_html_e( 'Enam sisi operasional, satu tim', 'mea-orbit' ); ?></h2>
			<p class="lede"><?php esc_html_e( 'Pilih satu bagian yang paling menahan pertumbuhan toko Anda, atau serahkan semuanya.', 'mea-orbit' ); ?></p>
		</div>
		<ul class="svc">
			<?php foreach ( $mea_orbit_services as $mea_orbit_service ) : ?>
				<li><a href="<?php echo esc_url( mea_orbit_url( $mea_orbit_service['url'] ) ); ?>">
					<?php
					$mea_orbit_slug = (string) $mea_orbit_service['motion'];
					if ( in_array( $mea_orbit_slug, array( 'iklan', 'tiktok-shop', 'shopee', 'live', 'affiliate', 'konten' ), true ) ) :
						?>
						<span class="svc-foto" aria-hidden="true"><img
							src="<?php echo esc_url( mea_orbit_foto_url( 'layanan/' . $mea_orbit_slug . '-1200.webp' ) ); ?>"
							srcset="<?php echo esc_url( mea_orbit_foto_url( 'layanan/' . $mea_orbit_slug . '-600.webp' ) ); ?> 600w, <?php echo esc_url( mea_orbit_foto_url( 'layanan/' . $mea_orbit_slug . '-1200.webp' ) ); ?> 1200w"
							sizes="(max-width: 560px) 100vw, (max-width: 900px) 50vw, 380px"
							width="1200" height="900" alt="" loading="lazy" decoding="async"></span>
					<?php endif; ?>
					<span class="display"><?php echo esc_html( $mea_orbit_service['nama'] ); ?></span><span><?php echo esc_html( $mea_orbit_service['deskripsi'] ); ?></span>
				</a></li>
			<?php endforeach; ?>
		</ul>
		<p class="svc-more">
			<?php esc_html_e( 'Juga tersedia:', 'mea-orbit' ); ?>
			<a href="<?php echo esc_url( home_url( '/social-media-management/' ) ); ?>"><?php esc_html_e( 'Social Media Management', 'mea-orbit' ); ?></a>
			<span aria-hidden="true">·</span>
			<a href="<?php echo esc_url( home_url( '/seo/' ) ); ?>"><?php esc_html_e( 'SEO', 'mea-orbit' ); ?></a>
		</p>
	</div>
</section>
