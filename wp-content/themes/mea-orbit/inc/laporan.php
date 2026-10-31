<?php
/**
 * Laporan HTML: laporan klien (weekly, monthly, final report) tampil tanpa Elementor.
 *
 * Laporan adalah dokumen HTML utuh (<!DOCTYPE html>…) buatan tim. Dulu ditempel ke satu
 * widget HTML Elementor; sekarang dicetak apa adanya oleh tema, satu halaman penuh tanpa
 * header/footer situs, sama seperti dokumen itu dirancang. Dua sumber:
 *
 * 1. Halaman dengan template "Laporan HTML": isi field "Kode HTML laporan"
 *    (meta `mea_laporan_html`), atau isi editor (tab Text) bila field kosong.
 * 2. Laporan lama dari Elementor: halaman builder yang isinya hanya widget HTML berisi
 *    dokumen utuh, dan berpassword, private, atau noindex. Dibaca langsung dari
 *    `_elementor_data`, jadi tetap tampil saat Elementor nonaktif. Halaman pemasaran
 *    (terindeks, tanpa password) tidak disentuh.
 *
 * Halaman berpassword tetap meminta password dulu (form bergaya tema). Laporan tidak pernah
 * diindeks: header X-Robots-Tag dan meta robots noindex, nofollow.
 *
 * @package MEA_Orbit
 */

defined( 'ABSPATH' ) || exit;

const MEA_ORBIT_LAPORAN_TEMPLATE = 'page-templates/template-laporan.php';

/**
 * Apakah teks HTML adalah dokumen utuh (diawali <!DOCTYPE html> atau <html>, boleh
 * didahului komentar dan spasi).
 *
 * @param string $html HTML.
 * @return bool
 */
function mea_orbit_laporan_is_document( $html ) {
	return 1 === preg_match( '/^\s*(?:<!--.*?-->\s*)*<(?:!doctype\s+html|html)[\s>]/is', (string) $html );
}

/**
 * Gabungan isi widget HTML dari data Elementor, atau null bila ada widget lain.
 *
 * @param mixed $elements Array elemen Elementor (hasil json_decode).
 * @return string[]|null
 */
function mea_orbit_laporan_html_widgets( $elements ) {
	$out = array();
	if ( ! is_array( $elements ) ) {
		return $out;
	}
	foreach ( $elements as $el ) {
		if ( ! is_array( $el ) ) {
			continue;
		}
		if ( 'widget' === ( $el['elType'] ?? '' ) ) {
			if ( 'html' !== ( $el['widgetType'] ?? '' ) ) {
				return null;
			}
			$out[] = (string) ( $el['settings']['html'] ?? '' );
			continue;
		}
		$inner = mea_orbit_laporan_html_widgets( $el['elements'] ?? array() );
		if ( null === $inner ) {
			return null;
		}
		$out = array_merge( $out, $inner );
	}
	return $out;
}

/**
 * HTML laporan dari data Elementor: hanya bila halaman builder berisi widget HTML saja.
 *
 * @param int $post_id ID halaman.
 * @return string Kosong bila bukan laporan Elementor.
 */
function mea_orbit_laporan_elementor_html( $post_id ) {
	if ( 'builder' !== get_post_meta( $post_id, '_elementor_edit_mode', true ) ) {
		return '';
	}
	$data = get_post_meta( $post_id, '_elementor_data', true );
	$data = is_string( $data ) ? json_decode( $data, true ) : $data;
	$html = mea_orbit_laporan_html_widgets( $data );

	return $html ? trim( implode( "\n", $html ) ) : '';
}

/**
 * Halaman tidak untuk publik umum: berpassword, private, atau noindex di Rank Math.
 *
 * @param WP_Post $post Halaman.
 * @return bool
 */
function mea_orbit_laporan_is_closed( $post ) {
	if ( '' !== (string) $post->post_password || 'private' === $post->post_status ) {
		return true;
	}
	$robots = get_post_meta( $post->ID, 'rank_math_robots', true );
	return is_array( $robots ) && in_array( 'noindex', $robots, true );
}

/**
 * HTML laporan untuk halaman ini, atau string kosong bila halaman bukan laporan.
 *
 * @param int|WP_Post|null $post Halaman (null = yang sedang dibuka).
 * @return string
 */
function mea_orbit_laporan_html( $post = null ) {
	static $cache = array();

	$post = get_post( $post );
	if ( ! $post instanceof WP_Post || 'page' !== $post->post_type ) {
		return '';
	}
	if ( isset( $cache[ $post->ID ] ) ) {
		return $cache[ $post->ID ];
	}

	$html = '';
	if ( MEA_ORBIT_LAPORAN_TEMPLATE === get_page_template_slug( $post ) ) {
		$html = trim( (string) get_post_meta( $post->ID, 'mea_laporan_html', true ) );
		if ( '' === $html ) {
			$html = trim( (string) $post->post_content );
		}
		if ( '' === $html ) {
			$html = mea_orbit_laporan_elementor_html( $post->ID );
		}
	} elseif ( mea_orbit_laporan_is_closed( $post ) ) {
		$doc  = mea_orbit_laporan_elementor_html( $post->ID );
		$html = mea_orbit_laporan_is_document( $doc ) ? $doc : '';
	}

	$cache[ $post->ID ] = $html;
	return $html;
}

/**
 * Siapkan dokumen untuk dicetak: meta robots noindex di <head>. Potongan HTML (bukan
 * dokumen utuh) dibungkus dokumen minimal.
 *
 * @param string $html  HTML laporan.
 * @param string $title Judul halaman (dipakai bila HTML bukan dokumen utuh).
 * @return string
 */
function mea_orbit_laporan_document( $html, $title ) {
	$robots = '<meta name="robots" content="noindex, nofollow">';

	if ( ! mea_orbit_laporan_is_document( $html ) ) {
		return "<!DOCTYPE html>\n<html lang=\"id\"><head><meta charset=\"UTF-8\"><meta name=\"viewport\" content=\"width=device-width,initial-scale=1\">"
			. $robots . '<title>' . esc_html( $title ) . "</title></head><body>\n" . $html . "\n</body></html>\n";
	}

	$with_meta = preg_replace( '/<head(\s[^>]*)?>/i', '$0' . $robots, $html, 1, $count );
	if ( $count ) {
		return $with_meta;
	}
	// Dokumen tanpa <head>: sisipkan setelah tag <html>.
	return (string) preg_replace( '/<html(\s[^>]*)?>/i', '$0<head>' . $robots . '</head>', $html, 1 );
}

/**
 * Halaman laporan memakai template-laporan.php, termasuk laporan Elementor lama.
 * Editor Elementor (?elementor-preview) tetap memakai template biasa.
 *
 * @param string $template Path template yang dipilih.
 * @return string
 */
function mea_orbit_laporan_template_include( $template ) {
	if ( ! is_page() || isset( $_GET['elementor-preview'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- hanya membaca ada/tidaknya parameter.
		return $template;
	}
	$post = get_queried_object();
	if ( ! $post instanceof WP_Post ) {
		return $template;
	}
	if ( MEA_ORBIT_LAPORAN_TEMPLATE === get_page_template_slug( $post ) || '' !== mea_orbit_laporan_html( $post ) ) {
		return MEA_ORBIT_DIR . '/' . MEA_ORBIT_LAPORAN_TEMPLATE;
	}
	return $template;
}
add_filter( 'template_include', 'mea_orbit_laporan_template_include', 100 );

/**
 * Laporan dengan template "Laporan HTML" tidak masuk sitemap. Laporan Elementor lama
 * sudah keluar karena berpassword/private/noindex.
 *
 * @param array<string, mixed>|false $url    Entri sitemap.
 * @param string                     $type   Jenis entri.
 * @param object                     $object Objek entri.
 * @return array<string, mixed>|false
 */
function mea_orbit_laporan_sitemap_entry( $url, $type, $object ) {
	if ( 'post' === $type && $object instanceof WP_Post && MEA_ORBIT_LAPORAN_TEMPLATE === get_page_template_slug( $object ) ) {
		return false;
	}
	return $url;
}
add_filter( 'rank_math/sitemap/entry', 'mea_orbit_laporan_sitemap_entry', 99, 3 );

/**
 * Laporan tidak diindeks, juga saat tampil lewat tema (form password).
 *
 * @param array<string, string> $robots Direktif robots dari Rank Math.
 * @return array<string, string>
 */
function mea_orbit_laporan_robots( $robots ) {
	if ( is_page() && MEA_ORBIT_LAPORAN_TEMPLATE === get_page_template_slug( get_queried_object_id() ) ) {
		$robots['index']  = 'noindex';
		$robots['follow'] = 'nofollow';
	}
	return $robots;
}
add_filter( 'rank_math/frontend/robots', 'mea_orbit_laporan_robots', 99 );

/**
 * Field "Kode HTML laporan". Disimpan apa adanya hanya bila pengguna boleh menulis HTML
 * tanpa filter (unfiltered_html: admin dan editor); selain itu disaring wp_kses_post,
 * sehingga <script> dan <style> hilang.
 *
 * @return void
 */
function mea_orbit_laporan_register_meta() {
	register_post_meta(
		'page',
		'mea_laporan_html',
		array(
			'type'              => 'string',
			'single'            => true,
			'default'           => '',
			'show_in_rest'      => true,
			'sanitize_callback' => static function ( $value ) {
				$value = (string) $value;
				return current_user_can( 'unfiltered_html' ) ? $value : wp_kses_post( $value );
			},
			'auth_callback'     => static function ( $allowed, $meta_key, $post_id ) {
				return current_user_can( 'edit_post', $post_id );
			},
		)
	);
}
add_action( 'init', 'mea_orbit_laporan_register_meta' );

/**
 * Meta box di halaman yang memakai template "Laporan HTML".
 *
 * @param string  $post_type Post type.
 * @param WP_Post $post      Halaman yang diedit.
 * @return void
 */
function mea_orbit_laporan_add_meta_box( $post_type, $post ) {
	if ( 'page' !== $post_type || MEA_ORBIT_LAPORAN_TEMPLATE !== get_page_template_slug( $post ) ) {
		return;
	}
	add_meta_box( 'mea-orbit-laporan', __( 'Kode HTML laporan', 'mea-orbit' ), 'mea_orbit_laporan_render_meta_box', 'page', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'mea_orbit_laporan_add_meta_box', 10, 2 );

/**
 * Isi meta box: satu textarea untuk dokumen HTML utuh.
 *
 * @param WP_Post $post Halaman.
 * @return void
 */
function mea_orbit_laporan_render_meta_box( $post ) {
	wp_nonce_field( 'mea_orbit_laporan', 'mea_orbit_laporan_nonce' );
	echo '<p>' . esc_html__( 'Tempel seluruh kode HTML laporan di sini (dari <!DOCTYPE html> sampai </html>). Halaman tampil penuh tanpa header dan footer situs, tidak diindeks Google, dan tetap meminta password bila password diisi. Kosongkan bila laporan ditulis di editor (tab Text).', 'mea-orbit' ) . '</p>';
	if ( ! current_user_can( 'unfiltered_html' ) ) {
		echo '<p><strong>' . esc_html__( 'Akun Anda tidak boleh menyimpan script dan style. Minta admin atau editor yang menyimpan laporan ini.', 'mea-orbit' ) . '</strong></p>';
	}
	printf(
		'<textarea name="mea_laporan_html" rows="20" class="large-text code" spellcheck="false" style="font-family:monospace">%s</textarea>',
		esc_textarea( (string) get_post_meta( $post->ID, 'mea_laporan_html', true ) )
	);
}

/**
 * Simpan field dari meta box.
 *
 * @param int $post_id ID halaman.
 * @return void
 */
function mea_orbit_laporan_save_meta_box( $post_id ) {
	if ( ! isset( $_POST['mea_orbit_laporan_nonce'], $_POST['mea_laporan_html'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['mea_orbit_laporan_nonce'] ) ), 'mea_orbit_laporan' ) ) {
		return;
	}
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	// Disanitasi oleh sanitize_callback register_post_meta (unfiltered_html atau wp_kses_post).
	update_post_meta( $post_id, 'mea_laporan_html', wp_unslash( $_POST['mea_laporan_html'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
}
add_action( 'save_post_page', 'mea_orbit_laporan_save_meta_box' );
