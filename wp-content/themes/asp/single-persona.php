<?php
/**
 * Ficha de persona: bio, iglesia, ciudad y lo que corresponda por rol.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$asp_id        = get_the_ID();
	$asp_linea     = asp_persona_cargo_iglesia( $asp_id );
	$asp_ciudad    = (string) get_post_meta( $asp_id, 'persona_ciudad', true );
	$asp_pais      = (string) get_post_meta( $asp_id, 'persona_pais', true );
	$asp_bio       = (string) get_post_meta( $asp_id, 'persona_bio', true );
	$asp_eventos   = asp_eventos_de_orador( $asp_id );
	$asp_articulos = asp_articulos_de_persona( $asp_id );
	$asp_predic    = asp_predicaciones_de_persona( $asp_id );
	?>
	<div class="asp-container asp-section">
		<div class="asp-column asp-section__inner asp-section__inner--loose">
			<header class="asp-stack">
				<?php echo asp_persona_foto( $asp_id, 'asp-persona-ficha__foto' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<h1 class="asp-pagina__titulo"><?php the_title(); ?></h1>
				<?php if ( $asp_linea ) : ?><p class="asp-persona-box__iglesia"><?php echo esc_html( $asp_linea ); ?></p><?php endif; ?>
				<?php if ( $asp_ciudad || $asp_pais ) : ?>
					<div class="asp-lugar">
						<?php if ( $asp_ciudad ) : ?><?php echo asp_icono_ubicacion(); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php echo esc_html( $asp_ciudad ); ?></span><?php endif; ?>
						<?php if ( $asp_ciudad && $asp_pais ) : ?><span class="asp-lugar__sep" aria-hidden="true"></span><?php endif; ?>
						<?php if ( $asp_pais ) : ?><span class="asp-lugar__pais"><?php echo esc_html( $asp_pais ); ?></span><?php endif; ?>
					</div>
				<?php endif; ?>
			</header>
			<?php if ( $asp_bio ) : ?>
				<div class="asp-prose"><?php echo wp_kses_post( wpautop( $asp_bio ) ); ?></div>
			<?php endif; ?>
			<?php if ( ! empty( $asp_predic ) ) : ?>
				<section class="asp-stack" aria-label="<?php esc_attr_e( 'Predicaciones', 'asp' ); ?>">
					<span class="asp-label"><?php esc_html_e( 'Predicaciones', 'asp' ); ?></span>
					<div>
						<?php foreach ( $asp_predic as $asp_p ) : ?>
							<?php get_template_part( 'parts/predicacion/fila', null, [ 'post_id' => $asp_p->ID ] ); ?>
						<?php endforeach; ?>
					</div>
				</section>
			<?php endif; ?>
			<?php if ( ! empty( $asp_eventos ) ) : ?>
				<section class="asp-stack" aria-label="<?php esc_attr_e( 'Eventos', 'asp' ); ?>">
					<span class="asp-label"><?php esc_html_e( 'Eventos donde participó', 'asp' ); ?></span>
					<div>
						<?php foreach ( $asp_eventos as $asp_post ) : ?>
							<?php get_template_part( 'parts/evento/archivo-item', null, [ 'post_id' => $asp_post->ID ] ); ?>
						<?php endforeach; ?>
					</div>
				</section>
			<?php endif; ?>
			<?php if ( ! empty( $asp_articulos ) ) : ?>
				<section class="asp-stack" aria-label="<?php esc_attr_e( 'Artículos', 'asp' ); ?>">
					<span class="asp-label"><?php esc_html_e( 'Artículos', 'asp' ); ?></span>
					<div>
						<?php foreach ( $asp_articulos as $asp_post ) : ?>
							<?php get_template_part( 'parts/articulo/card', null, [ 'post_id' => $asp_post->ID ] ); ?>
						<?php endforeach; ?>
					</div>
				</section>
			<?php endif; ?>
		</div>
	</div>
<?php
endwhile;
get_footer();
