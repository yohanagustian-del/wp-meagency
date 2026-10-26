<?php
/**
 * Fungsi inti tanpa ketergantungan WordPress: normalisasi, validasi, payload v1,
 * tanda tangan HMAC, klasifikasi respons, backoff, dan CSV aman.
 *
 * Diuji langsung oleh tests/run.php (tanpa WordPress). Kontrak: docs/WEBSITE_LEAD_CONTRACT.md.
 *
 * @package MEA_Leads
 */

defined( 'ABSPATH' ) || defined( 'MEA_LEADS_TEST' ) || exit;

/**
 * Nilai omzet yang sah (kontrak v1).
 *
 * @return array<string, string> Nilai => label tampil.
 */
function mea_leads_omzet_options() {
	return array(
		'<100jt'    => 'Di bawah Rp 100 juta',
		'100-250jt' => 'Rp 100–250 juta',
		'250jt-1m'  => 'Rp 250 juta–1 miliar',
		'>1m'       => 'Di atas Rp 1 miliar',
	);
}

/**
 * Pilihan layanan di form. String kosong = "Belum tahu, minta saran".
 *
 * @return string[]
 */
function mea_leads_layanan_options() {
	return array(
		'Iklan Shopee dan TikTok Shop',
		'Kelola toko TikTok Shop',
		'Kelola toko Shopee',
		'Live streaming',
		'Affiliate',
		'Konten',
	);
}

/**
 * Normalisasi nomor WhatsApp ke format 08… (sama dengan prototipe).
 *
 * @param string $value Nomor masukan.
 * @return string
 */
function mea_leads_normalize_wa( $value ) {
	$value = preg_replace( '/[^\d+]/', '', (string) $value );
	if ( 0 === strpos( $value, '+62' ) ) {
		$value = '0' . substr( $value, 3 );
	} elseif ( 0 === strpos( $value, '62' ) ) {
		$value = '0' . substr( $value, 2 );
	}
	return $value;
}

/**
 * Validasi dan rapikan isian form. Pesan galat sama persis dengan prototipe.
 *
 * @param array<string, mixed> $input Isian mentah (sudah di-unslash).
 * @return array{data: array<string, string>, errors: array<string, string>}
 */
function mea_leads_validate( $input ) {
	$clean  = static function ( $value, $max ) {
		$value = trim( preg_replace( '/[\x00-\x1F\x7F]+/u', ' ', (string) $value ) );
		$value = trim( strip_tags( $value ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.strip_tags_strip_tags -- harus bisa jalan tanpa WordPress (tests/run.php).
		return function_exists( 'mb_substr' ) ? mb_substr( $value, 0, $max ) : substr( $value, 0, $max );
	};
	$errors = array();

	$nama = $clean( $input['nama'] ?? '', 191 );
	if ( ( function_exists( 'mb_strlen' ) ? mb_strlen( $nama ) : strlen( $nama ) ) < 2 ) {
		$errors['nama'] = 'Isi nama Anda.';
	}

	$wa = mea_leads_normalize_wa( $input['whatsapp'] ?? '' );
	if ( ! preg_match( '/^08\d{8,11}$/', $wa ) ) {
		$errors['whatsapp'] = 'Nomor WhatsApp diawali 08 dan berisi 10–13 angka.';
	}

	$omzet = (string) ( $input['omzet'] ?? '' );
	if ( ! array_key_exists( $omzet, mea_leads_omzet_options() ) ) {
		$errors['omzet'] = 'Pilih kisaran omzet.';
	}

	$layanan = (string) ( $input['layanan'] ?? '' );
	if ( '' !== $layanan && ! in_array( $layanan, mea_leads_layanan_options(), true ) ) {
		$layanan = '';
	}

	$setuju = $input['setuju'] ?? '';
	if ( ! in_array( $setuju, array( '1', 'on', 'yes', 'true', 1, true ), true ) ) {
		$errors['setuju'] = 'Centang persetujuan agar tim bisa menghubungi Anda.';
	}

	$halaman = $clean( $input['halaman_asal'] ?? '/', 255 );
	if ( '' === $halaman || '/' !== $halaman[0] || 0 === strpos( $halaman, '//' ) ) {
		$halaman = '/';
	}

	$sumber = strtolower( preg_replace( '/[^a-zA-Z0-9-]/', '', (string) ( $input['sumber_form'] ?? '' ) ) );

	return array(
		'data'   => array(
			'nama'         => $nama,
			'whatsapp'     => $wa,
			'link_toko'    => $clean( $input['link_toko'] ?? '', 255 ),
			'omzet'        => $omzet,
			'layanan'      => $layanan,
			'sumber_form'  => '' !== $sumber ? substr( $sumber, 0, 100 ) : 'website',
			'halaman_asal' => $halaman,
			'utm_source'   => $clean( $input['utm_source'] ?? '', 191 ),
			'utm_medium'   => $clean( $input['utm_medium'] ?? '', 191 ),
			'utm_campaign' => $clean( $input['utm_campaign'] ?? '', 191 ),
		),
		'errors' => $errors,
	);
}

/**
 * Body JSON payload v1. Urutan kunci mengikuti kontrak. Byte yang dihasilkan
 * disimpan dan dikirim apa adanya di setiap percobaan.
 *
 * @param array<string, string> $lead         Data lead tervalidasi.
 * @param string                $key          Idempotency key (UUID v4).
 * @param string                $site         Domain situs, mis. meagency.co.id.
 * @param string                $submitted_at ISO 8601 dengan offset +07:00.
 * @return string
 */
function mea_leads_build_body( $lead, $key, $site, $submitted_at ) {
	$utm = static function ( $value ) {
		return '' === (string) $value ? null : (string) $value;
	};

	$payload = array(
		'schema'                => 'mea.website_lead.v1',
		'kanal'                 => 'website',
		'idempotency_key'       => $key,
		'site'                  => $site,
		'submitted_at'          => $submitted_at,
		'nama'                  => $lead['nama'],
		'whatsapp'              => $lead['whatsapp'],
		'link_toko'             => $lead['link_toko'],
		'omzet'                 => $lead['omzet'],
		'layanan'               => $lead['layanan'],
		'sumber_form'           => $lead['sumber_form'],
		'halaman_asal'          => $lead['halaman_asal'],
		'utm'                   => array(
			'source'   => $utm( $lead['utm_source'] ?? '' ),
			'medium'   => $utm( $lead['utm_medium'] ?? '' ),
			'campaign' => $utm( $lead['utm_campaign'] ?? '' ),
		),
		'persetujuan_dihubungi' => true,
	);

	return json_encode( $payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ); // phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode -- harus bisa jalan tanpa WordPress (tests/run.php).
}

/**
 * Tanda tangan kontrak v1: "sha256=" + hex huruf kecil HMAC-SHA256(secret, "{timestamp}.{raw_body}").
 *
 * @param string     $secret    Secret bersama.
 * @param int|string $timestamp Detik Unix (UTC), sama dengan header X-MEA-Timestamp.
 * @param string     $raw_body  Byte persis body request.
 * @return string
 */
function mea_leads_sign( $secret, $timestamp, $raw_body ) {
	return 'sha256=' . hash_hmac( 'sha256', $timestamp . '.' . $raw_body, (string) $secret );
}

/**
 * Klasifikasi respons penerima.
 *
 * @param int|null $code Kode HTTP; null bila galat jaringan atau timeout.
 * @return string 'terkirim' | 'gagal' (tanpa retry) | 'ulang' (retry dengan backoff)
 */
function mea_leads_classify_response( $code ) {
	if ( null === $code ) {
		return 'ulang';
	}
	$code = (int) $code;
	if ( $code >= 200 && $code < 300 ) {
		return 'terkirim';
	}
	if ( in_array( $code, array( 408, 409, 425, 429 ), true ) || $code >= 500 ) {
		return 'ulang';
	}
	return 'gagal';
}

/**
 * Jeda sebelum percobaan berikutnya: 1 mnt, 5 mnt, 30 mnt, 2 jam, 12 jam.
 *
 * @param int $attempts Jumlah percobaan yang sudah gagal.
 * @return int|null Detik, atau null bila percobaan habis (status gagal).
 */
function mea_leads_next_delay( $attempts ) {
	$delays = array(
		1 => 60,
		2 => 300,
		3 => 1800,
		4 => 7200,
		5 => 43200,
	);
	return $delays[ (int) $attempts ] ?? null;
}

/**
 * ID lead CDPS dari body respons, bila ada dan formatnya benar.
 *
 * @param string $body Body respons.
 * @return string
 */
function mea_leads_parse_lead_id( $body ) {
	$data = json_decode( (string) $body, true );
	$id   = is_array( $data ) && isset( $data['lead_id'] ) ? (string) $data['lead_id'] : '';
	return preg_match( '/^LEAD-\d{6}-\d{4,}$/', $id ) ? $id : '';
}

/**
 * Sel CSV yang aman dari formula injection saat dibuka di spreadsheet.
 *
 * @param mixed $value Nilai sel.
 * @return string
 */
function mea_leads_csv_cell( $value ) {
	$value = (string) $value;
	if ( '' !== $value && in_array( $value[0], array( '=', '+', '-', '@', "\t", "\r" ), true ) ) {
		$value = "'" . $value;
	}
	return $value;
}
