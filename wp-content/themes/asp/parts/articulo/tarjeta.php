<?php
/**
 * Artículo a la escala de las predicaciones, para el inicio. Args: post_id,
 * variante ('mediana': título más grande, para los destacados de Recursos),
 * fecha (bool: sumar la fecha al autor).
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

$asp_id    = (int) ( $args['post_id'] ?? get_the_ID() );
$asp_serie = asp_serie_de_articulo( $asp_id );
$asp_cat   = get_the_category( $asp_id );
$asp_chip  = $asp_serie ? $asp_serie->name : ( ( ! empty( $asp_cat ) && 'uncategorized' !== $asp_cat[0]->slug ) ? $asp_cat[0]->name : __( 'Artículo', 'asp' ) );
$asp_autor = asp_autor_articulo( $asp_id );
$asp_img   = asp_imagen_destacada( $asp_id, 'asp-tarjeta', 'asp-imagen asp-imagen--tarjeta' );
$asp_var   = 'mediana' === ( $args['variante'] ?? '' ) ? ' asp-articulo-tarjeta--mediana' : '';
?>
<a class="asp-articulo-tarjeta<?php echo esc_attr( $asp_var ); ?><?php echo $asp_img ? '' : ' asp-articulo-tarjeta--sin-foto'; ?>" href="<?php echo esc_url( get_permalink( $asp_id ) ); ?>">
	<?php echo $asp_img; // phpcs:ignore WordPress.Security.EscapeOutput ?>
	<span class="asp-articulo-tarjeta__texto">
		<span class="asp-chip"><?php echo esc_html( $asp_chip ); ?></span>
		<span class="asp-articulo-tarjeta__titulo"><?php echo esc_html( get_the_title( $asp_id ) ); ?></span>
		<?php if ( $asp_autor['nombre'] ) : ?>
			<span class="asp-articulo-tarjeta__meta"><?php echo esc_html( implode( ' · ', array_filter( [ $asp_autor['nombre'], ! empty( $args['fecha'] ) ? asp_fecha_articulo( $asp_id ) : '' ] ) ) ); ?></span>
		<?php endif; ?>
	</span>
</a>
