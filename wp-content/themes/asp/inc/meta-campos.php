<?php
/**
 * Esquema de post meta: nombres, tipos y sanitización.
 *
 * Los meta boxes que escriben estos campos llegan en las sesiones 3a y 3b.
 * Registrarlos acá fija el contrato para consultas, plantillas y seed.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

/**
 * Fecha en formato Ymd o cadena vacía.
 *
 * @param mixed $valor Valor crudo.
 * @return string
 */
function asp_sanitizar_fecha_ymd( $valor ): string {
	$valor = preg_replace( '/[^0-9]/', '', (string) $valor );
	if ( strlen( $valor ) !== 8 ) {
		return '';
	}
	$anio = (int) substr( $valor, 0, 4 );
	$mes  = (int) substr( $valor, 4, 2 );
	$dia  = (int) substr( $valor, 6, 2 );
	return checkdate( $mes, $dia, $anio ) ? $valor : '';
}

/**
 * Estado de inscripción válido o el default.
 *
 * @param mixed $valor Valor crudo.
 * @return string
 */
function asp_sanitizar_estado_inscripcion( $valor ): string {
	$valor = (string) $valor;
	return in_array( $valor, array_keys( asp_estados_inscripcion() ), true ) ? $valor : 'reserva';
}

/**
 * Filas del programa: dia (Ymd), hora, titulo, orador.
 *
 * @param mixed $valor Valor crudo.
 * @return array<int, array<string, string>>
 */
function asp_sanitizar_programa( $valor ): array {
	if ( ! is_array( $valor ) ) {
		return [];
	}
	$filas = [];
	foreach ( $valor as $fila ) {
		if ( ! is_array( $fila ) ) {
			continue;
		}
		$limpia = [
			'dia'    => asp_sanitizar_fecha_ymd( $fila['dia'] ?? '' ),
			'hora'   => sanitize_text_field( $fila['hora'] ?? '' ),
			'titulo' => sanitize_text_field( $fila['titulo'] ?? '' ),
			'orador' => sanitize_text_field( $fila['orador'] ?? '' ),
		];
		if ( '' === $limpia['titulo'] && '' === $limpia['hora'] ) {
			continue;
		}
		$filas[] = $limpia;
	}
	return $filas;
}

/**
 * Opciones del estado de inscripción, en el orden del panel.
 *
 * @return array<string, string>
 */
function asp_estados_inscripcion(): array {
	return [
		'reserva' => __( 'Reservá la fecha (todavía no hay inscripción)', 'asp' ),
		'abierta' => __( 'Inscripción abierta', 'asp' ),
		'cerrada' => __( 'Inscripción cerrada', 'asp' ),
		'agotado' => __( 'Sin cupo', 'asp' ),
	];
}

/**
 * Tipos de predicación.
 *
 * @return array<string, string>
 */
function asp_tipos_predicacion(): array {
	return [
		'video' => __( 'Video (YouTube)', 'asp' ),
		'audio' => __( 'Audio (archivo mp3)', 'asp' ),
		'texto' => __( 'Texto', 'asp' ),
	];
}

/**
 * Tipo de predicación válido o texto.
 *
 * @param mixed $valor Valor crudo.
 * @return string
 */
function asp_sanitizar_tipo_predicacion( $valor ): string {
	$valor = (string) $valor;
	return isset( asp_tipos_predicacion()[ $valor ] ) ? $valor : 'texto';
}

/**
 * Roles posibles de una persona.
 *
 * @return array<string, string>
 */
function asp_roles_persona(): array {
	return [
		'consejo' => __( 'Consejo pastoral', 'asp' ),
		'orador'  => __( 'Orador', 'asp' ),
		'autor'   => __( 'Autor de artículos', 'asp' ),
	];
}

/**
 * Registra todos los campos.
 *
 * @return void
 */
function asp_registrar_meta(): void {
	$texto    = [ 'type' => 'string', 'single' => true, 'sanitize_callback' => 'sanitize_text_field', 'show_in_rest' => false ];
	$textarea = [ 'type' => 'string', 'single' => true, 'sanitize_callback' => 'sanitize_textarea_field', 'show_in_rest' => false ];
	$html     = [ 'type' => 'string', 'single' => true, 'sanitize_callback' => 'wp_kses_post', 'show_in_rest' => false ];
	$url      = [ 'type' => 'string', 'single' => true, 'sanitize_callback' => 'esc_url_raw', 'show_in_rest' => false ];
	$id       = [ 'type' => 'integer', 'single' => true, 'sanitize_callback' => 'absint', 'show_in_rest' => false ];
	$ids      = [ 'type' => 'integer', 'single' => false, 'sanitize_callback' => 'absint', 'show_in_rest' => false ];
	$bool     = [ 'type' => 'boolean', 'single' => true, 'sanitize_callback' => 'rest_sanitize_boolean', 'show_in_rest' => false ];
	$fecha    = [ 'type' => 'string', 'single' => true, 'sanitize_callback' => 'asp_sanitizar_fecha_ymd', 'show_in_rest' => false ];

	/* Evento — obligatorios */
	register_post_meta( 'evento', 'evento_fecha_inicio', $fecha );
	register_post_meta( 'evento', 'evento_fecha_fin', $fecha );
	register_post_meta( 'evento', 'evento_ciudad', $texto );
	register_post_meta( 'evento', 'evento_estado_inscripcion', [ 'type' => 'string', 'single' => true, 'sanitize_callback' => 'asp_sanitizar_estado_inscripcion', 'show_in_rest' => false ] );
	register_post_meta( 'evento', 'evento_url_registro', $url );

	/* Evento — opcionales */
	register_post_meta( 'evento', 'evento_iniciativa', $id );
	register_post_meta( 'evento', 'evento_sede_nombre', $texto );
	register_post_meta( 'evento', 'evento_sede_direccion', $textarea );
	register_post_meta( 'evento', 'evento_oradores', $ids );
	register_post_meta( 'evento', 'evento_flyer', $id );
	register_post_meta( 'evento', 'evento_descripcion', $html );
	register_post_meta( 'evento', 'evento_programa', [ 'type' => 'array', 'single' => true, 'sanitize_callback' => 'asp_sanitizar_programa', 'show_in_rest' => false ] );
	register_post_meta( 'evento', 'evento_aliados', $ids );
	register_post_meta( 'evento', 'evento_precio', $texto );
	register_post_meta( 'evento', 'evento_destacado', $bool );

	/* Evento — archivo (fase 2, se declaran para que el modelo no cambie) */
	register_post_meta( 'evento', 'evento_galeria', $ids );
	register_post_meta( 'evento', 'evento_videos', [ 'type' => 'string', 'single' => false, 'sanitize_callback' => 'esc_url_raw', 'show_in_rest' => false ] );

	/* Iniciativa */
	register_post_meta( 'iniciativa', 'iniciativa_bajada', $texto );
	register_post_meta( 'iniciativa', 'iniciativa_publico', $texto );
	register_post_meta( 'iniciativa', 'iniciativa_descripcion', $html );
	register_post_meta( 'iniciativa', 'iniciativa_historia', $html );
	register_post_meta( 'iniciativa', 'iniciativa_imagen', $id );

	/* Persona */
	register_post_meta( 'persona', 'persona_iglesia', $texto );
	register_post_meta( 'persona', 'persona_cargo', $texto );
	register_post_meta( 'persona', 'persona_ciudad', $texto );
	register_post_meta( 'persona', 'persona_pais', $texto );
	register_post_meta( 'persona', 'persona_bio', $textarea );
	register_post_meta( 'persona', 'persona_foto', $id );
	register_post_meta( 'persona', 'persona_roles', [ 'type' => 'string', 'single' => false, 'sanitize_callback' => 'sanitize_key', 'show_in_rest' => false ] );
	register_post_meta( 'persona', 'persona_usuario', $id );

	/* Predicación */
	register_post_meta( 'predicacion', 'predicacion_tipo', [ 'type' => 'string', 'single' => true, 'sanitize_callback' => 'asp_sanitizar_tipo_predicacion', 'show_in_rest' => false ] );
	register_post_meta( 'predicacion', 'predicacion_video_url', $url );
	register_post_meta( 'predicacion', 'predicacion_audio_url', $url );
	register_post_meta( 'predicacion', 'predicacion_evento', $id );
	register_post_meta( 'predicacion', 'predicacion_orador', $id );
	register_post_meta( 'predicacion', 'predicacion_pasaje', $texto );
	register_post_meta( 'predicacion', 'predicacion_duracion', $texto );
	register_post_meta( 'predicacion', 'predicacion_fecha', $fecha );

	/* Aliado */
	register_post_meta( 'aliado', 'aliado_logo', $id );
	register_post_meta( 'aliado', 'aliado_url', $url );
}
add_action( 'init', 'asp_registrar_meta' );
