<?php
/**
 * Agregar al calendario: Google Calendar y archivo .ics (Apple, Outlook).
 * Args: post_id. Se autooculta sin fechas o si el evento ya pasó.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

$asp_id = (int) ( $args['post_id'] ?? get_the_ID() );

if ( ! asp_evento_agendable( $asp_id ) ) {
	return;
}
?>
<div class="asp-agenda">
	<span class="asp-label"><?php esc_html_e( 'Agregar al calendario', 'asp' ); ?></span>
	<div class="asp-agenda__links">
		<a class="asp-link-accion" href="<?php echo esc_url( asp_evento_url_google_calendar( $asp_id ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Google Calendar', 'asp' ); ?><span class="screen-reader-text"> <?php esc_html_e( '(se abre en otra pestaña)', 'asp' ); ?></span></a>
		<a class="asp-link-accion" href="<?php echo esc_url( asp_evento_url_ics( $asp_id ) ); ?>" download><?php esc_html_e( 'Apple y Outlook', 'asp' ); ?><span class="screen-reader-text"> <?php esc_html_e( '(descarga un archivo .ics)', 'asp' ); ?></span></a>
	</div>
</div>
