<?php
/**
 * Formulario de contacto sin plugin: nonce, honeypot, marca de tiempo
 * firmada, límite de envíos por IP, validación en el servidor y wp_mail al
 * email del ministerio.
 *
 * Si algo falla, lo escrito se guarda unos minutos y el formulario vuelve
 * completo, con el campo que falta marcado. Antes volvía vacío.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

/** Largos máximos de cada campo. */
const ASP_CONTACTO_LARGOS = [ 'nombre' => 100, 'email' => 254, 'mensaje' => 5000 ];

/** Envíos permitidos por IP en la ventana de tiempo. */
const ASP_CONTACTO_LIMITE = 3;

/**
 * Email destino: el de contacto del ministerio.
 *
 * @return string
 */
function asp_contacto_destino(): string {
	return asp_email();
}

/**
 * Marca de tiempo firmada para el campo oculto: "segundos.firma".
 *
 * @return string
 */
function asp_contacto_marca(): string {
	$t = (string) time();
	return $t . '.' . hash_hmac( 'sha256', $t, wp_salt( 'nonce' ) );
}

/**
 * Segundos desde que se abrió el formulario, o -1 si la marca falta o no
 * es nuestra.
 *
 * @param string $marca Valor recibido.
 * @return int
 */
function asp_contacto_segundos( string $marca ): int {
	$partes = explode( '.', $marca, 2 );
	if ( 2 !== count( $partes ) || ! ctype_digit( $partes[0] ) ) {
		return -1;
	}
	$esperada = hash_hmac( 'sha256', $partes[0], wp_salt( 'nonce' ) );
	return hash_equals( $esperada, $partes[1] ) ? time() - (int) $partes[0] : -1;
}

/**
 * Campo de texto del POST: solo strings, recortado al largo máximo.
 *
 * @param string $clave Nombre del campo.
 * @return string
 */
function asp_contacto_campo( string $clave ): string {
	$valor = $_POST[ $clave ] ?? ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
	if ( ! is_string( $valor ) ) {
		return '';
	}
	$valor = wp_unslash( $valor );
	return mb_substr( $valor, 0, ASP_CONTACTO_LARGOS[ $clave ] ?? 200 );
}

/**
 * Clave del contador de envíos de la IP actual (hash: no se guarda la IP).
 *
 * @return string
 */
function asp_contacto_clave_ip(): string {
	$ip = (string) ( $_SERVER['REMOTE_ADDR'] ?? '' );
	return 'asp_contacto_ip_' . substr( hash_hmac( 'sha256', $ip, wp_salt( 'auth' ) ), 0, 20 );
}

/**
 * Procesa el envío.
 *
 * @return void
 */
function asp_contacto_enviar(): void {
	$volver = wp_get_referer() ?: home_url( '/' );
	$volver = remove_query_arg( [ 'enviado', 'error', 'asp_error', 'asp_falta', 'asp_borrador' ], strtok( $volver, '#' ) );
	/* Vuelve al panel del formulario, donde se muestra el aviso. */
	add_filter( 'wp_redirect', static fn( string $url ): string => $url . '#escribinos' );

	$nombre  = sanitize_text_field( asp_contacto_campo( 'nombre' ) );
	$email   = sanitize_email( asp_contacto_campo( 'email' ) );
	$mensaje = sanitize_textarea_field( asp_contacto_campo( 'mensaje' ) );

	/* Guarda lo escrito para devolverlo en el formulario si algo falla. */
	$fallar = static function ( string $motivo, array $faltan = [] ) use ( $volver, $nombre, $email, $mensaje ): void {
		$token = wp_generate_password( 16, false );
		set_transient( 'asp_contacto_borrador_' . $token, [ 'nombre' => $nombre, 'email' => $email, 'mensaje' => $mensaje ], 15 * MINUTE_IN_SECONDS );
		$args = [ 'asp_error' => $motivo, 'asp_borrador' => $token ];
		if ( $faltan ) {
			$args['asp_falta'] = implode( ',', $faltan );
		}
		wp_safe_redirect( add_query_arg( $args, $volver ) );
		exit;
	};

	$nonce = $_POST['asp_contacto_nonce'] ?? ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
	if ( ! is_string( $nonce ) || ! wp_verify_nonce( $nonce, 'asp_contacto' ) ) {
		$fallar( 'sesion' );
	}

	/* Honeypot y marca de tiempo firmada: los bots llenan todo, en menos de
	   tres segundos, o mandan una marca inventada. Se les dice "enviado". */
	$segundos = asp_contacto_segundos( asp_contacto_campo( 'asp_t' ) );
	if ( '' !== asp_contacto_campo( 'sitio_web' ) || $segundos < 3 ) {
		wp_safe_redirect( add_query_arg( 'enviado', '1', $volver ) );
		exit;
	}

	$faltan = [];
	if ( '' === $nombre ) {
		$faltan[] = 'nombre';
	}
	if ( ! is_email( $email ) ) {
		$faltan[] = 'email';
	}
	if ( mb_strlen( $mensaje ) < 10 ) {
		$faltan[] = 'mensaje';
	}
	if ( $faltan ) {
		$fallar( 'campos', $faltan );
	}

	$clave  = asp_contacto_clave_ip();
	$envios = (int) get_transient( $clave );
	if ( $envios >= ASP_CONTACTO_LIMITE ) {
		$fallar( 'limite' );
	}

	$motivos = asp_contacto_motivos();
	$motivo  = sanitize_key( asp_contacto_campo( 'motivo' ) );
	$asunto  = isset( $motivos[ $motivo ] )
		/* translators: 1: motivo, 2: nombre */
		? sprintf( __( '%1$s: %2$s', 'asp' ), $motivos[ $motivo ], $nombre )
		/* translators: %s: nombre */
		: sprintf( __( 'Contacto desde el sitio: %s', 'asp' ), $nombre );
	$cuerpo = sprintf( "%s\n\n%s\n\n—\n%s <%s>", $mensaje, __( 'Respondé a este mail para contestarle.', 'asp' ), $nombre, $email );
	/* Nombre sin comillas ni saltos: va dentro de una cabecera. */
	$nombre_cabecera = trim( str_replace( [ '"', "\r", "\n", '<', '>' ], '', $nombre ) );
	$ok = wp_mail( asp_contacto_destino(), $asunto, $cuerpo, [ 'Reply-To: "' . $nombre_cabecera . '" <' . $email . '>' ] );

	if ( ! $ok ) {
		$fallar( 'envio' );
	}
	set_transient( $clave, $envios + 1, 10 * MINUTE_IN_SECONDS );
	wp_safe_redirect( add_query_arg( 'enviado', '1', $volver ) );
	exit;
}
add_action( 'admin_post_nopriv_asp_contacto', 'asp_contacto_enviar' );
add_action( 'admin_post_asp_contacto', 'asp_contacto_enviar' );

/**
 * Motivos que pueden llegar precargados desde otra página con ?motivo=.
 * El motivo va como asunto del mail.
 *
 * @return array<string,string> clave => asunto.
 */
function asp_contacto_motivos(): array {
	return [
		'taller' => __( 'Quiero recibir un taller en mi iglesia', 'asp' ),
	];
}

/**
 * Motivo precargado en la dirección, o cadena vacía.
 *
 * @return string
 */
function asp_contacto_motivo(): string {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$motivo = isset( $_GET['motivo'] ) ? sanitize_key( (string) wp_unslash( $_GET['motivo'] ) ) : '';
	return isset( asp_contacto_motivos()[ $motivo ] ) ? $motivo : '';
}

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
 * Lo que la persona había escrito antes de un error, para volver a mostrarlo.
 *
 * @return array{nombre:string,email:string,mensaje:string}
 */
function asp_contacto_borrador(): array {
	$vacio = [ 'nombre' => '', 'email' => '', 'mensaje' => '' ];
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$token = isset( $_GET['asp_borrador'] ) ? preg_replace( '/[^A-Za-z0-9]/', '', (string) wp_unslash( $_GET['asp_borrador'] ) ) : '';
	if ( '' === $token ) {
		return $vacio;
	}
	$datos = get_transient( 'asp_contacto_borrador_' . $token );
	return is_array( $datos ) ? array_merge( $vacio, array_map( 'strval', $datos ) ) : $vacio;
}

/**
 * Campos que faltaron en el último envío.
 *
 * @return string[]
 */
function asp_contacto_faltan(): array {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$lista = isset( $_GET['asp_falta'] ) ? (string) wp_unslash( $_GET['asp_falta'] ) : '';
	return array_values( array_intersect( array_map( 'sanitize_key', explode( ',', $lista ) ), [ 'nombre', 'email', 'mensaje' ] ) );
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
	/* asp_error y no error: "error" es un parámetro reservado de WordPress,
	   que lo borra antes de llegar a la plantilla, y el aviso no salía nunca. */
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$error  = sanitize_key( (string) ( $_GET['asp_error'] ?? '' ) );
	$textos = [
		'campos' => __( 'Revisá lo marcado: falta completar un campo o el mail no es válido. Lo que escribiste sigue acá.', 'asp' ),
		'sesion' => __( 'La página estuvo abierta mucho tiempo y el envío no salió. Lo que escribiste sigue acá: tocá «Enviar mensaje» de nuevo.', 'asp' ),
		/* translators: %s: mail del ministerio */
		'limite' => sprintf( __( 'Ya recibimos varios mensajes desde esta conexión. Probá en unos minutos o escribinos a %s.', 'asp' ), asp_email() ),
		/* translators: %s: mail del ministerio */
		'envio'  => sprintf( __( 'No pudimos enviar el mensaje. Lo que escribiste sigue acá: probá de nuevo o escribinos a %s.', 'asp' ), asp_email() ),
	];
	return isset( $textos[ $error ] ) ? [ 'tipo' => 'error', 'texto' => $textos[ $error ] ] : null;
}
