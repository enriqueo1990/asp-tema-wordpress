<?php
/**
 * Badge de estado. Args: post_id.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

$asp_id     = (int) ( $args['post_id'] ?? get_the_ID() );
$asp_estado = asp_evento_estado( $asp_id );
?>
<span class="asp-badge asp-badge--<?php echo esc_attr( $asp_estado ); ?>"><?php echo esc_html( asp_evento_estado_etiqueta( $asp_estado ) ); ?></span>
