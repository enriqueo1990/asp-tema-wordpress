<?php
/**
 * Retrato de persona para filas (consejo pastoral en la home). Args: post_id.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

$asp_id    = (int) ( $args['post_id'] ?? get_the_ID() );
$asp_linea = (string) get_post_meta( $asp_id, 'persona_iglesia', true );
?>
<a class="asp-retrato" href="<?php echo esc_url( get_permalink( $asp_id ) ); ?>">
	<?php echo asp_persona_foto( $asp_id, 'asp-retrato__foto' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
	<span class="asp-retrato__nombre"><?php echo esc_html( get_the_title( $asp_id ) ); ?></span>
	<?php if ( $asp_linea ) : ?><span class="asp-retrato__iglesia"><?php echo esc_html( $asp_linea ); ?></span><?php endif; ?>
</a>
