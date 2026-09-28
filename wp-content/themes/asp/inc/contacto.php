<?php
/**
 * Formulario de contacto sin plugin: nonce, honeypot, trampa de tiempo,
 * validación en el servidor y wp_mail al email del ministerio.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

/**
 * Email destino: el de contacto del ministerio.
 *
 * @return string
 */
function asp_contacto_destino(): string {
	return asp_email();
}

/**
 * Procesa el envío.
 *
 * @return void
 */
function asp_contacto_enviar(): void {
	$volver = wp_get_referer() ?: home_url( '/' );
	$volver = remove_query_arg( [ 'enviado', 'error', 'asp_error' ], strtok( $volver, '#' ) );
	/* Vuelve al panel del formulario, donde se muestra el aviso. */
	add_filter( 'wp_redirect', static fn( string $url ): string => $url . '#escribinos' );

	$nonce = $_POST['asp_contacto_nonce'] ?? '';
	if ( ! is_string( $nonce ) || ! wp_verify_nonce( $nonce, 'asp_contacto' ) ) {
		wp_safe_redirect( add_query_arg( 'asp_error', 'sesion', $volver ) );
		exit;
	}

	/* Honeypot y trampa de tiempo: los bots llenan todo y en menos de tres segundos. */
	$honeypot = (string) ( $_POST['sitio_web'] ?? '' );
	$t        = absint( $_POST['asp_t'] ?? 0 );
	if ( '' !== $honeypot || ( time() - $t ) < 3 ) {
		wp_safe_redirect( add_query_arg( 'enviado', '1', $volver ) );
		exit;
	}

	$nombre  = sanitize_text_field( wp_unslash( (string) ( $_POST['nombre'] ?? '' ) ) );
	$email   = sanitize_email( wp_unslash( (string) ( $_POST['email'] ?? '' ) ) );
	$mensaje = sanitize_textarea_field( wp_unslash( (string) ( $_POST['mensaje'] ?? '' ) ) );

	if ( '' === $nombre || ! is_email( $email ) || strlen( $mensaje ) < 10 ) {
		wp_safe_redirect( add_query_arg( 'asp_error', 'campos', $volver ) );
		exit;
	}

	$asunto = sprintf( /* translators: %s: nombre */ __( 'Contacto desde el sitio: %s', 'asp' ), $nombre );
	$cuerpo = sprintf( "%s\n\n%s\n\n—\n%s <%s>", $mensaje, __( 'Respondé a este mail para contestarle.', 'asp' ), $nombre, $email );
	$ok     = wp_mail( asp_contacto_destino(), $asunto, $cuerpo, [ 'Reply-To: ' . $nombre . ' <' . $email . '>' ] );

	wp_safe_redirect( add_query_arg( $ok ? 'enviado' : 'asp_error', $ok ? '1' : 'envio', $volver ) );
	exit;
}
add_action( 'admin_post_nopriv_asp_contacto', 'asp_contacto_enviar' );
add_action( 'admin_post_asp_contacto', 'asp_contacto_enviar' );

/**
 * Foto de la portada de Contacto: la propia de Personalizar o, si no hay,
 * la segunda de la galería del inicio. Vacío si no hay ninguna.
 *
 * @return string
 */
function asp_contacto_foto(): string {
	$clave = absint( get_theme_mod( 'asp_contacto_imagen', 0 ) ) ? 'asp_contacto_imagen' : 'asp_galeria_2';
	return asp_imagen_mod( $clave, 'asp-contacto-portada__foto', 'full', 'eager' );
}

/**
 * Mensaje de estado tras el envío.
 *
 * @return array{tipo:string,texto:string}|null
 */
function asp_contacto_estado(): ?array {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! empty( $_GET['enviado'] ) ) {
		return [ 'tipo' => 'ok', 'texto' => __( 'Recibimos tu mensaje. Gracias por escribir.', 'asp' ) ];
	}
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	/* asp_error y no error: "error" es un parámetro reservado de WordPress,
	   que lo borra antes de llegar a la plantilla, y el aviso no salía nunca. */
	$error = sanitize_key( (string) ( $_GET['asp_error'] ?? '' ) );
	$textos = [
		'campos' => __( 'Falta el nombre, un email válido o el mensaje.', 'asp' ),
		'sesion' => __( 'La página estuvo abierta demasiado tiempo. Volvé a enviar.', 'asp' ),
		'envio'  => __( 'No pudimos enviar el mensaje. Probá de nuevo o escribinos por mail.', 'asp' ),
	];
	return isset( $textos[ $error ] ) ? [ 'tipo' => 'error', 'texto' => $textos[ $error ] ] : null;
}
