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

echo "\n" . ( $mea_tests_total - $mea_tests_failed ) . "/$mea_tests_total lolos\n";
exit( $mea_tests_failed ? 1 : 0 );
