<?php
/**
 * Tracking situs: snippet GTM di browser, event Lead ke Meta Conversions API dan
 * TikTok Events API dari server. Rencana: docs/tracking/PLAN.md.
 *
 * Konstanta di wp-config.php (kosong/tidak ada = bagian itu diam):
 *   MEA_GTM_ID               GTM-XXXX. Meta Pixel, TikTok Pixel, dan GA4 dipasang di GTM.
 *   MEA_META_PIXEL_ID        + MEA_META_CAPI_TOKEN      → event server Meta.
 *   MEA_TIKTOK_PIXEL_ID      + MEA_TIKTOK_EVENTS_TOKEN  → event server TikTok.
 *
 * Satu lead = satu event_id (idempotency_key mea-leads) di dataLayer, pixel browser
 * (lewat GTM), dan event server, supaya Meta dan TikTok menggabungkan keduanya.
 *
 * Token hanya dibaca saat request dikirim. Tidak disimpan di tabel, tidak dicetak di
 * HTML atau admin, dan dibersihkan dari pesan galat (mea_orbit_tracking_scrub()).
 *
 * @package MEA_Orbit
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/tracking-payload.php';

const MEA_ORBIT_TRACKING_DB_VERSION = '1';

/**
 * Nilai konstanta tracking sebagai string ('' bila tidak didefinisikan).
 *
 * @param string $name Nama konstanta.
 * @return string
 */
function mea_orbit_tracking_const( $name ) {
	return defined( $name ) ? trim( (string) constant( $name ) ) : '';
}

/**
 * Platform server yang konstantanya lengkap.
 *
 * @return string[] Subset dari meta, tiktok.
 */
function mea_orbit_tracking_platforms() {
	$platforms = array();
	if ( '' !== mea_orbit_tracking_const( 'MEA_META_PIXEL_ID' ) && '' !== mea_orbit_tracking_const( 'MEA_META_CAPI_TOKEN' ) ) {
		$platforms[] = 'meta';
	}
	if ( '' !== mea_orbit_tracking_const( 'MEA_TIKTOK_PIXEL_ID' ) && '' !== mea_orbit_tracking_const( 'MEA_TIKTOK_EVENTS_TOKEN' ) ) {
		$platforms[] = 'tiktok';
	}
	return $platforms;
}

/**
 * Nilai token yang tidak boleh muncul di galat yang disimpan atau ditampilkan.
 *
 * @return string[]
 */
function mea_orbit_tracking_secrets() {
	return array_filter( array( mea_orbit_tracking_const( 'MEA_META_CAPI_TOKEN' ), mea_orbit_tracking_const( 'MEA_TIKTOK_EVENTS_TOKEN' ) ) );
}

/*
 * ---------------------------------------------------------------------------
 * Browser: GTM
 * ---------------------------------------------------------------------------
 */

/**
 * Apakah halaman ini boleh memuat tag. Tidak di wp-admin, preview, Customizer,
 * embed, feed, atau halaman login (wp-login.php tidak memanggil wp_head).
 *
 * @return bool
 */
function mea_orbit_tracking_should_print() {
	$print = ! is_admin() && ! is_preview() && ! is_customize_preview() && ! is_embed() && ! is_feed()
		&& mea_orbit_tracking_gtm_valid( mea_orbit_tracking_const( 'MEA_GTM_ID' ) );
	return (bool) apply_filters( 'mea_orbit_tracking_print', $print );
}

/**
 * Snippet GTM di <head>, didahului status login untuk pengecualian di GTM.
 *
 * @return void
 */
function mea_orbit_tracking_head() {
	if ( ! mea_orbit_tracking_should_print() ) {
		return;
	}
	echo '<script>window.dataLayer=window.dataLayer||[];window.dataLayer.push(' . wp_json_encode( array( 'logged_in' => is_user_logged_in() ) ) . ");</script>\n";
	echo mea_orbit_tracking_gtm_head( mea_orbit_tracking_const( 'MEA_GTM_ID' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- snippet statis, ID lolos pola GTM-[A-Z0-9].
}
add_action( 'wp_head', 'mea_orbit_tracking_head', 0 );

/**
 * Snippet GTM <noscript> tepat setelah <body>.
 *
 * @return void
 */
function mea_orbit_tracking_body() {
	if ( ! mea_orbit_tracking_should_print() ) {
		return;
	}
	echo mea_orbit_tracking_gtm_body( mea_orbit_tracking_const( 'MEA_GTM_ID' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- snippet statis, ID lolos pola GTM-[A-Z0-9].
}
add_action( 'wp_body_open', 'mea_orbit_tracking_body', 0 );

/*
 * ---------------------------------------------------------------------------
 * Server: tabel antrian
 * ---------------------------------------------------------------------------
 */

/**
 * Nama tabel antrian event server.
 *
 * @return string
 */
function mea_orbit_tracking_table() {
	global $wpdb;
	return $wpdb->prefix . 'mea_orbit_tracking';
}

/**
 * Buat/perbarui tabel bila versinya berubah, lalu pastikan jadwal cron ada.
 *
 * @return void
 */
function mea_orbit_tracking_maybe_install() {
	if ( MEA_ORBIT_TRACKING_DB_VERSION !== get_option( 'mea_orbit_tracking_db_version' ) ) {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$table   = mea_orbit_tracking_table();
		$charset = $wpdb->get_charset_collate();
		dbDelta(
			"CREATE TABLE {$table} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				platform varchar(10) NOT NULL,
				lead_id bigint(20) unsigned NOT NULL DEFAULT 0,
				event_id varchar(64) NOT NULL,
				payload longtext NOT NULL,
				status varchar(20) NOT NULL DEFAULT 'menunggu',
				percobaan smallint(5) unsigned NOT NULL DEFAULT 0,
				berikutnya_at datetime NULL,
				kunci_sampai datetime NULL,
				kode_http smallint(5) unsigned NULL,
				galat varchar(255) NULL,
				created_at datetime NOT NULL,
				updated_at datetime NOT NULL,
				PRIMARY KEY  (id),
				UNIQUE KEY platform_event (platform, event_id),
				KEY status (status),
				KEY created_at (created_at)
			) {$charset};"
		);
		update_option( 'mea_orbit_tracking_db_version', MEA_ORBIT_TRACKING_DB_VERSION, false );
	}

	if ( ! wp_next_scheduled( 'mea_orbit_tracking_sapu' ) ) {
		wp_schedule_event( time() + HOUR_IN_SECONDS, 'hourly', 'mea_orbit_tracking_sapu' );
	}
	if ( ! wp_next_scheduled( 'mea_orbit_tracking_retensi' ) ) {
		wp_schedule_event( time() + DAY_IN_SECONDS, 'daily', 'mea_orbit_tracking_retensi' );
	}
}
add_action( 'init', 'mea_orbit_tracking_maybe_install' );

/**
 * Ganti tema: hentikan jadwal. Tabel dan isinya tidak disentuh.
 *
 * @return void
 */
function mea_orbit_tracking_unschedule() {
	wp_clear_scheduled_hook( 'mea_orbit_tracking_sapu' );
	wp_clear_scheduled_hook( 'mea_orbit_tracking_retensi' );
	wp_unschedule_hook( 'mea_orbit_tracking_kirim' );
}
add_action( 'switch_theme', 'mea_orbit_tracking_unschedule' );

/**
 * Waktu UTC untuk kolom datetime.
 *
 * @param int $offset Detik dari sekarang.
 * @return string
 */
function mea_orbit_tracking_now( $offset = 0 ) {
	return gmdate( 'Y-m-d H:i:s', time() + $offset );
}

/*
 * ---------------------------------------------------------------------------
 * Server: pemicu dari mea-leads
 * ---------------------------------------------------------------------------
 */

/**
 * Lead tersimpan di mea-leads → susun payload per platform (data pribadi sudah
 * di-hash), simpan di antrian, kirim lewat WP-Cron.
 *
 * @param int                  $lead_id ID baris mea-leads.
 * @param array<string, mixed> $context Konteks dari mea_leads_handle_submission().
 * @return void
 */
function mea_orbit_tracking_on_lead( $lead_id, $context ) {
	$platforms = mea_orbit_tracking_platforms();
	if ( ! $platforms || empty( $context['event_id'] ) ) {
		return;
	}

	$ev = array_merge(
		array(
			'event_id'    => (string) $context['event_id'],
			'event_time'  => (int) ( $context['event_time'] ?? time() ),
			'url'         => (string) ( $context['event_source_url'] ?? '' ),
			'nama'        => (string) ( $context['nama'] ?? '' ),
			'whatsapp'    => (string) ( $context['whatsapp'] ?? '' ),
			'ip'          => (string) ( $context['ip'] ?? '' ),
			'user_agent'  => (string) ( $context['user_agent'] ?? '' ),
			'sumber_form' => (string) ( $context['sumber_form'] ?? '' ),
			'omzet'       => (string) ( $context['omzet'] ?? '' ),
		),
		mea_orbit_tracking_cookies( wp_unslash( $_COOKIE ) ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- tiap nilai dicek pola di mea_orbit_tracking_cookies().
	);

	global $wpdb;
	$now = mea_orbit_tracking_now();
	foreach ( $platforms as $platform ) {
		$payload = 'meta' === $platform
			? mea_orbit_tracking_meta_payload( $ev )
			: mea_orbit_tracking_tiktok_payload( $ev, mea_orbit_tracking_const( 'MEA_TIKTOK_PIXEL_ID' ) );

		$ok = $wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- tabel milik tema.
			mea_orbit_tracking_table(),
			array(
				'platform'   => $platform,
				'lead_id'    => (int) $lead_id,
				'event_id'   => $ev['event_id'],
				'payload'    => (string) wp_json_encode( $payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ),
				'status'     => 'menunggu',
				'created_at' => $now,
				'updated_at' => $now,
			)
		);
		if ( $ok ) {
			wp_schedule_single_event( time(), 'mea_orbit_tracking_kirim', array( (int) $wpdb->insert_id ) );
		}
	}

	// Picu WP-Cron di akhir request, setelah mea-leads juga menjadwalkan kirimannya.
	add_action( 'shutdown', 'mea_orbit_tracking_spawn_cron' );
}
add_action( 'mea_leads_lead_saved', 'mea_orbit_tracking_on_lead', 10, 2 );

/**
 * Picu WP-Cron (diam bila DISABLE_WP_CRON; kunci bawaan WordPress mencegah dobel).
 *
 * @return void
 */
function mea_orbit_tracking_spawn_cron() {
	if ( ! ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) ) {
		spawn_cron();
	}
}

/*
 * ---------------------------------------------------------------------------
 * Server: pengiriman
 * ---------------------------------------------------------------------------
 */

/**
 * Kirim satu payload ke platform. Token ditempelkan di sini saja.
 *
 * @param string               $platform meta | tiktok.
 * @param array<string, mixed> $payload  Payload tanpa token.
 * @return array{hasil: string, kode: int|null, galat: string}
 */
function mea_orbit_tracking_request( $platform, $payload ) {
	if ( 'meta' === $platform ) {
		$version = (string) apply_filters( 'mea_orbit_meta_api_version', MEA_ORBIT_META_API_VERSION );
		$url     = 'https://graph.facebook.com/' . rawurlencode( $version ) . '/' . rawurlencode( mea_orbit_tracking_const( 'MEA_META_PIXEL_ID' ) ) . '/events';
		$body    = $payload + array( 'access_token' => mea_orbit_tracking_const( 'MEA_META_CAPI_TOKEN' ) );
		$headers = array( 'Content-Type' => 'application/json' );
	} else {
		$url     = 'https://business-api.tiktok.com/open_api/v1.3/event/track/';
		$body    = $payload;
		$headers = array(
			'Content-Type' => 'application/json',
			'Access-Token' => mea_orbit_tracking_const( 'MEA_TIKTOK_EVENTS_TOKEN' ),
		);
	}

	/**
	 * Ganti alamat tujuan (hanya untuk uji lokal dengan penerima tiruan).
	 *
	 * @param string $url      Alamat bawaan.
	 * @param string $platform meta | tiktok.
	 */
	$url = (string) apply_filters( 'mea_orbit_tracking_endpoint', $url, $platform );

	$response = wp_remote_post(
		$url,
		array(
			'timeout'     => 15,
			'redirection' => 0,
			'headers'     => $headers,
			'body'        => (string) wp_json_encode( $body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ),
		)
	);

	if ( is_wp_error( $response ) ) {
		return array(
			'hasil' => 'ulang',
			'kode'  => null,
			'galat' => mea_orbit_tracking_scrub( $response->get_error_message(), mea_orbit_tracking_secrets() ),
		);
	}

	$code   = (int) wp_remote_retrieve_response_code( $response );
	$text   = (string) wp_remote_retrieve_body( $response );
	$result = mea_orbit_tracking_classify( $platform, $code, $text );

	return array(
		'hasil' => $result,
		'kode'  => $code,
		'galat' => 'terkirim' === $result ? '' : mea_orbit_tracking_scrub( $text, mea_orbit_tracking_secrets() ),
	);
}

/**
 * Kunci baris supaya tidak dikirim dua proses sekaligus.
 *
 * @param int $id ID baris.
 * @return bool
 */
function mea_orbit_tracking_claim( $id ) {
	global $wpdb;
	$table = mea_orbit_tracking_table();
	$now   = mea_orbit_tracking_now();
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- tabel milik tema.
	$affected = $wpdb->query( $wpdb->prepare( "UPDATE {$table} SET kunci_sampai = %s WHERE id = %d AND status = 'menunggu' AND (kunci_sampai IS NULL OR kunci_sampai < %s)", mea_orbit_tracking_now( 120 ), $id, $now ) );
	return 1 === (int) $affected;
}

/**
 * Kirim satu baris antrian sekarang.
 *
 * @param int $id ID baris.
 * @return string terkirim | ulang | gagal | lewat.
 */
function mea_orbit_tracking_send( $id ) {
	global $wpdb;
	$table = mea_orbit_tracking_table();
	$id    = (int) $id;

	$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- tabel milik tema.
	if ( ! $row || ! in_array( $row->platform, mea_orbit_tracking_platforms(), true ) || ! mea_orbit_tracking_claim( $id ) ) {
		return 'lewat';
	}

	$payload  = json_decode( (string) $row->payload, true );
	$out      = is_array( $payload ) ? mea_orbit_tracking_request( $row->platform, $payload ) : array(
		'hasil' => 'gagal',
		'kode'  => null,
		'galat' => 'payload rusak',
	);
	$attempts = (int) $row->percobaan + 1;
	$delay    = mea_orbit_tracking_next_delay( $attempts );
	$result   = ( 'ulang' === $out['hasil'] && null === $delay ) ? 'gagal' : $out['hasil'];

	$wpdb->update( // phpcs:ignore WordPress.DB.DirectDatabaseQuery -- tabel milik tema.
		$table,
		array(
			'status'        => 'ulang' === $result ? 'menunggu' : $result,
			'percobaan'     => $attempts,
			'berikutnya_at' => 'ulang' === $result ? mea_orbit_tracking_now( (int) $delay ) : null,
			'kunci_sampai'  => null,
			'kode_http'     => $out['kode'],
			'galat'         => '' === $out['galat'] ? null : $out['galat'],
			'updated_at'    => mea_orbit_tracking_now(),
		),
		array( 'id' => $id )
	);

	if ( 'ulang' === $result ) {
		wp_schedule_single_event( time() + (int) $delay, 'mea_orbit_tracking_kirim', array( $id ) );
	}
	return $result;
}
add_action( 'mea_orbit_tracking_kirim', 'mea_orbit_tracking_send' );

/**
 * Sapu per jam: baris menunggu yang sudah waktunya (termasuk event cron yang hilang).
 *
 * @return void
 */
function mea_orbit_tracking_sweep() {
	if ( ! mea_orbit_tracking_platforms() ) {
		return;
	}
	global $wpdb;
	$table = mea_orbit_tracking_table();
	$now   = mea_orbit_tracking_now();
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- tabel milik tema.
	$ids = $wpdb->get_col( $wpdb->prepare( "SELECT id FROM {$table} WHERE status = 'menunggu' AND (berikutnya_at IS NULL OR berikutnya_at <= %s) AND (kunci_sampai IS NULL OR kunci_sampai < %s) ORDER BY id ASC LIMIT 50", $now, $now ) );
	foreach ( $ids as $id ) {
		mea_orbit_tracking_send( (int) $id );
	}
}
add_action( 'mea_orbit_tracking_sapu', 'mea_orbit_tracking_sweep' );

/**
 * Retensi harian: baris lebih dari 90 hari dihapus (sejalan dengan mea-leads).
 *
 * @return void
 */
function mea_orbit_tracking_retention() {
	global $wpdb;
	$table = mea_orbit_tracking_table();
	$wpdb->query( $wpdb->prepare( "DELETE FROM {$table} WHERE created_at < %s", mea_orbit_tracking_now( -90 * DAY_IN_SECONDS ) ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- tabel milik tema.
}
add_action( 'mea_orbit_tracking_retensi', 'mea_orbit_tracking_retention' );

if ( is_admin() ) {
	require_once __DIR__ . '/tracking-admin.php';
}
