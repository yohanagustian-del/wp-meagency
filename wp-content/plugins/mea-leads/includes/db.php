<?php
/**
 * Tabel {prefix}mea_leads: skema, simpan, ubah status, query admin, retensi.
 *
 * Semua waktu disimpan dalam UTC (Y-m-d H:i:s).
 *
 * @package MEA_Leads
 */

defined( 'ABSPATH' ) || exit;

/**
 * Nama tabel.
 *
 * @return string
 */
function mea_leads_table() {
	global $wpdb;
	return $wpdb->prefix . 'mea_leads';
}

/**
 * Buat atau perbarui tabel lewat dbDelta.
 *
 * @return void
 */
function mea_leads_install_table() {
	global $wpdb;
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';

	$table   = mea_leads_table();
	$charset = $wpdb->get_charset_collate();

	dbDelta(
		"CREATE TABLE {$table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			idempotency_key char(36) NOT NULL,
			status varchar(20) NOT NULL DEFAULT 'menunggu',
			site varchar(100) NOT NULL DEFAULT '',
			kanal varchar(20) NOT NULL DEFAULT 'website',
			sumber_form varchar(100) NOT NULL DEFAULT '',
			halaman_asal varchar(255) NOT NULL DEFAULT '',
			nama varchar(191) NULL,
			whatsapp varchar(20) NULL,
			link_toko varchar(255) NULL,
			omzet varchar(20) NOT NULL DEFAULT '',
			layanan varchar(100) NOT NULL DEFAULT '',
			utm_source varchar(191) NULL,
			utm_medium varchar(191) NULL,
			utm_campaign varchar(191) NULL,
			persetujuan tinyint(1) NOT NULL DEFAULT 1,
			submitted_at datetime NOT NULL,
			body longtext NULL,
			percobaan smallint(5) unsigned NOT NULL DEFAULT 0,
			berikutnya_at datetime NULL,
			kunci_sampai datetime NULL,
			riwayat longtext NULL,
			cdps_lead_id varchar(32) NULL,
			terkirim_at datetime NULL,
			dianonimkan_at datetime NULL,
			created_at datetime NOT NULL,
			updated_at datetime NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY idempotency_key (idempotency_key),
			KEY status (status),
			KEY sumber_form (sumber_form),
			KEY submitted_at (submitted_at)
		) {$charset};"
	);

	update_option( 'mea_leads_db_version', MEA_LEADS_DB_VERSION, false );
}

/**
 * Waktu sekarang (UTC) untuk kolom datetime.
 *
 * @param int $offset Detik dari sekarang.
 * @return string
 */
function mea_leads_now( $offset = 0 ) {
	return gmdate( 'Y-m-d H:i:s', time() + $offset );
}

/**
 * Domain situs untuk payload (tanpa www), bisa diganti dengan konstanta MEA_LEADS_SITE.
 *
 * @return string
 */
function mea_leads_site() {
	if ( defined( 'MEA_LEADS_SITE' ) && MEA_LEADS_SITE ) {
		return (string) MEA_LEADS_SITE;
	}
	$host = (string) wp_parse_url( home_url(), PHP_URL_HOST );
	return preg_replace( '/^www\./', '', $host );
}

/**
 * Simpan lead baru berstatus menunggu, lengkap dengan body payload v1.
 *
 * @param array<string, string> $lead Data tervalidasi dari mea_leads_validate().
 * @return int ID baris, 0 bila gagal.
 */
function mea_leads_insert( $lead ) {
	global $wpdb;

	$key       = wp_generate_uuid4();
	$submitted = new DateTimeImmutable( 'now', new DateTimeZone( 'Asia/Jakarta' ) );
	$body      = mea_leads_build_body( $lead, $key, mea_leads_site(), $submitted->format( 'Y-m-d\TH:i:sP' ) );
	$now       = mea_leads_now();

	$ok = $wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- tabel milik plugin.
		mea_leads_table(),
		array(
			'idempotency_key' => $key,
			'status'          => 'menunggu',
			'site'            => mea_leads_site(),
			'kanal'           => 'website',
			'sumber_form'     => $lead['sumber_form'],
			'halaman_asal'    => $lead['halaman_asal'],
			'nama'            => $lead['nama'],
			'whatsapp'        => $lead['whatsapp'],
			'link_toko'       => $lead['link_toko'],
			'omzet'           => $lead['omzet'],
			'layanan'         => $lead['layanan'],
			'utm_source'      => '' !== $lead['utm_source'] ? $lead['utm_source'] : null,
			'utm_medium'      => '' !== $lead['utm_medium'] ? $lead['utm_medium'] : null,
			'utm_campaign'    => '' !== $lead['utm_campaign'] ? $lead['utm_campaign'] : null,
			'persetujuan'     => 1,
			'submitted_at'    => $submitted->setTimezone( new DateTimeZone( 'UTC' ) )->format( 'Y-m-d H:i:s' ),
			'body'            => $body,
			'riwayat'         => wp_json_encode( array() ),
			'created_at'      => $now,
			'updated_at'      => $now,
		)
	);

	return $ok ? (int) $wpdb->insert_id : 0;
}

/**
 * Ambil satu baris.
 *
 * @param int $id ID baris.
 * @return object|null
 */
function mea_leads_get( $id ) {
	global $wpdb;
	$table = mea_leads_table();
	return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- tabel milik plugin.
}

/**
 * Ubah kolom satu baris (updated_at diisi otomatis).
 *
 * @param int                  $id     ID baris.
 * @param array<string, mixed> $fields Kolom => nilai.
 * @return void
 */
function mea_leads_update( $id, $fields ) {
	global $wpdb;
	$fields['updated_at'] = mea_leads_now();
	$wpdb->update( mea_leads_table(), $fields, array( 'id' => $id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery -- tabel milik plugin.
}

/**
 * Tambah satu entri ke riwayat percobaan.
 *
 * @param object               $row   Baris lead.
 * @param array<string, mixed> $entry Entri riwayat.
 * @return string JSON riwayat baru.
 */
function mea_leads_history_append( $row, $entry ) {
	$history   = json_decode( (string) $row->riwayat, true );
	$history   = is_array( $history ) ? $history : array();
	$history[] = array_merge( array( 'waktu' => mea_leads_now() ), $entry );
	return wp_json_encode( $history );
}

/**
 * Kunci baris supaya tidak dikirim dua proses sekaligus.
 *
 * @param int $id ID baris.
 * @return bool True bila kunci didapat.
 */
function mea_leads_claim( $id ) {
	global $wpdb;
	$table = mea_leads_table();
	$now   = mea_leads_now();
	$until = mea_leads_now( 120 );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- tabel milik plugin.
	$affected = $wpdb->query( $wpdb->prepare( "UPDATE {$table} SET kunci_sampai = %s WHERE id = %d AND status = 'menunggu' AND (kunci_sampai IS NULL OR kunci_sampai < %s)", $until, $id, $now ) );
	return 1 === (int) $affected;
}

/**
 * Hapus satu lead yang belum diterima CDPS (status menunggu atau gagal), beserta
 * jadwal kirimnya. Lead terkirim tidak bisa dihapus dari sini: datanya sudah ada
 * di CDPS. Baris yang sedang dikirim (terkunci) juga dilewati.
 *
 * @param int $id ID baris.
 * @return bool True bila baris terhapus.
 */
function mea_leads_delete( $id ) {
	global $wpdb;
	$table = mea_leads_table();
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- tabel milik plugin.
	$deleted = $wpdb->query( $wpdb->prepare( "DELETE FROM {$table} WHERE id = %d AND status <> 'terkirim' AND (kunci_sampai IS NULL OR kunci_sampai < %s)", $id, mea_leads_now() ) );
	if ( 1 !== (int) $deleted ) {
		return false;
	}
	wp_clear_scheduled_hook( 'mea_leads_kirim', array( (int) $id ) );
	return true;
}

/**
 * ID lead menunggu yang sudah waktunya dikirim.
 *
 * @param int $limit Batas jumlah.
 * @return int[]
 */
function mea_leads_due_ids( $limit = 50 ) {
	global $wpdb;
	$table = mea_leads_table();
	$now   = mea_leads_now();
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- tabel milik plugin.
	return array_map( 'intval', $wpdb->get_col( $wpdb->prepare( "SELECT id FROM {$table} WHERE status = 'menunggu' AND (berikutnya_at IS NULL OR berikutnya_at <= %s) AND (kunci_sampai IS NULL OR kunci_sampai < %s) ORDER BY id ASC LIMIT %d", $now, $now, $limit ) ) );
}

/**
 * Retensi: hapus data pribadi baris terkirim yang lebih dari 90 hari.
 *
 * @return int Jumlah baris yang dianonimkan.
 */
function mea_leads_anonymize_old() {
	global $wpdb;
	$table  = mea_leads_table();
	$cutoff = mea_leads_now( -90 * DAY_IN_SECONDS );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- tabel milik plugin.
	return (int) $wpdb->query( $wpdb->prepare( "UPDATE {$table} SET nama = NULL, whatsapp = NULL, link_toko = NULL, utm_source = NULL, utm_medium = NULL, utm_campaign = NULL, body = NULL, dianonimkan_at = %s, updated_at = %s WHERE status = 'terkirim' AND terkirim_at < %s AND dianonimkan_at IS NULL", mea_leads_now(), mea_leads_now(), $cutoff ) );
}

/**
 * Query daftar untuk admin dan ekspor CSV.
 *
 * @param array<string, string> $filters status, sumber, dari (Y-m-d), sampai (Y-m-d) dalam WIB.
 * @param int                   $per_page Baris per halaman. 0 = semua.
 * @param int                   $page     Halaman (mulai 1).
 * @return array{rows: object[], total: int}
 */
function mea_leads_query( $filters, $per_page = 20, $page = 1 ) {
	global $wpdb;
	$table = mea_leads_table();
	$where = array( '1=1' );
	$args  = array();

	if ( ! empty( $filters['status'] ) ) {
		$where[] = 'status = %s';
		$args[]  = $filters['status'];
	}
	if ( ! empty( $filters['sumber'] ) ) {
		$where[] = 'sumber_form = %s';
		$args[]  = $filters['sumber'];
	}
	foreach ( array(
		'dari'   => '>=',
		'sampai' => '<',
	) as $key => $op ) {
		if ( empty( $filters[ $key ] ) || ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $filters[ $key ] ) ) {
			continue;
		}
		$local   = new DateTimeImmutable( $filters[ $key ] . ' 00:00:00', new DateTimeZone( 'Asia/Jakarta' ) );
		$local   = 'sampai' === $key ? $local->modify( '+1 day' ) : $local;
		$where[] = "submitted_at {$op} %s";
		$args[]  = $local->setTimezone( new DateTimeZone( 'UTC' ) )->format( 'Y-m-d H:i:s' );
	}

	$sql_where = implode( ' AND ', $where );
	$limit     = $per_page > 0 ? $wpdb->prepare( ' LIMIT %d OFFSET %d', $per_page, ( max( 1, $page ) - 1 ) * $per_page ) : '';
	// phpcs:disable WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.PreparedSQL.NotPrepared -- tabel milik plugin; $sql_where hanya berisi placeholder.
	$count_sql = "SELECT COUNT(*) FROM {$table} WHERE {$sql_where}";
	$rows_sql  = "SELECT * FROM {$table} WHERE {$sql_where} ORDER BY id DESC{$limit}";
	$total     = (int) ( $args ? $wpdb->get_var( $wpdb->prepare( $count_sql, $args ) ) : $wpdb->get_var( $count_sql ) );
	$rows      = $args ? $wpdb->get_results( $wpdb->prepare( $rows_sql, $args ) ) : $wpdb->get_results( $rows_sql );
	// phpcs:enable

	return array(
		'rows'  => (array) $rows,
		'total' => $total,
	);
}

/**
 * Daftar sumber form yang pernah masuk (untuk filter).
 *
 * @return string[]
 */
function mea_leads_sources() {
	global $wpdb;
	$table = mea_leads_table();
	return $wpdb->get_col( "SELECT DISTINCT sumber_form FROM {$table} ORDER BY sumber_form ASC" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- tabel milik plugin.
}
