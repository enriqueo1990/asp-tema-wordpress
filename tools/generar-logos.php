<?php
/**
 * Genera los logos descargables de la página oculta /logos-asp/ (ver
 * inc/logos.php): el logo completo y la cruz en cada color, en SVG y PNG
 * transparente, la imagen de perfil para redes y un ZIP con todo.
 *
 * Sale de los dos vectoriales del tema (assets/img/logo-asp.svg e
 * icono.svg), así que no se redibuja nada: solo se pinta. Los PNG los
 * rasteriza Chrome sin interfaz con fondo transparente.
 *
 * Si cambia el logo o un color, se vuelve a correr y se commitea la
 * carpeta assets/descargas/logos/ entera.
 *
 * Uso: php tools/generar-logos.php [ruta/a/chrome]
 */

declare(strict_types=1);

$chrome = $argv[1] ?? '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';
if ( ! is_executable( $chrome ) ) {
	fwrite( STDERR, "No encuentro Chrome en «{$chrome}». Pasá la ruta como argumento.\n" );
	exit( 1 );
}

$tema   = dirname( __DIR__ ) . '/wp-content/themes/asp';
$salida = $tema . '/assets/descargas/logos';
$tmp    = sys_get_temp_dir() . '/asp-logos-' . getmypid();

// Mismos colores que inc/logos.php. Azul = --c-accent, azul noche = --c-dark-bg.
$colores = [
	'negro'  => '#000000',
	'blanco' => '#FFFFFF',
	'azul'   => '#1E3A6E',
];
$azul_noche = '#12172A';

// Trazos de cada vectorial, sin el <svg> que los envuelve.
$trazos = static function ( string $archivo ): string {
	$svg = (string) file_get_contents( $archivo );
	$svg = (string) preg_replace( '#<style>.*?</style>|<title>.*?</title>#s', '', $svg );
	return (string) preg_replace( '#^.*?<svg[^>]*>|</svg>\s*$#s', '', $svg );
};
$logo = [ 'viewbox' => '0 0 290.65 91.08', 'w' => 290.65, 'h' => 91.08, 'trazos' => $trazos( $tema . '/assets/img/logo-asp.svg' ) ];
$cruz = [ 'viewbox' => '195 -4.5 100 100', 'w' => 100, 'h' => 100, 'trazos' => $trazos( $tema . '/assets/img/icono.svg' ) ];

$svg = static fn( array $pieza, string $color ): string => sprintf(
	'<svg xmlns="http://www.w3.org/2000/svg" viewBox="%1$s" width="%2$s" height="%3$s"><title>Ante Su Palabra</title><g fill="%4$s">%5$s</g></svg>' . "\n",
	$pieza['viewbox'],
	$pieza['w'],
	$pieza['h'],
	$color,
	$pieza['trazos']
);

/**
 * Rasteriza un SVG a PNG del ancho pedido, con fondo transparente.
 */
$png = static function ( string $svg_archivo, string $png_archivo, int $ancho, int $alto ) use ( $chrome, $tmp ): void {
	$html = $tmp . '/' . basename( $png_archivo, '.png' ) . '.html';
	file_put_contents(
		$html,
		'<!doctype html><html><head><style>html,body{margin:0;background:transparent}img{display:block;width:' . $ancho . 'px;height:' . $alto . 'px}</style></head><body><img src="file://' . $svg_archivo . '"></body></html>'
	);
	$cmd = sprintf(
		'%s --headless=new --disable-gpu --hide-scrollbars --force-device-scale-factor=1 --default-background-color=00000000 --window-size=%d,%d --screenshot=%s %s 2>/dev/null',
		escapeshellarg( $chrome ),
		$ancho,
		$alto,
		escapeshellarg( $png_archivo ),
		escapeshellarg( 'file://' . $html )
	);
	exec( $cmd, $out, $codigo );
	if ( 0 !== $codigo || ! is_file( $png_archivo ) ) {
		fwrite( STDERR, "Chrome no pudo generar {$png_archivo}.\n" );
		exit( 1 );
	}
};

if ( is_dir( $salida ) ) {
	array_map( 'unlink', glob( $salida . '/*' ) ?: [] );
} else {
	mkdir( $salida, 0755, true );
}
mkdir( $tmp, 0755, true );

foreach ( $colores as $nombre => $hex ) {
	foreach ( [ 'logo' => [ $logo, 3000 ], 'cruz' => [ $cruz, 2000 ] ] as $pieza_nombre => [ $pieza, $ancho ] ) {
		$base = $salida . "/ante-su-palabra-{$pieza_nombre}-{$nombre}";
		file_put_contents( $base . '.svg', $svg( $pieza, $hex ) );
		$png( $base . '.svg', $base . '.png', $ancho, (int) round( $ancho * $pieza['h'] / $pieza['w'] ) );
	}
}

// Perfil de redes: la cruz blanca sobre azul noche, como logo-cuadrado.png.
$perfil_svg = $tmp . '/perfil.svg';
file_put_contents(
	$perfil_svg,
	sprintf(
		'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><rect width="100" height="100" fill="%1$s"/><svg x="20" y="20" width="60" height="60" viewBox="%2$s"><g fill="#FFFFFF">%3$s</g></svg></svg>',
		$azul_noche,
		$cruz['viewbox'],
		$cruz['trazos']
	)
);
$png( $perfil_svg, $salida . '/ante-su-palabra-perfil-redes.png', 1080, 1080 );

$zip = $salida . '/ante-su-palabra-logos.zip';
exec( sprintf( 'cd %s && zip -q -X %s *.svg *.png', escapeshellarg( $salida ), escapeshellarg( basename( $zip ) ) ), $out, $codigo );
if ( 0 !== $codigo ) {
	fwrite( STDERR, "No se pudo armar el ZIP.\n" );
	exit( 1 );
}

array_map( 'unlink', glob( $tmp . '/*' ) ?: [] );
rmdir( $tmp );

foreach ( glob( $salida . '/*' ) ?: [] as $archivo ) {
	printf( "%-40s %6.1f KB\n", basename( $archivo ), filesize( $archivo ) / 1024 );
}
