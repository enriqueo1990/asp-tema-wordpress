<?php
/**
 * Trae las fotos actuales del consejo pastoral desde antesupalabra.com a la
 * biblioteca de medios local y las vincula a cada ficha de persona.
 * Idempotente: salta a quien ya tiene foto. Mismo uso que seed-local.php.
 */
define( 'WP_USE_THEMES', false );
$_SERVER['HTTP_HOST'] = 'asp-newsite.local';
require '/Users/ibg/Local Sites/asp-newsite/app/public/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

add_filter( 'http_headers_useragent', static fn() => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0 Safari/537.36' );

$fotos = [
	'Greg Travis'        => 'https://antesupalabra.com/wp-content/uploads/2019/02/consejoArtboard-1.png',
	'Dardo Leandi'       => 'https://antesupalabra.com/wp-content/uploads/2019/02/consejoArtboard-1-copy.png',
	'Ernesto Harris'     => 'https://antesupalabra.com/wp-content/uploads/2019/02/consejoArtboard-1-copy-2.png',
	'Cristian Palomares' => 'https://antesupalabra.com/wp-content/uploads/2021/06/cristian.jpg',
	'Ricardo Daglio'     => 'https://antesupalabra.com/wp-content/uploads/2019/08/richard2.png',
	'Joselo Mercado'     => 'https://antesupalabra.com/wp-content/uploads/2021/03/joselo.jpg',
];

foreach ( $fotos as $nombre => $url ) {
	$persona = get_posts( [ 'post_type' => 'persona', 'title' => $nombre, 'posts_per_page' => 1 ] );
	if ( ! $persona ) {
		echo "! no existe la persona $nombre\n";
		continue;
	}
	$pid = $persona[0]->ID;
	if ( absint( get_post_meta( $pid, 'persona_foto', true ) ) ) {
		echo "= $nombre ya tiene foto\n";
		continue;
	}
	$tmp = download_url( $url, 30 );
	if ( is_wp_error( $tmp ) ) {
		echo "! $nombre: " . $tmp->get_error_message() . "\n";
		continue;
	}
	$archivo = [ 'name' => sanitize_file_name( strtolower( str_replace( ' ', '-', $nombre ) ) . '.' . pathinfo( $url, PATHINFO_EXTENSION ) ), 'tmp_name' => $tmp ];
	$att_id  = media_handle_sideload( $archivo, $pid, $nombre );
	if ( is_wp_error( $att_id ) ) {
		@unlink( $tmp );
		echo "! $nombre: " . $att_id->get_error_message() . "\n";
		continue;
	}
	update_post_meta( $att_id, '_wp_attachment_image_alt', $nombre );
	update_post_meta( $pid, 'persona_foto', $att_id );
	$meta = wp_get_attachment_metadata( $att_id );
	printf( "+ %s → adjunto #%d, %dx%d, %s KB\n", $nombre, $att_id, $meta['width'] ?? 0, $meta['height'] ?? 0, round( filesize( get_attached_file( $att_id ) ) / 1024 ) );
}
