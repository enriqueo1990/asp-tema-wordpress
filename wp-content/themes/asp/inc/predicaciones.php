<?php
/**
 * Predicaciones: consultas y helpers de render (fecha efectiva, formato,
 * reproductor). Video por oEmbed del core (YouTube), audio con <audio>
 * nativo, texto con el editor. Sin plugins.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

/**
 * Consulta base de predicaciones publicadas, más recientes primero por
 * fecha efectiva (propia o del evento), resuelta en PHP porque la fecha
 * puede venir de dos lugares.
 *
 * @param array<string,mixed> $args Args extra de WP_Query (meta_query, etc.).
 * @param int                 $cantidad -1 para todas.
 * @return WP_Post[]
 */
function asp_predicaciones( array $args = [], int $cantidad = -1 ): array {
	$query = new WP_Query(
		array_merge(
			[
				'post_type'      => 'predicacion',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'no_found_rows'  => true,
			],
			$args
		)
	);
	$posts = $query->posts;
	usort(
		$posts,
		static fn( WP_Post $a, WP_Post $b ) => strcmp( asp_predicacion_fecha_ymd( $b->ID ), asp_predicacion_fecha_ymd( $a->ID ) ) ?: ( $b->ID <=> $a->ID )
	);
	return $cantidad > 0 ? array_slice( $posts, 0, $cantidad ) : $posts;
}

/**
 * Predicaciones de un evento, en orden de carga (menu_order, luego título).
 *
 * @param int $evento_id ID del evento.
 * @return WP_Post[]
 */
function asp_predicaciones_de_evento( int $evento_id ): array {
	$query = new WP_Query(
		[
			'post_type'      => 'predicacion',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'no_found_rows'  => true,
			'orderby'        => [ 'menu_order' => 'ASC', 'date' => 'ASC' ],
			'meta_query'     => [ [ 'key' => 'predicacion_evento', 'value' => $evento_id ] ],
		]
	);
	return $query->posts;
}

/**
 * Predicaciones de una persona.
 *
 * @param int $persona_id ID de la persona.
 * @return WP_Post[]
 */
function asp_predicaciones_de_persona( int $persona_id ): array {
	return asp_predicaciones( [ 'meta_query' => [ [ 'key' => 'predicacion_orador', 'value' => $persona_id ] ] ] );
}

/**
 * Predicaciones agrupadas por año descendente.
 *
 * @return array<int, WP_Post[]>
 */
function asp_predicaciones_por_anio(): array {
	$grupos = [];
	foreach ( asp_predicaciones() as $p ) {
		$partes = asp_fecha_partes( asp_predicacion_fecha_ymd( $p->ID ) );
		$grupos[ $partes ? $partes['anio'] : 0 ][] = $p;
	}
	krsort( $grupos );
	return $grupos;
}

/**
 * Fecha efectiva Ymd: la propia, o la de inicio del evento, o la de carga.
 *
 * @param int $post_id ID.
 * @return string
 */
function asp_predicacion_fecha_ymd( int $post_id ): string {
	$propia = (string) get_post_meta( $post_id, 'predicacion_fecha', true );
	if ( $propia ) {
		return $propia;
	}
	$evento = absint( get_post_meta( $post_id, 'predicacion_evento', true ) );
	if ( $evento ) {
		$del_evento = (string) get_post_meta( $evento, 'evento_fecha_inicio', true );
		if ( $del_evento ) {
			return $del_evento;
		}
	}
	return (string) get_the_date( 'Ymd', $post_id );
}

/**
 * Fecha efectiva en castellano.
 *
 * @param int $post_id ID.
 * @return string
 */
function asp_predicacion_fecha_texto( int $post_id ): string {
	$p = asp_fecha_partes( asp_predicacion_fecha_ymd( $post_id ) );
	return $p ? sprintf( __( '%1$d de %2$s de %3$d', 'asp' ), $p['dia'], asp_nombre_mes( $p['mes'] ), $p['anio'] ) : '';
}

/**
 * Formato efectivo: el cargado, o el que se deduce de lo que hay.
 *
 * @param int $post_id ID.
 * @return string video|audio|texto
 */
function asp_predicacion_tipo( int $post_id ): string {
	$tipo = (string) get_post_meta( $post_id, 'predicacion_tipo', true );
	if ( 'video' === $tipo && get_post_meta( $post_id, 'predicacion_video_url', true ) ) {
		return 'video';
	}
	if ( 'audio' === $tipo && get_post_meta( $post_id, 'predicacion_audio_url', true ) ) {
		return 'audio';
	}
	return 'texto';
}

/**
 * Etiqueta corta del formato.
 *
 * @param string $tipo Formato.
 * @return string
 */
function asp_predicacion_tipo_etiqueta( string $tipo ): string {
	$e = [ 'video' => __( 'Video', 'asp' ), 'audio' => __( 'Audio', 'asp' ), 'texto' => __( 'Texto', 'asp' ) ];
	return $e[ $tipo ] ?? $e['texto'];
}

/**
 * Evento de la predicación, si tiene.
 *
 * @param int $post_id ID.
 * @return WP_Post|null
 */
function asp_predicacion_evento( int $post_id ): ?WP_Post {
	$id = absint( get_post_meta( $post_id, 'predicacion_evento', true ) );
	$p  = $id ? get_post( $id ) : null;
	return ( $p && 'evento' === $p->post_type && 'publish' === $p->post_status ) ? $p : null;
}

/**
 * Orador de la predicación, si tiene.
 *
 * @param int $post_id ID.
 * @return WP_Post|null
 */
function asp_predicacion_orador( int $post_id ): ?WP_Post {
	$id = absint( get_post_meta( $post_id, 'predicacion_orador', true ) );
	$p  = $id ? get_post( $id ) : null;
	return ( $p && 'persona' === $p->post_type && 'publish' === $p->post_status ) ? $p : null;
}

/**
 * Reproductor: iframe de YouTube por oEmbed, <audio> nativo, o nada (texto).
 *
 * @param int $post_id ID.
 * @return string HTML.
 */
function asp_predicacion_reproductor( int $post_id ): string {
	$tipo = asp_predicacion_tipo( $post_id );
	if ( 'video' === $tipo ) {
		$url   = (string) get_post_meta( $post_id, 'predicacion_video_url', true );
		$yt_id = asp_youtube_id( $url );
		$embed = $yt_id ? asp_youtube_iframe( $yt_id, get_the_title( $post_id ) ) : asp_oembed_cacheado( $url );
		if ( ! $embed ) {
			return '<p class="asp-reproductor__enlace"><a href="' . esc_url( $url ) . '" rel="noopener" target="_blank">' . esc_html__( 'Ver el video', 'asp' ) . '</a></p>';
		}
		return '<div class="asp-reproductor asp-reproductor--video">' . $embed . '</div>';
	}
	if ( 'audio' === $tipo ) {
		$url = (string) get_post_meta( $post_id, 'predicacion_audio_url', true );
		return '<div class="asp-reproductor asp-reproductor--audio"><audio controls preload="none" src="' . esc_url( $url ) . '"><a href="' . esc_url( $url ) . '">' . esc_html__( 'Descargar el audio', 'asp' ) . '</a></audio></div>';
	}
	return '';
}

/**
 * oEmbed con caché de una semana por URL: YouTube se consulta una vez, no en
 * cada visita. Si falla, se guarda un vacío corto para no reintentar en
 * cada carga.
 *
 * @param string $url URL del video.
 * @return string HTML del embed o cadena vacía.
 */
function asp_oembed_cacheado( string $url ): string {
	if ( '' === $url ) {
		return '';
	}
	$clave = 'asp_oembed_' . md5( $url );
	$cache = get_transient( $clave );
	if ( is_string( $cache ) ) {
		return $cache;
	}
	add_filter( 'oembed_remote_get_args', 'asp_oembed_timeout_corto' );
	$html = wp_oembed_get( $url, [ 'width' => 1280 ] );
	remove_filter( 'oembed_remote_get_args', 'asp_oembed_timeout_corto' );
	$html = is_string( $html ) ? $html : '';
	set_transient( $clave, $html, $html ? WEEK_IN_SECONDS : 10 * MINUTE_IN_SECONDS );
	return $html;
}

/**
 * Tiempo de espera corto para el pedido a YouTube.
 *
 * @param array<string,mixed> $args Args de wp_remote_get.
 * @return array<string,mixed>
 */
function asp_oembed_timeout_corto( array $args ): array {
	$args['timeout'] = 5;
	return $args;
}

/**
 * Id de un video de YouTube a partir de cualquiera de sus URLs.
 *
 * @param string $url URL.
 * @return string Id de 11 caracteres o cadena vacía.
 */
function asp_youtube_id( string $url ): string {
	$host = strtolower( (string) wp_parse_url( $url, PHP_URL_HOST ) );
	if ( ! str_contains( $host, 'youtube.com' ) && ! str_contains( $host, 'youtu.be' ) ) {
		return '';
	}
	if ( preg_match( '#(?:v=|/embed/|/shorts/|/live/|youtu\.be/)([\w-]{11})#', $url, $m ) ) {
		return $m[1];
	}
	return '';
}

/**
 * Iframe de YouTube sin cookies, armado en el tema: no hay ningún pedido
 * a YouTube desde el servidor.
 *
 * @param string $yt_id  Id del video.
 * @param string $titulo Título accesible.
 * @return string
 */
function asp_youtube_iframe( string $yt_id, string $titulo ): string {
	return sprintf(
		'<iframe src="%1$s" title="%2$s" loading="lazy" allow="accelerometer; encrypted-media; picture-in-picture; web-share" allowfullscreen referrerpolicy="strict-origin-when-cross-origin"></iframe>',
		esc_url( 'https://www.youtube-nocookie.com/embed/' . $yt_id ),
		esc_attr( $titulo )
	);
}
