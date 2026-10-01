<?php
/**
 * Compartir un evento: WhatsApp, Facebook, correo, copiar el enlace y, en
 * los teléfonos que lo tienen, el menú de compartir del sistema (Instagram,
 * Telegram, mensajes). Todo son enlaces o botones del tema: nada de
 * terceros ni de scripts de redes.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

/**
 * Texto con el que se comparte el evento: tipo y título, fecha y ciudad.
 * Así el mensaje se entiende aunque la vista previa no cargue.
 *
 * @param int $post_id ID del evento.
 * @return string
 */
function asp_evento_compartir_texto( int $post_id ): string {
	$iniciativa = asp_evento_iniciativa( $post_id );
	$titulo     = html_entity_decode( get_the_title( $post_id ), ENT_QUOTES, 'UTF-8' );
	if ( $iniciativa ) {
		/* translators: 1: tipo de evento, 2: título */
		$titulo = sprintf( __( '%1$s: %2$s', 'asp' ), html_entity_decode( get_the_title( $iniciativa ), ENT_QUOTES, 'UTF-8' ), $titulo );
	}
	return implode( ' · ', array_filter( [ $titulo, asp_evento_fecha_texto( $post_id ), asp_evento_ciudad( $post_id ) ] ) );
}

/**
 * Enlaces para compartir el evento.
 *
 * @param int $post_id ID del evento.
 * @return array{url:string,texto:string,whatsapp:string,facebook:string,correo:string}
 */
function asp_evento_compartir( int $post_id ): array {
	$url   = (string) get_permalink( $post_id );
	$texto = asp_evento_compartir_texto( $post_id );
	return [
		'url'      => $url,
		'texto'    => $texto,
		'whatsapp' => 'https://wa.me/?text=' . rawurlencode( $texto . ' ' . $url ),
		'facebook' => 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode( $url ),
		'correo'   => 'mailto:?subject=' . rawurlencode( $texto ) . '&body=' . rawurlencode( $texto . "\n\n" . $url ),
	];
}
