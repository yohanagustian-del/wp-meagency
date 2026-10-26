<?php
/**
 * Render blok mea/lead-form.
 *
 * @package MEA_Leads
 */

defined( 'ABSPATH' ) || exit;

echo mea_leads_form( array( 'sumber' => (string) ( $attributes['sumber'] ?? '' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- markup di-escape di mea_leads_form().
