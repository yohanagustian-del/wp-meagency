<?php
/**
 * Jadwal WP-Cron: kirim satu lead, sapu per jam, retensi harian.
 *
 * @package MEA_Leads
 */

defined( 'ABSPATH' ) || exit;

/**
 * Pastikan jadwal sapu dan retensi terdaftar.
 *
 * @return void
 */
function mea_leads_schedule_cron() {
	if ( ! wp_next_scheduled( 'mea_leads_sapu' ) ) {
		wp_schedule_event( time() + HOUR_IN_SECONDS, 'hourly', 'mea_leads_sapu' );
	}
	if ( ! wp_next_scheduled( 'mea_leads_retensi' ) ) {
		wp_schedule_event( time() + DAY_IN_SECONDS, 'daily', 'mea_leads_retensi' );
	}
}

/**
 * Event satu lead.
 *
 * @param int $id ID baris.
 * @return void
 */
function mea_leads_cron_send( $id ) {
	mea_leads_send( (int) $id );
}
add_action( 'mea_leads_kirim', 'mea_leads_cron_send' );

/**
 * Sapu per jam: kirim lead menunggu yang sudah waktunya (termasuk yang tersimpan
 * sebelum konstanta diisi, dan event cron yang hilang).
 *
 * @return void
 */
function mea_leads_cron_sweep() {
	if ( ! mea_leads_configured() ) {
		return;
	}
	foreach ( mea_leads_due_ids( 50 ) as $id ) {
		mea_leads_send( $id );
	}
}
add_action( 'mea_leads_sapu', 'mea_leads_cron_sweep' );

/**
 * Retensi harian (brief 6.3): data pribadi dihapus 90 hari setelah terkirim.
 *
 * @return void
 */
function mea_leads_cron_retention() {
	mea_leads_anonymize_old();
}
add_action( 'mea_leads_retensi', 'mea_leads_cron_retention' );
