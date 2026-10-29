<?php
/**
 * Robots dan sitemap untuk konten berpassword.
 *
 * Laporan klien diproteksi password — jangan diindeks. Berlaku untuk semua post
 * dan page yang `post_password`-nya terisi, apa pun isian Rank Math per halaman.
 * Halaman tanpa password tidak disentuh.
 *
 * @package MEA_Orbit
 */

defined( 'ABSPATH' ) || exit;

/**
 * Apakah post punya password.
 *
 * @param int|WP_Post|null $post Post (null = post yang sedang dibuka).
 * @return bool
 */
function mea_orbit_has_password( $post = null ) {
	$post = get_post( $post );
	return $post instanceof WP_Post && '' !== (string) $post->post_password;
}

/**
 * Laporan klien diproteksi password — jangan diindeks: noindex, nofollow.
 *
 * @param array<string, string> $robots Direktif robots dari Rank Math.
 * @return array<string, string>
 */
function mea_orbit_password_robots( $robots ) {
	if ( is_singular() && mea_orbit_has_password( get_queried_object_id() ) ) {
		$robots['index']  = 'noindex';
		$robots['follow'] = 'nofollow';
	}
	return $robots;
}
add_filter( 'rank_math/frontend/robots', 'mea_orbit_password_robots', 99 );

/**
 * Laporan klien diproteksi password — jangan diindeks: keluarkan dari sitemap.
 *
 * Rank Math versi ini tidak punya filter `rank_math/sitemap/exclude_post`;
 * `rank_math/sitemap/entry` dipakai untuk sitemap XML dan HTML (entri kosong
 * dilewati). Kueri sitemap Rank Math sendiri juga menyaring post_password,
 * filter ini menjaga bila perilaku itu berubah.
 *
 * @param array<string, mixed>|false $url    Entri sitemap.
 * @param string                     $type   Jenis entri (post, term, user).
 * @param object                     $object Objek entri.
 * @return array<string, mixed>|false
 */
function mea_orbit_password_sitemap_entry( $url, $type, $object ) {
	if ( 'post' === $type && $object instanceof WP_Post && mea_orbit_has_password( $object ) ) {
		return false;
	}
	return $url;
}
add_filter( 'rank_math/sitemap/entry', 'mea_orbit_password_sitemap_entry', 99, 3 );
