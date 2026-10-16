<?php
/**
 * Bios y web de la iglesia de los miembros del Consejo Pastoral (16-10-2026).
 *
 * Bios de Travis y Mercado tal como las pasó el usuario el 9-10-2026 (ya en
 * producción vía actualizar-encarga.php; acá para que local las tenga). Las
 * de Leandi, Daglio y Oriolo se redactaron a partir de fuentes públicas:
 * fichas de autor de Coalición por el Evangelio, bios de la conferencia 2019
 * en 9Marcas, endos.network, fichas de misioneros de SERVE (Denton Bible
 * Church) y las páginas de liderazgo de ibdelagracia.com e ibciudaddedios.com.
 * Harris no tiene bio: en ninguna fuente aparece más que «pastor de tal
 * iglesia» (regla 7: no inventar).
 *
 * Idempotente: solo completa lo que está vacío, salvo las entradas con
 * 'forzar' (texto dado por el usuario, que pisa lo que haya). Para cambiar
 * otra bio o un link, editarlo desde el panel.
 *
 * Las URL dentro de una bio se vuelven links en la ficha (asp_persona_bio_html).
 *
 * Uso: php tools/consejo-bios-web.php
 */

declare(strict_types=1);

require __DIR__ . '/arranque.php';

$consejo = [
	'Greg Travis'        => [
		'web' => 'https://iglesiabiblicareformada.com/',
		'bio' => 'Greg Travis es miembro del concilio de Coalición por el Evangelio. Actualmente sirve como misionero de SERVE y es plantador y pastor de la Iglesia Bíblica Reformada Denton, Texas (Estados Unidos). Tiene una maestría del Dallas Theological Seminary, y su pasión es ayudar a pastores, plantadores e iglesias locales a ser más saludables. Está casado con Carolina, y juntos tienen tres hijos: Alaina, Wendy y Thiago.',
	],
	'Dardo Leandi'       => [
		'web' => 'https://www.ibmdecaba.com.ar/',
		'bio' => 'Dardo Leandi es pastor de la Iglesia Bautista Misionera de la Ciudad de Buenos Aires y cofundador de Ante Su Palabra. Pastorea desde 1998 y planta iglesias desde 2005: durante más de trece años pastoreó la Iglesia Bautista Misionera de Campana, y hoy trabaja en la revitalización y plantación de la iglesia en Buenos Aires. Cursa una maestría en Estudios Teológicos en el Seminario Bíblico William Carey. Está casado con Nancy y tienen dos hijos, Josías y Santiago.',
	],
	'Ernesto Harris'     => [
		'web' => 'https://www.ibmcampana.com.ar/',
	],
	// Web y bio del 16-10-2026, de la página de pastores de su iglesia (ibciudaddedios.com/liderazgo), que pasó el usuario.
	'Cristian Palomares' => [
		'web'    => 'https://www.ibciudaddedios.com/',
		'forzar' => true, // antes apuntaba al canal de YouTube, único rastro que había
		'bio'    => 'Cristian Palomares es pastor de la Iglesia Bíblica Ciudad de Dios, en Rosario, con veinte años de trayectoria en el ministerio pastoral. Preside ABRA e integra el consejo pastoral de Ante Su Palabra. Tiene un bachiller en Teología y Ministerio Pastoral del SBF y una diplomatura en Consejería Bíblica del Seminario Bíblico William Carey, donde cursa una Maestría en Estudios Teológicos. Está casado con Miriam y tienen tres hijos.',
	],
	'Ricardo Daglio'     => [
		'web' => 'https://www.instagram.com/iglesiabiblicavillaregina/',
		'bio' => 'Ricardo Daglio es pastor de la Iglesia Bíblica de Villa Regina (UCB), en la Patagonia argentina, desde 2008. Antes pastoreó durante dieciséis años en Salto, Uruguay. Desde 2021 vive en General Roca, donde trabaja en la plantación de una iglesia en el valle de Río Negro, y como misionero de SERVE sirve a pastores y líderes de la región con capacitación y conferencias. Escribe en su blog personal, https://ricardodaglio.org. Está casado con Silvina y tienen tres hijos: Carolina, Lucas y Micaela.',
		'forzar' => true, // blog agregado el 16-10-2026 a pedido del usuario
	],
	'Joselo Mercado'     => [
		'web' => 'https://graciasob.org/',
		'bio' => 'José (Joselo) Mercado es miembro del concilio de Coalición por el Evangelio. Oriundo de Puerto Rico, renuncia a su carrera de consultoría en el año 2006 para ingresar al colegio de pastores de Sovereign Grace Ministries. Es el pastor principal de la Iglesia Gracia Soberana en Gaithersburg, Maryland. Joselo completó su Maestría en Artes en estudios teológicos en SBTS, y está casado con Kathy Mercado y es padre de Joey y Janelle.',
	],
	// Bio tal como la pasó el usuario, 16-10-2026 (reemplaza la redactada de fuentes).
	'Enrique Oriolo'     => [
		'web'    => 'https://ibdelagracia.com/',
		'forzar' => true,
		'bio'    => "Enrique Oriolo es misionero de SERVE, la agencia misionera de Denton Bible Church, en Denton, Texas (EEUU). Trabaja en el entrenamiento de pastores, plantadores y líderes cristianos. Fue ordenado al ministerio pastoral el 16 de diciembre de 2018 en la Iglesia Bíblica de City Bell, donde sirvió por cinco años como pastor. Actualmente, es pastor de la Iglesia Bíblica de la Gracia, fundada en 2022. Es parte del consejo pastoral del ministerio Ante Su Palabra, cuyo fin es ayudar a las iglesias a ser más saludables.

Graduado del Centro de Capacitación Bíblica para Pastores dictado en su iglesia local, posee un Diplomado en Estudios Teológicos del Instituto Integridad y Sabiduría. Ama servir en la capacitación de nuevos pastores y líderes, anhelando el establecimiento de nuevas iglesias bíblicas. Escribe en su blog personal, https://enriqueoriolo.com.

Enrique está casado con Tamara y tienen cuatro hijas: Luz, Paz, Sarah y Sophie.",
	],
];

foreach ( $consejo as $nombre => $datos ) {
	$persona = get_posts( [ 'post_type' => 'persona', 'title' => $nombre, 'post_status' => 'any', 'posts_per_page' => 1 ] );
	if ( ! $persona ) {
		echo "! no existe la persona {$nombre}\n";
		continue;
	}
	$pid = $persona[0]->ID;
	foreach ( [ 'web' => 'esc_url_raw', 'bio' => 'sanitize_textarea_field' ] as $campo => $sanear ) {
		if ( empty( $datos[ $campo ] ) ) {
			continue;
		}
		if ( empty( $datos['forzar'] ) && '' !== (string) get_post_meta( $pid, 'persona_' . $campo, true ) ) {
			echo "= {$nombre}: {$campo} ya cargada\n";
			continue;
		}
		update_post_meta( $pid, 'persona_' . $campo, $sanear( $datos[ $campo ] ) );
		echo "+ {$nombre}: {$campo}\n";
	}
}
