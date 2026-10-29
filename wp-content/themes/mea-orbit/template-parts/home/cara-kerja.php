<?php
/**
 * Homepage · cara kerja (lima langkah sesuai alur CDPS) + kutipan.
 *
 * @package MEA_Orbit
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="sec" id="cara-kerja" aria-labelledby="judul-proses">
	<div class="wrap">
		<div class="sec-head">
			<h2 id="judul-proses"><?php esc_html_e( 'Cara kami bekerja', 'mea-orbit' ); ?></h2>
			<p class="lede"><?php esc_html_e( 'Setiap kerja sama berjalan dengan urutan yang sama, sehingga Anda selalu tahu apa yang sedang dikerjakan dan kenapa.', 'mea-orbit' ); ?></p>
		</div>
		<?php
		get_template_part(
			'template-parts/global/proses',
			null,
			array(
				'langkah' => array(
					array(
						'judul'     => __( 'Riset awal toko', 'mea-orbit' ),
						'deskripsi' => __( 'Kami membaca data Seller Center Anda sebelum memberi saran apa pun.', 'mea-orbit' ),
					),
					array(
						'judul'     => __( 'Strategi tertulis', 'mea-orbit' ),
						'deskripsi' => __( 'Target, prioritas kanal, dan risiko ditulis lalu disetujui bersama.', 'mea-orbit' ),
					),
					array(
						'judul'     => __( 'Rencana per periode', 'mea-orbit' ),
						'deskripsi' => __( 'Strategi dipecah menjadi rencana kerja bulanan dengan target mingguan.', 'mea-orbit' ),
					),
					array(
						'judul'     => __( 'Eksekusi tim', 'mea-orbit' ),
						'deskripsi' => __( 'Iklan, konten, live, dan toko dikerjakan divisinya masing-masing.', 'mea-orbit' ),
					),
					array(
						'judul'     => __( 'Rekap mingguan', 'mea-orbit' ),
						'deskripsi' => __( 'Hasil minggu lalu dan langkah berikutnya dilaporkan setiap pekan.', 'mea-orbit' ),
					),
				),
				'foto'    => array(
					array(
						'file' => 'langkah-1',
						'alt'  => __( 'Tim MEA Agency membaca laporan toko klien saat riset awal', 'mea-orbit' ),
					),
					array(
						'file' => 'langkah-2',
						'alt'  => __( 'Pemilik bisnis menyetujui strategi tertulis bersama strategist MEA Agency', 'mea-orbit' ),
					),
					array(
						'file' => 'langkah-3',
						'alt'  => __( 'Tim menyusun rencana kerja bulanan dengan target mingguan', 'mea-orbit' ),
					),
					array(
						'file' => 'langkah-4',
						'alt'  => __( 'Operator live dan host menjalankan siaran langsung di studio', 'mea-orbit' ),
					),
					array(
						'file' => 'langkah-5',
						'alt'  => __( 'Account manager melaporkan rekap mingguan kepada klien', 'mea-orbit' ),
					),
				),
			)
		);
		get_template_part( 'template-parts/global/kutipan', null, array( 'id' => mea_orbit_home( 'cara_kerja_kutipan_id' ) ) );
		?>
	</div>
</section>
