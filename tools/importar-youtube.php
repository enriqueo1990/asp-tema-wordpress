<?php
/**
 * Importa videos del canal de YouTube del ministerio como predicaciones.
 *
 * Lee un JSON con [{id, t, d, f, desc}] (id de YouTube, título textual,
 * duración, fecha de publicación Y-m-d, descripción corta). Crea una
 * predicación por video, formato "video", con el título tal cual, sin
 * inventar nada más: el orador solo se vincula si el título nombra a una
 * persona ya cargada, y el evento queda vacío.
 *
 * Idempotente por id de YouTube (meta _asp_youtube_id).
 *
 * Uso: php importar-youtube.php ruta/al/videos.json
 */

declare(strict_types=1);

define( 'WP_USE_THEMES', false );
$_SERVER['HTTP_HOST'] = 'asp-newsite.local';
require '/Users/ibg/Local Sites/asp-newsite/app/public/wp-load.php';

$archivo = $argv[1] ?? '';
if ( ! is_readable( $archivo ) ) {
	fwrite( STDERR, "Falta el JSON de videos.\n" );
	exit( 1 );
}
$videos = json_decode( (string) file_get_contents( $archivo ), true );
if ( ! is_array( $videos ) ) {
	fwrite( STDERR, "JSON inválido.\n" );
	exit( 1 );
}

$personas = get_posts( [ 'post_type' => 'persona', 'post_status' => 'publish', 'posts_per_page' => -1 ] );

/** Convierte "53:36" o "1:02:10" a "53 min" / "1 h 2 min". */
$duracion = static function ( string $d ): string {
	$p = array_map( 'intval', explode( ':', $d ) );
	if ( 3 === count( $p ) ) {
		return sprintf( '%d h %d min', $p[0], $p[1] );
	}
	if ( 2 === count( $p ) ) {
		return sprintf( '%d min', $p[0] );
	}
	return '';
};

$nuevos = 0;
$iguales = 0;
foreach ( $videos as $v ) {
	$yt_id  = preg_replace( '/[^\w-]/', '', (string) ( $v['id'] ?? '' ) );
	$titulo = trim( (string) ( $v['t'] ?? '' ) );
	if ( '' === $yt_id || '' === $titulo ) {
		continue;
	}
	$existe = get_posts( [ 'post_type' => 'predicacion', 'post_status' => 'any', 'posts_per_page' => 1, 'meta_key' => '_asp_youtube_id', 'meta_value' => $yt_id, 'fields' => 'ids' ] );
	if ( $existe ) {
		$iguales++;
		continue;
	}
	$fecha = preg_replace( '/[^0-9]/', '', (string) ( $v['f'] ?? '' ) );
	$id    = wp_insert_post(
		[
			'post_type'    => 'predicacion',
			'post_status'  => 'publish',
			'post_title'   => $titulo,
			'post_content' => '',
			'post_date'    => $fecha && 8 === strlen( $fecha ) ? substr( $fecha, 0, 4 ) . '-' . substr( $fecha, 4, 2 ) . '-' . substr( $fecha, 6, 2 ) . ' 12:00:00' : current_time( 'mysql' ),
		],
		true
	);
	if ( is_wp_error( $id ) ) {
		echo "! {$titulo}: " . $id->get_error_message() . "\n";
		continue;
	}
	update_post_meta( $id, '_asp_youtube_id', $yt_id );
	update_post_meta( $id, '_asp_origen', 'youtube' );
	update_post_meta( $id, 'predicacion_tipo', 'video' );
	update_post_meta( $id, 'predicacion_video_url', 'https://www.youtube.com/watch?v=' . $yt_id );
	if ( $fecha && 8 === strlen( $fecha ) ) {
		update_post_meta( $id, 'predicacion_fecha', $fecha );
	}
	$dur = $duracion( (string) ( $v['d'] ?? '' ) );
	if ( $dur ) {
		update_post_meta( $id, 'predicacion_duracion', $dur );
	}
	$orador = '';
	foreach ( $personas as $p ) {
		if ( false !== mb_stripos( $titulo, $p->post_title ) ) {
			update_post_meta( $id, 'predicacion_orador', $p->ID );
			$orador = $p->post_title;
			break;
		}
	}
	$nuevos++;
	echo "+ #$id {$titulo}" . ( $fecha ? " ($fecha)" : '' ) . ( $orador ? " → orador: $orador" : '' ) . "\n";
}
echo "Listo: $nuevos nuevas, $iguales ya existían.\n";
