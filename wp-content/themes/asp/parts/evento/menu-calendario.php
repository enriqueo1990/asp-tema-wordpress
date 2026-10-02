<?php
/**
 * Menú «Guardar en el calendario» (details/summary). Args: agenda
 * (['google' => url, 'ics' => url]), variante (link|secundario|primario).
 *
 * - link: junto a la fecha, en el panel de datos; abre hacia abajo.
 * - secundario / primario: en la banda de acción; abren hacia arriba. En el
 *   teléfono el secundario dice solo «Calendario».
 *
 * Apple Calendar y Outlook usan el mismo .ics que genera el tema.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

$asp_agenda   = (array) ( $args['agenda'] ?? [] );
$asp_variante = (string) ( $args['variante'] ?? 'link' );
if ( empty( $asp_agenda['google'] ) ) {
	return;
}
$asp_en_banda = 'link' !== $asp_variante;
?>
<details class="ev-menu<?php echo $asp_en_banda ? ' ev-menu--up' : ' ev-menu--link'; ?><?php echo 'primario' === $asp_variante ? ' ev-menu--primario' : ''; ?>" data-asp-menu>
	<?php if ( 'link' === $asp_variante ) : ?>
		<summary><?php echo asp_ficha_icono( 'calendar-plus', 'ev-ico--sm' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span class="ev-u"><?php esc_html_e( 'Guardar en el calendario', 'asp' ); ?></span></summary>
	<?php else : ?>
		<summary class="ev-btn ev-btn--<?php echo 'primario' === $asp_variante ? 'primary' : 'secondary'; ?>">
			<?php echo asp_ficha_icono( 'calendar-plus', 'ev-ico--sm' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<?php if ( 'primario' === $asp_variante ) : ?>
				<?php esc_html_e( 'Guardar en el calendario', 'asp' ); ?>
			<?php else : ?>
				<span class="ev-only-m"><?php esc_html_e( 'Calendario', 'asp' ); ?></span><span class="ev-only-d"><?php esc_html_e( 'Guardar en el calendario', 'asp' ); ?></span>
			<?php endif; ?>
		</summary>
	<?php endif; ?>
	<div class="ev-menu__list">
		<a href="<?php echo esc_url( $asp_agenda['google'] ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Google Calendar', 'asp' ); ?><?php echo asp_aviso_pestana(); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
		<a href="<?php echo esc_url( $asp_agenda['ics'] ); ?>" download><?php esc_html_e( 'Apple Calendar', 'asp' ); ?></a>
		<a href="<?php echo esc_url( $asp_agenda['ics'] ); ?>" download><?php esc_html_e( 'Outlook', 'asp' ); ?></a>
	</div>
</details>
