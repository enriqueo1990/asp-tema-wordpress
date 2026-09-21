<?php
/**
 * Carga de contenido REAL del relevamiento en el WordPress local, para
 * probar el tema. Idempotente: se puede correr varias veces.
 *
 * Solo datos que están en docs/proyecto.md. Donde falta un dato, queda
 * vacío y la plantilla no lo muestra. Nada inventado.
 *
 * Uso (PHP de Local, con el php.ini del sitio para el socket de MySQL;
 * <sitio> es el id de la carpeta en Local/run/):
 *   LS="$HOME/Library/Application Support/Local"
 *   "$LS/lightning-services/php-8.2.29+0/bin/darwin-arm64/bin/php" \
 *     -c "$LS/run/<sitio>/conf/php/php.ini" tools/seed-local.php
 *
 * La ruta de WordPress se configura con ASP_WP_PATH (ver arranque.php).
 */

declare(strict_types=1);

require __DIR__ . '/arranque.php';

$log = static function ( string $m ): void {
	echo $m, "\n";
};

/** Busca un post por tipo y título; si no existe lo crea. */
$asegurar_post = static function ( string $tipo, string $titulo, array $extra = [] ) use ( $log ): int {
	$existente = get_posts( [ 'post_type' => $tipo, 'title' => $titulo, 'post_status' => 'any', 'posts_per_page' => 1 ] );
	if ( $existente ) {
		$id = (int) $existente[0]->ID;
		if ( $extra ) {
			wp_update_post( array_merge( [ 'ID' => $id ], $extra ) );
		}
		$log( "  = $tipo «{$titulo}» (#$id)" );
		return $id;
	}
	$id = wp_insert_post( array_merge( [ 'post_type' => $tipo, 'post_title' => $titulo, 'post_status' => 'publish' ], $extra ), true );
	if ( is_wp_error( $id ) ) {
		$log( "  ! error en «{$titulo}»: " . $id->get_error_message() );
		return 0;
	}
	$log( "  + $tipo «{$titulo}» (#$id)" );
	return (int) $id;
};

$meta = static function ( int $id, array $campos ): void {
	foreach ( $campos as $clave => $valor ) {
		delete_post_meta( $id, $clave );
		if ( is_array( $valor ) && ! isset( $valor['__serializado'] ) ) {
			foreach ( $valor as $v ) {
				add_post_meta( $id, $clave, $v );
			}
		} elseif ( is_array( $valor ) ) {
			unset( $valor['__serializado'] );
			update_post_meta( $id, $clave, $valor );
		} elseif ( '' !== $valor && null !== $valor && false !== $valor ) {
			update_post_meta( $id, $clave, $valor );
		}
	}
};

$log( '== Opciones del sitio' );
update_option( 'blogname', 'Ante Su Palabra' );
update_option( 'blogdescription', 'Comunión de pastores e iglesias locales' );
update_option( 'timezone_string', 'America/Argentina/Buenos_Aires' );
update_option( 'date_format', 'j \d\e F \d\e Y' );
update_option( 'permalink_structure', '/recursos/%postname%/' );
update_option( 'category_base', 'recursos/categoria' );
update_option( 'tag_base', 'recursos/etiqueta' );

$hola = get_page_by_path( 'hello-world', OBJECT, 'post' );
if ( $hola && 'trash' !== $hola->post_status ) {
	wp_trash_post( $hola->ID );
	$log( '  - entrada de ejemplo a la papelera' );
}
$sample = get_page_by_path( 'sample-page', OBJECT, 'page' );
if ( $sample && 'trash' !== $sample->post_status ) {
	wp_trash_post( $sample->ID );
	$log( '  - página de ejemplo a la papelera' );
}

$log( '== Páginas' );
$inicio   = $asegurar_post( 'page', 'Inicio' );
$recursos = $asegurar_post( 'page', 'Recursos', [ 'post_name' => 'recursos' ] );
$nosotros = $asegurar_post(
	'page',
	'Confiamos en la Palabra de Dios',
	[
		'post_name'    => 'nosotros',
		/* Texto verbatim del sitio actual (proyecto.md 10.3). La cita de Gálatas la imprime la plantilla como caja aparte. */
		'post_content' => '<p>Dios ha hablado. Y no hay otra autoridad mayor a la del Creador del Universo. Buscamos someternos a la autoridad de la Palabra de Dios en cada aspecto de nuestras vidas. Reconociendo que el Evangelio es lo que nos motiva a vivir para Dios.</p>',
	]
);
update_post_meta( $nosotros, '_wp_page_template', 'templates/page-nosotros.php' );
update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', $inicio );
update_option( 'page_for_posts', $recursos );

$log( '== Países' );
$pais_ar = get_term_by( 'slug', 'argentina', 'pais' );
$pais_us = get_term_by( 'slug', 'estados-unidos', 'pais' );
if ( ! $pais_ar || ! $pais_us ) {
	delete_option( 'asp_paises_creados' );
	asp_asegurar_paises();
	$pais_ar = get_term_by( 'slug', 'argentina', 'pais' );
	$pais_us = get_term_by( 'slug', 'estados-unidos', 'pais' );
}
$log( '  argentina #' . $pais_ar->term_id . ' · estados-unidos #' . $pais_us->term_id );

$log( '== Aliados' );
$aliados = [];
foreach ( [ 'Cross Connections', 'TeoLibros', 'The Charles Simeon Trust' ] as $nombre ) {
	$aliados[ $nombre ] = $asegurar_post( 'aliado', $nombre );
}

$log( '== Personas · consejo pastoral (orden del relevamiento)' );
$consejo = [
	[ 'Greg Travis', 'Pastor', 'Iglesia Bíblica Reformada', 'Denton', 'Estados Unidos' ],
	[ 'Dardo Leandi', 'Pastor', 'Iglesia Bautista Misionera de C.A.B.A.', 'Buenos Aires', 'Argentina' ],
	[ 'Ernesto Harris', 'Pastor', 'Iglesia Bautista Misionera de Campana', 'Campana', 'Argentina' ],
	[ 'Cristian Palomares', 'Pastor', 'Iglesia Bíblica Ciudad de Dios', 'Rosario', 'Argentina' ],
	[ 'Ricardo Daglio', 'Pastor', 'Iglesia Bíblica de Villa Regina (UCB)', 'Villa Regina', 'Argentina' ],
	[ 'Joselo Mercado', 'Pastor principal', 'Iglesia Gracia Soberana', 'Gaithersburg, Maryland', 'Estados Unidos' ],
];
foreach ( $consejo as $orden => [ $nombre, $cargo, $iglesia, $ciudad, $pais ] ) {
	$id = $asegurar_post( 'persona', $nombre, [ 'menu_order' => $orden + 1 ] );
	$meta( $id, [ 'persona_cargo' => $cargo, 'persona_iglesia' => $iglesia, 'persona_ciudad' => $ciudad, 'persona_pais' => $pais, 'persona_roles' => [ 'consejo' ] ] );
}
/* Ricardo Daglio y Joselo Mercado también son autores del blog (rol autor). */
foreach ( [ 'Ricardo Daglio', 'Joselo Mercado' ] as $nombre ) {
	$p = get_posts( [ 'post_type' => 'persona', 'title' => $nombre, 'posts_per_page' => 1 ] );
	if ( $p ) {
		delete_post_meta( $p[0]->ID, 'persona_roles' );
		add_post_meta( $p[0]->ID, 'persona_roles', 'consejo' );
		add_post_meta( $p[0]->ID, 'persona_roles', 'autor' );
	}
}
$log( '== Personas · oradores' );
$nigel = $asegurar_post( 'persona', 'Nigel Styles', [ 'menu_order' => 50 ] );
$meta( $nigel, [ 'persona_roles' => [ 'orador' ] ] );

$log( '== Iniciativas (textos actuales del sitio, verbatim; los reescribe el ministerio)' );
$iniciativas = [
	[ 'Conferencia Ante Su Palabra', 'La conferencia Ante Su Palabra nació como una iniciativa para jóvenes pero debido a la demanda fue abierta al público en general. Hoy es nuestra conferencia anual para cientos de personas.' ],
	[ 'Conferencia Ante Su Palabra en Estados Unidos', 'Este 2018, iniciamos nuestra primer conferencia en Estados Unidos para hispanos. Esta conferencia se llevó a cabo en Denton, Texas.' ],
	[ 'Cánticos Espirituales', 'Un evento pensado para líderes de alabanza. Nuestro propósito es alabar a nuestro Dios por su gracia, celebrando juntos el Evangelio, por medio de canciones espirituales y la exposición de las Escrituras.' ],
	[ 'Taller de Predicación Expositiva', 'En Argentina, junto con el ministerio The Charles Simeon Trust buscamos ayudar a cientos de pastores y predicadores a perfeccionarse en la tarea de manejar con precisión la Palabra de Verdad.' ],
];
$inic_ids = [];
foreach ( $iniciativas as $orden => [ $titulo, $texto ] ) {
	$id = $asegurar_post( 'iniciativa', $titulo, [ 'menu_order' => $orden + 1 ] );
	$meta( $id, [ 'iniciativa_descripcion' => '<p>' . $texto . '</p>' ] );
	$inic_ids[ $titulo ] = $id;
}

$log( '== Eventos (solo los del relevamiento con datos suficientes)' );
$ev1 = $asegurar_post( 'evento', 'El cuidado de las almas' );
$meta(
	$ev1,
	[
		'evento_fecha_inicio'       => '20260925',
		'evento_fecha_fin'          => '20260926',
		'evento_ciudad'             => 'Washington DC',
		/* TODO: la URL de Eventbrite 2026 no está en el relevamiento (la del sitio actual es la de 2024). Hasta tenerla, queda en "Reservá la fecha". */
		'evento_estado_inscripcion' => 'reserva',
		'evento_sede_nombre'        => 'Iglesia Gracia Soberana',
		'evento_sede_direccion'     => '8300 Helgerman Ct, Gaithersburg, MD',
		'evento_iniciativa'         => $inic_ids['Conferencia Ante Su Palabra en Estados Unidos'],
		'evento_destacado'          => '1',
	]
);
wp_set_object_terms( $ev1, 'estados-unidos', 'pais' );

$ev2 = $asegurar_post( 'evento', 'Un libro en un día' );
$meta(
	$ev2,
	[
		'evento_fecha_inicio'       => '20260428',
		'evento_fecha_fin'          => '20260428',
		'evento_ciudad'             => 'Buenos Aires',
		'evento_estado_inscripcion' => 'cerrada',
		'evento_url_registro'       => 'https://entrada27.com.ar/unlibroenundia',
		'evento_oradores'           => [ $nigel ],
		'evento_aliados'            => [ $aliados['Cross Connections'], $aliados['TeoLibros'] ],
	]
);
wp_set_object_terms( $ev2, 'argentina', 'pais' );

$contacto = $asegurar_post( 'page', 'Contacto', [ 'post_name' => 'contacto', 'post_content' => '' ] );
update_post_meta( $contacto, '_wp_page_template', 'templates/page-contacto.php' );

$log( '== Usuario de prueba editor_eventos' );
delete_option( 'asp_caps_version' );
asp_registrar_rol_y_caps();
if ( ! get_user_by( 'login', 'redes' ) ) {
	$uid = wp_insert_user( [ 'user_login' => 'redes', 'user_pass' => wp_generate_password( 24 ), 'display_name' => 'Área de redes', 'role' => 'editor_eventos', 'user_email' => 'redes@asp-newsite.local' ] );
	$log( is_wp_error( $uid ) ? '  ! ' . $uid->get_error_message() : "  + usuario redes (#$uid), rol editor_eventos, clave aleatoria: cambiarla desde Usuarios" );
} else {
	$log( '  = usuario redes' );
}

$log( '== Menú principal' );
$menu_id = wp_get_nav_menu_object( 'Principal' );
$menu_id = $menu_id ? (int) $menu_id->term_id : (int) wp_create_nav_menu( 'Principal' );
$items   = wp_get_nav_menu_items( $menu_id ) ?: [];
if ( empty( $items ) ) {
	$defs = [
		[ 'Eventos', 'post_type_archive', 'evento' ],
		[ 'Iniciativas', 'post_type_archive', 'iniciativa' ],
		[ 'Recursos', 'post_type', $recursos ],
		[ 'Nosotros', 'post_type', $nosotros ],
		[ 'Contacto', 'post_type', $contacto ],
	];
	foreach ( $defs as $pos => [ $titulo, $tipo, $obj ] ) {
		$args = [ 'menu-item-title' => $titulo, 'menu-item-status' => 'publish', 'menu-item-position' => $pos + 1, 'menu-item-type' => $tipo ];
		if ( 'post_type_archive' === $tipo ) {
			$args['menu-item-object'] = $obj;
		} else {
			$args['menu-item-object']    = 'page';
			$args['menu-item-object-id'] = $obj;
		}
		wp_update_nav_menu_item( $menu_id, 0, $args );
	}
	$log( '  + 4 ítems' );
}
$locations              = get_theme_mod( 'nav_menu_locations', [] );
$locations['principal'] = $menu_id;
set_theme_mod( 'nav_menu_locations', $locations );

flush_rewrite_rules();
$log( '== Listo. Permalinks vaciados.' );

/* Menú ya creado antes de que existiera Contacto: se agrega si falta. */
$items = wp_get_nav_menu_items( $menu_id ) ?: [];
$hay_contacto = false;
foreach ( $items as $it ) {
	if ( (int) $it->object_id === (int) $contacto ) {
		$hay_contacto = true;
	}
}
if ( ! $hay_contacto ) {
	wp_update_nav_menu_item( $menu_id, 0, [ 'menu-item-title' => 'Contacto', 'menu-item-status' => 'publish', 'menu-item-position' => 5, 'menu-item-type' => 'post_type', 'menu-item-object' => 'page', 'menu-item-object-id' => $contacto ] );
	$log( '  + ítem Contacto en el menú' );
}
