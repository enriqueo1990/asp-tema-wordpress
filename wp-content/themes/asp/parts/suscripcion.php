<?php
/**
 * Caja de suscripción por mail (Kit). Va en el pie de todo el sitio
 * (variante oscura), bajo «Próximos eventos» en el inicio (clara) y en
 * /suscribite/, donde la página pone su propio título y bajada y la caja
 * trae solo el formulario y una nota chica debajo («solo_form» y «nota»).
 *
 * Sin JavaScript el formulario hace un POST común a Kit. Con JavaScript
 * (app.js) manda con fetch y cambia el formulario por el mensaje de éxito,
 * o deja el formulario como estaba y avisa del error en la misma zona
 * aria-live. Los textos de los dos mensajes viajan en data-*.
 *
 * El campo «web» es una trampa para robots: oculto por CSS, fuera del
 * orden de tabulación y sin autocompletar. Si llega completo, app.js no
 * manda nada.
 *
 * @package asp
 *
 * @var array{variante?: string, nivel?: int, solo_form?: bool, nota?: string} $args
 */

defined( 'ABSPATH' ) || exit;

$asp_variante = 'clara' === ( $args['variante'] ?? '' ) ? 'clara' : 'oscura';
$asp_nivel    = 3 === ( $args['nivel'] ?? 2 ) ? 'h3' : 'h2';
$asp_boton    = 'oscura' === $asp_variante ? 'asp-btn asp-btn--invertido' : 'asp-btn';
$asp_solo     = ! empty( $args['solo_form'] );
$asp_nota     = (string) ( $args['nota'] ?? '' );
$asp_id       = asp_suscripcion_id();
?>
<?php if ( $asp_solo ) : ?>
<div class="asp-suscripcion asp-suscripcion--<?php echo esc_attr( $asp_variante ); ?> asp-suscripcion--solo">
<?php else : ?>
<section class="asp-suscripcion asp-suscripcion--<?php echo esc_attr( $asp_variante ); ?>" aria-labelledby="<?php echo esc_attr( $asp_id ); ?>-titulo">
	<div class="asp-suscripcion__texto">
		<<?php echo $asp_nivel; // phpcs:ignore WordPress.Security.EscapeOutput ?> id="<?php echo esc_attr( $asp_id ); ?>-titulo" class="asp-suscripcion__titulo"><?php esc_html_e( 'Recibí los próximos eventos', 'asp' ); ?></<?php echo $asp_nivel; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
		<p class="asp-suscripcion__bajada"><?php esc_html_e( 'Te avisamos por email de los próximos eventos, talleres y novedades de Ante Su Palabra. Sin spam; te podés dar de baja cuando quieras.', 'asp' ); ?></p>
	</div>
<?php endif; ?>
	<div class="asp-suscripcion__accion">
		<form class="asp-suscripcion__form" action="<?php echo esc_url( ASP_SUSCRIPCION_ENDPOINT ); ?>" method="post"
			data-asp-suscripcion
			data-exito="<?php esc_attr_e( '¡Gracias! Te mandamos un mail para confirmar tu suscripción. Revisá tu bandeja de entrada (y también Promociones o Spam).', 'asp' ); ?>"
			data-error="<?php esc_attr_e( 'No pudimos completar la suscripción. Probá de nuevo en unos minutos.', 'asp' ); ?>"
			data-enviando="<?php esc_attr_e( 'Enviando…', 'asp' ); ?>">
			<label class="visually-hidden" for="<?php echo esc_attr( $asp_id ); ?>-nombre"><?php esc_html_e( 'Nombre (opcional)', 'asp' ); ?></label>
			<input class="asp-suscripcion__campo" id="<?php echo esc_attr( $asp_id ); ?>-nombre" type="text" name="fields[first_name]" autocomplete="given-name" placeholder="<?php esc_attr_e( 'Tu nombre (opcional)', 'asp' ); ?>">
			<label class="visually-hidden" for="<?php echo esc_attr( $asp_id ); ?>-email"><?php esc_html_e( 'Email', 'asp' ); ?></label>
			<input class="asp-suscripcion__campo" id="<?php echo esc_attr( $asp_id ); ?>-email" type="email" name="email_address" autocomplete="email" required placeholder="<?php esc_attr_e( 'Tu email', 'asp' ); ?>">
			<div class="asp-suscripcion__trampa" aria-hidden="true">
				<label for="<?php echo esc_attr( $asp_id ); ?>-web"><?php esc_html_e( 'No completes este campo', 'asp' ); ?></label>
				<input id="<?php echo esc_attr( $asp_id ); ?>-web" type="text" name="asp_web" tabindex="-1" autocomplete="off">
			</div>
			<button class="<?php echo esc_attr( $asp_boton ); ?> asp-suscripcion__boton" type="submit"><?php esc_html_e( 'Suscribirme', 'asp' ); ?></button>
			<?php if ( '' !== $asp_nota ) : ?>
				<p class="asp-suscripcion__nota"><?php echo esc_html( $asp_nota ); ?></p>
			<?php endif; ?>
		</form>
		<p class="asp-suscripcion__mensaje" aria-live="polite" tabindex="-1" data-asp-suscripcion-mensaje></p>
	</div>
<?php echo $asp_solo ? '</div>' : '</section>'; ?>
