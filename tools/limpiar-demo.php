<?php
/** Borra todo el contenido creado por seed-demo.php (meta _asp_demo). */
declare(strict_types=1);
require __DIR__ . '/arranque.php';
$ids = get_posts( [ 'post_type' => 'any', 'post_status' => 'any', 'posts_per_page' => -1, 'fields' => 'ids', 'meta_key' => '_asp_demo', 'meta_value' => '1' ] );
$adj = get_posts( [ 'post_type' => 'attachment', 'post_status' => 'any', 'posts_per_page' => -1, 'fields' => 'ids', 'meta_key' => '_asp_demo', 'meta_value' => '1' ] );
foreach ( array_unique( array_merge( $ids, $adj ) ) as $id ) {
	$t = get_the_title( $id );
	'attachment' === get_post_type( $id ) ? wp_delete_attachment( $id, true ) : wp_delete_post( $id, true );
	echo "- borrado #$id $t\n";
}
foreach ( get_posts( [ 'post_type' => 'iniciativa', 'posts_per_page' => -1, 'meta_key' => '_asp_demo_campos', 'meta_value' => '1' ] ) as $p ) {
	foreach ( [ 'iniciativa_bajada', 'iniciativa_publico', 'iniciativa_historia', '_asp_demo_campos' ] as $k ) {
		delete_post_meta( $p->ID, $k );
	}
	echo "- vaciados campos de muestra en «{$p->post_title}»\n";
}
$s = get_term_by( 'name', 'Serie de muestra', 'serie' );
if ( $s ) { wp_delete_term( $s->term_id, 'serie' ); echo "- serie de muestra borrada\n"; }
echo "Listo.\n";
