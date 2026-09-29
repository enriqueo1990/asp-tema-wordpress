<?php
/**
 * Crea la página «Inicio nuevo» con la plantilla templates/page-inicio.php,
 * publicada, para ver el rediseño en /inicio-nuevo/ sin tocar la portada.
 * Con --portada, además la deja como página de inicio (Ajustes → Lectura).
 * Para volver atrás: elegir de nuevo la página «Inicio» ahí (ver
 * inc/inicio.php).
 *
 * Idempotente: si la página ya existe, solo se asegura la plantilla.
 *
 * Uso: php tools/crear-inicio-nuevo.php [--portada]
 */

declare(strict_types=1);

require __DIR__ . '/arranque.php';

$plantilla = 'templates/page-inicio.php';
$existente = get_page_by_path( 'inicio-nuevo', OBJECT, 'page' );

if ( $existente ) {
	$id = $existente->ID;
	update_post_meta( $id, '_wp_page_template', $plantilla );
	echo "Ya existía: página {$id}, plantilla asegurada.\n";
} else {
	$id = wp_insert_post(
		[
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_title'   => 'Inicio nuevo',
			'post_name'    => 'inicio-nuevo',
			'post_content' => '',
			'meta_input'   => [ '_wp_page_template' => $plantilla ],
		],
		true
	);
	if ( is_wp_error( $id ) ) {
		fwrite( STDERR, $id->get_error_message() . "\n" );
		exit( 1 );
	}
	echo "Creada: página {$id}.\n";
}

if ( in_array( '--portada', $argv, true ) ) {
	$anterior = (int) get_option( 'page_on_front' );
	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', $id );
	echo "Portada: página {$id} (antes: {$anterior}; para volver atrás, elegir esa en Ajustes → Lectura).\n";
}

echo get_permalink( $id ) . "\n";
