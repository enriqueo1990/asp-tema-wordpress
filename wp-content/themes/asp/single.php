<?php
/**
 * Artículo: vuelta al listado, serie o categoría, autor, fecha y cuerpo;
 * al pie, fuente, navegación de la serie, compartir, autor y tres
 * artículos para seguir leyendo. En escritorio, una columna lateral fija
 * con el índice de subtítulos y la serie; sin ninguno de los dos, la
 * columna de lectura va centrada.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$asp_id         = get_the_ID();
	$asp_s          = asp_articulo_serie( $asp_id );
	$asp_serie      = $asp_s['serie'];
	$asp_lista      = $asp_s['lista'];
	$asp_pos        = $asp_s['pos'];
	$asp_cat        = get_the_category( $asp_id );
	$asp_autor      = asp_autor_articulo( $asp_id );
	$asp_autor_pie  = asp_articulo_autor( $asp_id );
	$asp_indice     = asp_articulo_indice( $asp_id );
	$asp_con_serie  = $asp_serie && count( $asp_lista ) > 1;
	$asp_con_aside  = $asp_indice || $asp_con_serie;
	$asp_compartir  = asp_articulo_compartir( $asp_id );
	$asp_relac      = asp_articulos_relacionados( $asp_id );
	?>
	<div class="asp-container asp-section">
		<div class="asp-grid-articulo<?php echo $asp_con_aside ? '' : ' asp-grid-articulo--solo'; ?>">
			<article class="asp-articulo__cabecera">
				<a class="asp-volver" href="<?php echo esc_url( asp_url_articulos() ); ?>"><span aria-hidden="true">←</span> <?php esc_html_e( 'Artículos', 'asp' ); ?></a>
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
				<?php
				/* Apertura: la foto del artículo antes del cuerpo. Sin foto, el
				   artículo arranca en el texto y no queda ningún hueco. */
				echo asp_articulo_apertura( $asp_id ); // phpcs:ignore WordPress.Security.EscapeOutput
				?>
				<div class="asp-prose asp-prose--articulo"><?php the_content(); ?></div>

				<footer class="asp-articulo__cierre">
					<?php if ( $asp_s['anterior'] || $asp_s['siguiente'] ) : ?>
						<nav class="asp-serie-nav" aria-label="<?php esc_attr_e( 'Otros artículos de la serie', 'asp' ); ?>">
							<?php if ( $asp_s['anterior'] ) : ?>
								<a class="asp-serie-nav__enlace" href="<?php echo esc_url( get_permalink( $asp_s['anterior'] ) ); ?>" rel="prev">
									<span class="asp-label"><span aria-hidden="true">←</span> <?php esc_html_e( 'Anterior', 'asp' ); ?></span>
									<span class="asp-serie-nav__titulo"><?php echo esc_html( get_the_title( $asp_s['anterior'] ) ); ?></span>
								</a>
							<?php endif; ?>
							<?php if ( $asp_s['siguiente'] ) : ?>
								<a class="asp-serie-nav__enlace asp-serie-nav__enlace--siguiente" href="<?php echo esc_url( get_permalink( $asp_s['siguiente'] ) ); ?>" rel="next">
									<span class="asp-label"><?php esc_html_e( 'Siguiente', 'asp' ); ?> <span aria-hidden="true">→</span></span>
									<span class="asp-serie-nav__titulo"><?php echo esc_html( get_the_title( $asp_s['siguiente'] ) ); ?></span>
								</a>
							<?php endif; ?>
						</nav>
					<?php endif; ?>

					<div class="asp-compartir">
						<span class="asp-label"><?php esc_html_e( 'Compartir', 'asp' ); ?></span>
						<a class="asp-compartir__boton" href="<?php echo esc_url( $asp_compartir['whatsapp'] ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'WhatsApp', 'asp' ); ?></a>
						<button class="asp-compartir__boton" type="button" data-asp-copiar="<?php echo esc_attr( $asp_compartir['url'] ); ?>" data-asp-copiado="<?php esc_attr_e( 'Enlace copiado', 'asp' ); ?>" aria-live="polite" hidden><?php esc_html_e( 'Copiar enlace', 'asp' ); ?></button>
					</div>

					<?php if ( $asp_autor_pie ) : ?>
						<div class="asp-autor-pie">
							<?php if ( $asp_autor_pie['persona'] ) : ?>
								<?php echo asp_persona_foto( $asp_autor_pie['persona'], 'asp-autor-pie__foto', 'thumbnail' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
							<?php endif; ?>
							<div class="asp-autor-pie__texto">
								<span class="asp-label"><?php esc_html_e( 'Escribió', 'asp' ); ?></span>
								<a class="asp-autor-pie__nombre" href="<?php echo esc_url( $asp_autor_pie['url'] ); ?>"><?php echo esc_html( $asp_autor_pie['nombre'] ); ?></a>
								<?php if ( $asp_autor_pie['linea'] ) : ?>
									<p class="asp-autor-pie__linea"><?php echo esc_html( $asp_autor_pie['linea'] ); ?></p>
								<?php endif; ?>
								<?php if ( $asp_autor_pie['otros'] > 0 ) : ?>
									<a class="asp-cta-link" href="<?php echo esc_url( $asp_autor_pie['url'] ); ?>">
										<?php
										/* translators: %d: cantidad de artículos */
										echo esc_html( sprintf( _n( 'Otro artículo suyo', 'Sus otros %d artículos', $asp_autor_pie['otros'], 'asp' ), $asp_autor_pie['otros'] ) );
										?>
									</a>
								<?php endif; ?>
							</div>
						</div>
					<?php endif; ?>
				</footer>
			</article>

			<?php if ( $asp_con_aside ) : ?>
				<aside class="asp-articulo__aside asp-sticky" aria-label="<?php esc_attr_e( 'Guía del artículo', 'asp' ); ?>">
					<?php if ( $asp_indice ) : ?>
						<nav class="asp-aside-caja" aria-label="<?php esc_attr_e( 'En este artículo', 'asp' ); ?>">
							<span class="asp-label"><?php esc_html_e( 'En este artículo', 'asp' ); ?></span>
							<ol class="asp-indice-art">
								<?php foreach ( $asp_indice as $asp_item ) : ?>
									<li><a href="#<?php echo esc_attr( $asp_item['id'] ); ?>"><?php echo esc_html( $asp_item['texto'] ); ?></a></li>
								<?php endforeach; ?>
							</ol>
						</nav>
					<?php endif; ?>
					<?php if ( $asp_con_serie ) : ?>
						<div class="asp-aside-caja">
							<span class="asp-label"><?php esc_html_e( 'En esta serie', 'asp' ); ?></span>
							<div class="asp-serie-lista">
								<?php foreach ( $asp_lista as $asp_i => $asp_p ) : ?>
									<a class="asp-serie-lista__fila<?php echo $asp_p->ID === $asp_id ? ' is-current' : ''; ?>" href="<?php echo esc_url( get_permalink( $asp_p ) ); ?>"<?php echo $asp_p->ID === $asp_id ? ' aria-current="page"' : ''; ?>>
										<span class="asp-serie-lista__num"><?php echo esc_html( str_pad( (string) ( $asp_i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
										<span><?php echo esc_html( get_the_title( $asp_p ) ); ?></span>
									</a>
								<?php endforeach; ?>
							</div>
						</div>
					<?php endif; ?>
				</aside>
			<?php endif; ?>
		</div>
	</div>

	<?php if ( ! empty( $asp_relac ) ) : ?>
		<section class="asp-container asp-home-bloque asp-relacionados" aria-labelledby="seguir-leyendo">
			<div class="asp-editorial__cab asp-cab-suelto">
				<h2 id="seguir-leyendo" class="asp-seccion__titulo"><?php esc_html_e( 'Seguí leyendo', 'asp' ); ?></h2>
				<a class="asp-cta-link" href="<?php echo esc_url( asp_url_articulos() ); ?>"><?php esc_html_e( 'Todos los artículos', 'asp' ); ?></a>
			</div>
			<div class="asp-grilla-articulos">
				<?php foreach ( $asp_relac as $asp_post ) : ?>
					<?php get_template_part( 'parts/articulo/tarjeta', null, [ 'post_id' => $asp_post->ID ] ); ?>
				<?php endforeach; ?>
			</div>
		</section>
	<?php endif; ?>
<?php
endwhile;
get_footer();
