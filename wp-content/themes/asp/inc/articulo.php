<?php
/**
 * Artículo individual: normalización del cuerpo importado, índice de
 * subtítulos, serie, autor, compartir y relacionados.
 *
 * El cuerpo de los artículos migrados del blog viejo trae marcas de
 * Gutenberg y del editor clásico mezcladas: subtítulos en h4, listas
 * numeradas escritas como párrafos con <br>, la fuente como un párrafo más.
 * Acá se corrige solo la forma, al imprimir: el texto no se toca y la base
 * queda como vino (regla 7 de CLAUDE.md).
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

/**
 * Ancla estable para un subtítulo: "sec-" + su texto en minúsculas.
 *
 * @param string   $texto  Texto del subtítulo, con o sin HTML.
 * @param string[] $usadas Anclas ya asignadas en el mismo artículo.
 * @return string
 */
function asp_articulo_ancla( string $texto, array $usadas ): string {
	$base  = 'sec-' . sanitize_title( wp_strip_all_tags( $texto ) );
	$ancla = $base;
	$n     = 2;
	while ( in_array( $ancla, $usadas, true ) ) {
		$ancla = $base . '-' . $n++;
	}
	return $ancla;
}

/**
 * Subtítulos h2–h4 de un HTML, con el ancla que les pone el filtro.
 *
 * @param string $html Cuerpo del artículo.
 * @return array<int, array{id:string,texto:string}>
 */
function asp_articulo_subtitulos( string $html ): array {
	if ( ! preg_match_all( '#<h([2-4])([^>]*)>(.*?)</h\1>#is', $html, $m, PREG_SET_ORDER ) ) {
		return [];
	}
	$items  = [];
	$usadas = [];
	foreach ( $m as $h ) {
		$texto = trim( html_entity_decode( wp_strip_all_tags( $h[3] ), ENT_QUOTES, 'UTF-8' ) );
		if ( '' === $texto ) {
			continue;
		}
		$id       = preg_match( '#\sid=["\']([^"\']+)["\']#i', $h[2], $mid ) ? $mid[1] : asp_articulo_ancla( $texto, $usadas );
		$usadas[] = $id;
		$items[]  = [ 'id' => $id, 'texto' => $texto ];
	}
	return $items;
}

/**
 * Índice "En este artículo": solo si hay tres subtítulos o más. Con menos,
 * un índice no ayuda a ubicarse y ocupa el lugar de la serie.
 *
 * @param int $post_id ID del artículo.
 * @return array<int, array{id:string,texto:string}>
 */
function asp_articulo_indice( int $post_id ): array {
	$items = asp_articulo_subtitulos( (string) get_post_field( 'post_content', $post_id ) );
	return count( $items ) >= 3 ? $items : [];
}

/**
 * Pone ancla a cada subtítulo h2–h4 que no tenga una.
 *
 * @param string $html Cuerpo.
 * @return string
 */
function asp_articulo_anclar_subtitulos( string $html ): string {
	$usadas = [];
	return (string) preg_replace_callback(
		'#<h([2-4])([^>]*)>(.*?)</h\1>#is',
		static function ( array $h ) use ( &$usadas ): string {
			if ( preg_match( '#\sid=["\']([^"\']+)["\']#i', $h[2], $mid ) ) {
				$usadas[] = $mid[1];
				return $h[0];
			}
			$texto = trim( html_entity_decode( wp_strip_all_tags( $h[3] ), ENT_QUOTES, 'UTF-8' ) );
			if ( '' === $texto ) {
				return $h[0];
			}
			$id       = asp_articulo_ancla( $texto, $usadas );
			$usadas[] = $id;
			return '<h' . $h[1] . $h[2] . ' id="' . esc_attr( $id ) . '">' . $h[3] . '</h' . $h[1] . '>';
		},
		$html
	);
}

/**
 * Un párrafo con líneas "<strong>1. …</strong>" separadas por <br> pasa a
 * ser un párrafo de entrada más una lista <ol>. El numeral lo dibuja la
 * lista; el texto de cada ítem queda igual.
 *
 * @param string $html Cuerpo.
 * @return string
 */
function asp_articulo_listas_numeradas( string $html ): string {
	return (string) preg_replace_callback(
		'#<p([^>]*)>(.*?)</p>#is',
		static function ( array $p ): string {
			$lineas = preg_split( '#<br\s*/?>\s*#i', $p[2] );
			if ( count( $lineas ) < 3 ) {
				return $p[0];
			}
			$patron = '#^\s*(<(strong|b)>)\s*(\d+)[.)]\s*#i';
			$intro  = [];
			$items  = [];
			$inicio = 0;
			foreach ( $lineas as $linea ) {
				if ( preg_match( $patron, $linea, $m ) ) {
					if ( ! $items ) {
						$inicio = (int) $m[3];
					}
					$items[] = preg_replace( $patron, '$1', $linea, 1 );
				} elseif ( $items ) {
					// Una línea sin número después de la lista: no es el patrón.
					return $p[0];
				} else {
					$intro[] = $linea;
				}
			}
			if ( count( $items ) < 2 ) {
				return $p[0];
			}
			$salida = $intro ? '<p' . $p[1] . '>' . implode( '<br>', $intro ) . '</p>' : '';
			$salida .= '<ol class="asp-lista-num"' . ( 1 !== $inicio ? ' start="' . absint( $inicio ) . '"' : '' ) . '>';
			foreach ( $items as $item ) {
				$salida .= '<li>' . $item . '</li>';
			}
			return $salida . '</ol>';
		},
		$html
	);
}

/**
 * Dos <br> seguidos dentro de un párrafo son, en el contenido importado, un
 * cambio de párrafo. Se parten para que todo el texto tenga el mismo aire.
 *
 * @param string $html Cuerpo.
 * @return string
 */
function asp_articulo_partir_parrafos( string $html ): string {
	return (string) preg_replace_callback(
		'#<p([^>]*)>(.*?)</p>#is',
		static function ( array $p ): string {
			$partes = preg_split( '#(?:\s*<br\s*/?>\s*){2,}#i', $p[2] );
			if ( count( $partes ) < 2 ) {
				return $p[0];
			}
			$partes = array_filter( $partes, static fn( $t ) => '' !== trim( $t ) );
			return implode( '', array_map( static fn( $t ) => '<p' . $p[1] . '>' . $t . '</p>', $partes ) );
		},
		$html
	);
}

/**
 * El último párrafo "(Tomado de …)" o "Fuente: …" se marca como fuente
 * para que tenga su propio estilo y no se lea como un párrafo más.
 *
 * @param string $html Cuerpo.
 * @return string
 */
function asp_articulo_marcar_fuente( string $html ): string {
	return (string) preg_replace_callback(
		'#<p([^>]*)>(\s*\(?\s*(?:Tomado|Tomada|Extra[ií]do|Publicado originalmente|Fuente)\b.*?)</p>(\s*)$#isu',
		static function ( array $p ): string {
			$attrs = $p[1];
			if ( preg_match( '#class=(["\'])(.*?)\1#i', $attrs ) ) {
				$attrs = preg_replace( '#class=(["\'])(.*?)\1#i', 'class="$2 asp-fuente"', $attrs, 1 );
			} else {
				$attrs .= ' class="asp-fuente"';
			}
			return '<p' . $attrs . '>' . $p[2] . '</p>' . $p[3];
		},
		$html
	);
}

/**
 * Filtro del cuerpo, solo en la vista del artículo individual.
 *
 * @param string $html Contenido ya pasado por wpautop y los bloques.
 * @return string
 */
function asp_articulo_filtrar_contenido( string $html ): string {
	if ( ! is_singular( 'post' ) || ! in_the_loop() || ! is_main_query() ) {
		return $html;
	}
	$html = asp_articulo_partir_parrafos( $html );
	$html = asp_articulo_listas_numeradas( $html );
	$html = asp_articulo_anclar_subtitulos( $html );
	return asp_articulo_marcar_fuente( $html );
}
add_filter( 'the_content', 'asp_articulo_filtrar_contenido', 20 );

/**
 * Serie del artículo con su posición y vecinos.
 *
 * @param int $post_id ID del artículo.
 * @return array{serie:?WP_Term,lista:WP_Post[],pos:int,anterior:?WP_Post,siguiente:?WP_Post}
 */
function asp_articulo_serie( int $post_id ): array {
	$serie = asp_serie_de_articulo( $post_id );
	$lista = $serie ? asp_articulos_de_serie( $serie->term_id ) : [];
	$pos   = 0;
	foreach ( $lista as $i => $p ) {
		if ( $p->ID === $post_id ) {
			$pos = $i + 1;
		}
	}
	return [
		'serie'     => $serie,
		'lista'     => $lista,
		'pos'       => $pos,
		'anterior'  => $pos > 1 ? $lista[ $pos - 2 ] : null,
		'siguiente' => ( $pos && $pos < count( $lista ) ) ? $lista[ $pos ] : null,
	];
}

/**
 * Bloque de autor al pie: foto, una línea y el enlace a sus otros
 * artículos. La línea es el comienzo de la bio o, si no hay, cargo e
 * iglesia. Sin ficha de persona queda solo el nombre.
 *
 * @param int $post_id ID del artículo.
 * @return array{nombre:string,url:string,persona:int,linea:string,otros:int}|null
 */
function asp_articulo_autor( int $post_id ): ?array {
	$user_id = (int) get_post_field( 'post_author', $post_id );
	$persona = asp_persona_de_usuario( $user_id );
	if ( $persona ) {
		$bio   = (string) get_post_meta( $persona->ID, 'persona_bio', true );
		$linea = '' !== trim( $bio ) ? asp_primeras_oraciones( $bio, 1 ) : asp_persona_cargo_iglesia( $persona->ID );
		return [
			'nombre'  => get_the_title( $persona ),
			'url'     => (string) get_permalink( $persona ),
			'persona' => $persona->ID,
			'linea'   => $linea,
			'otros'   => max( 0, count( asp_articulos_de_persona( $persona->ID ) ) - 1 ),
		];
	}
	$nombre = (string) get_the_author_meta( 'display_name', $user_id );
	if ( '' === $nombre ) {
		return null;
	}
	return [
		'nombre'  => $nombre,
		'url'     => (string) get_author_posts_url( $user_id ),
		'persona' => 0,
		'linea'   => '',
		'otros'   => max( 0, (int) count_user_posts( $user_id, 'post', true ) - 1 ),
	];
}

/**
 * Enlaces para compartir: copiar el enlace y WhatsApp, que es lo que más
 * se usa entre pastores de la región.
 *
 * @param int $post_id ID del artículo.
 * @return array{url:string,whatsapp:string}
 */
function asp_articulo_compartir( int $post_id ): array {
	$url    = (string) get_permalink( $post_id );
	$titulo = html_entity_decode( get_the_title( $post_id ), ENT_QUOTES, 'UTF-8' );
	return [
		'url'      => $url,
		'whatsapp' => 'https://wa.me/?text=' . rawurlencode( $titulo . ' ' . $url ),
	];
}

/**
 * Tres artículos para seguir leyendo: primero los de la misma serie,
 * después los del mismo autor y por último los de la misma categoría.
 *
 * @param int $post_id  ID del artículo.
 * @param int $cantidad Cuántos.
 * @return WP_Post[]
 */
function asp_articulos_relacionados( int $post_id, int $cantidad = 3 ): array {
	$elegidos = [];
	$excluir  = [ $post_id ];
	$base     = [
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	];

	$criterios = [];
	$serie     = asp_serie_de_articulo( $post_id );
	if ( $serie ) {
		$criterios[] = [ 'tax_query' => [ [ 'taxonomy' => 'serie', 'field' => 'term_id', 'terms' => $serie->term_id ] ], 'orderby' => 'date', 'order' => 'ASC' ];
	}
	$criterios[] = [ 'author' => (int) get_post_field( 'post_author', $post_id ) ];
	$cats        = array_diff( wp_get_post_categories( $post_id ), [ (int) get_option( 'default_category' ) ] );
	if ( $cats ) {
		$criterios[] = [ 'category__in' => array_values( $cats ) ];
	}

	foreach ( $criterios as $criterio ) {
		$faltan = $cantidad - count( $elegidos );
		if ( $faltan <= 0 ) {
			break;
		}
		$posts = get_posts( array_merge( $base, $criterio, [ 'posts_per_page' => $faltan, 'post__not_in' => $excluir ] ) );
		foreach ( $posts as $p ) {
			$elegidos[] = $p;
			$excluir[]  = $p->ID;
		}
	}
	return $elegidos;
}

/**
 * Foto de apertura con su epígrafe, si la imagen tiene uno cargado en la
 * biblioteca de medios.
 *
 * @param int $post_id ID del artículo.
 * @return string HTML listo para imprimir, o cadena vacía.
 */
function asp_articulo_apertura( int $post_id ): string {
	$img = asp_imagen_destacada( $post_id, 'asp-apertura', 'asp-imagen asp-imagen--apertura' );
	if ( '' === $img ) {
		return '';
	}
	$pie = trim( (string) wp_get_attachment_caption( (int) get_post_thumbnail_id( $post_id ) ) );
	return '<figure class="asp-articulo__apertura">' . $img
		. ( '' !== $pie ? '<figcaption class="asp-articulo__pie">' . esc_html( $pie ) . '</figcaption>' : '' )
		. '</figure>';
}
