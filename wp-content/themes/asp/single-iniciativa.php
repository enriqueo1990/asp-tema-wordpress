<?php
/**
 * Ficha de iniciativa: qué es, para quién, historia, y dos consultas
 * inversas: próximos eventos y ediciones anteriores. Las ediciones van en
 * la misma grilla de flyers que el archivo de /eventos/, a todo el ancho.
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
	$asp_resumen  = asp_iniciativa_ediciones( $asp_id );
	?>
	<div class="asp-container asp-section">
		<div class="asp-column asp-section__inner asp-section__inner--loose">
			<header class="asp-stack">
				<span class="asp-label"><?php esc_html_e( 'Iniciativa', 'asp' ); ?></span>
				<h1 class="asp-pagina__titulo"><?php the_title(); ?></h1>
				<?php if ( $asp_bajada ) : ?><p class="asp-prose asp-muted"><?php echo esc_html( $asp_bajada ); ?></p><?php endif; ?>
				<?php if ( $asp_resumen['ediciones'] > 1 && $asp_resumen['desde'] ) : ?>
					<p class="asp-iniciativa-fila__datos"><span><?php
						/* translators: 1: cantidad de ediciones, 2: año de la primera */
						echo esc_html( sprintf( __( '%1$d ediciones desde %2$d', 'asp' ), $asp_resumen['ediciones'], $asp_resumen['desde'] ) );
					?></span></p>
				<?php endif; ?>
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
		</div>
	</div>

	<?php if ( ! empty( $asp_proximos ) ) : ?>
		<section class="asp-container asp-eventos__proximos" aria-labelledby="iniciativa-proximos">
			<h2 class="asp-label" id="iniciativa-proximos"><?php esc_html_e( 'Próximos', 'asp' ); ?></h2>
			<?php foreach ( $asp_proximos as $asp_post ) : ?>
				<?php get_template_part( 'parts/evento/card', null, [ 'post_id' => $asp_post->ID, 'ancha' => true ] ); ?>
			<?php endforeach; ?>
		</section>
	<?php endif; ?>

	<?php if ( ! empty( $asp_pasados ) ) : ?>
		<section class="asp-container asp-section--rule asp-eventos__archivo asp-iniciativa__ediciones" aria-labelledby="iniciativa-ediciones">
			<h2 class="asp-anio__num asp-anio__num--grilla" id="iniciativa-ediciones">
				<span><?php esc_html_e( 'Ediciones anteriores', 'asp' ); ?></span>
				<span class="asp-anio__cuenta"><?php echo esc_html( (string) count( $asp_pasados ) ); ?></span>
			</h2>
			<div class="asp-grilla-eventos">
				<?php foreach ( $asp_pasados as $asp_post ) : ?>
					<?php get_template_part( 'parts/evento/tarjeta-archivo', null, [ 'post_id' => $asp_post->ID, 'con_anio' => true, 'con_iniciativa' => false ] ); ?>
				<?php endforeach; ?>
			</div>
		</section>
	<?php endif; ?>
<?php
endwhile;
get_footer();
