<?php
/**
 * Artículo: serie y posición, autor, fecha, cuerpo y navegación de la serie.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$asp_id     = get_the_ID();
	$asp_serie  = asp_serie_de_articulo( $asp_id );
	$asp_lista  = $asp_serie ? asp_articulos_de_serie( $asp_serie->term_id ) : [];
	$asp_pos    = 0;
	foreach ( $asp_lista as $asp_i => $asp_p ) {
		if ( $asp_p->ID === $asp_id ) {
			$asp_pos = $asp_i + 1;
		}
	}
	$asp_cat   = get_the_category( $asp_id );
	$asp_autor = asp_autor_articulo( $asp_id );
	?>
	<div class="asp-container asp-section">
		<div class="asp-grid-articulo">
			<article class="asp-articulo__cabecera">
				<div class="asp-row">
					<?php if ( $asp_serie ) : ?>
						<span class="asp-chip"><?php esc_html_e( 'Serie', 'asp' ); ?></span>
						<a class="asp-label" href="<?php echo esc_url( get_term_link( $asp_serie ) ); ?>"><?php echo esc_html( $asp_serie->name ); ?><?php if ( $asp_pos ) : ?> · <?php echo esc_html( str_pad( (string) $asp_pos, 2, '0', STR_PAD_LEFT ) ); ?> <?php esc_html_e( 'de', 'asp' ); ?> <?php echo esc_html( (string) count( $asp_lista ) ); ?><?php endif; ?></a>
					<?php elseif ( ! empty( $asp_cat ) && 'uncategorized' !== $asp_cat[0]->slug ) : ?>
						<a class="asp-chip" href="<?php echo esc_url( get_category_link( $asp_cat[0] ) ); ?>"><?php echo esc_html( $asp_cat[0]->name ); ?></a>
					<?php else : ?>
						<span class="asp-chip"><?php esc_html_e( 'Artículo', 'asp' ); ?></span>
					<?php endif; ?>
				</div>
				<h1 class="asp-articulo__titulo"><?php the_title(); ?></h1>
				<div class="asp-articulo__meta">
					<?php if ( $asp_autor['nombre'] ) : ?>
						<div><span class="asp-label"><?php esc_html_e( 'Autor', 'asp' ); ?></span><a href="<?php echo esc_url( $asp_autor['url'] ); ?>"><?php echo esc_html( $asp_autor['nombre'] ); ?></a></div>
					<?php endif; ?>
					<div><span class="asp-label"><?php esc_html_e( 'Fecha', 'asp' ); ?></span><time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( asp_fecha_articulo( $asp_id ) ); ?></time></div>
				</div>
				<div class="asp-prose"><?php the_content(); ?></div>
			</article>

			<?php if ( $asp_serie && count( $asp_lista ) > 1 ) : ?>
				<aside class="asp-aside-caja asp-sticky" aria-label="<?php esc_attr_e( 'En esta serie', 'asp' ); ?>">
					<span class="asp-label"><?php esc_html_e( 'En esta serie', 'asp' ); ?></span>
					<div class="asp-serie-lista">
						<?php foreach ( $asp_lista as $asp_i => $asp_p ) : ?>
							<a class="asp-serie-lista__fila<?php echo $asp_p->ID === $asp_id ? ' is-current' : ''; ?>" href="<?php echo esc_url( get_permalink( $asp_p ) ); ?>"<?php echo $asp_p->ID === $asp_id ? ' aria-current="page"' : ''; ?>>
								<span class="asp-serie-lista__num"><?php echo esc_html( str_pad( (string) ( $asp_i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
								<span><?php echo esc_html( get_the_title( $asp_p ) ); ?></span>
							</a>
						<?php endforeach; ?>
					</div>
				</aside>
			<?php endif; ?>
		</div>
	</div>
<?php
endwhile;
get_footer();
