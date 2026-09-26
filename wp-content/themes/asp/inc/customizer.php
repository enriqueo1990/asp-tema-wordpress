<?php
/**
 * Ajustes del sitio en el Customizer nativo: fotografías de los heros,
 * galería de la home, eslogan, redes y email. Sin plugins.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

/**
 * Texto de misión por defecto, tal cual está en el sitio actual.
 *
 * @return string
 */
function asp_mision_default(): string {
	return 'Ante Su Palabra es una comunión de pastores e iglesias locales, que cree en la autoridad y la suficiencia de las Escrituras, cuyo propósito es contribuir a la salud de las iglesias locales.';
}

/**
 * Registra sección, ajustes y controles.
 *
 * @param WP_Customize_Manager $wp_customize Manager.
 * @return void
 */
function asp_customizer( WP_Customize_Manager $wp_customize ): void {
	$wp_customize->add_section(
		'asp_sitio',
		[
			'title'       => __( 'Ante Su Palabra', 'asp' ),
			'description' => __( 'Fotografías, eslogan, redes y email. Todo lo que no es un evento ni un artículo.', 'asp' ),
			'priority'    => 20,
		]
	);

	$imagenes = [
		'asp_hero_imagen'     => __( 'Fotografía del inicio (a la derecha del versículo, 4:3)', 'asp' ),
		'asp_nosotros_imagen' => __( 'Fotografía de la página Nosotros', 'asp' ),
		'asp_galeria_1'       => __( 'Galería del inicio · foto 1', 'asp' ),
		'asp_galeria_2'       => __( 'Galería del inicio · foto 2', 'asp' ),
		'asp_galeria_3'       => __( 'Galería del inicio · foto 3', 'asp' ),
		'asp_galeria_4'       => __( 'Galería del inicio · foto 4', 'asp' ),
	];
	foreach ( $imagenes as $clave => $etiqueta ) {
		$wp_customize->add_setting( $clave, [ 'sanitize_callback' => 'absint', 'default' => 0 ] );
		$wp_customize->add_control(
			new WP_Customize_Media_Control(
				$wp_customize,
				$clave,
				[
					'label'     => $etiqueta,
					'section'   => 'asp_sitio',
					'mime_type' => 'image',
				]
			)
		);
	}

	$wp_customize->add_setting( 'asp_hero_eslogan', [ 'sanitize_callback' => 'sanitize_text_field', 'default' => '' ] );
	$wp_customize->add_control(
		'asp_hero_eslogan',
		[
			'label'       => __( 'Eslogan propio', 'asp' ),
			'description' => __( 'Si se deja vacío, el inicio muestra el versículo de Isaías 66:2.', 'asp' ),
			'section'     => 'asp_sitio',
			'type'        => 'text',
		]
	);

	$wp_customize->add_setting( 'asp_mision_texto', [ 'sanitize_callback' => 'sanitize_textarea_field', 'default' => asp_mision_default() ] );
	$wp_customize->add_control(
		'asp_mision_texto',
		[
			'label'   => __( 'Texto de la misión', 'asp' ),
			'section' => 'asp_sitio',
			'type'    => 'textarea',
		]
	);

	$wp_customize->add_setting( 'asp_testimonio_texto', [ 'sanitize_callback' => 'sanitize_textarea_field', 'default' => '' ] );
	$wp_customize->add_control(
		'asp_testimonio_texto',
		[
			'label'       => __( 'Testimonio', 'asp' ),
			'description' => __( 'Palabras de un pastor que participó de una conferencia, tal como las dijo. Aparece en el inicio, entre las predicaciones y los artículos. Hace falta también el nombre; si falta alguno de los dos, no se muestra.', 'asp' ),
			'section'     => 'asp_sitio',
			'type'        => 'textarea',
		]
	);
	$wp_customize->add_setting( 'asp_testimonio_nombre', [ 'sanitize_callback' => 'sanitize_text_field', 'default' => '' ] );
	$wp_customize->add_control( 'asp_testimonio_nombre', [ 'label' => __( 'Testimonio: nombre', 'asp' ), 'section' => 'asp_sitio', 'type' => 'text' ] );
	$wp_customize->add_setting( 'asp_testimonio_iglesia', [ 'sanitize_callback' => 'sanitize_text_field', 'default' => '' ] );
	$wp_customize->add_control(
		'asp_testimonio_iglesia',
		[
			'label'       => __( 'Testimonio: iglesia y ciudad', 'asp' ),
			'description' => __( 'Ej. Pastor, Iglesia Bíblica de Salta', 'asp' ),
			'section'     => 'asp_sitio',
			'type'        => 'text',
		]
	);

	$wp_customize->add_setting( 'asp_sumarse_texto', [ 'sanitize_callback' => 'sanitize_textarea_field', 'default' => '' ] );
	$wp_customize->add_control(
		'asp_sumarse_texto',
		[
			'label'       => __( 'Sumarse', 'asp' ),
			'description' => __( 'Qué le decimos a una iglesia o a un pastor que quiere acercarse. Aparece al final del inicio, arriba del enlace al formulario. Vacío = solo se muestra el enlace.', 'asp' ),
			'section'     => 'asp_sitio',
			'type'        => 'textarea',
		]
	);

	$wp_customize->add_setting( 'asp_email', [ 'sanitize_callback' => 'sanitize_email', 'default' => 'contacto@antesupalabra.com' ] );
	$wp_customize->add_control(
		'asp_email',
		[
			'label'       => __( 'Email de contacto', 'asp' ),
			'description' => __( 'Se muestra en el pie y en Contacto, y ahí llegan los mensajes del formulario.', 'asp' ),
			'section'     => 'asp_sitio',
			'type'        => 'email',
		]
	);

	$wp_customize->add_setting( 'asp_donar_url', [ 'sanitize_callback' => 'esc_url_raw', 'default' => '' ] );
	$wp_customize->add_control(
		'asp_donar_url',
		[
			'label'       => __( 'Link para donar', 'asp' ),
			'description' => __( 'La página donde se dona (PayPal, Mercado Pago u otra). Con link cargado aparece el botón "Donar" en la cabecera; vacío, no aparece.', 'asp' ),
			'section'     => 'asp_sitio',
			'type'        => 'url',
		]
	);

	$redes = [
		'asp_red_facebook'  => [ 'Facebook', 'https://www.facebook.com/ConferenciaAnteSuPalabra/' ],
		'asp_red_twitter'   => [ 'X', 'https://x.com/antesupalabra' ],
		'asp_red_youtube'   => [ 'YouTube', 'https://www.youtube.com/channel/UCzBclEQZPuu7qy7rQRpUdaA' ],
		'asp_red_instagram' => [ 'Instagram', 'https://www.instagram.com/antesupalabra/' ],
	];
	foreach ( $redes as $clave => [ $nombre, $default ] ) {
		$wp_customize->add_setting( $clave, [ 'sanitize_callback' => 'esc_url_raw', 'default' => $default ] );
		$wp_customize->add_control( $clave, [ 'label' => $nombre, 'section' => 'asp_sitio', 'type' => 'url' ] );
	}

	$wp_customize->add_setting( 'asp_afirmaciones_pdf', [ 'sanitize_callback' => 'esc_url_raw', 'default' => '' ] );
	$wp_customize->add_control(
		new WP_Customize_Upload_Control(
			$wp_customize,
			'asp_afirmaciones_pdf',
			[
				'label'       => __( 'PDF de Afirmaciones y Negaciones', 'asp' ),
				'description' => __( 'Vacío = se ofrece el PDF que trae el tema.', 'asp' ),
				'section'     => 'asp_sitio',
			]
		)
	);
}
add_action( 'customize_register', 'asp_customizer' );

/**
 * Imagen del Customizer como <img> con clase, o cadena vacía.
 *
 * @param string $clave  Ajuste.
 * @param string $clase  Clase CSS.
 * @param string $tamano Tamaño registrado.
 * @param string $loading lazy|eager.
 * @return string
 */
function asp_imagen_mod( string $clave, string $clase, string $tamano = 'large', string $loading = 'lazy' ): string {
	$id = absint( get_theme_mod( $clave, 0 ) );
	if ( ! $id ) {
		return '';
	}
	return (string) wp_get_attachment_image( $id, $tamano, false, [ 'class' => $clase, 'loading' => $loading, 'fetchpriority' => 'eager' === $loading ? 'high' : 'auto' ] );
}
