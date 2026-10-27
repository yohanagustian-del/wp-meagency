<?php
/**
 * Endpoint form:
 * - GET  /wp-json/mea-leads/v1/token  nonce segar (no-store), supaya
 *   form di halaman yang di-cache tidak kedaluwarsa.
 * - POST /wp-json/mea-leads/v1/kirim  isian form (JavaScript). Sukses membawa event_id
 *   (idempotency_key lead) untuk dataLayer.
 * - POST /wp-admin/admin-post.php?action=mea_leads_kirim  isian form tanpa JavaScript.
 *
 * @package MEA_Leads
 */

defined( 'ABSPATH' ) || exit;

/**
 * Daftarkan route REST.
 *
 * @return void
 */
function mea_leads_register_routes() {
	register_rest_route(
		'mea-leads/v1',
		'/token',
		array(
			'methods'             => 'GET',
			'callback'            => 'mea_leads_rest_token',
			'permission_callback' => '__return_true',
		)
	);
	register_rest_route(
		'mea-leads/v1',
		'/kirim',
		array(
			'methods'             => 'POST',
			'callback'            => 'mea_leads_rest_submit',
			'permission_callback' => '__return_true',
		)
	);
}
add_action( 'rest_api_init', 'mea_leads_register_routes' );

/**
 * Nonce segar, diambil form.js tepat sebelum mengirim.
 *
 * Nonce di HTML halaman yang di-cache bisa sudah kedaluwarsa; nonce dari sini
 * tidak. Token waktu (mea_t) tetap dari HTML halaman.
 *
 * @return WP_REST_Response
 */
function mea_leads_rest_token() {
	$response = new WP_REST_Response(
		array(
			'nonce' => wp_create_nonce( 'mea_leads_kirim' ),
		)
	);
	$response->header( 'Cache-Control', 'no-store, max-age=0' );
	return $response;
}

/**
 * Terima isian form dari JavaScript.
 *
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response
 */
function mea_leads_rest_submit( $request ) {
	$result = mea_leads_handle_submission( (array) $request->get_body_params() + (array) $request->get_json_params() );

	$map             = array(
		'ok'      => array(
			200,
			array(
				'ok'       => true,
				'event_id' => $result['event_id'] ?? '',
			),
		),
		'spam'    => array( 200, array( 'ok' => true ) ),
		'invalid' => array(
			422,
			array(
				'ok'     => false,
				'errors' => $result['errors'] ?? array(),
			),
		),
		'sesi'    => array(
			403,
			array(
				'ok'     => false,
				'errors' => array( 'form' => __( 'Sesi form sudah kedaluwarsa. Muat ulang halaman lalu kirim lagi.', 'mea-leads' ) ),
			),
		),
		'batas'   => array(
			429,
			array(
				'ok'     => false,
				'errors' => array( 'form' => __( 'Terlalu banyak percobaan dari perangkat ini. Coba lagi beberapa menit lagi, atau hubungi tim sales lewat WhatsApp.', 'mea-leads' ) ),
			),
		),
	);
	[ $code, $body ] = $map[ $result['status'] ];

	$response = new WP_REST_Response( $body, $code );
	$response->header( 'Cache-Control', 'no-store, max-age=0' );
	return $response;
}

/**
 * Terima isian form tanpa JavaScript, lalu kembali ke halaman asal.
 *
 * @return void
 */
function mea_leads_admin_post_submit() {
	// Nonce diverifikasi di mea_leads_handle_submission().
	$input  = wp_unslash( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- divalidasi dan disanitasi di mea_leads_handle_submission().
	$result = mea_leads_handle_submission( is_array( $input ) ? $input : array() );
	$path   = isset( $input['halaman_asal'] ) && is_string( $input['halaman_asal'] ) && 0 === strpos( $input['halaman_asal'], '/' ) && 0 !== strpos( $input['halaman_asal'], '//' ) ? $input['halaman_asal'] : '/';
	$flag   = in_array( $result['status'], array( 'ok', 'spam' ), true ) ? 'mea_terkirim' : 'mea_galat';

	wp_safe_redirect( add_query_arg( $flag, '1', home_url( $path ) ) . '#konsultasi', 303 );
	exit;
}
add_action( 'admin_post_nopriv_mea_leads_kirim', 'mea_leads_admin_post_submit' );
add_action( 'admin_post_mea_leads_kirim', 'mea_leads_admin_post_submit' );
