<?php
/**
 * Caja de una serie con sus primeros artículos numerados.
 * Args: term (WP_Term), maximo (int).
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

$asp_term = $args['term'] ?? null;
if ( ! $asp_term instanceof WP_Term ) {
	return;
}
$asp_articulos = asp_articulos_de_serie( $asp_term->term_id );
if ( empty( $asp_articulos ) ) {
	return;
}
$asp_max = (int) ( $args['maximo'] ?? 3 );
?>
<div class="asp-caja asp-stack">
	<div class="asp-row">
		<span class="asp-chip"><?php esc_html_e( 'Serie', 'asp' ); ?></span>
		<span class="asp-label"><?php
			/* translators: %d: cantidad de artículos */
			echo esc_html( sprintf( _n( '%d artículo', '%d artículos', count( $asp_articulos ), 'asp' ), count( $asp_articulos ) ) );
		?></span>
	</div>
	<h3 class="asp-articulo-item__titulo"><a class="asp-link-titulo" href="<?php echo esc_url( get_term_link( $asp_term ) ); ?>"><?php echo esc_html( $asp_term->name ); ?></a></h3>
	<div class="asp-serie-lista">
		<?php foreach ( array_slice( $asp_articulos, 0, $asp_max ) as $asp_i => $asp_post ) : ?>
			<a class="asp-serie-lista__fila" href="<?php echo esc_url( get_permalink( $asp_post ) ); ?>">
				<span class="asp-serie-lista__num"><?php echo esc_html( str_pad( (string) ( $asp_i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
				<span><?php echo esc_html( get_the_title( $asp_post ) ); ?></span>
			</a>
		<?php endforeach; ?>
	</div>
	<a class="asp-cta-link" href="<?php echo esc_url( get_term_link( $asp_term ) ); ?>"><?php esc_html_e( 'Leer la serie', 'asp' ); ?></a>
</div>
