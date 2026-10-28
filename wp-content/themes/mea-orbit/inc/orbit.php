<?php
/**
 * Helper lapis Orbit: gambar, motion, angka studi kasus, URL.
 *
 * @package MEA_Orbit
 */

defined( 'ABSPATH' ) || exit;

/**
 * Gambar dari Media Library dengan srcset, width/height, dan alt.
 *
 * Mengembalikan string kosong bila lampiran tidak ada (tidak pernah hotlink).
 *
 * @param int                   $id   ID lampiran.
 * @param string                $size Ukuran gambar.
 * @param array<string, string> $attr Atribut tambahan (alt kosong = alt di Media Library).
 * @return string
 */
function mea_orbit_img( $id, $size = 'large', $attr = array() ) {
	if ( ! $id || ! wp_attachment_is_image( $id ) ) {
		return '';
	}
	if ( isset( $attr['alt'] ) && '' === $attr['alt'] ) {
		unset( $attr['alt'] );
	}
	$attr = wp_parse_args( $attr, array( 'loading' => 'lazy' ) );
	if ( 'eager' === $attr['loading'] ) {
		unset( $attr['loading'] );
		$attr['fetchpriority'] = $attr['fetchpriority'] ?? 'high';
	}
	return wp_get_attachment_image( $id, $size, false, $attr );
}

/**
 * Apakah gambar lampiran punya kanal transparansi (PNG RGBA / abu-abu+alpha, WebP
 * dengan alpha). Dibaca dari header berkas, tanpa memuat seluruh gambar.
 *
 * @param int $id ID lampiran.
 * @return bool
 */
function mea_orbit_image_has_alpha( $id ) {
	$file = (string) get_attached_file( (int) $id );
	if ( '' === $file || ! is_readable( $file ) ) {
		return false;
	}
	$head = (string) file_get_contents( $file, false, null, 0, 32 ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- berkas lokal Media Library, 32 byte.
	if ( 0 === strpos( $head, "\x89PNG" ) ) {
		return in_array( ord( $head[25] ?? "\0" ), array( 4, 6 ), true );
	}
	if ( 0 === strpos( $head, 'RIFF' ) && 'WEBP' === substr( $head, 8, 4 ) ) {
		$chunk = substr( $head, 12, 4 );
		if ( 'VP8X' === $chunk ) {
			return (bool) ( ord( $head[20] ?? "\0" ) & 0x10 );
		}
		if ( 'VP8L' === $chunk ) {
			return (bool) ( ord( $head[24] ?? "\0" ) & 0x10 );
		}
	}
	return false;
}

/**
 * URL aset motion di tema.
 *
 * @param string $path Path relatif di assets/motion/.
 * @return string
 */
function mea_orbit_motion_url( $path ) {
	return MEA_ORBIT_URI . '/assets/motion/' . ltrim( $path, '/' );
}

/**
 * URL dari field: path relatif (/affiliate/) jadi URL situs, URL penuh dibiarkan.
 *
 * @param string $url URL atau path.
 * @return string
 */
function mea_orbit_url( $url ) {
	$url = trim( (string) $url );
	return 0 === strpos( $url, '/' ) && 0 !== strpos( $url, '//' ) ? home_url( $url ) : $url;
}

/**
 * Atribut hitung naik dibaca dari teks nilai, mis. "Rp 8,2 miliar" → prefix "Rp ",
 * angka 8.2, 1 desimal, suffix " miliar". Pemisah ribuan titik ("3.000") ikut
 * dikenali. Nilai yang tidak bisa dihitung (mis. "75 : 25") mengembalikan array
 * kosong dan tampil statis.
 *
 * @param string $text Nilai tampil.
 * @return array<string, string>
 */
function mea_orbit_count_attrs( $text ) {
	if ( ! preg_match( '/^(\D*?)(\d{1,3}(?:\.\d{3})+(?:,\d+)?|\d+(?:,\d+)?)(\D*)$/u', trim( $text ), $m ) ) {
		return array();
	}
	$parts = explode( ',', $m[2] );
	$attrs = array(
		'data-count'  => str_replace( array( '.', ',' ), array( '', '.' ), $m[2] ),
		'data-prefix' => $m[1],
		'data-suffix' => $m[3],
		'data-dec'    => (string) ( isset( $parts[1] ) ? strlen( $parts[1] ) : 0 ),
	);
	if ( false !== strpos( $parts[0], '.' ) ) {
		$attrs['data-group'] = '1';
	}
	return $attrs;
}

/**
 * Cetak atribut HTML dari array.
 *
 * @param array<string, string> $attrs Atribut.
 * @return string
 */
function mea_orbit_attrs( $attrs ) {
	$out = '';
	foreach ( $attrs as $name => $value ) {
		$out .= ' ' . esc_attr( $name ) . '="' . esc_attr( $value ) . '"';
	}
	return $out;
}

/**
 * Apakah halaman studi kasus valid dan izin publikasi angkanya aktif.
 *
 * @param int $case_id ID halaman studi kasus.
 * @return bool
 */
function mea_orbit_case_published( $case_id ) {
	return $case_id
		&& 'publish' === get_post_status( $case_id )
		&& 'page-templates/template-studi-kasus.php' === get_page_template_slug( $case_id )
		&& (bool) mea_orbit_meta( 'izin_publikasi', $case_id );
}

/**
 * Nama brand studi kasus (kosong = judul halaman).
 *
 * @param int $case_id ID halaman.
 * @return string
 */
function mea_orbit_case_brand( $case_id ) {
	$brand = (string) mea_orbit_meta( 'brand', $case_id );
	return '' !== $brand ? $brand : get_the_title( $case_id );
}

/**
 * Angka studi kasus untuk konteks tertentu.
 *
 * - `beranda`: baris bertanda tampil_beranda, memakai nilai/label versi homepage bila diisi.
 * - `studi`: semua baris apa adanya.
 * - Array label: baris yang labelnya cocok, dalam urutan label yang diminta (bukti di layanan).
 *
 * Mengembalikan array kosong bila izin publikasi nonaktif.
 *
 * @param int                  $case_id ID halaman studi kasus.
 * @param string|array<string> $context Konteks.
 * @return array<int, array{nilai:string, label:string}>
 */
function mea_orbit_case_numbers( $case_id, $context = 'studi' ) {
	if ( ! mea_orbit_case_published( $case_id ) ) {
		return array();
	}

	$rows = (array) mea_orbit_meta( 'angka', $case_id );
	$out  = array();

	if ( is_array( $context ) ) {
		foreach ( $context as $label ) {
			foreach ( $rows as $row ) {
				if ( 0 === strcasecmp( trim( $row['label'] ), trim( $label ) ) ) {
					$out[] = array(
						'nilai' => $row['nilai'],
						'label' => $row['label'],
					);
					break;
				}
			}
		}
		return $out;
	}

	foreach ( $rows as $row ) {
		if ( 'beranda' === $context ) {
			if ( empty( $row['tampil_beranda'] ) ) {
				continue;
			}
			$out[] = array(
				'nilai' => '' !== $row['nilai_beranda'] ? $row['nilai_beranda'] : $row['nilai'],
				'label' => '' !== $row['label_beranda'] ? $row['label_beranda'] : $row['label'],
			);
		} else {
			$out[] = array(
				'nilai' => $row['nilai'],
				'label' => $row['label'],
			);
		}
	}
	return $out;
}

/**
 * Sumber form untuk halaman ini.
 *
 * @param string $prefix Awalan bawaan (`layanan-`, `studi-kasus-`).
 * @return string
 */
function mea_orbit_form_source( $prefix ) {
	$source = (string) mea_orbit_meta( 'sumber_form' );
	return '' !== $source ? $source : $prefix . get_post_field( 'post_name', get_queried_object_id() );
}

/**
 * Data breadcrumb halaman Orbit (dipakai tampilan dan schema BreadcrumbList).
 *
 * @return array<int, array{0:string, 1:string}> Pasangan [label, URL]; item terakhir URL halaman ini.
 */
function mea_orbit_orbit_breadcrumb() {
	$id   = get_queried_object_id();
	$home = array( __( 'Beranda', 'mea-orbit' ), home_url( '/' ) );

	if ( is_page_template( 'page-templates/template-layanan.php' ) ) {
		$label = (string) mea_orbit_meta( 'judul_pendek' );
		return array(
			$home,
			array( __( 'Layanan', 'mea-orbit' ), home_url( '/services/' ) ),
			array( '' !== $label ? $label : get_the_title( $id ), get_permalink( $id ) ),
		);
	}

	if ( is_page_template( 'page-templates/template-studi-kasus.php' ) ) {
		return array(
			$home,
			array( __( 'Studi kasus', 'mea-orbit' ), home_url( '/study-case/' ) ),
			array( mea_orbit_case_brand( $id ), get_permalink( $id ) ),
		);
	}

	return array();
}
