<?php
/**
 * Pengaturan → Tracking MEA: status konstanta (tanpa nilai), log kiriman server, dan
 * kirim event uji ke Test Events Meta/TikTok.
 *
 * @package MEA_Orbit
 */

defined( 'ABSPATH' ) || exit;

/**
 * Daftarkan halaman.
 *
 * @return void
 */
function mea_orbit_tracking_admin_menu() {
	add_options_page(
		__( 'Tracking MEA', 'mea-orbit' ),
		__( 'Tracking MEA', 'mea-orbit' ),
		'manage_options',
		'mea-orbit-tracking',
		'mea_orbit_tracking_admin_page'
	);
}
add_action( 'admin_menu', 'mea_orbit_tracking_admin_menu' );

/**
 * Kirim satu event Lead sintetis langsung (tanpa antrian) dengan Test Event Code.
 *
 * @param string $platform  meta | tiktok.
 * @param string $test_code Test Event Code dari Events Manager.
 * @return array{hasil: string, kode: int|null, galat: string, event_id: string}
 */
function mea_orbit_tracking_send_test( $platform, $test_code ) {
	$ev      = array(
		'event_id'    => 'uji-' . wp_generate_uuid4(),
		'event_time'  => time(),
		'url'         => home_url( '/' ),
		'ip'          => isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '',
		'user_agent'  => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '',
		'sumber_form' => 'uji-admin',
	);
	$payload = 'meta' === $platform
		? mea_orbit_tracking_meta_payload( $ev, $test_code )
		: mea_orbit_tracking_tiktok_payload( $ev, mea_orbit_tracking_const( 'MEA_TIKTOK_PIXEL_ID' ), $test_code );

	return mea_orbit_tracking_request( $platform, $payload ) + array( 'event_id' => $ev['event_id'] );
}

/**
 * Halaman Tracking MEA.
 *
 * @return void
 */
function mea_orbit_tracking_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$test = null;
	if ( isset( $_POST['mea_tracking_uji'] ) ) {
		check_admin_referer( 'mea_orbit_tracking_uji' );
		$platform = sanitize_key( wp_unslash( $_POST['platform'] ?? '' ) );
		$code     = sanitize_text_field( wp_unslash( $_POST['test_event_code'] ?? '' ) );
		if ( ! in_array( $platform, mea_orbit_tracking_platforms(), true ) ) {
			$test = array( 'galat' => __( 'Konstanta platform ini belum lengkap.', 'mea-orbit' ) );
		} elseif ( '' === $code ) {
			$test = array( 'galat' => __( 'Isi Test Event Code dari Events Manager.', 'mea-orbit' ) );
		} else {
			$test = mea_orbit_tracking_send_test( $platform, $code );
		}
	}

	$gtm       = mea_orbit_tracking_const( 'MEA_GTM_ID' );
	$constants = array(
		'MEA_GTM_ID'              => '' === $gtm ? 'kosong' : ( mea_orbit_tracking_gtm_valid( $gtm ) ? 'terisi' : 'format salah (harus GTM-XXXX)' ),
		'MEA_META_PIXEL_ID'       => '' === mea_orbit_tracking_const( 'MEA_META_PIXEL_ID' ) ? 'kosong' : 'terisi',
		'MEA_META_CAPI_TOKEN'     => '' === mea_orbit_tracking_const( 'MEA_META_CAPI_TOKEN' ) ? 'kosong' : 'terisi',
		'MEA_TIKTOK_PIXEL_ID'     => '' === mea_orbit_tracking_const( 'MEA_TIKTOK_PIXEL_ID' ) ? 'kosong' : 'terisi',
		'MEA_TIKTOK_EVENTS_TOKEN' => '' === mea_orbit_tracking_const( 'MEA_TIKTOK_EVENTS_TOKEN' ) ? 'kosong' : 'terisi',
	);
	$platforms = mea_orbit_tracking_platforms();

	global $wpdb;
	$table = mea_orbit_tracking_table();
	$logs  = array();
	foreach ( array( 'meta', 'tiktok' ) as $platform ) {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- tabel milik tema.
		$logs[ $platform ] = (array) $wpdb->get_results( $wpdb->prepare( "SELECT created_at, updated_at, event_id, status, kode_http, percobaan, galat FROM {$table} WHERE platform = %s ORDER BY id DESC LIMIT 50", $platform ) );
	}
	$labels = array(
		'meta'   => 'Meta Conversions API',
		'tiktok' => 'TikTok Events API',
	);
	$time   = static function ( $utc ) {
		return $utc ? get_date_from_gmt( $utc, 'Y-m-d H:i' ) : '—';
	};
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Tracking MEA', 'mea-orbit' ); ?></h1>
		<p><?php esc_html_e( 'Tema mencetak snippet GTM saja. Meta Pixel, TikTok Pixel, dan GA4 diatur di GTM. Event Lead dari server dikirim ke Meta dan TikTok dengan event_id yang sama dengan dataLayer. Nilai diatur di wp-config.php; halaman ini tidak pernah menampilkan nilainya.', 'mea-orbit' ); ?></p>

		<h2><?php esc_html_e( 'Konstanta', 'mea-orbit' ); ?></h2>
		<table class="widefat striped" style="max-width:640px">
			<tbody>
			<?php foreach ( $constants as $name => $state ) : ?>
				<tr><td><code><?php echo esc_html( $name ); ?></code></td><td><?php echo esc_html( $state ); ?></td></tr>
			<?php endforeach; ?>
			</tbody>
		</table>

		<h2><?php esc_html_e( 'Kirim event uji', 'mea-orbit' ); ?></h2>
		<?php if ( $test ) : ?>
			<?php $ok = isset( $test['hasil'] ) && 'terkirim' === $test['hasil']; ?>
			<div class="notice <?php echo $ok ? 'notice-success' : 'notice-error'; ?> inline"><p>
				<?php
				if ( $ok ) {
					/* translators: %s: event_id. */
					printf( esc_html__( 'Terkirim. Cari event Lead dengan event_id %s di Test Events.', 'mea-orbit' ), '<code>' . esc_html( $test['event_id'] ) . '</code>' );
				} else {
					echo esc_html( trim( ( isset( $test['kode'] ) ? 'HTTP ' . $test['kode'] . '. ' : '' ) . ( $test['galat'] ?? '' ) ) );
				}
				?>
			</p></div>
		<?php endif; ?>
		<?php if ( $platforms ) : ?>
			<form method="post">
				<?php wp_nonce_field( 'mea_orbit_tracking_uji' ); ?>
				<p>
					<label for="mea-tracking-platform"><?php esc_html_e( 'Platform', 'mea-orbit' ); ?></label>
					<select id="mea-tracking-platform" name="platform">
						<?php foreach ( $platforms as $platform ) : ?>
							<option value="<?php echo esc_attr( $platform ); ?>"><?php echo esc_html( $labels[ $platform ] ); ?></option>
						<?php endforeach; ?>
					</select>
					<label for="mea-tracking-code"><?php esc_html_e( 'Test Event Code', 'mea-orbit' ); ?></label>
					<input id="mea-tracking-code" name="test_event_code" class="regular-text" autocomplete="off" placeholder="TEST12345">
					<button class="button button-primary" name="mea_tracking_uji" value="1"><?php esc_html_e( 'Kirim event uji', 'mea-orbit' ); ?></button>
				</p>
				<p class="description"><?php esc_html_e( 'Kode diambil dari Events Manager → Test events. Event uji langsung dikirim tanpa antrian, tanpa data pribadi, dan kodenya tidak disimpan.', 'mea-orbit' ); ?></p>
			</form>
		<?php else : ?>
			<p><?php esc_html_e( 'Belum ada platform server dengan pixel dan token lengkap.', 'mea-orbit' ); ?></p>
		<?php endif; ?>

		<?php foreach ( $logs as $platform => $rows ) : ?>
			<h2>
				<?php
				/* translators: %s: nama platform. */
				printf( esc_html__( 'Log kiriman: %s (50 terakhir)', 'mea-orbit' ), esc_html( $labels[ $platform ] ) );
				?>
			</h2>
			<?php if ( ! $rows ) : ?>
				<p><?php esc_html_e( 'Belum ada kiriman.', 'mea-orbit' ); ?></p>
			<?php else : ?>
				<table class="widefat striped">
					<thead><tr>
						<th><?php esc_html_e( 'Dibuat', 'mea-orbit' ); ?></th>
						<th><?php esc_html_e( 'Diperbarui', 'mea-orbit' ); ?></th>
						<th>event_id</th>
						<th><?php esc_html_e( 'Status', 'mea-orbit' ); ?></th>
						<th>HTTP</th>
						<th><?php esc_html_e( 'Percobaan', 'mea-orbit' ); ?></th>
						<th><?php esc_html_e( 'Galat', 'mea-orbit' ); ?></th>
					</tr></thead>
					<tbody>
					<?php foreach ( $rows as $row ) : ?>
						<tr>
							<td><?php echo esc_html( $time( $row->created_at ) ); ?></td>
							<td><?php echo esc_html( $time( $row->updated_at ) ); ?></td>
							<td><code><?php echo esc_html( $row->event_id ); ?></code></td>
							<td><?php echo esc_html( $row->status ); ?></td>
							<td><?php echo esc_html( null === $row->kode_http ? '—' : (string) $row->kode_http ); ?></td>
							<td><?php echo esc_html( (string) $row->percobaan ); ?></td>
							<td><?php echo esc_html( (string) $row->galat ); ?></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		<?php endforeach; ?>
	</div>
	<?php
}
