<?php
/**
 * Estado efectivo del evento y helpers de render derivados.
 *
 * El estado se calcula, nunca se carga: cuando pasa la fecha de fin el
 * evento es "realizado" sin que nadie toque nada.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

/**
 * Estado efectivo del evento.
 *
 * @param int $post_id ID del evento.
 * @return string reserva|abierta|cerrada|agotado|en_curso|realizado
 */
function asp_evento_estado( int $post_id ): string {
	$hoy    = current_time( 'Ymd' );
	$inicio = (string) get_post_meta( $post_id, 'evento_fecha_inicio', true );
	$fin    = (string) get_post_meta( $post_id, 'evento_fecha_fin', true );

	if ( '' === $inicio || '' === $fin ) {
		return 'reserva';
	}
	if ( $fin < $hoy ) {
		return 'realizado';
	}
	if ( $inicio <= $hoy && $hoy <= $fin ) {
		return 'en_curso';
	}
	$estado = (string) get_post_meta( $post_id, 'evento_estado_inscripcion', true );
	return in_array( $estado, [ 'reserva', 'abierta', 'cerrada', 'agotado' ], true ) ? $estado : 'reserva';
}

/**
 * Texto del badge para cada estado.
 *
 * @param string $estado Estado efectivo.
 * @return string
 */
function asp_evento_estado_etiqueta( string $estado ): string {
	$etiquetas = [
		'reserva'   => __( 'Reservá la fecha', 'asp' ),
		'abierta'   => __( 'Inscripción abierta', 'asp' ),
		'cerrada'   => __( 'Inscripción cerrada', 'asp' ),
		'agotado'   => __( 'Sin cupo', 'asp' ),
		'en_curso'  => __( 'En curso', 'asp' ),
		'realizado' => __( 'Realizado', 'asp' ),
	];
	return $etiquetas[ $estado ] ?? $etiquetas['reserva'];
}

/**
 * Motivo que acompaña al estado cerrado o agotado.
 *
 * @param string $estado Estado efectivo.
 * @return string
 */
function asp_evento_motivo( string $estado ): string {
	if ( 'cerrada' === $estado ) {
		return __( 'La inscripción ya cerró.', 'asp' );
	}
	if ( 'agotado' === $estado ) {
		return __( 'Se agotaron los cupos.', 'asp' );
	}
	return '';
}

/**
 * URL de registro, o cadena vacía.
 *
 * @param int $post_id ID del evento.
 * @return string
 */
function asp_evento_url_registro( int $post_id ): string {
	return (string) get_post_meta( $post_id, 'evento_url_registro', true );
}

/**
 * El botón de inscripción existe solo con inscripción abierta y URL cargada.
 *
 * @param int $post_id ID del evento.
 * @return bool
 */
function asp_evento_tiene_boton( int $post_id ): bool {
	return 'abierta' === asp_evento_estado( $post_id ) && '' !== asp_evento_url_registro( $post_id );
}

/**
 * Nombre de la plataforma de inscripción según el dominio de la URL.
 *
 * @param string $url URL de registro.
 * @return string
 */
function asp_evento_plataforma( string $url ): string {
	$host = strtolower( (string) wp_parse_url( $url, PHP_URL_HOST ) );
	if ( str_contains( $host, 'eventbrite' ) ) {
		return 'Eventbrite';
	}
	if ( str_contains( $host, 'entrada27' ) ) {
		return 'Entrada27';
	}
	return '';
}

/**
 * Texto del botón: corto en tarjetas, con plataforma en la ficha.
 *
 * @param int  $post_id        ID del evento.
 * @param bool $con_plataforma Si se agrega "en Eventbrite".
 * @return string
 */
function asp_evento_boton_texto( int $post_id, bool $con_plataforma = false ): string {
	$plataforma = $con_plataforma ? asp_evento_plataforma( asp_evento_url_registro( $post_id ) ) : '';
	if ( '' !== $plataforma ) {
		/* translators: %s: nombre de la plataforma de inscripción */
		return sprintf( __( 'Inscribirse en %s', 'asp' ), $plataforma );
	}
	return __( 'Inscribirse', 'asp' );
}

/* ------------------------------------------------------------------------
   Fechas
   --------------------------------------------------------------------- */

/**
 * Nombres de mes en castellano, independientes del locale del sitio.
 *
 * @param int $mes 1 a 12.
 * @return string
 */
function asp_nombre_mes( int $mes ): string {
	$meses = [
		1  => __( 'enero', 'asp' ),
		2  => __( 'febrero', 'asp' ),
		3  => __( 'marzo', 'asp' ),
		4  => __( 'abril', 'asp' ),
		5  => __( 'mayo', 'asp' ),
		6  => __( 'junio', 'asp' ),
		7  => __( 'julio', 'asp' ),
		8  => __( 'agosto', 'asp' ),
		9  => __( 'septiembre', 'asp' ),
		10 => __( 'octubre', 'asp' ),
		11 => __( 'noviembre', 'asp' ),
		12 => __( 'diciembre', 'asp' ),
	];
	return $meses[ $mes ] ?? '';
}

/**
 * Descompone una fecha Ymd.
 *
 * @param string $ymd Fecha.
 * @return array{anio:int,mes:int,dia:int}|null
 */
function asp_fecha_partes( string $ymd ): ?array {
	if ( strlen( $ymd ) !== 8 ) {
		return null;
	}
	return [
		'anio' => (int) substr( $ymd, 0, 4 ),
		'mes'  => (int) substr( $ymd, 4, 2 ),
		'dia'  => (int) substr( $ymd, 6, 2 ),
	];
}

/**
 * Fecha Ymd a ISO 8601 (para <time datetime> y schema.org).
 *
 * @param string $ymd Fecha.
 * @return string
 */
function asp_fecha_iso( string $ymd ): string {
	$p = asp_fecha_partes( $ymd );
	return $p ? sprintf( '%04d-%02d-%02d', $p['anio'], $p['mes'], $p['dia'] ) : '';
}

/**
 * Rango de fechas en una línea: "25 y 26 de septiembre de 2026",
 * "28 de abril de 2026", "25 al 27 de septiembre de 2026",
 * "30 de abril al 2 de mayo de 2026".
 *
 * Sin año ("25 y 26 de septiembre") donde el año ya está a la vista, como
 * en la grilla del archivo; si el evento cruza de año, lo lleva igual.
 *
 * @param int  $post_id  ID del evento.
 * @param bool $con_anio Incluir el año.
 * @return string
 */
function asp_evento_fecha_texto( int $post_id, bool $con_anio = true ): string {
	$ini = asp_fecha_partes( (string) get_post_meta( $post_id, 'evento_fecha_inicio', true ) );
	$fin = asp_fecha_partes( (string) get_post_meta( $post_id, 'evento_fecha_fin', true ) );
	if ( ! $ini ) {
		return '';
	}
	if ( ! $con_anio && ( ! $fin || $ini['anio'] === $fin['anio'] ) ) {
		if ( ! $fin || $fin === $ini ) {
			/* translators: 1: día, 2: mes */
			return sprintf( __( '%1$d de %2$s', 'asp' ), $ini['dia'], asp_nombre_mes( $ini['mes'] ) );
		}
		if ( $ini['mes'] === $fin['mes'] ) {
			return $fin['dia'] - $ini['dia'] === 1
				/* translators: 1: primer día, 2: segundo día, 3: mes */
				? sprintf( __( '%1$d y %2$d de %3$s', 'asp' ), $ini['dia'], $fin['dia'], asp_nombre_mes( $ini['mes'] ) )
				/* translators: 1: primer día, 2: último día, 3: mes */
				: sprintf( __( '%1$d al %2$d de %3$s', 'asp' ), $ini['dia'], $fin['dia'], asp_nombre_mes( $ini['mes'] ) );
		}
		/* translators: 1: día inicio, 2: mes inicio, 3: día fin, 4: mes fin */
		return sprintf( __( '%1$d de %2$s al %3$d de %4$s', 'asp' ), $ini['dia'], asp_nombre_mes( $ini['mes'] ), $fin['dia'], asp_nombre_mes( $fin['mes'] ) );
	}
	if ( ! $fin || $fin === $ini ) {
		/* translators: 1: día, 2: mes, 3: año */
		return sprintf( __( '%1$d de %2$s de %3$d', 'asp' ), $ini['dia'], asp_nombre_mes( $ini['mes'] ), $ini['anio'] );
	}
	if ( $ini['anio'] === $fin['anio'] && $ini['mes'] === $fin['mes'] ) {
		if ( $fin['dia'] - $ini['dia'] === 1 ) {
			/* translators: 1: primer día, 2: segundo día, 3: mes, 4: año */
			return sprintf( __( '%1$d y %2$d de %3$s de %4$d', 'asp' ), $ini['dia'], $fin['dia'], asp_nombre_mes( $ini['mes'] ), $ini['anio'] );
		}
		/* translators: 1: primer día, 2: último día, 3: mes, 4: año */
		return sprintf( __( '%1$d al %2$d de %3$s de %4$d', 'asp' ), $ini['dia'], $fin['dia'], asp_nombre_mes( $ini['mes'] ), $ini['anio'] );
	}
	if ( $ini['anio'] === $fin['anio'] ) {
		/* translators: 1: día inicio, 2: mes inicio, 3: día fin, 4: mes fin, 5: año */
		return sprintf( __( '%1$d de %2$s al %3$d de %4$s de %5$d', 'asp' ), $ini['dia'], asp_nombre_mes( $ini['mes'] ), $fin['dia'], asp_nombre_mes( $fin['mes'] ), $ini['anio'] );
	}
	/* translators: 1: día inicio, 2: mes inicio, 3: año inicio, 4: día fin, 5: mes fin, 6: año fin */
	return sprintf( __( '%1$d de %2$s de %3$d al %4$d de %5$s de %6$d', 'asp' ), $ini['dia'], asp_nombre_mes( $ini['mes'] ), $ini['anio'], $fin['dia'], asp_nombre_mes( $fin['mes'] ), $fin['anio'] );
}

/**
 * Fecha grande en dos líneas: días ("25—26") y mes ("septiembre de 2026").
 *
 * @param int $post_id ID del evento.
 * @return array{dias:string,mes:string}
 */
function asp_evento_fecha_grande( int $post_id ): array {
	$ini = asp_fecha_partes( (string) get_post_meta( $post_id, 'evento_fecha_inicio', true ) );
	$fin = asp_fecha_partes( (string) get_post_meta( $post_id, 'evento_fecha_fin', true ) );
	if ( ! $ini ) {
		return [ 'dias' => '', 'mes' => '' ];
	}
	if ( ! $fin || $fin === $ini ) {
		return [
			'dias' => (string) $ini['dia'],
			/* translators: 1: mes, 2: año */
			'mes'  => sprintf( __( '%1$s de %2$d', 'asp' ), asp_nombre_mes( $ini['mes'] ), $ini['anio'] ),
		];
	}
	if ( $ini['anio'] === $fin['anio'] && $ini['mes'] === $fin['mes'] ) {
		return [
			'dias' => $ini['dia'] . '—' . $fin['dia'],
			'mes'  => sprintf( __( '%1$s de %2$d', 'asp' ), asp_nombre_mes( $ini['mes'] ), $ini['anio'] ),
		];
	}
	return [
		'dias' => $ini['dia'] . '—' . $fin['dia'],
		/* translators: 1: mes inicio, 2: mes fin, 3: año */
		'mes'  => sprintf( __( '%1$s — %2$s de %3$d', 'asp' ), asp_nombre_mes( $ini['mes'] ), asp_nombre_mes( $fin['mes'] ), $fin['anio'] ),
	];
}

/**
 * Año del evento a partir de la fecha de inicio.
 *
 * @param int $post_id ID del evento.
 * @return int 0 si no hay fecha.
 */
function asp_evento_anio( int $post_id ): int {
	$p = asp_fecha_partes( (string) get_post_meta( $post_id, 'evento_fecha_inicio', true ) );
	return $p ? $p['anio'] : 0;
}

/* ------------------------------------------------------------------------
   Lugar
   --------------------------------------------------------------------- */

/**
 * Ciudad del evento.
 *
 * @param int $post_id ID del evento.
 * @return string
 */
function asp_evento_ciudad( int $post_id ): string {
	return (string) get_post_meta( $post_id, 'evento_ciudad', true );
}

/**
 * Nombre del país (término de la taxonomía pais) o cadena vacía.
 *
 * @param int $post_id ID del evento.
 * @return string
 */
function asp_evento_pais( int $post_id ): string {
	$terminos = get_the_terms( $post_id, 'pais' );
	if ( ! is_array( $terminos ) || empty( $terminos ) ) {
		return '';
	}
	return $terminos[0]->name;
}

/* ------------------------------------------------------------------------
   Relaciones
   --------------------------------------------------------------------- */

/**
 * Posts relacionados por un meta múltiple (oradores, aliados), publicados.
 *
 * @param int    $post_id   ID del evento.
 * @param string $clave     Clave del meta.
 * @param string $post_type Tipo esperado.
 * @return WP_Post[]
 */
function asp_evento_relacionados( int $post_id, string $clave, string $post_type ): array {
	$ids = array_filter( array_map( 'absint', (array) get_post_meta( $post_id, $clave, false ) ) );
	if ( empty( $ids ) ) {
		return [];
	}
	$posts = get_posts(
		[
			'post_type'      => $post_type,
			'post__in'       => $ids,
			'orderby'        => 'post__in',
			'posts_per_page' => count( $ids ),
			'post_status'    => 'publish',
		]
	);
	return $posts;
}

/**
 * Iniciativa del evento, si tiene.
 *
 * @param int $post_id ID del evento.
 * @return WP_Post|null
 */
function asp_evento_iniciativa( int $post_id ): ?WP_Post {
	$id = absint( get_post_meta( $post_id, 'evento_iniciativa', true ) );
	if ( ! $id ) {
		return null;
	}
	$post = get_post( $id );
	return ( $post && 'iniciativa' === $post->post_type && 'publish' === $post->post_status ) ? $post : null;
}

/**
 * Filas del programa, ya sanitizadas y agrupadas por día.
 *
 * @param int $post_id ID del evento.
 * @return array<string, array<int, array<string, string>>> día Ymd ('' si no tiene) => filas.
 */
function asp_evento_programa( int $post_id ): array {
	$filas = asp_sanitizar_programa( get_post_meta( $post_id, 'evento_programa', true ) );
	$grupos = [];
	foreach ( $filas as $fila ) {
		$grupos[ $fila['dia'] ][] = $fila;
	}
	return $grupos;
}

/**
 * Fecha para la franja: "25 · 26" + "septiembre 2026", más el texto
 * accesible completo y el datetime ISO del primer día.
 *
 * @param int $post_id ID del evento.
 * @return array{dias:string,mes:string,texto:string,iso:string}
 */
function asp_evento_fecha_display( int $post_id ): array {
	$ini_raw = (string) get_post_meta( $post_id, 'evento_fecha_inicio', true );
	$ini     = asp_fecha_partes( $ini_raw );
	$fin     = asp_fecha_partes( (string) get_post_meta( $post_id, 'evento_fecha_fin', true ) );
	if ( ! $ini ) {
		return [ 'dias' => '', 'mes' => '', 'texto' => '', 'iso' => '' ];
	}
	$mismo_mes = ! $fin || ( $ini['anio'] === $fin['anio'] && $ini['mes'] === $fin['mes'] );
	$dias      = ( ! $fin || $fin === $ini ) ? (string) $ini['dia'] : $ini['dia'] . ' · ' . $fin['dia'];
	$mes       = $mismo_mes
		? asp_nombre_mes( $ini['mes'] ) . ' ' . $ini['anio']
		: asp_nombre_mes( $ini['mes'] ) . ' · ' . asp_nombre_mes( $fin['mes'] ) . ' ' . $fin['anio'];
	return [
		'dias'  => $dias,
		'mes'   => $mes,
		'texto' => asp_evento_fecha_texto( $post_id ),
		'iso'   => asp_fecha_iso( $ini_raw ),
	];
}
