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
		<?php if ( count( $asp_por_anio ) > 1 ) : ?>
			<nav class="asp-indice-anios" aria-label="<?php esc_attr_e( 'Ir a un año', 'asp' ); ?>">
				<?php foreach ( array_keys( $asp_por_anio ) as $asp_a ) : ?>
					<a href="#anio-<?php echo esc_attr( (string) $asp_a ); ?>"><?php echo esc_html( $asp_a ? (string) $asp_a : __( 'Sin fecha', 'asp' ) ); ?></a>
				<?php endforeach; ?>
			</nav>
		<?php endif; ?>
	</header>
</div>
<?php if ( ! empty( $asp_por_anio ) ) : ?>
	<?php
	/* Diez años de conferencias no entran en una sola pantalla larga: cada año
	   se pliega y el último viene abierto. Es <details> del navegador, sin
	   JavaScript, y el contenido queda en el HTML para los buscadores. */
	$asp_primero = true;
	?>
	<?php foreach ( $asp_por_anio as $asp_anio => $asp_items ) : ?>
		<section class="asp-container asp-section--rule asp-section--tight" id="anio-<?php echo esc_attr( (string) $asp_anio ); ?>" aria-label="<?php echo esc_attr( (string) $asp_anio ); ?>">
			<details class="asp-anio-plegable"<?php echo $asp_primero ? ' open' : ''; ?>>
				<summary class="asp-anio__num asp-anio__num--grilla">
					<span><?php echo esc_html( $asp_anio ? (string) $asp_anio : __( 'Sin fecha', 'asp' ) ); ?></span>
					<span class="asp-anio__cuenta"><?php
						/* translators: %d: cantidad de predicaciones del año */
						echo esc_html( sprintf( _n( '%d predicación', '%d predicaciones', count( $asp_items ), 'asp' ), count( $asp_items ) ) );
					?></span>
				</summary>
				<div class="asp-grilla-predicaciones">
					<?php foreach ( $asp_items as $asp_p ) : ?>
						<?php get_template_part( 'parts/predicacion/tarjeta', null, [ 'post_id' => $asp_p->ID ] ); ?>
					<?php endforeach; ?>
				</div>
			</details>
		</section>
		<?php $asp_primero = false; ?>
	<?php endforeach; ?>
<?php else : ?>
	<div class="asp-container asp-section"><p class="asp-muted"><?php esc_html_e( 'Todavía no hay predicaciones publicadas.', 'asp' ); ?></p></div>
<?php endif; ?>
<?php
get_footer();
