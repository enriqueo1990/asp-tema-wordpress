<?php
/**
 * Carga los eventos realizados de Ante Su Palabra (2015–2026) y vincula cada
 * predicación importada de YouTube con su evento.
 *
 * Datos relevados el 24-9-2026 de los historiales de Eventbrite de
 * "Conferencias Ante Su Palabra" (Argentina) y "Ante Su Palabra" (EE. UU.),
 * de las listas de reproducción y descripciones del canal de YouTube, del
 * sitio viejo y de lo que pasó el ministerio. Nada se completó a ojo.
 *
 * Título: el tema del evento en minúscula inicial ("El alma del pastor"),
 * sin año ni ciudad, que la ficha ya muestra. Sin tema conocido: "tipo en
 * ciudad". A un evento que le falta un dato obligatorio (fechas, ciudad) se lo
 * carga como borrador con lo que se sabe.
 *
 * Vínculos: por el id del video de YouTube, en el orden de la lista de
 * reproducción (que es el orden de las sesiones). Si el evento está
 * publicado, la predicación pierde su fecha propia, que era el día de subida
 * a YouTube, y toma la del evento.
 *
 * Idempotente: un evento con el mismo título y fecha de inicio no se duplica.
 *
 * Uso: php tools/cargar-eventos-historicos.php
 */

declare(strict_types=1);

require __DIR__ . '/arranque.php';

$admin = get_users( [ 'role' => 'administrator', 'number' => 1 ] );
if ( ! $admin ) {
	fwrite( STDERR, "No hay un administrador para firmar la carga.\n" );
	exit( 1 );
}
wp_set_current_user( $admin[0]->ID );

const AR = 'argentina';
const US = 'estados-unidos';

// Iniciativas por nombre: los ids cambian de un sitio a otro.
$iniciativas = [];
foreach ( [
	'asp' => 'Conferencia Ante Su Palabra',
	'usa' => 'Conferencia Ante Su Palabra en Estados Unidos',
	'can' => 'Cánticos Espirituales',
	'tal' => 'Taller de Predicación Expositiva',
] as $clave => $nombre ) {
	$p = get_posts( [ 'post_type' => 'iniciativa', 'title' => $nombre, 'post_status' => 'publish', 'numberposts' => 1 ] );
	$iniciativas[ $clave ] = $p ? $p[0]->ID : 0;
	if ( ! $p ) {
		fwrite( STDERR, "Aviso: no existe la iniciativa «{$nombre}»; sus eventos quedan sin iniciativa.\n" );
	}
}

// clave => [título, slug, inicio, fin, ciudad, país, sede, dirección, iniciativa]
$eventos = [
	'asp2015'  => [ 'Conferencia Ante Su Palabra 2015', '', '20150710', '20150712', 'Pilar', AR, '', '', 'asp' ],
	'9marks'   => [ 'Conferencia 9Marks en Buenos Aires', '', '', '', 'Buenos Aires', AR, '', '', 'asp' ], // TODO: días (octubre de 2015).
	'patag'    => [ 'Regresando a la Biblia: la Palabra de Dios', '', '', '', 'Villa Regina', AR, '', '', 'asp' ], // TODO: días (julio de 2016).
	'asp2016'  => [ 'El grito de la Reforma', '', '20160909', '20160910', 'Buenos Aires', AR, '', 'Salcedo 4038, Boedo, Ciudad Autónoma de Buenos Aires', 'asp' ],
	'cant17cb' => [ 'Cánticos Espirituales en City Bell', '', '20170728', '20170728', 'City Bell', AR, 'Iglesia Bíblica de City Bell', 'Camino General Belgrano 311, City Bell, Buenos Aires', 'can' ],
	'cant17ca' => [ 'Cánticos Espirituales en Campana', 'canticos-espirituales-en-campana-2017', '20170729', '20170729', 'Campana', AR, 'Templo Unión Evangélica', 'Av. Varela 447, Campana, Buenos Aires', 'can' ],
	// Reforma 2017 fue una gira: Córdoba, Buenos Aires y Villa Regina (eventos de Facebook).
	'ref17co'  => [ 'Reforma: el Espíritu y la Palabra', 'reforma-el-espiritu-y-la-palabra-cordoba', '20171026', '20171027', 'Córdoba', AR, '', '', 'asp' ],
	'asp2017'  => [ 'Reforma: el Espíritu y la Palabra', 'reforma-el-espiritu-y-la-palabra-buenos-aires', '20171027', '20171028', 'Buenos Aires', AR, '', '', 'asp' ],
	'ref17vr'  => [ 'Reforma: el Espíritu y la Palabra', 'reforma-el-espiritu-y-la-palabra-villa-regina', '20171030', '20171031', 'Villa Regina', AR, '', 'Lisandro de la Torre, Villa Regina, Río Negro', 'asp' ],
	'mez'      => [ 'La iglesia en lugares difíciles', '', '20180504', '20180505', 'Buenos Aires', AR, '', 'Av. Gaona 3581, Villa Santa Rita, Ciudad Autónoma de Buenos Aires', 'asp' ],
	'usa2018'  => [ 'Edificando iglesias saludables', '', '', '', 'Denton', US, '', '', 'usa' ], // TODO: días (junio de 2018).
	'tal18ba'  => [ 'Los Evangelios', 'los-evangelios-buenos-aires', '20180709', '20180710', 'Buenos Aires', AR, 'Iglesia Redil Abierto', 'Av. Gaona 3581, Villa Santa Rita, Ciudad Autónoma de Buenos Aires', 'tal' ],
	'tal18bb'  => [ 'Los Evangelios', 'los-evangelios-bahia-blanca', '20180713', '20180714', 'Bahía Blanca', AR, 'Iglesia Cristiana de la Gracia', 'Donado 667, Bahía Blanca, Buenos Aires', 'tal' ],
	'asp2019'  => [ 'Dios es el evangelio', '', '20190301', '20190302', 'Pilar', AR, 'Parque Nazareno', 'Av. Sgto. Cayetano Beliera 1635, Pilar, Buenos Aires', 'asp' ],
	'cant19'   => [ 'Cánticos Espirituales en Campana', 'canticos-espirituales-en-campana-2019', '20190304', '20190304', 'Campana', AR, 'IBM Campana', 'Urquiza 451, Campana, Buenos Aires', 'can' ],
	// Fechas y sede: página de la conferencia en 9marcas.org.
	'usa2019'  => [ 'No me avergüenzo del evangelio', '', '20190222', '20190223', 'Denton', US, 'Denton Bible Church', '2300 East University Drive, Denton, TX 76209', 'usa' ],
	'tal19ba'  => [ 'Salmos de lamento', 'salmos-de-lamento-pilar', '20190708', '20190709', 'Pilar', AR, 'Parque Nazareno de Pilar', 'Av. Sgto. Cayetano Beliera 1635, Pilar, Buenos Aires', 'tal' ],
	'tal19co'  => [ 'Salmos de lamento', 'salmos-de-lamento-cordoba', '20190712', '20190713', 'Córdoba', AR, 'Iglesia Bíblica Bautista Crecer', 'Lima 849, Córdoba', 'tal' ],
	'dever'    => [ 'Pastoreando iglesias saludables', '', '20200220', '20200220', 'Presidente Derqui', AR, 'Centro de Desarrollo Cristiano (El Nazareno)', 'Av. Juan Domingo Perón 3251, Presidente Derqui, Buenos Aires', 'asp' ],
	'asp2020'  => [ 'Conferencia Ante Su Palabra 2020', '', '', '', '', AR, '', '', 'asp' ], // TODO: fechas y ciudad.
	// En línea, transmitida desde Denton.
	'disc2021' => [ 'Discipulado eficaz', '', '20210202', '20210205', 'Denton', US, '', '', 'asp' ],
	'cambios'  => [ 'Cambios profundos: cuando disfrutar a Dios me transforma', '', '20211217', '20211218', 'Rosario', AR, '', 'Rodríguez 542, Rosario, Santa Fe', 'asp' ],
	'comun22'  => [ 'La comunión de los santos', '', '20220218', '20220219', 'Denton', US, '', '', 'usa' ],
	'tal22'    => [ 'Amós', 'amos-2022', '20220715', '20220716', 'Presidente Derqui', AR, 'Centro de Desarrollo Cristiano (El Nazareno)', 'Av. Juan Domingo Perón 3251, Presidente Derqui, Buenos Aires', 'tal' ],
	'tal23us'  => [ 'Literatura profética', '', '20230118', '20230119', 'Argyle', US, '', '600 FM 407, Argyle, TX 76226', 'tal' ],
	'usa2023'  => [ 'Nuestra esperanza viva', '', '20230120', '20230121', 'Denton', US, 'Denton Bible Church', '2300 East University Drive, Denton, TX 76209', 'usa' ],
	'tal23ar'  => [ 'Éxodo', '', '20230703', '20230705', 'Pilar', AR, 'Parque Nazareno de Pilar', 'Av. Sgto. Cayetano Beliera 1635, Pilar, Buenos Aires', 'tal' ],
	'cant23'   => [ 'Nos gloriamos en la cruz', '', '20230826', '20230826', 'Campana', AR, 'IBM Campana', 'Urquiza 451, Campana, Buenos Aires', 'can' ],
	'retiro23' => [ 'Pastores aprobados', '', '20231130', '20231202', 'Pilar', AR, 'Parque Nazareno de Pilar', 'Av. Sgto. Cayetano Beliera 1635, Pilar, Buenos Aires', 'asp' ],
	'tal24us'  => [ 'Amós', 'amos-2024', '20240117', '20240118', 'Denton', US, 'Denton Bible Church', '2300 East University Drive, Denton, TX 76209', 'tal' ],
	'suf2024'  => [ 'La suficiencia de las Escrituras', '', '20240119', '20240120', 'Denton', US, 'Denton Bible Church', '2300 East University Drive, Denton, TX 76209', 'usa' ],
	'reg24'    => [ 'Conferencia regional en Rahway', '', '20240511', '20240511', 'Rahway', US, '', '177 Elm Avenue, Rahway, NJ 07065', 'usa' ],
	'asp2024'  => [ 'La iglesia', '', '20241101', '20241102', 'Pilar', AR, 'Parque Nazareno de Pilar', 'Av. Sgto. Cayetano Beliera 1635, Pilar, Buenos Aires', 'asp' ],
	'tal25us'  => [ 'Efesios', '', '20250226', '20250227', 'Denton', US, '', '2300 East University Drive, Denton, TX 76209', 'tal' ],
	'arrep25'  => [ 'Arrepentíos y creed', '', '20250228', '20250228', 'Fort Worth', US, '', '4616 Stanley Avenue, Fort Worth, TX 76115', 'usa' ],
	'tal25co'  => [ 'Eclesiastés', 'eclesiastes-cordoba', '20250704', '20250705', 'Córdoba', AR, '', '', 'tal' ],
	'tal25ar'  => [ 'Eclesiastés', 'eclesiastes-buenos-aires', '20250708', '20250709', 'Buenos Aires', AR, '', '', 'tal' ],
	'tal25nq'  => [ '2 Timoteo', '', '20251128', '20251129', 'Neuquén', AR, '', 'Mendoza 44, Neuquén', 'tal' ],
	'asp2025'  => [ 'El alma del pastor', '', '20251107', '20251108', 'Lanús', AR, 'Iglesia Bíblica de la Gracia', 'Eva Perón 122, Lanús Oeste, Provincia de Buenos Aires', 'asp' ],
	'tal26us'  => [ 'Eclesiastés', 'eclesiastes-denton', '20260226', '20260227', 'Denton', US, '', '2300 East University Drive, Denton, TX 76209', 'tal' ],
	'tal26ar'  => [ 'Evangelio de Juan', '', '20260707', '20260708', 'Buenos Aires', AR, '', 'Lascano 2659, Villa del Parque, Ciudad Autónoma de Buenos Aires', 'tal' ],
	'cant26'   => [ 'Serviremos al Señor', '', '20260910', '20260910', 'Campana', AR, 'Iglesia Bautista Misionera en Campana', 'Urquiza 451, Campana, Buenos Aires', 'can' ],
	'reg26'    => [ 'Conferencia regional en Houston', '', '20260515', '20260516', 'Rosenberg', US, '', '6701 FM 762 Road, Rosenberg, TX 77469', 'usa' ],
	'poder26'  => [ 'Por el poder del Espíritu Santo', '', '20260911', '20260912', 'Buenos Aires', AR, 'Iglesia Bautista Misionera de C.A.B.A.', 'Auditorio · Lascano 2659, C1417, Ciudad Autónoma de Buenos Aires', 'asp' ],
];

// Videos de YouTube de cada evento, en el orden de su lista de reproducción.
$videos = [
	'asp2015'  => [ 'fteWTutQq24', 'GuLauTojbkc', 'LjlcVOrnJZw', '_TKOXlo6xDc', 'e9OXiNsJvbw', 'y4AgcBWk8Dw', 'hLtKepeiH0Q', 'mY7Stwe4ijo', 'vBYLuRcZghs', 'p-731d69MOA', 'SCK1S_Rxppc', 'bG-a0NhoC_I' ],
	'9marks'   => [ '3egXZYy-lS0', '9JsXDitpsQc', 'IWVM50IceFQ', 'V35yhpGNGcE', 'n-71oxulesw', 'iLbW4tTSD9M', '5dvV5DNjdF4', 'sv8YgC-09Vs', 'UAUGn61UUoI' ],
	'patag'    => [ 'JgZwOimMcq4' ],
	'asp2017'  => [ 'xrrP5fjK2Ko', 'oUkJEiIGBS0', 'QO2Uk8Au7do', 'TnOjIIpcu30', 'fPO8G8bnBBo', 'dC02iVfX9yU', 'eya2K9JIfWQ', 'uMO2g8bCjeU' ],
	'usa2018'  => [ 'WBF5Bk_X-XY', 'ea77LzhUBUY', '9LvXc2lO2ak', '6GcY4Uu-2CA', 'Tx6rLpH4x8s', 'mjKxPGTx_gw', '4HiIC8Zhbx4', '-L0MR1Z7JrY', 'ji-ny8dfu1w', 'ZhkRWWeRvCI', 'Zb368_TgBKw' ],
	// Fuera de toda lista: los que llevan "Ante Su Palabra 2019" en el título, subidos en mayo de 2019.
	'asp2019'  => [ 'G9lAe8Csn5g', 'MyMgNRMT3V8' ],
	'usa2019'  => [ '5FxV2kIX0Yo', 'h4N14w7Mfx0', 'elqgkQAXK5I', 'ERR2AwloWvU', 'vARWfoshxRo', '6PT6HxSOdI0', '8A3cZ0Ji-yY', '9G4tpQFX9Xg', '67vXRmlTjrA', '-dIRWGTfp28', '3VBivBFXyEo', 'VnmAmDjMDAQ', 's62fcLU0cEI', 'hf1GpQPZ2AQ', 'ZRijBXEc0-E', 'PSzYmGu7rJw', 'm72nCX3e_SE', '4_qIWYmvpuM', '496yWN57u8w', 'rpPi7tJh6t8', '2VgjU0YtMXA', 'umpQiFk8Sbg', '3AQanqSygs8', 'KzsG1UVjsJw', '3DfacTryWZs' ],
	'asp2020'  => [ 'sYj1XZXbo3w', 'FYgsvAptyaA', 'i-Arc5iWTGM', 'CEbAX1IcogQ', 'krs_uAkvI_0', 'Gy9kivSBa6E', '7qdXZ-khCe4' ],
	'disc2021' => [ 'DYBCgBZvofM', '3Up9ekbh1FE', 'Q8tueVi55nI', 'xccZRC7TBhk', '9KfW1tYF_ro', 'qTiAhOsBHso', 'oqhKiIy5xMg', 'T5rMzYm6UzU', 'ukHYFQx76uo', 'UPX4Ij2aSp4', 'cHZ6Rtta5aw', 'mtMRdBbYoVw' ],
	'cambios'  => [ 'CLppRRrJksk', 'oz8uzJBrHXc', 'rz066bA5HUQ', 'gjm_VbUXhKk', 'k-LBx6AdsI4' ],
	'usa2023'  => [ 'hDWQKULhdaE', '1ACMQimLejM', 'naRnWCjhqOs', 'HBsZ9nnPTtg', 'jYhRsus0XaQ', 'BNUYkIjdFmw', 'eBnR9S6nMwk' ],
	'suf2024'  => [ '3wvuGfTXyfA', '1oed9WpavqY', 'Pd_LBNewySE', '65BvyUmEfvI', 'cJzHBhY_mgI', 'NkmHS9kmsbA', 'ho4i81PPExc' ],
	'asp2024'  => [ 'pPXomTLoAfo', 'hj492FoYFMs', 'XG45ZVKEz2M', '7KQRGSsEpZw', 'tKevnvp4Dc8', 'tfUoRb-GwCc' ],
	'tal25ar'  => [ 'IDFT4dRZczg' ],
	'asp2025'  => [ 'zIiVXkfLaG0', 'WNWrlJMysXk', 'NkYh7GlGMb4', 'jtzMOH_z1GE', 'gi_BhtHHR4M', 'UgPcWy9bhdg', 'b6T4zTBeX7A', 'kQNBDF2pYH8', 'w6PDalUKuf0' ],
];

// Predicación por id de video.
$por_video = [];
foreach ( get_posts( [ 'post_type' => 'predicacion', 'post_status' => 'any', 'numberposts' => -1 ] ) as $p ) {
	$vid = asp_youtube_id( (string) get_post_meta( $p->ID, 'predicacion_video_url', true ) );
	if ( '' !== $vid ) {
		$por_video[ $vid ] = $p;
	}
	// El resto de "Ante Su Palabra 2019" (mayo de 2019) entra por título.
	if ( str_contains( $p->post_title, 'Ante Su Palabra 2019' ) && '' !== $vid && ! in_array( $vid, $videos['asp2019'], true ) ) {
		$videos['asp2019'][] = $vid;
	}
}

$ymd_a_input = static fn( string $ymd ): string => $ymd ? substr( $ymd, 0, 4 ) . '-' . substr( $ymd, 4, 2 ) . '-' . substr( $ymd, 6, 2 ) : '';

foreach ( $eventos as $clave => [ $titulo, $slug, $inicio, $fin, $ciudad, $pais, $sede, $dir, $inic ] ) {
	$completo = $inicio && $fin && $ciudad && $pais;

	$id = 0;
	foreach ( get_posts( [ 'post_type' => 'evento', 'post_status' => 'any', 'title' => $titulo, 'numberposts' => -1 ] ) as $p ) {
		if ( (string) get_post_meta( $p->ID, 'evento_fecha_inicio', true ) === $inicio ) {
			$id = $p->ID;
		}
	}

	if ( ! $id ) {
		// Por el mismo camino que el formulario del panel: validación y guardado propios.
		$_POST = [
			'asp_evento_nonce'          => wp_create_nonce( 'asp_guardar_evento' ),
			'evento_fecha_inicio'       => $ymd_a_input( $inicio ),
			'evento_fecha_fin'          => $ymd_a_input( $fin ),
			'evento_ciudad'             => $ciudad,
			'evento_pais'               => $pais,
			'evento_estado_inscripcion' => 'cerrada',
			'evento_url_registro'       => '',
			'evento_iniciativa'         => (string) $iniciativas[ $inic ],
			'evento_sede_nombre'        => $sede,
			'evento_sede_direccion'     => $dir,
			'evento_precio'             => '',
			'evento_flyer'              => '',
			'evento_descripcion'        => '',
		];
		$datos = [ 'post_type' => 'evento', 'post_status' => $completo ? 'publish' : 'draft', 'post_title' => $titulo ];
		if ( $slug ) {
			$datos['post_name'] = $slug;
		}
		$id = wp_insert_post( $datos, true );
		$_POST = [];
		if ( is_wp_error( $id ) ) {
			fwrite( STDERR, "Error en {$clave}: " . $id->get_error_message() . "\n" );
			continue;
		}
		$accion = 'creado';
	} else {
		$accion = 'ya estaba';
	}
	$publicado = 'publish' === get_post_status( $id );

	$n = 0;
	foreach ( $videos[ $clave ] ?? [] as $orden => $vid ) {
		$pred = $por_video[ $vid ] ?? null;
		if ( ! $pred ) {
			continue;
		}
		update_post_meta( $pred->ID, 'predicacion_evento', $id );
		wp_update_post( [ 'ID' => $pred->ID, 'menu_order' => $orden + 1 ] );
		if ( $publicado ) {
			delete_post_meta( $pred->ID, 'predicacion_fecha' );
		}
		$n++;
	}

	printf( "%-9s #%-5d %-9s %-9s %s%s\n", $clave, $id, $accion, $publicado ? 'publicado' : 'borrador', $titulo, $n ? " · {$n} predicaciones" : '' );
}

// "Un libro en un día" ya existía antes de esta carga: le falta la iniciativa,
// y se canceló (su flyer en Entrada27 dice "Evento cancelado"), así que no
// se muestra como realizado: queda en borrador.
foreach ( get_posts( [ 'post_type' => 'evento', 'post_status' => 'any', 'title' => 'Un libro en un día', 'numberposts' => -1 ] ) as $p ) {
	if ( ! get_post_meta( $p->ID, 'evento_iniciativa', true ) && $iniciativas['tal'] ) {
		update_post_meta( $p->ID, 'evento_iniciativa', $iniciativas['tal'] );
		echo "Un libro en un día #{$p->ID} → Taller de Predicación Expositiva\n";
	}
	if ( 'publish' === $p->post_status ) {
		wp_update_post( [ 'ID' => $p->ID, 'post_status' => 'draft' ] );
		echo "Un libro en un día #{$p->ID} → borrador (cancelado)\n";
	}
}
