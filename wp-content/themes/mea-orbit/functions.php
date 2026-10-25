<?php
/**
 * MEA Orbit: memuat semua file inc/.
 *
 * @package MEA_Orbit
 */

defined( 'ABSPATH' ) || exit;

define( 'MEA_ORBIT_VERSION', '0.1.0' );
define( 'MEA_ORBIT_DIR', get_template_directory() );
define( 'MEA_ORBIT_URI', get_template_directory_uri() );

require MEA_ORBIT_DIR . '/inc/scope.php';
require MEA_ORBIT_DIR . '/inc/setup.php';
require MEA_ORBIT_DIR . '/inc/assets.php';
require MEA_ORBIT_DIR . '/inc/locale.php';
require MEA_ORBIT_DIR . '/inc/template-tags.php';
require MEA_ORBIT_DIR . '/inc/elementor.php';
