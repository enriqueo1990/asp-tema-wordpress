<?php
/**
 * Serie en banda de color, para Recursos: título grande, autores, y los
 * capítulos numerados para leer en orden. Sin fotos: las partes de una
 * serie suelen compartir la misma imagen. Args: term (WP_Term).
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
$asp_autores = asp_autores_de( $asp_articulos );
$asp_desc    = term_description( $asp_term );
$asp_ancla   = 'serie-' . $asp_term->slug;
?>
<div class="asp-banda-serie">
	<section class="asp-container asp-recursos-bloque asp-serie-banda" aria-labelledby="<?php echo esc_attr( $asp_ancla ); ?>">
		<div class="asp-serie-banda__cab">
			<div class="asp-row">
				<span class="asp-chip"><?php esc_html_e( 'Serie', 'asp' ); ?></span>
				<span class="asp-label asp-label--muted"><?php
					/* translators: %d: cantidad de artículos */
					echo esc_html( sprintf( _n( '%d parte', '%d partes', count( $asp_articulos ), 'asp' ), count( $asp_articulos ) ) );
				?></span>
			</div>
			<h2 id="<?php echo esc_attr( $asp_ancla ); ?>" class="asp-serie-banda__titulo"><a class="asp-link-titulo" href="<?php echo esc_url( get_term_link( $asp_term ) ); ?>"><?php echo esc_html( $asp_term->name ); ?></a></h2>
			<?php if ( $asp_autores ) : ?><p class="asp-serie-banda__autor"><?php echo esc_html( $asp_autores ); ?></p><?php endif; ?>
			<?php if ( $asp_desc ) : ?><div class="asp-serie-banda__desc"><?php echo wp_kses_post( $asp_desc ); ?></div><?php endif; ?>
			<a class="asp-btn" href="<?php echo esc_url( get_permalink( $asp_articulos[0] ) ); ?>"><?php esc_html_e( 'Empezar a leer', 'asp' ); ?></a>
		</div>
		<ol class="asp-serie-banda__partes">
			<?php foreach ( $asp_articulos as $asp_i => $asp_post ) : ?>
				<li>
					<a class="asp-serie-banda__parte" href="<?php echo esc_url( get_permalink( $asp_post ) ); ?>">
						<span class="asp-serie-banda__num" aria-hidden="true"><?php echo esc_html( str_pad( (string) ( $asp_i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
						<span class="asp-serie-banda__texto">
							<span class="asp-serie-banda__parte-titulo"><?php echo esc_html( get_the_title( $asp_post ) ); ?></span>
							<span class="asp-serie-banda__resumen"><?php echo esc_html( asp_resumen_articulo( $asp_post->ID, 18 ) ); ?></span>
						</span>
					</a>
				</li>
			<?php endforeach; ?>
		</ol>
	</section>
</div>
