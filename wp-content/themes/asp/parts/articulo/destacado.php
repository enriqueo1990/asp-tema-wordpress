<?php
/**
 * Artículo en grande. Args: post_id, rotulo (por defecto "Último artículo").
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

$asp_id    = (int) ( $args['post_id'] ?? get_the_ID() );
$asp_serie = asp_serie_de_articulo( $asp_id );
$asp_cat   = get_the_category( $asp_id );
$asp_chip  = $asp_serie ? $asp_serie->name : ( ( ! empty( $asp_cat ) && 'uncategorized' !== $asp_cat[0]->slug ) ? $asp_cat[0]->name : __( 'Artículo', 'asp' ) );
$asp_autor = asp_autor_articulo( $asp_id );
$asp_res   = asp_resumen_articulo( $asp_id, 42 );
$asp_img   = asp_imagen_destacada( $asp_id, 'asp-apertura', 'asp-imagen asp-imagen--apertura', true );
?>
<article class="asp-articulo-destacado<?php echo $asp_img ? ' asp-articulo-destacado--con-imagen' : ''; ?>">
	<?php if ( $asp_img ) : ?>
		<a class="asp-articulo-destacado__foto" href="<?php echo esc_url( get_permalink( $asp_id ) ); ?>" tabindex="-1" aria-hidden="true"><?php echo $asp_img; // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
	<?php endif; ?>
	<div class="asp-row">
		<span class="asp-label"><?php echo esc_html( $args['rotulo'] ?? __( 'Último artículo', 'asp' ) ); ?></span>
		<span class="asp-chip"><?php echo esc_html( $asp_chip ); ?></span>
	</div>
	<h3 class="asp-articulo-destacado__titulo"><a class="asp-link-titulo" href="<?php echo esc_url( get_permalink( $asp_id ) ); ?>"><?php echo esc_html( get_the_title( $asp_id ) ); ?></a></h3>
	<?php if ( $asp_res ) : ?><p class="asp-articulo-destacado__resumen"><?php echo esc_html( $asp_res ); ?></p><?php endif; ?>
	<div class="asp-row asp-articulo-destacado__meta">
		<?php if ( $asp_autor['nombre'] ) : ?><span><?php echo esc_html( $asp_autor['nombre'] ); ?></span><span class="asp-lugar__sep" aria-hidden="true"></span><?php endif; ?>
		<span class="asp-articulo-item__fecha"><?php echo esc_html( asp_fecha_articulo( $asp_id ) ); ?></span>
	</div>
	<a class="asp-cta-link" href="<?php echo esc_url( get_permalink( $asp_id ) ); ?>"><?php esc_html_e( 'Leer', 'asp' ); ?></a>
</article>
