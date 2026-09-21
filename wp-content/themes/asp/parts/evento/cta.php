<?php
/**
 * Acción según estado. Args: post_id, con_plataforma (bool), bloque (bool).
 * - abierta con URL: botón activo y nota "Se abre en Eventbrite".
 * - cerrada / agotado: una línea con el motivo. Sin botón: no hay nada que
 *   tocar, y el badge del evento ya dice el estado.
 * - reserva / en_curso / realizado: nada; el badge ocupa ese lugar.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

$asp_id       = (int) ( $args['post_id'] ?? get_the_ID() );
$asp_estado   = asp_evento_estado( $asp_id );
$asp_bloque   = ! empty( $args['bloque'] );
$asp_con_plat = ! empty( $args['con_plataforma'] );
$asp_clase    = 'asp-btn' . ( $asp_bloque ? ' asp-btn--block' : '' );
$asp_motivo   = asp_evento_motivo( $asp_estado );

if ( asp_evento_tiene_boton( $asp_id ) ) :
	$asp_url  = asp_evento_url_registro( $asp_id );
	$asp_plat = asp_evento_plataforma( $asp_url );
	?>
	<div class="asp-stack asp-stack--2">
		<a class="<?php echo esc_attr( $asp_clase ); ?>" href="<?php echo esc_url( $asp_url ); ?>" target="_blank" rel="noopener"><?php echo esc_html( asp_evento_boton_texto( $asp_id, $asp_con_plat ) ); ?></a>
		<?php if ( $asp_con_plat && $asp_plat ) : ?>
			<span class="asp-cta__nota"><?php
				/* translators: %s: plataforma de inscripción */
				echo esc_html( sprintf( __( 'Se abre en %s', 'asp' ), $asp_plat ) );
			?></span>
		<?php endif; ?>
	</div>
<?php elseif ( '' !== $asp_motivo ) : ?>
	<p class="asp-cta__cerrado"><?php echo esc_html( $asp_motivo ); ?></p>
<?php endif;
