<?php
/**
 * Carga el flyer de cada evento histórico: la imagen original de Eventbrite
 * (sin el recorte 2:1 que muestra Eventbrite), de 9Marcas o Entrada27, o una
 * copia guardada en tools/flyers-facebook/ de lo publicado en Facebook.
 *
 * Las URLs salen de los historiales de los organizadores "Conferencias Ante
 * Su Palabra" (Argentina) y "Ante Su Palabra" (EE. UU.), relevados el
 * 25-9-2026. Salvo El alma del pastor (4:5), son los banners apaisados de
 * Eventbrite: el tema los muestra enteros sobre la banda tonal.
 *
 * Idempotente: un evento que ya tiene flyer no se toca.
 *
 * Uso: php tools/flyers-eventbrite.php   (después de cargar-eventos-historicos.php)
 */

declare(strict_types=1);

require __DIR__ . '/arranque.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

$admin = get_users( [ 'role' => 'administrator', 'number' => 1 ] );
wp_set_current_user( $admin ? $admin[0]->ID : 0 );

// [título del evento, fecha de inicio, imagen original en Eventbrite]
$flyers = [
	[ 'El grito de la Reforma', '20160909', 'https://cdn.evbuc.com/images/22531629/174424328922/1/original.jpg' ],
	[ 'Cánticos Espirituales en City Bell', '20170728', 'https://cdn.evbuc.com/images/31548558/174424328922/1/original.jpg' ],
	[ 'Cánticos Espirituales en Campana', '20170729', 'https://cdn.evbuc.com/images/31548463/174424328922/1/original.jpg' ],
	[ 'La iglesia en lugares difíciles', '20180504', 'https://cdn.evbuc.com/images/42161899/174424328922/1/original.jpg' ],
	[ 'Los Evangelios', '20180709', 'https://cdn.evbuc.com/images/42168210/174424328922/1/original.jpg' ],
	[ 'Los Evangelios', '20180713', 'https://cdn.evbuc.com/images/45438589/174424328922/1/original.jpg' ],
	[ 'Dios es el evangelio', '20190301', 'https://cdn.evbuc.com/images/49289886/174424328922/1/original.jpg' ],
	[ 'Cánticos Espirituales en Campana', '20190304', 'https://cdn.evbuc.com/images/53022780/174424328922/1/original.jpg' ],
	[ 'Salmos de lamento', '20190708', 'https://cdn.evbuc.com/images/61267131/174424328922/1/original.20190426-193525' ],
	[ 'Salmos de lamento', '20190712', 'https://cdn.evbuc.com/images/61267245/174424328922/1/original.20190426-193707' ],
	[ 'Pastoreando iglesias saludables', '20200220', 'https://cdn.evbuc.com/images/86410307/174424328922/1/original.20200106-154940' ],
	[ 'Discipulado eficaz', '20210202', 'https://cdn.evbuc.com/images/124393211/174424328922/1/original.20210128-000525' ],
	[ 'Cambios profundos: cuando disfrutar a Dios me transforma', '20211217', 'https://cdn.evbuc.com/images/184051609/174424328922/1/original.20211111-010155' ],
	[ 'Amós', '20220715', 'https://cdn.evbuc.com/images/218780019/174424328922/1/original.20220126-172255' ],
	[ 'Literatura profética', '20230118', 'https://cdn.evbuc.com/images/409276959/1188570728983/1/original.20221213-182717' ],
	[ 'Nuestra esperanza viva', '20230120', 'https://cdn.evbuc.com/images/368395669/1188570728983/1/original.20221006-151212' ],
	[ 'Éxodo', '20230703', 'https://cdn.evbuc.com/images/487034239/174424328922/1/original.20230406-160132' ],
	[ 'Nos gloriamos en la cruz', '20230826', 'https://cdn.evbuc.com/images/547162689/174424328922/1/original.jpg' ],
	[ 'Pastores aprobados', '20231130', 'https://cdn.evbuc.com/images/604280339/174424328922/1/original.20230922-171458' ],
	[ 'Amós', '20240117', 'https://cdn.evbuc.com/images/608815059/1188570728983/1/original.20230928-193945' ],
	[ 'La suficiencia de las Escrituras', '20240119', 'https://cdn.evbuc.com/images/608796209/1188570728983/1/original.20230928-191746' ],
	[ 'Conferencia regional en Rahway', '20240511', 'https://cdn.evbuc.com/images/725563059/1188570728983/1/original.20240322-001019' ],
	[ 'Efesios', '20250226', 'https://cdn.evbuc.com/images/893953843/1188570728983/1/original.20241107-144346' ],
	[ 'Arrepentíos y creed', '20250228', 'https://cdn.evbuc.com/images/852838739/1188570728983/1/original.20240917-222000' ],
	[ 'El alma del pastor', '20251107', 'https://cdn.evbuc.com/images/1097570123/174424328922/1/original.20250816-211953' ],
	[ 'Conferencia regional en Houston', '20260515', 'https://cdn.evbuc.com/images/1179645054/1188570728983/1/original.20260311-233123' ],
	// Fuera de Eventbrite, también públicas: 9Marcas y Entrada27.
	[ 'No me avergüenzo del evangelio', '20190222', 'https://9marcas.org/wp-content/uploads/2019/01/Registration-Page.png' ],
	[ 'Por el poder del Espíritu Santo', '20260911', 'https://res.cloudinary.com/daxlbrqec/image/upload/v1782320703/events-uploads/gudpgv3tq4wgdqsf4ahz.jpg' ],
	// De la página de Facebook del ministerio (portadas de eventos y del
	// álbum "Fotos de portada"): no tienen URL pública estable, así que viajan
	// con el repo en tools/flyers-facebook/.
	[ 'Conferencia Ante Su Palabra 2015', '20150710', 'flyers-facebook/asp-2015.jpg' ],
	[ 'Conferencia 9Marks en Buenos Aires', '', 'flyers-facebook/9marks-2015.jpg' ],
	[ 'Reforma: el Espíritu y la Palabra', '20171026', 'flyers-facebook/reforma-2017-cordoba.jpg' ],
	[ 'Reforma: el Espíritu y la Palabra', '20171027', 'flyers-facebook/reforma-2017-buenos-aires.jpg' ],
	[ 'Reforma: el Espíritu y la Palabra', '20171030', 'flyers-facebook/reforma-2017-villa-regina.jpg' ],
	[ 'La comunión de los santos', '20220218', 'flyers-facebook/la-comunion-de-los-santos-2022.jpg' ],
	[ 'El cuidado de las almas', '20260925', 'flyers-facebook/el-cuidado-de-las-almas-2026.jpg' ],
	// Un mismo flyer anuncia las dos sedes del taller 2025.
	[ 'Eclesiastés', '20250704', 'flyers-facebook/eclesiastes-2025.jpg' ],
	[ 'Eclesiastés', '20250708', 'flyers-facebook/eclesiastes-2025.jpg' ],
	// Del Instagram del ministerio.
	[ 'Eclesiastés', '20260226', 'flyers-facebook/eclesiastes-denton-2026.jpg' ],
];

$extensiones = [ 'image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif' ];

foreach ( $flyers as [ $titulo, $inicio, $url ] ) {
	$evento = 0;
	foreach ( get_posts( [ 'post_type' => 'evento', 'post_status' => 'any', 'title' => $titulo, 'numberposts' => -1 ] ) as $p ) {
		if ( (string) get_post_meta( $p->ID, 'evento_fecha_inicio', true ) === $inicio ) {
			$evento = $p->ID;
		}
	}
	if ( ! $evento ) {
		echo "No existe «{$titulo}» ({$inicio}): corré antes cargar-eventos-historicos.php\n";
		continue;
	}
	if ( absint( get_post_meta( $evento, 'evento_flyer', true ) ) ) {
		echo "#{$evento} {$titulo}: ya tiene flyer\n";
		continue;
	}

	if ( str_starts_with( $url, 'http' ) ) {
		$tmp = download_url( $url, 60 );
	} else {
		$tmp = wp_tempnam( basename( $url ) );
		copy( __DIR__ . '/' . $url, $tmp );
	}
	if ( is_wp_error( $tmp ) ) {
		echo "#{$evento} {$titulo}: no se pudo bajar ({$tmp->get_error_message()})\n";
		continue;
	}
	$mime = (string) mime_content_type( $tmp );
	if ( ! isset( $extensiones[ $mime ] ) ) {
		wp_delete_file( $tmp );
		echo "#{$evento} {$titulo}: tipo inesperado {$mime}\n";
		continue;
	}
	$archivo = [
		'name'     => 'flyer-' . get_post_field( 'post_name', $evento ) . '-' . substr( $inicio, 0, 4 ) . '.' . $extensiones[ $mime ],
		'tmp_name' => $tmp,
	];
	$adjunto = media_handle_sideload( $archivo, $evento, sprintf( 'Flyer · %s', $titulo ) );
	if ( is_wp_error( $adjunto ) ) {
		wp_delete_file( $tmp );
		echo "#{$evento} {$titulo}: {$adjunto->get_error_message()}\n";
		continue;
	}
	update_post_meta( $adjunto, '_wp_attachment_image_alt', sprintf( 'Flyer de %s', $titulo ) );
	update_post_meta( $evento, 'evento_flyer', $adjunto );
	echo "#{$evento} {$titulo}: flyer #{$adjunto}\n";
}
