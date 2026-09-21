<?php
/**
 * Configuración base del tema: soportes, menús y tamaños de imagen.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

/**
 * Ruta absoluta y URL del tema, para no repetir las llamadas en cada módulo.
 */
define( 'ASP_THEME_DIR', get_template_directory() );
define( 'ASP_THEME_URI', get_template_directory_uri() );
define( 'ASP_THEME_VERSION', '0.1.0' );

/**
 * Soportes del tema, menús y tamaños de imagen.
 *
 * @return void
 */
function asp_setup(): void {
	load_theme_textdomain( 'asp', ASP_THEME_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support(
		'html5',
		[
			'search-form',
			'gallery',
			'caption',
			'style',
			'script',
			'navigation-widgets',
		]
	);
	add_theme_support(
		'custom-logo',
		[
			'flex-width'  => true,
			'flex-height' => true,
			'unlink-homepage-logo' => true,
		]
	);

	register_nav_menus(
		[
			'principal' => __( 'Menú principal', 'asp' ),
			'pie'       => __( 'Menú del pie', 'asp' ),
		]
	);

	/*
	 * Tamaños de imagen.
	 * El flyer nunca se recorta (regla 5 de CLAUDE.md): solo se limita el ancho.
	 * Las fotos de persona sí se recortan a cuadrado para normalizar el consejo.
	 */
	add_image_size( 'asp-flyer', 1200, 9999, false );
	add_image_size( 'asp-flyer-card', 800, 9999, false );
	add_image_size( 'asp-persona', 480, 480, true );
}
add_action( 'after_setup_theme', 'asp_setup' );

/**
 * Ancho máximo del contenido incrustado (oEmbed, imágenes del editor).
 * Coincide con --w-page de tokens.css.
 *
 * @return void
 */
function asp_content_width(): void {
	$GLOBALS['content_width'] = 1280;
}
add_action( 'after_setup_theme', 'asp_content_width', 0 );

/**
 * Nombres legibles para los tamaños de imagen en el selector de medios.
 *
 * @param array<string, string> $sizes Tamaños existentes.
 * @return array<string, string>
 */
function asp_image_size_names( array $sizes ): array {
	return array_merge(
		$sizes,
		[
			'asp-flyer'      => __( 'Flyer (ancho completo)', 'asp' ),
			'asp-flyer-card' => __( 'Flyer (tarjeta)', 'asp' ),
			'asp-persona'    => __( 'Foto de persona (cuadrada)', 'asp' ),
		]
	);
}
add_filter( 'image_size_names_choose', 'asp_image_size_names' );
