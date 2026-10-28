<?php
/**
 * Opsi "Homepage MEA" dan daftar kutipan Yohan (Tampilan → Homepage MEA).
 *
 * Default berisi copy prototipe dan teks kutipan final dari brief 3.4.
 * Kedua opsi terdaftar dengan show_in_rest, jadi bisa dibaca dan diisi lewat
 * /wp/v2/settings oleh administrator.
 *
 * @package MEA_Orbit
 */

defined( 'ABSPATH' ) || exit;

/**
 * Definisi field opsi Homepage.
 *
 * @return array<string, array<string, mixed>>
 */
function mea_orbit_home_defs() {
	$foto = array(
		'media_id'   => array(
			'type'  => 'media',
			'label' => __( 'Foto', 'mea-orbit' ),
		),
		'keterangan' => array(
			'type'  => 'text',
			'label' => __( 'Keterangan (kosong = tanpa keterangan)', 'mea-orbit' ),
		),
		'alt'        => array(
			'type'  => 'text',
			'label' => __( 'Alt', 'mea-orbit' ),
		),
	);

	return array(
		'hero_kicker'           => array(
			'type'    => 'text',
			'label'   => __( 'Hero: teks kecil di atas judul', 'mea-orbit' ),
			'default' => 'Partner resmi TikTok Shop dan Shopee',
		),
		'hero_judul'            => array(
			'type'    => 'text',
			'label'   => __( 'Hero: judul (H1)', 'mea-orbit' ),
			'default' => 'Toko online Anda, kami yang jalankan',
		),
		'hero_lede'             => array(
			'type'    => 'textarea',
			'label'   => __( 'Hero: lede', 'mea-orbit' ),
			'default' => 'Iklan, toko, konten, dan live di Shopee dan TikTok Shop, dikerjakan tim kami untuk brand dengan omzet Rp 250 juta sampai 1 miliar per bulan.',
		),
		'cta_utama'             => array(
			'type'    => 'text',
			'label'   => __( 'Label CTA utama', 'mea-orbit' ),
			'default' => 'Jadwalkan konsultasi',
		),
		'cta_kedua'             => array(
			'type'    => 'text',
			'label'   => __( 'Label CTA kedua', 'mea-orbit' ),
			'default' => 'Lihat studi kasus',
		),
		'kasus_unggulan_id'     => array(
			'type'    => 'page',
			'label'   => __( 'Studi kasus unggulan (ID halaman)', 'mea-orbit' ),
			'default' => 14512,
		),
		'partner_resmi'         => array(
			'type'    => 'repeater',
			'label'   => __( 'Partner resmi (di bawah hero; kosong = section tidak tampil). Badge PNG tampil apa adanya, foto JPG dalam kartu berbingkai', 'mea-orbit' ),
			'fields'  => array(
				'media_id' => array(
					'type'  => 'media',
					'label' => __( 'Gambar badge atau plakat', 'mea-orbit' ),
				),
				'alt'      => array(
					'type'  => 'text',
					'label' => __( 'Alt', 'mea-orbit' ),
				),
				'label'    => array(
					'type'  => 'text',
					'label' => __( 'Label (mis. Shopee-Certified Enablers Program)', 'mea-orbit' ),
				),
				'periode'  => array(
					'type'  => 'text',
					'label' => __( 'Periode (mis. Q3 2026)', 'mea-orbit' ),
				),
				'link'     => array(
					'type'  => 'text',
					'label' => __( 'Link (opsional)', 'mea-orbit' ),
				),
			),
			'default' => array(),
		),
		'pimpinan_foto_id'      => array(
			'type'    => 'media',
			'label'   => __( 'Foto pimpinan', 'mea-orbit' ),
			'default' => 15633,
		),
		'pimpinan_keterangan'   => array(
			'type'    => 'text',
			'label'   => __( 'Keterangan foto pimpinan', 'mea-orbit' ),
			'default' => 'Yohan Agustian (CEO) dan Nerissa (COO)',
		),
		'pimpinan_alt'          => array(
			'type'    => 'text',
			'label'   => __( 'Alt foto pimpinan', 'mea-orbit' ),
			'default' => 'Yohan Agustian, CEO, dan Nerissa, COO MEA Agency, di kantor MEA',
		),
		'pimpinan_kutipan_id'   => array(
			'type'    => 'quote',
			'label'   => __( 'Kutipan di blok pimpinan', 'mea-orbit' ),
			'default' => 'k1',
		),
		'mosaik'                => array(
			'type'    => 'repeater',
			'label'   => __( 'Mosaik foto tim (5 foto, urutan sesuai tata letak)', 'mea-orbit' ),
			'fields'  => $foto,
			'default' => array(
				array(
					'media_id'   => 15630,
					'keterangan' => 'Sesi kerja tim',
					'alt'        => 'Tim MEA Agency saat sesi kerja',
				),
				array(
					'media_id'   => 15642,
					'keterangan' => '',
					'alt'        => 'Tim MEA Agency',
				),
				array(
					'media_id'   => 16518,
					'keterangan' => 'Studio konten dan live',
					'alt'        => 'Studio produksi konten dan live MEA Agency',
				),
				array(
					'media_id'   => 16517,
					'keterangan' => '',
					'alt'        => 'Studio live streaming MEA Agency',
				),
				array(
					'media_id'   => 16519,
					'keterangan' => '',
					'alt'        => 'Studio produksi MEA Agency',
				),
			),
		),
		'layanan'               => array(
			'type'    => 'repeater',
			'label'   => __( 'Enam layanan', 'mea-orbit' ),
			'fields'  => array(
				'nama'      => array(
					'type'  => 'text',
					'label' => __( 'Nama', 'mea-orbit' ),
				),
				'deskripsi' => array(
					'type'  => 'textarea',
					'label' => __( 'Deskripsi', 'mea-orbit' ),
				),
				'url'       => array(
					'type'  => 'text',
					'label' => __( 'URL (relatif, mis. /affiliate/)', 'mea-orbit' ),
				),
				'motion'    => array(
					'type'    => 'select',
					'label'   => __( 'Loop Enam Sisi', 'mea-orbit' ),
					'options' => array( 'iklan', 'tiktok-shop', 'shopee', 'live', 'affiliate', 'konten' ),
				),
			),
			'default' => array(
				array(
					'nama'      => 'Iklan',
					'deskripsi' => 'Shopee Ads dan TikTok Shop Ads yang diatur dari data penjualan, bukan dari tebakan.',
					'url'       => '/jasa-performance-ads/',
					'motion'    => 'iklan',
				),
				array(
					'nama'      => 'TikTok Shop',
					'deskripsi' => 'Pengelolaan toko harian, katalog, promo, dan kampanye platform.',
					'url'       => '/jasa-kelola-tiktok-shop/',
					'motion'    => 'tiktok-shop',
				),
				array(
					'nama'      => 'Shopee',
					'deskripsi' => 'Optimasi toko, produk, dan program Shopee untuk menaikkan konversi.',
					'url'       => '/ecommerce-service/',
					'motion'    => 'shopee',
				),
				array(
					'nama'      => 'Live',
					'deskripsi' => 'Host, studio, dan jadwal live yang kami siapkan untuk toko Anda.',
					'url'       => '/jasa-live-streaming/',
					'motion'    => 'live',
				),
				array(
					'nama'      => 'Affiliate',
					'deskripsi' => 'Merekrut dan mengaktifkan affiliator yang sesuai dengan produk Anda.',
					'url'       => '/affiliate/',
					'motion'    => 'affiliate',
				),
				array(
					'nama'      => 'Konten',
					'deskripsi' => 'Produksi video dan materi iklan yang dirancang untuk dijual, bukan sekadar ditonton.',
					'url'       => '/jasa-konten/',
					'motion'    => 'konten',
				),
			),
		),
		'cara_kerja_kutipan_id' => array(
			'type'    => 'quote',
			'label'   => __( 'Kutipan setelah Cara kerja', 'mea-orbit' ),
			'default' => 'k5',
		),
	);
}

/**
 * Definisi field daftar kutipan.
 *
 * @return array<string, mixed>
 */
function mea_orbit_quote_def() {
	$atribusi = 'Yohan Agustian, CEO MEA Agency';

	return array(
		'type'    => 'repeater',
		'label'   => __( 'Kutipan Yohan (teks final, jangan diedit selain typo)', 'mea-orbit' ),
		'fields'  => array(
			'id'         => array(
				'type'    => 'select',
				'label'   => __( 'ID', 'mea-orbit' ),
				'options' => array( 'k1', 'k2', 'k3', 'k4', 'k5', 'k6', 'k7', 'k8' ),
			),
			'teks'       => array(
				'type'  => 'textarea',
				'label' => __( 'Teks', 'mea-orbit' ),
			),
			'atribusi'   => array(
				'type'  => 'text',
				'label' => __( 'Atribusi', 'mea-orbit' ),
			),
			'penempatan' => array(
				'type'  => 'text',
				'label' => __( 'Penempatan (catatan)', 'mea-orbit' ),
			),
		),
		'default' => array(
			array(
				'id'         => 'k1',
				'teks'       => 'Gw mulai jualan online 2013, di zaman orang belum ngeuh masuk ke situ. MEA Agency lahir dari pengalaman itu: pemilik bisnis harusnya memimpin, bukan ngerjain semua kerjaan receh.',
				'atribusi'   => $atribusi,
				'penempatan' => 'Homepage, blok pimpinan',
			),
			array(
				'id'         => 'k2',
				'teks'       => 'Pernah gak kepikiran, kalau lu cuma laku karena perang harga, gimana kalau harganya udah gak bisa turun lagi? Yang kami bangun growth yang tetep ada untungnya.',
				'atribusi'   => $atribusi,
				'penempatan' => 'Template studi kasus, setelah bagian Hasil',
			),
			array(
				'id'         => 'k3',
				'teks'       => 'MEA lahir di masa pandemi, saat banyak bisnis harus pindah ke online dalam hitungan minggu. Tugas kami sejak itu sama: bantuin kamu jualan online tanpa harus urusin sendiri.',
				'atribusi'   => $atribusi,
				'penempatan' => 'Halaman About Us (template berikutnya)',
			),
			array(
				'id'         => 'k4',
				'teks'       => 'Kami tidak memberi saran sebelum membaca data toko. Keputusan yang baik dimulai dari angka, bukan dari tebakan.',
				'atribusi'   => $atribusi,
				'penempatan' => 'Template layanan, setelah Cara kerja',
			),
			array(
				'id'         => 'k5',
				'teks'       => 'Gw dan tim sudah mendampingi lebih dari 3.000 UMKM. Pelajarannya selalu sama: yang bertahan adalah yang tumbuh sehat, bukan yang paling pengen ngebut.',
				'atribusi'   => $atribusi,
				'penempatan' => 'Homepage, setelah Cara kerja',
			),
		),
	);
}

/**
 * Nilai default opsi Homepage.
 *
 * @return array<string, mixed>
 */
function mea_orbit_home_defaults() {
	$out = array();
	foreach ( mea_orbit_home_defs() as $key => $def ) {
		$out[ $key ] = $def['default'] ?? mea_orbit_field_empty( $def );
	}
	return $out;
}

/**
 * Sanitasi opsi Homepage.
 *
 * @param mixed $value Nilai masuk.
 * @return array<string, mixed>
 */
function mea_orbit_sanitize_home( $value ) {
	$value = is_array( $value ) ? $value : array();
	$out   = array();
	foreach ( mea_orbit_home_defs() as $key => $def ) {
		$raw = $value[ $key ] ?? mea_orbit_field_empty( $def );
		if ( 'repeater' === $def['type'] && is_array( $raw ) ) {
			unset( $raw['__i__'] );
		}
		$out[ $key ] = mea_orbit_sanitize_field( $raw, $def );
	}
	return $out;
}

/**
 * Sanitasi daftar kutipan.
 *
 * @param mixed $value Nilai masuk.
 * @return array<int, array<string, string>>
 */
function mea_orbit_sanitize_quotes( $value ) {
	if ( is_array( $value ) ) {
		unset( $value['__i__'] );
	}
	return mea_orbit_sanitize_field( $value, mea_orbit_quote_def() );
}

/**
 * Daftarkan kedua opsi.
 *
 * @return void
 */
function mea_orbit_register_settings() {
	$home_schema = array(
		'type'       => 'object',
		'properties' => array(),
	);
	foreach ( mea_orbit_home_defs() as $key => $def ) {
		$home_schema['properties'][ $key ] = mea_orbit_field_schema( $def );
	}

	register_setting(
		'mea_orbit_home_group',
		'mea_orbit_home',
		array(
			'type'              => 'object',
			'default'           => mea_orbit_home_defaults(),
			'sanitize_callback' => 'mea_orbit_sanitize_home',
			'show_in_rest'      => array( 'schema' => $home_schema ),
		)
	);

	register_setting(
		'mea_orbit_home_group',
		'mea_orbit_kutipan',
		array(
			'type'              => 'array',
			'default'           => mea_orbit_quote_def()['default'],
			'sanitize_callback' => 'mea_orbit_sanitize_quotes',
			'show_in_rest'      => array( 'schema' => mea_orbit_field_schema( mea_orbit_quote_def() ) ),
		)
	);
}
add_action( 'init', 'mea_orbit_register_settings' );

/**
 * Opsi Homepage, digabung dengan default untuk kunci yang belum ada.
 *
 * @param string $key Kunci opsi. Kosong = seluruh opsi.
 * @return mixed
 */
function mea_orbit_home( $key = '' ) {
	$value = wp_parse_args( (array) get_option( 'mea_orbit_home', array() ), mea_orbit_home_defaults() );
	return '' === $key ? $value : ( $value[ $key ] ?? null );
}

/**
 * Semua kutipan.
 *
 * @return array<int, array<string, string>>
 */
function mea_orbit_quotes() {
	$quotes = get_option( 'mea_orbit_kutipan', mea_orbit_quote_def()['default'] );
	return is_array( $quotes ) && $quotes ? $quotes : mea_orbit_quote_def()['default'];
}

/**
 * Satu kutipan berdasarkan ID.
 *
 * @param string $id ID kutipan (k1…k5).
 * @return array<string, string>|null
 */
function mea_orbit_quote( $id ) {
	foreach ( mea_orbit_quotes() as $quote ) {
		if ( $id && $quote['id'] === $id && '' !== trim( $quote['teks'] ) ) {
			return $quote;
		}
	}
	return null;
}

/**
 * Menu Tampilan → Homepage MEA.
 *
 * @return void
 */
function mea_orbit_options_menu() {
	add_theme_page( __( 'Homepage MEA', 'mea-orbit' ), __( 'Homepage MEA', 'mea-orbit' ), 'manage_options', 'mea-orbit-home', 'mea_orbit_render_options_page' );
}
add_action( 'admin_menu', 'mea_orbit_options_menu' );

/**
 * Isi halaman opsi.
 *
 * @return void
 */
function mea_orbit_render_options_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$home = mea_orbit_home();
	echo '<div class="wrap"><h1>' . esc_html__( 'Homepage MEA', 'mea-orbit' ) . '</h1>';
	echo '<p>' . esc_html__( 'Isi homepage (front-page.php) dan daftar kutipan yang dipilih per template. Angka bukti di homepage diambil dari studi kasus unggulan, bukan diketik di sini.', 'mea-orbit' ) . '</p>';
	settings_errors();
	echo '<form method="post" action="options.php" class="mea-fields">';
	settings_fields( 'mea_orbit_home_group' );
	foreach ( mea_orbit_home_defs() as $key => $def ) {
		mea_orbit_render_field( 'mea_orbit_home[' . $key . ']', $def, $home[ $key ] );
	}
	echo '<h2>' . esc_html__( 'Kutipan', 'mea-orbit' ) . '</h2>';
	mea_orbit_render_field( 'mea_orbit_kutipan', mea_orbit_quote_def(), mea_orbit_quotes() );
	submit_button();
	echo '</form></div>';
}
