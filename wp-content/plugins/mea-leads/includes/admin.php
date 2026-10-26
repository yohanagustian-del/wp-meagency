<?php
/**
 * Menu wp-admin "Leads MEA": daftar, filter, kirim ulang, ekspor CSV, riwayat.
 *
 * @package MEA_Leads
 */

defined( 'ABSPATH' ) || exit;

require_once MEA_LEADS_DIR . '/includes/class-mea-leads-list-table.php';

/**
 * Daftarkan menu.
 *
 * @return void
 */
function mea_leads_admin_menu() {
	add_menu_page( __( 'Leads MEA', 'mea-leads' ), __( 'Leads MEA', 'mea-leads' ), 'manage_options', 'mea-leads', 'mea_leads_admin_page', 'dashicons-email-alt', 26 );
}
add_action( 'admin_menu', 'mea_leads_admin_menu' );

/**
 * Filter dari query string.
 *
 * @return array<string, string>
 */
function mea_leads_admin_filters() {
	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- filter tampilan, bukan aksi.
	$filters = array(
		'status' => isset( $_GET['status'] ) ? sanitize_key( wp_unslash( $_GET['status'] ) ) : '',
		'sumber' => isset( $_GET['sumber'] ) ? sanitize_text_field( wp_unslash( $_GET['sumber'] ) ) : '',
		'dari'   => isset( $_GET['dari'] ) ? sanitize_text_field( wp_unslash( $_GET['dari'] ) ) : '',
		'sampai' => isset( $_GET['sampai'] ) ? sanitize_text_field( wp_unslash( $_GET['sampai'] ) ) : '',
	);
	// phpcs:enable
	if ( ! in_array( $filters['status'], array( '', 'menunggu', 'terkirim', 'gagal' ), true ) ) {
		$filters['status'] = '';
	}
	return $filters;
}

/**
 * Halaman admin.
 *
 * @return void
 */
function mea_leads_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- hanya memilih tampilan.
	$lead = isset( $_GET['lead'] ) ? absint( $_GET['lead'] ) : 0;
	if ( $lead ) {
		mea_leads_admin_detail( $lead );
		return;
	}

	$filters        = mea_leads_admin_filters();
	$table          = new MEA_Leads_List_Table();
	$table->filters = $filters;
	$table->prepare_items();

	echo '<div class="wrap"><h1 class="wp-heading-inline">' . esc_html__( 'Leads MEA', 'mea-leads' ) . '</h1>';
	$export = wp_nonce_url( add_query_arg( array_merge( array( 'action' => 'mea_leads_ekspor' ), array_filter( $filters ) ), admin_url( 'admin-post.php' ) ), 'mea_leads_ekspor' );
	echo ' <a class="page-title-action" href="' . esc_url( $export ) . '">' . esc_html__( 'Ekspor CSV', 'mea-leads' ) . '</a><hr class="wp-header-end">';

	mea_leads_admin_notices();

	echo '<form method="get"><input type="hidden" name="page" value="mea-leads"><div class="tablenav top" style="height:auto"><div class="alignleft actions" style="display:flex;gap:6px;flex-wrap:wrap;align-items:center">';
	echo '<label class="screen-reader-text" for="mea-status">' . esc_html__( 'Status', 'mea-leads' ) . '</label><select id="mea-status" name="status"><option value="">' . esc_html__( 'Semua status', 'mea-leads' ) . '</option>';
	foreach ( array( 'menunggu', 'terkirim', 'gagal' ) as $status ) {
		printf( '<option value="%1$s"%2$s>%1$s</option>', esc_attr( $status ), selected( $filters['status'], $status, false ) );
	}
	echo '</select><label class="screen-reader-text" for="mea-sumber">' . esc_html__( 'Sumber form', 'mea-leads' ) . '</label><select id="mea-sumber" name="sumber"><option value="">' . esc_html__( 'Semua sumber', 'mea-leads' ) . '</option>';
	foreach ( mea_leads_sources() as $source ) {
		printf( '<option value="%1$s"%2$s>%1$s</option>', esc_attr( $source ), selected( $filters['sumber'], $source, false ) );
	}
	echo '</select>';
	printf( '<label for="mea-dari">%1$s</label><input id="mea-dari" type="date" name="dari" value="%2$s"><label for="mea-sampai">%3$s</label><input id="mea-sampai" type="date" name="sampai" value="%4$s">', esc_html__( 'Dari', 'mea-leads' ), esc_attr( $filters['dari'] ), esc_html__( 'sampai', 'mea-leads' ), esc_attr( $filters['sampai'] ) );
	submit_button( __( 'Saring', 'mea-leads' ), '', '', false );
	echo '</div></div></form>';

	echo '<form method="post">';
	$table->display();
	echo '</form></div>';
}

/**
 * Pemberitahuan di halaman Leads MEA.
 *
 * @return void
 */
function mea_leads_admin_notices() {
	if ( ! mea_leads_configured() ) {
		echo '<div class="notice notice-warning"><p>' . esc_html__( 'MEA_LEADS_ENDPOINT dan MEA_LEADS_SECRET belum diisi di wp-config.php. Lead tetap tersimpan dengan status menunggu dan dikirim otomatis setelah keduanya diisi.', 'mea-leads' ) . '</p></div>';
	}
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- hanya pesan hasil.
	$done = isset( $_GET['dijadwalkan'] ) ? absint( $_GET['dijadwalkan'] ) : null;
	if ( null !== $done ) {
		/* translators: %d: jumlah lead. */
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html( sprintf( __( '%d lead dijadwalkan untuk dikirim ulang.', 'mea-leads' ), $done ) ) . '</p></div>';
	}
}

/**
 * Halaman riwayat satu lead.
 *
 * @param int $id ID lead.
 * @return void
 */
function mea_leads_admin_detail( $id ) {
	$row = mea_leads_get( $id );
	echo '<div class="wrap"><h1>' . esc_html__( 'Riwayat lead', 'mea-leads' ) . '</h1><p><a href="' . esc_url( admin_url( 'admin.php?page=mea-leads' ) ) . '">&larr; ' . esc_html__( 'Kembali ke daftar', 'mea-leads' ) . '</a></p>';
	if ( ! $row ) {
		echo '<p>' . esc_html__( 'Lead tidak ditemukan.', 'mea-leads' ) . '</p></div>';
		return;
	}
	$fields = array(
		'idempotency_key' => 'Idempotency key',
		'status'          => 'Status',
		'site'            => 'Site',
		'sumber_form'     => 'Sumber form',
		'halaman_asal'    => 'Halaman asal',
		'nama'            => 'Nama',
		'whatsapp'        => 'WhatsApp',
		'link_toko'       => 'Link toko',
		'omzet'           => 'Omzet',
		'layanan'         => 'Layanan',
		'utm_source'      => 'UTM source',
		'utm_medium'      => 'UTM medium',
		'utm_campaign'    => 'UTM campaign',
		'cdps_lead_id'    => 'ID lead CDPS',
		'submitted_at'    => 'Dikirim pengunjung (UTC)',
		'terkirim_at'     => 'Diterima CDPS (UTC)',
		'dianonimkan_at'  => 'Data pribadi dihapus (UTC)',
	);
	echo '<table class="widefat striped" style="max-width:900px"><tbody>';
	foreach ( $fields as $key => $label ) {
		printf( '<tr><th style="width:240px">%1$s</th><td>%2$s</td></tr>', esc_html( $label ), esc_html( '' !== (string) $row->$key ? (string) $row->$key : '—' ) );
	}
	echo '</tbody></table><h2>' . esc_html__( 'Percobaan pengiriman', 'mea-leads' ) . '</h2><table class="widefat striped" style="max-width:900px"><thead><tr><th>Waktu (UTC)</th><th>Percobaan</th><th>Kode HTTP</th><th>Hasil</th><th>Keterangan</th></tr></thead><tbody>';
	$history = json_decode( (string) $row->riwayat, true );
	foreach ( is_array( $history ) ? $history : array() as $entry ) {
		$note = trim( implode( ' ', array_filter( array( $entry['galat'] ?? '', isset( $entry['lead_id'] ) ? 'lead ' . $entry['lead_id'] : '', isset( $entry['berikutnya'] ) ? 'coba lagi ' . $entry['berikutnya'] : '', isset( $entry['oleh'] ) ? 'oleh ' . $entry['oleh'] : '' ) ) ) );
		printf( '<tr><td>%1$s</td><td>%2$s</td><td>%3$s</td><td>%4$s</td><td>%5$s</td></tr>', esc_html( $entry['waktu'] ?? '' ), esc_html( (string) ( $entry['percobaan'] ?? '' ) ), esc_html( null === ( $entry['kode'] ?? null ) ? '—' : (string) $entry['kode'] ), esc_html( $entry['hasil'] ?? '' ), esc_html( $note ) );
	}
	echo '</tbody></table></div>';
}

/**
 * Kirim ulang satu lead dari aksi baris.
 *
 * @return void
 */
function mea_leads_admin_resend_one() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Anda tidak punya akses.', 'mea-leads' ), 403 );
	}
	check_admin_referer( 'mea_leads_kirim_ulang' );
	$done = mea_leads_resend( isset( $_GET['lead'] ) ? absint( $_GET['lead'] ) : 0 ) ? 1 : 0;
	wp_safe_redirect( admin_url( 'admin.php?page=mea-leads&dijadwalkan=' . $done ) );
	exit;
}
add_action( 'admin_post_mea_leads_kirim_ulang', 'mea_leads_admin_resend_one' );

/**
 * Aksi massal "Kirim ulang" dari tabel daftar (nonce bawaan WP_List_Table).
 *
 * @return void
 */
function mea_leads_admin_bulk() {
	if ( empty( $_POST['lead'] ) || ! current_user_can( 'manage_options' ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce dicek di bawah.
		return;
	}
	$table = new MEA_Leads_List_Table();
	if ( 'kirim-ulang' !== $table->current_action() ) {
		return;
	}
	check_admin_referer( 'bulk-leads' );
	$done = 0;
	foreach ( array_map( 'absint', (array) wp_unslash( $_POST['lead'] ) ) as $id ) {
		$done += mea_leads_resend( $id ) ? 1 : 0;
	}
	wp_safe_redirect( admin_url( 'admin.php?page=mea-leads&dijadwalkan=' . $done ) );
	exit;
}
add_action( 'load-toplevel_page_mea-leads', 'mea_leads_admin_bulk' );

/**
 * Ekspor CSV sesuai filter aktif.
 *
 * @return void
 */
function mea_leads_admin_export() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Anda tidak punya akses.', 'mea-leads' ), 403 );
	}
	check_admin_referer( 'mea_leads_ekspor' );

	$rows    = mea_leads_query( mea_leads_admin_filters(), 0 )['rows'];
	$columns = array( 'id', 'submitted_at', 'status', 'nama', 'whatsapp', 'link_toko', 'omzet', 'layanan', 'sumber_form', 'halaman_asal', 'utm_source', 'utm_medium', 'utm_campaign', 'percobaan', 'cdps_lead_id', 'terkirim_at', 'dianonimkan_at', 'idempotency_key' );

	nocache_headers();
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=leads-mea-' . gmdate( 'Ymd-His' ) . '.csv' );

	$out = fopen( 'php://output', 'w' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen -- stream keluaran unduhan.
	fwrite( $out, "\xEF\xBB\xBF" ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite -- BOM agar Excel membaca UTF-8.
	fputcsv( $out, $columns );
	foreach ( $rows as $row ) {
		$line = array();
		foreach ( $columns as $column ) {
			$line[] = mea_leads_csv_cell( $row->$column ?? '' );
		}
		fputcsv( $out, $line );
	}
	fclose( $out ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- stream keluaran unduhan.
	exit;
}
add_action( 'admin_post_mea_leads_ekspor', 'mea_leads_admin_export' );
