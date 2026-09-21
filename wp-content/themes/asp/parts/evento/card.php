<?php
/**
 * Tarjeta de evento para listados. Args: post_id, ancha (bool).
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
	get_template_part( 'parts/evento/flyer', null, [ 'post_id' => $asp_id, 'clase' => $asp_ancha ? 'asp-flyer--desktop-wide' : '', 'tamano' => 'asp-flyer-card' ] );
	?>
	<div class="asp-card-evento__cuerpo">
		<div class="asp-row">
			<?php get_template_part( 'parts/evento/badge', null, [ 'post_id' => $asp_id ] ); ?>
			<?php if ( $asp_ancha && $asp_inic ) : ?>
				<span class="asp-label"><?php echo esc_html( get_the_title( $asp_inic ) ); ?></span>
			<?php endif; ?>
		</div>
		<h2 class="asp-card-evento__titulo"><a href="<?php echo esc_url( get_permalink( $asp_id ) ); ?>"><?php echo esc_html( get_the_title( $asp_id ) ); ?></a></h2>
		<?php get_template_part( 'parts/evento/fecha', null, [ 'post_id' => $asp_id ] ); ?>
		<?php get_template_part( 'parts/evento/lugar', null, [ 'post_id' => $asp_id, 'variante' => $asp_ancha ? 'md' : 'inline' ] ); ?>
		<?php if ( $asp_ancha && ( $asp_sede || $asp_dir ) ) : ?>
			<div class="asp-card-evento__sede"><?php echo esc_html( implode( ' · ', array_filter( [ $asp_sede, $asp_dir ] ) ) ); ?></div>
		<?php endif; ?>
		<?php get_template_part( 'parts/evento/cta', null, [ 'post_id' => $asp_id, 'con_plataforma' => $asp_ancha, 'bloque' => ! $asp_ancha ] ); ?>
	</div>
</article>
