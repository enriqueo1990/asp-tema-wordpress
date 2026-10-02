<?php
/**
 * Apertura del documento y cabecera.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php /* Safari en iPhone convertía fechas y ciudades en enlaces punteados (agenda, Mapas). Los enlaces tel: y mailto: explícitos siguen andando. */ ?>
	<meta name="format-detection" content="telephone=no, date=no, address=no, email=no">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<?php get_template_part( 'parts/header/site-header' ); ?>
<main id="contenido">
