<?php
/**
 * Borra el contenido creado por seed-demo.php (meta _asp_demo).
 *
 * Con --salvo-eventos deja en pie los eventos de muestra y sus flyers. Sirve
 * mientras el sitio ya tiene artículos y predicaciones reales pero todavía no
 * tiene conferencias cargadas: sin los eventos de muestra no quedaría ninguna
 * ficha completa para mirar.
 *
 * Uso: php tools/limpiar-demo.php [--salvo-eventos]
 */

declare(strict_types=1);

require __DIR__ . '/arranque.php';

$salvar_eventos = in_array( '--salvo-eventos', (array) $argv, true );

$ids = get_posts( [ 'post_type' => 'any', 'post_status' => 'any', 'posts_per_page' => -1, 'fields' => 'ids', 'meta_key' => '_asp_demo', 'meta_value' => '1' ] );
$adj = get_posts( [ 'post_type' => 'attachment', 'post_status' => 'any', 'posts_per_page' => -1, 'fields' => 'ids', 'meta_key' => '_asp_demo', 'meta_value' => '1' ] );

/* Flyers y demás adjuntos colgados de un evento que se conserva. */
$adjuntos_a_salvar = [];
if ( $salvar_eventos ) {
	foreach ( $ids as $id ) {
		if ( 'evento' !== get_post_type( $id ) ) {
			continue;
		}
		$flyer = absint( get_post_meta( $id, 'evento_flyer', true ) );
		if ( $flyer ) {
			$adjuntos_a_salvar[] = $flyer;
		}
		foreach ( get_posts( [ 'post_type' => 'attachment', 'post_parent' => $id, 'posts_per_page' => -1, 'fields' => 'ids', 'post_status' => 'any' ] ) as $hijo ) {
			$adjuntos_a_salvar[] = (int) $hijo;
		}
	}
}

$borrados = 0;
$salvados = 0;
foreach ( array_unique( array_merge( $ids, $adj ) ) as $id ) {
	$tipo = get_post_type( $id );
	if ( $salvar_eventos && ( 'evento' === $tipo || in_array( (int) $id, $adjuntos_a_salvar, true ) ) ) {
		$salvados++;
		continue;
	}
	$t = get_the_title( $id );
	'attachment' === $tipo ? wp_delete_attachment( $id, true ) : wp_delete_post( $id, true );
	echo "- borrado #$id $t\n";
	$borrados++;
}

foreach ( get_posts( [ 'post_type' => 'iniciativa', 'posts_per_page' => -1, 'meta_key' => '_asp_demo_campos', 'meta_value' => '1' ] ) as $p ) {
	foreach ( [ 'iniciativa_bajada', 'iniciativa_publico', 'iniciativa_historia', '_asp_demo_campos' ] as $k ) {
		delete_post_meta( $p->ID, $k );
	}
	echo "- vaciados campos de muestra en «{$p->post_title}»\n";
}

$s = get_term_by( 'name', 'Serie de muestra', 'serie' );
if ( $s && ! $salvar_eventos ) {
	wp_delete_term( $s->term_id, 'serie' );
	echo "- serie de muestra borrada\n";
} elseif ( $s ) {
	/* La serie cuelga de artículos; si estos se borraron, el término queda vacío. */
	$usos = (int) get_term( $s->term_id )->count;
	if ( 0 === $usos ) {
		wp_delete_term( $s->term_id, 'serie' );
		echo "- serie de muestra borrada (quedó vacía)\n";
	}
}

echo "\nborrados: $borrados" . ( $salvar_eventos ? " · conservados por --salvo-eventos: $salvados" : '' ) . "\n";
