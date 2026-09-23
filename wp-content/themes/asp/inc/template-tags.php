<?php
/**
 * Helpers de impresión para las plantillas. Las plantillas solo imprimen;
 * las consultas y cálculos viven en los otros módulos de inc/.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

/**
 * Logo: el cargado en el Customizer o el PNG del tema.
 *
 * @param string $clase Clase del enlace.
 * @return void
 */
function asp_logo( string $clase = 'asp-header__logo' ): void {
	$id  = (int) get_theme_mod( 'custom_logo' );
	$src = $id ? wp_get_attachment_image_url( $id, 'full' ) : '';
	if ( ! $src ) {
		$src = asp_asset_url( 'assets/img/logo-asp.png' );
	}
	printf(
		'<a class="%1$s" href="%2$s" rel="home"><img src="%3$s" alt="%4$s" width="582" height="183"></a>',
		esc_attr( $clase ),
		esc_url( home_url( '/' ) ),
		esc_url( $src ),
		esc_attr( get_bloginfo( 'name' ) )
	);
}

/**
 * Ícono de locación: el único ícono del sistema.
 *
 * @param string $clase Clases extra.
 * @return string
 */
function asp_icono_ubicacion( string $clase = 'asp-lugar__icono' ): string {
	return '<svg class="' . esc_attr( $clase ) . '" viewBox="0 0 16 16" fill="none" aria-hidden="true" focusable="false"><path d="M8 1.6c-2.4 0-4.3 1.9-4.3 4.3 0 3.1 4.3 8.5 4.3 8.5s4.3-5.4 4.3-8.5c0-2.4-1.9-4.3-4.3-4.3Z" stroke="currentColor" stroke-width="1.3" stroke-linejoin="round"/><circle cx="8" cy="5.9" r="1.7" fill="currentColor"/></svg>';
}

/**
 * Ícono de descarga.
 *
 * @return string
 */
function asp_icono_descarga(): string {
	return '<svg width="15" height="15" viewBox="0 0 16 16" fill="none" aria-hidden="true" focusable="false"><path d="M8 2v8m0 0 3-3m-3 3-3-3M3 13.2h10" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/></svg>';
}

/**
 * URL del listado de eventos.
 *
 * @return string
 */
function asp_url_eventos(): string {
	$url = get_post_type_archive_link( 'evento' );
	return $url ? $url : home_url( '/eventos/' );
}

/**
 * URL del listado de recursos (la página de entradas) o la home.
 *
 * @return string
 */
function asp_url_recursos(): string {
	$id = (int) get_option( 'page_for_posts' );
	return $id ? (string) get_permalink( $id ) : home_url( '/' );
}

/**
 * URL de la primera página que use una plantilla dada.
 *
 * @param string $plantilla Ruta relativa, ej. templates/page-nosotros.php.
 * @return string
 */
function asp_url_pagina_plantilla( string $plantilla ): string {
	$paginas = get_posts(
		[
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'meta_key'       => '_wp_page_template',
			'meta_value'     => $plantilla,
		]
	);
	return $paginas ? (string) get_permalink( $paginas[0] ) : '';
}

/**
 * Ítems del menú cuando no hay uno asignado: Eventos · Iniciativas ·
 * Recursos · Nosotros · Contacto, solo los que existen.
 *
 * @return array<int, array{url:string,label:string,actual:bool}>
 */
function asp_menu_items_fallback(): array {
	$items = [];

	$items[] = [
		'url'    => asp_url_eventos(),
		'label'  => __( 'Eventos', 'asp' ),
		'actual' => is_post_type_archive( 'evento' ) || is_singular( 'evento' ),
	];

	if ( get_posts( [ 'post_type' => 'iniciativa', 'posts_per_page' => 1, 'post_status' => 'publish' ] ) ) {
		$items[] = [
			'url'    => (string) get_post_type_archive_link( 'iniciativa' ),
			'label'  => __( 'Iniciativas', 'asp' ),
			'actual' => is_post_type_archive( 'iniciativa' ) || is_singular( 'iniciativa' ),
		];
	}

	if ( (int) get_option( 'page_for_posts' ) ) {
		$items[] = [
			'url'    => asp_url_recursos(),
			'label'  => __( 'Recursos', 'asp' ),
			'actual' => is_home() || is_singular( 'post' ) || is_category() || is_tag() || is_tax( 'serie' ),
		];
	}

	$nosotros = asp_url_pagina_plantilla( 'templates/page-nosotros.php' );
	if ( $nosotros ) {
		$items[] = [
			'url'    => $nosotros,
			'label'  => __( 'Nosotros', 'asp' ),
			'actual' => is_page_template( 'templates/page-nosotros.php' ),
		];
	}

	$contacto = asp_url_pagina_plantilla( 'templates/page-contacto.php' );
	if ( $contacto ) {
		$items[] = [
			'url'    => $contacto,
			'label'  => __( 'Contacto', 'asp' ),
			'actual' => is_page_template( 'templates/page-contacto.php' ),
		];
	}

	return $items;
}

/**
 * Imprime el menú de una ubicación, con el fallback automático.
 *
 * @param string $ubicacion principal|pie.
 * @return void
 */
function asp_menu( string $ubicacion ): void {
	if ( has_nav_menu( $ubicacion ) ) {
		wp_nav_menu(
			[
				'theme_location' => $ubicacion,
				'container'      => false,
				'menu_class'     => '',
				'fallback_cb'    => false,
				'depth'          => 1,
			]
		);
		return;
	}
	$items = asp_menu_items_fallback();
	if ( empty( $items ) ) {
		return;
	}
	echo '<ul>';
	foreach ( $items as $item ) {
		printf(
			'<li class="%1$s"><a href="%2$s"%3$s>%4$s</a></li>',
			$item['actual'] ? 'is-current' : '',
			esc_url( $item['url'] ),
			$item['actual'] ? ' aria-current="page"' : '',
			esc_html( $item['label'] )
		);
	}
	echo '</ul>';
}

/**
 * Redes del ministerio cargadas en el Customizer.
 *
 * @return array<string, string> nombre => url.
 */
function asp_redes(): array {
	$redes = [
		'Facebook'  => (string) get_theme_mod( 'asp_red_facebook', 'https://www.facebook.com/ConferenciaAnteSuPalabra/' ),
		'Twitter'   => (string) get_theme_mod( 'asp_red_twitter', 'https://twitter.com/antesupalabra' ),
		'YouTube'   => (string) get_theme_mod( 'asp_red_youtube', 'https://www.youtube.com/channel/UCzBclEQZPuu7qy7rQRpUdaA' ),
		'Instagram' => (string) get_theme_mod( 'asp_red_instagram', 'https://www.instagram.com/antesupalabra/' ),
	];
	return array_filter( $redes );
}

/**
 * Foto de una persona o el placeholder rayado.
 *
 * @param int    $persona_id ID de la persona.
 * @param string $clase      Clase del elemento.
 * @param string $tamano     Tamaño de imagen registrado.
 * @return string
 */
function asp_persona_foto( int $persona_id, string $clase, string $tamano = 'asp-persona' ): string {
	$foto_id = absint( get_post_meta( $persona_id, 'persona_foto', true ) );
	if ( ! $foto_id ) {
		$foto_id = (int) get_post_thumbnail_id( $persona_id );
	}
	if ( $foto_id ) {
		$html = wp_get_attachment_image( $foto_id, $tamano, false, [ 'class' => $clase, 'alt' => get_the_title( $persona_id ) ] );
		if ( $html ) {
			return $html;
		}
	}
	return '<span class="' . esc_attr( $clase ) . '" aria-hidden="true"></span>';
}

/**
 * Cargo e iglesia de una persona en una línea: "Pastor · Iglesia X".
 *
 * @param int $persona_id ID de la persona.
 * @return string
 */
function asp_persona_cargo_iglesia( int $persona_id ): string {
	$partes = array_filter(
		[
			(string) get_post_meta( $persona_id, 'persona_cargo', true ),
			(string) get_post_meta( $persona_id, 'persona_iglesia', true ),
		]
	);
	return implode( ' · ', $partes );
}

/**
 * Nombre a mostrar del autor de un artículo: su ficha de persona si existe.
 *
 * @param int $post_id ID del artículo.
 * @return array{nombre:string,url:string}
 */
function asp_autor_articulo( int $post_id ): array {
	$user_id = (int) get_post_field( 'post_author', $post_id );
	$persona = asp_persona_de_usuario( $user_id );
	if ( $persona ) {
		return [ 'nombre' => get_the_title( $persona ), 'url' => (string) get_permalink( $persona ) ];
	}
	return [ 'nombre' => (string) get_the_author_meta( 'display_name', $user_id ), 'url' => (string) get_author_posts_url( $user_id ) ];
}

/**
 * Primera línea de la descripción de una iniciativa, para las cajas del
 * inicio. Sale del texto real del ministerio; si no hay descripción, no se
 * imprime nada.
 *
 * @param int $post_id  ID de la iniciativa.
 * @param int $palabras Máximo de palabras.
 * @return string
 */
function asp_iniciativa_resumen( int $post_id, int $palabras = 18 ): string {
	$bajada = (string) get_post_meta( $post_id, 'iniciativa_bajada', true );
	if ( '' === trim( $bajada ) ) {
		$bajada = (string) get_post_meta( $post_id, 'iniciativa_descripcion', true );
	}
	return wp_trim_words( wp_strip_all_tags( $bajada ), $palabras, '…' );
}

/**
 * Resumen de un artículo: el extracto cargado a mano y, si no hay, las
 * primeras palabras del cuerpo. Los artículos migrados del blog traen su
 * propio extracto, así que no se les inventa una bajada.
 *
 * @param int $post_id  ID del artículo.
 * @param int $palabras Máximo de palabras.
 * @return string
 */
function asp_resumen_articulo( int $post_id, int $palabras = 28 ): string {
	$extracto = (string) get_post_field( 'post_excerpt', $post_id );
	if ( '' === trim( $extracto ) ) {
		$extracto = preg_replace( '#</(p|h[1-6]|li|blockquote|div)>#i', ' ', (string) get_post_field( 'post_content', $post_id ) );
	}
	return wp_trim_words( wp_strip_all_tags( (string) $extracto ), $palabras, '…' );
}

/**
 * Imagen destacada de un artículo o predicación, lista para imprimir.
 * Cadena vacía si no tiene: ninguna vista dibuja un hueco gris.
 *
 * @param int    $post_id ID del post.
 * @param string $tamano  Tamaño registrado.
 * @param string $clase   Clase del contenedor.
 * @return string
 */
function asp_imagen_destacada( int $post_id, string $tamano = 'asp-tarjeta', string $clase = 'asp-imagen' ): string {
	if ( ! has_post_thumbnail( $post_id ) ) {
		return '';
	}
	$img = get_the_post_thumbnail( $post_id, $tamano, [ 'loading' => 'lazy', 'alt' => '' ] );
	if ( ! $img ) {
		return '';
	}
	return '<span class="' . esc_attr( $clase ) . '">' . $img . '</span>';
}

/**
 * Fecha de un artículo en castellano: "3 de marzo de 2025".
 *
 * @param int $post_id ID del artículo.
 * @return string
 */
function asp_fecha_articulo( int $post_id ): string {
	$ts = get_post_timestamp( $post_id );
	if ( ! $ts ) {
		return '';
	}
	$ymd = wp_date( 'Ymd', $ts );
	$p   = asp_fecha_partes( (string) $ymd );
	return $p ? sprintf( __( '%1$d de %2$s de %3$d', 'asp' ), $p['dia'], asp_nombre_mes( $p['mes'] ), $p['anio'] ) : '';
}

/**
 * Datos JSON-LD schema.org/Event de un evento, para single-evento.php.
 *
 * @param int $post_id ID del evento.
 * @return array<string, mixed>
 */
function asp_evento_schema( int $post_id ): array {
	$inicio = asp_fecha_iso( (string) get_post_meta( $post_id, 'evento_fecha_inicio', true ) );
	$fin    = asp_fecha_iso( (string) get_post_meta( $post_id, 'evento_fecha_fin', true ) );
	$estado = asp_evento_estado( $post_id );

	$schema = [
		'@context'            => 'https://schema.org',
		'@type'               => 'Event',
		'name'                => get_the_title( $post_id ),
		'url'                 => get_permalink( $post_id ),
		'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
		'eventStatus'         => 'https://schema.org/EventScheduled',
	];
	if ( $inicio ) {
		$schema['startDate'] = $inicio;
	}
	if ( $fin ) {
		$schema['endDate'] = $fin;
	}
	$ciudad = asp_evento_ciudad( $post_id );
	$pais   = asp_evento_pais( $post_id );
	if ( $ciudad || $pais ) {
		$lugar = [
			'@type'   => 'Place',
			'name'    => (string) get_post_meta( $post_id, 'evento_sede_nombre', true ) ?: $ciudad,
			'address' => [
				'@type'           => 'PostalAddress',
				'addressLocality' => $ciudad,
				'addressCountry'  => $pais,
			],
		];
		$direccion = (string) get_post_meta( $post_id, 'evento_sede_direccion', true );
		if ( $direccion ) {
			$lugar['address']['streetAddress'] = $direccion;
		}
		$schema['location'] = $lugar;
	}
	$flyer_id = absint( get_post_meta( $post_id, 'evento_flyer', true ) );
	if ( $flyer_id ) {
		$schema['image'] = wp_get_attachment_image_url( $flyer_id, 'full' );
	}
	$descripcion = wp_strip_all_tags( (string) get_post_meta( $post_id, 'evento_descripcion', true ) );
	if ( $descripcion ) {
		$schema['description'] = $descripcion;
	}
	$oradores = asp_evento_relacionados( $post_id, 'evento_oradores', 'persona' );
	if ( $oradores ) {
		$schema['performer'] = array_map(
			static fn( WP_Post $p ) => [ '@type' => 'Person', 'name' => get_the_title( $p ) ],
			$oradores
		);
	}
	if ( 'abierta' === $estado && asp_evento_url_registro( $post_id ) ) {
		$schema['offers'] = [
			'@type'        => 'Offer',
			'url'          => asp_evento_url_registro( $post_id ),
			'availability' => 'https://schema.org/InStock',
		];
	} elseif ( 'agotado' === $estado ) {
		$schema['offers'] = [ '@type' => 'Offer', 'availability' => 'https://schema.org/SoldOut' ];
	}
	$schema['organizer'] = [ '@type' => 'Organization', 'name' => get_bloginfo( 'name' ), 'url' => home_url( '/' ) ];
	return $schema;
}

/**
 * Clases "actual" del menú: WordPress marca la página de entradas como
 * current_page_parent en cualquier ficha de CPT. Se corrige y se marca el
 * archivo de eventos/iniciativas cuando se ve una ficha de ese tipo.
 *
 * @param string[] $clases Clases del ítem.
 * @param WP_Post  $item   Ítem de menú.
 * @return string[]
 */
function asp_menu_clases( array $clases, $item ): array {
	$es_recursos = is_home() || is_singular( 'post' ) || is_category() || is_tag() || is_tax( 'serie' ) || is_author();
	if ( ! $es_recursos ) {
		$clases = array_diff( $clases, [ 'current_page_parent' ] );
	}
	if ( 'post_type_archive' === $item->type && is_singular( $item->object ) ) {
		$clases[] = 'current-menu-ancestor';
	}
	return array_values( array_unique( $clases ) );
}
add_filter( 'nav_menu_css_class', 'asp_menu_clases', 10, 2 );

/**
 * Las primeras N oraciones de un texto plano, para citas de cierre.
 *
 * @param string $texto Texto sin HTML.
 * @param int    $n     Cantidad de oraciones.
 * @return string
 */
function asp_primeras_oraciones( string $texto, int $n = 2 ): string {
	$texto = trim( preg_replace( '/\s+/', ' ', $texto ) );
	if ( '' === $texto ) {
		return '';
	}
	$partes = preg_split( '/(?<=[.!?])\s+/u', $texto, $n + 1 );
	return implode( ' ', array_slice( $partes, 0, $n ) );
}

/**
 * Clase en <body> cuando la portada tiene fotografía: la cabecera arranca
 * transparente sobre ella.
 *
 * @param string[] $clases Clases.
 * @return string[]
 */
function asp_body_class_portada( array $clases ): array {
	if ( is_front_page() && absint( get_theme_mod( 'asp_hero_imagen', 0 ) ) ) {
		$clases[] = 'asp-con-portada';
	}
	return $clases;
}
add_filter( 'body_class', 'asp_body_class_portada' );
