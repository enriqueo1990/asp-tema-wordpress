<?php
/**
 * Página no encontrada.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="asp-container asp-section">
	<div class="asp-column asp-section__inner asp-section__inner--loose">
		<span class="asp-label"><?php esc_html_e( 'Error 404', 'asp' ); ?></span>
		<h1 class="asp-pagina__titulo"><?php esc_html_e( 'Esa página no existe', 'asp' ); ?></h1>
		<p class="asp-prose"><?php esc_html_e( 'Puede que el enlace sea viejo o esté mal escrito. Lo más buscado del sitio son los eventos.', 'asp' ); ?></p>
		<div class="asp-row">
			<a class="asp-btn" href="<?php echo esc_url( asp_url_eventos() ); ?>"><?php esc_html_e( 'Ver eventos', 'asp' ); ?></a>
			<a class="asp-cta-link" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Ir al inicio', 'asp' ); ?></a>
		</div>
		<?php get_search_form(); ?>
	</div>
</div>
<?php
get_footer();
