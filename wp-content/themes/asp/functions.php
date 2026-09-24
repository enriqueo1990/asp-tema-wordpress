<?php
/**
 * Ante Su Palabra — punto de entrada del tema.
 *
 * Este archivo únicamente carga los módulos de inc/. Toda la lógica
 * vive ahí, un archivo por responsabilidad (ver docs/plantillas.md).
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

$asp_modulos = [
	'setup',
	'assets',
	'cpt',
	'taxonomias',
	'meta-campos',
	'evento-estado',
	'evento-queries',
	'predicaciones',
	'afirmaciones',
	'template-tags',
	'articulo',
	'recursos',
	'customizer',
	'contacto',
	'redirects',
	'meta-helpers',
	'meta-evento',
	'evento-predicaciones',
	'meta-otros',
	'evento-validacion',
	'admin-rol',
	'admin-ui',
	'admin-duplicar',
	'admin-ayuda',
];
foreach ( $asp_modulos as $asp_modulo ) {
	require_once get_template_directory() . '/inc/' . $asp_modulo . '.php';
}
unset( $asp_modulos, $asp_modulo );
