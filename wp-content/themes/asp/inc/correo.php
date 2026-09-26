<?php
/**
 * Envío de mail por SMTP, sin plugin.
 *
 * DigitalOcean bloquea el puerto 25 en los droplets, así que wp_mail() sin
 * SMTP no llega a ningún lado. Los datos del servicio (Brevo, Postmark,
 * Mailgun…) van como constantes en wp-config.php, nunca en el repo:
 *
 *   define( 'ASP_SMTP_HOST', 'smtp-relay.brevo.com' );
 *   define( 'ASP_SMTP_PORT', 587 );
 *   define( 'ASP_SMTP_USER', '…' );
 *   define( 'ASP_SMTP_PASS', '…' );
 *   define( 'ASP_SMTP_FROM', 'no-responder@antesupalabra.com' );
 *
 * Sin ASP_SMTP_HOST definido no se toca nada: en local sigue el mail de PHP.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

/**
 * Configura PHPMailer con los datos de wp-config.php.
 *
 * @param PHPMailer\PHPMailer\PHPMailer $mailer Instancia de wp_mail().
 * @return void
 */
function asp_smtp( $mailer ): void {
	if ( ! defined( 'ASP_SMTP_HOST' ) || '' === (string) ASP_SMTP_HOST ) {
		return;
	}
	$puerto = defined( 'ASP_SMTP_PORT' ) ? (int) ASP_SMTP_PORT : 587;

	$mailer->isSMTP();
	$mailer->Host       = (string) ASP_SMTP_HOST; // phpcs:ignore WordPress.NamingConventions.ValidVariableName
	$mailer->Port       = $puerto; // phpcs:ignore WordPress.NamingConventions.ValidVariableName
	$mailer->SMTPSecure = 465 === $puerto ? 'ssl' : 'tls'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName
	if ( defined( 'ASP_SMTP_USER' ) && '' !== (string) ASP_SMTP_USER ) {
		$mailer->SMTPAuth = true; // phpcs:ignore WordPress.NamingConventions.ValidVariableName
		$mailer->Username = (string) ASP_SMTP_USER; // phpcs:ignore WordPress.NamingConventions.ValidVariableName
		$mailer->Password = defined( 'ASP_SMTP_PASS' ) ? (string) ASP_SMTP_PASS : ''; // phpcs:ignore WordPress.NamingConventions.ValidVariableName
	}
}
add_action( 'phpmailer_init', 'asp_smtp' );

/**
 * Remitente: el del dominio verificado en el servicio de SMTP. El Reply-To
 * del formulario de contacto sigue apuntando a quien escribió.
 *
 * @param string $remitente Por defecto, wordpress@dominio.
 * @return string
 */
function asp_smtp_remitente( string $remitente ): string {
	return ( defined( 'ASP_SMTP_FROM' ) && is_email( (string) ASP_SMTP_FROM ) ) ? (string) ASP_SMTP_FROM : $remitente;
}
add_filter( 'wp_mail_from', 'asp_smtp_remitente' );

/**
 * Nombre del remitente: el del sitio, no "WordPress".
 *
 * @return string
 */
function asp_smtp_nombre(): string {
	return wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
}
add_filter( 'wp_mail_from_name', 'asp_smtp_nombre' );
