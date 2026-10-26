<?php
/**
 * Form konsultasi: markup (sama dengan prototipe), shortcode, blok, dan pemroses
 * isian yang dipakai bersama oleh jalur REST (JavaScript) dan admin-post (tanpa JS).
 *
 * @package MEA_Leads
 */

defined( 'ABSPATH' ) || exit;

/**
 * Daftarkan aset form, shortcode, dan blok.
 *
 * @return void
 */
function mea_leads_register_form() {
	wp_register_script(
		'mea-leads-form',
		plugins_url( 'assets/js/form.js', MEA_LEADS_FILE ),
		array(),
		MEA_LEADS_VERSION,
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);
	wp_register_style( 'mea-leads-form', plugins_url( 'assets/css/form.css', MEA_LEADS_FILE ), array(), MEA_LEADS_VERSION );
	add_shortcode( 'mea_lead_form', 'mea_leads_shortcode' );
	register_block_type( MEA_LEADS_DIR . '/blocks/lead-form' );
}
add_action( 'init', 'mea_leads_register_form' );

/**
 * Shortcode [mea_lead_form sumber="..."].
 *
 * @param array<string, string>|string $atts Atribut.
 * @return string
 */
function mea_leads_shortcode( $atts ) {
	$atts = shortcode_atts( array( 'sumber' => '' ), $atts, 'mea_lead_form' );
	return mea_leads_form( array( 'sumber' => $atts['sumber'] ) );
}

/**
 * Token waktu bertanda tangan untuk jebakan waktu (isian < 3 detik dianggap bot).
 *
 * @param int $time Waktu Unix.
 * @return string
 */
function mea_leads_time_token( $time ) {
	return $time . '.' . substr( wp_hash( 'mea_leads_t' . $time, 'nonce' ), 0, 16 );
}

/**
 * Markup form. Dipakai tema (mea_leads_form()), shortcode, dan blok.
 *
 * @param array<string, string> $args sumber (sumber_form).
 * @return string
 */
function mea_leads_form( $args = array() ) {
	static $instance = 0;
	++$instance;
	$sfx = 1 === $instance ? '' : '-' . $instance;
	$id  = static function ( $name ) use ( $sfx ) {
		return esc_attr( 'f-' . $name . $sfx );
	};

	$source = preg_replace( '/[^a-z0-9-]/', '', strtolower( (string) ( $args['sumber'] ?? '' ) ) );
	$path   = (string) wp_parse_url( isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '/', PHP_URL_PATH );
	$sent   = isset( $_GET['mea_terkirim'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- hanya penanda tampilan setelah redirect.
	$failed = isset( $_GET['mea_galat'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- hanya penanda tampilan setelah redirect.
	$sales  = 'https://wa.me/6282117932648';

	if ( ! current_theme_supports( 'mea-leads' ) ) {
		wp_enqueue_style( 'mea-leads-form' );
	}
	wp_enqueue_script( 'mea-leads-form' );
	wp_add_inline_script(
		'mea-leads-form',
		'window.meaLeads=' . wp_json_encode(
			array(
				'token' => rest_url( 'mea-leads/v1/token' ),
				'kirim' => rest_url( 'mea-leads/v1/kirim' ),
				'wa'    => $sales,
				'galat' => __( 'Data belum terkirim. Coba lagi beberapa saat lagi, atau hubungi tim sales lewat WhatsApp.', 'mea-leads' ),
			)
		) . ';',
		'before'
	);

	ob_start();
	?>
	<form class="lead-form<?php echo $sent ? ' is-sent' : ''; ?>" id="lead-form<?php echo esc_attr( $sfx ); ?>" novalidate data-source="<?php echo esc_attr( $source ); ?>" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php if ( $failed ) : ?>
			<p class="err" role="alert"><?php esc_html_e( 'Data belum terkirim. Periksa isian lalu coba lagi, atau hubungi tim sales lewat WhatsApp.', 'mea-leads' ); ?></p>
		<?php endif; ?>
		<div class="row2">
			<div class="field"><label for="<?php echo $id( 'nama' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- sudah esc_attr. ?>"><?php esc_html_e( 'Nama Anda', 'mea-leads' ); ?></label><input id="<?php echo $id( 'nama' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" name="nama" autocomplete="name" required><span class="err" aria-live="polite"></span></div>
			<div class="field"><label for="<?php echo $id( 'wa' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>"><?php esc_html_e( 'Nomor WhatsApp', 'mea-leads' ); ?></label><input id="<?php echo $id( 'wa' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" name="whatsapp" inputmode="tel" autocomplete="tel" placeholder="08xx-xxxx-xxxx" required><span class="err" aria-live="polite"></span></div>
		</div>
		<div class="field"><label for="<?php echo $id( 'toko' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>"><?php esc_html_e( 'Link toko atau akun Instagram', 'mea-leads' ); ?></label><input id="<?php echo $id( 'toko' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" name="link_toko" inputmode="url" placeholder="shopee.co.id/namatoko"><span class="hint"><?php esc_html_e( 'Supaya tim bisa membaca kondisi toko sebelum menghubungi Anda.', 'mea-leads' ); ?></span></div>
		<div class="row2">
			<div class="field"><label for="<?php echo $id( 'omzet' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>"><?php esc_html_e( 'Omzet per bulan', 'mea-leads' ); ?></label>
				<select id="<?php echo $id( 'omzet' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" name="omzet" required>
					<option value=""><?php esc_html_e( 'Pilih kisaran', 'mea-leads' ); ?></option>
					<?php foreach ( mea_leads_omzet_options() as $value => $label ) : ?>
						<option value="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select><span class="err" aria-live="polite"></span></div>
			<div class="field"><label for="<?php echo $id( 'layanan' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>"><?php esc_html_e( 'Yang ingin dibantu', 'mea-leads' ); ?></label>
				<select id="<?php echo $id( 'layanan' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" name="layanan">
					<option value=""><?php esc_html_e( 'Belum tahu, minta saran', 'mea-leads' ); ?></option>
					<?php foreach ( mea_leads_layanan_options() as $label ) : ?>
						<option><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select><span class="err" aria-live="polite"></span></div>
		</div>
		<input type="hidden" name="action" value="mea_leads_kirim">
		<input type="hidden" name="kanal" value="website"><input type="hidden" name="site" value="<?php echo esc_attr( mea_leads_site() ); ?>">
		<input type="hidden" name="sumber_form" value="<?php echo esc_attr( $source ); ?>">
		<input type="hidden" name="halaman_asal" value="<?php echo esc_attr( $path ? $path : '/' ); ?>">
		<input type="hidden" name="utm_source" value=""><input type="hidden" name="utm_medium" value=""><input type="hidden" name="utm_campaign" value="">
		<input type="hidden" name="_mea_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mea_leads_kirim' ) ); ?>">
		<input type="hidden" name="mea_t" value="<?php echo esc_attr( mea_leads_time_token( time() ) ); ?>">
		<div class="hp" aria-hidden="true"><label for="<?php echo $id( 'web' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>">Website</label><input id="<?php echo $id( 'web' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" name="website" tabindex="-1" autocomplete="off"></div>
		<label class="consent"><input type="checkbox" name="setuju" value="1" required> <span><?php esc_html_e( 'Saya setuju dihubungi tim MEA Agency lewat WhatsApp terkait konsultasi ini.', 'mea-leads' ); ?></span></label>
		<span class="err" id="err-consent<?php echo esc_attr( $sfx ); ?>" aria-live="polite"></span>
		<p class="err err-form" role="alert" hidden></p>
		<button class="btn btn-primary" type="submit"><?php esc_html_e( 'Kirim dan jadwalkan konsultasi', 'mea-leads' ); ?></button>
		<div class="thanks" role="status" tabindex="-1">
			<h3 class="display" style="font-size:2rem"><?php esc_html_e( 'Terima kasih, data Anda sudah kami terima', 'mea-leads' ); ?></h3>
			<p><?php esc_html_e( 'Tim kami akan menghubungi Anda lewat WhatsApp pada jam kerja. Kalau ingin mulai sekarang, kirim pesan langsung ke tim sales.', 'mea-leads' ); ?></p>
			<a class="btn btn-primary wa-cs" href="<?php echo esc_url( $sales ); ?>"><?php esc_html_e( 'Chat tim sales sekarang', 'mea-leads' ); ?></a>
		</div>
	</form>
	<?php
	return (string) ob_get_clean();
}

/**
 * Alamat IP klien untuk rate limit. Bisa diganti lewat filter mea_leads_client_ip
 * bila situs berada di belakang CDN atau proxy.
 *
 * @return string
 */
function mea_leads_client_ip() {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	return (string) apply_filters( 'mea_leads_client_ip', $ip );
}

/**
 * Proses satu isian form.
 *
 * @param array<string, mixed> $input Isian mentah (sudah di-unslash).
 * @return array{status: string, errors?: array<string, string>, id?: int}
 *         status: ok | invalid | spam | sesi | batas
 */
function mea_leads_handle_submission( $input ) {
	if ( ! wp_verify_nonce( (string) ( $input['_mea_nonce'] ?? '' ), 'mea_leads_kirim' ) ) {
		return array( 'status' => 'sesi' );
	}

	// Honeypot dan jebakan waktu: bot mendapat jawaban sukses, tanpa data disimpan.
	if ( '' !== trim( (string) ( $input['website'] ?? '' ) ) ) {
		return array( 'status' => 'spam' );
	}
	$parts = explode( '.', (string) ( $input['mea_t'] ?? '' ) );
	$time  = (int) $parts[0];
	if ( 2 !== count( $parts ) || ! hash_equals( mea_leads_time_token( $time ), (string) $input['mea_t'] ) || time() - $time < 3 ) {
		return array( 'status' => 'spam' );
	}

	$key   = 'mea_leads_rl_' . md5( mea_leads_client_ip() . wp_salt( 'nonce' ) );
	$count = (int) get_transient( $key );
	if ( $count >= (int) apply_filters( 'mea_leads_rate_limit', 5 ) ) {
		return array( 'status' => 'batas' );
	}

	$checked = mea_leads_validate( $input );
	if ( $checked['errors'] ) {
		return array(
			'status' => 'invalid',
			'errors' => $checked['errors'],
		);
	}

	set_transient( $key, $count + 1, 10 * MINUTE_IN_SECONDS );

	$id = mea_leads_insert( $checked['data'] );
	if ( ! $id ) {
		return array(
			'status' => 'invalid',
			'errors' => array( 'form' => __( 'Data belum terkirim. Coba lagi beberapa saat lagi, atau hubungi tim sales lewat WhatsApp.', 'mea-leads' ) ),
		);
	}

	mea_leads_queue( $id );

	return array(
		'status' => 'ok',
		'id'     => $id,
	);
}
