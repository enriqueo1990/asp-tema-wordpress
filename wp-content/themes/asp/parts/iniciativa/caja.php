<?php
/**
 * Tarjeta de iniciativa para el inicio. Args: post_id, numero.
 *
 * Con foto propia (campo "Imagen" de la iniciativa), la foto va de fondo con
 * un velo. Sin foto, un bloque de color de la paleta. En los dos casos, solo
 * el nombre grande: el párrafo recortado con "…" se fue.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

$asp_id   = (int) ( $args['post_id'] ?? get_the_ID() );
$asp_num  = max( 1, (int) ( $args['numero'] ?? 1 ) );
$asp_foto = asp_iniciativa_foto( $asp_id );
$asp_tono = ( ( $asp_num - 1 ) % 4 ) + 1;
?>
<a class="asp-tile asp-tile--tono-<?php echo esc_attr( (string) $asp_tono ); ?><?php echo $asp_foto ? ' asp-tile--con-foto' : ''; ?>" href="<?php echo esc_url( get_permalink( $asp_id ) ); ?>">
	<?php echo $asp_foto; // phpcs:ignore WordPress.Security.EscapeOutput ?>
	<span class="asp-tile__texto">
		<span class="asp-tile__num" aria-hidden="true"><?php echo esc_html( asp_romano( $asp_num ) ); ?></span>
		<span class="asp-tile__titulo"><?php echo esc_html( get_the_title( $asp_id ) ); ?></span>
	</span>
	<span class="asp-tile__flecha" aria-hidden="true"></span>
</a>
