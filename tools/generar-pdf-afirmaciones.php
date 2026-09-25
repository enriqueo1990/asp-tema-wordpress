<?php
/**
 * Genera el PDF de Afirmaciones y Negaciones que se descarga desde Nosotros.
 *
 * El texto sale de inc/afirmaciones-datos.php, el mismo que muestra el
 * sitio, migrado verbatim del sitio viejo: acá solo se le da forma de
 * documento (A4, tipografía del tema, un artículo que no se parte entre
 * páginas). No se reescribe ni se corrige nada (regla 7 de CLAUDE.md).
 *
 * Arma un HTML temporal y lo imprime con Chrome sin interfaz. Si cambia el
 * texto, se vuelve a correr y se commitea el PDF nuevo.
 *
 * Uso: php tools/generar-pdf-afirmaciones.php [ruta/a/chrome]
 */

declare(strict_types=1);

require __DIR__ . '/arranque.php';

$chrome = $argv[1] ?? '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';
if ( ! is_executable( $chrome ) ) {
	fwrite( STDERR, "No encuentro Chrome en «{$chrome}». Pasá la ruta como argumento.\n" );
	exit( 1 );
}

$tema    = ASP_THEME_DIR;
$salida  = $tema . '/assets/docs/afirmaciones-y-negaciones.pdf';
$af      = asp_afirmaciones();
$logo    = (string) file_get_contents( $tema . '/assets/img/logo-asp.svg' );
$e       = static fn( string $t ): string => htmlspecialchars( $t, ENT_QUOTES, 'UTF-8' );
$archivo = static fn( string $ruta ): string => 'file://' . str_replace( ' ', '%20', $ruta );

ob_start();
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Afirmaciones y Negaciones · Ante Su Palabra</title>
<link rel="stylesheet" href="<?php echo $e( $archivo( $tema . '/assets/css/fuentes.css' ) ); ?>">
<link rel="stylesheet" href="<?php echo $e( $archivo( $tema . '/assets/css/tokens.css' ) ); ?>">
<style>
	@page {
		size: A4;
		margin: 22mm 20mm 20mm;
		@bottom-left {
			content: "Ante Su Palabra · Afirmaciones y Negaciones";
			font-family: var(--font-ui);
			font-size: 7.5pt;
			letter-spacing: 0.08em;
			text-transform: uppercase;
			color: var(--c-text-faint);
		}
		@bottom-right {
			content: counter(page) " / " counter(pages);
			font-family: var(--font-ui);
			font-size: 7.5pt;
			color: var(--c-text-faint);
		}
	}
	@page :first {
		@bottom-left { content: none; }
	}
	* { box-sizing: border-box; }
	html { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
	body {
		margin: 0;
		font-family: var(--font-body);
		font-size: 10.5pt;
		line-height: 1.55;
		color: var(--c-text);
	}
	.logo { display: block; width: 44mm; color: var(--c-text); }
	.logo svg { display: block; width: 100%; height: auto; }
	h1 {
		margin: 16mm 0 5mm;
		font-family: var(--font-display);
		font-weight: 500;
		font-size: 30pt;
		line-height: 1.05;
		letter-spacing: -0.015em;
	}
	.fuente {
		margin: 0 0 10mm;
		max-width: 125mm;
		font-family: var(--font-ui);
		font-size: 8.5pt;
		line-height: 1.5;
		color: var(--c-text-muted);
	}
	.cita {
		margin: 0 0 10mm;
		padding: 6mm 0 6mm 7mm;
		border-left: 0.6mm solid var(--c-accent);
	}
	.cita blockquote {
		margin: 0 0 3mm;
		font-family: var(--font-display);
		font-style: italic;
		font-size: 12.5pt;
		line-height: 1.45;
	}
	.rotulo {
		font-family: var(--font-ui);
		font-size: 7.5pt;
		font-weight: 600;
		letter-spacing: 0.14em;
		text-transform: uppercase;
		color: var(--c-text-faint);
	}
	.intro p { margin: 0 0 3.5mm; text-align: justify; hyphens: auto; }
	.articulos { margin-top: 10mm; }
	.articulo {
		display: grid;
		grid-template-columns: 16mm 1fr;
		gap: 0 5mm;
		padding: 5mm 0;
		border-top: 0.25mm solid var(--c-border-strong);
		break-inside: avoid;
	}
	.num {
		font-family: var(--font-numeral);
		font-weight: 600;
		font-size: 16pt;
		line-height: 1.1;
		color: var(--c-accent);
	}
	.num .rotulo { display: block; margin-top: 1mm; }
	.parte + .parte { margin-top: 3mm; }
	.parte .rotulo { display: block; margin-bottom: 1mm; color: var(--c-accent); }
	.parte--niega .rotulo { color: var(--c-text-muted); }
	.parte p { margin: 0; text-align: justify; hyphens: auto; }
	.parte--niega p { color: var(--c-text-muted); }
	.cierre {
		margin-top: 8mm;
		padding-top: 4mm;
		border-top: 0.25mm solid var(--c-border-strong);
		font-family: var(--font-ui);
		font-size: 8pt;
		color: var(--c-text-muted);
	}
</style>
</head>
<body>
	<div class="logo"><?php echo $logo; // Archivo propio del tema. ?></div>
	<h1>Afirmaciones y Negaciones</h1>
	<p class="fuente"><?php echo $e( $af['fuente'] ); ?></p>

	<div class="cita">
		<blockquote><?php echo $e( $af['cita'] ); ?></blockquote>
		<span class="rotulo"><?php echo $e( $af['cita_ref'] ); ?></span>
	</div>

	<div class="intro">
		<?php foreach ( $af['introduccion'] as $parrafo ) : ?>
			<p><?php echo $e( $parrafo ); ?></p>
		<?php endforeach; ?>
	</div>

	<div class="articulos">
		<?php foreach ( $af['articulos'] as $i => $art ) : ?>
			<section class="articulo">
				<div class="num"><?php echo $e( asp_romano( $i + 1 ) ); ?><span class="rotulo">Artículo</span></div>
				<div>
					<div class="parte">
						<span class="rotulo">Afirmamos</span>
						<p><?php echo $e( $art['afirmamos'] ); ?></p>
					</div>
					<div class="parte parte--niega">
						<span class="rotulo">Negamos</span>
						<p><?php echo $e( $art['negamos'] ); ?></p>
					</div>
				</div>
			</section>
		<?php endforeach; ?>
	</div>

	<p class="cierre">antesupalabra.com · contacto@antesupalabra.com</p>
</body>
</html>
<?php
$html = (string) ob_get_clean();

$tmp = sys_get_temp_dir() . '/asp-afirmaciones-' . getmypid() . '.html';
file_put_contents( $tmp, $html );
if ( ! is_dir( dirname( $salida ) ) ) {
	mkdir( dirname( $salida ), 0755, true );
}

$cmd = sprintf(
	'%s --headless --disable-gpu --no-pdf-header-footer --allow-file-access-from-files --virtual-time-budget=5000 --print-to-pdf=%s %s 2>&1',
	escapeshellarg( $chrome ),
	escapeshellarg( $salida ),
	escapeshellarg( 'file://' . $tmp )
);
exec( $cmd, $log, $codigo );
unlink( $tmp );

if ( 0 !== $codigo || ! is_readable( $salida ) ) {
	fwrite( STDERR, "Chrome no generó el PDF:\n" . implode( "\n", $log ) . "\n" );
	exit( 1 );
}
printf( "PDF: %s (%d KB)\n", $salida, (int) round( filesize( $salida ) / 1024 ) );
