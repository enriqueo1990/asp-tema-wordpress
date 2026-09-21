<?php
/**
 * Ciudad y país. Args: post_id, variante (inline|md|grande).
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

$asp_id       = (int) ( $args['post_id'] ?? get_the_ID() );
$asp_variante = (string) ( $args['variante'] ?? 'inline' );
$asp_ciudad   = asp_evento_ciudad( $asp_id );
$asp_pais     = asp_evento_pais( $asp_id );

if ( '' === $asp_ciudad && '' === $asp_pais ) {
	return;
}

if ( 'grande' === $asp_variante ) : ?>
	<div class="asp-lugar-grande">
		<?php if ( $asp_ciudad ) : ?>
			<span class="asp-lugar-grande__ciudad"><?php echo asp_icono_ubicacion(); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php echo esc_html( $asp_ciudad ); ?></span></span>
		<?php endif; ?>
		<?php if ( $asp_pais ) : ?>
			<span class="asp-lugar-grande__pais"><span class="asp-label"><?php echo esc_html( $asp_pais ); ?></span></span>
		<?php endif; ?>
	</div>
<?php else : ?>
	<div class="asp-lugar<?php echo 'md' === $asp_variante ? ' asp-lugar--md' : ''; ?>">
		<?php if ( $asp_ciudad ) : ?>
			<?php echo asp_icono_ubicacion(); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php echo esc_html( $asp_ciudad ); ?></span>
		<?php endif; ?>
		<?php if ( $asp_ciudad && $asp_pais ) : ?>
			<span class="asp-lugar__sep" aria-hidden="true"></span>
		<?php endif; ?>
		<?php if ( $asp_pais ) : ?>
			<span class="asp-lugar__pais"><?php echo esc_html( $asp_pais ); ?></span>
		<?php endif; ?>
	</div>
<?php endif;
