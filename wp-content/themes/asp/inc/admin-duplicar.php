<?php
/**
 * "Duplicar edición anterior": clona un evento entero como borrador y
 * limpia solo fechas, link de inscripción y destacado. Es el flujo
 * principal de carga: los eventos se repiten año a año.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

/**
 * URL de la acción para un evento.
 *
 * @param int $post_id ID.
 * @return string
 */
function asp_url_duplicar( int $post_id ): string {
	return wp_nonce_url(
		admin_url( 'admin-post.php?action=asp_duplicar_evento&post=' . $post_id ),
		'asp_duplicar_' . $post_id
	);
}

/**
 * Enlace en la fila del listado.
 *
 * @param string[] $acciones Acciones.
 * @param WP_Post  $post     Post.
 * @return string[]
 */
function asp_accion_duplicar( array $acciones, WP_Post $post ): array {
	if ( 'evento' === $post->post_type && current_user_can( 'edit_post', $post->ID ) ) {
		$acciones['asp_duplicar'] = sprintf(
			'<a href="%1$s" aria-label="%3$s">%2$s</a>',
			esc_url( asp_url_duplicar( $post->ID ) ),
			esc_html__( 'Duplicar edición anterior', 'asp' ),
			/* translators: %s: título del evento */
			esc_attr( sprintf( __( 'Crear la próxima edición de «%s»', 'asp' ), $post->post_title ) )
		);
	}
	return $acciones;
}
add_filter( 'post_row_actions', 'asp_accion_duplicar', 10, 2 );

/**
 * Botón en la ficha de edición, junto a Publicar.
 *
 * @param WP_Post $post Post.
 * @return void
 */
function asp_boton_duplicar( WP_Post $post ): void {
	if ( 'evento' !== $post->post_type || 'auto-draft' === $post->post_status ) {
		return;
	}
	printf(
		'<div class="misc-pub-section"><a class="button" href="%1$s">%2$s</a><p class="description">%3$s</p></div>',
		esc_url( asp_url_duplicar( $post->ID ) ),
		esc_html__( 'Duplicar como nueva edición', 'asp' ),
		esc_html__( 'Copia todo menos las fechas y el link de inscripción.', 'asp' )
	);
}
add_action( 'post_submitbox_misc_actions', 'asp_boton_duplicar' );

/**
 * Handler.
 *
 * @return void
 */
function asp_duplicar_evento(): void {
	$origen_id = absint( $_GET['post'] ?? 0 ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	check_admin_referer( 'asp_duplicar_' . $origen_id );

	$origen = get_post( $origen_id );
	if ( ! $origen || 'evento' !== $origen->post_type || ! current_user_can( 'edit_post', $origen_id ) || ! current_user_can( 'edit_eventos' ) ) {
		wp_die( esc_html__( 'No se puede duplicar ese evento.', 'asp' ) );
	}

	$nuevo_id = wp_insert_post(
		[
			'post_type'   => 'evento',
			'post_status' => 'draft',
			/* translators: %s: título del evento original */
			'post_title'  => sprintf( __( '%s (nueva edición)', 'asp' ), $origen->post_title ),
			'post_author' => get_current_user_id(),
		],
		true
	);
	if ( is_wp_error( $nuevo_id ) ) {
		wp_die( esc_html( $nuevo_id->get_error_message() ) );
	}

	$no_copiar = [ 'evento_fecha_inicio', 'evento_fecha_fin', 'evento_url_registro', 'evento_destacado', 'evento_galeria', 'evento_videos' ];
	foreach ( get_post_meta( $origen_id ) as $clave => $valores ) {
		if ( str_starts_with( $clave, '_' ) || in_array( $clave, $no_copiar, true ) ) {
			continue;
		}
		foreach ( $valores as $valor ) {
			add_post_meta( $nuevo_id, $clave, maybe_unserialize( $valor ) );
		}
	}
	update_post_meta( $nuevo_id, 'evento_estado_inscripcion', 'reserva' );

	$paises = wp_get_object_terms( $origen_id, 'pais', [ 'fields' => 'slugs' ] );
	if ( is_array( $paises ) ) {
		wp_set_object_terms( $nuevo_id, $paises, 'pais' );
	}

	wp_safe_redirect( add_query_arg( [ 'post' => $nuevo_id, 'action' => 'edit', 'asp_duplicado' => '1' ], admin_url( 'post.php' ) ) );
	exit;
}
add_action( 'admin_post_asp_duplicar_evento', 'asp_duplicar_evento' );

/**
 * Aviso en la copia recién creada.
 *
 * @return void
 */
function asp_aviso_duplicado(): void {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( empty( $_GET['asp_duplicado'] ) ) {
		return;
	}
	?>
	<div class="notice notice-info">
		<p><strong><?php esc_html_e( 'Esta es una copia, guardada como borrador.', 'asp' ); ?></strong>
		<?php esc_html_e( 'Cambiá el nombre si corresponde, cargá las fechas nuevas, revisá la sede y, cuando lo tengas, el link de inscripción. Después publicá.', 'asp' ); ?></p>
	</div>
	<?php
}
add_action( 'admin_notices', 'asp_aviso_duplicado' );
