<?php
/**
 * Suma a Enrique Oriolo al Consejo Pastoral (pedido del 29-9-2026). Datos
 * dados por el usuario: Pastor, Iglesia Bíblica de la Gracia, Lanús,
 * Argentina. Sin foto ni bio: se cargan desde el panel cuando estén (la
 * ficha muestra las iniciales mientras tanto).
 *
 * Idempotente: si la persona ya existe, completa los datos y le asegura el
 * rol «consejo» sin tocar los demás roles.
 *
 * Uso: php tools/agregar-consejo-oriolo.php
 */

declare(strict_types=1);

require __DIR__ . '/arranque.php';

$nombre = 'Enrique Oriolo';
$datos  = [
	'persona_cargo'   => 'Pastor',
	'persona_iglesia' => 'Iglesia Bíblica de la Gracia',
	'persona_ciudad'  => 'Lanús',
	'persona_pais'    => 'Argentina',
];

$existente = get_posts( [ 'post_type' => 'persona', 'title' => $nombre, 'post_status' => 'any', 'posts_per_page' => 1 ] );

if ( $existente ) {
	$id = $existente[0]->ID;
	echo "Ya existía: persona {$id}.\n";
} else {
	/* Al final del consejo: el orden sale de «Atributos → Orden». */
	$ultimo = 0;
	foreach ( asp_personas_por_rol( 'consejo' ) as $p ) {
		$ultimo = max( $ultimo, (int) $p->menu_order );
	}
	$id = wp_insert_post(
		[
			'post_type'   => 'persona',
			'post_status' => 'publish',
			'post_title'  => $nombre,
			'menu_order'  => $ultimo + 1,
		],
		true
	);
	if ( is_wp_error( $id ) ) {
		fwrite( STDERR, $id->get_error_message() . "\n" );
		exit( 1 );
	}
	echo "Creada: persona {$id}.\n";
}

foreach ( $datos as $clave => $valor ) {
	update_post_meta( $id, $clave, $valor );
}
$roles = array_map( 'strval', (array) get_post_meta( $id, 'persona_roles', false ) );
if ( ! in_array( 'consejo', $roles, true ) ) {
	add_post_meta( $id, 'persona_roles', 'consejo' );
}

echo get_permalink( $id ) . "\n";
