<?php
/**
 * Bloque Sede. Args: post_id, con_pais (bool). Se autooculta si está vacío.
 * Con dirección, suma el link "Cómo llegar" a Google Maps.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

$asp_id   = (int) ( $args['post_id'] ?? get_the_ID() );
$asp_sede = (string) get_post_meta( $asp_id, 'evento_sede_nombre', true );
$asp_dir  = (string) get_post_meta( $asp_id, 'evento_sede_direccion', true );
$asp_mapa = asp_evento_url_mapa( $asp_id );

if ( '' === $asp_sede && '' === $asp_dir ) {
	return;
}
?>
<span class="asp-label"><?php esc_html_e( 'Sede', 'asp' ); ?></span>
<?php if ( $asp_sede ) : ?>
	<span class="asp-sede__nombre"><?php echo esc_html( $asp_sede ); ?></span>
<?php endif; ?>
<?php if ( $asp_dir ) : ?>
	<span class="asp-bloque__detalle"><?php echo nl2br( esc_html( $asp_dir ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
<?php endif; ?>
<?php if ( ! empty( $args['con_pais'] ) && asp_evento_pais( $asp_id ) ) : ?>
	<span class="asp-lugar-grande__pais"><span class="asp-label"><?php echo esc_html( asp_evento_pais( $asp_id ) ); ?></span></span>
<?php endif; ?>
<?php if ( $asp_mapa ) : ?>
	<a class="asp-link-accion" href="<?php echo esc_url( $asp_mapa ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Cómo llegar', 'asp' ); ?><span class="screen-reader-text"> <?php esc_html_e( '(abre Google Maps)', 'asp' ); ?></span></a>
<?php endif;
