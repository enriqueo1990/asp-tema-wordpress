<?php
/**
 * Baja la miniatura de YouTube de cada predicación y la deja como imagen
 * destacada, para que los listados se vean y no sean solo texto.
 *
 * Se guarda una copia local: el sitio no le pide imágenes a Google en cada
 * carga, igual que con las fuentes.
 *
 * Prueba maxresdefault (1280×720) y cae a sddefault y hqdefault, que existen
 * siempre pero vienen en 4:3 con bandas negras; el recorte a 16:9 del tema
 * se las come.
 *
 * Idempotente: salta las que ya tienen imagen.
 *
 * Uso: php tools/miniaturas-predicaciones.php
 */

declare(strict_types=1);

require __DIR__ . '/arranque.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

$predicaciones = get_posts( [ 'post_type' => 'predicacion', 'post_status' => 'any', 'posts_per_page' => -1 ] );
$puestas = 0;
$saltadas = 0;
$fallidas = 0;

foreach ( $predicaciones as $p ) {
	if ( has_post_thumbnail( $p->ID ) ) {
		$saltadas++;
		continue;
	}
	$url   = (string) get_post_meta( $p->ID, 'predicacion_video_url', true );
	$yt_id = $url ? asp_youtube_id( $url ) : '';
	if ( ! $yt_id ) {
		$saltadas++;
		continue;
	}

	$adjunto = null;
	foreach ( [ 'maxresdefault', 'sddefault', 'hqdefault' ] as $variante ) {
		$respuesta = wp_remote_head( "https://i.ytimg.com/vi/{$yt_id}/{$variante}.jpg", [ 'timeout' => 15 ] );
		if ( is_wp_error( $respuesta ) || 200 !== (int) wp_remote_retrieve_response_code( $respuesta ) ) {
			continue;
		}
		$adjunto = media_sideload_image(
			"https://i.ytimg.com/vi/{$yt_id}/{$variante}.jpg",
			$p->ID,
			/* translators: %s: título de la predicación */
			sprintf( __( 'Miniatura de %s', 'asp' ), $p->post_title ),
			'id'
		);
		break;
	}

	if ( null === $adjunto || is_wp_error( $adjunto ) ) {
		echo "! {$p->post_title}\n";
		$fallidas++;
		continue;
	}
	set_post_thumbnail( $p->ID, (int) $adjunto );
	update_post_meta( (int) $adjunto, '_asp_origen', 'youtube' );
	echo "+ {$p->post_title}\n";
	$puestas++;
}

echo "\nminiaturas: $puestas · ya tenían o sin video: $saltadas · fallidas: $fallidas\n";
