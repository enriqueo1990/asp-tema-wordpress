<?php
/**
 * SEO básico sin plugin: descripción, Open Graph y Twitter para la vista
 * previa al compartir (la mayor parte del tráfico llega de Instagram y
 * WhatsApp), títulos de pestaña más precisos, datos estructurados que no
 * son de eventos (Organization, Article) y cierre de las vías que exponían
 * usuarios o generaban páginas sin sentido.
 *
 * Todo se arma con datos cargados. Si falta un texto, la etiqueta no se
 * imprime: nunca se inventa una descripción (regla 7).
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

/* ------------------------------------------------------------------------
   Datos de la página actual para compartir
   --------------------------------------------------------------------- */

/**
 * Texto plano recortado a una longitud razonable para una vista previa.
 *
 * @param string $texto Texto o HTML.
 * @param int    $palabras Máximo de palabras.
 * @return string
 */
function asp_seo_recortar( string $texto, int $palabras = 32 ): string {
	$plano = trim( preg_replace( '/\s+/u', ' ', wp_strip_all_tags( strip_shortcodes( $texto ) ) ) );
	return '' === $plano ? '' : wp_trim_words( $plano, $palabras, '…' );
}

/**
 * Imagen para compartir a partir de un adjunto.
 *
 * @param int    $id     Adjunto.
 * @param string $tamano Tamaño registrado.
 * @return array{url:string,ancho:int,alto:int}|null
 */
function asp_seo_imagen_adjunto( int $id, string $tamano = 'large' ): ?array {
	if ( ! $id ) {
		return null;
	}
	$src = wp_get_attachment_image_src( $id, $tamano );
	if ( ! $src ) {
		return null;
	}
	return [ 'url' => (string) $src[0], 'ancho' => (int) $src[1], 'alto' => (int) $src[2] ];
}

/**
 * Miniatura de un video de YouTube para compartir. La grande (1280×720) no
 * existe en todos los videos, así que se consulta una vez por video y se
 * guarda; si no está, la chica (480×360, con franjas negras).
 *
 * @param string $yt_id Id del video.
 * @return array{url:string,ancho:int,alto:int}
 */
function asp_seo_miniatura_youtube( string $yt_id ): array {
	$base  = 'https://i.ytimg.com/vi/' . rawurlencode( $yt_id ) . '/';
	$chica = [ 'url' => $base . 'hqdefault.jpg', 'ancho' => 480, 'alto' => 360 ];
	$clave = 'asp_yt_miniatura_' . $yt_id;
	$cache = get_transient( $clave );
	if ( false === $cache ) {
		$respuesta = wp_safe_remote_head( $base . 'maxresdefault.jpg', [ 'timeout' => 5, 'redirection' => 0 ] );
		if ( is_wp_error( $respuesta ) ) {
			/* YouTube no respondió: la chica por ahora y se reintenta pronto. */
			set_transient( $clave, 'chica', 10 * MINUTE_IN_SECONDS );
			return $chica;
		}
		$cache = 200 === (int) wp_remote_retrieve_response_code( $respuesta ) ? 'grande' : 'chica';
		set_transient( $clave, $cache, MONTH_IN_SECONDS );
	}
	return 'grande' === $cache ? [ 'url' => $base . 'maxresdefault.jpg', 'ancho' => 1280, 'alto' => 720 ] : $chica;
}

/**
 * Saca el nombre del sitio y la bajada de las partes del título.
 *
 * @param array<string,string> $partes Partes del título.
 * @return array<string,string>
 */
function asp_seo_titulo_sin_sitio( array $partes ): array {
	unset( $partes['site'], $partes['tagline'] );
	return $partes;
}

/**
 * Título, descripción, imagen y tipo de la página actual.
 *
 * @return array{titulo:string,descripcion:string,imagen:?array,tipo:string,url:string}
 */
function asp_seo_datos(): array {
	static $datos = null;
	if ( null !== $datos ) {
		return $datos;
	}

	/* Título para compartir: el de la pestaña sin " – Ante Su Palabra",
	   que ya va en og:site_name. En el inicio queda entero. */
	if ( is_front_page() ) {
		$titulo = wp_get_document_title();
	} else {
		add_filter( 'document_title_parts', 'asp_seo_titulo_sin_sitio', 99 );
		$titulo = wp_get_document_title();
		remove_filter( 'document_title_parts', 'asp_seo_titulo_sin_sitio', 99 );
	}
	$descripcion = '';
	$imagen      = null;
	$tipo        = 'website';
	$url         = '';

	if ( is_front_page() ) {
		$descripcion = asp_seo_recortar( (string) get_theme_mod( 'asp_mision_texto', asp_mision_default() ) );
		/* Imagen armada para compartir, con logo y lema: la de Personalizar
		   o la que viene con el tema (assets/img/og-inicio.jpg). */
		$imagen      = asp_seo_imagen_adjunto( absint( get_theme_mod( 'asp_compartir_imagen', 0 ) ), 'full' )
			?? [ 'url' => asp_asset_url( 'assets/img/og-inicio.jpg' ), 'ancho' => 1200, 'alto' => 630 ];
		$url         = home_url( '/' );
	} elseif ( is_singular() ) {
		$id  = (int) get_queried_object_id();
		$url = (string) get_permalink( $id );
		switch ( get_post_type( $id ) ) {
			case 'evento':
				$lugar       = implode( ', ', array_filter( [ asp_evento_ciudad( $id ), asp_evento_pais( $id ) ] ) );
				$cabeza      = implode( ' · ', array_filter( [ asp_evento_fecha_texto( $id ), $lugar ] ) );
				$cuerpo      = asp_seo_recortar( (string) get_post_meta( $id, 'evento_descripcion', true ), 24 );
				$descripcion = trim( ( $cabeza ? $cabeza . '. ' : '' ) . $cuerpo );
				$imagen      = asp_seo_imagen_adjunto( absint( get_post_meta( $id, 'evento_flyer', true ) ) )
					?? asp_seo_imagen_adjunto( absint( get_post_meta( $id, 'evento_foto', true ) ) );
				break;
			case 'predicacion':
				$orador      = absint( get_post_meta( $id, 'predicacion_orador', true ) );
				$evento      = absint( get_post_meta( $id, 'predicacion_evento', true ) );
				$descripcion = implode(
					' · ',
					array_filter(
						[
							$orador ? get_the_title( $orador ) : '',
							(string) get_post_meta( $id, 'predicacion_pasaje', true ),
							$evento ? get_the_title( $evento ) : '',
						]
					)
				);
				$yt = asp_youtube_id( (string) get_post_meta( $id, 'predicacion_video_url', true ) );
				if ( '' !== $yt ) {
					$imagen = asp_seo_miniatura_youtube( $yt );
				}
				break;
			case 'persona':
				$descripcion = asp_seo_recortar( (string) get_post_meta( $id, 'persona_bio', true ) ) ?: asp_persona_cargo_iglesia( $id );
				$imagen      = asp_seo_imagen_adjunto( absint( get_post_meta( $id, 'persona_foto', true ) ) );
				break;
			case 'iniciativa':
				$descripcion = (string) get_post_meta( $id, 'iniciativa_bajada', true ) ?: asp_seo_recortar( (string) get_post_meta( $id, 'iniciativa_descripcion', true ) );
				$imagen      = asp_seo_imagen_adjunto( absint( get_post_meta( $id, 'iniciativa_imagen', true ) ) );
				if ( ! $imagen ) {
					$vitrina = asp_iniciativa_ediciones( $id )['vitrina'];
					$imagen  = $vitrina ? asp_seo_imagen_adjunto( absint( get_post_meta( $vitrina->ID, 'evento_flyer', true ) ) ) : null;
				}
				break;
			default:
				$post        = get_post( $id );
				$descripcion = $post ? asp_seo_recortar( has_excerpt( $post ) ? $post->post_excerpt : $post->post_content ) : '';
				$imagen      = asp_seo_imagen_adjunto( (int) get_post_thumbnail_id( $id ) );
				$tipo        = 'post' === get_post_type( $id ) ? 'article' : 'website';
		}
	} elseif ( is_post_type_archive() ) {
		$objeto      = get_queried_object();
		$descripcion = $objeto instanceof WP_Post_Type ? (string) $objeto->description : '';
		$url         = (string) get_post_type_archive_link( (string) get_query_var( 'post_type' ) );
	} elseif ( is_category() || is_tax() ) {
		$descripcion = asp_seo_recortar( (string) term_description() );
		$url         = (string) get_term_link( get_queried_object() );
	}

	if ( '' === $descripcion && ( is_home() || is_post_type_archive() ) ) {
		$descripcion = (string) get_bloginfo( 'description' );
	}
	/* Sin imagen propia: la fotografía general del inicio, que es real. */
	if ( ! $imagen ) {
		$imagen = asp_seo_imagen_adjunto( absint( get_theme_mod( 'asp_hero_imagen', 0 ) ) );
	}

	$datos = [
		'titulo'      => $titulo,
		'descripcion' => $descripcion,
		'imagen'      => $imagen,
		'tipo'        => $tipo,
		'url'         => is_wp_error( $url ) ? '' : $url,
	];
	return $datos;
}

/**
 * Imprime descripción, Open Graph y Twitter.
 *
 * @return void
 */
function asp_seo_meta(): void {
	if ( is_404() || is_search() || is_feed() ) {
		return;
	}
	$d = asp_seo_datos();

	if ( '' !== $d['descripcion'] ) {
		printf( '<meta name="description" content="%s">' . "\n", esc_attr( $d['descripcion'] ) );
	}
	printf( '<meta property="og:site_name" content="%s">' . "\n", esc_attr( get_bloginfo( 'name' ) ) );
	echo '<meta property="og:locale" content="es_AR">' . "\n";
	printf( '<meta property="og:type" content="%s">' . "\n", esc_attr( $d['tipo'] ) );
	printf( '<meta property="og:title" content="%s">' . "\n", esc_attr( $d['titulo'] ) );
	if ( '' !== $d['descripcion'] ) {
		printf( '<meta property="og:description" content="%s">' . "\n", esc_attr( $d['descripcion'] ) );
	}
	if ( '' !== $d['url'] ) {
		printf( '<meta property="og:url" content="%s">' . "\n", esc_url( $d['url'] ) );
	}
	if ( $d['imagen'] ) {
		printf( '<meta property="og:image" content="%s">' . "\n", esc_url( $d['imagen']['url'] ) );
		printf( '<meta property="og:image:width" content="%d">' . "\n", $d['imagen']['ancho'] );
		printf( '<meta property="og:image:height" content="%d">' . "\n", $d['imagen']['alto'] );
	}
	printf( '<meta name="twitter:card" content="%s">' . "\n", $d['imagen'] ? 'summary_large_image' : 'summary' );
}
add_action( 'wp_head', 'asp_seo_meta', 5 );

/**
 * Canonical en archivos y taxonomías: el core solo lo pone en singulares.
 * Así las variantes con parámetros (?buscar=, utm_…) apuntan a la URL limpia.
 *
 * @return void
 */
function asp_seo_canonical_archivos(): void {
	if ( is_singular() || is_front_page() || is_search() || is_404() || is_feed() ) {
		return;
	}
	$url = '';
	if ( is_home() && get_query_var( 'asp_articulos' ) ) {
		$url = asp_url_articulos();
	} elseif ( is_home() ) {
		$url = asp_url_recursos();
	} elseif ( is_post_type_archive() ) {
		$url = (string) get_post_type_archive_link( (string) get_query_var( 'post_type' ) );
	} elseif ( is_category() || is_tax() ) {
		$link = get_term_link( get_queried_object() );
		$url  = is_wp_error( $link ) ? '' : (string) $link;
	}
	if ( '' === $url ) {
		return;
	}
	$pagina = (int) get_query_var( 'paged' );
	if ( $pagina > 1 ) {
		$url = trailingslashit( $url ) . user_trailingslashit( 'page/' . $pagina, 'paged' );
	}
	printf( '<link rel="canonical" href="%s">' . "\n", esc_url( $url ) );
}
add_action( 'wp_head', 'asp_seo_canonical_archivos', 6 );

/**
 * Búsquedas dentro de Predicaciones (?buscar=) sin indexar: son infinitas
 * variantes de la misma página.
 *
 * @param array<string,bool|string> $robots Directivas.
 * @return array<string,bool|string>
 */
function asp_seo_robots( array $robots ): array {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( isset( $_GET['buscar'] ) ) {
		$robots['noindex'] = true;
		$robots['follow']  = true;
	}
	return $robots;
}
add_filter( 'wp_robots', 'asp_seo_robots' );

/* ------------------------------------------------------------------------
   Títulos de pestaña
   --------------------------------------------------------------------- */

/**
 * Evento con ciudad y año (había tres «Eclesiastés» iguales en la pestaña y
 * en Google) y el listado de artículos con su nombre.
 *
 * @param array<string,string> $partes Partes del título.
 * @return array<string,string>
 */
function asp_seo_titulo( array $partes ): array {
	if ( is_singular( 'evento' ) ) {
		$id    = (int) get_queried_object_id();
		$extra = trim( asp_evento_ciudad( $id ) . ' ' . ( asp_evento_anio( $id ) ?: '' ) );
		if ( '' !== $extra ) {
			$partes['title'] = get_the_title( $id ) . ' · ' . $extra;
		}
	} elseif ( is_home() && get_query_var( 'asp_articulos' ) ) {
		$partes['title'] = __( 'Artículos', 'asp' );
	}
	return $partes;
}
add_filter( 'document_title_parts', 'asp_seo_titulo' );

/* ------------------------------------------------------------------------
   Datos estructurados que no son de eventos
   --------------------------------------------------------------------- */

/**
 * Organization en el inicio y Article en cada artículo.
 *
 * @return void
 */
function asp_seo_schema(): void {
	$schema = null;
	if ( is_front_page() ) {
		$schema = [
			'@context' => 'https://schema.org',
			'@type'    => 'Organization',
			'name'     => get_bloginfo( 'name' ),
			'url'      => home_url( '/' ),
			'email'    => asp_email(),
			'sameAs'   => array_values( asp_redes() ),
		];
	} elseif ( is_singular( 'post' ) ) {
		$id     = (int) get_queried_object_id();
		$autor  = asp_persona_de_usuario( (int) get_post_field( 'post_author', $id ) );
		$schema = [
			'@context'         => 'https://schema.org',
			'@type'            => 'Article',
			'headline'         => get_the_title( $id ),
			'datePublished'    => get_the_date( 'c', $id ),
			'dateModified'     => get_the_modified_date( 'c', $id ),
			'mainEntityOfPage' => get_permalink( $id ),
			'publisher'        => [ '@type' => 'Organization', 'name' => get_bloginfo( 'name' ), 'url' => home_url( '/' ) ],
		];
		if ( $autor ) {
			$schema['author'] = [ '@type' => 'Person', 'name' => get_the_title( $autor ), 'url' => get_permalink( $autor ) ];
		}
		$imagen = get_the_post_thumbnail_url( $id, 'large' );
		if ( $imagen ) {
			$schema['image'] = $imagen;
		}
	}
	if ( $schema ) {
		echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
	}
}
add_action( 'wp_head', 'asp_seo_schema', 20 );

/**
 * Código ISO del país de un evento, para schema.org.
 *
 * @param int $post_id Evento.
 * @return string Código de dos letras, o el nombre si no se conoce.
 */
function asp_evento_pais_iso( int $post_id ): string {
	$terminos = get_the_terms( $post_id, 'pais' );
	$slug     = ( is_array( $terminos ) && $terminos ) ? $terminos[0]->slug : '';
	$codigos  = [ 'argentina' => 'AR', 'estados-unidos' => 'US' ];
	return $codigos[ $slug ] ?? asp_evento_pais( $post_id );
}

/* ------------------------------------------------------------------------
   Lo que exponía usuarios o generaba páginas sin sentido
   --------------------------------------------------------------------- */

/**
 * Archivos de autor: van a la ficha de la persona vinculada o dan 404.
 * Dejaban a la vista el usuario del administrador («asp») y duplicaban las
 * fichas de persona.
 *
 * @return void
 */
function asp_seo_autores(): void {
	if ( ! is_author() ) {
		return;
	}
	$usuario = get_queried_object();
	$persona = $usuario instanceof WP_User ? asp_persona_de_usuario( $usuario->ID ) : null;
	if ( $persona ) {
		wp_safe_redirect( get_permalink( $persona ), 301 );
		exit;
	}
	global $wp_query;
	$wp_query->set_404();
	status_header( 404 );
	nocache_headers();
}
add_action( 'template_redirect', 'asp_seo_autores', 5 );

/**
 * Sin usuarios en el sitemap.
 *
 * @param WP_Sitemaps_Provider|false $proveedor Proveedor.
 * @param string                     $nombre    Nombre.
 * @return WP_Sitemaps_Provider|false
 */
function asp_seo_sitemap_sin_usuarios( $proveedor, string $nombre ) {
	return 'users' === $nombre ? false : $proveedor;
}
add_filter( 'wp_sitemaps_add_provider', 'asp_seo_sitemap_sin_usuarios', 10, 2 );

/**
 * Sin la taxonomía país en el sitemap: no tiene páginas propias.
 *
 * @param array<string,WP_Taxonomy> $taxonomias Taxonomías.
 * @return array<string,WP_Taxonomy>
 */
function asp_seo_sitemap_taxonomias( array $taxonomias ): array {
	unset( $taxonomias['pais'] );
	return $taxonomias;
}
add_filter( 'wp_sitemaps_taxonomies', 'asp_seo_sitemap_taxonomias' );

/**
 * La lista de usuarios de la API REST, solo para quien está logueado.
 *
 * @param array<string,mixed> $rutas Rutas.
 * @return array<string,mixed>
 */
function asp_seo_rest_sin_usuarios( array $rutas ): array {
	if ( ! is_user_logged_in() ) {
		unset( $rutas['/wp/v2/users'], $rutas['/wp/v2/users/(?P<id>[\d]+)'] );
	}
	return $rutas;
}
add_filter( 'rest_endpoints', 'asp_seo_rest_sin_usuarios' );

/**
 * En los feeds, el autor es el ministerio y no el usuario que cargó.
 *
 * @param string|null $autor Nombre.
 * @return string|null
 */
function asp_seo_autor_feed( $autor ) {
	return is_feed() ? get_bloginfo( 'name' ) : $autor;
}
add_filter( 'the_author', 'asp_seo_autor_feed' );

/**
 * Sin versión de WordPress a la vista.
 */
remove_action( 'wp_head', 'wp_generator' );
add_filter( 'the_generator', '__return_empty_string' );

/**
 * Comentarios y pings cerrados: el tema no los muestra y el panel no los
 * tiene, así que solo juntaban spam sin que nadie lo viera.
 */
add_filter( 'comments_open', '__return_false', 20 );
add_filter( 'pings_open', '__return_false', 20 );
add_filter( 'feed_links_show_comments_feed', '__return_false' );

/**
 * Sin cabecera X-Pingback.
 *
 * @param array<string,string> $cabeceras Cabeceras.
 * @return array<string,string>
 */
function asp_seo_sin_pingback( array $cabeceras ): array {
	unset( $cabeceras['X-Pingback'] );
	return $cabeceras;
}
add_filter( 'wp_headers', 'asp_seo_sin_pingback' );

/**
 * Páginas que responden sin tener contenido propio:
 * - /eventos/page/2/, /iniciativas/page/2/, /recursos/predicaciones/page/2/:
 *   esos archivos no se paginan y repetían la primera página.
 * - Feeds de eventos e iniciativas: salían sin descripción y con la fecha
 *   de carga en vez de la del evento. Van al archivo.
 * - La categoría por defecto vacía («Uncategorized»): va a Artículos.
 *
 * @return void
 */
function asp_seo_paginas_vacias(): void {
	$sin_paginar = [ 'evento', 'iniciativa', 'predicacion' ];
	if ( is_feed() && is_post_type_archive( [ 'evento', 'iniciativa' ] ) ) {
		wp_safe_redirect( (string) get_post_type_archive_link( (string) get_query_var( 'post_type' ) ), 301 );
		exit;
	}
	if ( is_paged() && is_post_type_archive( $sin_paginar ) ) {
		global $wp_query;
		$wp_query->set_404();
		status_header( 404 );
		nocache_headers();
		return;
	}
	if ( is_category() ) {
		$cat = get_queried_object();
		if ( $cat instanceof WP_Term && (int) get_option( 'default_category' ) === $cat->term_id && 0 === (int) $cat->count ) {
			wp_safe_redirect( asp_url_articulos(), 301 );
			exit;
		}
	}
}
add_action( 'template_redirect', 'asp_seo_paginas_vacias', 5 );

/* ------------------------------------------------------------------------
   Ícono del sitio
   --------------------------------------------------------------------- */

/**
 * Sin ícono cargado en Personalizar, el símbolo del logo. Evita el 404 de
 * /favicon.ico (el core redirige ahí al ícono del sitio).
 *
 * @param string $url URL del ícono cargado.
 * @return string
 */
function asp_seo_icono( string $url ): string {
	/* Sin ícono propio, $url viene vacío o con el logo de WordPress que el
	   core usa de respaldo para /favicon.ico. */
	return get_option( 'site_icon' ) ? $url : asp_asset_url( 'assets/img/icono.svg' );
}
add_filter( 'get_site_icon_url', 'asp_seo_icono' );
