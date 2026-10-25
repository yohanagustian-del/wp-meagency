<?php
/**
 * Header situs. Kode yang sama dipakai di setiap URL.
 *
 * @package MEA_Orbit
 */

defined( 'ABSPATH' ) || exit;
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip" href="#konten"><?php esc_html_e( 'Lewati ke konten', 'mea-orbit' ); ?></a>
<header class="site-head">
	<div class="wrap">
		<a class="brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'MEA Agency, beranda', 'mea-orbit' ); ?>"><span class="brand-mark" aria-hidden="true"></span>MEA Agency</a>
		<nav class="nav" aria-label="<?php esc_attr_e( 'Menu utama', 'mea-orbit' ); ?>">
			<?php mea_orbit_menu( 'mea-orbit-utama' ); ?>
		</nav>
		<div class="head-actions">
			<details class="nav-sm">
				<summary><?php esc_html_e( 'Menu', 'mea-orbit' ); ?></summary>
				<nav aria-label="<?php esc_attr_e( 'Menu utama', 'mea-orbit' ); ?>">
					<?php mea_orbit_menu( 'mea-orbit-utama' ); ?>
				</nav>
			</details>
			<a class="btn btn-primary" href="<?php echo esc_url( mea_orbit_consult_url() ); ?>"><span class="hide-sm"><?php esc_html_e( 'Jadwalkan konsultasi', 'mea-orbit' ); ?></span><span class="show-sm"><?php esc_html_e( 'Konsultasi', 'mea-orbit' ); ?></span></a>
		</div>
	</div>
</header>
