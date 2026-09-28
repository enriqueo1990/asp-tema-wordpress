<?php
/**
 * Próximo evento en fila, para /eventos/ después del primero: flyer en
 * miniatura (entero sobre la banda tonal) o, sin flyer, la fecha en
 * numerales; al lado estado, iniciativa, título, fecha y ciudad, y el
 * botón de inscripción si está abierta. Args: post_id.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

$asp_id     = (int) ( $args['post_id'] ?? get_the_ID() );
$asp_flyer  = absint( get_post_meta( $asp_id, 'evento_flyer', true ) );
$asp_grande = $asp_flyer ? [] : asp_evento_fecha_grande( $asp_id );
$asp_inic   = asp_evento_iniciativa( $asp_id );
$asp_url    = get_permalink( $asp_id );
?>
<article class="asp-proximo-fila">
	<a class="asp-proximo-fila__visual" href="<?php echo esc_url( $asp_url ); ?>" tabindex="-1" aria-hidden="true">
		<?php if ( $asp_flyer ) : ?>
			<?php get_template_part( 'parts/evento/flyer', null, [ 'post_id' => $asp_id, 'tamano' => 'asp-flyer-card', 'alt' => '', 'sizes' => '(min-width: 1024px) 176px, 112px' ] ); ?>
		<?php elseif ( '' !== ( $asp_grande['dias'] ?? '' ) ) : ?>
			<span class="asp-proximo-fila__fecha">
				<span class="asp-proximo-fila__dias"><?php echo esc_html( $asp_grande['dias'] ); ?></span>
				<span class="asp-proximo-fila__mes"><?php echo esc_html( $asp_grande['mes'] ); ?></span>
			</span>
		<?php endif; ?>
	</a>
	<div class="asp-proximo-fila__cuerpo">
		<div class="asp-row">
			<?php get_template_part( 'parts/evento/badge', null, [ 'post_id' => $asp_id ] ); ?>
			<?php if ( $asp_inic ) : ?>
				<span class="asp-label"><?php echo esc_html( get_the_title( $asp_inic ) ); ?></span>
			<?php endif; ?>
		</div>
		<h3 class="asp-proximo-fila__titulo"><a href="<?php echo esc_url( $asp_url ); ?>"><?php echo esc_html( get_the_title( $asp_id ) ); ?></a></h3>
		<?php get_template_part( 'parts/evento/fecha', null, [ 'post_id' => $asp_id ] ); ?>
		<?php /* Ciudad y país en una línea de texto: con el separador de lugar.php, en la columna angosta el país bajaba solo al renglón de abajo. */ ?>
		<?php $asp_lugar = implode( ', ', array_filter( [ asp_evento_ciudad( $asp_id ), asp_evento_pais( $asp_id ) ] ) ); ?>
		<?php if ( '' !== $asp_lugar ) : ?>
			<span class="asp-proximo-fila__lugar"><?php echo esc_html( $asp_lugar ); ?></span>
		<?php endif; ?>
		<?php if ( asp_evento_tiene_boton( $asp_id ) ) : ?>
			<a class="asp-btn asp-btn--inline" href="<?php echo esc_url( asp_evento_url_registro( $asp_id ) ); ?>" target="_blank" rel="noopener"><?php echo esc_html( asp_evento_boton_texto( $asp_id, false ) ); ?><?php echo asp_aviso_pestana(); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
		<?php endif; ?>
	</div>
</article>
