<?php
/**
 * Tipos de contenido: evento, iniciativa, persona, aliado.
 *
 * Etiquetas en castellano llano. Los cuatro usan el editor clásico:
 * es la única forma de que el bloque obligatorio del evento quede arriba,
 * junto al título, con el país adentro y no en la barra lateral.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registra los cuatro CPT.
 *
 * @return void
 */
function asp_registrar_cpt(): void {
	register_post_type(
		'evento',
		[
			'labels'              => [
				'name'               => __( 'Eventos', 'asp' ),
				'singular_name'      => __( 'Evento', 'asp' ),
				'add_new'            => __( 'Cargar evento', 'asp' ),
				'add_new_item'       => __( 'Cargar un evento nuevo', 'asp' ),
				'edit_item'          => __( 'Editar evento', 'asp' ),
				'new_item'           => __( 'Evento nuevo', 'asp' ),
				'view_item'          => __( 'Ver evento', 'asp' ),
				'search_items'       => __( 'Buscar eventos', 'asp' ),
				'not_found'          => __( 'Todavía no hay eventos cargados.', 'asp' ),
				'not_found_in_trash' => __( 'No hay eventos en la papelera.', 'asp' ),
				'all_items'          => __( 'Todos los eventos', 'asp' ),
				'menu_name'          => __( 'Eventos', 'asp' ),
			],
			'description'         => __( 'Conferencias y talleres del ministerio.', 'asp' ),
			'public'              => true,
			'show_in_rest'        => false,
			'menu_position'       => 5,
			'menu_icon'           => 'dashicons-calendar-alt',
			'capability_type'     => [ 'evento', 'eventos' ],
			'map_meta_cap'        => true,
			'has_archive'         => 'eventos',
			'rewrite'             => [ 'slug' => 'eventos', 'with_front' => false ],
			'supports'            => [ 'title', 'revisions' ],
			'taxonomies'          => [ 'pais' ],
		]
	);

	register_post_type(
		'iniciativa',
		[
			'labels'        => [
				'name'          => __( 'Iniciativas', 'asp' ),
				'singular_name' => __( 'Iniciativa', 'asp' ),
				'add_new'       => __( 'Agregar iniciativa', 'asp' ),
				'add_new_item'  => __( 'Agregar una iniciativa', 'asp' ),
				'edit_item'     => __( 'Editar iniciativa', 'asp' ),
				'not_found'     => __( 'Todavía no hay iniciativas cargadas.', 'asp' ),
				'menu_name'     => __( 'Iniciativas', 'asp' ),
			],
			'public'        => true,
			'show_in_rest'  => false,
			'menu_position' => 6,
			'menu_icon'     => 'dashicons-groups',
			'capability_type' => [ 'iniciativa', 'iniciativas' ],
			'map_meta_cap'  => true,
			'has_archive'   => 'iniciativas',
			'rewrite'       => [ 'slug' => 'iniciativas', 'with_front' => false ],
			'supports'      => [ 'title', 'revisions', 'page-attributes' ],
		]
	);

	register_post_type(
		'persona',
		[
			'labels'        => [
				'name'          => __( 'Personas', 'asp' ),
				'singular_name' => __( 'Persona', 'asp' ),
				'add_new'       => __( 'Agregar persona', 'asp' ),
				'add_new_item'  => __( 'Agregar una persona', 'asp' ),
				'edit_item'     => __( 'Editar persona', 'asp' ),
				'not_found'     => __( 'Todavía no hay personas cargadas.', 'asp' ),
				'menu_name'     => __( 'Personas', 'asp' ),
			],
			'description'   => __( 'Consejo pastoral, oradores y autores en una sola ficha.', 'asp' ),
			'public'        => true,
			'show_in_rest'  => false,
			'menu_position' => 7,
			'menu_icon'     => 'dashicons-id-alt',
			'capability_type' => [ 'persona', 'personas' ],
			'map_meta_cap'  => true,
			'has_archive'   => false,
			'rewrite'       => [ 'slug' => 'personas', 'with_front' => false ],
			'supports'      => [ 'title', 'revisions', 'page-attributes' ],
		]
	);

	register_post_type(
		'predicacion',
		[
			'labels'          => [
				'name'          => __( 'Predicaciones', 'asp' ),
				'singular_name' => __( 'Predicación', 'asp' ),
				'add_new'       => __( 'Cargar predicación', 'asp' ),
				'add_new_item'  => __( 'Cargar una predicación', 'asp' ),
				'edit_item'     => __( 'Editar predicación', 'asp' ),
				'not_found'     => __( 'Todavía no hay predicaciones cargadas.', 'asp' ),
				'menu_name'     => __( 'Predicaciones', 'asp' ),
			],
			'description'     => __( 'Sesiones y predicaciones de las conferencias: video, audio o texto.', 'asp' ),
			'public'          => true,
			'show_in_rest'    => false,
			'menu_position'   => 6,
			'menu_icon'       => 'dashicons-microphone',
			'capability_type' => [ 'predicacion', 'predicaciones' ],
			'map_meta_cap'    => true,
			'has_archive'     => 'recursos/predicaciones',
			'rewrite'         => [ 'slug' => 'recursos/predicaciones', 'with_front' => false ],
			'supports'        => [ 'title', 'editor', 'revisions' ],
		]
	);

	register_post_type(
		'aliado',
		[
			'labels'              => [
				'name'          => __( 'Aliados', 'asp' ),
				'singular_name' => __( 'Aliado', 'asp' ),
				'add_new'       => __( 'Agregar aliado', 'asp' ),
				'add_new_item'  => __( 'Agregar un aliado', 'asp' ),
				'edit_item'     => __( 'Editar aliado', 'asp' ),
				'not_found'     => __( 'Todavía no hay aliados cargados.', 'asp' ),
				'menu_name'     => __( 'Aliados', 'asp' ),
			],
			'description'         => __( 'Ministerios e iglesias que acompañan los eventos. No tienen página propia.', 'asp' ),
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'show_in_rest'        => false,
			'menu_position'       => 8,
			'menu_icon'           => 'dashicons-admin-links',
			'capability_type'     => [ 'aliado', 'aliados' ],
			'map_meta_cap'        => true,
			'has_archive'         => false,
			'rewrite'             => false,
			'supports'            => [ 'title' ],
		]
	);
}
add_action( 'init', 'asp_registrar_cpt' );

/**
 * Editor clásico para los cuatro CPT.
 *
 * @param bool   $usar_bloques Valor actual.
 * @param string $post_type    Tipo de contenido.
 * @return bool
 */
function asp_editor_clasico( bool $usar_bloques, string $post_type ): bool {
	if ( in_array( $post_type, [ 'evento', 'iniciativa', 'persona', 'aliado', 'predicacion' ], true ) ) {
		return false;
	}
	return $usar_bloques;
}
add_filter( 'use_block_editor_for_post_type', 'asp_editor_clasico', 10, 2 );
