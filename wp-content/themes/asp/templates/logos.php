<?php
/**
 * Página oculta de logos (/logos-asp/). La sirve inc/logos.php; no es una
 * plantilla de página del panel.
 *
 * Arriba el título y el ZIP con todo, que es lo que la mayoría necesita.
 * Debajo, cada pieza con sus colores, cada uno sobre el fondo en el que se
 * usa (el blanco sobre azul noche), con PNG y SVG por separado.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

$asp_grupos = asp_logos_grupos();
$asp_zip    = asp_logos_zip();

get_header();
?>
<div class="asp-container asp-section asp-logos">
	<header class="asp-logos__cabecera">
		<span class="asp-label"><?php esc_html_e( 'Para ministerios e iglesias', 'asp' ); ?></span>
		<h1 class="asp-pagina__titulo"><?php esc_html_e( 'Logos de Ante Su Palabra', 'asp' ); ?></h1>
		<p class="asp-logos__intro"><?php esc_html_e( 'Cada versión en PNG con fondo transparente, para usar directo en un flyer o una presentación, y en SVG, que no pierde calidad a ningún tamaño y es lo que va a pedir una imprenta.', 'asp' ); ?></p>
		<?php if ( $asp_zip ) : ?>
			<div class="asp-row">
				<a class="asp-btn" href="<?php echo esc_url( $asp_zip['url'] ); ?>" download>
					<?php esc_html_e( 'Descargar todo', 'asp' ); ?>
					<span class="asp-logos__peso-btn"><?php echo esc_html( 'ZIP · ' . $asp_zip['peso'] ); ?></span>
				</a>
			</div>
		<?php endif; ?>
		<p class="asp-logos__uso"><?php esc_html_e( 'El negro y el azul van sobre fondos claros; el blanco, sobre fondos oscuros o fotos. Usalo tal cual: sin estirarlo, cambiarle los colores ni separar la cruz del nombre en el logo completo.', 'asp' ); ?></p>
	</header>

	<?php foreach ( $asp_grupos as $asp_grupo ) : ?>
		<section class="asp-logos__grupo">
			<div class="asp-logos__grupo-cabecera">
				<h2 class="asp-logos__grupo-titulo"><?php echo esc_html( $asp_grupo['titulo'] ); ?></h2>
				<p class="asp-logos__grupo-texto"><?php echo esc_html( $asp_grupo['texto'] ); ?></p>
			</div>
			<ul class="asp-logos__lista" role="list">
				<?php foreach ( $asp_grupo['variantes'] as $asp_variante ) : ?>
					<li class="asp-logos__item">
						<div class="asp-logos__muestra asp-logos__muestra--<?php echo esc_attr( $asp_variante['fondo'] ); ?>">
							<img src="<?php echo esc_url( $asp_variante['preview'] ); ?>" alt="" loading="lazy" decoding="async">
						</div>
						<div class="asp-logos__pie">
							<h3 class="asp-logos__nombre"><?php echo esc_html( $asp_variante['nombre'] ); ?></h3>
							<ul class="asp-logos__descargas" role="list">
								<?php foreach ( $asp_variante['descargas'] as $asp_descarga ) : ?>
									<li>
										<a class="asp-logos__descarga" href="<?php echo esc_url( $asp_descarga['url'] ); ?>" download>
											<span class="asp-logos__formato"><?php echo esc_html( $asp_descarga['formato'] ); ?></span>
											<span class="asp-logos__detalle"><?php echo esc_html( $asp_descarga['detalle'] . ( $asp_descarga['peso'] ? ' · ' . $asp_descarga['peso'] : '' ) ); ?></span>
											<span class="screen-reader-text"><?php echo esc_html( sprintf( /* translators: 1: pieza, 2: color */ __( '%1$s, %2$s', 'asp' ), $asp_grupo['titulo'], $asp_variante['nombre'] ) ); ?></span>
										</a>
									</li>
								<?php endforeach; ?>
							</ul>
						</div>
					</li>
				<?php endforeach; ?>
			</ul>
		</section>
	<?php endforeach; ?>
</div>
<?php
get_footer();
