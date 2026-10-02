<?php
/**
 * Template Name: Inicio nuevo
 * Template Post Type: page
 *
 * Inicio rediseñado el 28-9-2026, en paralelo al de siempre (front-page.php),
 * que no se tocó. Para activarlo: Ajustes → Lectura → «Una página estática»
 * y elegir como portada la página que usa esta plantilla. Para volver atrás,
 * elegir de nuevo la página «Inicio». Ver inc/inicio.php.
 *
 * Orden: hero con el evento abierto, quiénes somos con la franja de
 * confianza, cómo servimos, próximos eventos, aliados, predicaciones
 * por conferencia, artículos y formas de participar. Cada bloque tiene su
 * forma y su fondo; el que no tiene datos no se imprime.
 *
 * El texto y el título de la página no se usan: todo sale de los CPT y de
 * Personalizar → Inicio.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

get_header();

foreach ( [ 'hero', 'somos', 'servir', 'agenda', 'aliados', 'predicaciones', 'articulos', 'participar' ] as $asp_parte ) {
	get_template_part( 'parts/inicio/' . $asp_parte );
}

get_footer();
