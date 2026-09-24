<?php
/**
 * Resultados de búsqueda.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="asp-container asp-section">
	<div class="asp-column asp-section__inner asp-section__inner--loose">
		<div class="asp-stack">
			<span class="asp-label"><?php esc_html_e( 'Búsqueda', 'asp' ); ?></span>
			<h1 class="asp-pagina__titulo"><?php echo esc_html( get_search_query() ); ?></h1>
			<?php get_search_form(); ?>
		</div>
		<?php if ( have_posts() ) : ?>
			<div>
				<?php while ( have_posts() ) : the_post(); ?>
					<?php if ( 'evento' === get_post_type() ) : ?>
						<?php get_template_part( 'parts/evento/archivo-item', null, [ 'post_id' => get_the_ID() ] ); ?>
					<?php elseif ( 'predicacion' === get_post_type() ) : ?>
						<?php get_template_part( 'parts/predicacion/fila', null, [ 'post_id' => get_the_ID() ] ); ?>
					<?php else : ?>
						<?php get_template_part( 'parts/articulo/card', null, [ 'post_id' => get_the_ID() ] ); ?>
					<?php endif; ?>
				<?php endwhile; ?>
			</div>
			<nav class="asp-paginacion" aria-label="<?php esc_attr_e( 'Paginación', 'asp' ); ?>"><?php the_posts_pagination( [ 'prev_text' => __( 'Anteriores', 'asp' ), 'next_text' => __( 'Siguientes', 'asp' ) ] ); ?></nav>
		<?php else : ?>
			<p class="asp-muted"><?php esc_html_e( 'No encontramos nada con esas palabras.', 'asp' ); ?></p>
		<?php endif; ?>
	</div>
</div>
<?php
get_footer();
