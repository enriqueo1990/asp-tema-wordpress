<?php
/**
 * Formulario de búsqueda.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

$asp_form_id = wp_unique_id( 'asp-buscar-' );
?>
<form class="asp-form asp-buscador" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="visually-hidden" for="<?php echo esc_attr( $asp_form_id ); ?>"><?php esc_html_e( 'Buscar', 'asp' ); ?></label>
	<input id="<?php echo esc_attr( $asp_form_id ); ?>" type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Buscar…', 'asp' ); ?>">
	<button class="asp-btn asp-btn--secundario" type="submit"><?php esc_html_e( 'Buscar', 'asp' ); ?></button>
</form>
