<?php
/**
 * Los cinco campos mínimos bloquean la publicación. Desde el formulario se
 * valida el POST actual (el meta todavía no se guardó cuando corre
 * wp_insert_post_data); desde la edición rápida, la masiva o REST, lo que
 * ya está guardado. Si falta algo, el evento queda en borrador —o, si ya
 * estaba publicado, sigue publicado con sus datos anteriores— y el panel
 * dice qué falta, por su etiqueta.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

/**
 * Sufijo que "Duplicar edición anterior" le pone al título de la copia.
 * Un evento no se publica con ese sufijo: es la marca de que nadie le puso
 * el nombre de la edición nueva.
 *
 * @return string
 */
function asp_sufijo_nueva_edicion(): string {
	/* translators: %s: título del evento original */
	return trim( sprintf( __( '%s (nueva edición)', 'asp' ), '' ) );
}

/**
 * Qué le falta al título, o vacío si está bien.
 *
 * @param string $titulo Título.
 * @return string
 */
function asp_evento_error_titulo( string $titulo ): string {
	$titulo = trim( $titulo );
	if ( '' === $titulo || __( 'Auto Draft', 'default' ) === $titulo ) {
		return __( 'el nombre del evento', 'asp' );
	}
	if ( str_ends_with( $titulo, asp_sufijo_nueva_edicion() ) ) {
		return __( 'el nombre de esta edición (sacale «(nueva edición)» al título)', 'asp' );
	}
	return '';
}

/**
 * Lo que falta para publicar, a partir de los valores de los campos mínimos.
 *
 * @param array{titulo:string,inicio:string,fin:string,ciudad:string,pais:string,estado:string,url:string} $v Valores.
 * @return string[] Etiquetas de lo que falta.
 */
function asp_evento_errores( array $v ): array {
	$faltan = array_filter( [ asp_evento_error_titulo( $v['titulo'] ) ] );
	if ( '' === $v['inicio'] ) {
		$faltan[] = __( 'el primer día del evento', 'asp' );
	}
	if ( '' === $v['fin'] ) {
		$faltan[] = __( 'el último día del evento', 'asp' );
	}
	if ( $v['inicio'] && $v['fin'] && $v['fin'] < $v['inicio'] ) {
		$faltan[] = __( 'un último día que no sea anterior al primero', 'asp' );
	}
	if ( '' === trim( $v['ciudad'] ) ) {
		$faltan[] = __( 'la ciudad', 'asp' );
	}
	if ( '' === $v['pais'] ) {
		$faltan[] = __( 'el país', 'asp' );
	}
	if ( 'abierta' === $v['estado'] && '' === $v['url'] ) {
		$faltan[] = __( 'el link de inscripción (la inscripción está marcada como abierta)', 'asp' );
	}
	return $faltan;
}

/**
 * Valores de los campos mínimos según el formulario que se está enviando.
 *
 * @param string $titulo Título que entra.
 * @return array{titulo:string,inicio:string,fin:string,ciudad:string,pais:string,estado:string,url:string}
 */
function asp_evento_valores_post( string $titulo ): array {
	return [
		'titulo' => $titulo,
		'inicio' => asp_fecha_input_a_ymd( asp_post_texto( 'evento_fecha_inicio' ) ),
		'fin'    => asp_fecha_input_a_ymd( asp_post_texto( 'evento_fecha_fin' ) ),
		'ciudad' => asp_post_texto( 'evento_ciudad' ),
		'pais'   => sanitize_key( asp_post_texto( 'evento_pais' ) ),
		'estado' => asp_sanitizar_estado_inscripcion( asp_post_texto( 'evento_estado_inscripcion' ) ),
		'url'    => esc_url_raw( asp_post_texto( 'evento_url_registro' ) ),
	];
}

/**
 * Valores de los campos mínimos ya guardados en un evento.
 *
 * @param int    $post_id ID.
 * @param string $titulo  Título que entra.
 * @return array{titulo:string,inicio:string,fin:string,ciudad:string,pais:string,estado:string,url:string}
 */
function asp_evento_valores_guardados( int $post_id, string $titulo ): array {
	$paises = $post_id ? wp_get_object_terms( $post_id, 'pais', [ 'fields' => 'slugs' ] ) : [];
	return [
		'titulo' => $titulo,
		'inicio' => (string) get_post_meta( $post_id, 'evento_fecha_inicio', true ),
		'fin'    => (string) get_post_meta( $post_id, 'evento_fecha_fin', true ),
		'ciudad' => (string) get_post_meta( $post_id, 'evento_ciudad', true ),
		'pais'   => ( is_array( $paises ) && $paises ) ? (string) $paises[0] : '',
		'estado' => asp_sanitizar_estado_inscripcion( (string) get_post_meta( $post_id, 'evento_estado_inscripcion', true ) ),
		'url'    => (string) get_post_meta( $post_id, 'evento_url_registro', true ),
	];
}

/**
 * Compatibilidad: errores según el POST actual.
 *
 * @param string $titulo Título que entra.
 * @return string[]
 */
function asp_evento_errores_publicacion( string $titulo ): array {
	return asp_evento_errores( asp_evento_valores_post( $titulo ) );
}

/**
 * Registro, dentro del pedido, de eventos publicados cuyo formulario llegó
 * incompleto: se guarda lo opcional pero no se tocan los datos mínimos.
 *
 * @param int       $post_id ID.
 * @param bool|null $marcar  true para marcarlo; null para consultar.
 * @return bool
 */
function asp_evento_minimos_rechazados( int $post_id, ?bool $marcar = null ): bool {
	static $rechazados = [];
	if ( null !== $marcar ) {
		$rechazados[ $post_id ] = $marcar;
	}
	return ! empty( $rechazados[ $post_id ] );
}

/**
 * ¿El pedido es una edición rápida, masiva o por REST? Esas vías no traen
 * el formulario del evento: se valida lo que ya está guardado.
 *
 * @return bool
 */
function asp_evento_edicion_sin_formulario(): bool {
	// phpcs:disable WordPress.Security.NonceVerification
	$accion = isset( $_POST['action'] ) ? sanitize_key( (string) $_POST['action'] ) : '';
	$masiva = ! empty( $_REQUEST['bulk_edit'] );
	// phpcs:enable
	return ( wp_doing_ajax() && 'inline-save' === $accion ) || $masiva || ( defined( 'REST_REQUEST' ) && REST_REQUEST );
}

/**
 * Valida los datos mínimos al publicar.
 *
 * - Desde el formulario del evento: se valida lo que se está enviando.
 * - Desde la edición rápida, la masiva o REST: se valida lo que ya está
 *   guardado (antes esas vías publicaban sin fechas ni ciudad).
 * - Scripts de tools/: no se validan; cargan el meta después de crear el post.
 *
 * Si falta algo y el evento todavía no estaba publicado, queda en borrador.
 * Si ya estaba publicado, sigue publicado: se conserva el título anterior y
 * los datos mínimos anteriores, y el aviso dice qué no se guardó. Bajarlo a
 * borrador lo sacaba del sitio y rompía el link ya compartido.
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

	$post_id = absint( $postarr['ID'] ?? 0 );
	$titulo  = (string) ( $data['post_title'] ?? '' );
	$nonce   = $_POST['asp_evento_nonce'] ?? ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
	$con_formulario = is_string( $nonce ) && wp_verify_nonce( $nonce, 'asp_guardar_evento' );

	if ( $con_formulario ) {
		$valores = asp_evento_valores_post( $titulo );
	} elseif ( $post_id && asp_evento_edicion_sin_formulario() ) {
		$valores = asp_evento_valores_guardados( $post_id, $titulo );
	} else {
		return $data;
	}

	$faltan = asp_evento_errores( $valores );
	if ( empty( $faltan ) ) {
		return $data;
	}

	$ya_publicado = $post_id && 'publish' === get_post_status( $post_id );
	if ( $ya_publicado ) {
		$anterior = get_post( $post_id );
		if ( $anterior && asp_evento_error_titulo( $titulo ) ) {
			$data['post_title'] = $anterior->post_title;
			$data['post_name']  = $anterior->post_name;
		}
		if ( $con_formulario ) {
			asp_evento_minimos_rechazados( $post_id, true );
		}
		$modo = 'publicado';
	} else {
		$data['post_status'] = 'draft';
		$modo                = 'borrador';
	}

	set_transient( 'asp_errores_evento_' . get_current_user_id(), [ 'modo' => $modo, 'faltan' => $faltan ], 60 );
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
	$error = get_transient( 'asp_errores_evento_' . get_current_user_id() );
	delete_transient( 'asp_errores_evento_' . get_current_user_id() );
	if ( ! is_array( $error ) ) {
		return;
	}
	/* Formato anterior: solo la lista. */
	$modo   = (string) ( $error['modo'] ?? 'borrador' );
	$faltan = isset( $error['faltan'] ) && is_array( $error['faltan'] ) ? $error['faltan'] : $error;
	if ( empty( $faltan ) ) {
		return;
	}
	?>
	<div class="notice notice-error asp-aviso">
		<?php if ( 'publicado' === $modo ) : ?>
			<p><strong><?php esc_html_e( 'El evento sigue publicado, pero el nombre, las fechas, la ciudad, el país y la inscripción quedaron como estaban.', 'asp' ); ?></strong></p>
			<p><?php esc_html_e( 'Lo demás se guardó. Para cambiar esos datos falta:', 'asp' ); ?></p>
		<?php else : ?>
			<p><strong><?php esc_html_e( 'No se publicó. El evento quedó guardado como borrador.', 'asp' ); ?></strong></p>
			<p><?php esc_html_e( 'Para publicarlo falta:', 'asp' ); ?></p>
		<?php endif; ?>
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

/**
 * Aviso de fecha provisoria. Algunos eventos históricos se publicaron con
 * una fecha supuesta para que sus predicaciones se vieran en la ficha (la
 * de subida a YouTube de la primera sesión). _asp_fecha_provisoria guarda
 * esa fecha; cuando alguien carga la real, deja de coincidir y el aviso se
 * va solo.
 *
 * @return void
 */
function asp_aviso_fecha_provisoria(): void {
	$pantalla = get_current_screen();
	if ( ! $pantalla || 'post' !== $pantalla->base || 'evento' !== $pantalla->post_type ) {
		return;
	}
	$id         = absint( get_the_ID() );
	$provisoria = (string) get_post_meta( $id, '_asp_fecha_provisoria', true );
	if ( '' === $provisoria ) {
		return;
	}
	if ( (string) get_post_meta( $id, 'evento_fecha_inicio', true ) !== $provisoria ) {
		delete_post_meta( $id, '_asp_fecha_provisoria' );
		return;
	}
	?>
	<div class="notice notice-warning asp-aviso">
		<p><strong><?php esc_html_e( 'La fecha de este evento es provisoria.', 'asp' ); ?></strong>
		<?php esc_html_e( 'Se puso la fecha en que se subió a YouTube la primera predicación, para que el evento pudiera publicarse. Cuando tengas los días reales, cambiá el primer y el último día y guardá.', 'asp' ); ?></p>
	</div>
	<?php
}
add_action( 'admin_notices', 'asp_aviso_fecha_provisoria' );
