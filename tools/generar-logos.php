<?php
/**
 * Genera los logos descargables de la página oculta /logos-asp/ (ver
 * inc/logos.php): el logo completo y la cruz en cada color, en SVG y PNG
 * transparente, la imagen de perfil para redes y un ZIP con todo. También
 * la imagen para compartir el link (assets/img/og-logos.jpg).
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

// Imagen para compartir el link de la página (assets/img/og-logos.jpg).
// Fuera de la carpeta de descargas: no entra al ZIP. Mismo lenguaje que
// og-inicio.jpg: azul noche, logo blanco, título en Newsreader y rótulo en
// Archivo; a la derecha el logo en negro y en azul, como se ve en la página.
$inline = static fn( array $pieza, string $color ): string => sprintf(
	'<svg xmlns="http://www.w3.org/2000/svg" viewBox="%1$s"><g fill="%2$s">%3$s</g></svg>',
	$pieza['viewbox'],
	$color,
	$pieza['trazos']
);
$fuentes = $tema . '/assets/fonts';
$og_html = $tmp . '/og-logos.html';
file_put_contents(
	$og_html,
	'<!doctype html><html><head><meta charset="utf-8"><style>'
	. '@font-face{font-family:Newsreader;font-weight:400 600;src:url("file://' . $fuentes . '/newsreader-latin.woff2")}'
	. '@font-face{font-family:Archivo;font-weight:400 600;src:url("file://' . $fuentes . '/archivo-latin.woff2")}'
	. 'html,body{margin:0}'
	. 'body{width:1200px;height:630px;display:grid;grid-template-columns:620px 580px;grid-template-rows:1fr 1fr}'
	. '.oscuro{grid-row:1/3;background:' . $azul_noche . ';color:#fff;padding:0 80px;display:flex;flex-direction:column;justify-content:center}'
	. '.oscuro svg{width:186px;display:block}'
	. 'h1{font:400 84px/1.02 Newsreader,serif;letter-spacing:-.015em;margin:44px 0 50px}'
	. '.rotulo{font:500 15px Archivo,sans-serif;letter-spacing:.14em;color:#AEB5C6;display:flex;align-items:center;gap:18px}'
	. '.rotulo i{width:24px;height:1px;background:#AEB5C6}.rotulo b{font-weight:500;width:1px;height:16px;background:rgba(255,255,255,.3)}'
	. '.muestra{display:grid;place-items:center}.muestra svg{width:330px}'
	. '.claro{background:#F5F6FA}.blanco{background:#fff;border-top:1px solid #E2E5EE}'
	. '</style></head><body>'
	. '<div class="oscuro">' . $inline( $logo, '#FFFFFF' )
	. '<h1>Logos para<br>descargar</h1>'
	. '<div class="rotulo"><i></i>PNG <b></b> SVG <b></b> NEGRO, AZUL Y BLANCO</div></div>'
	. '<div class="muestra claro">' . $inline( $logo, $colores['negro'] ) . '</div>'
	. '<div class="muestra blanco">' . $inline( $logo, $colores['azul'] ) . '</div>'
	. '</body></html>'
);
$og_png = $tmp . '/og-logos.png';
exec(
	sprintf(
		'%s --headless=new --disable-gpu --hide-scrollbars --force-device-scale-factor=1 --virtual-time-budget=2000 --window-size=1200,630 --screenshot=%s %s 2>/dev/null',
		escapeshellarg( $chrome ),
		escapeshellarg( $og_png ),
		escapeshellarg( 'file://' . $og_html )
	),
	$out,
	$codigo
);
exec( sprintf( 'sips -s format jpeg -s formatOptions 88 %s --out %s >/dev/null', escapeshellarg( $og_png ), escapeshellarg( $tema . '/assets/img/og-logos.jpg' ) ), $out, $codigo_jpg );
if ( 0 !== $codigo || 0 !== $codigo_jpg ) {
	fwrite( STDERR, "No se pudo generar og-logos.jpg.\n" );
	exit( 1 );
}

array_map( 'unlink', glob( $tmp . '/*' ) ?: [] );
rmdir( $tmp );

foreach ( glob( $salida . '/*' ) ?: [] as $archivo ) {
	printf( "%-40s %6.1f KB\n", basename( $archivo ), filesize( $archivo ) / 1024 );
}
