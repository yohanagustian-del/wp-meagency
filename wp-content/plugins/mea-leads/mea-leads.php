<?php
/**
 * Plugin Name: MEA Leads
 * Description: Form konsultasi MEA Agency. Lead disimpan di tabel lokal, tampil di menu "Leads MEA", lalu dikirim ke CDPS dengan tanda tangan HMAC (kontrak mea.website_lead.v1). Endpoint dan secret diatur lewat konstanta MEA_LEADS_ENDPOINT dan MEA_LEADS_SECRET di wp-config.php.
 * Version: 0.2.0
 * Requires at least: 6.4
 * Requires PHP: 8.0
 * Author: PT MEA Digital Marketing
 * License: GPL-2.0-or-later
 * Text Domain: mea-leads
 *
 * @package MEA_Leads
 */

defined( 'ABSPATH' ) || exit;

define( 'MEA_LEADS_VERSION', '0.2.0' );
define( 'MEA_LEADS_DB_VERSION', '1' );
define( 'MEA_LEADS_FILE', __FILE__ );
define( 'MEA_LEADS_DIR', __DIR__ );

require MEA_LEADS_DIR . '/includes/core.php';
require MEA_LEADS_DIR . '/includes/db.php';
require MEA_LEADS_DIR . '/includes/form.php';
require MEA_LEADS_DIR . '/includes/rest.php';
require MEA_LEADS_DIR . '/includes/sender.php';
require MEA_LEADS_DIR . '/includes/cron.php';

if ( is_admin() ) {
	require MEA_LEADS_DIR . '/includes/admin.php';
}

register_activation_hook( __FILE__, 'mea_leads_activate' );
register_deactivation_hook( __FILE__, 'mea_leads_deactivate' );

/**
 * Aktivasi: buat tabel dan jadwalkan cron.
 *
 * @return void
 */
function mea_leads_activate() {
	mea_leads_install_table();
	mea_leads_schedule_cron();
}

/**
 * Deaktivasi: hapus jadwal cron. Data lead tidak disentuh.
 *
 * @return void
 */
function mea_leads_deactivate() {
	wp_clear_scheduled_hook( 'mea_leads_sapu' );
	wp_clear_scheduled_hook( 'mea_leads_retensi' );
	wp_unschedule_hook( 'mea_leads_kirim' );
}

/**
 * Perbarui skema tabel bila versi plugin berubah tanpa aktivasi ulang.
 *
 * @return void
 */
function mea_leads_maybe_upgrade() {
	if ( MEA_LEADS_DB_VERSION !== get_option( 'mea_leads_db_version' ) ) {
		mea_leads_install_table();
	}
	mea_leads_schedule_cron();
}
add_action( 'plugins_loaded', 'mea_leads_maybe_upgrade' );
