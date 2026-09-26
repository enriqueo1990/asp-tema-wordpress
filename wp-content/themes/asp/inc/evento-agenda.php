<?php
/**
 * Cómo llegar y agregar al calendario.
 *
 * Todo sale de los datos que ya tiene el evento: quien carga no completa
 * nada nuevo. El link al mapa existe solo con dirección (un pin en la
 * ciudad no ayuda a llegar). Los dos existen solo mientras el evento no haya
 * pasado, y el calendario además necesita las dos fechas.
 *
 * Los eventos no tienen horario: entran al calendario como días completos.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

/**
 * Lugar en una línea: sede, dirección y país. La ciudad va solo si no hay
 * dirección, que ya la incluye.
 *
 * @param int $post_id ID del evento.
 * @return string
 */
function asp_evento_lugar_texto( int $post_id ): string {
	$dir = trim( (string) get_post_meta( $post_id, 'evento_sede_direccion', true ) );
	return implode(
		', ',
		array_filter(
			[
				trim( (string) get_post_meta( $post_id, 'evento_sede_nombre', true ) ),
				'' !== $dir ? preg_replace( '/\s*\R\s*/', ', ', $dir ) : asp_evento_ciudad( $post_id ),
				asp_evento_pais( $post_id ),
			]
		)
	);
}

/**
 * Búsqueda de Google Maps con la sede y la dirección. Vacío sin dirección
 * y en un evento que ya pasó, donde "Cómo llegar" no tiene sentido.
 *
 * @param int $post_id ID del evento.
 * @return string
 */
function asp_evento_url_mapa( int $post_id ): string {
	if ( '' === trim( (string) get_post_meta( $post_id, 'evento_sede_direccion', true ) ) || 'realizado' === asp_evento_estado( $post_id ) ) {
		return '';
	}
	return 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( asp_evento_lugar_texto( $post_id ) );
}

/**
 * ¿Se ofrece agregar el evento al calendario?
 *
 * @param int $post_id ID del evento.
 * @return bool
 */
function asp_evento_agendable( int $post_id ): bool {
	$inicio = (string) get_post_meta( $post_id, 'evento_fecha_inicio', true );
	$fin    = (string) get_post_meta( $post_id, 'evento_fecha_fin', true );
	return 8 === strlen( $inicio ) && 8 === strlen( $fin ) && 'realizado' !== asp_evento_estado( $post_id );
}

/**
 * Días del evento en formato de calendario: el fin es exclusivo, así que
 * es el día siguiente a la fecha de fin.
 *
 * @param int $post_id ID del evento.
 * @return string[] [inicio, fin] en Ymd.
 */
function asp_evento_dias_calendario( int $post_id ): array {
	$inicio = (string) get_post_meta( $post_id, 'evento_fecha_inicio', true );
	$fin    = DateTimeImmutable::createFromFormat( '!Ymd', (string) get_post_meta( $post_id, 'evento_fecha_fin', true ) );
	return [ $inicio, $fin ? $fin->modify( '+1 day' )->format( 'Ymd' ) : $inicio ];
}

/**
 * Título del evento en la agenda de quien lo agrega: con el nombre del
 * ministerio, que el título solo no dice de quién es.
 *
 * @param int $post_id ID del evento.
 * @return string
 */
function asp_evento_titulo_calendario( int $post_id ): string {
	/* translators: 1: título del evento, 2: nombre del sitio */
	return sprintf( __( '%1$s · %2$s', 'asp' ), wp_strip_all_tags( get_the_title( $post_id ) ), get_bloginfo( 'name' ) );
}

/**
 * Link que abre Google Calendar con el evento listo para guardar.
 *
 * @param int $post_id ID del evento.
 * @return string
 */
function asp_evento_url_google_calendar( int $post_id ): string {
	[ $inicio, $fin ] = asp_evento_dias_calendario( $post_id );
	return 'https://calendar.google.com/calendar/render?' . http_build_query(
		[
			'action'   => 'TEMPLATE',
			'text'     => asp_evento_titulo_calendario( $post_id ),
			'dates'    => $inicio . '/' . $fin,
			'details'  => get_permalink( $post_id ),
			'location' => asp_evento_lugar_texto( $post_id ),
		],
		'',
		'&',
		PHP_QUERY_RFC3986
	);
}

/**
 * URL del archivo .ics del evento (Apple Calendar, Outlook).
 *
 * @param int $post_id ID del evento.
 * @return string
 */
function asp_evento_url_ics( int $post_id ): string {
	return add_query_arg( 'calendario', 'ics', get_permalink( $post_id ) );
}

/**
 * Escapa un texto para un valor de iCalendar (RFC 5545, 3.3.11).
 *
 * @param string $texto Texto plano.
 * @return string
 */
function asp_ics_texto( string $texto ): string {
	return str_replace( [ '\\', ';', ',', "\r\n", "\n" ], [ '\\\\', '\\;', '\\,', '\\n', '\\n' ], $texto );
}

/**
 * Parte una línea de iCalendar en tramos de 75 octetos sin cortar un
 * carácter multibyte (RFC 5545, 3.1).
 *
 * @param string $linea Línea completa.
 * @return string
 */
function asp_ics_plegar( string $linea ): string {
	$tramos = [];
	$actual = '';
	foreach ( mb_str_split( $linea ) as $caracter ) {
		$limite = $tramos ? 74 : 75; // Las continuaciones empiezan con un espacio.
		if ( strlen( $actual . $caracter ) > $limite ) {
			$tramos[] = $actual;
			$actual   = '';
		}
		$actual .= $caracter;
	}
	$tramos[] = $actual;
	return implode( "\r\n ", $tramos );
}

/**
 * Archivo .ics del evento.
 *
 * @param int $post_id ID del evento.
 * @return string
 */
function asp_evento_ics( int $post_id ): string {
	[ $inicio, $fin ] = asp_evento_dias_calendario( $post_id );
	$host             = (string) wp_parse_url( home_url(), PHP_URL_HOST );
	$lineas           = [
		'BEGIN:VCALENDAR',
		'VERSION:2.0',
		'PRODID:-//' . asp_ics_texto( get_bloginfo( 'name' ) ) . '//' . $host . '//ES',
		'CALSCALE:GREGORIAN',
		'METHOD:PUBLISH',
		'BEGIN:VEVENT',
		// Mismo UID siempre: si alguien lo vuelve a bajar, el calendario actualiza el que ya tiene.
		'UID:evento-' . $post_id . '@' . $host,
		'DTSTAMP:' . gmdate( 'Ymd\THis\Z' ),
		'DTSTART;VALUE=DATE:' . $inicio,
		'DTEND;VALUE=DATE:' . $fin,
		'SUMMARY:' . asp_ics_texto( asp_evento_titulo_calendario( $post_id ) ),
		'URL:' . get_permalink( $post_id ),
		'DESCRIPTION:' . asp_ics_texto( get_permalink( $post_id ) ),
	];
	$lugar = asp_evento_lugar_texto( $post_id );
	if ( '' !== $lugar ) {
		$lineas[] = 'LOCATION:' . asp_ics_texto( $lugar );
	}
	$lineas[] = 'TRANSP:TRANSPARENT';
	$lineas[] = 'END:VEVENT';
	$lineas[] = 'END:VCALENDAR';

	return implode( "\r\n", array_map( 'asp_ics_plegar', $lineas ) ) . "\r\n";
}

/**
 * Sirve el .ics en /eventos/<evento>/?calendario=ics. Un evento que ya pasó
 * o sin fechas vuelve a su ficha.
 *
 * @return void
 */
function asp_evento_servir_ics(): void {
	if ( ! is_singular( 'evento' ) || ! isset( $_GET['calendario'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}
	$post_id = get_queried_object_id();
	if ( ! asp_evento_agendable( $post_id ) ) {
		wp_safe_redirect( get_permalink( $post_id ), 302 );
		exit;
	}
	$archivo = sanitize_file_name( get_post_field( 'post_name', $post_id ) ?: 'evento-' . $post_id ) . '.ics';
	nocache_headers();
	header( 'Content-Type: text/calendar; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="' . $archivo . '"' );
	header( 'X-Robots-Tag: noindex' );
	echo asp_evento_ics( $post_id ); // phpcs:ignore WordPress.Security.EscapeOutput -- iCalendar, no HTML.
	exit;
}
add_action( 'template_redirect', 'asp_evento_servir_ics' );
