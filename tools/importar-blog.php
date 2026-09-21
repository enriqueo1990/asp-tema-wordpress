<?php
/**
 * Trae los artículos de blog.antesupalabra.com al sitio unificado.
 *
 * Lee el volcado de docs/blog-articulos-2026-09.json, hecho con la API REST
 * del blog viejo (?rest_route=/wp/v2/posts). No hace falta el importador de
 * WordPress ni ningún plugin.
 *
 * Qué trae, verbatim: título, contenido, resumen, fecha original, slug,
 * categoría, autor e imagen destacada. No reescribe ni resume nada.
 *
 * Idempotente por id del blog viejo (meta _asp_blog_id). Volver a correrlo
 * actualiza lo que cambió y no duplica.
 *
 * Uso: php tools/importar-blog.php [ruta/al/json]
 */

declare(strict_types=1);

require __DIR__ . '/arranque.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

$archivo = $argv[1] ?? dirname( __DIR__ ) . '/docs/blog-articulos-2026-09.json';
if ( ! is_readable( $archivo ) ) {
	fwrite( STDERR, "No encuentro el volcado del blog en «{$archivo}».\n" );
	exit( 1 );
}
$articulos = json_decode( (string) file_get_contents( $archivo ), true );
if ( ! is_array( $articulos ) ) {
	fwrite( STDERR, "JSON inválido.\n" );
	exit( 1 );
}

/* Las tres partes son una serie: los títulos vienen numerados en el original.
   Se declara acá, explícito, en vez de adivinarlo con una expresión regular. */
$series = [
	'El pastor frente a los falsos maestros' => [
		'el-pastor-frente-a-los-falsos-maestros-1',
		'el-pastor-frente-a-los-falsos-maestros-2',
		'el-pastor-frente-a-los-falsos-maestros-3',
	],
];

$log = static fn( string $m ) => print( $m . "\n" );

/** Usuario del autor, vinculado a su ficha de persona si existe. */
$asegurar_autor = static function ( array $autor ) use ( $log ): int {
	$usuario = get_user_by( 'login', $autor['slug'] );
	if ( ! $usuario ) {
		/* TODO: el email es un marcador local. Antes de producción hay que
		   poner el real o dejar la cuenta sin email. */
		$uid = wp_insert_user(
			[
				'user_login'   => $autor['slug'],
				'user_pass'    => wp_generate_password( 24 ),
				'user_email'   => $autor['slug'] . '@asp-newsite.local',
				'display_name' => $autor['nombre'],
				'description'  => (string) ( $autor['bio'] ?? '' ),
				'role'         => 'author',
			]
		);
		if ( is_wp_error( $uid ) ) {
			$log( "  ! no pude crear el usuario {$autor['slug']}: " . $uid->get_error_message() );
			return 0;
		}
		$log( "  + usuario «{$autor['nombre']}» (#$uid)" );
	} else {
		$uid = (int) $usuario->ID;
	}

	$persona = get_posts( [ 'post_type' => 'persona', 'title' => $autor['nombre'], 'post_status' => 'any', 'posts_per_page' => 1 ] );
	if ( $persona && (int) get_post_meta( $persona[0]->ID, 'persona_usuario', true ) !== $uid ) {
		update_post_meta( $persona[0]->ID, 'persona_usuario', $uid );
		$log( "  · ficha de «{$autor['nombre']}» vinculada al usuario" );
	}
	return (int) $uid;
};

/** Término de categoría por slug, creándolo si hace falta. */
$asegurar_categoria = static function ( array $cat ) use ( $log ): int {
	$t = get_term_by( 'slug', $cat['slug'], 'category' );
	if ( $t instanceof WP_Term ) {
		return (int) $t->term_id;
	}
	$nuevo = wp_insert_term( $cat['nombre'], 'category', [ 'slug' => $cat['slug'] ] );
	if ( is_wp_error( $nuevo ) ) {
		$log( "  ! categoría {$cat['slug']}: " . $nuevo->get_error_message() );
		return 0;
	}
	$log( "  + categoría «{$cat['nombre']}»" );
	return (int) $nuevo['term_id'];
};

$nuevos = 0;
$actualizados = 0;
foreach ( $articulos as $a ) {
	$blog_id = (int) ( $a['id'] ?? 0 );
	$titulo  = trim( (string) ( $a['titulo'] ?? '' ) );
	if ( ! $blog_id || '' === $titulo ) {
		continue;
	}

	$existentes = get_posts(
		[
			'post_type'      => 'post',
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'meta_key'       => '_asp_blog_id',
			'meta_value'     => (string) $blog_id,
		]
	);

	$datos = [
		'post_type'     => 'post',
		'post_status'   => 'publish',
		'post_title'    => $titulo,
		'post_name'     => (string) $a['slug'],
		'post_content'  => (string) $a['contenido'],
		'post_excerpt'  => trim( wp_strip_all_tags( (string) ( $a['resumen'] ?? '' ) ) ),
		'post_date'     => str_replace( 'T', ' ', (string) $a['fecha'] ),
		'post_date_gmt' => str_replace( 'T', ' ', (string) $a['fecha_gmt'] ),
		'post_author'   => $asegurar_autor( $a['autor'] ),
	];

	if ( $existentes ) {
		$id = (int) $existentes[0]->ID;
		$datos['ID'] = $id;
		wp_update_post( $datos );
		$log( "= «{$titulo}» (#$id)" );
		$actualizados++;
	} else {
		$id = wp_insert_post( $datos, true );
		if ( is_wp_error( $id ) ) {
			$log( "! «{$titulo}»: " . $id->get_error_message() );
			continue;
		}
		$id = (int) $id;
		update_post_meta( $id, '_asp_blog_id', (string) $blog_id );
		update_post_meta( $id, '_asp_blog_url', (string) ( $a['url_vieja'] ?? '' ) );
		$log( "+ «{$titulo}» (#$id)" );
		$nuevos++;
	}

	$cats = array_values( array_filter( array_map( $asegurar_categoria, (array) ( $a['categorias'] ?? [] ) ) ) );
	if ( $cats ) {
		wp_set_post_terms( $id, $cats, 'category' );
	}

	foreach ( $series as $nombre_serie => $slugs ) {
		if ( in_array( (string) $a['slug'], $slugs, true ) ) {
			wp_set_post_terms( $id, [ $nombre_serie ], 'serie' );
			$log( "  · serie «{$nombre_serie}»" );
		}
	}

	if ( ! empty( $a['imagen']['url'] ) && ! has_post_thumbnail( $id ) ) {
		$adjunto = media_sideload_image( (string) $a['imagen']['url'], $id, (string) ( $a['imagen']['titulo'] ?? '' ), 'id' );
		if ( is_wp_error( $adjunto ) ) {
			$log( '  ! imagen: ' . $adjunto->get_error_message() );
		} else {
			set_post_thumbnail( $id, (int) $adjunto );
			if ( ! empty( $a['imagen']['alt'] ) ) {
				update_post_meta( (int) $adjunto, '_wp_attachment_image_alt', (string) $a['imagen']['alt'] );
			}
			$log( '  · imagen destacada' );
		}
	}
}

$log( "\nnuevos: $nuevos · actualizados: $actualizados" );
