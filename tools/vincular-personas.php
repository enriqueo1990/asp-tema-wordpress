<?php
/**
 * Vuelve a enlazar lo que quedó suelto cuando una persona se cargó después
 * del contenido (16-10-2026: la ficha de Enrique Oriolo se creó el 29-9 y su
 * predicación y su artículo ya existían).
 *
 * 1. Predicaciones sin orador cuyo título nombra a una persona publicada:
 *    se les carga el orador, con el mismo criterio que importar-youtube.php.
 *    Las que nombran a invitados sin ficha quedan como están: no se inventan
 *    personas (regla 7).
 * 2. Fichas sin usuario de WordPress cuyo nombre coincide con el nombre a
 *    mostrar de un usuario que tiene artículos publicados: se vinculan.
 * 3. A quien quede con predicaciones o artículos se le asegura el rol
 *    «orador» o «autor», sin tocar los demás.
 *
 * Idempotente. Uso: php tools/vincular-personas.php
 */

declare(strict_types=1);

require __DIR__ . '/arranque.php';

$personas = get_posts( [ 'post_type' => 'persona', 'post_status' => 'publish', 'posts_per_page' => -1 ] );

$asegurar_rol = static function ( int $pid, string $rol ): void {
	$roles = array_map( 'strval', (array) get_post_meta( $pid, 'persona_roles', false ) );
	if ( ! in_array( $rol, $roles, true ) ) {
		add_post_meta( $pid, 'persona_roles', $rol );
		echo "  rol «{$rol}» → " . get_the_title( $pid ) . "\n";
	}
};

/* 1. Predicaciones sin orador. */
foreach ( get_posts( [ 'post_type' => 'predicacion', 'post_status' => 'any', 'posts_per_page' => -1 ] ) as $pred ) {
	if ( absint( get_post_meta( $pred->ID, 'predicacion_orador', true ) ) ) {
		continue;
	}
	foreach ( $personas as $p ) {
		if ( false !== mb_stripos( $pred->post_title, $p->post_title ) ) {
			update_post_meta( $pred->ID, 'predicacion_orador', $p->ID );
			echo "+ predicación #{$pred->ID} «{$pred->post_title}» → orador: {$p->post_title}\n";
			break;
		}
	}
}

/* 2. Fichas sin usuario. */
foreach ( $personas as $p ) {
	if ( absint( get_post_meta( $p->ID, 'persona_usuario', true ) ) ) {
		continue;
	}
	foreach ( get_users( [ 'has_published_posts' => [ 'post' ] ] ) as $u ) {
		if ( 0 === strcasecmp( trim( $u->display_name ), trim( $p->post_title ) ) ) {
			update_post_meta( $p->ID, 'persona_usuario', $u->ID );
			echo "+ {$p->post_title} → usuario #{$u->ID} ({$u->user_login})\n";
			break;
		}
	}
}

/* 3. Roles según lo que tiene cada uno. */
foreach ( $personas as $p ) {
	if ( asp_predicaciones_de_persona( $p->ID ) ) {
		$asegurar_rol( $p->ID, 'orador' );
	}
	if ( asp_articulos_de_persona( $p->ID ) ) {
		$asegurar_rol( $p->ID, 'autor' );
	}
}
echo "listo\n";
