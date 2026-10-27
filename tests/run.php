<?php
/**
 * Test tanpa dependency untuk inti plugin mea-leads.
 *
 * Jalankan: php tests/run.php
 * Vektor HMAC dihitung terpisah dengan openssl (bukan dengan kode plugin):
 *   printf '%s' "1761360900.<body>" | openssl dgst -sha256 -hmac 'contoh-rahasia-jangan-dipakai'
 *
 * @package MEA_Leads
 */

define( 'MEA_LEADS_TEST', true );
require __DIR__ . '/../wp-content/plugins/mea-leads/includes/core.php';

$mea_tests_failed = 0;
$mea_tests_total  = 0;

/**
 * Bandingkan nilai dan catat hasilnya.
 *
 * @param string $name     Nama kasus.
 * @param mixed  $expected Nilai yang diharapkan.
 * @param mixed  $actual   Nilai sebenarnya.
 * @return void
 */
function mea_test( $name, $expected, $actual ) {
	global $mea_tests_failed, $mea_tests_total;
	++$mea_tests_total;
	if ( $expected === $actual ) {
		echo "  ok   $name\n";
		return;
	}
	++$mea_tests_failed;
	echo "  GAGAL $name\n    harap: " . var_export( $expected, true ) . "\n    dapat: " . var_export( $actual, true ) . "\n";
}

$mea_body = '{"schema":"mea.website_lead.v1","kanal":"website","idempotency_key":"3f0c8a52-6c1e-4d8a-9b2f-0c4e2a7d9e11","site":"meagency.co.id","submitted_at":"2026-10-19T09:15:00+07:00","nama":"Rina Kusuma","whatsapp":"081234567890","link_toko":"shopee.co.id/namatoko","omzet":"250jt-1m","layanan":"Iklan Shopee dan TikTok Shop","sumber_form":"layanan-performance-ads","halaman_asal":"/jasa-performance-ads/","utm":{"source":null,"medium":null,"campaign":null},"persetujuan_dihubungi":true}';

echo "Payload v1\n";
$mea_lead = array(
	'nama'         => 'Rina Kusuma',
	'whatsapp'     => '081234567890',
	'link_toko'    => 'shopee.co.id/namatoko',
	'omzet'        => '250jt-1m',
	'layanan'      => 'Iklan Shopee dan TikTok Shop',
	'sumber_form'  => 'layanan-performance-ads',
	'halaman_asal' => '/jasa-performance-ads/',
	'utm_source'   => '',
	'utm_medium'   => '',
	'utm_campaign' => '',
);
mea_test( 'body sama byte demi byte dengan contoh kontrak', $mea_body, mea_leads_build_body( $mea_lead, '3f0c8a52-6c1e-4d8a-9b2f-0c4e2a7d9e11', 'meagency.co.id', '2026-10-19T09:15:00+07:00' ) );
$mea_lead['utm_source'] = 'instagram';
$mea_decoded            = json_decode( mea_leads_build_body( $mea_lead, 'k', 'meagency.co.id', 't' ), true );
mea_test( 'utm terisi jadi string, kosong jadi null', array( 'source' => 'instagram', 'medium' => null, 'campaign' => null ), $mea_decoded['utm'] );
mea_test( 'urutan kunci sesuai kontrak', array( 'schema', 'kanal', 'idempotency_key', 'site', 'submitted_at', 'nama', 'whatsapp', 'link_toko', 'omzet', 'layanan', 'sumber_form', 'halaman_asal', 'utm', 'persetujuan_dihubungi' ), array_keys( $mea_decoded ) );
$mea_lead['nama'] = 'Siti "Toko/Kue" Ü';
mea_test( 'unicode dan slash tidak di-escape', true, false !== strpos( mea_leads_build_body( $mea_lead, 'k', 's', 't' ), 'Siti \"Toko/Kue\" Ü' ) );

echo "Tanda tangan HMAC\n";
mea_test( 'vektor openssl', 'sha256=613ec03b112cb1e062b9f8eff5d1a7a4f4fb178bdb8a65e14f6cb60095b0c78d', mea_leads_sign( 'contoh-rahasia-jangan-dipakai', 1761360900, $mea_body ) );
mea_test( 'timestamp string sama dengan integer', mea_leads_sign( 's', 1761360900, 'b' ), mea_leads_sign( 's', '1761360900', 'b' ) );
mea_test( 'body berubah satu byte, tanda tangan berubah', false, mea_leads_sign( 's', 1, $mea_body ) === mea_leads_sign( 's', 1, $mea_body . ' ' ) );
mea_test( 'format sha256= + 64 hex huruf kecil', 1, preg_match( '/^sha256=[0-9a-f]{64}$/', mea_leads_sign( 'x', 1, 'y' ) ) );

echo "Normalisasi nomor\n";
mea_test( '+62 dengan spasi dan strip', '081234567890', mea_leads_normalize_wa( '+62 812-3456-7890' ) );
mea_test( 'awalan 62', '081234567890', mea_leads_normalize_wa( '6281234567890' ) );
mea_test( 'awalan 08 dengan titik', '0812345678', mea_leads_normalize_wa( '0812.345.678' ) );

echo "Validasi (pesan sama dengan prototipe)\n";
$mea_ok = array(
	'nama'         => '  Rina  ',
	'whatsapp'     => '+62 812 3456 7890',
	'link_toko'    => 'shopee.co.id/rina',
	'omzet'        => '100-250jt',
	'layanan'      => 'Konten',
	'setuju'       => '1',
	'sumber_form'  => 'layanan-performance-ads',
	'halaman_asal' => '/jasa-performance-ads/',
);
$mea_v  = mea_leads_validate( $mea_ok );
mea_test( 'isian lengkap tanpa galat', array(), $mea_v['errors'] );
mea_test( 'nama di-trim', 'Rina', $mea_v['data']['nama'] );
mea_test( 'nomor dinormalisasi', '081234567890', $mea_v['data']['whatsapp'] );
$mea_v = mea_leads_validate( array() );
mea_test(
	'isian kosong: empat pesan galat',
	array(
		'nama'     => 'Isi nama Anda.',
		'whatsapp' => 'Nomor WhatsApp diawali 08 dan berisi 10–13 angka.',
		'omzet'    => 'Pilih kisaran omzet.',
		'setuju'   => 'Centang persetujuan agar tim bisa menghubungi Anda.',
	),
	$mea_v['errors']
);
mea_test( 'sumber kosong jadi website', 'website', $mea_v['data']['sumber_form'] );
mea_test( 'halaman asal kosong jadi /', '/', $mea_v['data']['halaman_asal'] );
mea_test( 'nomor 9 digit ditolak', true, isset( mea_leads_validate( array( 'whatsapp' => '081234567' ) )['errors']['whatsapp'] ) );
mea_test( 'nomor 14 digit ditolak', true, isset( mea_leads_validate( array( 'whatsapp' => '08123456789012' ) )['errors']['whatsapp'] ) );
mea_test( 'nomor 13 digit diterima', false, isset( mea_leads_validate( array( 'whatsapp' => '0812345678901' ) )['errors']['whatsapp'] ) );
mea_test( 'nomor tidak diawali 08 ditolak', true, isset( mea_leads_validate( array( 'whatsapp' => '0712345678' ) )['errors']['whatsapp'] ) );
mea_test( 'omzet di luar daftar ditolak', true, isset( mea_leads_validate( array( 'omzet' => '>5m' ) )['errors']['omzet'] ) );
mea_test( 'layanan di luar daftar dikosongkan', '', mea_leads_validate( array( 'layanan' => 'Website' ) )['data']['layanan'] );
mea_test( 'halaman asal luar situs ditolak', '/', mea_leads_validate( array( 'halaman_asal' => '//evil.example/' ) )['data']['halaman_asal'] );
mea_test( 'tag HTML di nama dibuang', 'Rina', mea_leads_validate( array( 'nama' => '<b>Rina</b>' ) )['data']['nama'] );
mea_test( 'sumber hanya huruf kecil, angka, strip', 'studi-kasus-bellarosascript', mea_leads_validate( array( 'sumber_form' => 'studi-kasus-Bellarosa<script>' ) )['data']['sumber_form'] );

echo "Klasifikasi respons\n";
foreach ( array( 200, 201, 204 ) as $mea_code ) {
	mea_test( "HTTP $mea_code terkirim", 'terkirim', mea_leads_classify_response( $mea_code ) );
}
foreach ( array( 301, 400, 401, 403, 404, 422 ) as $mea_code ) {
	mea_test( "HTTP $mea_code gagal tanpa retry", 'gagal', mea_leads_classify_response( $mea_code ) );
}
foreach ( array( 408, 409, 425, 429, 500, 502, 503 ) as $mea_code ) {
	mea_test( "HTTP $mea_code diulang", 'ulang', mea_leads_classify_response( $mea_code ) );
}
mea_test( 'galat jaringan diulang', 'ulang', mea_leads_classify_response( null ) );

echo "Backoff\n";
mea_test( 'jadwal 1 mnt, 5 mnt, 30 mnt, 2 jam, 12 jam, lalu habis', array( 60, 300, 1800, 7200, 43200, null ), array_map( 'mea_leads_next_delay', array( 1, 2, 3, 4, 5, 6 ) ) );

echo "Respons penerima\n";
mea_test( 'lead_id dibaca', 'LEAD-202610-0007', mea_leads_parse_lead_id( '{"status":"dibuat","lead_id":"LEAD-202610-0007"}' ) );
mea_test( 'lead_id salah format diabaikan', '', mea_leads_parse_lead_id( '{"lead_id":"<script>"}' ) );
mea_test( 'body bukan JSON diabaikan', '', mea_leads_parse_lead_id( 'OK' ) );

echo "CSV\n";
mea_test( 'formula diberi awalan kutip', "'=HYPERLINK(1)", mea_leads_csv_cell( '=HYPERLINK(1)' ) );
mea_test( 'teks biasa tidak berubah', 'Rina', mea_leads_csv_cell( 'Rina' ) );

/*
 * Tracking tema mea-orbit (inc/tracking-payload.php, docs/tracking/PLAN.md bagian 7).
 * Vektor hash dihitung terpisah: printf '%s' '6281234567890' | sha256sum
 */
define( 'MEA_ORBIT_TEST', true );
require __DIR__ . '/../wp-content/themes/mea-orbit/inc/tracking-payload.php';

$mea_h = array(
	'ph'      => 'dd752605096029a73495a91862983963e1947a834a4300808074185af10bb725',
	'ph_plus' => '62397bbd6a8c9ae53bc914a6017300eb6b13af5be20e4cc9ad2dc3d61ecb24cd',
	'rina'    => '8444da51ab98379d96a9d9c971b6ed0cf68b4bde471119dc932b09ec23f8b3f4',
	'kusuma'  => '29b43c3fbb4b5b1e35f892821a2507ef120e672439f4394407e94b4c6171f49a',
	'eid'     => '5eedd337f35c173b83f0e70e2023678145be50925805d46d324b67b23f7418db',
);

echo "Tracking: normalisasi dan hash\n";
foreach ( array( '081234567890', '+62 812-3456-7890', '6281234567890', '81234567890', '0812.3456.7890' ) as $mea_in ) {
	mea_test( "nomor $mea_in jadi 62…", '6281234567890', mea_orbit_tracking_phone( $mea_in ) );
}
mea_test( 'nomor bukan Indonesia jadi kosong', '', mea_orbit_tracking_phone( '0712345678' ) );
mea_test( 'nomor kosong tetap kosong', '', mea_orbit_tracking_phone( '' ) );
mea_test( 'hash vektor sha256sum', $mea_h['ph'], mea_orbit_tracking_hash( '6281234567890' ) );
mea_test( 'hash nilai kosong tetap kosong', '', mea_orbit_tracking_hash( '' ) );
mea_test(
	'nama dirapikan: fn kata pertama, ln sisanya',
	array(
		'fn' => 'rina',
		'ln' => 'kusuma',
	),
	mea_orbit_tracking_name_parts( " Rina  Kusuma " )
);
mea_test(
	'nama satu kata: ln kosong',
	array(
		'fn' => 'siti',
		'ln' => '',
	),
	mea_orbit_tracking_name_parts( 'Siti' )
);
mea_test(
	'tanda baca dan angka dibuang, huruf non-ASCII tetap',
	array(
		'fn' => 'ümi',
		'ln' => 'nur aini',
	),
	mea_orbit_tracking_name_parts( 'Ümi, Nur-Aini 2' )
);

$mea_ev = array(
	'event_id'    => '3f0c8a52-6c1e-4d8a-9b2f-0c4e2a7d9e11',
	'event_time'  => 1761360900,
	'url'         => 'https://www.meagency.co.id/jasa-performance-ads/',
	'nama'        => 'Rina Kusuma',
	'whatsapp'    => '081234567890',
	'ip'          => '203.0.113.7',
	'user_agent'  => 'Mozilla/5.0 (uji)',
	'fbp'         => 'fb.1.1761360000000.123456789',
	'fbc'         => '',
	'ttp'         => 'abcDEF123',
	'ttclid'      => '',
	'sumber_form' => 'layanan-performance-ads',
	'omzet'       => '250jt-1m',
);

echo "Tracking: payload Meta Conversions API\n";
$mea_meta = mea_orbit_tracking_meta_payload( $mea_ev );
mea_test(
	'bentuk payload Meta (tanpa test code, field kosong tidak dikirim)',
	array(
		'data' => array(
			array(
				'event_name'       => 'Lead',
				'event_time'       => 1761360900,
				'event_id'         => '3f0c8a52-6c1e-4d8a-9b2f-0c4e2a7d9e11',
				'action_source'    => 'website',
				'event_source_url' => 'https://www.meagency.co.id/jasa-performance-ads/',
				'user_data'        => array(
					'ph'                => array( $mea_h['ph'] ),
					'fn'                => array( $mea_h['rina'] ),
					'ln'                => array( $mea_h['kusuma'] ),
					'external_id'       => array( $mea_h['eid'] ),
					'client_ip_address' => '203.0.113.7',
					'client_user_agent' => 'Mozilla/5.0 (uji)',
					'fbp'               => 'fb.1.1761360000000.123456789',
				),
				'custom_data'      => array(
					'lead_source' => 'layanan-performance-ads',
					'omzet'       => '250jt-1m',
				),
			),
		),
	),
	$mea_meta
);
mea_test( 'test_event_code Meta hanya bila diisi', 'TEST123', mea_orbit_tracking_meta_payload( $mea_ev, 'TEST123' )['test_event_code'] );

echo "Tracking: payload TikTok Events API\n";
$mea_tt = mea_orbit_tracking_tiktok_payload( $mea_ev, 'DBAODNJC77U9NS8CML30' );
mea_test(
	'bentuk payload TikTok (nomor E.164 di-hash, nama tidak dikirim)',
	array(
		'event_source'    => 'web',
		'event_source_id' => 'DBAODNJC77U9NS8CML30',
		'data'            => array(
			array(
				'event'      => 'Lead',
				'event_time' => 1761360900,
				'event_id'   => '3f0c8a52-6c1e-4d8a-9b2f-0c4e2a7d9e11',
				'user'       => array(
					'phone'       => $mea_h['ph_plus'],
					'external_id' => $mea_h['eid'],
					'ttp'         => 'abcDEF123',
					'ip'          => '203.0.113.7',
					'user_agent'  => 'Mozilla/5.0 (uji)',
				),
				'page'       => array( 'url' => 'https://www.meagency.co.id/jasa-performance-ads/' ),
				'properties' => array( 'content_name' => 'layanan-performance-ads' ),
			),
		),
	),
	$mea_tt
);
mea_test( 'test_event_code TikTok hanya bila diisi', 'TEST9', mea_orbit_tracking_tiktok_payload( $mea_ev, 'P', 'TEST9' )['test_event_code'] );
mea_test( 'event_id Meta = TikTok = idempotency_key (dataLayer)', array( $mea_ev['event_id'], $mea_ev['event_id'] ), array( $mea_meta['data'][0]['event_id'], $mea_tt['data'][0]['event_id'] ) );
mea_test( 'nama mentah tidak ada di payload mana pun', false, false !== stripos( json_encode( array( $mea_meta, $mea_tt ) ), 'rina' ) );
mea_test( 'nomor mentah tidak ada di payload mana pun', false, false !== strpos( json_encode( array( $mea_meta, $mea_tt ) ), '81234567890' ) );
mea_test( 'payload tidak membawa access_token', false, false !== strpos( json_encode( array( $mea_meta, $mea_tt ) ), 'access_token' ) );

echo "Tracking: respons, backoff, galat\n";
mea_test( 'Meta 200 terkirim', 'terkirim', mea_orbit_tracking_classify( 'meta', 200, '{"events_received":1}' ) );
mea_test( 'Meta 400 token salah gagal', 'gagal', mea_orbit_tracking_classify( 'meta', 400, '{"error":{"code":190,"message":"Invalid OAuth access token"}}' ) );
mea_test( 'Meta 400 rate limit (17) diulang', 'ulang', mea_orbit_tracking_classify( 'meta', 400, '{"error":{"code":17}}' ) );
mea_test( 'Meta is_transient diulang', 'ulang', mea_orbit_tracking_classify( 'meta', 400, '{"error":{"code":100,"is_transient":true}}' ) );
mea_test( 'Meta 500 diulang', 'ulang', mea_orbit_tracking_classify( 'meta', 500, '' ) );
mea_test( 'galat jaringan diulang', 'ulang', mea_orbit_tracking_classify( 'tiktok', null, '' ) );
mea_test( 'TikTok 200 code 0 terkirim', 'terkirim', mea_orbit_tracking_classify( 'tiktok', 200, '{"code":0,"message":"OK"}' ) );
mea_test( 'TikTok 200 code 40001 gagal', 'gagal', mea_orbit_tracking_classify( 'tiktok', 200, '{"code":40001,"message":"No permission"}' ) );
mea_test( 'TikTok 200 code 40100 diulang', 'ulang', mea_orbit_tracking_classify( 'tiktok', 200, '{"code":40100}' ) );
mea_test( 'TikTok 200 code 50002 diulang', 'ulang', mea_orbit_tracking_classify( 'tiktok', 200, '{"code":50002}' ) );
mea_test( 'TikTok 200 body bukan JSON gagal', 'gagal', mea_orbit_tracking_classify( 'tiktok', 200, 'OK' ) );
mea_test( 'backoff tracking sama dengan mea-leads', array_map( 'mea_leads_next_delay', range( 1, 6 ) ), array_map( 'mea_orbit_tracking_next_delay', range( 1, 6 ) ) );
$mea_secret = 'EAAB-contoh-token-rahasia-123';
$mea_clean  = mea_orbit_tracking_scrub( '{"error":{"message":"Invalid token EAAB-contoh-token-rahasia-123 dan EAAB-contoh-token-rahasia-123"}}', array( $mea_secret ) );
mea_test( 'token dihapus dari pesan galat', false, false !== strpos( $mea_clean, $mea_secret ) );
mea_test( 'access_token lain di URL ikut disamarkan', 'GET /events?access_token=[token]&x=1', mea_orbit_tracking_scrub( 'GET /events?access_token=abc123xyz&x=1', array() ) );
mea_test( 'galat dipotong 200 karakter', 200, strlen( mea_orbit_tracking_scrub( str_repeat( 'a', 500 ), array() ) ) );

echo "Tracking: cookie dan GTM\n";
mea_test(
	'cookie wajar diteruskan, yang aneh dibuang',
	array(
		'fbp'    => 'fb.1.1761360000000.123456789',
		'fbc'    => 'fb.1.1761360000000.IwAR0abc-_',
		'ttp'    => '',
		'ttclid' => 'E.C.P.abc',
	),
	mea_orbit_tracking_cookies(
		array(
			'_fbp'   => 'fb.1.1761360000000.123456789',
			'_fbc'   => 'fb.1.1761360000000.IwAR0abc-_',
			'_ttp'   => '<script>',
			'ttclid' => 'E.C.P.abc',
		)
	)
);
mea_test( 'ID GTM valid', true, mea_orbit_tracking_gtm_valid( 'GTM-5XWWJBW' ) );
mea_test( 'ID GTM berisi kutip ditolak', false, mea_orbit_tracking_gtm_valid( "GTM-5X');alert(1);//" ) );
mea_test( 'ID GTM huruf kecil ditolak', false, mea_orbit_tracking_gtm_valid( 'gtm-5xwwjbw' ) );
mea_test( 'snippet head memuat ID tepat sekali', 1, substr_count( mea_orbit_tracking_gtm_head( 'GTM-5XWWJBW' ), 'GTM-5XWWJBW' ) );
mea_test( 'snippet tidak memuat pixel apa pun', 0, preg_match( '/fbq|ttq|gtag\(/', mea_orbit_tracking_gtm_head( 'GTM-5XWWJBW' ) . mea_orbit_tracking_gtm_body( 'GTM-5XWWJBW' ) ) );
mea_test( 'ID tidak valid: tanpa snippet', '', mea_orbit_tracking_gtm_head( 'UA-123' ) . mea_orbit_tracking_gtm_body( 'UA-123' ) );

echo "\n" . ( $mea_tests_total - $mea_tests_failed ) . "/$mea_tests_total lolos\n";
exit( $mea_tests_failed ? 1 : 0 );
