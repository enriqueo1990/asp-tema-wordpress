<?php
/**
 * /recursos/predicaciones/ — todas las predicaciones, por año.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

get_header();

$asp_por_anio = asp_predicaciones_por_anio();
?>
<div class="asp-container">
	<header class="asp-recursos__cab">
		<div class="asp-stack asp-stack--3">
			<span class="asp-label"><?php esc_html_e( 'Recursos', 'asp' ); ?></span>
			<h1 class="asp-pagina__titulo"><?php esc_html_e( 'Predicaciones', 'asp' ); ?></h1>
			<p class="asp-recursos__bajada"><?php esc_html_e( 'Sesiones y predicaciones de las conferencias, en video, audio o texto.', 'asp' ); ?></p>
		</div>
		<nav class="asp-recursos__saltos" aria-label="<?php esc_attr_e( 'Recursos', 'asp' ); ?>">
			<a href="<?php echo esc_url( asp_url_recursos() ); ?>"><?php esc_html_e( 'Artículos', 'asp' ); ?></a>
			<a href="<?php echo esc_url( asp_url_eventos() ); ?>"><?php esc_html_e( 'Eventos', 'asp' ); ?></a>
		</nav>
	</header>
</div>
<?php if ( ! empty( $asp_por_anio ) ) : ?>
	<?php foreach ( $asp_por_anio as $asp_anio => $asp_items ) : ?>
		<section class="asp-container asp-editorial asp-editorial--sin-aire asp-section--rule" aria-label="<?php echo esc_attr( (string) $asp_anio ); ?>">
			<h2 class="asp-anio__num"><?php echo esc_html( $asp_anio ? (string) $asp_anio : __( 'Sin fecha', 'asp' ) ); ?></h2>
			<div class="asp-editorial__cuerpo asp-editorial__cuerpo--ancho">
				<div>
					<?php foreach ( $asp_items as $asp_p ) : ?>
						<?php get_template_part( 'parts/predicacion/fila', null, [ 'post_id' => $asp_p->ID ] ); ?>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endforeach; ?>
<?php else : ?>
	<div class="asp-container asp-section"><p class="asp-muted"><?php esc_html_e( 'Todavía no hay predicaciones publicadas.', 'asp' ); ?></p></div>
<?php endif; ?>
<?php
get_footer();
