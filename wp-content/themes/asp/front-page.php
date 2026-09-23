<?php
/**
 * Inicio. Cada bloque tiene su propia forma: el evento manda y va de hero,
 * quiénes somos es texto, las iniciativas son cajas, los eventos son fichas
 * con flyer, las predicaciones una banda oscura y los artículos una portada
 * editorial. Todo consulta los mismos CPT y una sección sin contenido no se
 * imprime.
 *
 * Rehecho el 23-9-2026: antes eventos, predicaciones y artículos eran tres
 * listas verticales seguidas, con el mismo ritmo tres veces. "Lo que creemos"
 * dejó de ser sección y es un enlace dentro de quiénes somos.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

get_header();

$asp_mision      = (string) get_theme_mod( 'asp_mision_texto', asp_mision_default() );
$asp_nosotros    = asp_url_pagina_plantilla( 'templates/page-nosotros.php' );
$asp_contacto    = asp_url_pagina_plantilla( 'templates/page-contacto.php' );
$asp_iniciativas = get_posts( [ 'post_type' => 'iniciativa', 'post_status' => 'publish', 'posts_per_page' => 4, 'orderby' => 'menu_order title', 'order' => 'ASC' ] );
$asp_destacado   = asp_evento_destacado();
$asp_hay_evento  = null !== $asp_destacado;
$asp_af          = asp_afirmaciones();

/* El hero ya le dio una pantalla entera al evento destacado: no se repite
   abajo. Sin más próximos, la sección muestra los últimos realizados y lo
   dice en el título. */
$asp_proximos = array_values(
	array_filter(
		asp_eventos_proximos( 5 )->posts,
		static fn( WP_Post $p ): bool => ! $asp_hay_evento || $p->ID !== $asp_destacado->ID
	)
);
$asp_proximos  = array_slice( $asp_proximos, 0, 2 );
$asp_pasados   = asp_eventos_pasados( null, 2 )->posts;
$asp_eventos   = $asp_proximos ?: $asp_pasados;
$asp_son_prox  = ! empty( $asp_proximos );

$asp_predic    = asp_predicaciones( [], 4 );
$asp_articulos = get_posts( [ 'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 3 ] );
$asp_sumarse   = (string) get_theme_mod( 'asp_sumarse_texto', '' );
$asp_fotos     = array_values(
	array_filter(
		[
			asp_imagen_mod( 'asp_galeria_1', '', 'full' ),
			asp_imagen_mod( 'asp_galeria_2', '', 'medium_large' ),
			asp_imagen_mod( 'asp_galeria_3', '', 'medium_large' ),
			asp_imagen_mod( 'asp_galeria_4', '', 'medium_large' ) ?: asp_imagen_mod( 'asp_mision_imagen', '', 'medium_large' ),
		]
	)
);

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
				<div class="asp-row asp-row--enlaces">
					<a class="asp-cta-link" href="<?php echo esc_url( $asp_nosotros ); ?>"><?php esc_html_e( 'Quiénes somos', 'asp' ); ?></a>
					<?php if ( ! empty( $asp_af['articulos'] ) ) : ?>
						<a class="asp-cta-link" href="<?php echo esc_url( $asp_nosotros . '#afirmaciones-y-negaciones' ); ?>"><?php
							/* translators: %d: cantidad de artículos del documento */
							echo esc_html( sprintf( __( 'Lo que creemos · %d artículos', 'asp' ), count( $asp_af['articulos'] ) ) );
						?></a>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
	</section>
<?php endif; ?>

<?php if ( ! empty( $asp_iniciativas ) ) : ?>
	<section class="asp-container asp-section--rule asp-section--tight" aria-labelledby="home-iniciativas">
		<div class="asp-editorial__cab asp-cab-suelto">
			<h2 id="home-iniciativas" class="asp-label"><?php esc_html_e( 'Iniciativas', 'asp' ); ?></h2>
			<a class="asp-cta-link" href="<?php echo esc_url( (string) get_post_type_archive_link( 'iniciativa' ) ); ?>"><?php esc_html_e( 'Todas', 'asp' ); ?></a>
		</div>
		<div class="asp-grilla-iniciativas">
			<?php foreach ( $asp_iniciativas as $asp_i => $asp_post ) : ?>
				<?php get_template_part( 'parts/iniciativa/caja', null, [ 'post_id' => $asp_post->ID, 'numero' => $asp_i + 1 ] ); ?>
			<?php endforeach; ?>
		</div>
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
	<section class="asp-container asp-section--tight" aria-labelledby="home-eventos">
		<div class="asp-editorial__cab asp-cab-suelto">
			<h2 id="home-eventos" class="asp-label"><?php echo esc_html( $asp_son_prox ? __( 'Próximos eventos', 'asp' ) : __( 'Últimos eventos', 'asp' ) ); ?></h2>
			<a class="asp-cta-link" href="<?php echo esc_url( asp_url_eventos() ); ?>"><?php echo esc_html( $asp_son_prox ? __( 'Todos los eventos', 'asp' ) : __( 'Ver el archivo', 'asp' ) ); ?></a>
		</div>
		<div class="asp-grilla-eventos">
			<?php foreach ( $asp_eventos as $asp_post ) : ?>
				<?php get_template_part( 'parts/evento/card', null, [ 'post_id' => $asp_post->ID ] ); ?>
			<?php endforeach; ?>
		</div>
	</section>
<?php endif; ?>

<?php if ( ! empty( $asp_predic ) ) : ?>
	<div class="asp-banda-oscura">
		<section class="asp-container asp-section--tight" aria-labelledby="home-predicaciones">
			<div class="asp-editorial__cab asp-cab-suelto">
				<h2 id="home-predicaciones" class="asp-label"><?php esc_html_e( 'Predicaciones', 'asp' ); ?></h2>
				<a class="asp-cta-link" href="<?php echo esc_url( (string) get_post_type_archive_link( 'predicacion' ) ); ?>"><?php esc_html_e( 'Todas las predicaciones', 'asp' ); ?></a>
			</div>
			<div class="asp-grilla-predicaciones">
				<?php foreach ( $asp_predic as $asp_post ) : ?>
					<?php get_template_part( 'parts/predicacion/tarjeta', null, [ 'post_id' => $asp_post->ID ] ); ?>
				<?php endforeach; ?>
			</div>
		</section>
	</div>
<?php endif; ?>

<?php if ( ! empty( $asp_articulos ) ) : ?>
	<section class="asp-container asp-section--tight" aria-labelledby="home-articulos">
		<div class="asp-editorial__cab asp-cab-suelto">
			<h2 id="home-articulos" class="asp-label"><?php esc_html_e( 'Artículos', 'asp' ); ?></h2>
			<a class="asp-cta-link" href="<?php echo esc_url( asp_url_recursos() ); ?>"><?php esc_html_e( 'Todos los recursos', 'asp' ); ?></a>
		</div>
		<div class="asp-portada-articulos">
			<?php get_template_part( 'parts/articulo/destacado', null, [ 'post_id' => $asp_articulos[0]->ID ] ); ?>
			<?php if ( count( $asp_articulos ) > 1 ) : ?>
				<div class="asp-portada-articulos__resto">
					<?php foreach ( array_slice( $asp_articulos, 1 ) as $asp_post ) : ?>
						<?php get_template_part( 'parts/articulo/card', null, [ 'post_id' => $asp_post->ID ] ); ?>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</section>
<?php endif; ?>

<?php /* TODO: el ministerio tiene que escribir la invitación real en
	   Personalizar → Ante Su Palabra → "Sumarse". Mientras esté vacío, la
	   sección es solo el enlace al formulario, que sí existe. No inventar
	   acá qué se le ofrece a una iglesia que quiere sumarse. */ ?>
<?php if ( $asp_contacto || $asp_sumarse ) : ?>
	<div class="asp-banda">
	<section class="asp-container asp-editorial asp-section--rule" aria-labelledby="home-sumarse">
		<h2 id="home-sumarse" class="asp-label"><?php esc_html_e( 'Sumarse', 'asp' ); ?></h2>
		<div class="asp-editorial__cuerpo">
			<?php if ( $asp_sumarse ) : ?>
				<p class="asp-editorial__texto"><?php echo esc_html( $asp_sumarse ); ?></p>
			<?php endif; ?>
			<?php if ( $asp_contacto ) : ?>
				<a class="asp-cta-link" href="<?php echo esc_url( $asp_contacto ); ?>"><?php esc_html_e( 'Escribir al ministerio', 'asp' ); ?></a>
			<?php endif; ?>
		</div>
	</section>
	</div>
<?php endif; ?>
<?php
get_footer();
