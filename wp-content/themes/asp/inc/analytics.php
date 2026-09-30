<?php
/**
 * Estadísticas de visitas con Cloudflare Web Analytics: sin cookies, sin
 * rastrear personas y sin plugin. El tema imprime el script de Cloudflare
 * solo si en Personalizar → Ante Su Palabra hay un token cargado; vacío, no
 * se imprime nada.
 *
 * No cuenta las visitas de quien está logueado con permiso para editar: el
 * área de redes y quien administra entran seguido y inflarían los números.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

/**
 * Token de Cloudflare Web Analytics, o cadena vacía.
 *
 * @return string
 */
function asp_analytics_token(): string {
	return asp_sanitizar_token_analytics( (string) get_theme_mod( 'asp_cf_analytics_token', '' ) );
}

/**
 * El token es una cadena hexadecimal de 32 caracteres. Se acepta también el
 * fragmento entero que da Cloudflare y se extrae el token de ahí.
 *
 * @param mixed $valor Lo que se pegó en el campo.
 * @return string
 */
function asp_sanitizar_token_analytics( $valor ): string {
	$valor = is_string( $valor ) ? $valor : '';
	if ( preg_match( '/\b([a-f0-9]{32})\b/i', $valor, $m ) ) {
		return strtolower( $m[1] );
	}
	return '';
}

/**
 * Imprime el script de Cloudflare al final de la página.
 *
 * @return void
 */
function asp_analytics_script(): void {
	$token = asp_analytics_token();
	if ( '' === $token || is_customize_preview() || current_user_can( 'edit_posts' ) ) {
		return;
	}
	printf(
		"<script defer src=\"https://static.cloudflareinsights.com/beacon.min.js\" data-cf-beacon='%s'></script>\n",
		esc_attr( (string) wp_json_encode( [ 'token' => $token ] ) )
	);
}
add_action( 'wp_footer', 'asp_analytics_script', 50 );
