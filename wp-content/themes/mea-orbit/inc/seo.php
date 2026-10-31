<?php
/**
 * SEO lapis Orbit: JSON-LD dan og:image cadangan.
 *
 * Rank Math tetap memegang title, meta description, Open Graph, sitemap, robots.
 * Tema hanya menambah node JSON-LD di halaman Orbit, lewat filter rank_math/json_ld,
 * supaya tidak ada dua Organization atau dua BreadcrumbList. Bila Rank Math atau
 * modul schema-nya nonaktif, tema mencetak graph yang sama sendiri.
 *
 * @package MEA_Orbit
 */

defined( 'ABSPATH' ) || exit;

/**
 * Apakah Rank Math mencetak JSON-LD (plugin aktif dan modul schema aktif).
 *
 * @return bool
 */
function mea_orbit_rank_math_schema_active() {
	if ( ! class_exists( '\RankMath\Helper' ) ) {
		return false;
	}
	return ! method_exists( '\RankMath\Helper', 'is_module_active' ) || \RankMath\Helper::is_module_active( 'rich-snippet' );
}

/**
 * Data Organization MEA Agency (dari prototipe).
 *
 * @return array<string, mixed>
 */
function mea_orbit_org_props() {
	return array(
		'name'         => 'MEA Agency',
		'legalName'    => 'PT MEA Digital Marketing',
		'url'          => home_url( '/' ),
		'telephone'    => '+6282117932648',
		'address'      => array(
			'@type'           => 'PostalAddress',
			'streetAddress'   => 'Jl. Rajawali Barat No.77A',
			'addressLocality' => 'Bandung',
			'postalCode'      => '40184',
			'addressCountry'  => 'ID',
		),
		'founder'      => array(
			'@type' => 'Person',
			'name'  => 'Yohan Agustian',
		),
		'foundingDate' => '2020',
	);
}

/**
 * Node JSON-LD khusus halaman Orbit saat ini.
 *
 * @param string $org_id @id node Organization yang dirujuk.
 * @return array<int, array<string, mixed>>
 */
function mea_orbit_schema_nodes( $org_id ) {
	$id    = get_queried_object_id();
	$url   = get_permalink( $id );
	$nodes = array();
	$org   = array( '@id' => $org_id );

	if ( is_page_template( 'page-templates/template-layanan.php' ) ) {
		$name    = (string) mea_orbit_meta( 'judul_pendek' );
		$service = array(
			'@type'      => 'Service',
			'@id'        => $url . '#service',
			'name'       => '' !== $name ? $name : get_the_title( $id ),
			'url'        => $url,
			'provider'   => $org,
			'areaServed' => 'ID',
		);
		$type    = (string) mea_orbit_meta( 'service_type' );
		if ( '' !== $type ) {
			$service['serviceType'] = $type;
		}
		$nodes[] = $service;

		$faq = array();
		foreach ( (array) mea_orbit_meta( 'faq' ) as $row ) {
			if ( '' === $row['tanya'] || '' === $row['jawab'] ) {
				continue;
			}
			$faq[] = array(
				'@type'          => 'Question',
				'name'           => $row['tanya'],
				'acceptedAnswer' => array(
					'@type' => 'Answer',
					'text'  => $row['jawab'],
				),
			);
		}
		if ( $faq ) {
			$nodes[] = array(
				'@type'      => 'FAQPage',
				'@id'        => $url . '#faq',
				'mainEntity' => $faq,
			);
		}
	}

	if ( is_page_template( 'page-templates/template-studi-kasus.php' ) && ! mea_orbit_meta( 'hub_kasus' ) ) {
		$headline = (string) mea_orbit_meta( 'h1' );
		$article  = array(
			'@type'            => 'Article',
			'@id'              => $url . '#article',
			'headline'         => '' !== $headline ? $headline : get_the_title( $id ),
			'mainEntityOfPage' => $url,
			'datePublished'    => get_the_date( DATE_W3C, $id ),
			'dateModified'     => get_the_modified_date( DATE_W3C, $id ),
			'author'           => $org,
			'publisher'        => $org,
			'inLanguage'       => 'id-ID',
		);
		$photos   = (array) mea_orbit_meta( 'foto_proses' );
		$image    = $photos ? wp_get_attachment_image_url( (int) $photos[0]['media_id'], 'large' ) : '';
		if ( $image ) {
			$article['image'] = $image;
		}
		$nodes[] = $article;
	}

	$crumbs = mea_orbit_orbit_breadcrumb();
	if ( $crumbs ) {
		$items = array();
		foreach ( $crumbs as $i => $crumb ) {
			$items[] = array(
				'@type'    => 'ListItem',
				'position' => $i + 1,
				'name'     => $crumb[0],
				'item'     => $crumb[1],
			);
		}
		$nodes[] = array(
			'@type'           => 'BreadcrumbList',
			'@id'             => $url . '#breadcrumb',
			'itemListElement' => $items,
		);
	}

	return $nodes;
}

/**
 * Tipe node Rank Math yang dibuang karena tema menambah node sejenis
 * (supaya tidak dobel). Tipe yang tidak ditambah tema dibiarkan.
 *
 * @param array<int, array<string, mixed>> $nodes Node tambahan tema.
 * @return string[]
 */
function mea_orbit_replaced_types( $nodes ) {
	$types = array();
	foreach ( $nodes as $node ) {
		$types[] = $node['@type'];
		if ( 'Article' === $node['@type'] ) {
			$types[] = 'BlogPosting';
			$types[] = 'NewsArticle';
		}
	}
	return $types;
}

/**
 * Sisipkan node Orbit ke graph Rank Math.
 *
 * @param array<string, mixed> $data Node graph Rank Math, dikunci nama entitas.
 * @return array<string, mixed>
 */
function mea_orbit_rank_math_json_ld( $data ) {
	if ( ! mea_orbit_is_view() || ! is_array( $data ) ) {
		return $data;
	}

	$org_id = home_url( '/#organization' );
	foreach ( $data as $node ) {
		$types = isset( $node['@type'] ) ? (array) $node['@type'] : array();
		if ( array_intersect( $types, array( 'Organization', 'Corporation', 'ProfessionalService', 'LocalBusiness' ) ) && isset( $node['@id'] ) ) {
			$org_id = $node['@id'];
			break;
		}
	}

	$nodes    = mea_orbit_schema_nodes( $org_id );
	$replaced = mea_orbit_replaced_types( $nodes );
	$has_org  = false;

	foreach ( $data as $key => $node ) {
		$types = isset( $node['@type'] ) ? (array) $node['@type'] : array();
		if ( array_intersect( $types, array( 'Organization', 'Corporation', 'ProfessionalService', 'LocalBusiness' ) ) ) {
			$has_org = true;
			if ( is_front_page() ) {
				$data[ $key ] = array_merge( $node, mea_orbit_org_props() );
			}
			continue;
		}
		if ( array_intersect( $types, $replaced ) ) {
			unset( $data[ $key ] );
		}
	}

	if ( ! $has_org ) {
		$data['mea_organization'] = array_merge(
			array(
				'@type' => 'Organization',
				'@id'   => $org_id,
			),
			mea_orbit_org_props()
		);
	}

	foreach ( $nodes as $i => $node ) {
		$data[ 'mea_orbit_' . $i ] = $node;
	}

	return $data;
}
add_filter( 'rank_math/json_ld', 'mea_orbit_rank_math_json_ld', 99 );

/**
 * Cadangan: cetak graph sendiri bila Rank Math tidak mencetak JSON-LD.
 *
 * @return void
 */
function mea_orbit_print_json_ld() {
	if ( ! mea_orbit_is_view() || mea_orbit_rank_math_schema_active() ) {
		return;
	}

	$org_id = home_url( '/#organization' );
	$graph  = array(
		array_merge(
			array(
				'@type' => 'Organization',
				'@id'   => $org_id,
			),
			mea_orbit_org_props()
		),
	);
	if ( is_front_page() ) {
		$graph[] = array(
			'@type'      => 'WebSite',
			'@id'        => home_url( '/#website' ),
			'url'        => home_url( '/' ),
			'name'       => 'MEA Agency',
			'publisher'  => array( '@id' => $org_id ),
			'inLanguage' => 'id-ID',
		);
	}
	$graph = array_merge( $graph, mea_orbit_schema_nodes( $org_id ) );

	echo '<script type="application/ld+json">' . wp_json_encode(
		array(
			'@context' => 'https://schema.org',
			'@graph'   => $graph,
		),
		JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
	) . "</script>\n";
}
add_action( 'wp_head', 'mea_orbit_print_json_ld', 20 );

/**
 * ID gambar og:image cadangan untuk halaman Orbit.
 *
 * @return int
 */
function mea_orbit_og_fallback_id() {
	if ( is_front_page() ) {
		return (int) mea_orbit_home( 'pimpinan_foto_id' );
	}
	if ( is_page_template( 'page-templates/template-layanan.php' ) ) {
		return (int) mea_orbit_meta( 'foto_hero_id' );
	}
	if ( is_page_template( 'page-templates/template-studi-kasus.php' ) ) {
		$photos = (array) mea_orbit_meta( 'foto_proses' );
		return $photos ? (int) $photos[0]['media_id'] : 0;
	}
	return 0;
}

/**
 * Gambar og:image cadangan: dipakai Rank Math hanya bila halaman belum punya gambar sosial.
 *
 * @param object $image Objek gambar Open Graph Rank Math.
 * @return void
 */
function mea_orbit_og_fallback_image( $image ) {
	if ( ! mea_orbit_is_view() || ! is_object( $image ) || ! method_exists( $image, 'add_image_by_id' ) ) {
		return;
	}
	if ( method_exists( $image, 'has_images' ) && $image->has_images() ) {
		return;
	}
	$id = mea_orbit_og_fallback_id();
	if ( $id ) {
		$image->add_image_by_id( $id );
	}
}
add_action( 'rank_math/opengraph/facebook/add_additional_images', 'mea_orbit_og_fallback_image' );
add_action( 'rank_math/opengraph/twitter/add_additional_images', 'mea_orbit_og_fallback_image' );
