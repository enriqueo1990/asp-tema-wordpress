<?php
/**
 * Carga eventos próximos anunciados por el ministerio, con todo lo que trae
 * su flyer: fechas, sede, iniciativa, oradores, aliados y el flyer mismo.
 *
 * A diferencia de cargar-eventos-historicos.php, el evento entra con su
 * estado de inscripción, link y precio, y los oradores que no existen se
 * crean como personas con rol "Orador", con su foto
 * (tools/fotos-personas/, sin metadatos) y los datos que pasó el ministerio. A una persona
 * que ya existe solo se le completan los campos vacíos.
 *
 * Idempotente: un evento con el mismo título y fecha de inicio no se
 * duplica, una persona con el mismo nombre no se vuelve a crear y a quien
 * ya tiene foto no se le cambia.
 *
 * Uso: php tools/cargar-proximos.php
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

$eventos = [
	// Flyer y texto del posteo del ministerio, 26-9-2026. El posteo no nombra la sede.
	[
		'titulo'     => '1 Samuel',
		'slug'       => '1-samuel',
		'inicio'     => '20261127',
		'fin'        => '20261128',
		'ciudad'     => 'General Roca',
		'pais'       => 'argentina',
		'sede'       => '', // TODO: nombre de la sede, el flyer solo trae la dirección.
		'direccion'  => 'Carlos Pellegrini 150, General Roca, Río Negro',
		'iniciativa' => 'Taller de Predicación Expositiva',
		// Bios tal como las pasó el ministerio.
		'oradores'   => [
			'Jonathan Bertin' => [
				'foto'  => 'fotos-personas/jonathan-bertin.jpg',
				'cargo' => 'Pastor',
				'bio'   => 'Jonathan es pastor y sirve a tiempo completo en el CEP, enseñando predicación expositiva, exégesis del NT y griego. Obtuvo un Master of Arts (MA) focalizado en exégesis del griego del NT en Oak Hill College (Londres) y completó el curso Cornhill de predicación expositiva del Proclamation Trust.',
			],
			'Nicolás Osorio'  => [
				'foto'    => 'fotos-personas/nicolas-osorio.jpg',
				'cargo'   => 'Pastor',
				'iglesia' => 'Iglesia Bautista Renacer',
				'ciudad'  => 'Bogotá',
				'pais'    => 'Colombia',
				'bio'     => 'Nicolás sirve como pastor en la iglesia Bautista Renacer en Bogotá Colombia. Obtuvo un grado de licenciatura en Estudios Bíblicos en Moody Bible Institute y está actualmente cursando un MDiv en The Southern Baptist Seminary. También sirve como Director Asistente de las iniciativas en español para Charles Simeon Trust. Nicolás se unió al equipo en Mayo de 2018 y es responsable de asistir a Jeremy Meeks en la planeación y ejecución de talleres y cursos en América Latina.',
			],
		],
		'aliados'    => [ 'The Charles Simeon Trust' ],
		'estado'     => 'abierta',
		'url'        => 'https://docs.google.com/forms/d/e/1FAIpQLSdT1ikzT7Vi3-IhSp6H3p9u2NpvuK-Mo0MmHFZsjfa16S0sXw/viewform',
		'precio'     => '$120.000 · incluye materiales, alojamiento, comidas y refrigerios',
		'descripcion' => '<p>¿Cómo predicar 1 Samuel? Te invitamos a dos días de trabajo sobre el texto de 1 Samuel: estructura, contexto, argumento, línea melódica y conexión con el evangelio.</p><p>Para pastores y predicadores.</p>',
		'flyer'      => 'flyers-facebook/1-samuel-2026.webp',
	],
	// Flyer del ministerio, 26-9-2026: "Registro próximamente". Sin nombre de sede ni precio.
	[
		'titulo'     => 'Hebreos',
		'slug'       => 'hebreos',
		'inicio'     => '20270708',
		'fin'        => '20270709',
		'ciudad'     => 'Buenos Aires',
		'pais'       => 'argentina',
		'sede'       => '', // TODO: nombre de la sede, el flyer solo trae la dirección.
		'direccion'  => 'Lascano 2659, Villa del Parque, Ciudad Autónoma de Buenos Aires',
		'iniciativa' => 'Taller de Predicación Expositiva',
		// Ya cargados con 1 Samuel: solo se reusan.
		'oradores'   => [
			'Jonathan Bertin' => [],
			'Nicolás Osorio'  => [],
		],
		'aliados'    => [ 'The Charles Simeon Trust' ],
		'estado'     => 'reserva',
		'url'        => '',
		'precio'     => '',
		'descripcion' => '<p>¿Cómo predicar Hebreos? Taller de predicación expositiva para pastores y predicadores.</p>',
		'flyer'      => 'flyers-facebook/hebreos-2027.webp',
	],
	// Página del evento en Entrada27 (entrada27.com.ar/e/simeontrust2027), 30-9-2026.
	// Sin flyer: la única imagen es el logo de Simeon Trust, que ya sale por el aliado.
	// Texto en tuteo neutro, como las piezas para EE.UU.
	[
		'titulo'     => '1 Samuel',
		'slug'       => '1-samuel-denton',
		'inicio'     => '20270210',
		'fin'        => '20270211',
		'ciudad'     => 'Denton',
		'pais'       => 'estados-unidos',
		'sede'       => 'Denton Bible Church',
		'direccion'  => '2300 E University Dr, Denton, TX 76209',
		'iniciativa' => 'Taller de Predicación Expositiva',
		// Bios tal como están en la página del evento. Sin fotos.
		'oradores'   => [
			'Mateo Bixby'      => [
				'iglesia' => 'Iglesia Bautista La Gracia',
				'ciudad'  => 'Juárez, Nuevo León',
				'pais'    => 'México',
				'bio'     => 'Matthew Bixby nació en España, de padres estadounidenses misioneros. Reside en Guadalupe, Nuevo León, México, desde el año 2002. Fue director de la Facultad de Teología de una universidad cristiana hasta el año 2024. Ahora, se enfoca en servir en Iglesia Bautista La Gracia en Juárez, Nuevo León, México, la cual fundó en el año 2012, y en ayudar a pastores y futuros pastores para servir a Dios con lealtad a las Escrituras.',
			],
			'Emmanuel Alfonzo' => [
				'cargo'   => 'Pastor',
				'iglesia' => 'Vida Abundante Bajío',
				'ciudad'  => 'Moroleón, Guanajuato',
				'pais'    => 'México',
				'bio'     => "Emmanuel Alfonzo es pastor en la iglesia Vida Abundante Bajío en Moroleón, Guanajuato, México y también es micro-empresario en el rubro textil.\n\nEmmanuel estudió una Licenciatura en Educación Musical en C.C.D.M.A.C. y diplomados básicos en Teología—así como eclesiología— en el Curso 222, que es un programa extendido de 9Marks.\n\nActualmente está realizando estudios como candidato para una posible ordenación pastoral por parte de la denominación Sovereign Grace (Gracia Soberana en LATAM).",
			],
		],
		'aliados'    => [ 'The Charles Simeon Trust' ],
		'estado'     => 'abierta',
		'url'        => 'https://www.entrada27.com.ar/e/simeontrust2027',
		'precio'     => 'USD 79 · USD 149 con la Conferencia Ante Su Palabra 2027',
		'descripcion' => '<p>Taller de predicación expositiva sobre el libro de 1 Samuel. Dos días para animar y equipar a pastores en las habilidades necesarias para leer, entender y proclamar la Palabra de Dios.</p>'
			. '<p><strong>¿Quién debería asistir?</strong> Principalmente los varones que enseñan y predican con regularidad en su iglesia local, pero también los hermanos bivocacionales y los que se están formando para el ministerio pastoral. Solo mayores de 18 años.</p>'
			. '<p><strong>¿Cómo prepararte?</strong> Lo más importante del taller son los grupos pequeños: cada participante llega listo para presentar dos pasajes, y eso requiere estudio previo. El pasaje y las tareas llegan por correo, así que revisa el tuyo con frecuencia.</p>'
			. '<p><strong>Horario:</strong> miércoles 10 y jueves 11 de febrero, de 8:00 a 17:30.</p>'
			. '<p><strong>Entradas:</strong> USD 79 el taller, con desayuno y almuerzo los dos días. USD 149 el taller más la Conferencia Ante Su Palabra 2027 (viernes 12 y sábado 13 de febrero), con la cena del viernes y el almuerzo del sábado.</p>'
			. '<p><strong>Para confirmar tu lugar completa los dos pasos:</strong> compra tu entrada en Entrada27 y llena el <a href="https://docs.google.com/forms/d/e/1FAIpQLSdD-SadFU6w_f7RW7PzKz14jixnvfkcVnKRd-BsL98rFPcV5g/viewform">formulario de inscripción</a>. La inscripción cierra el 10 de enero.</p>'
			. '<p>Dudas: <a href="mailto:eeuu@antesupalabra.com">eeuu@antesupalabra.com</a></p>',
		'flyer'      => '',
	],
];

$extensiones = [ 'image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp' ];

/**
 * Sube un archivo de tools/ a la biblioteca de medios.
 */
$subir = static function ( string $ruta, int $padre, string $titulo, string $nombre, string $alt ) use ( $extensiones ): int {
	$tmp = wp_tempnam( basename( $ruta ) );
	copy( __DIR__ . '/' . $ruta, $tmp );
	$mime = (string) mime_content_type( $tmp );
	if ( ! isset( $extensiones[ $mime ] ) ) {
		wp_delete_file( $tmp );
		echo "  {$ruta}: tipo inesperado {$mime}\n";
		return 0;
	}
	$adjunto = media_handle_sideload( [ 'name' => $nombre . '.' . $extensiones[ $mime ], 'tmp_name' => $tmp ], $padre, $titulo );
	if ( is_wp_error( $adjunto ) ) {
		wp_delete_file( $tmp );
		echo "  {$ruta}: {$adjunto->get_error_message()}\n";
		return 0;
	}
	update_post_meta( $adjunto, '_wp_attachment_image_alt', $alt );
	return $adjunto;
};

$por_titulo = static function ( string $tipo, string $titulo ): int {
	$p = get_posts( [ 'post_type' => $tipo, 'title' => $titulo, 'post_status' => 'any', 'numberposts' => 1 ] );
	return $p ? $p[0]->ID : 0;
};

foreach ( $eventos as $e ) {
	// Oradores: la persona existente se reusa; la nueva entra con rol "Orador", datos y foto.
	$oradores = [];
	foreach ( $e['oradores'] as $nombre => $datos ) {
		$foto   = $datos['foto'] ?? '';
		$campos = array_diff_key( $datos, [ 'foto' => 1 ] );
		$pid    = $por_titulo( 'persona', $nombre );
		if ( ! $pid ) {
			$_POST = [
				'asp_persona_nonce' => wp_create_nonce( 'asp_guardar_persona' ),
				'persona_roles'     => [ 'orador' ],
			];
			foreach ( $campos as $campo => $valor ) {
				$_POST[ 'persona_' . $campo ] = $valor;
			}
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
		foreach ( $campos as $campo => $valor ) {
			if ( '' === (string) get_post_meta( $pid, 'persona_' . $campo, true ) ) {
				update_post_meta( $pid, 'persona_' . $campo, 'bio' === $campo ? sanitize_textarea_field( $valor ) : sanitize_text_field( $valor ) );
				echo "Persona #{$pid} {$nombre}: {$campo}\n";
			}
		}
		if ( $foto && ! absint( get_post_meta( $pid, 'persona_foto', true ) ) ) {
			$adj = $subir( $foto, $pid, $nombre, sanitize_title( $nombre ), $nombre );
			if ( $adj ) {
				update_post_meta( $pid, 'persona_foto', $adj );
				echo "Persona #{$pid} {$nombre}: foto #{$adj}\n";
			}
		}
		$oradores[] = (string) $pid;
	}

	$aliados = [];
	foreach ( $e['aliados'] as $nombre ) {
		$aid = $por_titulo( 'aliado', $nombre );
		if ( $aid ) {
			$aliados[] = (string) $aid;
		} else {
			echo "Aviso: no existe el aliado «{$nombre}».\n";
		}
	}

	$iniciativa = $por_titulo( 'iniciativa', $e['iniciativa'] );
	if ( ! $iniciativa ) {
		echo "Aviso: no existe la iniciativa «{$e['iniciativa']}».\n";
	}

	$id = 0;
	foreach ( get_posts( [ 'post_type' => 'evento', 'post_status' => 'any', 'title' => $e['titulo'], 'numberposts' => -1 ] ) as $p ) {
		if ( (string) get_post_meta( $p->ID, 'evento_fecha_inicio', true ) === $e['inicio'] ) {
			$id = $p->ID;
		}
	}
	if ( $id ) {
		echo "Evento #{$id} {$e['titulo']}: ya estaba\n";
		continue;
	}

	$ymd = static fn( string $f ): string => substr( $f, 0, 4 ) . '-' . substr( $f, 4, 2 ) . '-' . substr( $f, 6, 2 );

	// Por el mismo camino que el formulario del panel: validación y guardado propios.
	$_POST = [
		'asp_evento_nonce'          => wp_create_nonce( 'asp_guardar_evento' ),
		'evento_fecha_inicio'       => $ymd( $e['inicio'] ),
		'evento_fecha_fin'          => $ymd( $e['fin'] ),
		'evento_ciudad'             => $e['ciudad'],
		'evento_pais'               => $e['pais'],
		'evento_estado_inscripcion' => $e['estado'],
		'evento_url_registro'       => $e['url'],
		'evento_iniciativa'         => (string) $iniciativa,
		'evento_sede_nombre'        => $e['sede'],
		'evento_sede_direccion'     => $e['direccion'],
		'evento_precio'             => $e['precio'],
		'evento_flyer'              => '',
		'evento_oradores'           => $oradores,
		'evento_aliados'            => $aliados,
		'evento_descripcion'        => $e['descripcion'],
	];
	$id    = wp_insert_post( [ 'post_type' => 'evento', 'post_status' => 'publish', 'post_title' => $e['titulo'], 'post_name' => $e['slug'] ], true );
	$_POST = [];
	if ( is_wp_error( $id ) ) {
		fwrite( STDERR, "Error en {$e['titulo']}: " . $id->get_error_message() . "\n" );
		continue;
	}
	echo "Evento #{$id} {$e['titulo']}: creado, " . get_post_status( $id ) . "\n";

	if ( $e['flyer'] ) {
		$adj = $subir( $e['flyer'], $id, sprintf( 'Flyer · %s', $e['titulo'] ), 'flyer-' . $e['slug'] . '-' . substr( $e['inicio'], 0, 4 ), sprintf( 'Flyer de %s', $e['titulo'] ) );
		if ( $adj ) {
			update_post_meta( $id, 'evento_flyer', $adj );
			echo "Evento #{$id} {$e['titulo']}: flyer #{$adj}\n";
		}
	}
}
