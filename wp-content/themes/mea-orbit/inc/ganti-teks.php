<?php
/**
 * Ganti teks persis di SATU post: `post_content` dan `_elementor_data` (bentuk JSON
 * yang disimpan Elementor). Untuk koreksi kecil artikel Elementor lewat konektor
 * (mis. ganti href satu link) tanpa search-replace seluruh database dan tanpa menulis
 * ulang seluruh data Elementor.
 *
 * `POST /wp-json/mea-orbit/v1/ganti-teks` (pengguna yang boleh mengedit post itu)
 *   { "post_id": 15889, "uji": true, "ganti": [ { "lama": "...", "baru": "..." } ] }
 * - Teks dicocokkan persis (HTML seperti di editor Text). Di `_elementor_data` dicari
 *   bentuk JSON-nya (`\/`, `\"`, `\uXXXX`), sama dengan cara Elementor menyimpan.
 * - Setiap pasangan harus muncul **tepat satu kali** di tiap tempat yang memuatnya, dan
 *   minimal di satu tempat; bila tidak, tidak ada yang diubah.
 * - `uji: true` (bawaan) hanya melaporkan jumlah kecocokan.
 * - Sebelum menulis, nilai lama disimpan di meta `_mea_ganti_teks_cadangan` (5 terakhir).
 *   post_content diubah lewat wp_update_post, jadi revisi WordPress ikut tercatat.
 * Judul, slug, dan meta SEO tidak disentuh.
 *
 * @package MEA_Orbit
 */

defined( 'ABSPATH' ) || exit;

/**
 * Bentuk teks seperti tersimpan di `_elementor_data` (string JSON tanpa tanda kutip luar).
 *
 * @param string $text Teks.
 * @return string
 */
function mea_orbit_ganti_teks_json( $text ) {
	return substr( (string) wp_json_encode( (string) $text ), 1, -1 );
}

/**
 * Hitung kecocokan dan (bila tidak uji) terapkan penggantian pada dua sumber.
 *
 * @param string                                   $content Isi post_content.
 * @param string                                   $data    Isi _elementor_data.
 * @param array<int, array{lama:string, baru:string}> $pairs   Pasangan ganti.
 * @return array{ok:bool, laporan:array<int, array<string, mixed>>, content:string, data:string}
 */
function mea_orbit_ganti_teks_hitung( $content, $data, $pairs ) {
	$ok      = true;
	$laporan = array();
	foreach ( $pairs as $pair ) {
		$lama      = (string) $pair['lama'];
		$baru      = (string) $pair['baru'];
		$lama_json = mea_orbit_ganti_teks_json( $lama );
		$di_konten = '' === $lama ? 0 : substr_count( $content, $lama );
		$di_data   = '' === $lama ? 0 : substr_count( $data, $lama_json );
		$cocok     = ( $di_konten + $di_data ) > 0 && $di_konten <= 1 && $di_data <= 1;
		$ok        = $ok && $cocok;
		$laporan[] = array(
			'lama'           => mb_substr( $lama, 0, 80 ) . ( mb_strlen( $lama ) > 80 ? '…' : '' ),
			'post_content'   => $di_konten,
			'elementor_data' => $di_data,
			'cocok'          => $cocok,
		);
		if ( $cocok ) {
			$content = str_replace( $lama, $baru, $content );
			$data    = str_replace( $lama_json, mea_orbit_ganti_teks_json( $baru ), $data );
		}
	}
	return compact( 'ok', 'laporan', 'content', 'data' );
}

/**
 * Callback REST.
 *
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response|WP_Error
 */
function mea_orbit_ganti_teks_rest( $request ) {
	$post_id = (int) $request['post_id'];
	$post    = get_post( $post_id );
	if ( ! $post ) {
		return new WP_Error( 'mea_post', 'Post tidak ada.', array( 'status' => 404 ) );
	}
	$pairs = array();
	foreach ( (array) $request['ganti'] as $pair ) {
		if ( is_array( $pair ) && isset( $pair['lama'], $pair['baru'] ) && '' !== (string) $pair['lama'] ) {
			$pairs[] = array(
				'lama' => (string) $pair['lama'],
				'baru' => (string) $pair['baru'],
			);
		}
	}
	if ( ! $pairs ) {
		return new WP_Error( 'mea_ganti', 'Daftar ganti kosong.', array( 'status' => 400 ) );
	}
	$uji     = false !== $request['uji'];
	$data    = (string) get_post_meta( $post_id, '_elementor_data', true );
	$hasil   = mea_orbit_ganti_teks_hitung( $post->post_content, $data, $pairs );
	$respons = array(
		'post_id' => $post_id,
		'uji'     => $uji,
		'ok'      => $hasil['ok'],
		'laporan' => $hasil['laporan'],
	);
	if ( $uji || ! $hasil['ok'] ) {
		$respons['diterapkan'] = false;
		return rest_ensure_response( $respons );
	}

	$cadangan   = (array) get_post_meta( $post_id, '_mea_ganti_teks_cadangan', true );
	$cadangan[] = array(
		'waktu'          => current_time( 'mysql' ),
		'post_content'   => $post->post_content,
		'elementor_data' => $data,
	);
	update_post_meta( $post_id, '_mea_ganti_teks_cadangan', array_slice( $cadangan, -5 ) );

	if ( $hasil['content'] !== $post->post_content ) {
		wp_update_post(
			wp_slash(
				array(
					'ID'           => $post_id,
					'post_content' => $hasil['content'],
				)
			)
		);
	}
	if ( '' !== $data && $hasil['data'] !== $data ) {
		update_post_meta( $post_id, '_elementor_data', wp_slash( $hasil['data'] ) );
		// Cache HTML elemen Elementor (bila fitur Element Caching aktif) dibuang.
		delete_post_meta( $post_id, '_elementor_element_cache' );
	}
	$respons['diterapkan'] = true;
	return rest_ensure_response( $respons );
}

/**
 * Daftarkan rute.
 *
 * @return void
 */
function mea_orbit_ganti_teks_routes() {
	register_rest_route(
		'mea-orbit/v1',
		'/ganti-teks',
		array(
			'methods'             => 'POST',
			'callback'            => 'mea_orbit_ganti_teks_rest',
			'permission_callback' => static function ( $request ) {
				return current_user_can( 'edit_post', (int) $request['post_id'] );
			},
			'args'                => array(
				'post_id' => array(
					'type'     => 'integer',
					'required' => true,
				),
				'uji'     => array(
					'type'    => 'boolean',
					'default' => true,
				),
				'ganti'   => array(
					'type'     => 'array',
					'required' => true,
				),
			),
		)
	);
}
add_action( 'rest_api_init', 'mea_orbit_ganti_teks_routes' );
