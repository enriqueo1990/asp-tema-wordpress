<?php
/**
 * Crea la página /gracias/, destino del botón «Sí, quiero recibirlos» del
 * mail de reconfirmación que se manda desde Kit (29-10-2026). Plantilla de
 * página común, sin comentarios y fuera del menú. El noindex y la salida del
 * sitemap los pone el tema (ASP_PAGINAS_SIN_INDEXAR en inc/seo.php).
 *
 * No pisa nada: si ya hay cualquier cosa con el slug «gracias» (en cualquier
 * estado, también en la papelera), avisa y no hace nada.
 *
 * Uso: php tools/crear-pagina-gracias.php
 */

declare(strict_types=1);

require __DIR__ . '/arranque.php';

global $wpdb;

$ocupado = $wpdb->get_results(
	"SELECT ID, post_type, post_status, post_title FROM {$wpdb->posts}
	 WHERE post_name IN ( 'gracias', 'gracias__trashed' ) AND post_type <> 'revision'"
);
if ( $ocupado ) {
	fwrite( STDERR, "El slug «gracias» ya está en uso; no se creó nada:\n" );
	foreach ( $ocupado as $fila ) {
		fwrite( STDERR, "  {$fila->ID} · {$fila->post_type} · {$fila->post_status} · {$fila->post_title}\n" );
	}
	exit( 1 );
}

$contenido = <<<'HTML'
<p>Gracias por confirmar. Te vamos a avisar de los próximos eventos y novedades de Ante Su Palabra.</p>

<p>Mientras tanto, podés ver predicaciones y recursos en:</p>

<div class="asp-botones">
<a class="asp-btn" href="https://www.youtube.com/channel/UCzBclEQZPuu7qy7rQRpUdaA" target="_blank" rel="noopener">Nuestro canal de YouTube</a>
<a class="asp-btn" href="https://www.instagram.com/antesupalabra/" target="_blank" rel="noopener">Instagram</a>
<a class="asp-btn asp-btn--secundario" href="%s">antesupalabra.com</a>
</div>
HTML;

// Si algún menú tiene «agregar páginas nuevas automáticamente», esta no entra.
remove_action( 'transition_post_status', '_wp_auto_add_pages_to_menu', 10 );

$id = wp_insert_post(
	[
		'post_type'      => 'page',
		'post_status'    => 'publish',
		'post_title'     => '¡Listo, ya estás anotado!',
		'post_name'      => 'gracias',
		'post_content'   => sprintf( $contenido, esc_url( home_url( '/' ) ) ),
		'comment_status' => 'closed',
		'ping_status'    => 'closed',
	],
	true
);
if ( is_wp_error( $id ) ) {
	fwrite( STDERR, $id->get_error_message() . "\n" );
	exit( 1 );
}

echo "Creada: página {$id}.\n" . get_permalink( $id ) . "\n";
