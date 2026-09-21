<?php
/**
 * Plantilla de respaldo: listado genérico.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="asp-container asp-section">
	<div class="asp-column asp-section__inner">
		<?php if ( have_posts() ) : ?>
			<div class="asp-stack asp-stack--2">
				<?php while ( have_posts() ) : the_post(); ?>
					<?php get_template_part( 'parts/articulo/card', null, [ 'post_id' => get_the_ID() ] ); ?>
				<?php endwhile; ?>
			</div>
			<nav class="asp-paginacion" aria-label="<?php esc_attr_e( 'Paginación', 'asp' ); ?>"><?php the_posts_pagination( [ 'prev_text' => __( 'Anteriores', 'asp' ), 'next_text' => __( 'Siguientes', 'asp' ) ] ); ?></nav>
		<?php else : ?>
			<p><?php esc_html_e( 'No hay contenido para mostrar.', 'asp' ); ?></p>
		<?php endif; ?>
	</div>
</div>
<?php
get_footer();
