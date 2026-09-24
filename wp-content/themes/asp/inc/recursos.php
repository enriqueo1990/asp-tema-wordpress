<?php
/**
 * Recursos: la portada de /recursos/ y el archivo de artículos.
 *
 * /recursos/ es un índice curado: artículos destacados, series, la última
 * tanda de predicaciones, artículos recientes y conferencias. El listado
 * completo con filtros y paginación vive en /recursos/articulos/, que es la
 * misma consulta de entradas con una variable de vista.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

/** Sube este número cuando cambien las reglas de reescritura de este archivo. */
const ASP_RECURSOS_REGLAS = '1';

/**
 * Ruta de la página de entradas, ej. "recursos". Vacía si no hay una.
 *
 * @return string
 */
function asp_recursos_ruta(): string {
	$id = (int) get_option( 'page_for_posts' );
	return $id ? (string) get_page_uri( $id ) : '';
}

/**
 * Regla /recursos/articulos/ (y /page/N/) sobre la página de entradas.
 *
 * @return void
 */
function asp_recursos_reglas(): void {
	$ruta = asp_recursos_ruta();
	if ( '' === $ruta ) {
		return;
	}
	add_rewrite_rule(
		'^' . preg_quote( $ruta, '#' ) . '/articulos(?:/page/([0-9]+))?/?$',
		'index.php?pagename=' . $ruta . '&asp_articulos=1&paged=$matches[1]',
		'top'
	);
	if ( get_option( 'asp_recursos_reglas' ) !== ASP_RECURSOS_REGLAS ) {
		flush_rewrite_rules( false );
		update_option( 'asp_recursos_reglas', ASP_RECURSOS_REGLAS );
	}
}
add_action( 'init', 'asp_recursos_reglas' );

/**
 * @param string[] $vars Variables públicas.
 * @return string[]
 */
function asp_recursos_query_vars( array $vars ): array {
	$vars[] = 'asp_articulos';
	return $vars;
}
add_filter( 'query_vars', 'asp_recursos_query_vars' );

/**
 * ¿Estamos en el archivo completo de artículos?
 *
 * @return bool
 */
function asp_es_archivo_articulos(): bool {
	return is_home() && (bool) get_query_var( 'asp_articulos' );
}

/**
 * URL del archivo completo de artículos.
 *
 * @return string
 */
function asp_url_articulos(): string {
	$ruta = asp_recursos_ruta();
	return '' !== $ruta ? home_url( user_trailingslashit( $ruta . '/articulos' ) ) : home_url( '/' );
}

/**
 * La portada de Recursos no se pagina: /recursos/page/2/ era la página 2
 * del listado y ahora ese listado vive en /recursos/articulos/.
 *
 * @return void
 */
function asp_recursos_redirigir_paginas(): void {
	if ( is_home() && is_paged() && ! get_query_var( 'asp_articulos' ) ) {
		wp_safe_redirect( trailingslashit( asp_url_articulos() ) . user_trailingslashit( 'page/' . (int) get_query_var( 'paged' ) ), 301 );
		exit;
	}
}
add_action( 'template_redirect', 'asp_recursos_redirigir_paginas' );

/**
 * Sin esto, redirect_canonical manda /recursos/articulos/ de vuelta a
 * /recursos/, porque la página consultada es la de entradas.
 *
 * @param string|false $url URL canónica propuesta.
 * @return string|false
 */
function asp_recursos_canonica( $url ) {
	return get_query_var( 'asp_articulos' ) ? false : $url;
}
add_filter( 'redirect_canonical', 'asp_recursos_canonica' );

/**
 * Artículos que no pertenecen a ninguna serie, del más nuevo al más viejo.
 * Las series tienen su propio bloque; repetirlas en la lista general
 * llenaba la página con la misma foto.
 *
 * @param int   $cantidad Cuántos.
 * @param int[] $excluir  IDs a dejar afuera.
 * @return WP_Post[]
 */
function asp_articulos_sueltos( int $cantidad, array $excluir = [] ): array {
	return get_posts(
		[
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'posts_per_page'      => $cantidad,
			'post__not_in'        => $excluir,
			'ignore_sticky_posts' => true,
			'tax_query'           => [ [ 'taxonomy' => 'serie', 'operator' => 'NOT EXISTS' ] ],
		]
	);
}

/**
 * Temas para entrar por interés: categorías con artículos, sin la de por
 * defecto.
 *
 * @return WP_Term[]
 */
function asp_recursos_temas(): array {
	$cats = get_categories(
		[
			'hide_empty' => true,
			'exclude'    => [ (int) get_option( 'default_category' ) ],
			'orderby'    => 'count',
			'order'      => 'DESC',
		]
	);
	return is_array( $cats ) ? $cats : [];
}

/**
 * Series con artículos, la más reciente primero.
 *
 * @return WP_Term[]
 */
function asp_recursos_series(): array {
	$series = get_terms( [ 'taxonomy' => 'serie', 'hide_empty' => true ] );
	if ( ! is_array( $series ) ) {
		return [];
	}
	$ultima = static function ( WP_Term $t ): int {
		$lista = asp_articulos_de_serie( $t->term_id );
		$ult   = end( $lista );
		return $ult ? (int) get_post_timestamp( $ult ) : 0;
	};
	usort( $series, static fn( $a, $b ) => $ultima( $b ) <=> $ultima( $a ) );
	return $series;
}

/**
 * Autores de una lista de artículos, sin repetir: "Ricardo Daglio".
 *
 * @param WP_Post[] $posts Artículos.
 * @return string
 */
function asp_autores_de( array $posts ): string {
	$nombres = [];
	foreach ( $posts as $p ) {
		$nombre = asp_autor_articulo( $p->ID )['nombre'];
		if ( $nombre ) {
			$nombres[ $nombre ] = true;
		}
	}
	return implode( ', ', array_keys( $nombres ) );
}

/**
 * La tanda más reciente de predicaciones, presentada como colección.
 *
 * Si las predicaciones están vinculadas a un evento, la colección lleva su
 * nombre. Si no, se agrupan por día: son los mensajes de un mismo
 * encuentro, pero el nombre no se adivina a partir de los títulos.
 * TODO: vincular cada predicación con su evento (campo "Evento" de la
 * predicación) para que la colección muestre el nombre de la conferencia.
 *
 * @param int $maximo Cuántas predicaciones mostrar como mucho.
 * @return array{titulo:string,evento:?WP_Post,fecha:string,total:int,items:WP_Post[]}|null
 */
function asp_predicaciones_coleccion_reciente( int $maximo = 6 ): ?array {
	$todas = asp_predicaciones( [], 40 );
	if ( empty( $todas ) ) {
		return null;
	}
	$dia   = asp_predicacion_fecha_ymd( $todas[0]->ID );
	$items = array_values( array_filter( $todas, static fn( $p ) => asp_predicacion_fecha_ymd( $p->ID ) === $dia ) );

	// Alcanza con que una del día esté vinculada para saber de qué evento son.
	$evento = null;
	foreach ( $items as $p ) {
		$evento = asp_predicacion_evento( $p->ID );
		if ( $evento ) {
			$items = asp_predicaciones_de_evento( $evento->ID );
			break;
		}
	}

	$partes = asp_fecha_partes( $dia );
	$fecha  = $partes ? sprintf( __( '%1$s de %2$d', 'asp' ), asp_nombre_mes( $partes['mes'] ), $partes['anio'] ) : '';

	return [
		'titulo' => $evento ? get_the_title( $evento ) : '',
		'evento' => $evento,
		'fecha'  => $fecha,
		'total'  => count( $items ),
		'items'  => array_slice( $items, 0, $maximo ),
	];
}
