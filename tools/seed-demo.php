<?php
/**
 * CONTENIDO DE MUESTRA para ver el diseño con las vistas llenas.
 *
 * Todo lo que crea lleva el meta `_asp_demo = 1` y textos que dicen
 * "muestra": no es contenido del ministerio y se borra entero con
 * tools/limpiar-demo.php. Los flyers se generan acá mismo con GD.
 *
 * Uso: igual que seed-local.php (PHP de Local con el php.ini del sitio).
 */

declare(strict_types=1);

define( 'WP_USE_THEMES', false );
$_SERVER['HTTP_HOST'] = 'asp-newsite.local';
require '/Users/ibg/Local Sites/asp-newsite/app/public/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

$log = static fn( string $m ) => print( $m . "\n" );

$crear = static function ( string $tipo, string $titulo, array $extra = [] ) use ( $log ): int {
	$existe = get_posts( [ 'post_type' => $tipo, 'title' => $titulo, 'post_status' => 'any', 'posts_per_page' => 1 ] );
	if ( $existe ) {
		$log( "  = $tipo «{$titulo}» (#{$existe[0]->ID})" );
		return (int) $existe[0]->ID;
	}
	$id = wp_insert_post( array_merge( [ 'post_type' => $tipo, 'post_title' => $titulo, 'post_status' => 'publish' ], $extra ), true );
	if ( is_wp_error( $id ) ) {
		$log( "  ! $titulo: " . $id->get_error_message() );
		return 0;
	}
	update_post_meta( $id, '_asp_demo', '1' );
	$log( "  + $tipo «{$titulo}» (#$id)" );
	return (int) $id;
};

$meta = static function ( int $id, array $campos ): void {
	foreach ( $campos as $k => $v ) {
		delete_post_meta( $id, $k );
		if ( is_array( $v ) && array_is_list( $v ) && ! isset( $v[0]['titulo'] ) ) {
			foreach ( $v as $x ) {
				add_post_meta( $id, $k, $x );
			}
		} elseif ( '' !== $v && null !== $v ) {
			update_post_meta( $id, $k, $v );
		}
	}
};

/** Imagen generada con GD, subida como adjunto de muestra. */
$imagen = static function ( string $nombre, int $w, int $h, string $texto, array $rgb ) use ( $log ): int {
	$existe = get_posts( [ 'post_type' => 'attachment', 'title' => $nombre, 'post_status' => 'inherit', 'posts_per_page' => 1 ] );
	if ( $existe ) {
		return (int) $existe[0]->ID;
	}
	$im   = imagecreatetruecolor( $w, $h );
	$bg   = imagecolorallocate( $im, $rgb[0], $rgb[1], $rgb[2] );
	$fg   = imagecolorallocate( $im, 250, 248, 244 );
	$fg2  = imagecolorallocate( $im, 200, 195, 185 );
	imagefill( $im, 0, 0, $bg );
	$font = '/System/Library/Fonts/Supplemental/Georgia.ttf';
	imagerectangle( $im, (int) ( $w * 0.06 ), (int) ( $h * 0.06 ), (int) ( $w * 0.94 ), (int) ( $h * 0.94 ), $fg2 );
	$lineas = explode( "\n", $texto );
	$y      = (int) ( $h * 0.42 );
	foreach ( $lineas as $i => $l ) {
		$size = 0 === $i ? (int) ( $w * 0.075 ) : (int) ( $w * 0.038 );
		$bbox = imagettfbbox( $size, 0, $font, $l );
		$x    = (int) ( ( $w - ( $bbox[2] - $bbox[0] ) ) / 2 );
		imagettftext( $im, $size, 0, $x, $y, 0 === $i ? $fg : $fg2, $font, $l );
		$y += (int) ( $size * 1.9 );
	}
	$tmp = wp_tempnam( $nombre . '.png' );
	imagepng( $im, $tmp );
	imagedestroy( $im );
	$att = media_handle_sideload( [ 'name' => sanitize_file_name( $nombre ) . '.png', 'tmp_name' => $tmp ], 0, $nombre );
	if ( is_wp_error( $att ) ) {
		$log( '  ! imagen: ' . $att->get_error_message() );
		return 0;
	}
	update_post_meta( $att, '_asp_demo', '1' );
	$log( "  + imagen «{$nombre}» (#$att, {$w}x{$h})" );
	return (int) $att;
};

$log( '== Imágenes de muestra' );
$flyer45  = $imagen( 'Flyer de muestra 4:5', 1080, 1350, "FLYER DE MUESTRA\nproporción 4:5, como en Instagram\nno se recorta", [ 30, 51, 80 ] );
$flyer11  = $imagen( 'Flyer de muestra cuadrado', 1080, 1080, "FLYER DE MUESTRA\nproporción 1:1\nno se recorta", [ 90, 40, 30 ] );
$retrato  = $imagen( 'Retrato de muestra', 600, 750, "RETRATO\nde muestra", [ 60, 60, 60 ] );
$logoal   = $imagen( 'Logo de aliado de muestra', 600, 240, "ALIADO DE MUESTRA", [ 40, 40, 40 ] );

$log( '== Personas de muestra' );
$or1 = $crear( 'persona', 'Orador de muestra uno', [ 'menu_order' => 60 ] );
$meta( $or1, [ 'persona_roles' => [ 'orador' ], 'persona_cargo' => 'Pastor', 'persona_iglesia' => 'Iglesia de muestra', 'persona_ciudad' => 'Córdoba', 'persona_pais' => 'Argentina', 'persona_foto' => $retrato, 'persona_bio' => 'Bio de muestra. Dos o tres líneas para ver cómo se despliega debajo del nombre en la ficha del evento y en la ficha de la persona.' ] );
$or2 = $crear( 'persona', 'Oradora de muestra dos', [ 'menu_order' => 61 ] );
$meta( $or2, [ 'persona_roles' => [ 'orador' ], 'persona_cargo' => 'Profesora', 'persona_iglesia' => 'Seminario de muestra', 'persona_ciudad' => 'Dallas', 'persona_pais' => 'Estados Unidos', 'persona_bio' => 'Bio de muestra sin foto: la fila tiene que sostenerse igual, con el placeholder rayado.' ] );
$autor = $crear( 'persona', 'Autor de muestra', [ 'menu_order' => 62 ] );
$admin = get_users( [ 'role' => 'administrator', 'number' => 1 ] );
$meta( $autor, [ 'persona_roles' => [ 'autor' ], 'persona_cargo' => 'Pastor', 'persona_iglesia' => 'Iglesia de muestra', 'persona_ciudad' => 'Mendoza', 'persona_pais' => 'Argentina', 'persona_usuario' => $admin ? (int) $admin[0]->ID : 0, 'persona_bio' => 'Bio del autor de muestra, vinculado al usuario administrador para que los artículos de muestra lo muestren como autor.' ] );

$log( '== Aliado de muestra' );
$al = $crear( 'aliado', 'Aliado de muestra' );
$meta( $al, [ 'aliado_logo' => $logoal, 'aliado_url' => 'https://example.com/' ] );

$log( '== Iniciativas: bajadas y textos de muestra (solo donde estén vacíos)' );
$bajadas = [
	'Conferencia Ante Su Palabra'                   => [ 'Bajada de muestra: la conferencia anual del ministerio en Argentina.', 'Pastores, líderes y público en general' ],
	'Conferencia Ante Su Palabra en Estados Unidos' => [ 'Bajada de muestra: la conferencia para hispanos en Estados Unidos.', 'Iglesias hispanas' ],
	'Cánticos Espirituales'                         => [ 'Bajada de muestra: un encuentro para líderes de alabanza.', 'Líderes de alabanza' ],
	'Taller de Predicación Expositiva'              => [ 'Bajada de muestra: talleres de predicación junto a Simeon Trust.', 'Pastores y predicadores' ],
];
$inic = [];
foreach ( $bajadas as $titulo => [ $bajada, $publico ] ) {
	$p = get_posts( [ 'post_type' => 'iniciativa', 'title' => $titulo, 'posts_per_page' => 1 ] );
	if ( ! $p ) {
		continue;
	}
	$inic[ $titulo ] = (int) $p[0]->ID;
	if ( '' === (string) get_post_meta( $p[0]->ID, 'iniciativa_bajada', true ) ) {
		update_post_meta( $p[0]->ID, 'iniciativa_bajada', $bajada );
		update_post_meta( $p[0]->ID, 'iniciativa_publico', $publico );
		update_post_meta( $p[0]->ID, 'iniciativa_historia', '<p>Historia de muestra. Este párrafo existe solo para ver la ficha de la iniciativa con todos sus bloques. Se borra con el resto del contenido de muestra.</p>' );
		update_post_meta( $p[0]->ID, '_asp_demo_campos', '1' );
		$log( "  + bajada y textos en «{$titulo}»" );
	}
}

$log( '== Eventos de muestra' );
$hoy = (int) current_time( 'Y' );
$eventos = [
	[ 'Conferencia de muestra con todo cargado', ( $hoy ) . '1113', ( $hoy ) . '1114', 'Córdoba', 'argentina', 'abierta', 'https://www.eventbrite.com/e/muestra', $flyer45, [ $or1, $or2 ], [ $al ], 'Conferencia Ante Su Palabra', 'Auditorio de muestra', 'Calle de muestra 123, Córdoba', 'Entrada libre con inscripción', true ],
	[ 'Taller de muestra sin cupo', ( $hoy + 1 ) . '0305', ( $hoy + 1 ) . '0306', 'Rosario', 'argentina', 'agotado', 'https://example.com/', $flyer11, [ $or1 ], [], 'Taller de Predicación Expositiva', 'Iglesia de muestra', '', 'USD 20', false ],
	[ 'Encuentro de muestra con inscripción cerrada', ( $hoy + 1 ) . '0522', ( $hoy + 1 ) . '0522', 'Dallas', 'estados-unidos', 'cerrada', 'https://example.com/', 0, [], [], 'Cánticos Espirituales', '', '', '', false ],
	[ 'Conferencia de muestra 2025', '20250926', '20250927', 'Buenos Aires', 'argentina', 'cerrada', '', $flyer45, [ $or1 ], [ $al ], 'Conferencia Ante Su Palabra', 'Iglesia de muestra', '', '', false ],
	[ 'Taller de muestra 2025', '20250314', '20250315', 'Denton', 'estados-unidos', 'cerrada', '', 0, [ $or2 ], [], 'Taller de Predicación Expositiva', '', '', '', false ],
	[ 'Cánticos de muestra 2024', '20241108', '20241108', 'Campana', 'argentina', 'cerrada', '', $flyer11, [], [], 'Cánticos Espirituales', '', '', '', false ],
];
foreach ( $eventos as [ $t, $ini, $fin, $ciudad, $pais, $estado, $url, $flyer, $oradores, $aliados, $iniciativa, $sede, $dir, $precio, $programa ] ) {
	$id = $crear( 'evento', $t );
	if ( ! $id ) {
		continue;
	}
	$campos = [
		'evento_fecha_inicio'       => $ini,
		'evento_fecha_fin'          => $fin,
		'evento_ciudad'             => $ciudad,
		'evento_estado_inscripcion' => $estado,
		'evento_url_registro'       => $url,
		'evento_flyer'              => $flyer ?: '',
		'evento_oradores'           => $oradores,
		'evento_aliados'            => $aliados,
		'evento_iniciativa'         => $inic[ $iniciativa ] ?? '',
		'evento_sede_nombre'        => $sede,
		'evento_sede_direccion'     => $dir,
		'evento_precio'             => $precio,
		'evento_descripcion'        => '<p>Descripción de muestra. Dos párrafos de texto para ver el cuerpo de la ficha con la serif y el ritmo vertical.</p><p>Segundo párrafo de muestra, con un <a href="#">enlace</a> para ver el subrayado.</p>',
	];
	if ( $programa ) {
		$campos['evento_programa'] = [
			[ 'dia' => $ini, 'hora' => '09:30', 'titulo' => 'Sesión de muestra uno', 'orador' => 'Orador de muestra uno' ],
			[ 'dia' => $ini, 'hora' => '11:00', 'titulo' => 'Sesión de muestra dos', 'orador' => 'Oradora de muestra dos' ],
			[ 'dia' => $fin, 'hora' => '09:30', 'titulo' => 'Sesión de muestra tres', 'orador' => '' ],
			[ 'dia' => $fin, 'hora' => '11:00', 'titulo' => 'Panel de cierre de muestra', 'orador' => 'Todos' ],
		];
	}
	$meta( $id, $campos );
	wp_set_object_terms( $id, $pais, 'pais' );
}

$log( '== Artículos de muestra' );
foreach ( [ 'Pastorado', 'Devocional', 'Vida Cristiana' ] as $c ) {
	if ( ! term_exists( $c, 'category' ) ) {
		wp_insert_term( $c, 'category' );
	}
}
$serie = term_exists( 'Serie de muestra', 'serie' ) ?: wp_insert_term( 'Serie de muestra', 'serie' );
$serie_id = (int) ( is_array( $serie ) ? $serie['term_id'] : $serie );
$cuerpo = '<p>Texto de muestra para ver el artículo con la serif de lectura. Este párrafo no dice nada del ministerio: existe para medir el ancho de columna, el interlineado y el ritmo.</p><h2>Subtítulo de muestra</h2><p>Segundo párrafo de muestra con un <a href="#">enlace</a> y una <strong>negrita</strong>.</p><blockquote><p>Cita de muestra dentro del cuerpo.</p></blockquote><p>Tercer párrafo de muestra para cerrar.</p>';
$arts = [
	[ 'Artículo de muestra en serie, primera parte', 'Pastorado', true, '-40 days' ],
	[ 'Artículo de muestra en serie, segunda parte', 'Pastorado', true, '-30 days' ],
	[ 'Artículo de muestra en serie, tercera parte', 'Pastorado', true, '-20 days' ],
	[ 'Artículo de muestra devocional', 'Devocional', false, '-12 days' ],
	[ 'Artículo de muestra sobre vida cristiana', 'Vida Cristiana', false, '-5 days' ],
];
foreach ( $arts as [ $t, $cat, $en_serie, $hace ] ) {
	$id = $crear( 'post', $t, [ 'post_content' => $cuerpo, 'post_date' => gmdate( 'Y-m-d H:i:s', strtotime( $hace ) ), 'post_author' => $admin ? (int) $admin[0]->ID : 1 ] );
	if ( ! $id ) {
		continue;
	}
	wp_set_object_terms( $id, $cat, 'category' );
	if ( $en_serie ) {
		wp_set_object_terms( $id, [ $serie_id ], 'serie' );
	}
}

$log( '== Listo. Para borrar todo: tools/limpiar-demo.php' );

$log( '== Predicaciones de muestra' );
$ev_muestra = get_posts( [ 'post_type' => 'evento', 'title' => 'Conferencia de muestra 2025', 'posts_per_page' => 1 ] );
$ev_libro   = get_posts( [ 'post_type' => 'evento', 'title' => 'Un libro en un día', 'posts_per_page' => 1 ] );
$or_uno     = get_posts( [ 'post_type' => 'persona', 'title' => 'Orador de muestra uno', 'posts_per_page' => 1 ] );
$or_dos     = get_posts( [ 'post_type' => 'persona', 'title' => 'Oradora de muestra dos', 'posts_per_page' => 1 ] );
$cuerpo_pred = '<p>Texto de muestra de una predicación. Existe para ver la lectura larga con la serif: párrafos, un subtítulo y una cita.</p><h2>Primer punto de muestra</h2><p>Segundo párrafo de muestra.</p><blockquote><p>Cita bíblica de muestra.</p></blockquote><p>Tercer párrafo de muestra para cerrar.</p>';
$preds = [
	[ 'Predicación de muestra en video', 'video', 'https://www.youtube.com/watch?v=', '', $ev_muestra, $or_uno, 'Isaías 66:1-2', '48 min', 1 ],
	[ 'Predicación de muestra en audio', 'audio', '', '', $ev_muestra, $or_dos, '2 Timoteo 4:1-5', '41 min', 2 ],
	[ 'Sesión de muestra en texto', 'texto', '', '', $ev_muestra, $or_uno, 'Salmo 119:105', '', 3 ],
	[ 'Predicación de muestra sin evento', 'texto', '', '', [], $or_dos, 'Romanos 12:1-2', '', 0 ],
];
foreach ( $preds as [ $t, $tipo, $video, $audio, $ev, $or, $pasaje, $dur, $orden ] ) {
	$id = $crear( 'predicacion', $t, [ 'post_content' => $cuerpo_pred, 'menu_order' => $orden ] );
	if ( ! $id ) {
		continue;
	}
	$meta(
		$id,
		[
			'predicacion_tipo'      => $tipo,
			/* TODO: no hay links reales de video ni audio en el relevamiento; quedan vacíos y el reproductor no se muestra. */
			'predicacion_video_url' => '',
			'predicacion_audio_url' => '',
			'predicacion_evento'    => $ev ? (int) $ev[0]->ID : '',
			'predicacion_orador'    => $or ? (int) $or[0]->ID : '',
			'predicacion_pasaje'    => $pasaje,
			'predicacion_duracion'  => $dur,
			'predicacion_fecha'     => $ev ? '' : '20240615',
		]
	);
}
