<?php
/**
 * Los cinco campos mínimos bloquean la publicación. Se valida en el
 * servidor sobre el POST actual: el meta todavía no se guardó cuando corre
 * wp_insert_post_data. Si falta algo, el evento vuelve a borrador y el
 * panel dice qué falta, por su etiqueta.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

/**
 * Errores de publicación según el POST actual.
 *
 * @param string $titulo Título que entra.
 * @return string[] Etiquetas de lo que falta.
 */
function asp_evento_errores_publicacion( string $titulo ): array {
	$faltan = [];
	if ( '' === trim( $titulo ) || __( 'Auto Draft', 'default' ) === $titulo ) {
		$faltan[] = __( 'el nombre del evento', 'asp' );
	}
	$inicio = asp_fecha_input_a_ymd( asp_post_texto( 'evento_fecha_inicio' ) );
	$fin    = asp_fecha_input_a_ymd( asp_post_texto( 'evento_fecha_fin' ) );
	if ( '' === $inicio ) {
		$faltan[] = __( 'el primer día del evento', 'asp' );
	}
	if ( '' === $fin ) {
		$faltan[] = __( 'el último día del evento', 'asp' );
	}
	if ( $inicio && $fin && $fin < $inicio ) {
		$faltan[] = __( 'un último día que no sea anterior al primero', 'asp' );
	}
	if ( '' === trim( asp_post_texto( 'evento_ciudad' ) ) ) {
		$faltan[] = __( 'la ciudad', 'asp' );
	}
	if ( '' === sanitize_key( asp_post_texto( 'evento_pais' ) ) ) {
		$faltan[] = __( 'el país', 'asp' );
	}
	$estado = asp_sanitizar_estado_inscripcion( asp_post_texto( 'evento_estado_inscripcion' ) );
	if ( 'abierta' === $estado && '' === esc_url_raw( asp_post_texto( 'evento_url_registro' ) ) ) {
		$faltan[] = __( 'el link de inscripción (la inscripción está marcada como abierta)', 'asp' );
	}
	return $faltan;
}

/**
 * Fuerza borrador si faltan datos.
 *
 * @param array<string,mixed> $data    Datos a insertar.
 * @param array<string,mixed> $postarr Datos crudos.
 * @return array<string,mixed>
 */
function asp_validar_evento_al_publicar( array $data, array $postarr ): array {
	if ( 'evento' !== ( $data['post_type'] ?? '' ) ) {
		return $data;
	}
	if ( ! in_array( $data['post_status'] ?? '', [ 'publish', 'future' ], true ) ) {
		return $data;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return $data;
	}
	$nonce = $_POST['asp_evento_nonce'] ?? ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
	if ( ! is_string( $nonce ) || ! wp_verify_nonce( $nonce, 'asp_guardar_evento' ) ) {
		/* Publicación desde otro lado (script, acción rápida): no hay POST que validar. */
		return $data;
	}

	$faltan = asp_evento_errores_publicacion( (string) ( $data['post_title'] ?? '' ) );
	if ( empty( $faltan ) ) {
		return $data;
	}

	$data['post_status'] = 'draft';
	set_transient( 'asp_errores_evento_' . get_current_user_id(), $faltan, 60 );
	add_filter(
		'redirect_post_location',
		static function ( string $location ): string {
			return add_query_arg( 'asp_error', '1', $location );
		}
	);
	return $data;
}
add_filter( 'wp_insert_post_data', 'asp_validar_evento_al_publicar', 10, 2 );

/**
 * Aviso en el panel con lo que falta.
 *
 * @return void
 */
function asp_aviso_errores_evento(): void {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( empty( $_GET['asp_error'] ) ) {
		return;
	}
	$faltan = get_transient( 'asp_errores_evento_' . get_current_user_id() );
	delete_transient( 'asp_errores_evento_' . get_current_user_id() );
	if ( ! is_array( $faltan ) || empty( $faltan ) ) {
		return;
	}
	?>
	<div class="notice notice-error asp-aviso">
		<p><strong><?php esc_html_e( 'No se publicó. El evento quedó guardado como borrador.', 'asp' ); ?></strong></p>
		<p><?php esc_html_e( 'Para publicarlo falta:', 'asp' ); ?></p>
		<ul>
			<?php foreach ( $faltan as $f ) : ?>
				<li><?php echo esc_html( $f ); ?></li>
			<?php endforeach; ?>
		</ul>
	</div>
	<?php
}
add_action( 'admin_notices', 'asp_aviso_errores_evento' );

/**
 * Silencia el "Entrada publicada" cuando en realidad quedó en borrador.
 *
 * @param array<int|string, string> $mensajes Mensajes por tipo.
 * @return array<int|string, string>
 */
function asp_mensajes_evento( array $mensajes ): array {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! empty( $_GET['asp_error'] ) && isset( $mensajes['post'] ) ) {
		$mensajes['post'][6] = '';
		$mensajes['post'][1] = '';
	}
	return $mensajes;
}
add_filter( 'post_updated_messages', 'asp_mensajes_evento' );
