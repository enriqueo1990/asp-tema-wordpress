<?php
/**
 * Franja "Próximo evento": bloque a sangre de color pleno bajo el hero.
 *
 * Carga el próximo evento dinámicamente con asp_evento_destacado(): el
 * primer evento futuro marcado como destacado o, si ninguno lo está, el
 * más cercano por fecha. Cuando pasa la fecha de fin, cambia solo al
 * siguiente. Sin evento próximo no imprime nada: el hero queda pegado a la
 * sección siguiente, sin espacio vacío ni placeholder.
 *
 * Args opcionales: post_id, para forzar un evento concreto.
 *
 * Solo el título y el botón son clicables. Con inscripción abierta y link
 * cargado el botón va a la inscripción; en los demás estados lleva a la ficha.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

$asp_id = (int) ( $args['post_id'] ?? 0 );
if ( ! $asp_id ) {
	$asp_destacado = asp_evento_destacado();
	$asp_id        = $asp_destacado ? $asp_destacado->ID : 0;
}
if ( ! $asp_id || 'evento' !== get_post_type( $asp_id ) || 'publish' !== get_post_status( $asp_id ) || 'realizado' === asp_evento_estado( $asp_id ) ) {
	return;
}

$asp_estado = asp_evento_estado( $asp_id );
$asp_fecha  = asp_evento_fecha_display( $asp_id );
$asp_boton  = asp_evento_tiene_boton( $asp_id );
$asp_url    = asp_evento_url_registro( $asp_id );
$asp_hid    = 'franja-titulo-' . $asp_id;
?>
<section class="asp-franja" aria-labelledby="<?php echo esc_attr( $asp_hid ); ?>">
	<div class="asp-container asp-franja__inner">

		<?php if ( $asp_fecha['dias'] ) : ?>
			<div class="asp-franja__fecha">
				<time datetime="<?php echo esc_attr( $asp_fecha['iso'] ); ?>">
					<span class="visually-hidden"><?php echo esc_html( $asp_fecha['texto'] ); ?></span>
					<span class="asp-franja__dias" aria-hidden="true"><?php echo esc_html( $asp_fecha['dias'] ); ?></span>
				</time>
				<span class="asp-franja__mes" aria-hidden="true"><?php echo esc_html( $asp_fecha['mes'] ); ?></span>
			</div>
		<?php endif; ?>

		<div class="asp-franja__datos">
			<div class="asp-franja__cabecera">
				<h2 id="<?php echo esc_attr( $asp_hid ); ?>" class="asp-label"><?php esc_html_e( 'Próximo evento', 'asp' ); ?></h2>
				<span class="asp-franja__sep" aria-hidden="true"></span>
				<span class="asp-franja__estado"><?php echo esc_html( asp_evento_estado_etiqueta( $asp_estado ) ); ?></span>
			</div>
			<p class="asp-franja__titulo"><a href="<?php echo esc_url( get_permalink( $asp_id ) ); ?>"><?php echo esc_html( get_the_title( $asp_id ) ); ?></a></p>
			<?php get_template_part( 'parts/evento/lugar', null, [ 'post_id' => $asp_id ] ); ?>
		</div>

		<?php if ( $asp_boton ) : ?>
			<a class="asp-btn asp-btn--block asp-btn--invertido" href="<?php echo esc_url( $asp_url ); ?>" target="_blank" rel="noopener">
				<span class="asp-franja__btn-corto"><?php echo esc_html( asp_evento_boton_texto( $asp_id, false ) ); ?></span>
				<span class="asp-franja__btn-largo"><?php echo esc_html( asp_evento_boton_texto( $asp_id, true ) ); ?></span>
			</a>
		<?php else : ?>
			<a class="asp-btn asp-btn--block asp-btn--invertido" href="<?php echo esc_url( get_permalink( $asp_id ) ); ?>"><?php esc_html_e( 'Ver el evento', 'asp' ); ?></a>
		<?php endif; ?>

	</div>
</section>
