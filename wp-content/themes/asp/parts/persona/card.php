<?php
/**
 * Box de persona (consejo pastoral). Args: post_id.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

$asp_id     = (int) ( $args['post_id'] ?? get_the_ID() );
$asp_linea  = asp_persona_cargo_iglesia( $asp_id );
$asp_ciudad = (string) get_post_meta( $asp_id, 'persona_ciudad', true );
$asp_pais   = (string) get_post_meta( $asp_id, 'persona_pais', true );
?>
<a class="asp-persona-box" href="<?php echo esc_url( get_permalink( $asp_id ) ); ?>">
	<?php echo asp_persona_foto( $asp_id, 'asp-persona-box__foto' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
	<span class="asp-persona-box__nombre"><?php echo esc_html( get_the_title( $asp_id ) ); ?></span>
	<?php if ( $asp_linea ) : ?>
		<span class="asp-persona-box__iglesia"><?php echo esc_html( $asp_linea ); ?></span>
	<?php endif; ?>
	<?php if ( $asp_ciudad || $asp_pais ) : ?>
		<span class="asp-persona-box__lugar">
			<?php if ( $asp_ciudad ) : ?><span><?php echo esc_html( $asp_ciudad ); ?></span><?php endif; ?>
			<?php if ( $asp_pais ) : ?><span class="asp-label"><?php echo esc_html( $asp_pais ); ?></span><?php endif; ?>
		</span>
	<?php endif; ?>
</a>
