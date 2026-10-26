<?php
/**
 * Uninstall: jadwal cron dan opsi dihapus. Tabel lead TIDAK dihapus, kecuali
 * konstanta MEA_LEADS_HAPUS_SAAT_UNINSTALL bernilai true di wp-config.php.
 *
 * @package MEA_Leads
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

wp_clear_scheduled_hook( 'mea_leads_sapu' );
wp_clear_scheduled_hook( 'mea_leads_retensi' );
wp_unschedule_hook( 'mea_leads_kirim' );

if ( defined( 'MEA_LEADS_HAPUS_SAAT_UNINSTALL' ) && true === MEA_LEADS_HAPUS_SAAT_UNINSTALL ) {
	global $wpdb;
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}mea_leads" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- tabel milik plugin, dihapus atas permintaan eksplisit.
	delete_option( 'mea_leads_db_version' );
}
