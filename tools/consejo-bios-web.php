<?php
/**
 * Bios y web de la iglesia de los miembros del Consejo Pastoral (16-10-2026).
 *
 * Bios de Travis y Mercado tal como las pasó el usuario el 9-10-2026 (ya en
 * producción vía actualizar-encarga.php; acá para que local las tenga). Las
 * de Leandi, Daglio y Oriolo se redactaron a partir de fuentes públicas:
 * fichas de autor de Coalición por el Evangelio, bios de la conferencia 2019
 * en 9Marcas, endos.network, fichas de misioneros de SERVE (Denton Bible
 * Church) y la página de liderazgo de ibdelagracia.com. Harris y Palomares
 * no tienen bio: en ninguna fuente aparece más que «pastor de tal iglesia»
 * (regla 7: no inventar).
 *
 * Idempotente: solo completa lo que está vacío. Para reemplazar una bio o un
 * link, editarlo desde el panel.
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
	'Cristian Palomares' => [
		'web' => 'https://www.youtube.com/c/VidaParaDarVidaRosario',
	],
	'Ricardo Daglio'     => [
		'web' => 'https://www.instagram.com/iglesiabiblicavillaregina/',
		'bio' => 'Ricardo Daglio es pastor de la Iglesia Bíblica de Villa Regina (UCB), en la Patagonia argentina, desde 2008. Antes pastoreó durante dieciséis años en Salto, Uruguay. Desde 2021 vive en General Roca, donde trabaja en la plantación de una iglesia en el valle de Río Negro, y como misionero de SERVE sirve a pastores y líderes de la región con capacitación y conferencias. Está casado con Silvina y tienen tres hijos: Carolina, Lucas y Micaela.',
	],
	'Joselo Mercado'     => [
		'web' => 'https://graciasob.org/',
		'bio' => 'José (Joselo) Mercado es miembro del concilio de Coalición por el Evangelio. Oriundo de Puerto Rico, renuncia a su carrera de consultoría en el año 2006 para ingresar al colegio de pastores de Sovereign Grace Ministries. Es el pastor principal de la Iglesia Gracia Soberana en Gaithersburg, Maryland. Joselo completó su Maestría en Artes en estudios teológicos en SBTS, y está casado con Kathy Mercado y es padre de Joey y Janelle.',
	],
	'Enrique Oriolo'     => [
		'web' => 'https://ibdelagracia.com/',
		'bio' => 'Enrique Oriolo es pastor plantador de la Iglesia Bíblica de la Gracia, en Lanús Oeste, y misionero de SERVE, la agencia misionera de Denton Bible Church. Fue ordenado al ministerio pastoral en 2018 en la Iglesia Bíblica de City Bell, donde sirvió como pastor durante cinco años, y en 2022 plantó con un grupo de esa iglesia la Iglesia Bíblica de Avellaneda, hoy Iglesia Bíblica de la Gracia. Coordina los Centros de Capacitación Bíblica para Pastores y Líderes en Argentina y Sudamérica, y continúa sus estudios en el Seminario Bíblico William Carey. Está casado con Tamara y tienen cuatro hijas: Luz, Paz, Sarah y Alma.',
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
		if ( '' !== (string) get_post_meta( $pid, 'persona_' . $campo, true ) ) {
			echo "= {$nombre}: {$campo} ya cargada\n";
			continue;
		}
		update_post_meta( $pid, 'persona_' . $campo, $sanear( $datos[ $campo ] ) );
		echo "+ {$nombre}: {$campo}\n";
	}
}
