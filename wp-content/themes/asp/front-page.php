<?php
/**
 * Inicio, dirección editorial: el evento como hero sobre fotografía a
 * sangre; quiénes somos; iniciativas como lista con numerales; fotografía
 * con pie; eventos; recursos cuando haya. Todo consulta el mismo CPT. Una
 * sección sin contenido no se imprime.
 *
 * El consejo pastoral se sacó de la home el 21-9-2026: vive en Nosotros.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

get_header();

$asp_mision      = (string) get_theme_mod( 'asp_mision_texto', asp_mision_default() );
$asp_nosotros    = asp_url_pagina_plantilla( 'templates/page-nosotros.php' );
$asp_iniciativas = get_posts( [ 'post_type' => 'iniciativa', 'post_status' => 'publish', 'posts_per_page' => 6, 'orderby' => 'menu_order title', 'order' => 'ASC' ] );
$asp_proximos    = asp_eventos_proximos( 4 )->posts;
$asp_pasados     = asp_eventos_pasados( null, 4 )->posts;
$asp_eventos     = array_slice( array_merge( $asp_proximos, $asp_pasados ), 0, 5 );
$asp_fotos       = array_values(
	array_filter(
		[
			asp_imagen_mod( 'asp_galeria_1', '', 'full' ),
			asp_imagen_mod( 'asp_galeria_2', '', 'medium_large' ),
			asp_imagen_mod( 'asp_galeria_3', '', 'medium_large' ),
			asp_imagen_mod( 'asp_galeria_4', '', 'medium_large' ) ?: asp_imagen_mod( 'asp_mision_imagen', '', 'medium_large' ),
		]
	)
);
$asp_articulos   = get_posts( [ 'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 3 ] );
$asp_hay_evento  = null !== asp_evento_destacado();

get_template_part( 'parts/evento/hero' );
?>

<?php if ( $asp_mision ) : ?>
	<section class="asp-container asp-editorial" aria-labelledby="home-somos">
		<h2 id="home-somos" class="asp-label"><?php esc_html_e( 'Quiénes somos', 'asp' ); ?></h2>
		<div class="asp-editorial__cuerpo">
			<p class="asp-editorial__texto"><?php echo esc_html( $asp_mision ); ?></p>
			<?php if ( $asp_hay_evento ) : ?>
				<p class="asp-editorial__cita">«Pero a este miraré: al que es humilde y contrito de espíritu, y que tiembla ante Mi palabra» <span class="asp-label"><?php esc_html_e( 'Isaías 66:2', 'asp' ); ?></span></p>
			<?php endif; ?>
			<?php if ( $asp_nosotros ) : ?>
				<a class="asp-cta-link" href="<?php echo esc_url( $asp_nosotros ); ?>"><?php esc_html_e( 'Quiénes somos', 'asp' ); ?></a>
			<?php endif; ?>
		</div>
	</section>
<?php endif; ?>

<?php if ( ! empty( $asp_iniciativas ) ) : ?>
	<section class="asp-container asp-editorial asp-section--rule" aria-labelledby="home-iniciativas">
		<div class="asp-editorial__cab">
			<h2 id="home-iniciativas" class="asp-label"><?php esc_html_e( 'Iniciativas', 'asp' ); ?></h2>
			<a class="asp-cta-link" href="<?php echo esc_url( (string) get_post_type_archive_link( 'iniciativa' ) ); ?>"><?php esc_html_e( 'Todas', 'asp' ); ?></a>
		</div>
		<ol class="asp-lista-numerada">
			<?php foreach ( $asp_iniciativas as $asp_i => $asp_post ) :
				$asp_bajada = (string) get_post_meta( $asp_post->ID, 'iniciativa_bajada', true );
				?>
				<li>
					<a class="asp-lista-numerada__item" href="<?php echo esc_url( get_permalink( $asp_post ) ); ?>">
						<span class="asp-lista-numerada__num" aria-hidden="true"><?php echo esc_html( asp_romano( $asp_i + 1 ) ); ?></span>
						<span class="asp-lista-numerada__cuerpo">
							<span class="asp-lista-numerada__titulo"><?php echo esc_html( get_the_title( $asp_post ) ); ?></span>
							<?php if ( $asp_bajada ) : ?><span class="asp-lista-numerada__bajada"><?php echo esc_html( $asp_bajada ); ?></span><?php endif; ?>
						</span>
					</a>
				</li>
			<?php endforeach; ?>
		</ol>
	</section>
<?php endif; ?>

<?php if ( ! empty( $asp_fotos ) ) : ?>
	<section class="asp-fotos" aria-label="<?php esc_attr_e( 'Fotografías', 'asp' ); ?>">
		<figure class="asp-fotos__principal"><?php echo $asp_fotos[0]; // phpcs:ignore WordPress.Security.EscapeOutput ?></figure>
		<?php if ( count( $asp_fotos ) > 1 ) : ?>
			<div class="asp-container asp-fotos__tira">
				<?php foreach ( array_slice( $asp_fotos, 1 ) as $asp_img ) : ?>
					<figure><?php echo $asp_img; // phpcs:ignore WordPress.Security.EscapeOutput ?></figure>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</section>
<?php endif; ?>

<?php if ( ! empty( $asp_eventos ) ) : ?>
	<section class="asp-container asp-editorial asp-section--rule" aria-labelledby="home-eventos">
		<div class="asp-editorial__cab">
			<h2 id="home-eventos" class="asp-label"><?php esc_html_e( 'Eventos', 'asp' ); ?></h2>
			<a class="asp-cta-link" href="<?php echo esc_url( asp_url_eventos() ); ?>"><?php esc_html_e( 'Todos los eventos', 'asp' ); ?></a>
		</div>
		<div class="asp-editorial__cuerpo asp-editorial__cuerpo--ancho">
			<?php foreach ( $asp_eventos as $asp_post ) : ?>
				<?php get_template_part( 'parts/evento/fila', null, [ 'post_id' => $asp_post->ID ] ); ?>
			<?php endforeach; ?>
		</div>
	</section>
<?php endif; ?>

<?php if ( ! empty( $asp_articulos ) ) : ?>
	<section class="asp-container asp-editorial asp-section--rule" aria-labelledby="home-recursos">
		<div class="asp-editorial__cab">
			<h2 id="home-recursos" class="asp-label"><?php esc_html_e( 'Recursos', 'asp' ); ?></h2>
			<a class="asp-cta-link" href="<?php echo esc_url( asp_url_recursos() ); ?>"><?php esc_html_e( 'Todos los recursos', 'asp' ); ?></a>
		</div>
		<div class="asp-editorial__cuerpo asp-editorial__cuerpo--ancho">
			<?php foreach ( $asp_articulos as $asp_post ) : ?>
				<?php get_template_part( 'parts/articulo/card', null, [ 'post_id' => $asp_post->ID ] ); ?>
			<?php endforeach; ?>
		</div>
	</section>
<?php endif; ?>
<?php
get_footer();
