<?php
/**
 * Acceso a Afirmaciones y Negaciones y numeración romana.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

/**
 * Datos del documento, cacheados en la petición.
 *
 * @return array{cita:string,cita_ref:string,fuente:string,introduccion:string[],articulos:array<int,array{afirmamos:string,negamos:string}>}
 */
function asp_afirmaciones(): array {
	static $datos = null;
	if ( null === $datos ) {
		$datos = require ASP_THEME_DIR . '/inc/afirmaciones-datos.php';
	}
	return $datos;
}

/**
 * Número romano de 1 a 3999.
 *
 * @param int $n Número.
 * @return string
 */
function asp_romano( int $n ): string {
	$mapa = [ 'M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400, 'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40, 'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1 ];
	$out  = '';
	foreach ( $mapa as $letra => $valor ) {
		while ( $n >= $valor ) {
			$out .= $letra;
			$n   -= $valor;
		}
	}
	return $out;
}

/**
 * Ancla de un artículo: #articulo-i … #articulo-xix.
 *
 * @param int $indice Índice base 1.
 * @return string
 */
function asp_afirmacion_ancla( int $indice ): string {
	return 'articulo-' . strtolower( asp_romano( $indice ) );
}
