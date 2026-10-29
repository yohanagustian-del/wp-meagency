/**
 * Motion MEA Orbit (docs/motion/00-peta-motion.md, PLAN bagian 8 dan 9).
 *
 * Satu modul tanpa library, dimuat dengan defer hanya di halaman Orbit.
 * - M1 hero: komponen scroll-scrub (canvas + rAF).
 * - M3 cara kerja: crossfade foto mengikuti langkah aktif.
 * - Mockup HP "Konten": video bisu diputar saat terlihat.
 * - M4 Kurva Bellarosa: diputar sekali saat 60% terlihat, menahan frame akhir.
 * - Motion kode: hitung naik, reveal, teks per baris, garis, penanda bab.
 *
 * Aturan bersama: tidak ada byte sequence atau video sebelum `load`, dan tiap
 * paket baru dimuat saat section-nya berjarak paling jauh satu layar. Reduced
 * motion, Hemat Data, atau koneksi 2g/3g: hanya poster. Tanpa JavaScript semua
 * teks, angka, dan poster tetap tampil.
 *
 * @package MEA_Orbit
 */

( function () {
	'use strict';

	var cfg = window.meaMotion || {};
	// Versi tema sebagai query: nama berkas motion tetap sama antar rilis, jadi tanpa
	// ini browser dan CDN bisa terus menyajikan frame lama dari cache.
	var bust = cfg.ver ? '?v=' + encodeURIComponent( cfg.ver ) : '';
	var root = document.documentElement;
	var media = function ( query ) {
		return window.matchMedia( query ).matches;
	};
	var conn = navigator.connection || {};
	var reduce = media( '(prefers-reduced-motion: reduce)' );
	// Hanya poster: reduced motion, Hemat Data, atau koneksi lambat.
	var lite = reduce || true === conn.saveData || /2g|3g/.test( conn.effectiveType || '' );
	var mobile = media( '(max-width: 767.98px)' );
	// Kelas js-motion dipasang skrip kecil di <head> (inc/assets.php) bila tidak reduced motion.
	var animate = root.classList.contains( 'js-motion' ) && 'IntersectionObserver' in window;
	var clamp = function ( value, min, max ) {
		return Math.min( max, Math.max( min, value ) );
	};
	var each = function ( list, fn ) {
		Array.prototype.forEach.call( list, fn );
	};

	/**
	 * Jalankan fn setelah event load (sekali).
	 *
	 * @param {Function} fn
	 */
	function afterLoad( fn ) {
		if ( 'complete' === document.readyState ) {
			setTimeout( fn, 0 );
		} else {
			window.addEventListener( 'load', fn, { once: true } );
		}
	}

	/**
	 * Jalankan fn sekali saat el berjarak paling jauh satu layar dari viewport.
	 *
	 * @param {Element}  el
	 * @param {Function} fn
	 */
	function whenNear( el, fn ) {
		if ( ! ( 'IntersectionObserver' in window ) ) {
			fn();
			return;
		}
		var io = new IntersectionObserver(
			function ( entries ) {
				if ( entries.some( function ( e ) {
					return e.isIntersecting;
				} ) ) {
					io.disconnect();
					fn();
				}
			},
			{ rootMargin: '100% 0px' }
		);
		io.observe( el );
	}

	/**
	 * Jalankan fn sekali pada interaksi pertama (scroll, sentuh, pointer, keyboard).
	 * PLAN 9.2: frame hero menunggu interaksi supaya tidak ikut dihitung saat halaman diam.
	 *
	 * @param {Function} fn
	 */
	function afterInteraction( fn ) {
		if ( window.scrollY > 0 ) {
			fn();
			return;
		}
		var events = [ 'scroll', 'wheel', 'touchstart', 'pointerdown', 'pointermove', 'keydown' ];
		var done = false;
		var go = function ( e ) {
			// Chrome mengirim pointermove sintetis (tanpa perpindahan) saat tata letak
			// berubah di bawah kursor yang diam. Itu bukan interaksi.
			if ( done || ( 'pointermove' === e.type && ! e.movementX && ! e.movementY ) ) {
				return;
			}
			done = true;
			events.forEach( function ( name ) {
				window.removeEventListener( name, go, { passive: true } );
			} );
			fn();
		};
		events.forEach( function ( name ) {
			window.addEventListener( name, go, { passive: true } );
		} );
	}

	/**
	 * Urutan muat progresif: frame 0, terakhir, lalu tiap 16, 8, 4, 2, 1 sampai stride.
	 *
	 * @param {number} count
	 * @param {number} stride Jarak frame terkecil yang dimuat (1 = semua).
	 * @return {number[]}
	 */
	function loadOrder( count, stride ) {
		var out = [ 0, count - 1 ];
		var seen = {};
		seen[ 0 ] = seen[ count - 1 ] = true;
		for ( var step = 16; step >= stride; step = step / 2 ) {
			for ( var i = 0; i < count; i += step ) {
				if ( ! seen[ i ] ) {
					seen[ i ] = true;
					out.push( i );
				}
			}
		}
		return out;
	}

	/**
	 * Komponen scroll-scrub (dipakai M1).
	 *
	 * Canvas diletakkan di atas poster dan baru terlihat setelah frame pertama
	 * tergambar, jadi poster (gambar LCP) tidak pernah diganti layar kosong.
	 *
	 * @param {Object}   opt
	 * @param {Element}  opt.box      Wadah berisi <picture> poster.
	 * @param {string}   opt.dir      URL folder frame (diakhiri /).
	 * @param {number}   opt.count    Jumlah frame.
	 * @param {number}   opt.size     Lebar frame dalam piksel.
	 * @param {number}   opt.stride   Lihat loadOrder().
	 * @param {Function} opt.progress Mengembalikan 0–1.
	 * @return {Object} { load: Function }
	 */
	function scrub( opt ) {
		var canvas = document.createElement( 'canvas' );
		var ctx = canvas.getContext( '2d' );
		var frames = new Array( opt.count );
		var current = 0;
		var drawn = -1;
		var raf = 0;
		var inView = true;

		canvas.hidden = true;
		canvas.setAttribute( 'aria-hidden', 'true' );
		opt.box.appendChild( canvas );

		function size() {
			var width = opt.box.querySelector( 'picture' ).getBoundingClientRect().width || opt.box.clientWidth;
			var px = Math.round( Math.min( width * Math.min( window.devicePixelRatio || 1, 2 ), opt.size ) );
			if ( px > 0 && canvas.width !== px ) {
				canvas.width = canvas.height = px;
				drawn = -1;
			}
		}

		function nearest( i ) {
			if ( frames[ i ] ) {
				return i;
			}
			for ( var d = 1; d < opt.count; d++ ) {
				if ( i - d >= 0 && frames[ i - d ] ) {
					return i - d;
				}
				if ( i + d < opt.count && frames[ i + d ] ) {
					return i + d;
				}
			}
			return -1;
		}

		function paint() {
			var k = nearest( Math.round( current ) );
			if ( k < 0 || k === drawn ) {
				return;
			}
			if ( canvas.hidden ) {
				size();
			}
			ctx.imageSmoothingQuality = 'high';
			ctx.clearRect( 0, 0, canvas.width, canvas.height );
			ctx.drawImage( frames[ k ], 0, 0, canvas.width, canvas.height );
			drawn = k;
			if ( canvas.hidden ) {
				canvas.hidden = false;
				opt.box.classList.add( 'is-live' );
			}
		}

		function tick() {
			raf = 0;
			var target = opt.progress() * ( opt.count - 1 );
			current += ( target - current ) * 0.25;
			if ( Math.abs( target - current ) < 0.02 ) {
				current = target;
			}
			paint();
			if ( current !== target && inView ) {
				raf = window.requestAnimationFrame( tick );
			}
		}

		function kick() {
			if ( ! raf && inView ) {
				raf = window.requestAnimationFrame( tick );
			}
		}

		function load() {
			var queue = loadOrder( opt.count, opt.stride );
			var next = 0;
			var busy = 0;

			current = opt.progress() * ( opt.count - 1 );
			new IntersectionObserver(
				function ( entries ) {
					inView = entries[ entries.length - 1 ].isIntersecting;
					kick();
				},
				{ rootMargin: '120px 0px' }
			).observe( opt.box );
			window.addEventListener( 'scroll', kick, { passive: true } );
			window.addEventListener( 'resize', function () {
				size();
				paint();
				kick();
			} );

			( function pump() {
				while ( busy < 6 && next < queue.length ) {
					( function ( i ) {
						var img = new Image();
						busy++;
						img.decoding = 'async';
						img.onload = function () {
							( img.decode ? img.decode() : Promise.resolve() ).catch( function () {} ).then( function () {
								frames[ i ] = img;
								busy--;
								paint();
								kick();
								pump();
							} );
						};
						img.onerror = function () {
							busy--;
							pump();
						};
						img.src = opt.dir + ( '00' + i ).slice( -3 ) + '.webp' + bust;
					}( queue[ next++ ] ) );
				}
			}() );
		}

		return { load: load };
	}

	/**
	 * M1 · hero (kubus v2). Tidak di-pin.
	 * progress = scrollY / (heroTop + 0,6 × tinggi hero) (README paket hero v2).
	 * Desktop berhenti di tier "tiap frame keempat", 31 frame ±0,82 MB, supaya
	 * poster + frame tetap ≤ 1,5 MB sebelum hero terlewati (PLAN 9.1, rilis 0.2.0).
	 */
	function hero() {
		var box = document.querySelector( '[data-motion="hero"]' );
		if ( ! box || lite ) {
			return;
		}
		var section = box.closest( '.hero' ) || box;
		var player = scrub( {
			box: box,
			dir: cfg.base + 'hero/frames/' + ( mobile ? 'mobile/' : 'desktop/' ),
			count: mobile ? 60 : 120,
			size: mobile ? 720 : 1200,
			stride: mobile ? 1 : 4,
			progress: function () {
				return clamp( window.scrollY / ( section.offsetTop + section.offsetHeight * 0.6 ), 0, 1 );
			},
		} );
		afterLoad( function () {
			afterInteraction( function () {
				whenNear( box, player.load );
			} );
		} );
	}

	/**
	 * M3 · cara kerja. Kotak sticky berisi satu foto per langkah; foto langkah
	 * aktif tampil (crossfade di CSS, tanpa fade saat reduced motion).
	 *
	 * progress diukur dari garis acuan (tengah layar; di HP tengah area di bawah
	 * kotak sticky) terhadap blok langkah pertama sampai terakhir. Pergantian
	 * juga berjalan saat lite (reduced motion, Hemat Data): hanya class yang pindah.
	 */
	function proses() {
		each( document.querySelectorAll( '[data-motion="proses"]' ), function ( wrap ) {
			var box = wrap.querySelector( '.proses-visual' );
			var steps = wrap.querySelectorAll( '.steps > li' );
			var pics = box ? box.querySelectorAll( '.proses-stack picture' ) : [];
			if ( ! box || ! steps.length ) {
				return;
			}

			var progress = function () {
				var first = steps[ 0 ].getBoundingClientRect();
				var last = steps[ steps.length - 1 ].getBoundingClientRect();
				var visual = box.getBoundingClientRect();
				var stacked = visual.right > first.left;
				var line = stacked ? visual.bottom + ( window.innerHeight - visual.bottom ) / 2 : window.innerHeight / 2;
				return clamp( ( line - first.top ) / Math.max( 1, last.bottom - first.top ), 0, 1 );
			};

			// Langkah aktif: blok progress dibagi rata per langkah (rumus 150 frame lama
			// dipertahankan supaya titik ganti langkah tidak bergeser).
			var active = -1;
			var stepRaf = 0;
			var mark = function () {
				stepRaf = 0;
				var k = Math.min( steps.length - 1, Math.floor( progress() * 149 * steps.length / 150 ) );
				if ( k !== active ) {
					each( steps, function ( li, i ) {
						li.classList.toggle( 'is-active', i === k );
					} );
					each( pics, function ( pic, i ) {
						pic.classList.toggle( 'is-active', i === k );
					} );
					active = k;
				}
			};
			window.addEventListener( 'scroll', function () {
				if ( ! stepRaf ) {
					stepRaf = window.requestAnimationFrame( mark );
				}
			}, { passive: true } );
			mark();
		} );
	}

	/**
	 * Elemen <video> bisu tanpa kontrol.
	 *
	 * @param {string} src    URL tanpa ekstensi.
	 * @param {number} width
	 * @param {number} height
	 * @return {HTMLVideoElement}
	 */
	function silentVideo( src, width, height ) {
		var video = document.createElement( 'video' );
		video.muted = true;
		video.playsInline = true;
		video.preload = 'auto';
		video.width = width;
		video.height = height;
		[ 'muted', 'playsinline', 'disablepictureinpicture', 'disableremoteplayback' ].forEach( function ( name ) {
			video.setAttribute( name, '' );
		} );
		video.setAttribute( 'aria-hidden', 'true' );
		[ [ 'webm', 'video/webm' ], [ 'mp4', 'video/mp4' ] ].forEach( function ( type ) {
			var source = document.createElement( 'source' );
			source.src = src + '.' + type[ 0 ] + bust;
			source.type = type[ 1 ];
			video.appendChild( source );
		} );
		return video;
	}

	/**
	 * Mockup HP "Konten". Poster di HTML; saat section mendekati layar, poster
	 * yang tampil diganti video bisu (preload none, jadi belum mengunduh apa pun).
	 * Video diputar saat 50% terlihat dan berhenti saat keluar layar. Mockup yang
	 * disembunyikan CSS (HP: hanya yang pertama) tidak pernah diganti.
	 */
	function konten() {
		var screens = document.querySelectorAll( '[data-motion="konten"]' );
		if ( ! screens.length || lite || ! ( 'IntersectionObserver' in window ) ) {
			return;
		}
		var io = new IntersectionObserver( function ( entries ) {
			entries.forEach( function ( e ) {
				if ( e.isIntersecting ) {
					var p = e.target.play();
					if ( p ) {
						p.catch( function () {} );
					}
				} else {
					e.target.pause();
				}
			} );
		}, { threshold: 0.5 } );

		afterLoad( function () {
			whenNear( screens[ 0 ].closest( 'section' ) || screens[ 0 ], function () {
				each( screens, function ( screen ) {
					var img = screen.querySelector( 'img' );
					if ( ! img || null === screen.offsetParent ) {
						return;
					}
					var video = document.createElement( 'video' );
					video.muted = true;
					video.loop = true;
					video.playsInline = true;
					video.preload = 'none';
					video.width = img.width;
					video.height = img.height;
					video.poster = img.currentSrc || img.src;
					[ 'muted', 'loop', 'playsinline', 'disablepictureinpicture', 'disableremoteplayback' ].forEach( function ( name ) {
						video.setAttribute( name, '' );
					} );
					video.setAttribute( 'aria-hidden', 'true' );
					[ [ 'data-webm', 'video/webm' ], [ 'data-mp4', 'video/mp4' ] ].forEach( function ( type ) {
						var url = screen.getAttribute( type[ 0 ] );
						if ( url ) {
							var source = document.createElement( 'source' );
							source.src = url;
							source.type = type[ 1 ];
							video.appendChild( source );
						}
					} );
					screen.replaceChild( video, img );
					io.observe( video );
				} );
			} );
		} );
	}

	/**
	 * M4 · Kurva Bellarosa. Poster = frame akhir; video dimulai dari grafik
	 * kosong. Video baru menggantikan poster saat figure tidak terlihat, supaya
	 * pengunjung tidak melihat grafik penuh lalu kosong. Diputar sekali saat 60%
	 * terlihat lalu menahan frame akhir. Bila play() ditolak, poster kembali.
	 */
	function bellarosa() {
		var fig = document.querySelector( '[data-motion="bellarosa"]' );
		if ( ! fig || lite || ! ( 'IntersectionObserver' in window ) ) {
			return;
		}
		afterLoad( function () {
			whenNear( fig, function () {
				var video = silentVideo(
					cfg.base + ( mobile ? 'bellarosa/bellarosa-case-mobile-900x900' : 'bellarosa/bellarosa-case-desktop-1200x900' ),
					mobile ? 900 : 1200,
					900
				);
				var ready = false;
				var swapped = false;
				var played = false;
				var ratio = 0;
				var io;

				var unswap = function () {
					video.hidden = true;
					fig.classList.remove( 'is-live' );
				};
				var update = function () {
					if ( ! ready || played ) {
						return;
					}
					if ( ! swapped ) {
						if ( ratio > 0 ) {
							return;
						}
						swapped = true;
						video.hidden = false;
						fig.classList.add( 'is-live' );
					}
					if ( ratio >= 0.6 ) {
						played = true;
						io.disconnect();
						var p = video.play();
						if ( p ) {
							p.catch( unswap );
						}
					}
				};

				video.hidden = true;
				video.addEventListener( 'loadeddata', function () {
					ready = true;
					update();
				}, { once: true } );
				video.addEventListener( 'error', unswap );
				fig.appendChild( video );
				io = new IntersectionObserver( function ( entries ) {
					ratio = entries[ entries.length - 1 ].intersectionRatio;
					update();
				}, { threshold: [ 0, 0.6 ] } );
				io.observe( fig );
			} );
		} );
	}

	/**
	 * Hitung naik (H2, S2, bukti layanan). Angka yang sudah terlihat saat skrip
	 * jalan dibiarkan apa adanya supaya tidak berkedip ke nol.
	 */
	function counters() {
		var items = document.querySelectorAll( '[data-count]' );
		if ( ! animate || ! items.length ) {
			return;
		}
		var format = function ( el, value ) {
			var dec = parseInt( el.getAttribute( 'data-dec' ), 10 ) || 0;
			var parts = value.toFixed( dec ).split( '.' );
			if ( '1' === el.getAttribute( 'data-group' ) ) {
				parts[ 0 ] = parts[ 0 ].replace( /\B(?=(\d{3})+(?!\d))/g, '.' );
			}
			return ( el.getAttribute( 'data-prefix' ) || '' ) + parts.join( ',' ) + ( el.getAttribute( 'data-suffix' ) || '' );
		};
		var run = function ( el, out ) {
			var target = parseFloat( el.getAttribute( 'data-count' ) );
			var t0 = 0;
			var frame = function ( now ) {
				t0 = t0 || now;
				var t = Math.min( 1, ( now - t0 ) / 1400 );
				out.textContent = format( el, target * ( 1 - Math.pow( 2, -10 * t ) ) );
				if ( t < 1 ) {
					window.requestAnimationFrame( frame );
				} else {
					out.textContent = format( el, target );
				}
			};
			window.requestAnimationFrame( frame );
		};
		var io = new IntersectionObserver( function ( entries ) {
			entries.forEach( function ( e ) {
				if ( e.isIntersecting ) {
					io.unobserve( e.target );
					run( e.target, e.target.querySelector( '.count' ) );
				}
			} );
		}, { threshold: 0.6 } );

		each( items, function ( el ) {
			var rect = el.getBoundingClientRect();
			if ( rect.top < window.innerHeight && rect.bottom > 0 ) {
				return;
			}
			var final = el.textContent;
			el.textContent = '';
			var sr = document.createElement( 'span' );
			sr.className = 'screen-reader-text';
			sr.textContent = final;
			var out = document.createElement( 'span' );
			out.className = 'count';
			out.setAttribute( 'aria-hidden', 'true' );
			out.textContent = format( el, 0 );
			el.appendChild( sr );
			el.appendChild( out );
			io.observe( el );
		} );
	}

	/**
	 * Pecah teks menjadi kata dengan indeks baris (--l), untuk muncul per baris.
	 * Elemen yang berisi markup selain teks muncul utuh.
	 *
	 * @param {Element} el
	 * @return {number} Durasi total (ms) sebelum markup asli dikembalikan.
	 */
	function splitLines( el ) {
		if ( el.children.length || ! el.textContent.trim() ) {
			return 0;
		}
		var original = el.textContent;
		var words = original.trim().split( /\s+/ );
		el.textContent = '';
		var spans = words.map( function ( word, i ) {
			var span = document.createElement( 'span' );
			span.className = 'rl-w';
			span.textContent = word;
			el.appendChild( span );
			if ( i < words.length - 1 ) {
				el.appendChild( document.createTextNode( ' ' ) );
			}
			return span;
		} );
		var line = -1;
		var top = null;
		spans.forEach( function ( span ) {
			if ( span.offsetTop !== top ) {
				top = span.offsetTop;
				line++;
			}
			span.style.setProperty( '--l', line );
		} );
		el.classList.add( 'is-split' );
		var total = line * 90 + 600;
		setTimeout( function () {
			el.textContent = original;
			el.classList.remove( 'is-split' );
		}, total + 100 );
		return total;
	}

	/**
	 * Reveal: foto (mask naik 12 px, 600 ms, mosaik stagger 80 ms), teks per baris,
	 * garis di bawah angka hero, garis meta, garis kutipan, bar komposisi. Keadaan
	 * awal diatur CSS di bawah .js-motion; skrip ini hanya menambah .is-in.
	 */
	function reveals() {
		var items = document.querySelectorAll( '[data-reveal], [data-reveal-lines], .proof > div, .case-head .meta, .story-body .pull-quote, .mix' );
		var showAll = function () {
			each( items, function ( el ) {
				el.classList.add( 'is-in' );
			} );
			root.classList.add( 'motion-ready' );
		};

		if ( ! animate ) {
			showAll();
			return;
		}

		each( document.querySelectorAll( '.mosaic' ), function ( group ) {
			each( group.querySelectorAll( '[data-reveal]' ), function ( el, i ) {
				el.style.setProperty( '--i', i );
			} );
		} );

		var show = function ( el ) {
			if ( el.hasAttribute( 'data-reveal-lines' ) && splitLines( el ) ) {
				void el.offsetWidth; // Keadaan awal kata harus tergambar dulu supaya transisinya jalan.
			}
			el.classList.add( 'is-in' );
		};

		// Skrip terlambat (jaringan sangat lambat): tampilkan semua tanpa animasi.
		if ( window.performance && performance.now() > 2500 ) {
			showAll();
			return;
		}

		// Elemen yang disembunyikan dengan clip-path dianggap tidak terlihat oleh
		// IntersectionObserver, jadi yang diamati adalah induknya. Foto satu wadah
		// (mosaik) muncul bersama dengan stagger 80 ms.
		var groups = new Map();
		var io = new IntersectionObserver( function ( entries ) {
			entries.forEach( function ( e ) {
				if ( e.isIntersecting ) {
					io.unobserve( e.target );
					groups.get( e.target ).forEach( show );
				}
			} );
		}, { threshold: 0.2, rootMargin: '0px 0px -8% 0px' } );

		each( items, function ( el ) {
			var target = el.matches( '[data-reveal], .mix' ) ? el.parentElement : el;
			if ( ! groups.has( target ) ) {
				groups.set( target, [] );
				io.observe( target );
			}
			groups.get( target ).push( el );
		} );
		root.classList.add( 'motion-ready' );
	}

	/**
	 * S3 · penanda bab aktif di samping teks cerita studi kasus.
	 */
	function chapters() {
		var heads = document.querySelectorAll( '.story-body h2[id^="bab-"]' );
		if ( ! heads.length ) {
			return;
		}
		var raf = 0;
		var mark = function () {
			raf = 0;
			var current = null;
			each( heads, function ( h ) {
				if ( h.getBoundingClientRect().top < window.innerHeight * 0.4 ) {
					current = h;
				}
			} );
			each( heads, function ( h ) {
				h.classList.toggle( 'is-current', h === current );
			} );
		};
		window.addEventListener( 'scroll', function () {
			if ( ! raf ) {
				raf = window.requestAnimationFrame( mark );
			}
		}, { passive: true } );
		mark();
	}

	reveals();
	counters();
	chapters();
	hero();
	proses();
	konten();
	bellarosa();
}() );
