<?php
/**
 * Homepage (lapis Orbit). Urutan section mengikuti prototipe docs/mea-orbit/index.html.
 *
 * @package MEA_Orbit
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="konten">
	<?php
	get_template_part( 'template-parts/home/hero' );
	get_template_part( 'template-parts/home/tim' );
	get_template_part( 'template-parts/home/layanan' );
	get_template_part( 'template-parts/home/cara-kerja' );
	get_template_part( 'template-parts/home/kasus-unggulan' );
	get_template_part( 'template-parts/global/konsultasi', null, array( 'sumber' => 'home' ) );
	?>
</main>
<?php
get_footer();
