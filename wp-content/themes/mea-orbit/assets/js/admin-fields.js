/**
 * MEA Orbit · admin-fields.js
 * Repeater (tambah, hapus, naik, turun) dan pemilih gambar Media Library
 * untuk meta box dan halaman opsi. JavaScript vanilla, hanya di wp-admin.
 */
( function () {
	'use strict';

	var counter = 0;

	function renumber( repeater ) {
		var name = repeater.getAttribute( 'data-name' );
		var rows = repeater.querySelectorAll( ':scope > .mea-rows > .mea-row' );
		rows.forEach( function ( row, index ) {
			row.querySelectorAll( '[name]' ).forEach( function ( input ) {
				var rest = input.name.slice( name.length ).replace( /^\[[^\]]+\]/, '' );
				input.name = name + '[' + index + ']' + rest;
			} );
		} );
	}

	document.addEventListener( 'click', function ( event ) {
		var target = event.target;
		var repeater = target.closest( '.mea-repeater' );

		if ( target.classList.contains( 'mea-row-add' ) && repeater ) {
			var template = repeater.querySelector( ':scope > template' );
			var html = template.innerHTML.replace( /\[__i__\]/g, '[n' + ( counter++ ) + ']' );
			repeater.querySelector( ':scope > .mea-rows' ).insertAdjacentHTML( 'beforeend', html );
			renumber( repeater );
			return;
		}

		var row = target.closest( '.mea-row' );
		if ( ! row || ! repeater ) {
			return;
		}
		if ( target.classList.contains( 'mea-row-remove' ) ) {
			row.remove();
		} else if ( target.classList.contains( 'mea-row-up' ) && row.previousElementSibling ) {
			row.parentNode.insertBefore( row, row.previousElementSibling );
		} else if ( target.classList.contains( 'mea-row-down' ) && row.nextElementSibling ) {
			row.parentNode.insertBefore( row.nextElementSibling, row );
		} else {
			return;
		}
		renumber( repeater );
	} );

	document.addEventListener( 'click', function ( event ) {
		var button = event.target.closest( '.mea-media-pick' );
		if ( ! button || ! window.wp || ! window.wp.media ) {
			return;
		}
		event.preventDefault();
		var wrap = button.closest( '.mea-media' );
		var frame = window.wp.media( {
			title: button.textContent,
			library: { type: 'image' },
			multiple: false,
		} );
		frame.on( 'select', function () {
			var item = frame.state().get( 'selection' ).first().toJSON();
			wrap.querySelector( '.mea-media-id' ).value = item.id;
			var size = item.sizes && item.sizes.thumbnail ? item.sizes.thumbnail : item;
			var thumb = wrap.querySelector( '.mea-media-thumb' );
			thumb.innerHTML = '';
			var img = document.createElement( 'img' );
			img.src = size.url;
			img.alt = '';
			thumb.appendChild( img );
		} );
		frame.open();
	} );
}() );
