<?php
/**
 * Ficha de iniciativa: qué es, para quién, historia, y dos consultas
 * inversas: próximos eventos y ediciones anteriores.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$asp_id       = get_the_ID();
	$asp_bajada   = (string) get_post_meta( $asp_id, 'iniciativa_bajada', true );
	$asp_publico  = (string) get_post_meta( $asp_id, 'iniciativa_publico', true );
	$asp_desc     = (string) get_post_meta( $asp_id, 'iniciativa_descripcion', true );
	$asp_historia = (string) get_post_meta( $asp_id, 'iniciativa_historia', true );
	$asp_proximos = asp_eventos_de_iniciativa( $asp_id, true );
	$asp_pasados  = asp_eventos_de_iniciativa( $asp_id, false );
	?>
	<div class="asp-container asp-section">
		<div class="asp-column asp-section__inner asp-section__inner--loose">
			<header class="asp-stack">
				<span class="asp-label"><?php esc_html_e( 'Iniciativa', 'asp' ); ?></span>
				<h1 class="asp-pagina__titulo"><?php the_title(); ?></h1>
				<?php if ( $asp_bajada ) : ?><p class="asp-prose asp-muted"><?php echo esc_html( $asp_bajada ); ?></p><?php endif; ?>
			</header>
			<?php if ( $asp_publico ) : ?>
				<div class="asp-bloque"><span class="asp-label"><?php esc_html_e( 'Para quién', 'asp' ); ?></span><span class="asp-bloque__valor"><?php echo esc_html( $asp_publico ); ?></span></div>
			<?php endif; ?>
			<?php if ( $asp_desc ) : ?>
				<div class="asp-bloque"><span class="asp-label"><?php esc_html_e( 'Qué es', 'asp' ); ?></span><div class="asp-prose"><?php echo wp_kses_post( wpautop( $asp_desc ) ); ?></div></div>
			<?php endif; ?>
			<?php if ( $asp_historia ) : ?>
				<div class="asp-bloque"><span class="asp-label"><?php esc_html_e( 'Historia', 'asp' ); ?></span><div class="asp-prose"><?php echo wp_kses_post( wpautop( $asp_historia ) ); ?></div></div>
			<?php endif; ?>
			<?php if ( ! empty( $asp_proximos ) ) : ?>
				<section class="asp-stack asp-stack--5" aria-label="<?php esc_attr_e( 'Próximos eventos', 'asp' ); ?>">
					<span class="asp-label"><?php esc_html_e( 'Próximos', 'asp' ); ?></span>
					<?php foreach ( $asp_proximos as $asp_post ) : ?>
						<?php get_template_part( 'parts/evento/card', null, [ 'post_id' => $asp_post->ID ] ); ?>
					<?php endforeach; ?>
				</section>
			<?php endif; ?>
			<?php if ( ! empty( $asp_pasados ) ) : ?>
				<section class="asp-stack" aria-label="<?php esc_attr_e( 'Ediciones anteriores', 'asp' ); ?>">
					<span class="asp-label"><?php esc_html_e( 'Ediciones anteriores', 'asp' ); ?></span>
					<div>
						<?php foreach ( $asp_pasados as $asp_post ) : ?>
							<?php get_template_part( 'parts/evento/archivo-item', null, [ 'post_id' => $asp_post->ID ] ); ?>
						<?php endforeach; ?>
					</div>
				</section>
			<?php endif; ?>
		</div>
	</div>
<?php
endwhile;
get_footer();
