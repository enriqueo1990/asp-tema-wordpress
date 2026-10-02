<?php
/**
 * /eventos/ — dos partes con forma distinta:
 * - Próximos, sobre una banda de color: el siguiente en tarjeta grande con
 *   su flyer y el resto en filas con el flyer en miniatura y el botón.
 * - Archivo, en lista tipográfica por año (sin flyers): el año más reciente
 *   abierto y los demás plegados, con el índice de años en su cabecera.
 *
 * Rehecha el 28-9-2026: próximos y archivo iban los dos con el flyer
 * grande y se leían como una sola lista de 10.000 px en el teléfono, con
 * el índice del archivo arriba de los próximos.
 *
 * Sin filtros y sin estados vacíos: una sección sin contenido no existe.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

get_header();

$asp_proximos = asp_eventos_proximos()->posts;
$asp_por_anio = asp_eventos_pasados_por_anio();
$asp_primero  = $asp_proximos[0] ?? null;
$asp_resto    = array_slice( $asp_proximos, 1 );
$asp_total    = array_sum( array_map( 'count', $asp_por_anio ) );
$asp_desde    = $asp_por_anio ? min( array_keys( $asp_por_anio ) ) : 0;
?>
<div class="asp-container">
	<header class="asp-eventos__cab">
		<h1 class="asp-pagina__titulo"><?php echo esc_html( post_type_archive_title( '', false ) ); ?></h1>
	</header>
</div>

<?php if ( $asp_primero ) : ?>
	<section class="asp-eventos__proximos" aria-labelledby="eventos-proximos">
		<div class="asp-container asp-eventos__proximos-inner">
			<h2 class="asp-seccion__titulo" id="eventos-proximos"><?php esc_html_e( 'Próximos', 'asp' ); ?></h2>
			<?php /* El siguiente, grande: se ve apenas abre la página, sin carga diferida. */ ?>
			<?php get_template_part( 'parts/evento/card', null, [ 'post_id' => $asp_primero->ID, 'ancha' => true, 'loading' => 'eager', 'nivel' => 'h3', 'sede' => false ] ); ?>
			<?php if ( $asp_resto ) : ?>
				<div class="asp-proximos-lista">
					<?php foreach ( $asp_resto as $asp_post ) : ?>
						<?php get_template_part( 'parts/evento/proximo-fila', null, [ 'post_id' => $asp_post->ID ] ); ?>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</section>
<?php endif; ?>

<?php if ( ! empty( $asp_por_anio ) ) : ?>
	<section class="asp-container asp-eventos__archivo" id="archivo" aria-labelledby="eventos-archivo">
		<div class="asp-eventos__archivo-cab">
			<div class="asp-stack asp-stack--2">
				<h2 class="asp-seccion__titulo" id="eventos-archivo"><?php esc_html_e( 'Archivo', 'asp' ); ?></h2>
				<p class="asp-eventos__archivo-cuenta"><?php
					/* translators: 1: cantidad de eventos realizados, 2: año del primero */
					echo esc_html( sprintf( _n( '%1$d evento desde %2$d', '%1$d eventos desde %2$d', $asp_total, 'asp' ), $asp_total, $asp_desde ) );
				?></p>
			</div>
			<?php if ( count( $asp_por_anio ) > 1 ) : ?>
				<nav class="asp-indice-anios" aria-label="<?php esc_attr_e( 'Ir a un año', 'asp' ); ?>">
					<?php foreach ( array_keys( $asp_por_anio ) as $asp_a ) : ?>
						<a href="#archivo-<?php echo esc_attr( (string) $asp_a ); ?>"><?php echo esc_html( (string) $asp_a ); ?></a>
					<?php endforeach; ?>
				</nav>
			<?php endif; ?>
		</div>

		<?php $asp_abierto = true; ?>
		<?php foreach ( $asp_por_anio as $asp_anio => $asp_eventos ) : ?>
			<details class="asp-anio-plegable asp-eventos__anio" id="archivo-<?php echo esc_attr( (string) $asp_anio ); ?>"<?php echo $asp_abierto ? ' open' : ''; ?>>
				<summary class="asp-anio__num asp-anio__num--grilla">
					<h3 class="asp-anio__titulo"><?php echo esc_html( (string) $asp_anio ); ?></h3>
					<span class="asp-anio__cuenta"><?php
						/* translators: %d: cantidad de eventos del año */
						echo esc_html( sprintf( _n( '%d evento', '%d eventos', count( $asp_eventos ), 'asp' ), count( $asp_eventos ) ) );
					?></span>
				</summary>
				<div class="asp-eventos__lista">
					<?php foreach ( $asp_eventos as $asp_post ) : ?>
						<?php get_template_part( 'parts/evento/archivo-item', null, [ 'post_id' => $asp_post->ID, 'ancho' => true, 'sin_anio' => true ] ); ?>
					<?php endforeach; ?>
				</div>
			</details>
			<?php $asp_abierto = false; ?>
		<?php endforeach; ?>
	</section>
<?php endif; ?>

<?php if ( ! $asp_primero && empty( $asp_por_anio ) ) : ?>
	<div class="asp-container asp-section"><p class="asp-muted"><?php esc_html_e( 'Todavía no hay eventos publicados.', 'asp' ); ?></p></div>
<?php endif; ?>
<?php
get_footer();
