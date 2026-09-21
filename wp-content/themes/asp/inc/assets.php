<?php
/**
 * Encolado de CSS y JS del tema.
 *
 * Orden fijo de hojas: fuentes → tokens → base → layout → components.
 * Cada una depende de la anterior para que WordPress respete el orden
 * aunque otro módulo encole algo en el medio.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

/**
 * Versión de un asset a partir de su fecha de modificación.
 *
 * Con filemtime cada cambio invalida la caché del navegador sin tocar
 * ningún número a mano. Si el archivo no existe cae a la versión del tema
 * en vez de emitir un aviso.
 *
 * @param string $relative_path Ruta relativa a la raíz del tema, ej. 'assets/css/base.css'.
 * @return string
 */
function asp_asset_version( string $relative_path ): string {
	$file = ASP_THEME_DIR . '/' . ltrim( $relative_path, '/' );

	if ( is_readable( $file ) ) {
		$mtime = filemtime( $file );
		if ( false !== $mtime ) {
			return (string) $mtime;
		}
	}

	return ASP_THEME_VERSION;
}

/**
 * URL pública de un asset del tema.
 *
 * @param string $relative_path Ruta relativa a la raíz del tema.
 * @return string
 */
function asp_asset_url( string $relative_path ): string {
	return ASP_THEME_URI . '/' . ltrim( $relative_path, '/' );
}

/**
 * Hojas de estilo del tema, en el orden en que se cargan.
 *
 * @return array<string, string> handle => ruta relativa.
 */
function asp_stylesheets(): array {
	return [
		'asp-fuentes'    => 'assets/css/fuentes.css',
		'asp-tokens'     => 'assets/css/tokens.css',
		'asp-base'       => 'assets/css/base.css',
		'asp-layout'     => 'assets/css/layout.css',
		'asp-components' => 'assets/css/components.css',
	];
}

/**
 * Encola fuentes, hojas de estilo y el script mínimo en el front.
 *
 * @return void
 */
function asp_enqueue_assets(): void {
	$previous = [];
	foreach ( asp_stylesheets() as $handle => $path ) {
		wp_enqueue_style(
			$handle,
			asp_asset_url( $path ),
			$previous,
			asp_asset_version( $path )
		);
		$previous = [ $handle ];
	}

	wp_enqueue_script(
		'asp-app',
		asp_asset_url( 'assets/js/app.js' ),
		[],
		asp_asset_version( 'assets/js/app.js' ),
		[ 'strategy' => 'defer', 'in_footer' => true ]
	);
}
add_action( 'wp_enqueue_scripts', 'asp_enqueue_assets' );

/**
 * Precarga las dos fuentes que aparecen en la primera pantalla de cualquier
 * vista: la serif de lectura y la sans de los rótulos, ambas en latin. Las
 * demás caras (itálica, latin-ext) se piden solo si hacen falta.
 *
 * @return void
 */
function asp_precargar_fuentes(): void {
	foreach ( [ 'newsreader-latin.woff2', 'archivo-latin.woff2' ] as $asp_fuente ) {
		printf(
			'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
			esc_url( asp_asset_url( 'assets/fonts/' . $asp_fuente ) )
		);
	}
}
add_action( 'wp_head', 'asp_precargar_fuentes', 1 );
