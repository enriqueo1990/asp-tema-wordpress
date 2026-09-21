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
			printf( '<span class="asp-columna-estado asp-columna-estado--%1$s">%2$s</span>', esc_attr( $estado ), esc_html( asp_evento_estado_etiqueta( $estado ) ) );
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
 * En el panel los eventos se listan del más próximo al más viejo.
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
		$query->set( 'meta_key', 'evento_fecha_inicio' );
		$query->set( 'orderby', 'meta_value_num' );
		if ( '' === $orderby ) {
			$query->set( 'order', 'DESC' );
		}
	}
}
add_action( 'pre_get_posts', 'asp_orden_admin_eventos' );

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
