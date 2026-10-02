<?php
/**
 * Verifica el contraste de los pares de color del tema contra WCAG AA.
 *
 * Lee los valores de assets/css/tokens.css, así que no hay una segunda copia
 * de la paleta que se pueda desincronizar. Los pares son los que existen de
 * verdad en las plantillas.
 *
 * Uso: php tools/contraste.php   (no necesita WordPress)
 */

declare(strict_types=1);

$css = (string) file_get_contents( dirname( __DIR__ ) . '/wp-content/themes/asp/assets/css/tokens.css' );
preg_match_all( '/(--c-[a-z0-9-]+):\s*(#[0-9A-Fa-f]{6})/', $css, $m, PREG_SET_ORDER );
$t = [];
foreach ( $m as $x ) {
	$t[ $x[1] ] = $x[2];
}

/* Velos rgba(): se verifican compuestos sobre blanco puro, el peor caso de
   una foto clara detrás del texto. Se agregan como tokens "--x@blanco". */
preg_match_all( '/(--c-[a-z0-9-]+):\s*rgba\(\s*(\d+),\s*(\d+),\s*(\d+),\s*([0-9.]+)\s*\)/', $css, $r, PREG_SET_ORDER );
foreach ( $r as $x ) {
	$a   = (float) $x[5];
	$hex = '#';
	foreach ( [ 2, 3, 4 ] as $i ) {
		$hex .= sprintf( '%02X', (int) round( (int) $x[ $i ] * $a + 255 * ( 1 - $a ) ) );
	}
	$t[ $x[1] . '@blanco' ] = $hex;
}

$lum = static function ( string $hex ): float {
	$c = [];
	foreach ( [ 1, 3, 5 ] as $i ) {
		$v = hexdec( substr( $hex, $i, 2 ) ) / 255;
		$c[] = $v <= 0.03928 ? $v / 12.92 : ( ( $v + 0.055 ) / 1.055 ) ** 2.4;
	}
	return 0.2126 * $c[0] + 0.7152 * $c[1] + 0.0722 * $c[2];
};
$ratio = static function ( string $a, string $b ) use ( $lum ): float {
	$la = $lum( $a );
	$lb = $lum( $b );
	return ( max( $la, $lb ) + 0.05 ) / ( min( $la, $lb ) + 0.05 );
};

/* [texto, fondo, dónde se ve, mínimo] — 3.0 para texto grande y para
   componentes que no son texto (bordes de campos, foco: WCAG 1.4.11). */
$pares = [
	[ '--c-text', '--c-bg', 'cuerpo sobre el fondo', 4.5 ],
	[ '--c-text-muted', '--c-bg', 'texto apagado', 4.5 ],
	[ '--c-text-faint', '--c-bg', 'texto tenue', 4.5 ],
	[ '--c-accent', '--c-bg', 'enlaces y botones', 4.5 ],
	[ '--c-accent-alt', '--c-bg', 'rótulos de categoría y numerales', 4.5 ],
	[ '--c-text', '--c-surface', 'cuerpo sobre banda', 4.5 ],
	[ '--c-text-muted', '--c-surface', 'apagado sobre banda', 4.5 ],
	[ '--c-accent', '--c-surface', 'enlaces sobre banda', 4.5 ],
	[ '--c-accent-alt', '--c-surface', 'rótulos sobre banda', 4.5 ],
	[ '--c-text-faint', '--c-surface-2', 'tenue sobre superficie', 4.5 ],
	[ '--c-accent-ink', '--c-accent', 'texto del botón principal', 4.5 ],
	[ '--c-flyer-band-ink', '--c-flyer-band', 'texto sobre la banda del flyer', 4.5 ],
	[ '--c-dark-fg', '--c-dark-bg', 'pie', 4.5 ],
	[ '--c-dark-muted', '--c-dark-bg', 'apagado del pie', 4.5 ],
	[ '--c-dark-fg', '--c-accent', 'texto sobre el tono más claro del degradado oscuro', 4.5 ],
	[ '--c-dark-muted', '--c-accent', 'apagado sobre el tono más claro del degradado oscuro', 4.5 ],
	[ '--c-btn-invertido-fg', '--c-btn-invertido-bg', 'botón invertido', 4.5 ],
	[ '--c-todo-fg', '--c-todo-bg', 'aviso de dato faltante', 4.5 ],
	[ '--c-tile-ink', '--c-tile-1', 'tarjeta de iniciativa, azul', 4.5 ],
	[ '--c-tile-ink', '--c-tile-2', 'tarjeta de iniciativa, azul medio', 4.5 ],
	[ '--c-tile-ink', '--c-tile-3', 'tarjeta de iniciativa, índigo', 4.5 ],
	[ '--c-tile-ink', '--c-tile-4', 'tarjeta de iniciativa, azul noche', 4.5 ],
	[ '--c-photo-text', '--c-photo-scrim-text@blanco', 'texto sobre foto con el velo mínimo, foto blanca', 4.5 ],
	[ '--c-border-control', '--c-bg', 'borde de campos de formulario (no texto)', 3.0 ],
	[ '--c-border-control', '--c-surface', 'borde de campos sobre banda (no texto)', 3.0 ],
	[ '--c-focus-ring', '--c-bg', 'anillo de foco', 3.0 ],
	[ '--c-focus-ring', '--c-surface', 'anillo de foco sobre banda', 3.0 ],
	[ '--c-focus-ring-inverso', '--c-dark-bg', 'anillo de foco en el pie y la banda oscura', 3.0 ],
	[ '--c-focus-ring-inverso', '--c-photo-scrim-text@blanco', 'anillo de foco sobre foto', 3.0 ],
];

$fallan = 0;
foreach ( $pares as [ $fg, $bg, $donde, $min ] ) {
	if ( ! isset( $t[ $fg ], $t[ $bg ] ) ) {
		printf( "  ?      falta un token: %s / %s\n", $fg, $bg );
		continue;
	}
	$r = $ratio( $t[ $fg ], $t[ $bg ] );
	$ok = $r >= $min;
	$fallan += $ok ? 0 : 1;
	printf( "%6.2f:1  %s  %s (%s sobre %s)\n", $r, $ok ? 'AA ' : '¡NO!', $donde, $t[ $fg ], $t[ $bg ] );
}
echo $fallan ? "\n$fallan pares por debajo del mínimo.\n" : "\nTodos los pares pasan AA.\n";
exit( $fallan ? 1 : 0 );
