<?php
/**
 * Panel podado para el editor de eventos, columnas útiles y orden.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

/**
 * Menú lateral: Eventos y Artículos. Nada más.
 *
 * @return void
 */
function asp_podar_menu(): void {
	if ( ! asp_es_editor_eventos() ) {
		return;
	}
	foreach ( [ 'index.php', 'upload.php', 'edit-comments.php', 'tools.php', 'themes.php', 'plugins.php', 'users.php', 'options-general.php', 'edit.php?post_type=iniciativa', 'edit.php?post_type=persona', 'edit.php?post_type=aliado' ] as $menu ) {
		remove_menu_page( $menu );
	}
	remove_submenu_page( 'edit.php', 'edit-tags.php?taxonomy=post_tag' );
}
add_action( 'admin_menu', 'asp_podar_menu', 999 );

/**
 * En el panel las entradas se llaman como en el sitio: Artículos.
 *
 * @param object $labels Etiquetas del tipo "post".
 * @return object
 */
function asp_etiquetas_articulos( object $labels ): object {
	$nuevas = [
		'name'               => __( 'Artículos', 'asp' ),
		'singular_name'      => __( 'Artículo', 'asp' ),
		'menu_name'          => __( 'Artículos', 'asp' ),
		'name_admin_bar'     => __( 'Artículo', 'asp' ),
		'all_items'          => __( 'Todos los artículos', 'asp' ),
		'add_new'            => __( 'Escribir artículo', 'asp' ),
		'add_new_item'       => __( 'Escribir un artículo', 'asp' ),
		'edit_item'          => __( 'Editar artículo', 'asp' ),
		'new_item'           => __( 'Artículo nuevo', 'asp' ),
		'view_item'          => __( 'Ver artículo', 'asp' ),
		'search_items'       => __( 'Buscar artículos', 'asp' ),
		'not_found'          => __( 'No hay artículos.', 'asp' ),
		'not_found_in_trash' => __( 'No hay artículos en la papelera.', 'asp' ),
	];
	foreach ( $nuevas as $clave => $texto ) {
		$labels->$clave = $texto;
	}
	return $labels;
}
add_filter( 'post_type_labels_post', 'asp_etiquetas_articulos' );

/**
 * Barra superior sin "Comentarios" ni "Nuevo" genérico para ese rol.
 *
 * @param WP_Admin_Bar $barra Barra.
 * @return void
 */
function asp_podar_barra( WP_Admin_Bar $barra ): void {
	if ( ! asp_es_editor_eventos() ) {
		return;
	}
	foreach ( [ 'comments', 'new-content', 'wp-logo', 'updates', 'customize' ] as $nodo ) {
		$barra->remove_node( $nodo );
	}
}
add_action( 'admin_bar_menu', 'asp_podar_barra', 999 );

/**
 * Al entrar, el editor de eventos aterriza en Eventos.
 *
 * @param string  $destino URL.
 * @param string  $pedido  Redirect pedido.
 * @param WP_User $user    Usuario.
 * @return string
 */
function asp_login_a_eventos( string $destino, string $pedido, $user ): string {
	if ( $user instanceof WP_User && in_array( 'editor_eventos', (array) $user->roles, true ) ) {
		return admin_url( 'edit.php?post_type=evento' );
	}
	return $destino;
}
add_filter( 'login_redirect', 'asp_login_a_eventos', 10, 3 );

/**
 * Sin escritorio para ese rol: cualquier visita a /wp-admin/ va a Eventos.
 *
 * @return void
 */
function asp_sin_escritorio(): void {
	global $pagenow;
	if ( 'index.php' === $pagenow && asp_es_editor_eventos() ) {
		wp_safe_redirect( admin_url( 'edit.php?post_type=evento' ) );
		exit;
	}
}
add_action( 'admin_init', 'asp_sin_escritorio' );

/* ------------------------------------------------------------------------
   Columnas del listado de eventos
   --------------------------------------------------------------------- */

/**
 * Columnas: título, fecha del evento, país, estado, destacado.
 *
 * @param array<string,string> $columnas Columnas.
 * @return array<string,string>
 */
function asp_columnas_evento( array $columnas ): array {
	return [
		'cb'            => $columnas['cb'] ?? '',
		'title'         => __( 'Evento', 'asp' ),
		'asp_fecha'     => __( 'Fecha', 'asp' ),
		'taxonomy-pais' => __( 'País', 'asp' ),
		'asp_estado'    => __( 'Estado', 'asp' ),
		'asp_destacado' => __( 'Inicio', 'asp' ),
	];
}
add_filter( 'manage_evento_posts_columns', 'asp_columnas_evento' );

/**
 * Contenido de las columnas.
 *
 * @param string $columna Columna.
 * @param int    $post_id ID.
 * @return void
 */
function asp_columna_evento( string $columna, int $post_id ): void {
	switch ( $columna ) {
		case 'asp_fecha':
			echo esc_html( asp_evento_fecha_texto( $post_id ) ?: '—' );
			break;
		case 'asp_estado':
			$estado = asp_evento_estado( $post_id );
			printf( '<span class="asp-columna-estado asp-columna-estado--%1$s">%2$s</span>', esc_attr( $estado ), esc_html( asp_evento_estado_etiqueta( $estado, $post_id ) ) );
			break;
		case 'asp_destacado':
			echo get_post_meta( $post_id, 'evento_destacado', true ) ? esc_html__( 'Destacado', 'asp' ) : '';
			break;
	}
}
add_action( 'manage_evento_posts_custom_column', 'asp_columna_evento', 10, 2 );

/**
 * La columna Fecha se ordena.
 *
 * @param array<string,string> $columnas Ordenables.
 * @return array<string,string>
 */
function asp_columnas_ordenables_evento( array $columnas ): array {
	$columnas['asp_fecha'] = 'asp_fecha';
	return $columnas;
}
add_filter( 'manage_edit-evento_sortable_columns', 'asp_columnas_ordenables_evento' );

/**
 * En el panel los eventos se listan del más próximo al más viejo, y los que
 * todavía no tienen fecha van arriba de todo. Antes se ordenaba con
 * meta_key, que excluye los posts sin ese meta: las copias recién hechas
 * con "Duplicar" (que salen sin fechas) no aparecían en el listado.
 *
 * @param WP_Query $query Consulta.
 * @return void
 */
function asp_orden_admin_eventos( WP_Query $query ): void {
	if ( ! is_admin() || ! $query->is_main_query() || 'evento' !== $query->get( 'post_type' ) ) {
		return;
	}
	$orderby = $query->get( 'orderby' );
	if ( '' === $orderby || 'asp_fecha' === $orderby ) {
		$order = strtoupper( (string) $query->get( 'order' ) );
		$query->set( 'asp_orden_fecha', ( '' === $orderby || 'ASC' !== $order ) ? 'DESC' : 'ASC' );
	}
}
add_action( 'pre_get_posts', 'asp_orden_admin_eventos' );

/**
 * El orden por fecha, con un LEFT JOIN que no deja afuera a nadie.
 *
 * @param array<string,string> $clausulas Cláusulas SQL.
 * @param WP_Query             $query     Consulta.
 * @return array<string,string>
 */
function asp_orden_admin_eventos_sql( array $clausulas, WP_Query $query ): array {
	$order = $query->get( 'asp_orden_fecha' );
	if ( ! in_array( $order, [ 'ASC', 'DESC' ], true ) ) {
		return $clausulas;
	}
	global $wpdb;
	$clausulas['join']   .= $wpdb->prepare( " LEFT JOIN {$wpdb->postmeta} AS asp_fi ON ( asp_fi.post_id = {$wpdb->posts}.ID AND asp_fi.meta_key = %s )", 'evento_fecha_inicio' );
	$clausulas['orderby'] = "( asp_fi.meta_value IS NULL OR asp_fi.meta_value = '' ) DESC, asp_fi.meta_value {$order}, {$wpdb->posts}.post_date DESC";
	return $clausulas;
}
add_filter( 'posts_clauses', 'asp_orden_admin_eventos_sql', 10, 2 );

/**
 * Sin edición rápida ni masiva en Eventos: esas vías no traen el formulario
 * con los datos obligatorios y dejaban publicar un evento sin fechas.
 * Además mostraban "Slug" y "Contraseña" a quien carga por primera vez.
 *
 * @param array<string,string> $acciones Acciones de la fila.
 * @param WP_Post              $post     Post.
 * @return array<string,string>
 */
function asp_sin_edicion_rapida_evento( array $acciones, WP_Post $post ): array {
	if ( 'evento' === $post->post_type ) {
		unset( $acciones['inline hide-if-no-js'] );
	}
	return $acciones;
}
add_filter( 'post_row_actions', 'asp_sin_edicion_rapida_evento', 10, 2 );

/**
 * Acciones masivas de Eventos: solo mover a la papelera.
 *
 * @param array<string,string> $acciones Acciones.
 * @return array<string,string>
 */
function asp_sin_edicion_masiva_evento( array $acciones ): array {
	unset( $acciones['edit'] );
	return $acciones;
}
add_filter( 'bulk_actions-edit-evento', 'asp_sin_edicion_masiva_evento' );

/**
 * Columna "Dónde aparece" en Personas.
 *
 * @param array<string,string> $columnas Columnas.
 * @return array<string,string>
 */
function asp_columnas_persona( array $columnas ): array {
	$columnas['asp_roles'] = __( 'Dónde aparece', 'asp' );
	unset( $columnas['date'] );
	return $columnas;
}
add_filter( 'manage_persona_posts_columns', 'asp_columnas_persona' );

function asp_columna_persona( string $columna, int $post_id ): void {
	if ( 'asp_roles' !== $columna ) {
		return;
	}
	$roles  = (array) get_post_meta( $post_id, 'persona_roles', false );
	$nombres = array_intersect_key( asp_roles_persona(), array_flip( array_map( 'strval', $roles ) ) );
	echo esc_html( implode( ' · ', $nombres ) );
}
add_action( 'manage_persona_posts_custom_column', 'asp_columna_persona', 10, 2 );

/**
 * Columnas de Predicaciones: formato, evento, orador.
 *
 * @param array<string,string> $columnas Columnas.
 * @return array<string,string>
 */
function asp_columnas_predicacion( array $columnas ): array {
	return [
		'cb'          => $columnas['cb'] ?? '',
		'title'       => __( 'Predicación', 'asp' ),
		'asp_formato' => __( 'Formato', 'asp' ),
		'asp_evento'  => __( 'Evento', 'asp' ),
		'asp_orador'  => __( 'Orador', 'asp' ),
		'date'        => __( 'Cargada', 'asp' ),
	];
}
add_filter( 'manage_predicacion_posts_columns', 'asp_columnas_predicacion' );

function asp_columna_predicacion( string $columna, int $post_id ): void {
	if ( 'asp_formato' === $columna ) {
		echo esc_html( asp_tipos_predicacion()[ (string) get_post_meta( $post_id, 'predicacion_tipo', true ) ] ?? '' );
	} elseif ( 'asp_evento' === $columna ) {
		$e = absint( get_post_meta( $post_id, 'predicacion_evento', true ) );
		echo $e ? esc_html( get_the_title( $e ) ) : '—';
	} elseif ( 'asp_orador' === $columna ) {
		$o = absint( get_post_meta( $post_id, 'predicacion_orador', true ) );
		echo $o ? esc_html( get_the_title( $o ) ) : '—';
	}
}
add_action( 'manage_predicacion_posts_custom_column', 'asp_columna_predicacion', 10, 2 );
