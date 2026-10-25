<?php
/**
 * Fungsi tampilan bersama: kontak sales, menu, breadcrumb, judul, form password.
 *
 * @package MEA_Orbit
 */

defined( 'ABSPATH' ) || exit;

/**
 * Nomor sales tunggal (brief bagian 1).
 *
 * @return array{tampil:string, wa:string}
 */
function mea_orbit_sales() {
	return array(
		'tampil' => '0821-1793-2648',
		'wa'     => 'https://wa.me/6282117932648',
	);
}

/**
 * Tujuan tombol "Jadwalkan konsultasi": form di halaman ini bila ada,
 * kalau tidak, form di homepage.
 *
 * @return string
 */
function mea_orbit_consult_url() {
	return mea_orbit_is_view() ? '#konsultasi' : home_url( '/#konsultasi' );
}

/**
 * Isi menu cadangan bila lokasi menu belum diatur di wp-admin.
 *
 * @param string $location Lokasi menu.
 * @return array<int, array{0:string, 1:string}> Pasangan [label, URL].
 */
function mea_orbit_fallback_menu_items( $location ) {
	$blog_id  = (int) get_option( 'page_for_posts' );
	$blog_url = $blog_id ? get_permalink( $blog_id ) : home_url( '/blog/' );

	if ( 'mea-orbit-footer' === $location ) {
		return array(
			array( __( 'Iklan Shopee dan TikTok Shop', 'mea-orbit' ), home_url( '/jasa-performance-ads/' ) ),
			array( __( 'Kelola TikTok Shop', 'mea-orbit' ), home_url( '/jasa-kelola-tiktok-shop/' ) ),
			array( __( 'Kelola Shopee', 'mea-orbit' ), home_url( '/ecommerce-service/' ) ),
			array( __( 'Live streaming', 'mea-orbit' ), home_url( '/jasa-live-streaming/' ) ),
			array( __( 'Affiliate', 'mea-orbit' ), home_url( '/affiliate/' ) ),
			array( __( 'Konten', 'mea-orbit' ), home_url( '/jasa-konten/' ) ),
			array( __( 'Studi kasus', 'mea-orbit' ), home_url( '/study-case/' ) ),
			array( __( 'Blog', 'mea-orbit' ), $blog_url ),
		);
	}

	return array(
		array( __( 'Layanan', 'mea-orbit' ), home_url( '/#layanan' ) ),
		array( __( 'Studi kasus', 'mea-orbit' ), home_url( '/study-case/' ) ),
		array( __( 'Tim', 'mea-orbit' ), home_url( '/#tim' ) ),
		array( __( 'Cara kerja', 'mea-orbit' ), home_url( '/#cara-kerja' ) ),
	);
}

/**
 * Cetak menu: menu WordPress bila diatur, cadangan bila belum.
 *
 * Markup cadangan sama dengan markup wp_nav_menu (ul > li > a), jadi gaya
 * dan struktur link tidak berubah saat tim mulai mengatur menu.
 *
 * @param string $location Lokasi menu.
 * @return void
 */
function mea_orbit_menu( $location ) {
	if ( has_nav_menu( $location ) ) {
		wp_nav_menu(
			array(
				'theme_location' => $location,
				'container'      => false,
				'menu_class'     => 'menu',
				'depth'          => 1,
				'fallback_cb'    => false,
			)
		);
		return;
	}

	// Penanda halaman aktif di menu cadangan, sama dengan prototipe:
	// template layanan menandai "Layanan", template studi kasus menandai "Studi kasus".
	$current = '';
	if ( is_page_template( 'page-templates/template-layanan.php' ) ) {
		$current = home_url( '/#layanan' );
	} elseif ( is_page_template( 'page-templates/template-studi-kasus.php' ) ) {
		$current = home_url( '/study-case/' );
	} elseif ( is_singular() || is_home() ) {
		$current = is_home() ? get_permalink( (int) get_option( 'page_for_posts' ) ) : get_permalink();
	}

	echo '<ul class="menu">';
	foreach ( mea_orbit_fallback_menu_items( $location ) as $item ) {
		printf( '<li><a href="%1$s"%3$s>%2$s</a></li>', esc_url( $item[1] ), esc_html( $item[0] ), $current === $item[1] ? ' aria-current="page"' : '' );
	}
	echo '</ul>';
}

/**
 * Tandai link menu halaman aktif dengan aria-current.
 *
 * @param array<string, string> $atts Atribut link.
 * @param WP_Post               $item Item menu.
 * @return array<string, string>
 */
function mea_orbit_menu_aria_current( $atts, $item ) {
	if ( ! empty( $item->current ) ) {
		$atts['aria-current'] = 'page';
	}
	return $atts;
}
add_filter( 'nav_menu_link_attributes', 'mea_orbit_menu_aria_current', 10, 2 );

/**
 * Cetak breadcrumb. Item terakhir adalah halaman saat ini (tanpa link).
 *
 * @param array<int, array{0:string, 1:string}> $items Pasangan [label, URL]; URL kosong untuk item terakhir.
 * @return void
 */
function mea_orbit_breadcrumb( $items ) {
	if ( ! $items ) {
		return;
	}

	echo '<nav class="crumbs" aria-label="' . esc_attr__( 'Breadcrumb', 'mea-orbit' ) . '"><ol>';
	$last = count( $items ) - 1;
	foreach ( $items as $i => $item ) {
		if ( $i === $last || '' === $item[1] ) {
			printf( '<li aria-current="page">%s</li>', esc_html( $item[0] ) );
		} else {
			printf( '<li><a href="%1$s">%2$s</a></li>', esc_url( $item[1] ), esc_html( $item[0] ) );
		}
	}
	echo '</ol></nav>';
}

/**
 * Item breadcrumb untuk template umum (artikel, arsip, pencarian).
 *
 * @return array<int, array{0:string, 1:string}>
 */
function mea_orbit_breadcrumb_items() {
	$items   = array( array( __( 'Beranda', 'mea-orbit' ), home_url( '/' ) ) );
	$blog_id = (int) get_option( 'page_for_posts' );
	$blog    = array( $blog_id ? get_the_title( $blog_id ) : __( 'Blog', 'mea-orbit' ), $blog_id ? get_permalink( $blog_id ) : home_url( '/blog/' ) );

	if ( is_singular( 'post' ) ) {
		$items[]    = $blog;
		$categories = get_the_category();
		if ( $categories ) {
			$items[] = array( $categories[0]->name, get_category_link( $categories[0] ) );
		}
		$items[] = array( get_the_title(), '' );
	} elseif ( is_home() ) {
		$items[] = array( $blog[0], '' );
	} elseif ( is_category() || is_tag() || is_tax() ) {
		$items[] = $blog;
		$items[] = array( single_term_title( '', false ), '' );
	} elseif ( is_search() ) {
		$items[] = array( __( 'Pencarian', 'mea-orbit' ), '' );
	} elseif ( is_archive() ) {
		$items[] = $blog;
		$items[] = array( wp_strip_all_tags( get_the_archive_title() ), '' );
	}

	return $items;
}

/**
 * Judul halaman ditampilkan kecuali dimatikan lewat pengaturan "Hide Title"
 * Elementor, sama dengan perilaku Hello Elementor (paritas H1).
 *
 * @param int $post_id ID posting. 0 = posting saat ini.
 * @return bool
 */
function mea_orbit_show_title( $post_id = 0 ) {
	$post_id  = $post_id ? $post_id : get_the_ID();
	$settings = get_post_meta( $post_id, '_elementor_page_settings', true );

	return ! ( is_array( $settings ) && isset( $settings['hide_title'] ) && 'yes' === $settings['hide_title'] );
}

/**
 * Tanggal terbit artikel dalam format Indonesia.
 *
 * @return void
 */
function mea_orbit_posted_on() {
	printf(
		'<time datetime="%1$s">%2$s</time>',
		esc_attr( get_the_date( DATE_W3C ) ),
		esc_html( mea_orbit_indonesian_months( date_i18n( 'j F Y', get_post_timestamp() ), 'j F Y' ) )
	);
}

/**
 * Nama bulan Indonesia, karena bahasa situs tidak diubah. Sengaja tidak
 * dipasang sebagai filter global supaya tanggal di konten lama tidak berubah.
 *
 * @param string $date   Tanggal terformat.
 * @param string $format Format.
 * @return string
 */
function mea_orbit_indonesian_months( $date, $format ) {
	if ( false === strpos( $format, 'F' ) ) {
		return $date;
	}

	$en = array( 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December' );
	$id = array( 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember' );

	return str_replace( $en, $id, $date );
}

/**
 * Form password dengan bahasa dan gaya mea-orbit. Mekanisme proteksi
 * bawaan WordPress (cookie dan action wp-login.php?action=postpass) tidak diubah.
 *
 * @return string
 */
function mea_orbit_password_form() {
	$post  = get_post();
	$label = 'pwbox-' . ( $post ? $post->ID : wp_rand() );

	// Satu baris tanpa baris baru: form ini melewati filter the_content, dan
	// wpautop akan menyisipkan <br> di antara elemen bila ada baris baru.
	return sprintf(
		'<form action="%1$s" class="post-password-form" method="post"><p class="pw-intro">%2$s</p><div class="pw-row"><label for="%3$s">%4$s</label><input name="post_password" id="%3$s" type="password" autocomplete="current-password" spellcheck="false" required><button class="btn btn-primary" type="submit">%5$s</button></div></form>',
		esc_url( site_url( 'wp-login.php?action=postpass', 'login_post' ) ),
		esc_html__( 'Halaman ini dilindungi kata sandi. Masukkan kata sandi yang Anda terima dari tim MEA Agency.', 'mea-orbit' ),
		esc_attr( $label ),
		esc_html__( 'Kata sandi', 'mea-orbit' ),
		esc_html__( 'Buka halaman', 'mea-orbit' )
	);
}
add_filter( 'the_password_form', 'mea_orbit_password_form' );
