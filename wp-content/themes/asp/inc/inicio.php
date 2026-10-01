<?php
/**
 * Inicio nuevo (rediseño del 28-9-2026), armado como plantilla de página
 * aparte para que el inicio de siempre (front-page.php) no se pierda.
 *
 * Cómo se activa y cómo se vuelve atrás: en Ajustes → Lectura, la página de
 * inicio. Si la página elegida usa la plantilla «Inicio nuevo», el filtro
 * frontpage_template la carga en lugar de front-page.php y la cabecera pasa
 * al botón «Inscribirse». Si se vuelve a elegir la página «Inicio» de antes,
 * todo queda como estaba: nada de este archivo corre fuera de esos dos casos.
 *
 * Mientras no es la portada, la página se puede ver en su propia dirección
 * (ej. /inicio-nuevo/) y no se indexa.
 *
 * Acá están las consultas y los datos; las plantillas de parts/inicio/ solo
 * imprimen.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

const ASP_PLANTILLA_INICIO = 'templates/page-inicio.php';

/* ------------------------------------------------------------------------
   Interruptor
   --------------------------------------------------------------------- */

/**
 * ¿La portada del sitio es la página con la plantilla nueva?
 *
 * @return bool
 */
function asp_inicio_nuevo_es_portada(): bool {
	static $es = null;
	if ( null === $es ) {
		$pagina = 'page' === get_option( 'show_on_front' ) ? absint( get_option( 'page_on_front' ) ) : 0;
		$es     = $pagina && ASP_PLANTILLA_INICIO === get_page_template_slug( $pagina ) && 'publish' === get_post_status( $pagina );
	}
	return $es;
}

/**
 * ¿Se está mostrando el inicio nuevo (como portada o en su propia dirección)?
 *
 * @return bool
 */
function asp_inicio_nuevo_en_vista(): bool {
	return is_page_template( ASP_PLANTILLA_INICIO ) || ( is_front_page() && asp_inicio_nuevo_es_portada() );
}

/**
 * ¿Rigen los cambios del inicio nuevo que tocan todo el sitio (el botón
 * «Inscribirse» de la cabecera)? Solo cuando es la portada, o en la vista
 * previa de la página, para poder revisarlo antes de activarlo.
 *
 * @return bool
 */
function asp_inicio_nuevo_activo(): bool {
	return asp_inicio_nuevo_es_portada() || asp_inicio_nuevo_en_vista();
}

/**
 * Con la plantilla nueva elegida como portada, se carga ella en lugar de
 * front-page.php (que en WordPress le gana a cualquier plantilla de página).
 *
 * @param string $plantilla Ruta que eligió WordPress.
 * @return string
 */
function asp_inicio_plantilla_portada( string $plantilla ): string {
	if ( ! asp_inicio_nuevo_es_portada() ) {
		return $plantilla;
	}
	$nueva = locate_template( ASP_PLANTILLA_INICIO );
	return $nueva ?: $plantilla;
}
add_filter( 'frontpage_template', 'asp_inicio_plantilla_portada' );

/**
 * La vista previa no se indexa: es el mismo contenido que tendrá la portada.
 *
 * @param array<string,bool|string> $robots Directivas.
 * @return array<string,bool|string>
 */
function asp_inicio_robots( array $robots ): array {
	if ( is_page_template( ASP_PLANTILLA_INICIO ) && ! is_front_page() ) {
		$robots['noindex'] = true;
		$robots['follow']  = true;
	}
	return $robots;
}
add_filter( 'wp_robots', 'asp_inicio_robots' );

/**
 * Cabecera transparente sobre la foto del hero nuevo. Corre después de
 * asp_body_class_portada(), que decide con el evento del inicio viejo.
 *
 * @param string[] $clases Clases.
 * @return string[]
 */
function asp_inicio_body_class( array $clases ): array {
	if ( ! asp_inicio_nuevo_en_vista() ) {
		return $clases;
	}
	$clases = array_values( array_diff( $clases, [ 'asp-con-portada' ] ) );
	if ( asp_inicio_foto_id() ) {
		$clases[] = 'asp-con-portada';
	}
	$clases[] = 'asp-inicio-nuevo';
	return $clases;
}
add_filter( 'body_class', 'asp_inicio_body_class', 20 );

/**
 * Hoja de estilos propia del inicio nuevo, solo donde se usa: si se vuelve
 * atrás, el resto del sitio no carga nada de más.
 *
 * @return void
 */
function asp_inicio_estilos(): void {
	if ( ! asp_inicio_nuevo_activo() ) {
		return;
	}
	wp_enqueue_style( 'asp-inicio', asp_asset_url( 'assets/css/inicio.css' ), [ 'asp-components' ], asp_asset_version( 'assets/css/inicio.css' ) );
}
add_action( 'wp_enqueue_scripts', 'asp_inicio_estilos', 20 );

/* ------------------------------------------------------------------------
   Aviso para quien administra
   --------------------------------------------------------------------- */

/**
 * Aviso de dato faltante: lo ve solo quien puede cargarlo. Para quien
 * visita, el bloque sin datos no existe (regla 4).
 *
 * @param string $falta Qué falta.
 * @param string $donde Dónde se carga.
 * @return void
 */
function asp_inicio_falta( string $falta, string $donde ): void {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}
	printf(
		'<p class="asp-inicio-falta"><strong>%1$s</strong> %2$s <span>%3$s</span></p>',
		esc_html__( 'Falta cargar:', 'asp' ),
		esc_html( $falta ),
		/* translators: %s: dónde se carga el dato */
		esc_html( sprintf( __( '(%s). Solo lo ves vos porque administrás el sitio.', 'asp' ), $donde ) )
	);
}

/* ------------------------------------------------------------------------
   Hero
   --------------------------------------------------------------------- */

/**
 * Evento del hero nuevo: el primero con inscripción abierta que esté
 * marcado como principal; si ninguno lo está, el próximo abierto por fecha.
 * Null si no hay ninguno abierto: el hero muestra solo la identidad.
 *
 * @return WP_Post|null
 */
function asp_evento_hero(): ?WP_Post {
	static $hero = false;
	if ( false !== $hero ) {
		return $hero;
	}
	$abiertos = array_values(
		array_filter(
			asp_eventos_proximos()->posts,
			static fn( WP_Post $p ): bool => 'abierta' === asp_evento_estado( $p->ID ) && asp_evento_tiene_boton( $p->ID )
		)
	);
	$hero = null;
	foreach ( $abiertos as $post ) {
		if ( get_post_meta( $post->ID, 'evento_destacado', true ) ) {
			$hero = $post;
			break;
		}
	}
	if ( ! $hero && ! empty( $abiertos ) ) {
		$hero = $abiertos[0];
	}
	return $hero;
}

/**
 * Línea de identidad del ministerio: la de Personalizar → Inicio y, si está
 * vacía, la descripción del sitio (Ajustes → Generales).
 *
 * @return string
 */
function asp_inicio_identidad(): string {
	$linea = trim( (string) get_theme_mod( 'asp_identidad', '' ) );
	return '' !== $linea ? $linea : trim( (string) get_bloginfo( 'description' ) );
}

/**
 * Foto de fondo del hero nuevo: la del evento abierto si tiene, si no la
 * general de Personalizar. 0 si no hay.
 *
 * @return int
 */
function asp_inicio_foto_id(): int {
	static $id = null;
	if ( null === $id ) {
		$evento = asp_evento_hero();
		$id     = $evento ? absint( get_post_meta( $evento->ID, 'evento_foto', true ) ) : 0;
		if ( ! $id || ! wp_attachment_is_image( $id ) ) {
			$id = absint( get_theme_mod( 'asp_hero_imagen', 0 ) );
		}
	}
	return $id;
}

/**
 * Datos del evento para el hero, ya priorizados (29-9-2026): lo que decide
 * la inscripción va arriba y grande (tipo, título, fecha y lugar); quiénes
 * enseñan, con foto chica; para quién es y el cupo en una línea
 * secundaria; el precio queda en la ficha del evento. Todo lo opcional llega vacío si
 * no se cargó, y la plantilla lo omite.
 *
 * @param int $id Evento.
 * @return array{tipo:string,titulo:string,publico:string,cupo:string,fecha:string,iso:string,lugar:string,oradores:WP_Post[],url:string,registro:string}
 */
function asp_inicio_hero_datos( int $id ): array {
	$iniciativa = asp_evento_iniciativa( $id );
	$inicio     = (string) get_post_meta( $id, 'evento_fecha_inicio', true );
	return [
		'tipo'     => $iniciativa ? get_the_title( $iniciativa ) : '',
		'titulo'   => get_the_title( $id ),
		'publico'  => $iniciativa ? trim( (string) get_post_meta( $iniciativa->ID, 'iniciativa_publico', true ) ) : '',
		'cupo'     => trim( (string) get_post_meta( $id, 'evento_cupo', true ) ),
		/* El año solo si no es el actual: en noviembre, «de 2026» sobra. */
		'fecha'    => asp_evento_fecha_texto( $id, asp_evento_anio( $id ) !== (int) current_time( 'Y' ) ),
		'iso'      => asp_fecha_iso( $inicio ),
		'lugar'    => implode( ', ', array_filter( [ asp_evento_ciudad( $id ), asp_evento_pais( $id ) ] ) ),
		'oradores' => asp_evento_relacionados( $id, 'evento_oradores', 'persona' ),
		'url'      => (string) get_permalink( $id ),
		'registro' => asp_evento_url_registro( $id ),
	];
}

/* ------------------------------------------------------------------------
   Quiénes somos y confianza
   --------------------------------------------------------------------- */

/**
 * Cifras de trayectoria cargadas en Personalizar → Inicio. Un par sin
 * número o sin texto no se muestra.
 *
 * @return array<int, array{numero:string,texto:string}>
 */
function asp_inicio_cifras(): array {
	$cifras = [];
	foreach ( [ 1, 2, 3 ] as $n ) {
		$numero = trim( (string) get_theme_mod( "asp_cifra_{$n}_numero", '' ) );
		$texto  = trim( (string) get_theme_mod( "asp_cifra_{$n}_texto", '' ) );
		if ( '' !== $numero && '' !== $texto ) {
			$cifras[] = [ 'numero' => $numero, 'texto' => $texto ];
		}
	}
	return $cifras;
}

/**
 * Testimonio de Personalizar, o null si falta el texto o el nombre.
 *
 * @return array{texto:string,nombre:string,iglesia:string}|null
 */
function asp_inicio_testimonio(): ?array {
	$t = [
		'texto'   => trim( (string) get_theme_mod( 'asp_testimonio_texto', '' ) ),
		'nombre'  => trim( (string) get_theme_mod( 'asp_testimonio_nombre', '' ) ),
		'iglesia' => trim( (string) get_theme_mod( 'asp_testimonio_iglesia', '' ) ),
	];
	return ( '' !== $t['texto'] && '' !== $t['nombre'] ) ? $t : null;
}

/* ------------------------------------------------------------------------
   Cómo servimos
   --------------------------------------------------------------------- */

/**
 * Datos de una iniciativa para su tarjeta del inicio nuevo.
 *
 * El país sale del evento más reciente de la iniciativa (próximo o pasado):
 * así se distingue la conferencia de Argentina de la de Estados Unidos sin
 * un campo más.
 *
 * @param int $id Iniciativa.
 * @return array{foto:string,bajada:string,publico:string,pais:string,proxima:string}
 */
function asp_inicio_iniciativa( int $id ): array {
	$prox   = asp_iniciativa_proximo( $id );
	$ultimo = $prox ?: ( asp_eventos_de_iniciativa( $id, false )[0] ?? null );
	$foto   = absint( get_post_meta( $id, 'iniciativa_imagen', true ) );
	return [
		'foto'    => $foto ? (string) wp_get_attachment_image( $foto, 'asp-tarjeta', false, [ 'loading' => 'lazy', 'alt' => '', 'class' => 'asp-inicio-servir__foto' ] ) : '',
		'bajada'  => trim( (string) get_post_meta( $id, 'iniciativa_bajada', true ) ),
		'publico' => trim( (string) get_post_meta( $id, 'iniciativa_publico', true ) ),
		'pais'    => $ultimo ? asp_evento_pais( $ultimo->ID ) : '',
		'proxima' => $prox ? implode( ' · ', array_filter( [ asp_evento_fecha_texto( $prox->ID ), asp_evento_ciudad( $prox->ID ) ] ) ) : '',
	];
}

/* ------------------------------------------------------------------------
   Próximos eventos
   --------------------------------------------------------------------- */

/**
 * Próximos eventos sin el del hero.
 *
 * @param int $cantidad Máximo.
 * @return WP_Post[]
 */
function asp_inicio_proximos( int $cantidad = 4 ): array {
	$hero = asp_evento_hero();
	$lista = array_filter(
		asp_eventos_proximos()->posts,
		static fn( WP_Post $p ): bool => ! $hero || $p->ID !== $hero->ID
	);
	return array_slice( array_values( $lista ), 0, $cantidad );
}

/**
 * Datos de un evento para su cartel en «Próximos eventos», ya priorizados
 * (1-10-2026). El orden de lectura repite el del hero: tipo y título como
 * una sola pieza, después la línea que decide (cuándo y dónde) y, solo si
 * pide hacer algo o avisa algo, el estado. «Reservá la fecha» no se
 * muestra: es lo esperable de un evento futuro y en el inicio solo sumaba
 * un rótulo más; sigue en Eventos y en la ficha.
 *
 * La imagen es el flyer (lo que la gente ya vio en Instagram), entero y sin
 * recortar sobre la banda tonal (regla 5); si no hay flyer, la foto del
 * evento. Sin ninguna de las dos, el cartel lleva la fecha grande sobre la
 * misma banda, como en Eventos: un dato real, no un placeholder.
 *
 * @param int $id Evento.
 * @return array{tipo:string,titulo:string,url:string,fecha:string,iso:string,lugar:string,estado:string,imagen:string,clase:string,dias:string,mes:string}
 */
function asp_inicio_agenda_datos( int $id ): array {
	$iniciativa = asp_evento_iniciativa( $id );
	$estado     = asp_evento_estado( $id );
	$flyer      = absint( get_post_meta( $id, 'evento_flyer', true ) );
	$foto       = absint( get_post_meta( $id, 'evento_foto', true ) );
	$imagen     = '';
	$clase      = 'fecha';
	if ( $flyer ) {
		$imagen = (string) wp_get_attachment_image( $flyer, 'asp-flyer-card', false, [ 'loading' => 'lazy', 'alt' => '', 'class' => 'asp-inicio-agenda__img' ] );
		$clase  = 'flyer';
	}
	if ( '' === $imagen && $foto ) {
		$imagen = (string) wp_get_attachment_image( $foto, 'asp-tarjeta', false, [ 'loading' => 'lazy', 'alt' => '', 'class' => 'asp-inicio-agenda__img' ] );
		$clase  = 'foto';
	}
	if ( '' === $imagen ) {
		$clase = 'fecha';
	}
	$grande = asp_evento_fecha_grande( $id );
	return [
		'tipo'   => $iniciativa ? get_the_title( $iniciativa ) : '',
		'titulo' => get_the_title( $id ),
		'url'    => (string) get_permalink( $id ),
		/* El año solo si no es el actual, como en el hero. */
		'fecha'  => asp_evento_fecha_texto( $id, asp_evento_anio( $id ) !== (int) current_time( 'Y' ) ),
		'iso'    => asp_fecha_iso( (string) get_post_meta( $id, 'evento_fecha_inicio', true ) ),
		'lugar'  => implode( ', ', array_filter( [ asp_evento_ciudad( $id ), asp_evento_pais( $id ) ] ) ),
		'estado' => 'reserva' === $estado ? '' : $estado,
		'imagen' => $imagen,
		'clase'  => $clase,
		'dias'   => $grande['dias'],
		'mes'    => $grande['mes'],
	];
}

/* ------------------------------------------------------------------------
   Formas de participar
   --------------------------------------------------------------------- */

/**
 * Kit de medios del próximo evento que tenga uno: el del hero primero.
 *
 * @return array{url:string,evento:string}|null
 */
function asp_inicio_kit(): ?array {
	$hero      = asp_evento_hero();
	$candidatos = array_merge( $hero ? [ $hero ] : [], asp_eventos_proximos()->posts );
	foreach ( $candidatos as $evento ) {
		$url = (string) get_post_meta( $evento->ID, 'evento_kit', true );
		if ( '' !== $url ) {
			return [ 'url' => $url, 'evento' => get_the_title( $evento ) ];
		}
	}
	return null;
}

/**
 * Motivos de oración cargados en Personalizar → Inicio.
 *
 * @return string[]
 */
function asp_inicio_motivos_oracion(): array {
	return array_values(
		array_filter(
			[
				trim( (string) get_theme_mod( 'asp_oracion_1', '' ) ),
				trim( (string) get_theme_mod( 'asp_oracion_2', '' ) ),
			]
		)
	);
}

/**
 * Tarjetas de "Formas de participar", en orden. Cada una trae su título,
 * su texto, una lista opcional y sus acciones; una tarjeta sin datos no
 * entra. Para sumar "Donar" más adelante alcanza con agregar una entrada
 * acá: la grilla se reacomoda sola.
 *
 * @return array<string, array{titulo:string,texto:string,lista:string[],acciones:array<int,array{texto:string,url:string,externo:bool}>}>
 */
function asp_inicio_participar(): array {
	$tarjetas = [];

	$suscripcion = (string) get_theme_mod( 'asp_suscripcion_url', '' );
	$whatsapp    = (string) get_theme_mod( 'asp_red_whatsapp', '' );
	$acciones    = [];
	if ( $suscripcion ) {
		$acciones[] = [ 'texto' => __( 'Suscribite por mail', 'asp' ), 'url' => $suscripcion, 'externo' => true ];
	}
	if ( $whatsapp ) {
		$acciones[] = [ 'texto' => __( 'Unite al canal de WhatsApp', 'asp' ), 'url' => $whatsapp, 'externo' => true ];
	}
	if ( $acciones ) {
		$tarjetas['anuncios'] = [
			'titulo'   => __( 'Recibí los anuncios', 'asp' ),
			'texto'    => __( 'Enterate cuando se abre la inscripción de cada encuentro.', 'asp' ),
			'lista'    => [],
			'acciones' => $acciones,
		];
	}

	$kit = asp_inicio_kit();
	if ( $kit ) {
		$tarjetas['invitar'] = [
			'titulo'   => __( 'Invitá a otros', 'asp' ),
			/* translators: %s: nombre del evento */
			'texto'    => sprintf( __( 'Descargá las piezas de «%s» para compartir en tu iglesia y en redes.', 'asp' ), $kit['evento'] ),
			'lista'    => [],
			'acciones' => [ [ 'texto' => __( 'Descargar el kit', 'asp' ), 'url' => $kit['url'], 'externo' => false ] ],
		];
	}

	$motivos = asp_inicio_motivos_oracion();
	if ( $motivos ) {
		$tarjetas['orar'] = [
			'titulo'   => __( 'Orá por el ministerio', 'asp' ),
			'texto'    => '',
			'lista'    => $motivos,
			'acciones' => [],
		];
	}

	$contacto = asp_url_pagina_plantilla( 'templates/page-contacto.php' );
	if ( $contacto ) {
		$tarjetas['taller'] = [
			'titulo'   => __( 'Recibí un taller en tu iglesia', 'asp' ),
			'texto'    => __( 'Si tu iglesia quiere ser anfitriona de un taller, escribinos y lo conversamos.', 'asp' ),
			'lista'    => [],
			'acciones' => [ [ 'texto' => __( 'Escribinos', 'asp' ), 'url' => add_query_arg( 'motivo', 'taller', $contacto ) . '#escribinos', 'externo' => false ] ],
		];
	}

	return $tarjetas;
}

/**
 * Qué tarjetas de "Formas de participar" faltan cargar, para el aviso de
 * quien administra.
 *
 * @return array<int, array{falta:string,donde:string}>
 */
function asp_inicio_participar_faltan(): array {
	$tarjetas = asp_inicio_participar();
	$faltan   = [];
	if ( ! isset( $tarjetas['anuncios'] ) ) {
		$faltan[] = [ 'falta' => __( 'link de suscripción por mail o del canal de WhatsApp', 'asp' ), 'donde' => __( 'Personalizar → Inicio', 'asp' ) ];
	}
	if ( ! isset( $tarjetas['invitar'] ) ) {
		$faltan[] = [ 'falta' => __( 'kit de medios del próximo evento', 'asp' ), 'donde' => __( 'Eventos → el evento → Más detalles → Kit de medios', 'asp' ) ];
	}
	if ( ! isset( $tarjetas['orar'] ) ) {
		$faltan[] = [ 'falta' => __( 'motivos de oración', 'asp' ), 'donde' => __( 'Personalizar → Inicio', 'asp' ) ];
	}
	return $faltan;
}

/* ------------------------------------------------------------------------
   Artículos
   --------------------------------------------------------------------- */

/**
 * Rótulo de un artículo: la serie si es parte de una, si no la categoría.
 * Vacío si solo tiene «Sin categoría».
 *
 * @param int $id Artículo.
 * @return string
 */
function asp_inicio_articulo_rotulo( int $id ): string {
	$serie = asp_serie_de_articulo( $id );
	if ( $serie ) {
		return $serie->name;
	}
	$cat = get_the_category( $id );
	return ( ! empty( $cat ) && 'uncategorized' !== $cat[0]->slug ) ? $cat[0]->name : '';
}
