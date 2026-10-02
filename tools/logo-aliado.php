<?php
/**
 * Sube un logo a Medios y lo deja como logo de un aliado (campo «Logo»).
 * Con un tercer argumento, carga también el sitio web del aliado. El logo
 * anterior queda en Medios, sin borrar.
 *
 * Los logos van en tools/logos-aliados/, recortados al borde del dibujo y
 * con fondo transparente: el sitio los muestra a una altura fija.
 *
 * Con --crear, si el aliado no existe lo crea (publicado) con ese nombre.
 *
 * Uso: php tools/logo-aliado.php "Nombre del aliado" ruta/al/logo.png [https://sitio] [--crear]
 */

declare(strict_types=1);

require __DIR__ . '/arranque.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

$crear   = in_array( '--crear', $argv, true );
$args    = array_values( array_diff( array_slice( $argv, 1 ), [ '--crear' ] ) );
$nombre  = (string) ( $args[0] ?? '' );
$archivo = (string) ( $args[1] ?? '' );
$sitio   = (string) ( $args[2] ?? '' );
if ( '' === $nombre || ! is_readable( $archivo ) ) {
	fwrite( STDERR, "Uso: php tools/logo-aliado.php \"Nombre\" archivo [https://sitio] [--crear]\n" );
	exit( 1 );
}

$aliado = get_posts( [ 'post_type' => 'aliado', 'title' => $nombre, 'post_status' => 'any', 'posts_per_page' => 1 ] );
if ( ! $aliado && $crear ) {
	$nuevo = wp_insert_post( [ 'post_type' => 'aliado', 'post_title' => $nombre, 'post_status' => 'publish' ], true );
	if ( is_wp_error( $nuevo ) ) {
		fwrite( STDERR, $nuevo->get_error_message() . "\n" );
		exit( 1 );
	}
	echo "Aliado «{$nombre}» creado ({$nuevo}).\n";
	$aliado = [ get_post( $nuevo ) ];
}
if ( ! $aliado ) {
	fwrite( STDERR, "No existe el aliado «{$nombre}». Para crearlo, agregá --crear.\n" );
	exit( 1 );
}
$aid      = $aliado[0]->ID;
$anterior = absint( get_post_meta( $aid, 'aliado_logo', true ) );

/* media_handle_sideload mueve el archivo: se trabaja sobre una copia. */
$tmp = wp_tempnam( basename( $archivo ) );
copy( $archivo, $tmp );
$id = media_handle_sideload( [ 'name' => basename( $archivo ), 'tmp_name' => $tmp ], $aid, $nombre );
if ( is_wp_error( $id ) ) {
	fwrite( STDERR, $id->get_error_message() . "\n" );
	exit( 1 );
}
update_post_meta( $id, '_wp_attachment_image_alt', $nombre );
update_post_meta( $aid, 'aliado_logo', $id );
if ( '' !== $sitio ) {
	update_post_meta( $aid, 'aliado_url', esc_url_raw( $sitio ) );
}

echo "Logo {$id} vinculado a {$nombre} (aliado {$aid}); el anterior era {$anterior}.\n";
