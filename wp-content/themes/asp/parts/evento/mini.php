<?php
/**
 * Evento en tamaño chico, para el inicio. Args: post_id.
 *
 * El flyer entra contenido en una banda tonal, sin recortarse (regla 5).
 * Sin flyer, la fecha ocupa ese lugar en grande. Toda la tarjeta lleva a la
 * ficha: el botón de inscripción vive ahí y en el hero, no se repite acá.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

$asp_id     = (int) ( $args['post_id'] ?? get_the_ID() );
$asp_flyer  = absint( get_post_meta( $asp_id, 'evento_flyer', true ) );
$asp_fecha  = asp_evento_fecha_display( $asp_id );
$asp_ciudad = asp_evento_ciudad( $asp_id );
$asp_pais   = asp_evento_pais( $asp_id );
$asp_lugar  = implode( ', ', array_filter( [ $asp_ciudad, $asp_pais ] ) );
$asp_img    = $asp_flyer ? wp_get_attachment_image( $asp_flyer, 'asp-flyer-card', false, [ 'loading' => 'lazy', 'alt' => '' ] ) : '';
?>
<a class="asp-evento-mini" href="<?php echo esc_url( get_permalink( $asp_id ) ); ?>">
	<span class="asp-evento-mini__visual<?php echo $asp_img ? '' : ' asp-evento-mini__visual--fecha'; ?>" aria-hidden="true">
		<?php if ( $asp_img ) : ?>
			<?php echo $asp_img; // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<?php elseif ( ! empty( $asp_fecha['dias'] ) ) : ?>
			<span class="asp-evento-mini__dias"><?php echo esc_html( str_replace( ' · ', '–', $asp_fecha['dias'] ) ); ?></span>
			<span class="asp-evento-mini__mes"><?php echo esc_html( $asp_fecha['mes'] ); ?></span>
		<?php endif; ?>
	</span>
	<span class="asp-evento-mini__texto">
		<?php get_template_part( 'parts/evento/badge', null, [ 'post_id' => $asp_id ] ); ?>
		<span class="asp-evento-mini__titulo"><?php echo esc_html( get_the_title( $asp_id ) ); ?></span>
		<span class="asp-evento-mini__dato"><?php echo esc_html( asp_evento_fecha_texto( $asp_id ) ); ?></span>
		<?php if ( $asp_lugar ) : ?>
			<span class="asp-evento-mini__dato"><?php echo esc_html( $asp_lugar ); ?></span>
		<?php endif; ?>
	</span>
</a>
