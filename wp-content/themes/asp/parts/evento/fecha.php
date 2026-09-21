<?php
/**
 * Fecha del evento. Args: post_id, variante (inline|xl|grande).
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

$asp_id       = (int) ( $args['post_id'] ?? get_the_ID() );
$asp_variante = (string) ( $args['variante'] ?? 'inline' );
$asp_inicio   = asp_fecha_iso( (string) get_post_meta( $asp_id, 'evento_fecha_inicio', true ) );

if ( '' === $asp_inicio ) {
	return;
}

if ( 'grande' === $asp_variante ) :
	$asp_grande = asp_evento_fecha_grande( $asp_id );
	?>
	<div class="asp-fecha-grande">
		<time class="asp-fecha-grande__dias" datetime="<?php echo esc_attr( $asp_inicio ); ?>"><?php echo esc_html( $asp_grande['dias'] ); ?></time>
		<span class="asp-fecha-grande__mes"><?php echo esc_html( $asp_grande['mes'] ); ?></span>
	</div>
<?php else : ?>
	<time class="asp-fecha<?php echo 'xl' === $asp_variante ? ' asp-fecha--xl' : ''; ?>" datetime="<?php echo esc_attr( $asp_inicio ); ?>"><?php echo esc_html( asp_evento_fecha_texto( $asp_id ) ); ?></time>
<?php endif;
