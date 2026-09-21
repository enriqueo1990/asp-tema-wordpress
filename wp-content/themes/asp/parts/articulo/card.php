<?php
/**
 * Fila de artículo en listados. Args: post_id.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

$asp_id    = (int) ( $args['post_id'] ?? get_the_ID() );
$asp_serie = asp_serie_de_articulo( $asp_id );
$asp_cat   = get_the_category( $asp_id );
$asp_chip  = $asp_serie ? $asp_serie->name : ( ( ! empty( $asp_cat ) && 'uncategorized' !== $asp_cat[0]->slug ) ? $asp_cat[0]->name : __( 'Artículo', 'asp' ) );
$asp_autor = asp_autor_articulo( $asp_id );
?>
<a class="asp-articulo-item" href="<?php echo esc_url( get_permalink( $asp_id ) ); ?>">
	<span class="asp-row">
		<span class="asp-chip"><?php echo esc_html( $asp_chip ); ?></span>
		<span class="asp-articulo-item__fecha"><?php echo esc_html( asp_fecha_articulo( $asp_id ) ); ?></span>
	</span>
	<span class="asp-articulo-item__titulo"><?php echo esc_html( get_the_title( $asp_id ) ); ?></span>
	<?php if ( $asp_autor['nombre'] ) : ?>
		<span class="asp-row"><span class="asp-label"><?php esc_html_e( 'Autor', 'asp' ); ?></span><span class="asp-muted"><?php echo esc_html( $asp_autor['nombre'] ); ?></span></span>
	<?php endif; ?>
</a>
