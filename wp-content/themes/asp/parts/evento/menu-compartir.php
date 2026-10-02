<?php
/**
 * Menú «Compartir» de la banda de acción (details/summary). Args: compartir
 * (asp_evento_compartir()), titulo.
 *
 * «Copiar enlace» y «Más opciones» nacen ocultos: app.js los muestra solo
 * si el navegador puede copiar o tiene el menú de compartir del sistema.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

$asp_c = (array) ( $args['compartir'] ?? [] );
if ( empty( $asp_c['url'] ) ) {
	return;
}
?>
<details class="ev-menu ev-menu--up" data-asp-menu>
	<summary class="ev-btn ev-btn--secondary"><?php echo asp_ficha_icono( 'share', 'ev-ico--sm' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Compartir', 'asp' ); ?></summary>
	<div class="ev-menu__list">
		<a href="<?php echo esc_url( $asp_c['whatsapp'] ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'WhatsApp', 'asp' ); ?><?php echo asp_aviso_pestana(); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
		<a href="<?php echo esc_url( $asp_c['facebook'] ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Facebook', 'asp' ); ?><?php echo asp_aviso_pestana(); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
		<a href="<?php echo esc_url( $asp_c['correo'] ); ?>"><?php esc_html_e( 'Correo', 'asp' ); ?></a>
		<button type="button" data-asp-copiar="<?php echo esc_attr( $asp_c['url'] ); ?>" data-asp-copiado="<?php esc_attr_e( 'Enlace copiado', 'asp' ); ?>" aria-live="polite" hidden><?php esc_html_e( 'Copiar enlace', 'asp' ); ?></button>
		<button type="button" data-asp-compartir data-titulo="<?php echo esc_attr( (string) ( $args['titulo'] ?? '' ) ); ?>" data-texto="<?php echo esc_attr( $asp_c['texto'] ); ?>" data-url="<?php echo esc_attr( $asp_c['url'] ); ?>" hidden><?php esc_html_e( 'Más opciones', 'asp' ); ?></button>
	</div>
</details>
