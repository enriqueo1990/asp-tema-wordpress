<?php
/**
 * Arranque común de los scripts de tools/: carga WordPress.
 *
 * La ruta del WordPress local sale de la variable de entorno ASP_WP_PATH;
 * si no está definida se prueba la ubicación por defecto de Local. El
 * dominio sale de ASP_WP_HOST.
 *
 *   ASP_WP_PATH="/ruta/al/app/public" php tools/seed-local.php
 */

$asp_ruta = getenv( 'ASP_WP_PATH' );
if ( ! is_string( $asp_ruta ) || '' === $asp_ruta ) {
	$asp_ruta = getenv( 'HOME' ) . '/Local Sites/asp-newsite/app/public';
}
$asp_ruta = rtrim( $asp_ruta, '/' );

if ( ! is_readable( $asp_ruta . '/wp-load.php' ) ) {
	fwrite( STDERR, "No encuentro WordPress en «{$asp_ruta}».\n" );
	fwrite( STDERR, "Indicá la ruta con ASP_WP_PATH=/ruta/al/app/public\n" );
	exit( 1 );
}

if ( ! defined( 'WP_USE_THEMES' ) ) {
	define( 'WP_USE_THEMES', false );
}
$_SERVER['HTTP_HOST'] = getenv( 'ASP_WP_HOST' ) ?: 'asp-newsite.local';

require $asp_ruta . '/wp-load.php';
