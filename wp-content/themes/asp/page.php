<?php
/**
 * Página genérica.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<article class="asp-container asp-section">
		<div class="asp-column asp-section__inner asp-section__inner--loose">
			<h1 class="asp-pagina__titulo"><?php the_title(); ?></h1>
			<div class="asp-prose"><?php the_content(); ?></div>
		</div>
	</article>
<?php
endwhile;
get_footer();
