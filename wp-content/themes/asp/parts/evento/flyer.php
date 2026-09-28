<?php
/**
 * Flyer contenido sobre la banda tonal, nunca recortado.
 * Args: post_id, clase (extra), tamano, loading, sizes, alt.
 * alt vacío cuando el título del evento está al lado dentro del mismo enlace.
 * No imprime nada si el evento no tiene flyer.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

$asp_id    = (int) ( $args['post_id'] ?? get_the_ID() );
$asp_flyer = absint( get_post_meta( $asp_id, 'evento_flyer', true ) );

if ( ! $asp_flyer ) {
	return;
}

$asp_attr = [
	'alt'     => isset( $args['alt'] ) ? (string) $args['alt'] : sprintf( /* translators: %s: título del evento */ __( 'Flyer de %s', 'asp' ), get_the_title( $asp_id ) ),
	'loading' => (string) ( $args['loading'] ?? 'lazy' ),
];
if ( ! empty( $args['sizes'] ) ) {
	$asp_attr['sizes'] = (string) $args['sizes'];
}

$asp_img = wp_get_attachment_image( $asp_flyer, (string) ( $args['tamano'] ?? 'asp-flyer' ), false, $asp_attr );

if ( ! $asp_img ) {
	return;
}
?>
<?php
/* Flyer apaisado (los de talleres vienen 16:9): la banda pasa a 4:3 para que
   no quede chico dentro de un cuadrado. Nunca se recorta. */
$asp_meta     = wp_get_attachment_metadata( $asp_flyer );
$asp_apaisado = is_array( $asp_meta ) && ! empty( $asp_meta['width'] ) && ! empty( $asp_meta['height'] ) && $asp_meta['width'] > $asp_meta['height'] * 1.2;
?>
<div class="asp-flyer<?php echo $asp_apaisado ? ' asp-flyer--apaisado' : ''; ?> <?php echo esc_attr( (string) ( $args['clase'] ?? '' ) ); ?>">
	<?php echo $asp_img; // phpcs:ignore WordPress.Security.EscapeOutput ?>
</div>
