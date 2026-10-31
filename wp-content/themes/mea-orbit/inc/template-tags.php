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
 * Empat pilar layanan: satu sumber untuk navigasi header dan kolom Layanan footer.
 *
 * @return array<int, array{0:string, 1:string, 2:array<int, array{0:string, 1:string}>}> [label, path, anak].
 */
function mea_orbit_pilar() {
	return array(
		array(
			__( 'E-commerce Service', 'mea-orbit' ),
			'/ecommerce-service/',
			array(
				array( __( 'Kelola Toko Shopee', 'mea-orbit' ), '/jasa-management-marketplace/' ),
				array( __( 'Optimasi Shopee', 'mea-orbit' ), '/jasa-optimasi-shopee/' ),
				array( __( 'Upgrade Shopee Mall', 'mea-orbit' ), '/jasa-upgrade-shopee-mall/' ),
				array( __( 'Kelola TikTok Shop', 'mea-orbit' ), '/jasa-kelola-tiktok-shop/' ),
				array( __( 'Live Streaming', 'mea-orbit' ), '/jasa-live-streaming/' ),
				array( __( 'Affiliate', 'mea-orbit' ), '/affiliate/' ),
			),
		),
		array(
			__( 'Performance Ads', 'mea-orbit' ),
			'/jasa-performance-ads/',
			array(
				array( __( 'Iklan Shopee', 'mea-orbit' ), '/jasa-iklan-shopee/' ),
				array( __( 'TikTok Ads', 'mea-orbit' ), '/jasa-tiktok-ads/' ),
				array( __( 'Meta Ads', 'mea-orbit' ), '/jasa-meta-ads/' ),
				array( __( 'Iklan Marketplace', 'mea-orbit' ), '/jasa-iklan-marketplace/' ),
			),
		),
		array(
			__( 'Social Media Management', 'mea-orbit' ),
			'/social-media-management/',
			array(
				array( __( 'Jasa Konten', 'mea-orbit' ), '/jasa-konten/' ),
				array( __( 'Konten TikTok', 'mea-orbit' ), '/jasa-konten-tiktok/' ),
			),
		),
		array( __( 'SEO', 'mea-orbit' ), '/seo/', array() ),
	);
}

/**
 * URL halaman yang sedang dibuka (untuk aria-current di menu tema).
 *
 * @return string
 */
function mea_orbit_current_url() {
	if ( is_front_page() ) {
		return home_url( '/' );
	}
	if ( is_home() ) {
		return (string) get_permalink( (int) get_option( 'page_for_posts' ) );
	}
	// Halaman studi kasus menandai link "Studi kasus" di header.
	if ( is_page_template( 'page-templates/template-studi-kasus.php' ) ) {
		return home_url( '/study-case/' );
	}
	return is_singular() ? (string) get_permalink() : '';
}

/**
 * Cetak empat pilar sebagai daftar bertingkat (ul.menu > li > a + ul.sub-menu),
 * markup yang sama dengan wp_nav_menu kedalaman 2, ditutup link Studi kasus.
 *
 * Dropdown header tanpa JavaScript: terbuka saat hover dan saat fokus keyboard
 * ada di dalam pilar (:focus-within), jadi Tab menelusuri pilar lalu anaknya.
 * Di menu HP anak selalu tampil terindentasi.
 *
 * @return void
 */
function mea_orbit_pilar_menu() {
	$current = mea_orbit_current_url();
	$link    = function ( $label, $path ) use ( $current ) {
		$url = home_url( $path );
		return sprintf( '<a href="%1$s"%3$s>%2$s</a>', esc_url( $url ), esc_html( $label ), $current === $url ? ' aria-current="page"' : '' );
	};

	echo '<ul class="menu">';
	foreach ( mea_orbit_pilar() as $pilar ) {
		if ( ! $pilar[2] ) {
			echo '<li class="menu-item">' . $link( $pilar[0], $pilar[1] ) . '</li>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- di-escape di $link.
			continue;
		}
		echo '<li class="menu-item menu-item-has-children">' . $link( $pilar[0], $pilar[1] ) . '<ul class="sub-menu">'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- di-escape di $link.
		foreach ( $pilar[2] as $anak ) {
			echo '<li class="menu-item">' . $link( $anak[0], $anak[1] ) . '</li>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- di-escape di $link.
		}
		echo '</ul></li>';
	}
	// Bukti hasil di samping pilar: link biasa tanpa dropdown.
	echo '<li class="menu-item menu-item-studi">' . $link( __( 'Studi kasus', 'mea-orbit' ), '/study-case/' ) . '</li>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- di-escape di $link.
	echo '</ul>';
}

/**
 * Isi menu cadangan kolom Perusahaan di footer bila lokasinya belum diatur.
 *
 * @return array<int, array{0:string, 1:string}> Pasangan [label, URL].
 */
function mea_orbit_fallback_menu_items() {
	$blog_id = (int) get_option( 'page_for_posts' );
	return array(
		array( __( 'Studi kasus', 'mea-orbit' ), home_url( '/study-case/' ) ),
		array( __( 'Blog', 'mea-orbit' ), $blog_id ? get_permalink( $blog_id ) : home_url( '/blog/' ) ),
	);
}

/**
 * Cetak menu lokasi tema.
 *
 * - Header (`mea-orbit-utama`): menu WordPress kedalaman 2 bila diatur di
 *   wp-admin, kalau tidak empat pilar layanan dari mea_orbit_pilar().
 * - Footer (`mea-orbit-footer`, kolom Perusahaan): menu WordPress kedalaman 1,
 *   kalau tidak daftar cadangan.
 *
 * @param string $location Lokasi menu.
 * @return void
 */
function mea_orbit_menu( $location ) {
	$header = 'mea-orbit-utama' === $location;
	if ( has_nav_menu( $location ) ) {
		wp_nav_menu(
			array(
				'theme_location' => $location,
				'container'      => false,
				'menu_class'     => 'menu',
				'depth'          => $header ? 2 : 1,
				'fallback_cb'    => false,
			)
		);
		return;
	}

	if ( $header ) {
		mea_orbit_pilar_menu();
		return;
	}

	$current = mea_orbit_current_url();
	echo '<ul class="menu">';
	foreach ( mea_orbit_fallback_menu_items() as $item ) {
		printf( '<li><a href="%1$s"%3$s>%2$s</a></li>', esc_url( $item[1] ), esc_html( $item[0] ), $current === $item[1] ? ' aria-current="page"' : '' );
	}
	echo '</ul>';
}

/**
 * Kolom Layanan footer: empat pilar dengan anaknya, struktur sama dengan header.
 *
 * @return void
 */
function mea_orbit_pilar_footer() {
	echo '<div class="foot-pilar">';
	foreach ( mea_orbit_pilar() as $pilar ) {
		echo '<div><a class="foot-pilar-h" href="' . esc_url( home_url( $pilar[1] ) ) . '">' . esc_html( $pilar[0] ) . '</a>';
		if ( $pilar[2] ) {
			echo '<ul>';
			foreach ( $pilar[2] as $anak ) {
				echo '<li><a href="' . esc_url( home_url( $anak[1] ) ) . '">' . esc_html( $anak[0] ) . '</a></li>';
			}
			echo '</ul>';
		}
		echo '</div>';
	}
	echo '</div>';
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
