<?php
/**
 * El próximo evento en /eventos/: banda azul noche a sangre, el flyer
 * grande en su proporción y al lado iniciativa, título, fecha, ciudad y el
 * botón si la inscripción está abierta (si no, el estado). Sin flyer, solo
 * el texto. Args: post_id.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

$asp_id    = (int) ( $args['post_id'] ?? get_the_ID() );
$asp_flyer = absint( get_post_meta( $asp_id, 'evento_flyer', true ) );
$asp_inic  = asp_evento_iniciativa( $asp_id );
$asp_url   = get_permalink( $asp_id );
$asp_lugar = implode( ', ', array_filter( [ asp_evento_ciudad( $asp_id ), asp_evento_pais( $asp_id ) ] ) );
?>
<article class="asp-destacado<?php echo $asp_flyer ? '' : ' asp-destacado--sin-flyer'; ?>">
	<div class="asp-container asp-destacado__inner">
		<?php if ( $asp_flyer ) : ?>
			<a class="asp-destacado__visual" href="<?php echo esc_url( $asp_url ); ?>" tabindex="-1" aria-hidden="true">
				<?php /* Se ve apenas abre la página: sin carga diferida. */ ?>
				<?php get_template_part( 'parts/evento/flyer', null, [ 'post_id' => $asp_id, 'tamano' => 'asp-flyer', 'alt' => '', 'natural' => true, 'loading' => 'eager', 'sizes' => '(min-width: 1024px) 680px, 100vw' ] ); ?>
			</a>
		<?php endif; ?>
		<div class="asp-destacado__cuerpo">
			<?php if ( $asp_inic ) : ?>
				<span class="asp-destacado__rotulo"><?php echo esc_html( get_the_title( $asp_inic ) ); ?></span>
			<?php endif; ?>
			<h3 class="asp-destacado__titulo"><a href="<?php echo esc_url( $asp_url ); ?>"><?php echo esc_html( get_the_title( $asp_id ) ); ?></a></h3>
			<?php get_template_part( 'parts/evento/fecha', null, [ 'post_id' => $asp_id ] ); ?>
			<?php if ( '' !== $asp_lugar ) : ?>
				<span class="asp-destacado__lugar"><?php echo esc_html( $asp_lugar ); ?></span>
			<?php endif; ?>
			<?php if ( asp_evento_tiene_boton( $asp_id ) ) : ?>
				<a class="asp-btn asp-btn--invertido asp-destacado__btn" href="<?php echo esc_url( asp_evento_url_registro( $asp_id ) ); ?>" target="_blank" rel="noopener"><?php echo esc_html( asp_evento_boton_texto( $asp_id, false ) ); ?><?php echo asp_aviso_pestana(); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
			<?php else : ?>
				<?php get_template_part( 'parts/evento/badge', null, [ 'post_id' => $asp_id ] ); ?>
			<?php endif; ?>
		</div>
	</div>
</article>
