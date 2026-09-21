<?php
/**
 * Taxonomías: país (eventos) y serie (artículos).
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registra las taxonomías.
 *
 * @return void
 */
function asp_registrar_taxonomias(): void {
	register_taxonomy(
		'pais',
		[ 'evento' ],
		[
			'labels'            => [
				'name'          => __( 'Países', 'asp' ),
				'singular_name' => __( 'País', 'asp' ),
				'menu_name'     => __( 'Países', 'asp' ),
				'all_items'     => __( 'Todos los países', 'asp' ),
				'edit_item'     => __( 'Editar país', 'asp' ),
				'add_new_item'  => __( 'Agregar país', 'asp' ),
			],
			'public'            => true,
			'hierarchical'      => false,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_rest'      => false,
			/* El radio se dibuja dentro del meta box obligatorio del evento (sesión 3a). */
			'meta_box_cb'       => false,
			'rewrite'           => [ 'slug' => 'pais', 'with_front' => false ],
		]
	);

	register_taxonomy(
		'serie',
		[ 'post' ],
		[
			'labels'            => [
				'name'          => __( 'Series', 'asp' ),
				'singular_name' => __( 'Serie', 'asp' ),
				'menu_name'     => __( 'Series', 'asp' ),
				'all_items'     => __( 'Todas las series', 'asp' ),
				'edit_item'     => __( 'Editar serie', 'asp' ),
				'add_new_item'  => __( 'Agregar serie', 'asp' ),
			],
			'description'       => __( 'Artículos que van juntos y en orden, como "El pastor frente a los falsos maestros".', 'asp' ),
			'public'            => true,
			'hierarchical'      => false,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => [ 'slug' => 'serie', 'with_front' => false ],
		]
	);
}
add_action( 'init', 'asp_registrar_taxonomias' );

/**
 * Los dos países existen siempre. Se crean una sola vez.
 *
 * @return void
 */
function asp_asegurar_paises(): void {
	if ( get_option( 'asp_paises_creados' ) === '1' ) {
		return;
	}
	$paises = [
		'argentina'      => __( 'Argentina', 'asp' ),
		'estados-unidos' => __( 'Estados Unidos', 'asp' ),
	];
	foreach ( $paises as $slug => $nombre ) {
		if ( ! term_exists( $slug, 'pais' ) ) {
			wp_insert_term( $nombre, 'pais', [ 'slug' => $slug ] );
		}
	}
	update_option( 'asp_paises_creados', '1' );
}
add_action( 'init', 'asp_asegurar_paises', 20 );

/**
 * Permalinks se vacían solos al activar el tema.
 *
 * @return void
 */
function asp_flush_al_activar(): void {
	asp_registrar_cpt();
	asp_registrar_taxonomias();
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'asp_flush_al_activar' );
