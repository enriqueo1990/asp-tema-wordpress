<?php
/**
 * Consultas de eventos, personas y artículos. Una sola fuente de datos:
 * home, listado, archivo y fichas pasan por acá.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

/**
 * Próximos: fecha de fin hoy o después, ascendente por fecha de inicio.
 *
 * @param int $cantidad -1 para todos.
 * @return WP_Query
 */
function asp_eventos_proximos( int $cantidad = -1 ): WP_Query {
	return new WP_Query(
		[
			'post_type'      => 'evento',
			'post_status'    => 'publish',
			'posts_per_page' => $cantidad,
			'meta_key'       => 'evento_fecha_inicio',
			'orderby'        => 'meta_value_num',
			'order'          => 'ASC',
			'no_found_rows'  => true,
			'meta_query'     => [
				[
					'key'     => 'evento_fecha_fin',
					'value'   => current_time( 'Ymd' ),
					'compare' => '>=',
					'type'    => 'NUMERIC',
				],
			],
		]
	);
}

/**
 * Pasados: fecha de fin anterior a hoy, descendente.
 *
 * @param int|null $anio     Filtrar por año de inicio.
 * @param int      $cantidad -1 para todos.
 * @return WP_Query
 */
function asp_eventos_pasados( ?int $anio = null, int $cantidad = -1 ): WP_Query {
	$meta_query = [
		[
			'key'     => 'evento_fecha_fin',
			'value'   => current_time( 'Ymd' ),
			'compare' => '<',
			'type'    => 'NUMERIC',
		],
	];
	if ( $anio ) {
		$meta_query[] = [
			'key'     => 'evento_fecha_inicio',
			'value'   => [ $anio . '0101', $anio . '1231' ],
			'compare' => 'BETWEEN',
			'type'    => 'NUMERIC',
		];
	}
	return new WP_Query(
		[
			'post_type'      => 'evento',
			'post_status'    => 'publish',
			'posts_per_page' => $cantidad,
			'meta_key'       => 'evento_fecha_inicio',
			'orderby'        => 'meta_value_num',
			'order'          => 'DESC',
			'no_found_rows'  => true,
			'meta_query'     => $meta_query,
		]
	);
}

/**
 * Pasados agrupados por año, años descendentes.
 *
 * @return array<int, WP_Post[]>
 */
function asp_eventos_pasados_por_anio(): array {
	$grupos = [];
	foreach ( asp_eventos_pasados()->posts as $post ) {
		$grupos[ asp_evento_anio( $post->ID ) ][] = $post;
	}
	krsort( $grupos );
	return $grupos;
}

/**
 * Evento del hero: el primer próximo marcado como destacado; si ninguno
 * lo está, el próximo por fecha. Null si no hay próximos.
 *
 * @return WP_Post|null
 */
function asp_evento_destacado(): ?WP_Post {
	$proximos = asp_eventos_proximos()->posts;
	if ( empty( $proximos ) ) {
		return null;
	}
	foreach ( $proximos as $post ) {
		if ( get_post_meta( $post->ID, 'evento_destacado', true ) ) {
			return $post;
		}
	}
	return $proximos[0];
}

/**
 * Último evento realizado.
 *
 * @return WP_Post|null
 */
function asp_ultimo_evento_realizado(): ?WP_Post {
	$pasados = asp_eventos_pasados( null, 1 )->posts;
	return $pasados[0] ?? null;
}

/**
 * Eventos de una iniciativa, próximos o pasados.
 *
 * @param int  $iniciativa_id ID de la iniciativa.
 * @param bool $proximos      true próximos, false pasados.
 * @return WP_Post[]
 */
function asp_eventos_de_iniciativa( int $iniciativa_id, bool $proximos ): array {
	$query = $proximos ? asp_eventos_proximos() : asp_eventos_pasados();
	return array_values(
		array_filter(
			$query->posts,
			static fn( WP_Post $p ) => absint( get_post_meta( $p->ID, 'evento_iniciativa', true ) ) === $iniciativa_id
		)
	);
}

/**
 * Eventos donde una persona fue oradora, del más reciente al más viejo.
 *
 * @param int $persona_id ID de la persona.
 * @return WP_Post[]
 */
function asp_eventos_de_orador( int $persona_id ): array {
	$query = new WP_Query(
		[
			'post_type'      => 'evento',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'meta_key'       => 'evento_fecha_inicio',
			'orderby'        => 'meta_value_num',
			'order'          => 'DESC',
			'no_found_rows'  => true,
			'meta_query'     => [
				[
					'key'   => 'evento_oradores',
					'value' => $persona_id,
				],
			],
		]
	);
	return $query->posts;
}

/**
 * Personas con un rol dado, ordenadas por "Orden" y luego por título.
 *
 * @param string $rol consejo|orador|autor.
 * @return WP_Post[]
 */
function asp_personas_por_rol( string $rol ): array {
	$query = new WP_Query(
		[
			'post_type'      => 'persona',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => [ 'menu_order' => 'ASC', 'title' => 'ASC' ],
			'no_found_rows'  => true,
			'meta_query'     => [
				[
					'key'   => 'persona_roles',
					'value' => $rol,
				],
			],
		]
	);
	return $query->posts;
}

/**
 * Persona vinculada a un usuario de WordPress (autor de artículos).
 *
 * @param int $user_id ID del usuario.
 * @return WP_Post|null
 */
function asp_persona_de_usuario( int $user_id ): ?WP_Post {
	if ( ! $user_id ) {
		return null;
	}
	$posts = get_posts(
		[
			'post_type'      => 'persona',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'meta_key'       => 'persona_usuario',
			'meta_value'     => $user_id,
		]
	);
	return $posts[0] ?? null;
}

/**
 * Artículos escritos por una persona (vía su usuario vinculado).
 *
 * @param int $persona_id ID de la persona.
 * @return WP_Post[]
 */
function asp_articulos_de_persona( int $persona_id ): array {
	$user_id = absint( get_post_meta( $persona_id, 'persona_usuario', true ) );
	if ( ! $user_id ) {
		return [];
	}
	return get_posts(
		[
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'author'         => $user_id,
			'posts_per_page' => -1,
		]
	);
}

/**
 * Artículos de una serie en orden de publicación (el primero es el 01).
 *
 * @param int $term_id ID del término serie.
 * @return WP_Post[]
 */
function asp_articulos_de_serie( int $term_id ): array {
	return get_posts(
		[
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'date',
			'order'          => 'ASC',
			'tax_query'      => [
				[
					'taxonomy' => 'serie',
					'field'    => 'term_id',
					'terms'    => $term_id,
				],
			],
		]
	);
}

/**
 * Serie de un artículo, si pertenece a una.
 *
 * @param int $post_id ID del artículo.
 * @return WP_Term|null
 */
function asp_serie_de_articulo( int $post_id ): ?WP_Term {
	$terminos = get_the_terms( $post_id, 'serie' );
	return ( is_array( $terminos ) && ! empty( $terminos ) ) ? $terminos[0] : null;
}
