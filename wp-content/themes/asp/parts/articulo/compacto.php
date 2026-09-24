<?php
/**
 * Artículo en lista compacta, sin foto: fecha, título, autor y tema.
 * Args: post_id.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

$asp_id    = (int) ( $args['post_id'] ?? get_the_ID() );
$asp_cat   = get_the_category( $asp_id );
$asp_tema  = ( ! empty( $asp_cat ) && 'uncategorized' !== $asp_cat[0]->slug ) ? $asp_cat[0]->name : '';
$asp_autor = asp_autor_articulo( $asp_id );
$asp_meta  = array_filter( [ $asp_autor['nombre'], $asp_tema ] );
?>
<a class="asp-articulo-compacto" href="<?php echo esc_url( get_permalink( $asp_id ) ); ?>">
	<time class="asp-articulo-compacto__fecha" datetime="<?php echo esc_attr( get_the_date( 'c', $asp_id ) ); ?>"><?php echo esc_html( asp_fecha_articulo( $asp_id ) ); ?></time>
	<span class="asp-articulo-compacto__texto">
		<span class="asp-articulo-compacto__titulo"><?php echo esc_html( get_the_title( $asp_id ) ); ?></span>
		<?php if ( ! empty( $asp_meta ) ) : ?>
			<span class="asp-articulo-compacto__meta"><?php echo esc_html( implode( ' · ', $asp_meta ) ); ?></span>
		<?php endif; ?>
	</span>
</a>
