<?php
/**
 * /eventos/ — próximos arriba como tarjetas anchas, archivo por año abajo
 * como grilla de flyers. Sin filtros, sin estados vacíos: una sección sin
 * contenido no existe.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

get_header();

$asp_proximos = asp_eventos_proximos()->posts;
$asp_por_anio = asp_eventos_pasados_por_anio();
?>
<div class="asp-container">
	<header class="asp-eventos__cab">
		<h1 class="asp-pagina__titulo"><?php echo esc_html( post_type_archive_title( '', false ) ); ?></h1>
		<?php if ( count( $asp_por_anio ) > 1 ) : ?>
			<nav class="asp-indice-anios" aria-label="<?php esc_attr_e( 'Archivo por año', 'asp' ); ?>">
				<span class="asp-label"><?php esc_html_e( 'Archivo', 'asp' ); ?></span>
				<?php foreach ( array_keys( $asp_por_anio ) as $asp_a ) : ?>
					<a href="#archivo-<?php echo esc_attr( (string) $asp_a ); ?>"><?php echo esc_html( (string) $asp_a ); ?></a>
				<?php endforeach; ?>
			</nav>
		<?php endif; ?>
	</header>
</div>

<?php if ( ! empty( $asp_proximos ) ) : ?>
	<section class="asp-container asp-eventos__proximos" aria-labelledby="eventos-proximos">
		<h2 class="asp-label" id="eventos-proximos"><?php esc_html_e( 'Próximos', 'asp' ); ?></h2>
		<?php foreach ( $asp_proximos as $asp_post ) : ?>
			<?php get_template_part( 'parts/evento/card', null, [ 'post_id' => $asp_post->ID, 'ancha' => true ] ); ?>
		<?php endforeach; ?>
	</section>
<?php endif; ?>

<?php if ( ! empty( $asp_por_anio ) ) : ?>
	<div id="archivo" class="asp-eventos__archivo">
		<?php foreach ( $asp_por_anio as $asp_anio => $asp_eventos ) : ?>
			<section class="asp-container asp-section--rule asp-section--tight" id="archivo-<?php echo esc_attr( (string) $asp_anio ); ?>" aria-labelledby="archivo-<?php echo esc_attr( (string) $asp_anio ); ?>-titulo">
				<h2 class="asp-anio__num asp-anio__num--grilla" id="archivo-<?php echo esc_attr( (string) $asp_anio ); ?>-titulo">
					<span><?php echo esc_html( (string) $asp_anio ); ?></span>
					<span class="asp-anio__cuenta"><?php
						/* translators: %d: cantidad de eventos del año */
						echo esc_html( sprintf( _n( '%d evento', '%d eventos', count( $asp_eventos ), 'asp' ), count( $asp_eventos ) ) );
					?></span>
				</h2>
				<div class="asp-grilla-eventos">
					<?php foreach ( $asp_eventos as $asp_post ) : ?>
						<?php get_template_part( 'parts/evento/tarjeta-archivo', null, [ 'post_id' => $asp_post->ID ] ); ?>
					<?php endforeach; ?>
				</div>
			</section>
		<?php endforeach; ?>
	</div>
<?php endif; ?>

<?php if ( empty( $asp_proximos ) && empty( $asp_por_anio ) ) : ?>
	<div class="asp-container asp-section"><p class="asp-muted"><?php esc_html_e( 'Todavía no hay eventos publicados.', 'asp' ); ?></p></div>
<?php endif; ?>
<?php
get_footer();
