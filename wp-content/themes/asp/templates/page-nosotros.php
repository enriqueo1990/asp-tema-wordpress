<?php
/**
 * Template Name: Nosotros
 * Template Post Type: page
 *
 * Una sola columna: hero, qué nos une, consejo pastoral, Afirmaciones y
 * Negaciones (texto verbatim, con índice y anclas) y el próximo evento.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$asp_consejo   = asp_personas_por_rol( 'consejo' );
	$asp_af        = asp_afirmaciones();
	$asp_pdf       = (string) get_theme_mod( 'asp_afirmaciones_pdf', '' );
	$asp_destacado = asp_evento_destacado();
	$asp_mision    = (string) get_theme_mod( 'asp_mision_texto', asp_mision_default() );
	?>
	<section class="asp-hero asp-hero--nosotros">
		<?php echo asp_imagen_mod( 'asp_nosotros_imagen', 'asp-hero__foto', 'full' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<div class="asp-container asp-hero__velo">
			<div class="asp-column asp-stack asp-stack--5">
				<div class="asp-row">
					<span class="asp-label"><?php esc_html_e( 'Nosotros', 'asp' ); ?></span>
					<span class="asp-hero__sep" aria-hidden="true"></span>
					<span class="asp-label"><?php esc_html_e( 'Argentina', 'asp' ); ?></span>
					<span class="asp-hero__sep" aria-hidden="true"></span>
					<span class="asp-label"><?php esc_html_e( 'Estados Unidos', 'asp' ); ?></span>
				</div>
				<h1 class="asp-hero__titulo"><?php the_title(); ?></h1>
				<?php if ( $asp_mision ) : ?><p class="asp-hero__texto"><?php echo esc_html( $asp_mision ); ?></p><?php endif; ?>
			</div>
		</div>
	</section>

	<?php if ( get_the_content() ) : ?>
		<section class="asp-section asp-section--rule">
			<div class="asp-container"><div class="asp-column asp-stack asp-stack--5">
				<span class="asp-label"><?php esc_html_e( 'Qué nos une', 'asp' ); ?></span>
				<div class="asp-prose"><?php the_content(); ?></div>
				<div class="asp-cita">
					<blockquote>«El me amó, y se entregó a sí mismo por mí»</blockquote>
					<span class="asp-label asp-label--muted"><?php esc_html_e( 'Gálatas 2:20', 'asp' ); ?></span>
				</div>
			</div></div>
		</section>
	<?php endif; ?>

	<?php if ( ! empty( $asp_consejo ) ) : ?>
		<section class="asp-section asp-section--rule" aria-label="<?php esc_attr_e( 'Consejo Pastoral', 'asp' ); ?>">
			<div class="asp-container"><div class="asp-column asp-stack asp-stack--5">
				<span class="asp-label"><?php esc_html_e( 'Consejo Pastoral', 'asp' ); ?></span>
				<p class="asp-prose"><?php esc_html_e( 'El Consejo Pastoral da dirección y cuidado al ministerio y las iniciativas del mismo.', 'asp' ); ?></p>
				<div class="asp-grid-consejo">
					<?php foreach ( $asp_consejo as $asp_persona ) : ?>
						<?php get_template_part( 'parts/persona/card', null, [ 'post_id' => $asp_persona->ID ] ); ?>
					<?php endforeach; ?>
				</div>
			</div></div>
		</section>
	<?php endif; ?>

	<section class="asp-section asp-section--rule" id="afirmaciones-y-negaciones" aria-labelledby="titulo-afirmaciones">
		<div class="asp-container"><div class="asp-column asp-stack asp-stack--5">
			<h2 id="titulo-afirmaciones" class="asp-pagina__titulo"><?php esc_html_e( 'Afirmaciones y Negaciones', 'asp' ); ?></h2>
			<div class="asp-caja">
				<span class="asp-label"><?php esc_html_e( 'Sobre este documento', 'asp' ); ?></span>
				<p class="asp-bloque__detalle"><?php echo esc_html( $asp_af['fuente'] ); ?></p>
				<?php if ( $asp_pdf ) : ?>
					<a class="asp-btn asp-btn--secundario asp-btn--inline" href="<?php echo esc_url( $asp_pdf ); ?>"><?php echo asp_icono_descarga(); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php esc_html_e( 'Descargar PDF', 'asp' ); ?></span></a>
				<?php endif; ?>
			</div>
			<div class="asp-cita">
				<blockquote><?php echo esc_html( $asp_af['cita'] ); ?></blockquote>
				<span class="asp-label asp-label--muted"><?php echo esc_html( $asp_af['cita_ref'] ); ?></span>
			</div>
			<div class="asp-stack">
				<?php foreach ( $asp_af['introduccion'] as $asp_parrafo ) : ?>
					<p class="asp-prose"><?php echo esc_html( $asp_parrafo ); ?></p>
				<?php endforeach; ?>
			</div>
			<nav class="asp-caja asp-caja--suave" aria-label="<?php esc_attr_e( 'Índice de artículos', 'asp' ); ?>">
				<span class="asp-label"><?php
					/* translators: %d: cantidad de artículos */
					echo esc_html( sprintf( __( 'Índice · %d artículos', 'asp' ), count( $asp_af['articulos'] ) ) );
				?></span>
				<div class="asp-indice">
					<?php foreach ( $asp_af['articulos'] as $asp_i => $asp_art ) : ?>
						<a href="#<?php echo esc_attr( asp_afirmacion_ancla( $asp_i + 1 ) ); ?>"><?php echo esc_html( asp_romano( $asp_i + 1 ) ); ?></a>
					<?php endforeach; ?>
				</div>
			</nav>
			<div class="asp-articulos-af">
				<?php foreach ( $asp_af['articulos'] as $asp_i => $asp_art ) : ?>
					<article class="asp-articulo-af" id="<?php echo esc_attr( asp_afirmacion_ancla( $asp_i + 1 ) ); ?>">
						<div class="asp-articulo-af__num"><strong><?php echo esc_html( asp_romano( $asp_i + 1 ) ); ?></strong><span class="asp-label"><?php esc_html_e( 'Artículo', 'asp' ); ?></span></div>
						<div class="asp-articulo-af__parte">
							<span class="asp-chip asp-chip--accent"><?php esc_html_e( 'Afirmamos', 'asp' ); ?></span>
							<p><?php echo esc_html( $asp_art['afirmamos'] ); ?></p>
						</div>
						<div class="asp-articulo-af__parte asp-articulo-af__parte--niega">
							<span class="asp-chip"><?php esc_html_e( 'Negamos', 'asp' ); ?></span>
							<p><?php echo esc_html( $asp_art['negamos'] ); ?></p>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		</div></div>
	</section>

	<?php if ( $asp_destacado ) : ?>
		<section class="asp-section asp-section--rule asp-section--surface asp-section--proximo" aria-label="<?php esc_attr_e( 'Próximo evento', 'asp' ); ?>">
			<div class="asp-container"><div class="asp-column asp-stack asp-stack--5">
				<span class="asp-label"><?php esc_html_e( 'Próximo evento', 'asp' ); ?></span>
				<?php get_template_part( 'parts/evento/flyer', null, [ 'post_id' => $asp_destacado->ID, 'clase' => 'asp-flyer--column', 'tamano' => 'asp-flyer-card' ] ); ?>
				<a class="asp-franja__titulo" href="<?php echo esc_url( get_permalink( $asp_destacado ) ); ?>"><?php echo esc_html( get_the_title( $asp_destacado ) ); ?></a>
				<?php get_template_part( 'parts/evento/fecha', null, [ 'post_id' => $asp_destacado->ID ] ); ?>
				<?php get_template_part( 'parts/evento/lugar', null, [ 'post_id' => $asp_destacado->ID ] ); ?>
				<?php get_template_part( 'parts/evento/cta', null, [ 'post_id' => $asp_destacado->ID ] ); ?>
				<a class="asp-cta-link" href="<?php echo esc_url( asp_url_eventos() ); ?>"><?php esc_html_e( 'Ver todos los eventos', 'asp' ); ?></a>
			</div></div>
		</section>
	<?php endif; ?>
<?php
endwhile;
get_footer();
