<?php
/**
 * Búsqueda del sitio: todos los resultados en una página, agrupados por
 * tipo. El sitio es chico y antes las personas y las iniciativas salían
 * como «Artículo», mezcladas con eventos y predicaciones.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

/**
 * La búsqueda no se pagina: los grupos van completos.
 *
 * @param WP_Query $query Consulta.
 * @return void
 */
function asp_busqueda_sin_paginar( WP_Query $query ): void {
	if ( ! is_admin() && $query->is_main_query() && $query->is_search() ) {
		$query->set( 'posts_per_page', 100 );
		$query->set( 'no_found_rows', true );
	}
}
add_action( 'pre_get_posts', 'asp_busqueda_sin_paginar' );

/**
 * ¿Hay algo escrito para buscar?
 *
 * @return bool
 */
function asp_busqueda_con_termino(): bool {
	return '' !== trim( get_search_query( false ) );
}

/**
 * Resultados de la consulta principal agrupados por tipo, en el orden en
 * que la gente los busca.
 *
 * @return array<string, array{nombre:string, posts:WP_Post[]}>
 */
function asp_busqueda_grupos(): array {
	global $wp_query;
	$orden  = [ 'evento', 'predicacion', 'post', 'persona', 'iniciativa', 'page' ];
	$grupos = [];
	foreach ( (array) $wp_query->posts as $post ) {
		if ( ! $post instanceof WP_Post ) {
			continue;
		}
		$tipo = $post->post_type;
		if ( ! isset( $grupos[ $tipo ] ) ) {
			$objeto          = get_post_type_object( $tipo );
			$grupos[ $tipo ] = [
				'nombre' => 'post' === $tipo ? __( 'Artículos', 'asp' ) : ( $objeto ? (string) $objeto->labels->name : $tipo ),
				'posts'  => [],
			];
		}
		$grupos[ $tipo ]['posts'][] = $post;
	}
	uksort(
		$grupos,
		static function ( string $a, string $b ) use ( $orden ): int {
			$ia = array_search( $a, $orden, true );
			$ib = array_search( $b, $orden, true );
			return ( false === $ia ? 99 : $ia ) <=> ( false === $ib ? 99 : $ib );
		}
	);
	return $grupos;
}

/**
 * Bajada de una fila genérica de resultado (persona, iniciativa, página).
 *
 * @param WP_Post $post Resultado.
 * @return string
 */
function asp_busqueda_bajada( WP_Post $post ): string {
	switch ( $post->post_type ) {
		case 'persona':
			return asp_persona_cargo_iglesia( $post->ID );
		case 'iniciativa':
			return (string) get_post_meta( $post->ID, 'iniciativa_bajada', true );
		default:
			return has_excerpt( $post ) ? wp_strip_all_tags( $post->post_excerpt ) : wp_trim_words( wp_strip_all_tags( strip_shortcodes( $post->post_content ) ), 22, '…' );
	}
}
