<?php
/**
 * Rol "Editor de eventos" y capacidades de los CPT.
 *
 * Quien carga viene del área de redes: ve Eventos y Artículos, nada más.
 * Administradores y editores ven todo.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

/**
 * Capacidades generadas por un capability_type con map_meta_cap.
 *
 * @param string $singular Ej. evento.
 * @param string $plural   Ej. eventos.
 * @return string[]
 */
function asp_caps_de( string $singular, string $plural ): array {
	return [
		"edit_{$singular}",
		"read_{$singular}",
		"delete_{$singular}",
		"edit_{$plural}",
		"edit_others_{$plural}",
		"publish_{$plural}",
		"read_private_{$plural}",
		"delete_{$plural}",
		"delete_private_{$plural}",
		"delete_published_{$plural}",
		"delete_others_{$plural}",
		"edit_private_{$plural}",
		"edit_published_{$plural}",
	];
}

/**
 * Crea el rol y reparte capacidades. Corre una vez por versión.
 *
 * @return void
 */
function asp_registrar_rol_y_caps(): void {
	if ( get_option( 'asp_caps_version' ) === '2' ) {
		return;
	}

	$todos = array_merge(
		asp_caps_de( 'evento', 'eventos' ),
		asp_caps_de( 'iniciativa', 'iniciativas' ),
		asp_caps_de( 'persona', 'personas' ),
		asp_caps_de( 'aliado', 'aliados' ),
		asp_caps_de( 'predicacion', 'predicaciones' )
	);
	foreach ( [ 'administrator', 'editor' ] as $nombre ) {
		$rol = get_role( $nombre );
		if ( ! $rol ) {
			continue;
		}
		foreach ( $todos as $cap ) {
			$rol->add_cap( $cap );
		}
	}

	$caps_redes = array_fill_keys(
		array_merge(
			asp_caps_de( 'evento', 'eventos' ),
			asp_caps_de( 'predicacion', 'predicaciones' ),
			[
				'read',
				'upload_files',
				'edit_posts',
				'edit_others_posts',
				'edit_published_posts',
				'publish_posts',
				'delete_posts',
				'delete_published_posts',
				'manage_categories',
			]
		),
		true
	);
	remove_role( 'editor_eventos' );
	add_role( 'editor_eventos', __( 'Editor de eventos', 'asp' ), $caps_redes );

	update_option( 'asp_caps_version', '2' );
}
add_action( 'init', 'asp_registrar_rol_y_caps', 30 );
add_action( 'after_switch_theme', static function (): void { delete_option( 'asp_caps_version' ); } );

/**
 * ¿El usuario actual es solo editor de eventos?
 *
 * @return bool
 */
function asp_es_editor_eventos(): bool {
	$user = wp_get_current_user();
	return $user instanceof WP_User && in_array( 'editor_eventos', (array) $user->roles, true ) && ! current_user_can( 'manage_options' );
}
