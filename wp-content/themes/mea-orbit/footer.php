<?php
/**
 * Footer situs. Kode yang sama dipakai di setiap URL.
 *
 * @package MEA_Orbit
 */

defined( 'ABSPATH' ) || exit;

?>
<footer class="site-foot">
	<div class="wrap">
		<div class="cols">
			<div>
				<a class="brand" href="<?php echo esc_url( home_url( '/' ) ); ?>"><span class="brand-mark" aria-hidden="true"></span>MEA Agency</a>
				<p class="foot-about"><?php esc_html_e( 'Mengerjakan iklan, toko, konten, dan live untuk brand yang berjualan di Shopee dan TikTok Shop.', 'mea-orbit' ); ?></p>
			</div>
			<div>
				<p class="foot-h" id="foot-layanan"><?php esc_html_e( 'Layanan', 'mea-orbit' ); ?></p>
				<nav aria-labelledby="foot-layanan">
					<?php mea_orbit_pilar_footer(); ?>
				</nav>
			</div>
			<div>
				<p class="foot-h" id="foot-perusahaan"><?php esc_html_e( 'Perusahaan', 'mea-orbit' ); ?></p>
				<nav aria-labelledby="foot-perusahaan">
					<?php mea_orbit_menu( 'mea-orbit-footer' ); ?>
				</nav>
				<p class="foot-h foot-h-next" id="foot-ekosistem"><?php esc_html_e( 'Ekosistem MEA', 'mea-orbit' ); ?></p>
				<nav aria-labelledby="foot-ekosistem">
					<?php mea_orbit_link_list( mea_orbit_ekosistem() ); ?>
				</nav>
			</div>
			<div>
				<p class="foot-h"><?php esc_html_e( 'Kantor', 'mea-orbit' ); ?></p>
				<p>Jl. Rajawali Barat No.77A<br>Kota Bandung 40184</p>
				<p class="foot-h foot-h-next"><?php esc_html_e( 'Hubungi', 'mea-orbit' ); ?></p>
				<ul>
					<li><a href="<?php echo esc_url( mea_orbit_consult_url() ); ?>"><?php esc_html_e( 'Form konsultasi', 'mea-orbit' ); ?></a></li>
					<li><a href="https://www.instagram.com/yohanagustian/">Instagram</a></li>
					<li><a href="https://www.tiktok.com/@101bisnis">TikTok</a></li>
				</ul>
			</div>
		</div>
		<div class="legal">
			<span>&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> PT MEA Digital Marketing</span>
			<span><?php esc_html_e( 'Bagian dari MEA Grup', 'mea-orbit' ); ?></span>
		</div>
	</div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
