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
 * URL de Google Fonts con las dos familias del sistema.
 *
 * TODO: pasar a fuentes autoalojadas en assets/fonts/ antes del lanzamiento
 * (privacidad y rendimiento). Mientras tanto se cargan desde Google.
 *
 * @return string
 */
function asp_fonts_url(): string {
	return 'https://fonts.googleapis.com/css2?family=Newsreader:ital,opsz,wght@0,6..72,400;0,6..72,500;0,6..72,600;1,6..72,400;1,6..72,500&family=Archivo:wght@400;500;600&display=swap';
}

/**
 * Hojas de estilo del tema, en el orden en que se cargan.
 *
 * @return array<string, string> handle => ruta relativa.
 */
function asp_stylesheets(): array {
	return [
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
	wp_enqueue_style( 'asp-fonts', asp_fonts_url(), [], null ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion

	$previous = [ 'asp-fonts' ];
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
 * Preconexión a Google Fonts.
 *
 * @param array<int, array<string, string>> $urls  URLs.
 * @param string                            $relation_type Tipo.
 * @return array<int, array<string, string>>
 */
function asp_resource_hints( array $urls, string $relation_type ): array {
	if ( 'preconnect' === $relation_type ) {
		$urls[] = [ 'href' => 'https://fonts.gstatic.com', 'crossorigin' => 'anonymous' ];
	}
	return $urls;
}
add_filter( 'wp_resource_hints', 'asp_resource_hints', 10, 2 );
