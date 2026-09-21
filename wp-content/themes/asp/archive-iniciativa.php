<?php
/**
 * /iniciativas/ — las iniciativas como tarjetas.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="asp-container asp-section">
	<div class="asp-section__inner asp-section__inner--loose">
		<h1 class="asp-pagina__titulo"><?php echo esc_html( post_type_archive_title( '', false ) ); ?></h1>
		<?php if ( have_posts() ) : ?>
			<div class="asp-grid-2">
				<?php while ( have_posts() ) : the_post(); ?>
					<?php get_template_part( 'parts/iniciativa/card', null, [ 'post_id' => get_the_ID() ] ); ?>
				<?php endwhile; ?>
			</div>
		<?php else : ?>
			<p class="asp-muted"><?php esc_html_e( 'Todavía no hay iniciativas publicadas.', 'asp' ); ?></p>
		<?php endif; ?>
	</div>
</div>
<?php
get_footer();
