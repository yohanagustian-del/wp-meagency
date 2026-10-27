<?php
/**
 * Tracking: fungsi murni tanpa WordPress (normalisasi, hash, payload Meta Conversions API
 * dan TikTok Events API, klasifikasi respons, backoff, pembersih galat, snippet GTM).
 *
 * Diuji langsung oleh tests/run.php. Rencana: docs/tracking/PLAN.md.
 *
 * Token TIDAK pernah masuk ke payload di sini. Token hanya ditempelkan saat request
 * dikirim (inc/tracking.php), supaya payload yang disimpan di tabel bersih dari token.
 *
 * @package MEA_Orbit
 */

defined( 'ABSPATH' ) || defined( 'MEA_ORBIT_TEST' ) || exit;

/**
 * Versi Graph API bawaan untuk Meta Conversions API.
 */
const MEA_ORBIT_META_API_VERSION = 'v24.0';

/**
 * Format ID GTM yang diterima.
 *
 * @param string $id ID kontainer.
 * @return bool
 */
function mea_orbit_tracking_gtm_valid( $id ) {
	return 1 === preg_match( '/^GTM-[A-Z0-9]{4,12}$/', (string) $id );
}

/**
 * Snippet GTM untuk <head>. ID wajib lolos mea_orbit_tracking_gtm_valid().
 *
 * @param string $id ID kontainer.
 * @return string
 */
function mea_orbit_tracking_gtm_head( $id ) {
	if ( ! mea_orbit_tracking_gtm_valid( $id ) ) {
		return '';
	}
	return "<!-- Google Tag Manager -->\n<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':\nnew Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],\nj=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=\n'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);\n})(window,document,'script','dataLayer','" . $id . "');</script>\n<!-- End Google Tag Manager -->\n";
}

/**
 * Snippet GTM <noscript> tepat setelah <body>.
 *
 * @param string $id ID kontainer.
 * @return string
 */
function mea_orbit_tracking_gtm_body( $id ) {
	if ( ! mea_orbit_tracking_gtm_valid( $id ) ) {
		return '';
	}
	return '<!-- Google Tag Manager (noscript) --><noscript><iframe src="https://www.googletagmanager.com/ns.html?id=' . $id . '" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript><!-- End Google Tag Manager (noscript) -->' . "\n";
}

/**
 * Nomor Indonesia ke format 62… (hanya angka). Nomor yang tidak dikenali menjadi ''.
 *
 * @param string $value Nomor masukan (08…, +62…, 62…, atau 8…).
 * @return string
 */
function mea_orbit_tracking_phone( $value ) {
	$digits = preg_replace( '/\D/', '', (string) $value );
	if ( 0 === strpos( $digits, '62' ) ) {
		$digits = substr( $digits, 2 );
	} elseif ( 0 === strpos( $digits, '0' ) ) {
		$digits = substr( $digits, 1 );
	}
	return preg_match( '/^8\d{7,11}$/', $digits ) ? '62' . $digits : '';
}

/**
 * SHA-256 heksadesimal huruf kecil. Nilai kosong tetap kosong (tidak di-hash).
 *
 * @param string $value Nilai yang sudah dinormalisasi.
 * @return string
 */
function mea_orbit_tracking_hash( $value ) {
	$value = (string) $value;
	return '' === $value ? '' : hash( 'sha256', $value );
}

/**
 * Pecah nama untuk Meta: huruf kecil, tanpa tanda baca dan angka. fn = kata pertama,
 * ln = sisanya (dipisah satu spasi; tanda hubung jadi pemisah kata).
 * Satu kata → ln kosong.
 *
 * @param string $nama Nama dari form.
 * @return array{fn: string, ln: string}
 */
function mea_orbit_tracking_name_parts( $nama ) {
	$nama  = function_exists( 'mb_strtolower' ) ? mb_strtolower( (string) $nama, 'UTF-8' ) : strtolower( (string) $nama );
	$nama  = (string) preg_replace( '/[^\p{L}\s]+/u', ' ', $nama );
	$words = preg_split( '/\s+/u', trim( $nama ), -1, PREG_SPLIT_NO_EMPTY );
	$words = is_array( $words ) ? $words : array();
	return array(
		'fn' => (string) array_shift( $words ),
		'ln' => implode( ' ', $words ),
	);
}

/**
 * Buang kunci yang nilainya kosong ('' , null, atau array kosong), rekursif.
 *
 * @param array<string, mixed> $data Data.
 * @return array<string, mixed>
 */
function mea_orbit_tracking_prune( $data ) {
	foreach ( $data as $key => $value ) {
		if ( is_array( $value ) ) {
			$value        = mea_orbit_tracking_prune( $value );
			$data[ $key ] = $value;
		}
		if ( '' === $value || null === $value || array() === $value ) {
			unset( $data[ $key ] );
		}
	}
	return $data;
}

/**
 * Payload Meta Conversions API untuk satu event Lead (tanpa access_token).
 *
 * @param array<string, mixed> $ev        Event: event_id, event_time, url, nama, whatsapp,
 *                                        ip, user_agent, fbp, fbc, sumber_form, omzet.
 * @param string               $test_code Test Event Code (kosong = event sungguhan).
 * @return array<string, mixed>
 */
function mea_orbit_tracking_meta_payload( $ev, $test_code = '' ) {
	$name  = mea_orbit_tracking_name_parts( (string) ( $ev['nama'] ?? '' ) );
	$hash1 = static function ( $value ) {
		$hashed = mea_orbit_tracking_hash( $value );
		return '' === $hashed ? array() : array( $hashed );
	};

	$event = array(
		'event_name'       => 'Lead',
		'event_time'       => (int) ( $ev['event_time'] ?? 0 ),
		'event_id'         => (string) ( $ev['event_id'] ?? '' ),
		'action_source'    => 'website',
		'event_source_url' => (string) ( $ev['url'] ?? '' ),
		'user_data'        => array(
			'ph'                => $hash1( mea_orbit_tracking_phone( (string) ( $ev['whatsapp'] ?? '' ) ) ),
			'fn'                => $hash1( $name['fn'] ),
			'ln'                => $hash1( $name['ln'] ),
			'external_id'       => $hash1( (string) ( $ev['event_id'] ?? '' ) ),
			'client_ip_address' => (string) ( $ev['ip'] ?? '' ),
			'client_user_agent' => (string) ( $ev['user_agent'] ?? '' ),
			'fbp'               => (string) ( $ev['fbp'] ?? '' ),
			'fbc'               => (string) ( $ev['fbc'] ?? '' ),
		),
		'custom_data'      => array(
			'lead_source' => (string) ( $ev['sumber_form'] ?? '' ),
			'omzet'       => (string) ( $ev['omzet'] ?? '' ),
		),
	);

	return mea_orbit_tracking_prune(
		array(
			'data'            => array( mea_orbit_tracking_prune( $event ) ),
			'test_event_code' => (string) $test_code,
		)
	);
}

/**
 * Payload TikTok Events API v1.3 (event/track) untuk satu event Lead (tanpa token).
 *
 * @param array<string, mixed> $ev        Event: event_id, event_time, url, whatsapp, ip,
 *                                        user_agent, ttp, ttclid, sumber_form.
 * @param string               $pixel_id  Kode pixel TikTok.
 * @param string               $test_code Test Event Code (kosong = event sungguhan).
 * @return array<string, mixed>
 */
function mea_orbit_tracking_tiktok_payload( $ev, $pixel_id, $test_code = '' ) {
	$phone = mea_orbit_tracking_phone( (string) ( $ev['whatsapp'] ?? '' ) );

	$event = array(
		'event'      => 'Lead',
		'event_time' => (int) ( $ev['event_time'] ?? 0 ),
		'event_id'   => (string) ( $ev['event_id'] ?? '' ),
		'user'       => array(
			'phone'       => '' === $phone ? '' : mea_orbit_tracking_hash( '+' . $phone ),
			'external_id' => mea_orbit_tracking_hash( (string) ( $ev['event_id'] ?? '' ) ),
			'ttp'         => (string) ( $ev['ttp'] ?? '' ),
			'ttclid'      => (string) ( $ev['ttclid'] ?? '' ),
			'ip'          => (string) ( $ev['ip'] ?? '' ),
			'user_agent'  => (string) ( $ev['user_agent'] ?? '' ),
		),
		'page'       => array( 'url' => (string) ( $ev['url'] ?? '' ) ),
		'properties' => array( 'content_name' => (string) ( $ev['sumber_form'] ?? '' ) ),
	);

	return mea_orbit_tracking_prune(
		array(
			'event_source'    => 'web',
			'event_source_id' => (string) $pixel_id,
			'test_event_code' => (string) $test_code,
			'data'            => array( mea_orbit_tracking_prune( $event ) ),
		)
	);
}

/**
 * Klasifikasi respons platform: terkirim | ulang | gagal.
 *
 * Meta: 2xx terkirim; 5xx, 429, galat jaringan, kode rate limit (4, 17, 32, 613) dan
 * `is_transient` diulang; selain itu gagal.
 * TikTok: selalu HTTP 200 dengan `code` di body. 0 terkirim; 40100 (rate limit) dan
 * 5xxxx diulang; selain itu gagal.
 *
 * @param string   $platform meta | tiktok.
 * @param int|null $code     Kode HTTP, null bila galat jaringan.
 * @param string   $body     Body respons.
 * @return string
 */
function mea_orbit_tracking_classify( $platform, $code, $body ) {
	if ( null === $code ) {
		return 'ulang';
	}
	if ( 429 === $code || $code >= 500 ) {
		return 'ulang';
	}
	$json = json_decode( (string) $body, true );
	$json = is_array( $json ) ? $json : array();

	if ( 'tiktok' === $platform ) {
		if ( 200 !== $code ) {
			return 'gagal';
		}
		$api = isset( $json['code'] ) ? (int) $json['code'] : -1;
		if ( 0 === $api ) {
			return 'terkirim';
		}
		return ( 40100 === $api || $api >= 50000 ) ? 'ulang' : 'gagal';
	}

	if ( $code >= 200 && $code < 300 ) {
		return 'terkirim';
	}
	$error = isset( $json['error'] ) && is_array( $json['error'] ) ? $json['error'] : array();
	if ( ! empty( $error['is_transient'] ) || in_array( (int) ( $error['code'] ?? 0 ), array( 1, 2, 4, 17, 32, 613 ), true ) ) {
		return 'ulang';
	}
	return 'gagal';
}

/**
 * Jeda sebelum percobaan berikutnya. Jadwal sama dengan mea-leads (pengiriman ke CDPS):
 * 1 mnt, 5 mnt, 30 mnt, 2 jam, 12 jam, lalu berhenti.
 *
 * @param int $attempts Jumlah percobaan yang sudah dilakukan.
 * @return int|null Detik, atau null bila percobaan sudah habis.
 */
function mea_orbit_tracking_next_delay( $attempts ) {
	$schedule = array(
		1 => 60,
		2 => 300,
		3 => 1800,
		4 => 7200,
		5 => 43200,
	);
	return $schedule[ (int) $attempts ] ?? null;
}

/**
 * Pesan galat aman untuk disimpan/ditampilkan: nilai rahasia diganti, tag dibuang,
 * dipotong 200 karakter.
 *
 * @param string   $message Pesan mentah.
 * @param string[] $secrets Nilai yang tidak boleh muncul (token).
 * @return string
 */
function mea_orbit_tracking_scrub( $message, $secrets ) {
	$message = (string) $message;
	foreach ( $secrets as $secret ) {
		$secret = (string) $secret;
		if ( strlen( $secret ) >= 4 ) {
			$message = str_replace( array( $secret, rawurlencode( $secret ) ), '[token]', $message );
		}
	}
	// Jaga-jaga: parameter access_token apa pun yang terbawa di URL atau body.
	$message = (string) preg_replace( '/(access_token["\'=:\s]+)[^"&\s,}]+/i', '$1[token]', $message );
	$message = trim( (string) preg_replace( '/\s+/', ' ', strip_tags( $message ) ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.strip_tags_strip_tags -- harus bisa jalan tanpa WordPress (tests/run.php).
	return function_exists( 'mb_substr' ) ? mb_substr( $message, 0, 200 ) : substr( $message, 0, 200 );
}

/**
 * Cookie pixel yang diteruskan ke server: hanya bila bentuknya wajar.
 *
 * @param array<string, mixed> $cookies $_COOKIE (sudah di-unslash).
 * @return array{fbp: string, fbc: string, ttp: string, ttclid: string}
 */
function mea_orbit_tracking_cookies( $cookies ) {
	$get = static function ( $key, $pattern ) use ( $cookies ) {
		$value = isset( $cookies[ $key ] ) && is_string( $cookies[ $key ] ) ? trim( $cookies[ $key ] ) : '';
		return ( strlen( $value ) <= 500 && preg_match( $pattern, $value ) ) ? $value : '';
	};
	return array(
		'fbp'    => $get( '_fbp', '/^fb\.\d\.\d+\.\d+$/' ),
		'fbc'    => $get( '_fbc', '/^fb\.\d\.\d+\.[A-Za-z0-9_\-\.]+$/' ),
		'ttp'    => $get( '_ttp', '/^[A-Za-z0-9_\-\.]+$/' ),
		'ttclid' => $get( 'ttclid', '/^[A-Za-z0-9_\-\.]+$/' ),
	);
}
