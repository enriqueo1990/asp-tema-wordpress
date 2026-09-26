<?php
/**
 * /iniciativas/ — cada iniciativa como un bloque propio, en el mismo orden
 * y con el mismo numeral que en el inicio.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="asp-container">
	<header class="asp-eventos__cab">
		<h1 class="asp-pagina__titulo"><?php echo esc_html( post_type_archive_title( '', false ) ); ?></h1>
	</header>
</div>
<?php if ( have_posts() ) : ?>
	<div class="asp-iniciativas">
		<?php $asp_n = 0; ?>
		<?php while ( have_posts() ) : the_post(); ?>
			<?php get_template_part( 'parts/iniciativa/fila', null, [ 'post_id' => get_the_ID(), 'numero' => ++$asp_n ] ); ?>
		<?php endwhile; ?>
	</div>
<?php else : ?>
	<div class="asp-container asp-section"><p class="asp-muted"><?php esc_html_e( 'Todavía no hay iniciativas publicadas.', 'asp' ); ?></p></div>
<?php endif; ?>
<?php
get_footer();
