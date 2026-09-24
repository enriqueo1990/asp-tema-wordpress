<?php
/**
 * Categorías, series, etiquetas y autores de artículos.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

get_header();

$asp_es_serie = is_tax( 'serie' );
?>
<div class="asp-container asp-section">
	<div class="asp-column asp-section__inner asp-section__inner--loose">
		<div class="asp-stack">
			<span class="asp-label"><?php echo $asp_es_serie ? esc_html__( 'Serie', 'asp' ) : ( is_author() ? esc_html__( 'Autor', 'asp' ) : esc_html__( 'Recursos', 'asp' ) ); ?></span>
			<h1 class="asp-pagina__titulo"><?php echo esc_html( is_author() ? get_the_author() : single_term_title( '', false ) ); ?></h1>
			<?php if ( term_description() ) : ?>
				<div class="asp-prose asp-muted"><?php echo wp_kses_post( term_description() ); ?></div>
			<?php endif; ?>
			<a class="asp-cta-link" href="<?php echo esc_url( asp_url_articulos() ); ?>"><?php esc_html_e( 'Todos los artículos', 'asp' ); ?></a>
		</div>
		<?php if ( have_posts() ) : ?>
			<div>
				<?php while ( have_posts() ) : the_post(); ?>
					<?php get_template_part( 'parts/articulo/card', null, [ 'post_id' => get_the_ID() ] ); ?>
				<?php endwhile; ?>
			</div>
			<nav class="asp-paginacion" aria-label="<?php esc_attr_e( 'Paginación', 'asp' ); ?>"><?php the_posts_pagination( [ 'prev_text' => __( 'Anteriores', 'asp' ), 'next_text' => __( 'Siguientes', 'asp' ) ] ); ?></nav>
		<?php else : ?>
			<p class="asp-muted"><?php esc_html_e( 'No hay artículos acá todavía.', 'asp' ); ?></p>
		<?php endif; ?>
	</div>
</div>
<?php
get_footer();
