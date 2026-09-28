<?php
/**
 * Resultados de búsqueda, agrupados por tipo (inc/busqueda.php). Sin nada
 * escrito, solo el buscador; sin resultados, los mismos caminos que la 404.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

get_header();

$asp_con_termino = asp_busqueda_con_termino();
$asp_grupos      = $asp_con_termino ? asp_busqueda_grupos() : [];
?>
<div class="asp-container asp-section">
	<div class="asp-column asp-section__inner asp-section__inner--loose">
		<div class="asp-stack">
			<span class="asp-label"><?php esc_html_e( 'Búsqueda', 'asp' ); ?></span>
			<h1 class="asp-pagina__titulo"><?php echo $asp_con_termino ? esc_html( get_search_query() ) : esc_html__( 'Buscar en el sitio', 'asp' ); ?></h1>
			<?php get_search_form(); ?>
		</div>

		<?php if ( ! empty( $asp_grupos ) ) : ?>
			<?php foreach ( $asp_grupos as $asp_tipo => $asp_grupo ) : ?>
				<section class="asp-busqueda-grupo" aria-labelledby="busqueda-<?php echo esc_attr( $asp_tipo ); ?>">
					<h2 class="asp-label" id="busqueda-<?php echo esc_attr( $asp_tipo ); ?>"><?php echo esc_html( sprintf( '%s · %d', $asp_grupo['nombre'], count( $asp_grupo['posts'] ) ) ); ?></h2>
					<div>
						<?php foreach ( $asp_grupo['posts'] as $asp_post ) : ?>
							<?php if ( 'evento' === $asp_tipo ) : ?>
								<?php get_template_part( 'parts/evento/archivo-item', null, [ 'post_id' => $asp_post->ID ] ); ?>
							<?php elseif ( 'predicacion' === $asp_tipo ) : ?>
								<?php get_template_part( 'parts/predicacion/fila', null, [ 'post_id' => $asp_post->ID ] ); ?>
							<?php elseif ( 'post' === $asp_tipo ) : ?>
								<?php get_template_part( 'parts/articulo/card', null, [ 'post_id' => $asp_post->ID ] ); ?>
							<?php else : ?>
								<?php get_template_part( 'parts/busqueda/fila', null, [ 'post_id' => $asp_post->ID ] ); ?>
							<?php endif; ?>
						<?php endforeach; ?>
					</div>
				</section>
			<?php endforeach; ?>
		<?php elseif ( $asp_con_termino ) : ?>
			<div class="asp-stack">
				<p class="asp-prose"><?php esc_html_e( 'No encontramos nada con esas palabras. Probá con el nombre de un orador, un libro de la Biblia o una ciudad.', 'asp' ); ?></p>
				<div class="asp-row">
					<a class="asp-btn" href="<?php echo esc_url( asp_url_eventos() ); ?>"><?php esc_html_e( 'Ver eventos', 'asp' ); ?></a>
					<a class="asp-cta-link" href="<?php echo esc_url( (string) get_post_type_archive_link( 'predicacion' ) ); ?>"><?php esc_html_e( 'Ver predicaciones', 'asp' ); ?></a>
				</div>
			</div>
		<?php endif; ?>
	</div>
</div>
<?php
get_footer();
