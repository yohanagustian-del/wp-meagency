<?php
/**
 * Uji paritas SEO: bandingkan sinyal SEO setiap URL "sebelum" dan "sesudah".
 *
 * CLI PHP tanpa dependency (curl + DOM bawaan PHP). Hanya membaca (GET).
 *
 *   php parity.php snapshot --base=https://www.meagency.co.id --out=snap/produksi
 *   php parity.php snapshot --base=https://staging.meagency.co.id --out=snap/staging --urls=snap/produksi/urls.txt
 *   php parity.php compare  --before=snap/produksi --after=snap/staging --out=laporan
 *
 * Lihat README.md untuk semua opsi.
 *
 * @package MEA_Orbit
 */

if ( 'cli' !== PHP_SAPI ) {
	exit( 1 );
}

const MEA_PARITY_VERSION = '1.0.0';

// Bagian halaman yang diabaikan saat membandingkan isi: header, footer, navigasi, dan
// elemen tanpa teks. Header/footer memang berganti di langkah 1 (PLAN bagian 14).
const MEA_PARITY_IGNORE = array(
	'//script',
	'//style',
	'//noscript',
	'//template',
	'//svg',
	'//header',
	'//footer',
	'//nav',
	'//*[@id="wpadminbar"]',
	'//*[@data-elementor-type="header"]',
	'//*[@data-elementor-type="footer"]',
	'//*[@data-elementor-type="popup"]',
	'//*[contains(concat(" ", normalize-space(@class), " "), " elementor-location-header ")]',
	'//*[contains(concat(" ", normalize-space(@class), " "), " elementor-location-footer ")]',
	'//*[contains(concat(" ", normalize-space(@class), " "), " skip-link ")]',
	'//*[contains(concat(" ", normalize-space(@class), " "), " screen-reader-text ")]',
);

/**
 * Baca opsi --nama=nilai dari argv.
 *
 * @param array<int, string> $argv Argumen.
 * @return array<string, string>
 */
function mea_parity_opts( array $argv ): array {
	$opts = array();
	foreach ( array_slice( $argv, 2 ) as $arg ) {
		if ( preg_match( '/^--([a-z-]+)(?:=(.*))?$/s', $arg, $m ) ) {
			$opts[ $m[1] ] = $m[2] ?? '1';
		}
	}
	return $opts;
}

/**
 * Hentikan dengan pesan.
 *
 * @param string $msg Pesan.
 * @return never
 */
function mea_parity_fail( string $msg ) {
	fwrite( STDERR, $msg . "\n" );
	exit( 1 );
}

/**
 * GET satu URL tanpa mengikuti redirect.
 *
 * @param string               $url  URL.
 * @param array<string,string> $opts Opsi CLI (auth, header, timeout, insecure).
 * @return array{status:int, location:string, body:string, error:string}
 */
function mea_parity_get( string $url, array $opts ): array {
	$ch      = curl_init( $url );
	$headers = array( 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8', 'Accept-Language: id-ID,id;q=0.9' );
	if ( ! empty( $opts['header'] ) ) {
		$headers[] = $opts['header'];
	}
	curl_setopt_array(
		$ch,
		array(
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_FOLLOWLOCATION => false,
			CURLOPT_TIMEOUT        => (int) ( $opts['timeout'] ?? 30 ),
			CURLOPT_USERAGENT      => 'MEA-SEO-Parity/' . MEA_PARITY_VERSION . ' (hanya baca)',
			CURLOPT_HTTPHEADER     => $headers,
			CURLOPT_ENCODING       => '',
			CURLOPT_HEADER         => true,
		)
	);
	if ( ! empty( $opts['auth'] ) ) {
		curl_setopt( $ch, CURLOPT_USERPWD, $opts['auth'] );
	}
	if ( ! empty( $opts['insecure'] ) ) {
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
	}
	$raw    = curl_exec( $ch );
	$error  = false === $raw ? curl_error( $ch ) : '';
	$status = (int) curl_getinfo( $ch, CURLINFO_RESPONSE_CODE );
	$hsize  = (int) curl_getinfo( $ch, CURLINFO_HEADER_SIZE );
	curl_close( $ch );

	$head     = false === $raw ? '' : substr( $raw, 0, $hsize );
	$location = preg_match( '/^location:\s*(\S+)/mi', $head, $m ) ? $m[1] : '';
	return array(
		'status'   => $status,
		'location' => $location,
		'body'     => false === $raw ? '' : substr( $raw, $hsize ),
		'error'    => $error,
	);
}

/**
 * Path dari URL bila host-nya milik base (atau URL relatif). URL luar dibiarkan.
 *
 * @param string $url  URL.
 * @param string $base URL dasar situs.
 * @return string
 */
function mea_parity_path( string $url, string $base ): string {
	$url = trim( html_entity_decode( $url, ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
	if ( '' === $url ) {
		return '';
	}
	$host = strtolower( preg_replace( '/^www\./', '', (string) parse_url( $base, PHP_URL_HOST ) ) );
	$p    = parse_url( $url );
	if ( false === $p ) {
		return $url;
	}
	if ( isset( $p['host'] ) && strtolower( preg_replace( '/^www\./', '', $p['host'] ) ) !== $host ) {
		return $url;
	}
	if ( ! isset( $p['host'] ) && isset( $p['scheme'] ) ) {
		return $url; // mailto:, tel:, javascript:.
	}
	$path = $p['path'] ?? '/';
	if ( '' === $path || '/' !== $path[0] ) {
		$path = '/' . $path;
	}
	return $path . ( isset( $p['query'] ) ? '?' . $p['query'] : '' ) . ( isset( $p['fragment'] ) ? '#' . $p['fragment'] : '' );
}

/**
 * Daftar URL dari sitemap (index atau urlset, rekursif).
 *
 * @param string               $url  URL sitemap.
 * @param array<string,string> $opts Opsi.
 * @param int                  $depth Kedalaman.
 * @return string[]
 */
function mea_parity_sitemap( string $url, array $opts, int $depth = 0 ): array {
	if ( $depth > 3 ) {
		return array();
	}
	$res = mea_parity_get( $url, $opts );
	if ( 200 !== $res['status'] ) {
		fwrite( STDERR, "Sitemap {$url}: HTTP {$res['status']} {$res['error']}\n" );
		return array();
	}
	$prev = libxml_use_internal_errors( true );
	$xml  = simplexml_load_string( $res['body'] );
	libxml_use_internal_errors( $prev );
	if ( false === $xml ) {
		return array();
	}
	$out = array();
	if ( 'sitemapindex' === $xml->getName() ) {
		foreach ( $xml->sitemap as $s ) {
			$out = array_merge( $out, mea_parity_sitemap( trim( (string) $s->loc ), $opts, $depth + 1 ) );
		}
	} else {
		foreach ( $xml->url as $u ) {
			$out[] = trim( (string) $u->loc );
		}
	}
	return $out;
}

/**
 * Nama berkas aman untuk satu path.
 *
 * @param string $path Path.
 * @return string
 */
function mea_parity_file( string $path ): string {
	$slug = trim( preg_replace( '/[^a-z0-9]+/i', '-', $path ), '-' );
	return ( '' === $slug ? 'beranda' : substr( $slug, 0, 80 ) ) . '-' . substr( md5( $path ), 0, 8 ) . '.html';
}

/**
 * Ambil snapshot HTML seluruh URL.
 *
 * @param array<string,string> $opts Opsi.
 * @return void
 */
function mea_parity_snapshot( array $opts ): void {
	$base = rtrim( $opts['base'] ?? '', '/' );
	$out  = $opts['out'] ?? '';
	if ( '' === $base || '' === $out ) {
		mea_parity_fail( 'Wajib: --base=URL --out=folder' );
	}
	if ( ! is_dir( $out . '/html' ) && ! mkdir( $out . '/html', 0777, true ) ) {
		mea_parity_fail( "Tidak bisa membuat {$out}/html" );
	}

	if ( ! empty( $opts['urls'] ) ) {
		$list = file( $opts['urls'], FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
		if ( false === $list ) {
			mea_parity_fail( "Tidak bisa membaca {$opts['urls']}" );
		}
	} else {
		$list = mea_parity_sitemap( $base . ( $opts['sitemap'] ?? '/sitemap_index.xml' ), $opts );
		if ( ! $list ) {
			mea_parity_fail( 'Sitemap kosong atau tidak terbaca. Pakai --sitemap=/path atau --urls=berkas.' );
		}
	}
	$paths = array();
	foreach ( $list as $line ) {
		$line = trim( $line );
		if ( '' !== $line && '#' !== $line[0] ) {
			$paths[] = mea_parity_path( $line, $base );
		}
	}
	$paths = array_values( array_unique( $paths ) );
	file_put_contents( $out . '/urls.txt', implode( "\n", $paths ) . "\n" );

	$index = array(
		'base'  => $base,
		'waktu' => gmdate( 'c' ),
		'urls'  => array(),
	);
	$delay = (int) ( $opts['delay'] ?? 500 );
	foreach ( $paths as $i => $path ) {
		$res  = mea_parity_get( $base . $path, $opts );
		$file = mea_parity_file( $path );
		file_put_contents( $out . '/html/' . $file, $res['body'] );
		$index['urls'][ $path ] = array(
			'status'   => $res['status'],
			'location' => '' !== $res['location'] ? mea_parity_path( $res['location'], $base ) : '',
			'file'     => $file,
			'error'    => $res['error'],
		);
		fwrite( STDERR, sprintf( "[%d/%d] %d %s\n", $i + 1, count( $paths ), $res['status'], $path ) );
		if ( $delay > 0 ) {
			usleep( $delay * 1000 );
		}
	}
	file_put_contents( $out . '/index.json', json_encode( $index, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) );
	fwrite( STDERR, count( $paths ) . " URL disimpan di {$out}\n" );
}

/**
 * Rapikan spasi.
 *
 * @param string $text Teks.
 * @return string
 */
function mea_parity_clean( string $text ): string {
	return trim( preg_replace( '/\s+/u', ' ', $text ) );
}

/**
 * Tipe JSON-LD (rekursif, termasuk @graph).
 *
 * @param mixed    $node Node.
 * @param string[] $out  Hasil.
 * @return void
 */
function mea_parity_ld_types( $node, array &$out ): void {
	if ( ! is_array( $node ) ) {
		return;
	}
	if ( isset( $node['@type'] ) ) {
		foreach ( (array) $node['@type'] as $type ) {
			$out[] = (string) $type;
		}
	}
	foreach ( $node as $value ) {
		mea_parity_ld_types( $value, $out );
	}
}

/**
 * Ekstrak sinyal SEO dari HTML.
 *
 * @param string   $html   HTML.
 * @param string   $base   URL dasar (untuk menormalkan link).
 * @param string[] $ignore XPath tambahan yang diabaikan.
 * @return array<string, mixed>
 */
function mea_parity_extract( string $html, string $base, array $ignore, array $area = array() ): array {
	$doc  = new DOMDocument();
	$prev = libxml_use_internal_errors( true );
	$doc->loadHTML( '<?xml encoding="UTF-8">' . $html, LIBXML_NOWARNING | LIBXML_NOERROR );
	libxml_use_internal_errors( $prev );
	$xp = new DOMXPath( $doc );

	$attr = static function ( string $query, string $name ) use ( $xp ): string {
		$node = $xp->query( $query )->item( 0 );
		return $node instanceof DOMElement ? mea_parity_clean( $node->getAttribute( $name ) ) : '';
	};
	$texts = static function ( string $query ) use ( $xp ): array {
		$out = array();
		foreach ( $xp->query( $query ) as $node ) {
			$out[] = mea_parity_clean( $node->textContent );
		}
		return $out;
	};

	$types = array();
	foreach ( $xp->query( '//script[@type="application/ld+json"]' ) as $node ) {
		$data = json_decode( $node->textContent, true );
		if ( null === $data ) {
			$types[] = '(JSON-LD tidak valid)';
			continue;
		}
		mea_parity_ld_types( $data, $types );
	}
	$types = array_values( array_unique( $types ) );
	sort( $types );

	$robots = $attr( '//meta[@name="robots"]', 'content' );
	$signal = array(
		'title'       => mea_parity_clean( (string) $xp->query( '//head/title' )->item( 0 )?->textContent ),
		'description' => $attr( '//meta[@name="description"]', 'content' ),
		'robots'      => $robots,
		'canonical'   => mea_parity_path( $attr( '//link[@rel="canonical"]', 'href' ), $base ),
		'og_locale'   => $attr( '//meta[@property="og:locale"]', 'content' ),
		'og_image'    => mea_parity_path( $attr( '//meta[@property="og:image"]', 'content' ), $base ),
		'lang'        => $attr( '//html', 'lang' ),
		'h1'          => $texts( '//h1' ),
		'jsonld'      => $types,
	);

	// Area isi: buang header, footer, navigasi, dan pola tambahan.
	foreach ( array_merge( MEA_PARITY_IGNORE, $ignore ) as $query ) {
		$found = $xp->query( $query );
		$nodes = array();
		foreach ( $found ? $found : array() as $node ) {
			$nodes[] = $node;
		}
		foreach ( $nodes as $node ) {
			if ( $node->parentNode ) {
				$node->parentNode->removeChild( $node );
			}
		}
	}
	// Area isi opsional (--area): elemen pertama yang cocok, mis. isi artikel saja.
	$body = $xp->query( '//body' )->item( 0 );
	foreach ( $area as $query ) {
		$hit = $xp->query( $query )->item( 0 );
		if ( $hit ) {
			$body = $hit;
			break;
		}
	}
	$scope = $body ? $body : $doc;

	$signal['heading'] = array();
	foreach ( $xp->query( './/h2 | .//h3', $scope ) as $node ) {
		$signal['heading'][] = strtolower( $node->nodeName ) . ': ' . mea_parity_clean( $node->textContent );
	}
	$links = array();
	foreach ( $xp->query( './/a[@href]', $scope ) as $node ) {
		$href = mea_parity_path( $node->getAttribute( 'href' ), $base );
		if ( '' !== $href && '/' === $href[0] ) {
			$links[] = preg_replace( '/#.*$/', '', $href );
		}
	}
	$links = array_values( array_unique( array_filter( $links ) ) );
	sort( $links );
	$signal['links'] = $links;

	$images = array();
	foreach ( $xp->query( './/img', $scope ) as $node ) {
		$src = $node->getAttribute( 'src' );
		if ( '' === $src || 0 === strpos( $src, 'data:' ) ) {
			$src = $node->getAttribute( 'data-src' );
		}
		$images[] = mea_parity_path( $src, $base ) . ' | alt: ' . mea_parity_clean( $node->getAttribute( 'alt' ) );
	}
	sort( $images );
	$signal['images'] = $images;

	$text             = $body ? mea_parity_clean( $body->textContent ) : '';
	$signal['text']   = $text;
	$signal['words']  = '' === $text ? 0 : count( preg_split( '/\s+/u', $text ) );
	$signal['noindex'] = (bool) preg_match( '/noindex/i', $robots );
	return $signal;
}

/**
 * Kemiripan dua teks: Jaccard dari shingle 3 kata (0–1).
 *
 * @param string $a Teks A.
 * @param string $b Teks B.
 * @return float
 */
function mea_parity_similarity( string $a, string $b ): float {
	$shingles = static function ( string $text ): array {
		$words = preg_split( '/\s+/u', mb_strtolower( $text ), -1, PREG_SPLIT_NO_EMPTY );
		$set   = array();
		$n     = count( $words );
		for ( $i = 0; $i < max( 1, $n - 2 ); $i++ ) {
			$set[ implode( ' ', array_slice( $words, $i, 3 ) ) ] = true;
		}
		return $set;
	};
	$sa = $shingles( $a );
	$sb = $shingles( $b );
	if ( ! $sa && ! $sb ) {
		return 1.0;
	}
	$inter = count( array_intersect_key( $sa, $sb ) );
	$union = count( $sa + $sb );
	return $union ? $inter / $union : 1.0;
}

/**
 * Ringkas beda dua daftar.
 *
 * @param string[] $a Sebelum.
 * @param string[] $b Sesudah.
 * @return string
 */
function mea_parity_list_diff( array $a, array $b ): string {
	$out = array();
	foreach ( array_diff( $a, $b ) as $x ) {
		$out[] = '- ' . $x;
	}
	foreach ( array_diff( $b, $a ) as $x ) {
		$out[] = '+ ' . $x;
	}
	if ( ! $out && $a !== $b ) {
		$out[] = '(urutan berbeda)';
	}
	return implode( "\n", $out );
}

/**
 * Bandingkan dua snapshot.
 *
 * @param array<string,string> $opts Opsi.
 * @return int Jumlah URL yang berbeda.
 */
function mea_parity_compare( array $opts ): int {
	$before = rtrim( $opts['before'] ?? '', '/' );
	$after  = rtrim( $opts['after'] ?? '', '/' );
	$out    = rtrim( $opts['out'] ?? 'laporan', '/' );
	if ( ! is_file( $before . '/index.json' ) || ! is_file( $after . '/index.json' ) ) {
		mea_parity_fail( 'Wajib: --before=folder --after=folder (hasil snapshot, berisi index.json)' );
	}
	if ( ! is_dir( $out ) && ! mkdir( $out, 0777, true ) ) {
		mea_parity_fail( "Tidak bisa membuat {$out}" );
	}
	$ib        = json_decode( file_get_contents( $before . '/index.json' ), true );
	$ia        = json_decode( file_get_contents( $after . '/index.json' ), true );
	$threshold = (float) ( $opts['ambang'] ?? 0.98 );
	$by_class  = static function ( string $list ): array {
		$out = array();
		foreach ( array_filter( array_map( 'trim', explode( ',', $list ) ) ) as $class ) {
			$out[] = '//*[contains(concat(" ", normalize-space(@class), " "), " ' . trim( $class, ' .' ) . ' ")]';
		}
		return $out;
	};
	$ignore = $by_class( $opts['abaikan'] ?? '' );
	$area   = $by_class( $opts['area'] ?? '' );

	$fields = array( 'status', 'location', 'title', 'description', 'robots', 'canonical', 'og_locale', 'og_image', 'lang', 'h1', 'heading', 'text', 'links', 'images', 'jsonld' );
	// Sinyal yang memang sengaja berubah, mis. --abaikan-sinyal=lang,og_locale (PLAN 10 nomor 3).
	$skip   = array_filter( array_map( 'trim', explode( ',', $opts['abaikan-sinyal'] ?? '' ) ) );
	$fields = array_values( array_diff( $fields, $skip ) );
	$csv    = fopen( $out . '/paritas.csv', 'w' );
	fwrite( $csv, "\xEF\xBB\xBF" );
	fputcsv( $csv, array( 'path', 'hasil', 'beda', 'kemiripan_teks', 'kata_sebelum', 'kata_sesudah', 'detail' ) );

	$rows  = array();
	$count = array(
		'lolos' => 0,
		'beda'  => 0,
	);
	$per   = array_fill_keys( $fields, 0 );
	if ( ! in_array( 'text', $fields, true ) ) {
		unset( $per['text'] );
	}
	foreach ( $ib['urls'] as $path => $b ) {
		$a = $ia['urls'][ $path ] ?? null;
		if ( null === $a ) {
			$rows[] = array( $path, 'beda', 'tidak ada di snapshot sesudah', '', '', '', '' );
			++$count['beda'];
			continue;
		}
		$sb = 200 === $b['status'] ? mea_parity_extract( (string) file_get_contents( $before . '/html/' . $b['file'] ), $ib['base'], $ignore, $area ) : array();
		$sa = 200 === $a['status'] ? mea_parity_extract( (string) file_get_contents( $after . '/html/' . $a['file'] ), $ia['base'], $ignore, $area ) : array();
		$sb['status']   = $b['status'];
		$sa['status']   = $a['status'];
		$sb['location'] = $b['location'];
		$sa['location'] = $a['location'];

		$diffs  = array();
		$detail = array();
		$sim    = '';
		foreach ( $fields as $field ) {
			$vb = $sb[ $field ] ?? null;
			$va = $sa[ $field ] ?? null;
			if ( 'text' === $field ) {
				if ( null === $vb && null === $va ) {
					continue;
				}
				$sim = round( mea_parity_similarity( (string) $vb, (string) $va ), 4 );
				if ( $sim < $threshold ) {
					$diffs[]  = 'teks';
					$detail[] = "teks: kemiripan {$sim} (ambang {$threshold}), kata " . ( $sb['words'] ?? 0 ) . ' → ' . ( $sa['words'] ?? 0 );
				}
				continue;
			}
			if ( $vb === $va ) {
				continue;
			}
			$diffs[] = $field;
			++$per[ $field ];
			$detail[] = is_array( $vb ) || is_array( $va )
				? $field . ":\n" . mea_parity_list_diff( (array) $vb, (array) $va )
				: $field . ': "' . $vb . '" → "' . $va . '"';
		}
		if ( in_array( 'teks', $diffs, true ) ) {
			++$per['text'];
		}
		$result = $diffs ? 'beda' : 'lolos';
		++$count[ $result ];
		$rows[] = array( $path, $result, implode( ', ', $diffs ), $sim, $sb['words'] ?? '', $sa['words'] ?? '', implode( "\n", $detail ) );
	}
	foreach ( array_diff( array_keys( $ia['urls'] ), array_keys( $ib['urls'] ) ) as $path ) {
		$rows[] = array( $path, 'info', 'hanya ada di snapshot sesudah', '', '', '', '' );
	}
	foreach ( $rows as $row ) {
		fputcsv( $csv, $row );
	}
	fclose( $csv );

	$md   = array();
	$md[] = '# Laporan paritas SEO';
	$md[] = '';
	$md[] = '- Sebelum: ' . $ib['base'] . ' (' . $ib['waktu'] . ')';
	$md[] = '- Sesudah: ' . $ia['base'] . ' (' . $ia['waktu'] . ')';
	$md[] = '- URL dibandingkan: ' . count( $ib['urls'] ) . ' · **lolos ' . $count['lolos'] . '** · **beda ' . $count['beda'] . '**';
	$md[] = '- Ambang kemiripan teks: ' . $threshold . ' (Jaccard shingle 3 kata, header/footer/nav diabaikan)';
	if ( $skip ) {
		$md[] = '- Sinyal yang tidak dibandingkan: ' . implode( ', ', $skip );
	}
	if ( $area ) {
		$md[] = '- Area isi: ' . $opts['area'];
	}
	$md[] = '';
	$md[] = '| Sinyal | URL berbeda |';
	$md[] = '|---|---|';
	foreach ( $per as $field => $n ) {
		$md[] = '| ' . $field . ' | ' . $n . ' |';
	}
	$md[] = '';
	foreach ( $rows as $row ) {
		if ( 'lolos' === $row[1] ) {
			continue;
		}
		$md[] = '## `' . $row[0] . '` · ' . $row[1] . ( '' !== $row[2] ? ' · ' . $row[2] : '' );
		if ( '' !== $row[6] ) {
			$md[] = '';
			$md[] = '```text';
			$md[] = $row[6];
			$md[] = '```';
		}
		$md[] = '';
	}
	file_put_contents( $out . '/ringkasan.md', implode( "\n", $md ) );
	fwrite( STDERR, "Lolos {$count['lolos']}, beda {$count['beda']}. Laporan: {$out}/ringkasan.md dan {$out}/paritas.csv\n" );
	return $count['beda'];
}

$mea_parity_cmd  = $argv[1] ?? '';
$mea_parity_opts = mea_parity_opts( $argv );
if ( 'snapshot' === $mea_parity_cmd ) {
	mea_parity_snapshot( $mea_parity_opts );
	exit( 0 );
}
if ( 'compare' === $mea_parity_cmd ) {
	exit( mea_parity_compare( $mea_parity_opts ) > 0 ? 2 : 0 );
}
fwrite( STDERR, "Pemakaian:\n  php parity.php snapshot --base=URL --out=folder [--sitemap=/sitemap_index.xml | --urls=berkas] [--delay=500] [--auth=user:pass] [--header='X: y'] [--insecure]\n  php parity.php compare --before=folder --after=folder [--out=laporan] [--ambang=0.98] [--abaikan=kelas1,kelas2] [--area=kelas1,kelas2] [--abaikan-sinyal=lang,og_locale]\n" );
exit( 1 );
