<?php
/**
 * Compartir el evento. Args: post_id.
 *
 * «Copiar enlace» y «Más opciones» nacen ocultos: app.js los muestra solo si
 * el navegador puede copiar o tiene el menú de compartir del sistema.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

$asp_id = (int) ( $args['post_id'] ?? get_the_ID() );
$asp_c  = asp_evento_compartir( $asp_id );
?>
<div class="asp-compartir asp-compartir--evento">
	<h2 class="asp-label"><?php esc_html_e( 'Compartir', 'asp' ); ?></h2>
	<div class="asp-compartir__botones">
		<a class="asp-compartir__boton" href="<?php echo esc_url( $asp_c['whatsapp'] ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'WhatsApp', 'asp' ); ?><?php echo asp_aviso_pestana(); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
		<a class="asp-compartir__boton" href="<?php echo esc_url( $asp_c['facebook'] ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Facebook', 'asp' ); ?><?php echo asp_aviso_pestana(); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
		<a class="asp-compartir__boton" href="<?php echo esc_url( $asp_c['correo'] ); ?>"><?php esc_html_e( 'Correo', 'asp' ); ?></a>
		<button class="asp-compartir__boton" type="button" data-asp-copiar="<?php echo esc_attr( $asp_c['url'] ); ?>" data-asp-copiado="<?php esc_attr_e( 'Enlace copiado', 'asp' ); ?>" aria-live="polite" hidden><?php esc_html_e( 'Copiar enlace', 'asp' ); ?></button>
		<button class="asp-compartir__boton" type="button" data-asp-compartir data-titulo="<?php echo esc_attr( html_entity_decode( get_the_title( $asp_id ), ENT_QUOTES, 'UTF-8' ) ); ?>" data-texto="<?php echo esc_attr( $asp_c['texto'] ); ?>" data-url="<?php echo esc_attr( $asp_c['url'] ); ?>" hidden><?php esc_html_e( 'Más opciones', 'asp' ); ?></button>
	</div>
</div>
