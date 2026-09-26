<?php
/**
 * Predicación: reproductor (video o audio) o texto, con orador, evento,
 * pasaje y duración, y las demás predicaciones del mismo evento.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$asp_id     = get_the_ID();
	$asp_tipo   = asp_predicacion_tipo( $asp_id );
	$asp_orador = asp_predicacion_orador( $asp_id );
	$asp_evento = asp_predicacion_evento( $asp_id );
	$asp_pasaje = (string) get_post_meta( $asp_id, 'predicacion_pasaje', true );
	$asp_dur    = asp_duracion_legible( (string) get_post_meta( $asp_id, 'predicacion_duracion', true ) );
	/* El título guardado es el de YouTube, con el orador y la conferencia
	   pegados; se muestra el mismo título limpio que en los listados. Si el
	   orador no es una persona cargada, su nombre sale del título. */
	$asp_partes = asp_predicacion_titulo_partes( $asp_id );
	$asp_otras  = $asp_evento ? array_values( array_filter( asp_predicaciones_de_evento( $asp_evento->ID ), static fn( WP_Post $p ) => $p->ID !== $asp_id ) ) : [];
	$asp_texto  = trim( (string) get_the_content() );
	?>
	<div class="asp-container asp-section">
		<div class="asp-grid-articulo">
			<article class="asp-articulo__cabecera">
				<div class="asp-row">
					<span class="asp-chip asp-chip--accent"><?php echo esc_html( asp_predicacion_tipo_etiqueta( $asp_tipo ) ); ?></span>
					<?php if ( $asp_evento ) : ?>
						<a class="asp-label" href="<?php echo esc_url( get_permalink( $asp_evento ) ); ?>"><?php echo esc_html( get_the_title( $asp_evento ) ); ?></a>
					<?php endif; ?>
				</div>
				<h1 class="asp-articulo__titulo"><?php echo esc_html( $asp_partes['titulo'] ); ?></h1>
				<div class="asp-articulo__meta">
					<?php if ( $asp_orador ) : ?>
						<div><span class="asp-label"><?php esc_html_e( 'Orador', 'asp' ); ?></span><a href="<?php echo esc_url( get_permalink( $asp_orador ) ); ?>"><?php echo esc_html( get_the_title( $asp_orador ) ); ?></a></div>
					<?php elseif ( '' !== $asp_partes['orador'] ) : ?>
						<div><span class="asp-label"><?php esc_html_e( 'Orador', 'asp' ); ?></span><span><?php echo esc_html( $asp_partes['orador'] ); ?></span></div>
					<?php endif; ?>
					<?php if ( $asp_pasaje ) : ?>
						<div><span class="asp-label"><?php esc_html_e( 'Pasaje', 'asp' ); ?></span><span><?php echo esc_html( $asp_pasaje ); ?></span></div>
					<?php endif; ?>
					<div><span class="asp-label"><?php esc_html_e( 'Fecha', 'asp' ); ?></span><span><?php echo esc_html( asp_predicacion_fecha_texto( $asp_id ) ); ?></span></div>
					<?php if ( $asp_dur ) : ?>
						<div><span class="asp-label"><?php esc_html_e( 'Duración', 'asp' ); ?></span><span><?php echo esc_html( $asp_dur ); ?></span></div>
					<?php endif; ?>
				</div>
				<?php echo asp_predicacion_reproductor( $asp_id ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<?php if ( $asp_texto ) : ?>
					<div class="asp-prose"><?php the_content(); ?></div>
				<?php endif; ?>
				<?php /* Sin esto la predicación es un callejón: la mayoría no tiene evento cargado y la columna «Del mismo evento» no aparece. */ ?>
				<a class="asp-link-archivo" href="<?php echo esc_url( (string) get_post_type_archive_link( 'predicacion' ) ); ?>"><?php esc_html_e( 'Todas las predicaciones', 'asp' ); ?></a>
			</article>

			<?php if ( ! empty( $asp_otras ) ) : ?>
				<aside class="asp-aside-caja asp-sticky" aria-label="<?php esc_attr_e( 'Del mismo evento', 'asp' ); ?>">
					<span class="asp-label"><?php esc_html_e( 'Del mismo evento', 'asp' ); ?></span>
					<div>
						<?php foreach ( $asp_otras as $asp_p ) : ?>
							<?php get_template_part( 'parts/predicacion/fila', null, [ 'post_id' => $asp_p->ID, 'sin_evento' => true ] ); ?>
						<?php endforeach; ?>
					</div>
				</aside>
			<?php endif; ?>
		</div>
	</div>
<?php
endwhile;
get_footer();
