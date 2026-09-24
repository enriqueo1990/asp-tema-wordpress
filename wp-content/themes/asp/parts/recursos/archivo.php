<?php
/**
 * /recursos/articulos/ — todos los artículos, con filtros por tema y serie
 * y paginación. Lo carga home.php cuando la vista es el archivo.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

get_header();

$asp_temas  = asp_recursos_temas();
$asp_series = asp_recursos_series();
?>
<div class="asp-container asp-section">
	<div class="asp-archivo-articulos">
		<header class="asp-stack asp-stack--5">
			<a class="asp-volver" href="<?php echo esc_url( asp_url_recursos() ); ?>"><span aria-hidden="true">←</span> <?php esc_html_e( 'Recursos', 'asp' ); ?></a>
			<h1 class="asp-pagina__titulo"><?php esc_html_e( 'Artículos', 'asp' ); ?></h1>
			<?php if ( ! empty( $asp_temas ) || ! empty( $asp_series ) ) : ?>
				<nav aria-label="<?php esc_attr_e( 'Filtrar artículos', 'asp' ); ?>">
					<ul class="asp-temas">
						<li><a class="asp-tema is-active" href="<?php echo esc_url( asp_url_articulos() ); ?>" aria-current="page"><?php esc_html_e( 'Todos', 'asp' ); ?></a></li>
						<?php foreach ( $asp_temas as $asp_tema ) : ?>
							<li><a class="asp-tema" href="<?php echo esc_url( get_category_link( $asp_tema ) ); ?>"><?php echo esc_html( $asp_tema->name ); ?> <span class="asp-tema__n"><?php echo esc_html( (string) $asp_tema->count ); ?></span></a></li>
						<?php endforeach; ?>
						<?php foreach ( $asp_series as $asp_term ) : ?>
							<li><a class="asp-tema" href="<?php echo esc_url( get_term_link( $asp_term ) ); ?>"><?php esc_html_e( 'Serie:', 'asp' ); ?> <?php echo esc_html( $asp_term->name ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				</nav>
			<?php endif; ?>
		</header>
		<?php if ( have_posts() ) : ?>
			<div>
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'parts/articulo/card', null, [ 'post_id' => get_the_ID() ] );
				endwhile;
				?>
			</div>
			<nav class="asp-paginacion" aria-label="<?php esc_attr_e( 'Paginación', 'asp' ); ?>"><?php the_posts_pagination( [ 'prev_text' => __( 'Anteriores', 'asp' ), 'next_text' => __( 'Siguientes', 'asp' ) ] ); ?></nav>
		<?php else : ?>
			<p class="asp-muted"><?php esc_html_e( 'Todavía no hay artículos publicados.', 'asp' ); ?></p>
		<?php endif; ?>
	</div>
</div>
<?php
get_footer();
