<?php
/**
 * 301 del blog viejo (blog.antesupalabra.com) al sitio unificado.
 *
 * El mapa vive en redirects.csv (raíz del tema), una fila por URL:
 *   origen,destino
 * "origen" es la ruta (con o sin dominio); "destino" una ruta o URL.
 * Se aplica cuando la petición llega con el dominio del blog viejo o cuando
 * WordPress daría 404. Lo que no está en el CSV cae a la regla genérica:
 * /category/x/ → categoría x, /slug/ → /recursos/slug/ si existe.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

const ASP_BLOG_VIEJO = 'blog.antesupalabra.com';

/**
 * Mapa origen => destino, normalizado y cacheado por fecha del archivo.
 *
 * @return array<string,string>
 */
function asp_mapa_redirects(): array {
	$archivo = ASP_THEME_DIR . '/redirects.csv';
	if ( ! is_readable( $archivo ) ) {
		return [];
	}
	$mtime = (int) filemtime( $archivo );
	$cache = get_transient( 'asp_redirects' );
	if ( is_array( $cache ) && ( $cache['mtime'] ?? 0 ) === $mtime ) {
		return $cache['mapa'];
	}
	$mapa = [];
	$fh   = fopen( $archivo, 'r' ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	if ( $fh ) {
		while ( ( $fila = fgetcsv( $fh ) ) !== false ) {
			if ( count( $fila ) < 2 || str_starts_with( trim( (string) $fila[0] ), '#' ) || 'origen' === trim( (string) $fila[0] ) ) {
				continue;
			}
			$origen = asp_normalizar_ruta( (string) $fila[0] );
			if ( '' !== $origen ) {
				$mapa[ $origen ] = trim( (string) $fila[1] );
			}
		}
		fclose( $fh ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	}
	set_transient( 'asp_redirects', [ 'mtime' => $mtime, 'mapa' => $mapa ], DAY_IN_SECONDS );
	return $mapa;
}

/**
 * Ruta sin dominio, sin query, con barra final, en minúsculas.
 *
 * @param string $url Ruta o URL.
 * @return string
 */
function asp_normalizar_ruta( string $url ): string {
	$url  = trim( $url );
	$ruta = str_starts_with( $url, 'http' ) ? (string) wp_parse_url( $url, PHP_URL_PATH ) : $url;
	$ruta = strtolower( trim( (string) strtok( $ruta, '?' ) ) );
	$ruta = '/' . trim( $ruta, '/' ) . '/';
	return '//' === $ruta ? '/' : $ruta;
}

/**
 * Destino para una ruta del blog viejo, o cadena vacía.
 *
 * @param string $ruta Ruta normalizada.
 * @return string
 */
function asp_destino_redirect( string $ruta ): string {
	$mapa = asp_mapa_redirects();
	if ( isset( $mapa[ $ruta ] ) ) {
		$d = $mapa[ $ruta ];
		return str_starts_with( $d, 'http' ) ? $d : home_url( $d );
	}
	if ( preg_match( '#^/category/([^/]+)/$#', $ruta, $m ) ) {
		$term = get_term_by( 'slug', $m[1], 'category' );
		if ( $term ) {
			return (string) get_category_link( $term );
		}
		return asp_url_recursos();
	}
	if ( preg_match( '#^/([^/]+)/$#', $ruta, $m ) ) {
		$post = get_page_by_path( $m[1], OBJECT, 'post' );
		if ( $post && 'publish' === $post->post_status ) {
			return (string) get_permalink( $post );
		}
	}
	return '';
}

/**
 * Aplica el 301.
 *
 * @return void
 */
function asp_redirigir_blog_viejo(): void {
	$host = strtolower( (string) ( $_SERVER['HTTP_HOST'] ?? '' ) );
	$ruta = asp_normalizar_ruta( (string) ( $_SERVER['REQUEST_URI'] ?? '/' ) );
	$es_blog_viejo = ASP_BLOG_VIEJO === $host || 'www.' . ASP_BLOG_VIEJO === $host;

	if ( ! $es_blog_viejo && ! is_404() ) {
		return;
	}

	$destino = asp_destino_redirect( $ruta );
	if ( '' === $destino && $es_blog_viejo ) {
		$destino = asp_url_recursos();
	}
	if ( '' === $destino ) {
		return;
	}
	wp_redirect( $destino, 301 ); // phpcs:ignore WordPress.Security.SafeRedirect
	exit;
}
add_action( 'template_redirect', 'asp_redirigir_blog_viejo', 1 );
