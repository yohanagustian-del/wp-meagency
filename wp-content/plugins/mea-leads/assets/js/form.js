/**
 * MEA Leads · form.js
 * Validasi klien (pesan sama dengan prototipe dan server), kirim lewat REST dengan
 * nonce segar, state terima kasih, dataLayer generate_lead, dan tautan WhatsApp
 * yang terisi otomatis. Tanpa JavaScript, form tetap terkirim lewat admin-post.php.
 */
( function () {
	'use strict';

	var cfg = window.meaLeads || {};

	function normWa( value ) {
		value = value.replace( /[^\d+]/g, '' );
		if ( value.indexOf( '+62' ) === 0 ) {
			value = '0' + value.slice( 3 );
		} else if ( value.indexOf( '62' ) === 0 ) {
			value = '0' + value.slice( 2 );
		}
		return value;
	}

	function fieldError( form, name ) {
		if ( name === 'setuju' ) {
			return form.querySelector( '[id^="err-consent"]' );
		}
		if ( name === 'form' ) {
			return form.querySelector( '.err-form' );
		}
		var input = form.elements[ name ];
		var field = input && input.closest ? input.closest( '.field' ) : null;
		return field ? field.querySelector( '.err' ) : null;
	}

	function setError( form, name, message ) {
		var target = fieldError( form, name );
		var input = form.elements[ name ];
		if ( target ) {
			target.textContent = message || '';
			if ( name === 'form' ) {
				target.hidden = ! message;
			}
		}
		if ( input && input.setAttribute && name !== 'form' ) {
			input.setAttribute( 'aria-invalid', message ? 'true' : 'false' );
		}
	}

	function validate( form ) {
		var errors = {};
		if ( form.elements.nama.value.trim().length < 2 ) {
			errors.nama = 'Isi nama Anda.';
		}
		if ( ! /^08\d{8,11}$/.test( normWa( form.elements.whatsapp.value ) ) ) {
			errors.whatsapp = 'Nomor WhatsApp diawali 08 dan berisi 10–13 angka.';
		}
		if ( ! form.elements.omzet.value ) {
			errors.omzet = 'Pilih kisaran omzet.';
		}
		if ( ! form.elements.setuju.checked ) {
			errors.setuju = 'Centang persetujuan agar tim bisa menghubungi Anda.';
		}
		return errors;
	}

	function showErrors( form, errors ) {
		[ 'nama', 'whatsapp', 'omzet', 'setuju', 'form' ].forEach( function ( name ) {
			setError( form, name, errors[ name ] );
		} );
		var first = form.querySelector( '[aria-invalid="true"]' );
		if ( first ) {
			first.focus();
		} else if ( errors.form ) {
			form.querySelector( '.err-form' ).focus && form.querySelector( '.err-form' ).focus();
		}
	}

	function done( form ) {
		var nama = form.elements.nama.value.trim();
		var toko = form.elements.link_toko.value.trim();
		var msg = 'Halo tim MEA Agency, saya ' + nama + '. Saya baru isi form konsultasi' + ( toko ? ' untuk toko ' + toko : '' ) + '.';
		var wa = form.querySelector( '.wa-cs' );
		if ( wa && cfg.wa ) {
			wa.href = cfg.wa + '?text=' + encodeURIComponent( msg );
		}
		window.dataLayer = window.dataLayer || [];
		window.dataLayer.push( {
			event: 'generate_lead',
			form_source: form.elements.sumber_form.value,
			lead_omzet: form.elements.omzet.value,
		} );
		form.classList.add( 'is-sent' );
		form.querySelector( '.thanks' ).focus();
	}

	function prepare( form ) {
		var query = new URLSearchParams( window.location.search );
		[ 'source', 'medium', 'campaign' ].forEach( function ( key ) {
			var input = form.elements[ 'utm_' + key ];
			if ( input && query.get( 'utm_' + key ) ) {
				input.value = query.get( 'utm_' + key ).slice( 0, 191 );
			}
		} );
		form.elements.halaman_asal.value = window.location.pathname;

		form.addEventListener( 'submit', function ( event ) {
			event.preventDefault();
			if ( form.dataset.busy ) {
				return;
			}
			if ( form.elements.website.value ) {
				return;
			}
			var errors = validate( form );
			showErrors( form, errors );
			if ( Object.keys( errors ).length ) {
				return;
			}

			var button = form.querySelector( 'button[type="submit"]' );
			form.dataset.busy = '1';
			button.disabled = true;

			fetch( cfg.token, { credentials: 'same-origin', cache: 'no-store' } )
				.then( function ( res ) {
					return res.json();
				} )
				.then( function ( token ) {
					var data = new FormData( form );
					data.set( '_mea_nonce', token.nonce );
					return fetch( cfg.kirim, { method: 'POST', body: data, credentials: 'same-origin' } );
				} )
				.then( function ( res ) {
					return res.json().then( function ( body ) {
						return { status: res.status, body: body };
					} );
				} )
				.then( function ( result ) {
					if ( result.body && result.body.ok ) {
						done( form );
						return;
					}
					showErrors( form, ( result.body && result.body.errors ) || { form: cfg.galat } );
				} )
				.catch( function () {
					showErrors( form, { form: cfg.galat } );
				} )
				.then( function () {
					delete form.dataset.busy;
					button.disabled = false;
				} );
		} );
	}

	document.querySelectorAll( 'form.lead-form' ).forEach( prepare );
}() );
