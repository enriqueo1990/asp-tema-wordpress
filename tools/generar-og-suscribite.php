<?php
/**
 * Genera la imagen para compartir /suscribite/ (assets/img/og-suscribite.jpg,
 * 1200×630). Parte de og-inicio.jpg: deja la foto de la derecha y tapa el
 * texto de la izquierda con el mismo azul noche, para poner encima el logo,
 * «Suscribite» y la dirección. Mismo lenguaje que og-inicio y og-logos.
 *
 * Uso: php tools/generar-og-suscribite.php [ruta/a/chrome]
 */

declare(strict_types=1);

$chrome = $argv[1] ?? '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';
if ( ! is_executable( $chrome ) ) {
	fwrite( STDERR, "No encuentro Chrome en «{$chrome}». Pasá la ruta como argumento.\n" );
	exit( 1 );
}

$tema = dirname( __DIR__ ) . '/wp-content/themes/asp';
$tmp  = sys_get_temp_dir() . '/asp-og-suscribite-' . getmypid();
if ( ! is_dir( $tmp ) && ! mkdir( $tmp ) ) {
	fwrite( STDERR, "No pude crear «{$tmp}».\n" );
	exit( 1 );
}

// Azul noche = --c-dark-bg; gris = --c-dark-muted.
$azul_noche = '#12172A';
$apagado    = '#AEB5C6';

$svg  = (string) file_get_contents( $tema . '/assets/img/logo-asp.svg' );
$svg  = (string) preg_replace( '#<style>.*?</style>|<title>.*?</title>#s', '', $svg );
$logo = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 290.65 91.08"><g fill="#FFFFFF">'
	. (string) preg_replace( '#^.*?<svg[^>]*>|</svg>\s*$#s', '', $svg )
	. '</g></svg>';

$fuentes = $tema . '/assets/fonts';
$html    = $tmp . '/og.html';
file_put_contents(
	$html,
	'<!doctype html><html><head><meta charset="utf-8"><style>'
	. '@font-face{font-family:Newsreader;font-weight:400 600;src:url("file://' . $fuentes . '/newsreader-latin.woff2")}'
	. '@font-face{font-family:Archivo;font-weight:400 600;src:url("file://' . $fuentes . '/archivo-latin.woff2")}'
	. 'html,body{margin:0}'
	. 'body{width:1200px;height:630px;position:relative;overflow:hidden;background:' . $azul_noche . ' url("file://' . $tema . '/assets/img/og-inicio.jpg") 0 0/1200px 630px no-repeat}'
	// Tapa el lema de og-inicio (termina cerca de x=680) y funde hacia la foto.
	. '.velo{position:absolute;inset:0;background:linear-gradient(90deg,' . $azul_noche . ' 0,' . $azul_noche . ' 700px,rgba(18,23,42,0) 880px)}'
	. '.texto{position:absolute;left:80px;top:0;bottom:0;width:600px;color:#fff;display:flex;flex-direction:column;justify-content:center}'
	. '.texto svg{width:186px;display:block}'
	. 'h1{font:400 120px/1 Newsreader,serif;letter-spacing:-.02em;margin:40px 0 22px}'
	. 'p{font:400 36px/1.2 Newsreader,serif;color:' . $apagado . ';margin:0 0 44px}'
	. '.rotulo{font:500 15px Archivo,sans-serif;letter-spacing:.14em;color:' . $apagado . ';display:flex;align-items:center;gap:18px}'
	. '.rotulo i{width:24px;height:1px;background:' . $apagado . '}'
	. '</style></head><body><div class="velo"></div><div class="texto">' . $logo
	. '<h1>Suscribite</h1>'
	. '<p>Próximos eventos, talleres<br>y novedades, por email</p>'
	. '<div class="rotulo"><i></i>ANTESUPALABRA.COM/SUSCRIBITE</div></div>'
	. '</body></html>'
);

$png = $tmp . '/og.png';
exec(
	sprintf(
		'%s --headless=new --disable-gpu --hide-scrollbars --allow-file-access-from-files --force-device-scale-factor=1 --virtual-time-budget=2000 --window-size=1200,630 --screenshot=%s %s 2>/dev/null',
		escapeshellarg( $chrome ),
		escapeshellarg( $png ),
		escapeshellarg( 'file://' . $html )
	),
	$out,
	$codigo
);
$jpg = $tema . '/assets/img/og-suscribite.jpg';
exec( sprintf( 'sips -s format jpeg -s formatOptions 88 %s --out %s >/dev/null', escapeshellarg( $png ), escapeshellarg( $jpg ) ), $out, $codigo_jpg );

array_map( 'unlink', glob( $tmp . '/*' ) ?: [] );
rmdir( $tmp );

if ( 0 !== $codigo || 0 !== $codigo_jpg ) {
	fwrite( STDERR, "No se pudo generar og-suscribite.jpg.\n" );
	exit( 1 );
}
printf( "%s %.1f KB\n", $jpg, filesize( $jpg ) / 1024 );
