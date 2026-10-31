<?php
/**
 * Field konten halaman Orbit (layanan, studi kasus).
 *
 * Satu definisi dipakai untuk register_post_meta (show_in_rest, bisa diisi lewat
 * konektor), sanitasi, meta box wp-admin, dan pembacaan nilai. Semua meta memakai
 * prefix `mea_`. Isi awal halaman 14144 dan 14512 ada di docs/seed/.
 *
 * Tipe field: text, textarea, html, int, bool, media, page, quote, select,
 * lines (daftar teks), pages (daftar ID halaman), repeater (daftar objek),
 * object (satu objek dengan sub-field tetap).
 *
 * @package MEA_Orbit
 */

defined( 'ABSPATH' ) || exit;

/**
 * Definisi field per template.
 *
 * @return array<string, array<string, array<string, mixed>>> template slug => (kunci => definisi).
 */
function mea_orbit_field_defs() {
	$layanan = array(
		'judul_pendek'     => array(
			'type'  => 'text',
			'label' => __( 'Judul pendek (breadcrumb dan schema Service)', 'mea-orbit' ),
		),
		'h1'               => array(
			'type'  => 'text',
			'label' => __( 'H1', 'mea-orbit' ),
		),
		'lede'             => array(
			'type'  => 'textarea',
			'label' => __( 'Lede di bawah H1', 'mea-orbit' ),
		),
		'foto_hero_id'     => array(
			'type'  => 'media',
			'label' => __( 'Foto hero', 'mea-orbit' ),
		),
		'foto_hero_alt'    => array(
			'type'  => 'text',
			'label' => __( 'Alt foto hero (kosong = alt di Media Library)', 'mea-orbit' ),
		),
		'cocok'            => array(
			'type'  => 'lines',
			'label' => __( 'Cocok jika (satu poin per baris)', 'mea-orbit' ),
		),
		'belum_cocok'      => array(
			'type'  => 'lines',
			'label' => __( 'Belum cocok jika (satu poin per baris)', 'mea-orbit' ),
		),
		'dikerjakan_lede'  => array(
			'type'  => 'textarea',
			'label' => __( 'Lede "Yang dikerjakan tim kami"', 'mea-orbit' ),
		),
		'deliverables'     => array(
			'type'   => 'repeater',
			'label'  => __( 'Deliverables', 'mea-orbit' ),
			'fields' => array(
				'judul'     => array(
					'type'  => 'text',
					'label' => __( 'Judul', 'mea-orbit' ),
				),
				'deskripsi' => array(
					'type'  => 'textarea',
					'label' => __( 'Deskripsi', 'mea-orbit' ),
				),
			),
		),
		'langkah'          => array(
			'type'   => 'repeater',
			'label'  => __( 'Cara kerja: 5 langkah (versi ringkas)', 'mea-orbit' ),
			'fields' => array(
				'judul'     => array(
					'type'  => 'text',
					'label' => __( 'Judul', 'mea-orbit' ),
				),
				'deskripsi' => array(
					'type'  => 'textarea',
					'label' => __( 'Deskripsi', 'mea-orbit' ),
				),
			),
		),
		'kutipan_id'       => array(
			'type'    => 'quote',
			'label'   => __( 'Kutipan setelah Cara kerja', 'mea-orbit' ),
			'default' => 'k4',
		),
		'kasus_terkait_id' => array(
			'type'  => 'page',
			'label' => __( 'Studi kasus terkait (ID halaman)', 'mea-orbit' ),
		),
		'bukti_judul'      => array(
			'type'  => 'text',
			'label' => __( 'Judul bagian bukti', 'mea-orbit' ),
		),
		'bukti_angka'      => array(
			'type'  => 'lines',
			'label' => __( 'Angka bukti: label angka di studi kasus, berurutan (satu per baris)', 'mea-orbit' ),
		),
		'faq'              => array(
			'type'   => 'repeater',
			'label'  => __( 'FAQ (juga jadi schema FAQPage)', 'mea-orbit' ),
			'fields' => array(
				'tanya' => array(
					'type'  => 'text',
					'label' => __( 'Pertanyaan', 'mea-orbit' ),
				),
				'jawab' => array(
					'type'  => 'textarea',
					'label' => __( 'Jawaban', 'mea-orbit' ),
				),
			),
		),
		'layanan_terkait'  => array(
			'type'   => 'repeater',
			'label'  => __( 'Layanan terkait (opsional; kosong = section tidak tampil)', 'mea-orbit' ),
			'fields' => array(
				'label'   => array(
					'type'  => 'text',
					'label' => __( 'Label (kosong = judul halaman)', 'mea-orbit' ),
				),
				'page_id' => array(
					'type'  => 'page',
					'label' => __( 'Halaman layanan (ID)', 'mea-orbit' ),
				),
			),
		),
		'hub_layanan'      => array(
			'type'  => 'bool',
			'label' => __( 'Tampilkan daftar semua layanan (hub, untuk /services/)', 'mea-orbit' ),
		),
		'service_type'     => array(
			'type'  => 'text',
			'label' => __( 'serviceType di schema Service', 'mea-orbit' ),
		),
		'sumber_form'      => array(
			'type'  => 'text',
			'label' => __( 'Sumber form (kosong = layanan-<slug>)', 'mea-orbit' ),
		),
	);

	$studi = array(
		'brand'          => array(
			'type'  => 'text',
			'label' => __( 'Nama brand', 'mea-orbit' ),
		),
		'h1'             => array(
			'type'  => 'text',
			'label' => __( 'H1 (juga headline schema Article)', 'mea-orbit' ),
		),
		'kartu_judul'    => array(
			'type'  => 'text',
			'label' => __( 'Judul kartu di homepage', 'mea-orbit' ),
		),
		'kartu_lede'     => array(
			'type'  => 'textarea',
			'label' => __( 'Ringkasan kartu di homepage', 'mea-orbit' ),
		),
		'kategori'       => array(
			'type'  => 'text',
			'label' => __( 'Kategori', 'mea-orbit' ),
		),
		'kanal'          => array(
			'type'  => 'text',
			'label' => __( 'Kanal', 'mea-orbit' ),
		),
		'periode'        => array(
			'type'  => 'text',
			'label' => __( 'Periode', 'mea-orbit' ),
		),
		'layanan'        => array(
			'type'   => 'repeater',
			'label'  => __( 'Layanan yang dipakai', 'mea-orbit' ),
			'fields' => array(
				'label'   => array(
					'type'  => 'text',
					'label' => __( 'Label', 'mea-orbit' ),
				),
				'page_id' => array(
					'type'  => 'page',
					'label' => __( 'Halaman layanan (opsional, ID)', 'mea-orbit' ),
				),
			),
		),
		'angka'          => array(
			'type'   => 'repeater',
			'label'  => __( 'Angka hasil', 'mea-orbit' ),
			'fields' => array(
				'nilai'          => array(
					'type'  => 'text',
					'label' => __( 'Nilai tampil (mis. Rp 8,2 miliar)', 'mea-orbit' ),
				),
				'label'          => array(
					'type'  => 'text',
					'label' => __( 'Label', 'mea-orbit' ),
				),
				'tampil_beranda' => array(
					'type'  => 'bool',
					'label' => __( 'Tampil di homepage', 'mea-orbit' ),
				),
				'nilai_beranda'  => array(
					'type'  => 'text',
					'label' => __( 'Nilai di homepage (kosong = sama)', 'mea-orbit' ),
				),
				'label_beranda'  => array(
					'type'  => 'text',
					'label' => __( 'Label di homepage (kosong = sama)', 'mea-orbit' ),
				),
			),
		),
		'komposisi'      => array(
			'type'   => 'object',
			'label'  => __( 'Komposisi GMV (opsional)', 'mea-orbit' ),
			'fields' => array(
				'label_a'  => array(
					'type'  => 'text',
					'label' => __( 'Label bagian A', 'mea-orbit' ),
				),
				'persen_a' => array(
					'type'  => 'int',
					'label' => __( 'Persen A', 'mea-orbit' ),
				),
				'label_b'  => array(
					'type'  => 'text',
					'label' => __( 'Label bagian B', 'mea-orbit' ),
				),
				'persen_b' => array(
					'type'  => 'int',
					'label' => __( 'Persen B', 'mea-orbit' ),
				),
			),
		),
		'tantangan'      => array(
			'type'  => 'html',
			'label' => __( 'Tantangannya', 'mea-orbit' ),
		),
		'dikerjakan'     => array(
			'type'  => 'html',
			'label' => __( 'Yang kami kerjakan', 'mea-orbit' ),
		),
		'hasil'          => array(
			'type'  => 'html',
			'label' => __( 'Hasilnya', 'mea-orbit' ),
		),
		'kutipan_id'     => array(
			'type'    => 'quote',
			'label'   => __( 'Kutipan setelah Hasil', 'mea-orbit' ),
			'default' => 'k2',
		),
		'foto_proses'    => array(
			'type'   => 'repeater',
			'label'  => __( 'Foto proses kerja tim', 'mea-orbit' ),
			'fields' => array(
				'media_id'   => array(
					'type'  => 'media',
					'label' => __( 'Foto', 'mea-orbit' ),
				),
				'keterangan' => array(
					'type'  => 'text',
					'label' => __( 'Keterangan', 'mea-orbit' ),
				),
				'alt'        => array(
					'type'  => 'text',
					'label' => __( 'Alt (kosong = alt di Media Library)', 'mea-orbit' ),
				),
			),
		),
		'galeri'         => array(
			'type'   => 'repeater',
			'label'  => __( 'Galeri (foto produk, toko, hasil; kosong = section tidak tampil)', 'mea-orbit' ),
			'fields' => array(
				'media_id'   => array(
					'type'  => 'media',
					'label' => __( 'Foto', 'mea-orbit' ),
				),
				'keterangan' => array(
					'type'  => 'text',
					'label' => __( 'Keterangan', 'mea-orbit' ),
				),
				'alt'        => array(
					'type'  => 'text',
					'label' => __( 'Alt (kosong = alt di Media Library)', 'mea-orbit' ),
				),
			),
		),
		'kasus_lain'     => array(
			'type'  => 'pages',
			'label' => __( 'Studi kasus lain (ID halaman, dipisah koma)', 'mea-orbit' ),
		),
		'cdps_client_id' => array(
			'type'  => 'text',
			'label' => __( 'cdps_client_id (opsional)', 'mea-orbit' ),
		),
		'izin_publikasi' => array(
			'type'    => 'bool',
			'label'   => __( 'Izin publikasi angka aktif', 'mea-orbit' ),
			'default' => true,
		),
		'sumber_form'    => array(
			'type'  => 'text',
			'label' => __( 'Sumber form (kosong = studi-kasus-<slug>)', 'mea-orbit' ),
		),
	);

	return array(
		'page-templates/template-layanan.php'     => $layanan,
		'page-templates/template-studi-kasus.php' => $studi,
	);
}

/**
 * Semua definisi field, digabung lintas template (kunci unik per meta).
 *
 * @return array<string, array<string, mixed>>
 */
function mea_orbit_all_field_defs() {
	$all = array();
	foreach ( mea_orbit_field_defs() as $defs ) {
		$all = array_merge( $all, $defs );
	}
	return $all;
}

/**
 * Nilai kosong untuk sebuah tipe field.
 *
 * @param array<string, mixed> $def Definisi field.
 * @return mixed
 */
function mea_orbit_field_empty( $def ) {
	switch ( $def['type'] ) {
		case 'int':
		case 'media':
		case 'page':
			return 0;
		case 'bool':
			return false;
		case 'lines':
		case 'pages':
		case 'repeater':
			return array();
		case 'object':
			$out = array();
			foreach ( $def['fields'] as $key => $sub ) {
				$out[ $key ] = mea_orbit_field_empty( $sub );
			}
			return $out;
		default:
			return '';
	}
}

/**
 * Skema REST (JSON Schema) untuk sebuah field.
 *
 * @param array<string, mixed> $def Definisi field.
 * @return array<string, mixed>
 */
function mea_orbit_field_schema( $def ) {
	switch ( $def['type'] ) {
		case 'int':
		case 'media':
		case 'page':
			return array( 'type' => 'integer' );
		case 'bool':
			return array( 'type' => 'boolean' );
		case 'lines':
			return array(
				'type'  => 'array',
				'items' => array( 'type' => 'string' ),
			);
		case 'pages':
			return array(
				'type'  => 'array',
				'items' => array( 'type' => 'integer' ),
			);
		case 'repeater':
		case 'object':
			$props = array();
			foreach ( $def['fields'] as $key => $sub ) {
				$props[ $key ] = mea_orbit_field_schema( $sub );
			}
			$object = array(
				'type'                 => 'object',
				'properties'           => $props,
				'additionalProperties' => false,
			);
			return 'object' === $def['type'] ? $object : array(
				'type'  => 'array',
				'items' => $object,
			);
		default:
			return array( 'type' => 'string' );
	}
}

/**
 * Tag HTML yang boleh dipakai di field bertipe html.
 *
 * @return array<string, array<string, bool>>
 */
function mea_orbit_allowed_html() {
	return array(
		'p'      => array(),
		'br'     => array(),
		'strong' => array(),
		'em'     => array(),
		'ul'     => array( 'class' => true ),
		'ol'     => array(),
		'li'     => array(),
		'a'      => array(
			'href'   => true,
			'target' => true,
			'rel'    => true,
		),
	);
}

/**
 * Sanitasi nilai sesuai tipe field.
 *
 * @param mixed                $value Nilai masuk.
 * @param array<string, mixed> $def   Definisi field.
 * @return mixed
 */
function mea_orbit_sanitize_field( $value, $def ) {
	switch ( $def['type'] ) {
		case 'int':
		case 'media':
		case 'page':
			return absint( $value );
		case 'bool':
			return in_array( $value, array( true, 1, '1', 'true', 'on', 'yes' ), true );
		case 'textarea':
			return sanitize_textarea_field( (string) $value );
		case 'html':
			return wp_kses( (string) $value, mea_orbit_allowed_html() );
		case 'lines':
			if ( is_string( $value ) ) {
				$value = preg_split( '/\r\n|\r|\n/', $value );
			}
			return array_values( array_filter( array_map( 'sanitize_text_field', (array) $value ), 'strlen' ) );
		case 'pages':
			if ( is_string( $value ) ) {
				$value = explode( ',', $value );
			}
			return array_values( array_filter( array_map( 'absint', (array) $value ) ) );
		case 'object':
			$value = is_array( $value ) ? $value : array();
			$out   = array();
			foreach ( $def['fields'] as $key => $sub ) {
				$out[ $key ] = mea_orbit_sanitize_field( $value[ $key ] ?? mea_orbit_field_empty( $sub ), $sub );
			}
			return $out;
		case 'repeater':
			$rows = array();
			foreach ( (array) $value as $row ) {
				if ( ! is_array( $row ) ) {
					continue;
				}
				$clean = mea_orbit_sanitize_field( $row, array_merge( $def, array( 'type' => 'object' ) ) );
				if ( array_filter( $clean ) ) {
					$rows[] = $clean;
				}
			}
			return $rows;
		case 'quote':
		case 'select':
			return sanitize_key( (string) $value );
		default:
			return sanitize_text_field( (string) $value );
	}
}

/**
 * Daftarkan semua meta halaman Orbit.
 *
 * @return void
 */
function mea_orbit_register_meta() {
	foreach ( mea_orbit_all_field_defs() as $key => $def ) {
		register_post_meta(
			'page',
			'mea_' . $key,
			array(
				'type'              => mea_orbit_field_schema( $def )['type'],
				'single'            => true,
				'default'           => $def['default'] ?? mea_orbit_field_empty( $def ),
				'show_in_rest'      => array( 'schema' => mea_orbit_field_schema( $def ) ),
				'sanitize_callback' => static function ( $value ) use ( $def ) {
					return mea_orbit_sanitize_field( $value, $def );
				},
				'auth_callback'     => static function ( $allowed, $meta_key, $post_id ) {
					return current_user_can( 'edit_post', $post_id );
				},
			)
		);
	}
}
add_action( 'init', 'mea_orbit_register_meta' );

/**
 * Baca satu field halaman (default terdaftar dipakai bila kosong).
 *
 * @param string $key     Kunci tanpa prefix `mea_`.
 * @param int    $post_id ID halaman. 0 = halaman saat ini.
 * @return mixed
 */
function mea_orbit_meta( $key, $post_id = 0 ) {
	$post_id = $post_id ? $post_id : get_queried_object_id();
	return get_post_meta( $post_id, 'mea_' . $key, true );
}

/**
 * Meta box per template. Muncul setelah template halaman dipilih dan disimpan.
 *
 * @param string  $post_type Post type.
 * @param WP_Post $post      Halaman yang diedit.
 * @return void
 */
function mea_orbit_add_meta_boxes( $post_type, $post ) {
	if ( 'page' !== $post_type ) {
		return;
	}

	$template = get_page_template_slug( $post );
	$defs     = mea_orbit_field_defs();
	if ( ! isset( $defs[ $template ] ) ) {
		return;
	}

	add_meta_box(
		'mea-orbit-fields',
		__( 'Konten MEA Orbit', 'mea-orbit' ),
		'mea_orbit_render_meta_box',
		'page',
		'normal',
		'high',
		array( 'defs' => $defs[ $template ] )
	);
}
add_action( 'add_meta_boxes', 'mea_orbit_add_meta_boxes', 10, 2 );

/**
 * Isi meta box.
 *
 * @param WP_Post              $post Halaman.
 * @param array<string, mixed> $box  Argumen meta box.
 * @return void
 */
function mea_orbit_render_meta_box( $post, $box ) {
	wp_nonce_field( 'mea_orbit_fields', 'mea_orbit_fields_nonce' );
	echo '<div class="mea-fields">';
	foreach ( $box['args']['defs'] as $key => $def ) {
		mea_orbit_render_field( 'mea_meta[' . $key . ']', $def, get_post_meta( $post->ID, 'mea_' . $key, true ) );
	}
	echo '</div>';
}

/**
 * Simpan meta dari meta box.
 *
 * @param int $post_id ID halaman.
 * @return void
 */
function mea_orbit_save_meta_box( $post_id ) {
	if ( ! isset( $_POST['mea_orbit_fields_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['mea_orbit_fields_nonce'] ) ), 'mea_orbit_fields' ) ) {
		return;
	}
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$template = get_page_template_slug( $post_id );
	$defs     = mea_orbit_field_defs();
	if ( ! isset( $defs[ $template ] ) ) {
		return;
	}

	// Nilai disanitasi per field oleh sanitize_callback yang didaftarkan di register_post_meta.
	$input = isset( $_POST['mea_meta'] ) && is_array( $_POST['mea_meta'] ) ? wp_unslash( $_POST['mea_meta'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	foreach ( $defs[ $template ] as $key => $def ) {
		$raw = $input[ $key ] ?? mea_orbit_field_empty( $def );
		if ( 'repeater' === $def['type'] && is_array( $raw ) ) {
			unset( $raw['__i__'] );
		}
		update_post_meta( $post_id, 'mea_' . $key, $raw );
	}
}
add_action( 'save_post_page', 'mea_orbit_save_meta_box' );

/**
 * Cetak satu kontrol field (dipakai meta box dan halaman opsi).
 *
 * @param string               $name  Nama input.
 * @param array<string, mixed> $def   Definisi field.
 * @param mixed                $value Nilai sekarang.
 * @return void
 */
function mea_orbit_render_field( $name, $def, $value ) {
	$id = 'f-' . md5( $name );
	echo '<div class="mea-field mea-field-' . esc_attr( $def['type'] ) . '">';
	if ( 'bool' !== $def['type'] ) {
		echo '<label class="mea-label" for="' . esc_attr( $id ) . '">' . esc_html( $def['label'] ) . '</label>';
	}

	switch ( $def['type'] ) {
		case 'textarea':
		case 'html':
			printf( '<textarea id="%1$s" name="%2$s" rows="%3$d" class="large-text">%4$s</textarea>', esc_attr( $id ), esc_attr( $name ), 'html' === $def['type'] ? 8 : 3, esc_textarea( (string) $value ) );
			if ( 'html' === $def['type'] ) {
				echo '<p class="description">' . esc_html__( 'HTML yang boleh: p, ul, li, strong, em, a, br.', 'mea-orbit' ) . '</p>';
			}
			break;
		case 'lines':
			printf( '<textarea id="%1$s" name="%2$s" rows="4" class="large-text">%3$s</textarea>', esc_attr( $id ), esc_attr( $name ), esc_textarea( implode( "\n", (array) $value ) ) );
			break;
		case 'pages':
			printf( '<input id="%1$s" type="text" name="%2$s" value="%3$s" class="regular-text">', esc_attr( $id ), esc_attr( $name ), esc_attr( implode( ',', (array) $value ) ) );
			break;
		case 'int':
		case 'page':
			printf( '<input id="%1$s" type="number" min="0" name="%2$s" value="%3$s" class="small-text">', esc_attr( $id ), esc_attr( $name ), esc_attr( (string) absint( $value ) ) );
			if ( 'page' === $def['type'] && $value && get_post( (int) $value ) ) {
				echo ' <span class="description">' . esc_html( get_the_title( (int) $value ) ) . '</span>';
			}
			break;
		case 'media':
			$library = $def['library'] ?? 'image';
			$thumb   = $value ? wp_get_attachment_image( (int) $value, 'thumbnail' ) : '';
			if ( $value && 'image' !== $library ) {
				$thumb = esc_html( wp_basename( (string) get_attached_file( (int) $value ) ) );
			}
			printf(
				'<span class="mea-media"><input id="%1$s" type="number" min="0" name="%2$s" value="%3$s" class="small-text mea-media-id"> <button type="button" class="button mea-media-pick" data-library="%6$s">%4$s</button> <span class="mea-media-thumb">%5$s</span></span>',
				esc_attr( $id ),
				esc_attr( $name ),
				esc_attr( (string) absint( $value ) ),
				'video' === $library ? esc_html__( 'Pilih video', 'mea-orbit' ) : esc_html__( 'Pilih gambar', 'mea-orbit' ),
				wp_kses_post( $thumb ),
				esc_attr( $library )
			);
			break;
		case 'bool':
			printf( '<label><input type="hidden" name="%1$s" value="0"><input id="%2$s" type="checkbox" name="%1$s" value="1"%3$s> %4$s</label>', esc_attr( $name ), esc_attr( $id ), checked( (bool) $value, true, false ), esc_html( $def['label'] ) );
			break;
		case 'quote':
			echo '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '"><option value="">' . esc_html__( 'Tanpa kutipan', 'mea-orbit' ) . '</option>';
			foreach ( mea_orbit_quotes() as $quote ) {
				printf( '<option value="%1$s"%2$s>%1$s: %3$s</option>', esc_attr( $quote['id'] ), selected( $value, $quote['id'], false ), esc_html( wp_trim_words( $quote['teks'], 10 ) ) );
			}
			echo '</select>';
			break;
		case 'select':
			echo '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '">';
			foreach ( $def['options'] as $option ) {
				printf( '<option value="%1$s"%2$s>%1$s</option>', esc_attr( $option ), selected( $value, $option, false ) );
			}
			echo '</select>';
			break;
		case 'object':
			echo '<div class="mea-object">';
			foreach ( $def['fields'] as $key => $sub ) {
				mea_orbit_render_field( $name . '[' . $key . ']', $sub, $value[ $key ] ?? mea_orbit_field_empty( $sub ) );
			}
			echo '</div>';
			break;
		case 'repeater':
			echo '<div class="mea-repeater" data-name="' . esc_attr( $name ) . '"><ol class="mea-rows">';
			foreach ( array_values( (array) $value ) as $i => $row ) {
				mea_orbit_render_repeater_row( $name . '[' . $i . ']', $def, $row );
			}
			echo '</ol><template>';
			mea_orbit_render_repeater_row( $name . '[__i__]', $def, array() );
			echo '</template><button type="button" class="button mea-row-add">' . esc_html__( 'Tambah baris', 'mea-orbit' ) . '</button></div>';
			break;
		default:
			printf( '<input id="%1$s" type="text" name="%2$s" value="%3$s" class="large-text">', esc_attr( $id ), esc_attr( $name ), esc_attr( (string) $value ) );
	}
	echo '</div>';
}

/**
 * Satu baris repeater.
 *
 * @param string               $name Nama input baris.
 * @param array<string, mixed> $def  Definisi repeater.
 * @param array<string, mixed> $row  Nilai baris.
 * @return void
 */
function mea_orbit_render_repeater_row( $name, $def, $row ) {
	echo '<li class="mea-row"><div class="mea-row-fields">';
	foreach ( $def['fields'] as $key => $sub ) {
		mea_orbit_render_field( $name . '[' . $key . ']', $sub, $row[ $key ] ?? mea_orbit_field_empty( $sub ) );
	}
	echo '</div><div class="mea-row-tools">';
	echo '<button type="button" class="button-link mea-row-up" aria-label="' . esc_attr__( 'Naikkan', 'mea-orbit' ) . '">&uarr;</button>';
	echo '<button type="button" class="button-link mea-row-down" aria-label="' . esc_attr__( 'Turunkan', 'mea-orbit' ) . '">&darr;</button>';
	echo '<button type="button" class="button-link button-link-delete mea-row-remove">' . esc_html__( 'Hapus', 'mea-orbit' ) . '</button>';
	echo '</div></li>';
}

/**
 * Aset wp-admin untuk meta box dan halaman opsi.
 *
 * @param string $hook Halaman admin.
 * @return void
 */
function mea_orbit_admin_assets( $hook ) {
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php', 'appearance_page_mea-orbit-home' ), true ) ) {
		return;
	}
	wp_enqueue_media();
	wp_enqueue_style( 'mea-orbit-admin', MEA_ORBIT_URI . '/assets/css/admin.css', array(), mea_orbit_asset_version( 'assets/css/admin.css' ) );
	wp_enqueue_script( 'mea-orbit-admin', MEA_ORBIT_URI . '/assets/js/admin-fields.js', array(), mea_orbit_asset_version( 'assets/js/admin-fields.js' ), true );
}
add_action( 'admin_enqueue_scripts', 'mea_orbit_admin_assets' );
