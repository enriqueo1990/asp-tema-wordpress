<?php
/**
 * Fila de predicación. Args: post_id, sin_evento (bool: no repetir el evento;
 * dentro de su ficha tampoco se repite la fecha, salvo que sea otro día).
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

$asp_id     = (int) ( $args['post_id'] ?? get_the_ID() );
$asp_tipo   = asp_predicacion_tipo( $asp_id );
$asp_partes = asp_predicacion_titulo_partes( $asp_id );
$asp_evento = empty( $args['sin_evento'] ) ? asp_predicacion_evento( $asp_id ) : null;
$asp_pasaje = (string) get_post_meta( $asp_id, 'predicacion_pasaje', true );
$asp_dur    = asp_duracion_legible( (string) get_post_meta( $asp_id, 'predicacion_duracion', true ) );
$asp_meta   = array_filter( [ $asp_partes['orador'], $asp_pasaje, $asp_dur ] );
/* Con miniatura, ella ocupa la columna de la izquierda y el formato pasa a
   la línea de datos. Sin miniatura (audio o texto), sigue el rótulo. */
$asp_img    = asp_imagen_destacada( $asp_id, 'asp-tarjeta', 'asp-imagen asp-imagen--miniatura' );
$asp_fecha  = ( ! empty( $args['sin_evento'] ) && '' === (string) get_post_meta( $asp_id, 'predicacion_fecha', true ) ) ? '' : asp_predicacion_fecha_texto( $asp_id );
?>
<a class="asp-predicacion-fila<?php echo $asp_img ? ' asp-predicacion-fila--con-imagen' : ''; ?>" href="<?php echo esc_url( get_permalink( $asp_id ) ); ?>">
	<?php if ( $asp_img ) : ?>
		<span class="asp-predicacion-fila__foto"><?php echo $asp_img; // phpcs:ignore WordPress.Security.EscapeOutput ?><span class="asp-play" aria-hidden="true"></span></span>
	<?php else : ?>
		<span class="asp-predicacion-fila__tipo asp-label"><?php echo esc_html( asp_predicacion_tipo_etiqueta( $asp_tipo ) ); ?></span>
	<?php endif; ?>
	<span class="asp-predicacion-fila__cuerpo">
		<span class="asp-predicacion-fila__titulo"><?php echo esc_html( $asp_partes['titulo'] ); ?></span>
		<?php
		$asp_datos = $asp_img ? array_merge( [ asp_predicacion_tipo_etiqueta( $asp_tipo ) ], $asp_meta ) : $asp_meta;
		?>
		<?php if ( ! empty( $asp_datos ) ) : ?><span class="asp-predicacion-fila__meta"><?php echo esc_html( implode( ' · ', $asp_datos ) ); ?></span><?php endif; ?>
		<?php if ( $asp_evento ) : ?><span class="asp-predicacion-fila__evento"><?php echo esc_html( implode( ' · ', array_filter( [ get_the_title( $asp_evento ), asp_evento_ciudad( $asp_evento->ID ) ] ) ) ); ?></span><?php endif; ?>
	</span>
	<?php if ( $asp_fecha ) : ?><span class="asp-predicacion-fila__fecha"><?php echo esc_html( $asp_fecha ); ?></span><?php endif; ?>
</a>
