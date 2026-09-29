<?php
/**
 * Sube una foto a Medios y la deja como foto de una persona (campo «Foto»).
 * La foto anterior queda en Medios, sin borrar.
 *
 * El sitio recorta las fotos de persona a un cuadrado centrado: una foto
 * vertical u horizontal conviene recortarla antes de hombros para arriba,
 * con la cabeza cerca del borde de arriba.
 *
 * Uso: php tools/foto-persona.php "Nombre y apellido" ruta/a/la/foto.jpg
 */

declare(strict_types=1);

require __DIR__ . '/arranque.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

$nombre  = (string) ( $argv[1] ?? '' );
$archivo = (string) ( $argv[2] ?? '' );
if ( '' === $nombre || ! is_readable( $archivo ) ) {
	fwrite( STDERR, "Uso: php tools/foto-persona.php \"Nombre\" archivo\n" );
	exit( 1 );
}

$persona = get_posts( [ 'post_type' => 'persona', 'title' => $nombre, 'post_status' => 'any', 'posts_per_page' => 1 ] );
if ( ! $persona ) {
	fwrite( STDERR, "No existe la persona «{$nombre}».\n" );
	exit( 1 );
}
$pid      = $persona[0]->ID;
$anterior = absint( get_post_meta( $pid, 'persona_foto', true ) );

/* media_handle_sideload mueve el archivo: se trabaja sobre una copia. */
$tmp = wp_tempnam( basename( $archivo ) );
copy( $archivo, $tmp );
$id = media_handle_sideload( [ 'name' => basename( $archivo ), 'tmp_name' => $tmp ], $pid, $nombre );
if ( is_wp_error( $id ) ) {
	fwrite( STDERR, $id->get_error_message() . "\n" );
	exit( 1 );
}
update_post_meta( $id, '_wp_attachment_image_alt', $nombre );
update_post_meta( $pid, 'persona_foto', $id );

echo "Foto {$id} vinculada a {$nombre} (persona {$pid}); la anterior era {$anterior}.\n";
