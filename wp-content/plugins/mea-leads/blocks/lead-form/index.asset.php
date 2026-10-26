<?php
/**
 * Dependency skrip editor blok (tanpa build step).
 *
 * Nama berkas mengikuti konvensi WordPress (block.json "file:./index.js").
 *
 * @package MEA_Leads
 */

// phpcs:disable WordPress.Files.FileName

return array(
	'dependencies' => array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-server-side-render', 'wp-i18n' ),
	'version'      => '0.1.0',
);
