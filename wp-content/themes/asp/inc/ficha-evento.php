<?php
/**
 * Ficha del evento (rediseño del handoff de Claude Design, 2-10-2026):
 * los datos ya preparados para single-evento.php, los íconos y la hoja de
 * estilos propia, que se carga solo en la ficha.
 *
 * Orden de la página, decidido con el ministerio: flyer, título, datos
 * clave (fecha, lugar, costo) en un panel, oradores, descripción, aliados y
 * la banda de acción (estado, inscribirse, calendario, compartir). Sin
 * rótulos de sección salvo el estado, sin filetes. Cada bloque se imprime
 * solo si tiene contenido.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

/**
 * Hoja de estilos de la ficha, solo en la ficha.
 *
 * @return void
 */
function asp_ficha_estilos(): void {
	if ( ! is_singular( 'evento' ) ) {
		return;
	}
	wp_enqueue_style( 'asp-ficha', asp_asset_url( 'assets/css/ficha.css' ), [ 'asp-components' ], asp_asset_version( 'assets/css/ficha.css' ) );
}
add_action( 'wp_enqueue_scripts', 'asp_ficha_estilos', 20 );

/**
 * Ícono de la ficha (Lucide, trazo 1,75, currentColor). Decorativo.
 *
 * @param string $nombre calendar|calendar-plus|map-pin|ticket|share|arrow.
 * @param string $clase  Clase extra.
 * @return string
 */
function asp_ficha_icono( string $nombre, string $clase = '' ): string {
	$trazos = [
		'calendar'      => '<path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/>',
		'calendar-plus' => '<path d="M8 2v4"/><path d="M16 2v4"/><path d="M21 13V6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h8"/><path d="M3 10h18"/><path d="M16 19h6"/><path d="M19 16v6"/>',
		'map-pin'       => '<path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"/><circle cx="12" cy="10" r="3"/>',
		'ticket'        => '<path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Z"/><path d="M13 5v2"/><path d="M13 17v2"/><path d="M13 11v2"/>',
		'share'         => '<circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="M8.59 13.51l6.83 3.98"/><path d="M15.41 6.51l-6.82 3.98"/>',
		'arrow'         => '<path d="M7 7h10v10"/><path d="M7 17 17 7"/>',
	];
	if ( ! isset( $trazos[ $nombre ] ) ) {
		return '';
	}
	return '<svg class="ev-ico' . ( $clase ? ' ' . esc_attr( $clase ) : '' ) . '" viewBox="0 0 24 24" aria-hidden="true" focusable="false">' . $trazos[ $nombre ] . '</svg>';
}

/**
 * Bandera del país del evento, servida desde el tema. Vacía si el país no
 * tiene bandera cargada.
 *
 * @param int $post_id ID del evento.
 * @return array{src:string,alt:string}|null
 */
function asp_ficha_bandera( int $post_id ): ?array {
	$banderas = [
		'argentina'      => 'ar',
		'estados-unidos' => 'us',
	];
	$terminos = get_the_terms( $post_id, 'pais' );
	if ( ! is_array( $terminos ) || empty( $terminos ) || ! isset( $banderas[ $terminos[0]->slug ] ) ) {
		return null;
	}
	return [
		'src' => asp_asset_url( 'assets/img/banderas/' . $banderas[ $terminos[0]->slug ] . '.svg' ),
		/* translators: %s: país */
		'alt' => sprintf( __( 'Bandera de %s', 'asp' ), $terminos[0]->name ),
	];
}

/**
 * Datos de la ficha, ya decididos según el estado (tabla «Reglas de render»
 * del handoff):
 * - Inscripción abierta: costo (si hay) y botón Inscribirse.
 * - Reservá la fecha: sin costo; el botón principal es Guardar en el
 *   calendario.
 * - Cerrada, sin cupo, en curso: sin costo ni Inscribirse.
 * - Realizado: sin costo ni calendario; solo Compartir.
 *
 * El precio es texto libre: si trae « · », lo de antes es el monto y lo de
 * después, el detalle («incluye…»).
 *
 * @param int $id ID del evento.
 * @return array<string, mixed>
 */
function asp_ficha_datos( int $id ): array {
	$estado  = asp_evento_estado( $id );
	$precio  = trim( (string) get_post_meta( $id, 'evento_precio', true ) );
	$monto   = '';
	$detalle = '';
	if ( 'abierta' === $estado && '' !== $precio ) {
		$partes  = array_map( 'trim', explode( ' · ', $precio, 2 ) );
		$monto   = $partes[0];
		$detalle = $partes[1] ?? '';
	}
	$sede   = trim( (string) get_post_meta( $id, 'evento_sede_nombre', true ) );
	$dir    = trim( (string) get_post_meta( $id, 'evento_sede_direccion', true ) );
	$agenda = asp_evento_agendable( $id );

	$cargados = asp_evento_relacionados( $id, 'evento_oradores', 'persona' );
	$oradores = ! empty( $cargados )
		? array_map( static fn( WP_Post $p ): array => [ 'persona' => $p, 'nombre' => get_the_title( $p ) ], $cargados )
		: asp_evento_oradores_de_predicaciones( $id );

	return [
		'estado'      => $estado,
		'etiqueta'    => asp_evento_estado_etiqueta( $estado, $id ),
		'flyer'       => asp_ficha_flyer( $id ),
		'fecha'       => asp_evento_fecha_texto( $id ),
		'iso'         => asp_fecha_iso( (string) get_post_meta( $id, 'evento_fecha_inicio', true ) ),
		'lugar'       => implode( ', ', array_filter( [ asp_evento_ciudad( $id ), asp_evento_pais( $id ) ] ) ),
		'bandera'     => asp_ficha_bandera( $id ),
		'sede'        => $sede,
		'direccion'   => $dir,
		'mapa'        => asp_evento_url_mapa( $id ),
		'monto'       => $monto,
		'detalle'     => $detalle,
		'oradores'    => $oradores,
		'descripcion' => (string) get_post_meta( $id, 'evento_descripcion', true ),
		'aliados'     => asp_evento_relacionados( $id, 'evento_aliados', 'aliado' ),
		'registro'    => asp_evento_tiene_boton( $id ) ? asp_evento_url_registro( $id ) : '',
		'agenda'      => $agenda ? [
			'google' => asp_evento_url_google_calendar( $id ),
			'ics'    => asp_evento_url_ics( $id ),
		] : null,
		'compartir'   => asp_evento_compartir( $id ),
		'eeuu'        => has_term( 'estados-unidos', 'pais', $id ),
	];
}

/**
 * Flyer de la ficha, entero y sin recortar (regla 5). Con versión apaisada
 * cargada, la computadora muestra esa y el teléfono el flyer de siempre
 * (4:5 o cuadrado), que ocupa la pantalla mejor. Sin flyer de siempre, la
 * apaisada va en los dos.
 *
 * @param int $id Evento.
 * @return string HTML.
 */
function asp_ficha_flyer( int $id ): string {
	$flyer = absint( get_post_meta( $id, 'evento_flyer', true ) );
	$ancho = absint( get_post_meta( $id, 'evento_flyer_ancho', true ) );
	$img   = $flyer ?: $ancho;
	if ( ! $img ) {
		return '';
	}
	$html = (string) wp_get_attachment_image(
		$img,
		'full',
		false,
		[
			/* translators: %s: título del evento */
			'alt'           => sprintf( __( 'Flyer de %s', 'asp' ), get_the_title( $id ) ),
			'loading'       => 'eager',
			'fetchpriority' => 'high',
			'class'         => 'ev-flyer__img',
		]
	);
	$datos = ( $flyer && $ancho && $flyer !== $ancho ) ? wp_get_attachment_image_src( $ancho, 'full' ) : false;
	if ( '' === $html || ! $datos ) {
		return $html;
	}
	$srcset = (string) wp_get_attachment_image_srcset( $ancho, 'full' );
	/* Mismo corte que ficha.css, donde el flyer deja de ir a sangre. */
	$source = sprintf(
		'<source media="(min-width: 900px)" srcset="%s" sizes="(min-width: 900px) min(100vw, 1280px), 100vw" width="%d" height="%d">',
		esc_attr( $srcset ? $srcset : $datos[0] ),
		(int) $datos[1],
		(int) $datos[2]
	);
	return '<picture>' . $source . $html . '</picture>';
}
