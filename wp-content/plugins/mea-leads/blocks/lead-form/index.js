/**
 * Editor blok mea/lead-form. JavaScript vanilla tanpa build step.
 */
( function ( wp ) {
	'use strict';

	var el = wp.element.createElement;
	var __ = wp.i18n.__;

	wp.blocks.registerBlockType( 'mea/lead-form', {
		edit: function ( props ) {
			return el(
				'div',
				wp.blockEditor.useBlockProps(),
				el(
					wp.blockEditor.InspectorControls,
					null,
					el(
						wp.components.PanelBody,
						{ title: __( 'Pengaturan form', 'mea-leads' ) },
						el( wp.components.TextControl, {
							label: __( 'Sumber form (mis. layanan-performance-ads)', 'mea-leads' ),
							value: props.attributes.sumber,
							onChange: function ( value ) {
								props.setAttributes( { sumber: value } );
							},
						} )
					)
				),
				el( wp.serverSideRender, { block: 'mea/lead-form', attributes: props.attributes } )
			);
		},
		save: function () {
			return null;
		},
	} );
}( window.wp ) );
