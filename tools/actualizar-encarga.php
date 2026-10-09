<?php
/**
 * Conferencia Nacional 2027 en Denton: el save-the-date «De generación en
 * generación» pasa a llamarse «Encarga» y abre la inscripción.
 *
 * Datos del flyer del ministerio y de la página del evento en Entrada27
 * (entrada27.com.ar/e/asp-conf-2027), 9-10-2026. Texto en tuteo neutro,
 * como las piezas para EE.UU.
 *
 * Actualiza la ficha que ya existe (se busca por fecha de inicio y por
 * cualquiera de los dos títulos) por el mismo camino que el formulario del
 * panel, partiendo de lo que ya tiene cargado: la foto, el kit, el programa o
 * lo que haya sumado el área de redes no se pierde. Si la ficha no existe
 * (una base local vieja), la crea. El slug viejo sigue redirigiendo a la
 * ficha (_wp_old_slug del core).
 *
 * Idempotente: el flyer se sube solo si la ficha no tiene uno, y un orador
 * que ya existe se reusa.
 *
 * Uso: php tools/actualizar-encarga.php
 */

declare(strict_types=1);

require __DIR__ . '/arranque.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

$admin = get_users( [ 'role' => 'administrator', 'number' => 1 ] );
if ( ! $admin ) {
	fwrite( STDERR, "No hay un administrador para firmar la carga.\n" );
	exit( 1 );
}
wp_set_current_user( $admin[0]->ID );

const ASP_ENCARGA_INICIO = '20270212';

$titulos_viejos = [ 'De generación en generación', 'Encarga' ];

// Oradores que nombra Entrada27 («y más…»). Joselo Mercado y Greg Travis ya
// están cargados. Bio de Joel Beeke tal como la pasó el usuario, 9-10-2026.
$oradores_datos = [
	'Joel Beeke'     => [
		'foto'    => 'fotos-personas/joel-beeke.jpg',
		'cargo'   => 'Pastor',
		'iglesia' => 'Heritage Netherlands Reformed Congregation',
		'ciudad'  => 'Grand Rapids, Michigan',
		'pais'    => 'Estados Unidos',
		'bio'     => 'Dr. Joel R. Beeke es el presidente y profesor de teología sistemática y homilética en el Puritan Reformed Theological Seminary y un pastor en Heritage Netherlands Reformed Congregation en Grand Rapids, Mich.',
	],
	'Joselo Mercado' => [],
	'Greg Travis'    => [],
];

$taller = get_page_by_path( '1-samuel-denton', OBJECT, 'evento' );
$taller = $taller
	? sprintf( '<a href="%s">Taller de Predicación Expositiva</a>', esc_url( get_permalink( $taller ) ) )
	: 'Taller de Predicación Expositiva';

$descripcion = '<p><strong>Transmitiendo el evangelio a la próxima generación.</strong></p>'
	. '<p>Cada generación de la iglesia de Cristo recibe la misma responsabilidad sagrada: preservar fielmente, proclamar y transmitir el evangelio a quienes vienen detrás de nosotros. Esta conferencia explorará el llamado bíblico de la iglesia local a alcanzar a la próxima generación con el evangelio, hacer discípulos, capacitar a los padres, formar futuros pastores y líderes, y guardar la fe que ha sido entregada de una vez para siempre a los santos.</p>'
	. '<p>Aunque cada generación enfrenta desafíos únicos, la misión de la iglesia permanece inalterable: encomendar el evangelio inmutable a hombres y mujeres fieles que continúen proclamándolo de generación en generación.</p>'
	. '<p><strong>Horario:</strong> viernes 12 de febrero de 15:00 a 21:00 y sábado 13 de febrero de 9:00 a 17:00.</p>'
	. '<p><strong>Entradas para mayores de 18 años:</strong> USD 139 con la cena del viernes y el almuerzo del sábado, o USD 119 sin comidas. Si es tu primera conferencia de Ante Su Palabra, USD 109 con comidas o USD 89 sin comidas. Después del 10 de enero la entrada cuesta USD 149 (USD 119 la primera vez), sin comidas y sin paquete de bienvenida garantizado.</p>'
	. '<p><strong>Niños y adolescentes:</strong> hasta 11 años, gratis sin comidas o USD 45 con comidas; de 12 a 17 años, USD 49 sin comidas o USD 59 con comidas. La conferencia no ofrece cuidado de niños: si vienes con tus hijos, están a tu cargo todo el tiempo.</p>'
	. '<p><strong>Grupos y becas:</strong> los grupos de 10 adultos o más tienen descuento (no se combina con otros descuentos), y hay becas limitadas, con solicitudes hasta el 11 de diciembre de 2026. Para las dos cosas escribe a <a href="mailto:eeuu@antesupalabra.com">eeuu@antesupalabra.com</a>.</p>'
	. '<p>Los dos días anteriores, 10 y 11 de febrero, se hace en Denton el ' . $taller . ' sobre 1 Samuel, con descuento para quienes asistan a los dos.</p>'
	. '<p>La librería y tienda de Ante Su Palabra acepta tarjeta y Zelle, no efectivo.</p>'
	. '<p>Dudas: <a href="mailto:eeuu@antesupalabra.com">eeuu@antesupalabra.com</a></p>';

$nuevos = [
	'evento_fecha_inicio'       => '2027-02-12',
	'evento_fecha_fin'          => '2027-02-13',
	'evento_ciudad'             => 'Denton',
	'evento_pais'               => 'estados-unidos',
	'evento_estado_inscripcion' => 'abierta',
	'evento_url_registro'       => 'https://www.entrada27.com.ar/e/asp-conf-2027',
	'evento_sede_nombre'        => 'Denton Bible Church',
	'evento_sede_direccion'     => '2300 E University Dr, Denton, TX 76209',
	'evento_precio'             => 'USD 139 con comidas · USD 119 sin comidas · descuento la primera vez',
	'evento_descripcion'        => $descripcion,
];

$por_titulo = static function ( string $tipo, string $titulo ): int {
	$p = get_posts( [ 'post_type' => $tipo, 'title' => $titulo, 'post_status' => 'any', 'numberposts' => 1 ] );
	return $p ? $p[0]->ID : 0;
};

// La ficha existente.
$id = 0;
foreach ( $titulos_viejos as $titulo ) {
	foreach ( get_posts( [ 'post_type' => 'evento', 'post_status' => 'any', 'title' => $titulo, 'numberposts' => -1 ] ) as $p ) {
		if ( (string) get_post_meta( $p->ID, 'evento_fecha_inicio', true ) === ASP_ENCARGA_INICIO ) {
			$id = $p->ID;
		}
	}
}

// Oradores: el que ya existe se reusa (por nombre exacto o, si hay uno solo, por apellido).
$oradores = $id ? array_map( 'strval', (array) get_post_meta( $id, 'evento_oradores', false ) ) : [];
foreach ( $oradores_datos as $nombre => $datos ) {
	$foto   = $datos['foto'] ?? '';
	$campos = array_diff_key( $datos, [ 'foto' => 1 ] );
	$pid    = $por_titulo( 'persona', $nombre );
	if ( ! $pid ) {
		$apellido = (string) substr( $nombre, (int) strrpos( $nombre, ' ' ) + 1 );
		$parecidos = get_posts( [ 'post_type' => 'persona', 'post_status' => 'any', 's' => $apellido, 'numberposts' => 2, 'search_columns' => [ 'post_title' ] ] );
		if ( 1 === count( $parecidos ) ) {
			$pid = $parecidos[0]->ID;
			echo "Persona #{$pid} «{$parecidos[0]->post_title}»: se usa para {$nombre}\n";
		}
	}
	if ( ! $pid ) {
		$_POST = [
			'asp_persona_nonce' => wp_create_nonce( 'asp_guardar_persona' ),
			'persona_roles'     => [ 'orador' ],
		];
		$pid   = wp_insert_post( [ 'post_type' => 'persona', 'post_status' => 'publish', 'post_title' => $nombre ], true );
		$_POST = [];
		if ( is_wp_error( $pid ) ) {
			fwrite( STDERR, "Error en {$nombre}: " . $pid->get_error_message() . "\n" );
			continue;
		}
		echo "Persona #{$pid} {$nombre}: creada\n";
	} elseif ( ! in_array( 'orador', (array) get_post_meta( $pid, 'persona_roles', false ), true ) ) {
		add_post_meta( $pid, 'persona_roles', 'orador' );
		echo "Persona #{$pid} {$nombre}: + rol Orador\n";
	}
	// Solo se completan los campos vacíos; a quien ya tiene foto no se le cambia.
	foreach ( $campos as $campo => $valor ) {
		if ( '' === (string) get_post_meta( $pid, 'persona_' . $campo, true ) ) {
			update_post_meta( $pid, 'persona_' . $campo, 'bio' === $campo ? sanitize_textarea_field( $valor ) : sanitize_text_field( $valor ) );
			echo "Persona #{$pid} {$nombre}: {$campo}\n";
		}
	}
	if ( $foto && is_readable( __DIR__ . '/' . $foto ) && ! absint( get_post_meta( $pid, 'persona_foto', true ) ) ) {
		$tmp = wp_tempnam( basename( $foto ) );
		copy( __DIR__ . '/' . $foto, $tmp );
		$adj = media_handle_sideload( [ 'name' => basename( $foto ), 'tmp_name' => $tmp ], $pid, $nombre );
		if ( is_wp_error( $adj ) ) {
			wp_delete_file( $tmp );
			echo "Persona #{$pid} {$nombre}: {$adj->get_error_message()}\n";
		} else {
			update_post_meta( $adj, '_wp_attachment_image_alt', $nombre );
			update_post_meta( $pid, 'persona_foto', $adj );
			echo "Persona #{$pid} {$nombre}: foto #{$adj}\n";
		}
	}
	$oradores[] = (string) $pid;
}

// Lo que la ficha ya tiene, en el formato del formulario, y encima lo nuevo.
$ymd_input = static fn( string $f ): string => 8 === strlen( $f ) ? substr( $f, 0, 4 ) . '-' . substr( $f, 4, 2 ) . '-' . substr( $f, 6, 2 ) : '';
$actual    = [];
if ( $id ) {
	$pais   = wp_get_object_terms( $id, 'pais', [ 'fields' => 'slugs' ] );
	$actual = [
		'evento_pais'        => is_array( $pais ) && $pais ? $pais[0] : '',
		'evento_flyer'       => (string) get_post_meta( $id, 'evento_flyer', true ),
		'evento_foto'        => (string) get_post_meta( $id, 'evento_foto', true ),
		'evento_iniciativa'  => (string) get_post_meta( $id, 'evento_iniciativa', true ),
		'evento_cupo'        => (string) get_post_meta( $id, 'evento_cupo', true ),
		'evento_kit'         => (string) get_post_meta( $id, 'evento_kit', true ),
		'evento_aliados'     => array_map( 'strval', (array) get_post_meta( $id, 'evento_aliados', false ) ),
		'evento_programa'    => (array) ( get_post_meta( $id, 'evento_programa', true ) ?: [] ),
	];
	if ( get_post_meta( $id, 'evento_destacado', true ) ) {
		$actual['evento_destacado'] = '1';
	}
}
if ( empty( $actual['evento_iniciativa'] ) ) {
	$actual['evento_iniciativa'] = (string) $por_titulo( 'iniciativa', 'Conferencia Ante Su Palabra en Estados Unidos' );
}

$_POST = array_merge(
	$actual,
	$nuevos,
	[
		'asp_evento_nonce' => wp_create_nonce( 'asp_guardar_evento' ),
		'evento_oradores'  => array_values( array_unique( $oradores ) ),
	]
);
$datos = [ 'post_type' => 'evento', 'post_status' => 'publish', 'post_title' => 'Encarga', 'post_name' => 'encarga' ];
if ( $id ) {
	$antes = get_the_title( $id );
	$datos = [ 'ID' => $id, 'post_title' => 'Encarga', 'post_name' => 'encarga' ];
	$id    = wp_update_post( $datos, true );
} else {
	$antes = '';
	$id    = wp_insert_post( $datos, true );
}
$_POST = [];
if ( is_wp_error( $id ) ) {
	fwrite( STDERR, 'Error: ' . $id->get_error_message() . "\n" );
	exit( 1 );
}
echo $antes ? "Evento #{$id} «{$antes}» → «Encarga»" : "Evento #{$id} Encarga: creado";
echo ', ' . get_post_status( $id ) . ', ' . get_permalink( $id ) . "\n";

if ( ! absint( get_post_meta( $id, 'evento_flyer', true ) ) ) {
	$tmp = wp_tempnam( 'encarga-2027.jpg' );
	copy( __DIR__ . '/flyers-facebook/encarga-2027.jpg', $tmp );
	$adj = media_handle_sideload( [ 'name' => 'flyer-encarga-2027.jpg', 'tmp_name' => $tmp ], $id, 'Flyer · Encarga' );
	if ( is_wp_error( $adj ) ) {
		wp_delete_file( $tmp );
		echo "Flyer: {$adj->get_error_message()}\n";
	} else {
		update_post_meta( $adj, '_wp_attachment_image_alt', 'Flyer de Encarga, Conferencia Nacional 2027 de Ante Su Palabra: 12 y 13 de febrero de 2027 en Denton, Texas' );
		update_post_meta( $id, 'evento_flyer', $adj );
		echo "Evento #{$id}: flyer #{$adj}\n";
	}
}
