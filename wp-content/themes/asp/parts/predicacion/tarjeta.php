<?php
/**
 * Tarjeta de predicación para la grilla del archivo. Args: post_id.
 *
 * La versión en fila (parts/predicacion/fila.php) se sigue usando donde son
 * pocas: la home, la ficha del evento y la de la persona.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

$asp_id     = (int) ( $args['post_id'] ?? get_the_ID() );
$asp_tipo   = asp_predicacion_tipo( $asp_id );
$asp_orador = asp_predicacion_orador( $asp_id );
$asp_dur    = (string) get_post_meta( $asp_id, 'predicacion_duracion', true );
$asp_img    = asp_imagen_destacada( $asp_id, 'asp-tarjeta', 'asp-imagen asp-imagen--tarjeta' );
$asp_meta   = array_filter( [ $asp_orador ? get_the_title( $asp_orador ) : '', $asp_dur ] );
?>
<a class="asp-predicacion-tarjeta" href="<?php echo esc_url( get_permalink( $asp_id ) ); ?>">
	<?php if ( $asp_img ) : ?>
		<span class="asp-predicacion-tarjeta__foto"><?php echo $asp_img; // phpcs:ignore WordPress.Security.EscapeOutput ?><span class="asp-play" aria-hidden="true"></span></span>
	<?php else : ?>
		<span class="asp-predicacion-tarjeta__sinfoto asp-label"><?php echo esc_html( asp_predicacion_tipo_etiqueta( $asp_tipo ) ); ?></span>
	<?php endif; ?>
	<span class="asp-predicacion-tarjeta__titulo"><?php echo esc_html( get_the_title( $asp_id ) ); ?></span>
	<?php if ( ! empty( $asp_meta ) ) : ?>
		<span class="asp-predicacion-tarjeta__meta"><?php echo esc_html( implode( ' · ', $asp_meta ) ); ?></span>
	<?php endif; ?>
</a>
