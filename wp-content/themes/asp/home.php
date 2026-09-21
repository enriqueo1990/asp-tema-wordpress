<?php
/**
 * /recursos/ — el blog y el archivo de conferencias en una página.
 *
 * Arriba, artículos: el último en grande, filtros por categoría y serie,
 * listado con paginación real. Abajo, conferencias y talleres realizados,
 * agrupados por año, que llevan a su ficha. Una sección sin contenido no se
 * imprime.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

get_header();

$asp_pagina_id = (int) get_option( 'page_for_posts' );
$asp_titulo    = $asp_pagina_id ? get_the_title( $asp_pagina_id ) : __( 'Recursos', 'asp' );
$asp_bajada    = $asp_pagina_id ? wp_strip_all_tags( (string) get_post_field( 'post_content', $asp_pagina_id ) ) : '';
$asp_primera   = ! is_paged();
$asp_series    = get_terms( [ 'taxonomy' => 'serie', 'hide_empty' => true ] );
$asp_series    = is_array( $asp_series ) ? $asp_series : [];
$asp_cats      = get_categories( [ 'hide_empty' => true, 'exclude' => [ (int) get_option( 'default_category' ) ] ] );
$asp_pasados   = asp_eventos_pasados_por_anio();
$asp_predic    = asp_predicaciones( [], 6 );
$asp_destacado = ( $asp_primera && have_posts() ) ? $GLOBALS['wp_query']->posts[0] : null;
?>
<div class="asp-container">
	<header class="asp-recursos__cab">
		<div class="asp-stack asp-stack--3">
			<h1 class="asp-pagina__titulo"><?php echo esc_html( $asp_titulo ); ?></h1>
			<?php if ( $asp_bajada ) : ?><p class="asp-recursos__bajada"><?php echo esc_html( $asp_bajada ); ?></p><?php endif; ?>
		</div>
		<nav class="asp-recursos__saltos" aria-label="<?php esc_attr_e( 'Secciones de Recursos', 'asp' ); ?>">
			<a href="#articulos"><?php esc_html_e( 'Artículos', 'asp' ); ?></a>
			<?php if ( ! empty( $asp_predic ) ) : ?><a href="#predicaciones"><?php esc_html_e( 'Predicaciones', 'asp' ); ?></a><?php endif; ?>
			<?php if ( ! empty( $asp_pasados ) ) : ?><a href="#conferencias"><?php esc_html_e( 'Conferencias', 'asp' ); ?></a><?php endif; ?>
		</nav>
	</header>
</div>

<?php if ( have_posts() ) : ?>
	<section class="asp-container asp-editorial asp-section--rule" id="articulos" aria-labelledby="recursos-articulos">
		<div class="asp-editorial__cab">
			<h2 id="recursos-articulos" class="asp-label"><?php esc_html_e( 'Artículos', 'asp' ); ?></h2>
			<?php if ( ! empty( $asp_cats ) || ! empty( $asp_series ) ) : ?>
				<ul class="asp-filtros asp-filtros--columna">
					<li class="<?php echo $asp_primera ? 'is-active' : ''; ?>"><a href="<?php echo esc_url( asp_url_recursos() ); ?>"><?php esc_html_e( 'Todos', 'asp' ); ?></a></li>
					<?php foreach ( $asp_cats as $asp_cat ) : ?>
						<li><a href="<?php echo esc_url( get_category_link( $asp_cat ) ); ?>"><?php echo esc_html( $asp_cat->name ); ?></a></li>
					<?php endforeach; ?>
					<?php foreach ( $asp_series as $asp_term ) : ?>
						<li><a href="<?php echo esc_url( get_term_link( $asp_term ) ); ?>"><?php echo esc_html( $asp_term->name ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
		<div class="asp-editorial__cuerpo asp-editorial__cuerpo--ancho">
			<?php if ( $asp_destacado ) : ?>
				<?php get_template_part( 'parts/articulo/destacado', null, [ 'post_id' => $asp_destacado->ID ] ); ?>
			<?php endif; ?>
			<div>
				<?php
				while ( have_posts() ) :
					the_post();
					if ( $asp_destacado && get_the_ID() === $asp_destacado->ID ) {
						continue;
					}
					get_template_part( 'parts/articulo/card', null, [ 'post_id' => get_the_ID() ] );
				endwhile;
				?>
			</div>
			<nav class="asp-paginacion" aria-label="<?php esc_attr_e( 'Paginación', 'asp' ); ?>"><?php the_posts_pagination( [ 'prev_text' => __( 'Anteriores', 'asp' ), 'next_text' => __( 'Siguientes', 'asp' ) ] ); ?></nav>
			<?php if ( $asp_primera && ! empty( $asp_series ) ) : ?>
				<div class="asp-stack asp-stack--5">
					<span class="asp-label"><?php esc_html_e( 'Series', 'asp' ); ?></span>
					<?php foreach ( $asp_series as $asp_term ) : ?>
						<?php get_template_part( 'parts/articulo/serie', null, [ 'term' => $asp_term ] ); ?>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</section>
<?php elseif ( empty( $asp_pasados ) && empty( $asp_predic ) ) : ?>
	<div class="asp-container asp-section"><p class="asp-muted"><?php esc_html_e( 'Todavía no hay artículos publicados.', 'asp' ); ?></p></div>
<?php endif; ?>

<?php if ( ! empty( $asp_predic ) ) : ?>
	<section class="asp-container asp-editorial asp-section--rule" id="predicaciones" aria-labelledby="recursos-predicaciones">
		<div class="asp-editorial__cab">
			<h2 id="recursos-predicaciones" class="asp-label"><?php esc_html_e( 'Predicaciones', 'asp' ); ?></h2>
			<a class="asp-cta-link" href="<?php echo esc_url( (string) get_post_type_archive_link( 'predicacion' ) ); ?>"><?php esc_html_e( 'Todas las predicaciones', 'asp' ); ?></a>
		</div>
		<div class="asp-editorial__cuerpo asp-editorial__cuerpo--ancho">
			<div>
				<?php foreach ( $asp_predic as $asp_p ) : ?>
					<?php get_template_part( 'parts/predicacion/fila', null, [ 'post_id' => $asp_p->ID ] ); ?>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
<?php endif; ?>

<?php if ( ! empty( $asp_pasados ) ) : ?>
	<section class="asp-container asp-editorial asp-section--rule" id="conferencias" aria-labelledby="recursos-conferencias">
		<div class="asp-editorial__cab">
			<h2 id="recursos-conferencias" class="asp-label"><?php esc_html_e( 'Conferencias y talleres realizados', 'asp' ); ?></h2>
			<a class="asp-cta-link" href="<?php echo esc_url( asp_url_eventos() ); ?>"><?php esc_html_e( 'Próximos eventos', 'asp' ); ?></a>
		</div>
		<div class="asp-editorial__cuerpo asp-editorial__cuerpo--ancho asp-stack--6">
			<?php foreach ( $asp_pasados as $asp_anio => $asp_eventos ) : ?>
				<div class="asp-stack asp-stack--3">
					<div class="asp-anio"><span class="asp-anio__num"><?php echo esc_html( (string) $asp_anio ); ?></span></div>
					<div>
						<?php foreach ( $asp_eventos as $asp_post ) : ?>
							<?php get_template_part( 'parts/evento/archivo-item', null, [ 'post_id' => $asp_post->ID ] ); ?>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</section>
<?php endif; ?>
<?php
get_footer();
