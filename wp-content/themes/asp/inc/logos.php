<?php
/**
 * Página oculta de logos: /logos-asp/.
 *
 * Para los ministerios y las iglesias que arman flyers o presentaciones:
 * el logo completo y la cruz en cada color, en SVG y PNG, y un ZIP con
 * todo. No figura en ningún menú, no se indexa y no entra al sitemap; se
 * llega solo con el link. No es una página del panel: la sirve el tema,
 * así que nadie la puede borrar ni editar por error.
 *
 * Los archivos viven en assets/descargas/logos/ y los genera
 * tools/generar-logos.php a partir de los vectoriales del tema.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

/** Ruta de la página, sin barras. Cambiarla invalida el link que ya circula. */
const ASP_LOGOS_RUTA = 'logos-asp';

/** Sube este número cuando cambie la regla: se vacían las reglas una vez. */
const ASP_LOGOS_REGLAS = '1';

/**
 * Regla /logos-asp/.
 *
 * @return void
 */
function asp_logos_regla(): void {
	add_rewrite_rule( '^' . ASP_LOGOS_RUTA . '/?$', 'index.php?asp_logos=1', 'top' );
	if ( get_option( 'asp_logos_reglas' ) !== ASP_LOGOS_REGLAS ) {
		flush_rewrite_rules( false );
		update_option( 'asp_logos_reglas', ASP_LOGOS_REGLAS );
	}
}
add_action( 'init', 'asp_logos_regla' );

/**
 * @param string[] $vars Variables públicas.
 * @return string[]
 */
function asp_logos_query_vars( array $vars ): array {
	$vars[] = 'asp_logos';
	return $vars;
}
add_filter( 'query_vars', 'asp_logos_query_vars' );

/**
 * ¿Estamos en la página de logos?
 *
 * @return bool
 */
function asp_es_logos(): bool {
	return (bool) get_query_var( 'asp_logos' );
}

/**
 * Sin otra variable, WordPress la tomaría por la página de entradas
 * (Recursos): marcaría ese menú, pondría su canónica y consultaría artículos.
 *
 * @param WP_Query $q Consulta.
 * @return void
 */
function asp_logos_consulta( WP_Query $q ): void {
	if ( $q->is_main_query() && $q->get( 'asp_logos' ) ) {
		$q->is_home = false;
		$q->set( 'posts_per_page', 1 );
		$q->set( 'no_found_rows', true );
	}
}
add_action( 'parse_query', 'asp_logos_consulta' );

/**
 * Que no termine en 404 por no tener entradas.
 *
 * @param bool $saltear Valor previo.
 * @return bool
 */
function asp_logos_sin_404( bool $saltear ): bool {
	return asp_es_logos() ? true : $saltear;
}
add_filter( 'pre_handle_404', 'asp_logos_sin_404' );

/**
 * Carga la plantilla de la página.
 *
 * @param string $plantilla Ruta que eligió WordPress.
 * @return string
 */
function asp_logos_plantilla( string $plantilla ): string {
	if ( ! asp_es_logos() ) {
		return $plantilla;
	}
	return locate_template( 'templates/logos.php' ) ?: $plantilla;
}
add_filter( 'template_include', 'asp_logos_plantilla' );

/**
 * Sin canónica a otra página: redirect_canonical no tiene nada que hacer acá.
 *
 * @param string|false $url URL canónica propuesta.
 * @return string|false
 */
function asp_logos_canonica( $url ) {
	return asp_es_logos() ? false : $url;
}
add_filter( 'redirect_canonical', 'asp_logos_canonica' );

/**
 * No se indexa ni se siguen sus enlaces.
 *
 * @param array<string,bool|string> $robots Directivas.
 * @return array<string,bool|string>
 */
function asp_logos_robots( array $robots ): array {
	if ( asp_es_logos() ) {
		$robots['noindex']  = true;
		$robots['nofollow'] = true;
		unset( $robots['follow'], $robots['max-image-preview'] );
	}
	return $robots;
}
add_filter( 'wp_robots', 'asp_logos_robots', 20 );

/**
 * Título de la pestaña.
 *
 * @param array<string,string> $partes Partes del título.
 * @return array<string,string>
 */
function asp_logos_titulo( array $partes ): array {
	if ( asp_es_logos() ) {
		$partes['title'] = __( 'Logos para descargar', 'asp' );
	}
	return $partes;
}
add_filter( 'document_title_parts', 'asp_logos_titulo' );

/**
 * Hoja de estilos propia, solo en esta página.
 *
 * @return void
 */
function asp_logos_estilos(): void {
	if ( asp_es_logos() ) {
		wp_enqueue_style( 'asp-logos', asp_asset_url( 'assets/css/logos.css' ), [ 'asp-components' ], asp_asset_version( 'assets/css/logos.css' ) );
	}
}
add_action( 'wp_enqueue_scripts', 'asp_logos_estilos', 20 );

/**
 * URL de un archivo de la carpeta de descargas.
 *
 * @param string $archivo Nombre del archivo.
 * @return string
 */
function asp_logos_url( string $archivo ): string {
	return asp_asset_url( 'assets/descargas/logos/' . $archivo ) . '?v=' . asp_asset_version( 'assets/descargas/logos/' . $archivo );
}

/**
 * Peso legible de un archivo de la carpeta, ej. «308 KB».
 *
 * @param string $archivo Nombre del archivo.
 * @return string
 */
function asp_logos_peso( string $archivo ): string {
	$ruta = ASP_THEME_DIR . '/assets/descargas/logos/' . $archivo;
	return is_readable( $ruta ) ? size_format( (int) filesize( $ruta ), 0 ) : '';
}

/**
 * Grupos de logos para la plantilla. Cada variante trae la vista previa
 * (el SVG), los links de descarga y el fondo sobre el que se muestra.
 * Una variante cuyo archivo no está no se lista.
 *
 * @return array<int,array{titulo:string,texto:string,variantes:array<int,array{nombre:string,fondo:string,preview:string,descargas:array<int,array{formato:string,url:string,peso:string,detalle:string}>}>}>
 */
function asp_logos_grupos(): array {
	$colores = [
		'negro'  => [ __( 'Negro', 'asp' ), 'claro' ],
		'azul'   => [ __( 'Azul', 'asp' ), 'claro' ],
		'blanco' => [ __( 'Blanco', 'asp' ), 'oscuro' ],
	];
	$piezas = [
		'logo' => [
			__( 'Logo completo', 'asp' ),
			__( 'La versión principal. Para flyers, presentaciones, pantallas y todo lo que tenga lugar a lo ancho.', 'asp' ),
			'3000 × 940 px',
		],
		'cruz' => [
			__( 'La cruz', 'asp' ),
			__( 'Para espacios chicos o cuadrados: sellos, esquinas de un flyer, íconos. Cuando el nombre ya está escrito al lado.', 'asp' ),
			'2000 × 2000 px',
		],
	];

	$grupos = [];
	foreach ( $piezas as $pieza => [ $titulo, $texto, $medida ] ) {
		$variantes = [];
		foreach ( $colores as $color => [ $nombre, $fondo ] ) {
			$base = "ante-su-palabra-{$pieza}-{$color}";
			if ( ! is_readable( ASP_THEME_DIR . "/assets/descargas/logos/{$base}.svg" ) ) {
				continue;
			}
			$variantes[] = [
				'nombre'    => $nombre,
				'fondo'     => $fondo,
				'preview'   => asp_logos_url( "{$base}.svg" ),
				'descargas' => [
					[ 'formato' => 'PNG', 'url' => asp_logos_url( "{$base}.png" ), 'peso' => asp_logos_peso( "{$base}.png" ), 'detalle' => $medida ],
					[ 'formato' => 'SVG', 'url' => asp_logos_url( "{$base}.svg" ), 'peso' => asp_logos_peso( "{$base}.svg" ), 'detalle' => __( 'vectorial', 'asp' ) ],
				],
			];
		}
		if ( $variantes ) {
			$grupos[] = [ 'titulo' => $titulo, 'texto' => $texto, 'variantes' => $variantes ];
		}
	}

	$perfil = 'ante-su-palabra-perfil-redes.png';
	if ( is_readable( ASP_THEME_DIR . '/assets/descargas/logos/' . $perfil ) ) {
		$grupos[] = [
			'titulo'    => __( 'Foto de perfil', 'asp' ),
			'texto'     => __( 'La cruz sobre el azul del ministerio, cuadrada, para el perfil de una cuenta de Instagram, WhatsApp o Facebook.', 'asp' ),
			'variantes' => [
				[
					'nombre'    => __( 'Azul noche', 'asp' ),
					'fondo'     => 'imagen',
					'preview'   => asp_logos_url( $perfil ),
					'descargas' => [
						[ 'formato' => 'PNG', 'url' => asp_logos_url( $perfil ), 'peso' => asp_logos_peso( $perfil ), 'detalle' => '1080 × 1080 px' ],
					],
				],
			],
		];
	}
	return $grupos;
}

/**
 * El ZIP con todos los archivos, o null si no está.
 *
 * @return array{url:string,peso:string}|null
 */
function asp_logos_zip(): ?array {
	$zip  = 'ante-su-palabra-logos.zip';
	$peso =asp_logos_peso( $zip );
	return '' !== $peso ? [ 'url' => asp_logos_url( $zip ), 'peso' => $peso ] : null;
}
