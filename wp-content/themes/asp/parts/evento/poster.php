<?php
/**
 * Próximo evento en la grilla de /eventos/, después del destacado: flyer
 * entero en su proporción (o, sin flyer, la fecha en numerales sobre azul
 * noche), fecha, título, ciudad y «Inscribirse» si está abierta; si no, el
 * estado. Args: post_id.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

$asp_id     = (int) ( $args['post_id'] ?? get_the_ID() );
$asp_flyer  = absint( get_post_meta( $asp_id, 'evento_flyer', true ) );
$asp_grande = $asp_flyer ? [] : asp_evento_fecha_grande( $asp_id );
$asp_url    = get_permalink( $asp_id );
$asp_lugar  = implode( ', ', array_filter( [ asp_evento_ciudad( $asp_id ), asp_evento_pais( $asp_id ) ] ) );
?>
<article class="asp-poster">
	<a class="asp-poster__visual" href="<?php echo esc_url( $asp_url ); ?>" tabindex="-1" aria-hidden="true">
		<?php if ( $asp_flyer ) : ?>
			<?php get_template_part( 'parts/evento/flyer', null, [ 'post_id' => $asp_id, 'tamano' => 'asp-flyer-card', 'alt' => '', 'natural' => true, 'sizes' => '(min-width: 1024px) 380px, (min-width: 768px) 33vw, 100vw' ] ); ?>
		<?php elseif ( '' !== ( $asp_grande['dias'] ?? '' ) ) : ?>
			<span class="asp-poster__fecha">
				<span class="asp-poster__dias"><?php echo esc_html( $asp_grande['dias'] ); ?></span>
				<span class="asp-poster__mes"><?php echo esc_html( $asp_grande['mes'] ); ?></span>
			</span>
		<?php endif; ?>
	</a>
	<?php get_template_part( 'parts/evento/fecha', null, [ 'post_id' => $asp_id ] ); ?>
	<h3 class="asp-poster__titulo"><a href="<?php echo esc_url( $asp_url ); ?>"><?php echo esc_html( get_the_title( $asp_id ) ); ?></a></h3>
	<?php if ( '' !== $asp_lugar ) : ?>
		<span class="asp-poster__lugar"><?php echo esc_html( $asp_lugar ); ?></span>
	<?php endif; ?>
	<?php if ( asp_evento_tiene_boton( $asp_id ) ) : ?>
		<a class="asp-poster__accion" href="<?php echo esc_url( asp_evento_url_registro( $asp_id ) ); ?>" target="_blank" rel="noopener"><span class="asp-poster__accion-texto"><?php echo esc_html( asp_evento_boton_texto( $asp_id, false ) ); ?></span><span aria-hidden="true">→</span><?php echo asp_aviso_pestana(); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
	<?php else : ?>
		<?php get_template_part( 'parts/evento/badge', null, [ 'post_id' => $asp_id ] ); ?>
	<?php endif; ?>
</article>
