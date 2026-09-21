<?php
/**
 * Fila compacta de evento (home). Args: post_id.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

$asp_id     = (int) ( $args['post_id'] ?? get_the_ID() );
$asp_estado = asp_evento_estado( $asp_id );
?>
<a class="asp-evento-fila" href="<?php echo esc_url( get_permalink( $asp_id ) ); ?>">
	<span class="asp-evento-fila__meta">
		<span class="asp-label"><?php echo esc_html( asp_evento_estado_etiqueta( $asp_estado ) ); ?></span>
		<span class="asp-evento-fila__fecha"><?php echo esc_html( asp_evento_fecha_texto( $asp_id ) ); ?></span>
	</span>
	<span class="asp-evento-fila__titulo"><?php echo esc_html( get_the_title( $asp_id ) ); ?></span>
	<?php get_template_part( 'parts/evento/lugar', null, [ 'post_id' => $asp_id ] ); ?>
</a>
