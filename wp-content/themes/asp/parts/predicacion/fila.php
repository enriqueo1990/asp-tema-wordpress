<?php
/**
 * Fila de predicación. Args: post_id, sin_evento (bool: no repetir el evento).
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

$asp_id     = (int) ( $args['post_id'] ?? get_the_ID() );
$asp_tipo   = asp_predicacion_tipo( $asp_id );
$asp_orador = asp_predicacion_orador( $asp_id );
$asp_evento = empty( $args['sin_evento'] ) ? asp_predicacion_evento( $asp_id ) : null;
$asp_pasaje = (string) get_post_meta( $asp_id, 'predicacion_pasaje', true );
$asp_dur    = (string) get_post_meta( $asp_id, 'predicacion_duracion', true );
$asp_meta   = array_filter( [ $asp_orador ? get_the_title( $asp_orador ) : '', $asp_pasaje, $asp_dur ] );
?>
<a class="asp-predicacion-fila" href="<?php echo esc_url( get_permalink( $asp_id ) ); ?>">
	<span class="asp-predicacion-fila__tipo asp-label"><?php echo esc_html( asp_predicacion_tipo_etiqueta( $asp_tipo ) ); ?></span>
	<span class="asp-predicacion-fila__cuerpo">
		<span class="asp-predicacion-fila__titulo"><?php echo esc_html( get_the_title( $asp_id ) ); ?></span>
		<?php if ( ! empty( $asp_meta ) ) : ?><span class="asp-predicacion-fila__meta"><?php echo esc_html( implode( ' · ', $asp_meta ) ); ?></span><?php endif; ?>
		<?php if ( $asp_evento ) : ?><span class="asp-predicacion-fila__evento"><?php echo esc_html( get_the_title( $asp_evento ) ); ?> · <?php echo esc_html( asp_evento_ciudad( $asp_evento->ID ) ); ?></span><?php endif; ?>
	</span>
	<span class="asp-predicacion-fila__fecha"><?php echo esc_html( asp_predicacion_fecha_texto( $asp_id ) ); ?></span>
</a>
