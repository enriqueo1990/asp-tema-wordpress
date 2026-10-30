<?php
/**
 * Crea la página /suscribite/, el link para compartir en redes con
 * «suscribite acá» (30-10-2026). Plantilla «Suscripción»
 * (templates/page-suscribite.php), sin comentarios y fuera del menú. Se
 * indexa y va al sitemap; los metadatos para compartir salen de inc/seo.php.
 *
 * No pisa nada: si ya hay cualquier cosa con el slug «suscribite» (en
 * cualquier estado, también en la papelera), avisa y no hace nada.
 *
 * Uso: php tools/crear-pagina-suscribite.php
 */

declare(strict_types=1);

require __DIR__ . '/arranque.php';

global $wpdb;

$ocupado = $wpdb->get_results(
	"SELECT ID, post_type, post_status, post_title FROM {$wpdb->posts}
	 WHERE post_name IN ( 'suscribite', 'suscribite__trashed' ) AND post_type <> 'revision'"
);
if ( $ocupado ) {
	fwrite( STDERR, "El slug «suscribite» ya está en uso; no se creó nada:\n" );
	foreach ( $ocupado as $fila ) {
		fwrite( STDERR, "  {$fila->ID} · {$fila->post_type} · {$fila->post_status} · {$fila->post_title}\n" );
	}
	exit( 1 );
}

// Si algún menú tiene «agregar páginas nuevas automáticamente», esta no entra.
remove_action( 'transition_post_status', '_wp_auto_add_pages_to_menu', 10 );

$id = wp_insert_post(
	[
		'post_type'      => 'page',
		'post_status'    => 'publish',
		'post_title'     => 'Suscribite a Ante Su Palabra',
		'post_name'      => 'suscribite',
		'post_content'   => '',
		'comment_status' => 'closed',
		'ping_status'    => 'closed',
		'meta_input'     => [ '_wp_page_template' => 'templates/page-suscribite.php' ],
	],
	true
);
if ( is_wp_error( $id ) ) {
	fwrite( STDERR, $id->get_error_message() . "\n" );
	exit( 1 );
}

echo "Creada: página {$id}.\n" . get_permalink( $id ) . "\n";
