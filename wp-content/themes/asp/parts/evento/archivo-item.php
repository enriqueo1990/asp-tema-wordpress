<?php
/**
 * Fila del archivo por año. Args: post_id, ancho (bool: grilla de cuatro columnas en escritorio).
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

$asp_id       = (int) ( $args['post_id'] ?? get_the_ID() );
$asp_oradores = asp_evento_relacionados( $asp_id, 'evento_oradores', 'persona' );
$asp_aliados  = asp_evento_relacionados( $asp_id, 'evento_aliados', 'aliado' );
$asp_extra    = array_filter(
	[
		implode( ', ', array_map( 'get_the_title', $asp_oradores ) ),
		implode( ', ', array_map( 'get_the_title', $asp_aliados ) ),
	]
);
?>
<a class="asp-archivo-item<?php echo ! empty( $args['ancho'] ) ? ' asp-archivo-item--ancho' : ''; ?>" href="<?php echo esc_url( get_permalink( $asp_id ) ); ?>">
	<span class="asp-archivo-item__fecha"><?php echo esc_html( asp_evento_fecha_texto( $asp_id ) ); ?></span>
	<span class="asp-archivo-item__principal">
		<span class="asp-archivo-item__titulo"><?php echo esc_html( get_the_title( $asp_id ) ); ?></span>
		<?php if ( ! empty( $asp_extra ) ) : ?>
			<span class="asp-archivo-item__extra"><?php echo esc_html( implode( ' · ', $asp_extra ) ); ?></span>
		<?php endif; ?>
	</span>
	<?php get_template_part( 'parts/evento/lugar', null, [ 'post_id' => $asp_id ] ); ?>
	<?php /* En el archivo todo está realizado y el badge sería ruido; en la búsqueda, donde puede aparecer un evento próximo, el estado sí importa. */ ?>
	<?php if ( 'realizado' !== asp_evento_estado( $asp_id ) ) : ?>
		<?php get_template_part( 'parts/evento/badge', null, [ 'post_id' => $asp_id ] ); ?>
	<?php endif; ?>
</a>
