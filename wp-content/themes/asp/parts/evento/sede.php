<?php
/**
 * Bloque Sede. Args: post_id, con_lugar (bool). Se autooculta si está vacío.
 * Con dirección, suma el link "Cómo llegar" a Google Maps.
 *
 * Con con_lugar (columna lateral de la ficha, 2-10-2026), suma la ciudad y
 * el país, que ya no van en la cabecera; sin sede ni dirección, el bloque
 * pasa a ser «Lugar» con la ciudad y el país solos.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

$asp_id   = (int) ( $args['post_id'] ?? get_the_ID() );
$asp_sede = (string) get_post_meta( $asp_id, 'evento_sede_nombre', true );
$asp_dir  = (string) get_post_meta( $asp_id, 'evento_sede_direccion', true );
$asp_mapa  = asp_evento_url_mapa( $asp_id );
$asp_lugar = ! empty( $args['con_lugar'] ) ? implode( ', ', array_filter( [ asp_evento_ciudad( $asp_id ), asp_evento_pais( $asp_id ) ] ) ) : '';

if ( '' === $asp_sede && '' === $asp_dir ) {
	if ( '' !== $asp_lugar ) :
		?>
		<h2 class="asp-label"><?php esc_html_e( 'Lugar', 'asp' ); ?></h2>
		<span class="asp-sede__nombre"><?php echo esc_html( $asp_lugar ); ?></span>
		<?php
	endif;
	return;
}
?>
<h2 class="asp-label"><?php esc_html_e( 'Sede', 'asp' ); ?></h2>
<?php if ( $asp_sede ) : ?>
	<span class="asp-sede__nombre"><?php echo esc_html( $asp_sede ); ?></span>
<?php endif; ?>
<?php if ( $asp_dir ) : ?>
	<?php /* Sin nombre de sede, la dirección es el dato principal: en chico parecía una nota al pie. */ ?>
	<span class="<?php echo $asp_sede ? 'asp-bloque__detalle' : 'asp-sede__nombre'; ?>"><?php echo nl2br( esc_html( $asp_dir ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
<?php endif; ?>
<?php if ( '' !== $asp_lugar ) : ?>
	<span class="asp-lugar-grande__pais"><span class="asp-label"><?php echo esc_html( $asp_lugar ); ?></span></span>
<?php endif; ?>
<?php if ( $asp_mapa ) : ?>
	<a class="asp-link-accion" href="<?php echo esc_url( $asp_mapa ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Cómo llegar', 'asp' ); ?><span class="screen-reader-text"> <?php esc_html_e( '(abre Google Maps)', 'asp' ); ?></span></a>
<?php endif;
