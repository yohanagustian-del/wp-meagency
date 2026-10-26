<?php
/**
 * Pengiriman lead ke penerima CDPS (kontrak docs/WEBSITE_LEAD_CONTRACT.md).
 *
 * Setiap percobaan memakai body dan idempotency key yang sama, dengan timestamp
 * dan tanda tangan baru. Tanpa konstanta MEA_LEADS_ENDPOINT/MEA_LEADS_SECRET
 * tidak ada pengiriman; lead tetap berstatus menunggu.
 *
 * @package MEA_Leads
 */

defined( 'ABSPATH' ) || exit;

/**
 * Apakah endpoint dan secret sudah diatur di wp-config.php.
 *
 * @return bool
 */
function mea_leads_configured() {
	return defined( 'MEA_LEADS_ENDPOINT' ) && defined( 'MEA_LEADS_SECRET' )
		&& '' !== (string) MEA_LEADS_ENDPOINT && '' !== (string) MEA_LEADS_SECRET;
}

/**
 * Jadwalkan pengiriman satu lead.
 *
 * @param int $id    ID baris.
 * @param int $delay Detik dari sekarang.
 * @return void
 */
function mea_leads_queue( $id, $delay = 0 ) {
	if ( ! mea_leads_configured() ) {
		return;
	}
	wp_schedule_single_event( time() + $delay, 'mea_leads_kirim', array( (int) $id ) );
	if ( 0 === $delay && ! ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) ) {
		spawn_cron();
	}
}

/**
 * Kirim satu lead sekarang.
 *
 * @param int $id ID baris.
 * @return string Status akhir percobaan ini: terkirim | gagal | ulang | lewat.
 */
function mea_leads_send( $id ) {
	if ( ! mea_leads_configured() || ! mea_leads_claim( (int) $id ) ) {
		return 'lewat';
	}

	$row = mea_leads_get( (int) $id );
	if ( ! $row || '' === (string) $row->body ) {
		mea_leads_update( (int) $id, array( 'kunci_sampai' => null ) );
		return 'lewat';
	}

	$timestamp = time();
	$response  = wp_remote_post(
		(string) MEA_LEADS_ENDPOINT,
		array(
			'timeout'     => 15,
			'redirection' => 0,
			'headers'     => array(
				'Content-Type'          => 'application/json; charset=utf-8',
				'X-MEA-Timestamp'       => (string) $timestamp,
				'X-MEA-Idempotency-Key' => $row->idempotency_key,
				'X-MEA-Signature'       => mea_leads_sign( (string) MEA_LEADS_SECRET, $timestamp, (string) $row->body ),
				'User-Agent'            => 'mea-leads/' . MEA_LEADS_VERSION . '; ' . mea_leads_site(),
			),
			'body'        => (string) $row->body,
		)
	);

	$code     = is_wp_error( $response ) ? null : (int) wp_remote_retrieve_response_code( $response );
	$result   = mea_leads_classify_response( $code );
	$attempts = (int) $row->percobaan + 1;
	$entry    = array(
		'percobaan' => $attempts,
		'kode'      => $code,
	);
	if ( is_wp_error( $response ) ) {
		$entry['galat'] = substr( $response->get_error_message(), 0, 200 );
	} elseif ( 'terkirim' !== $result ) {
		$entry['galat'] = substr( wp_strip_all_tags( (string) wp_remote_retrieve_body( $response ) ), 0, 200 );
	}

	$fields = array(
		'percobaan'    => $attempts,
		'kunci_sampai' => null,
	);

	if ( 'terkirim' === $result ) {
		$lead_id                 = mea_leads_parse_lead_id( (string) wp_remote_retrieve_body( $response ) );
		$fields['status']        = 'terkirim';
		$fields['terkirim_at']   = mea_leads_now();
		$fields['berikutnya_at'] = null;
		if ( '' !== $lead_id ) {
			$fields['cdps_lead_id'] = $lead_id;
			$entry['lead_id']       = $lead_id;
		}
	} elseif ( 'ulang' === $result && null !== mea_leads_next_delay( $attempts ) ) {
		$delay                   = mea_leads_next_delay( $attempts );
		$fields['berikutnya_at'] = mea_leads_now( $delay );
		$entry['berikutnya']     = $fields['berikutnya_at'];
	} else {
		$result                  = 'gagal';
		$fields['status']        = 'gagal';
		$fields['berikutnya_at'] = null;
	}

	$entry['hasil']    = $result;
	$fields['riwayat'] = mea_leads_history_append( $row, $entry );
	mea_leads_update( (int) $id, $fields );

	if ( 'ulang' === $result ) {
		mea_leads_queue( (int) $id, (int) mea_leads_next_delay( $attempts ) );
	}

	return $result;
}

/**
 * Kirim ulang manual dari wp-admin: hitungan percobaan diulang dari nol.
 *
 * @param int $id ID baris.
 * @return bool True bila lead dijadwalkan ulang.
 */
function mea_leads_resend( $id ) {
	$row = mea_leads_get( (int) $id );
	if ( ! $row || 'terkirim' === $row->status || '' === (string) $row->body ) {
		return false;
	}
	$user = wp_get_current_user();
	mea_leads_update(
		(int) $id,
		array(
			'status'        => 'menunggu',
			'percobaan'     => 0,
			'berikutnya_at' => null,
			'kunci_sampai'  => null,
			'riwayat'       => mea_leads_history_append(
				$row,
				array(
					'hasil' => 'kirim ulang manual',
					'oleh'  => $user ? $user->user_login : '',
				)
			),
		)
	);
	mea_leads_queue( (int) $id );
	return true;
}
