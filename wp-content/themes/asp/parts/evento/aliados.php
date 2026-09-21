<?php
/**
 * Aliados del evento. Args: post_id. Se autooculta si está vacío.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

$asp_id      = (int) ( $args['post_id'] ?? get_the_ID() );
$asp_aliados = asp_evento_relacionados( $asp_id, 'evento_aliados', 'aliado' );

if ( empty( $asp_aliados ) ) {
	return;
}
?>
<div class="asp-bloque">
	<span class="asp-label"><?php esc_html_e( 'Aliados', 'asp' ); ?></span>
	<div class="asp-aliados">
		<?php foreach ( $asp_aliados as $asp_i => $asp_aliado ) :
			$asp_url  = (string) get_post_meta( $asp_aliado->ID, 'aliado_url', true );
			$asp_logo = absint( get_post_meta( $asp_aliado->ID, 'aliado_logo', true ) );
			$asp_html = $asp_logo ? wp_get_attachment_image( $asp_logo, 'medium', false, [ 'alt' => get_the_title( $asp_aliado ) ] ) : '';
			if ( ! $asp_html ) {
				$asp_html = ( $asp_i > 0 ? '· ' : '' ) . esc_html( get_the_title( $asp_aliado ) );
			}
			if ( $asp_url ) :
				?>
				<a href="<?php echo esc_url( $asp_url ); ?>" rel="noopener" target="_blank"><?php echo $asp_html; // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
			<?php else : ?>
				<span><?php echo $asp_html; // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
			<?php endif;
		endforeach; ?>
	</div>
</div>
