<?php
/**
 * Flyer contenido sobre la banda tonal, nunca recortado.
 * Args: post_id, clase (extra), tamano.
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

$asp_img = wp_get_attachment_image(
	$asp_flyer,
	(string) ( $args['tamano'] ?? 'asp-flyer' ),
	false,
	[
		'alt'     => sprintf( /* translators: %s: título del evento */ __( 'Flyer de %s', 'asp' ), get_the_title( $asp_id ) ),
		'loading' => (string) ( $args['loading'] ?? 'lazy' ),
	]
);

if ( ! $asp_img ) {
	return;
}
?>
<div class="asp-flyer <?php echo esc_attr( (string) ( $args['clase'] ?? '' ) ); ?>">
	<?php echo $asp_img; // phpcs:ignore WordPress.Security.EscapeOutput ?>
</div>
