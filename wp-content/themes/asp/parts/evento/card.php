<?php
/**
 * Tarjeta de evento para listados. Args: post_id, ancha (bool), loading
 * ('lazy' por defecto; 'eager' para la primera que se ve al abrir), nivel
 * ('h2' o 'h3' para el título), sede (bool, por defecto true).
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

$asp_id     = (int) ( $args['post_id'] ?? get_the_ID() );
$asp_ancha  = ! empty( $args['ancha'] );
$asp_inic   = asp_evento_iniciativa( $asp_id );
$asp_sede   = (string) get_post_meta( $asp_id, 'evento_sede_nombre', true );
$asp_dir    = (string) get_post_meta( $asp_id, 'evento_sede_direccion', true );
$asp_flyer  = absint( get_post_meta( $asp_id, 'evento_flyer', true ) );
?>
<article class="asp-card-evento<?php echo $asp_ancha ? ' asp-card-evento--ancha' : ''; ?><?php echo $asp_flyer ? '' : ' asp-card-evento--sin-flyer'; ?>">
	<?php
	get_template_part( 'parts/evento/flyer', null, [ 'post_id' => $asp_id, 'clase' => $asp_ancha ? 'asp-flyer--desktop-wide' : '', 'tamano' => 'asp-flyer-card', 'loading' => (string) ( $args['loading'] ?? 'lazy' ) ] );
	?>
	<div class="asp-card-evento__cuerpo">
		<div class="asp-row">
			<?php get_template_part( 'parts/evento/badge', null, [ 'post_id' => $asp_id ] ); ?>
			<?php if ( $asp_ancha && $asp_inic ) : ?>
				<span class="asp-label"><?php echo esc_html( get_the_title( $asp_inic ) ); ?></span>
			<?php endif; ?>
		</div>
		<?php $asp_nivel = 'h3' === ( $args['nivel'] ?? '' ) ? 'h3' : 'h2'; ?>
		<<?php echo $asp_nivel; // phpcs:ignore WordPress.Security.EscapeOutput ?> class="asp-card-evento__titulo"><a href="<?php echo esc_url( get_permalink( $asp_id ) ); ?>"><?php echo esc_html( get_the_title( $asp_id ) ); ?></a></<?php echo $asp_nivel; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
		<?php get_template_part( 'parts/evento/fecha', null, [ 'post_id' => $asp_id ] ); ?>
		<?php get_template_part( 'parts/evento/lugar', null, [ 'post_id' => $asp_id, 'variante' => $asp_ancha ? 'md' : 'inline' ] ); ?>
		<?php if ( $asp_ancha && ( $args['sede'] ?? true ) && ( $asp_sede || $asp_dir ) ) : ?>
			<div class="asp-card-evento__sede"><?php echo esc_html( implode( ' · ', array_filter( [ $asp_sede, $asp_dir ] ) ) ); ?></div>
		<?php endif; ?>
		<?php get_template_part( 'parts/evento/cta', null, [ 'post_id' => $asp_id, 'con_plataforma' => $asp_ancha, 'bloque' => ! $asp_ancha ] ); ?>
	</div>
</article>
