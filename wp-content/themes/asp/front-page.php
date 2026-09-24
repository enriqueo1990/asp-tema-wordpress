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

/* Los tres próximos, incluido el del hero: la sección se llama "Próximos
   eventos" y sin él le faltaba el más cercano. Pero si el del hero es el
   único próximo, la lista sería una copia exacta del hero: en ese caso la
   sección muestra los últimos realizados, que es información nueva. */
$asp_proximos = array_slice( asp_eventos_proximos( 3 )->posts, 0, 3 );
if ( 1 === count( $asp_proximos ) && $asp_hay_evento && $asp_proximos[0]->ID === $asp_destacado->ID ) {
	$asp_proximos = [];
}
$asp_pasados   = asp_eventos_pasados( null, 3 )->posts;
$asp_eventos   = $asp_proximos ?: $asp_pasados;
$asp_son_prox  = ! empty( $asp_proximos );

$asp_predic    = asp_predicaciones( [], 4 );
$asp_articulos = asp_articulos_portada( 3 );
$asp_sumarse   = (string) get_theme_mod( 'asp_sumarse_texto', '' );
$asp_testimonio = [
	'texto'   => (string) get_theme_mod( 'asp_testimonio_texto', '' ),
	'nombre'  => (string) get_theme_mod( 'asp_testimonio_nombre', '' ),
	'iglesia' => (string) get_theme_mod( 'asp_testimonio_iglesia', '' ),
];
/* La galería cierra la página: su primera foto es el fondo de "Sumate". La
   tira de tres fotos que iba antes se sacó el 23-9-2026: pegada a las
   tarjetas de artículos se leía como una fila de artículos rota. */
$asp_foto_cierre = asp_imagen_mod( 'asp_galeria_1', 'asp-cierre__foto', 'full' );

get_template_part( 'parts/evento/hero' );
?>

<?php if ( $asp_mision ) : ?>
	<section class="asp-container asp-home-bloque" aria-labelledby="home-somos">
		<div class="asp-editorial__cab asp-cab-suelto">
			<h2 id="home-somos" class="asp-seccion__titulo"><?php esc_html_e( 'Quiénes somos', 'asp' ); ?></h2>
		</div>
		<div class="asp-editorial__cuerpo asp-somos__cuerpo">
			<p class="asp-editorial__texto"><?php echo esc_html( $asp_mision ); ?></p>
			<?php if ( $asp_hay_evento ) : ?>
				<p class="asp-editorial__cita">«Pero a este miraré: al que es humilde y contrito de espíritu, y que tiembla ante Mi palabra» <span class="asp-label"><?php esc_html_e( 'Isaías 66:2', 'asp' ); ?></span></p>
			<?php endif; ?>
			<?php if ( $asp_nosotros ) : ?>
				<div class="asp-row asp-row--enlaces">
					<a class="asp-cta-link" href="<?php echo esc_url( $asp_nosotros ); ?>"><?php esc_html_e( 'Conocé el ministerio', 'asp' ); ?></a>
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
	<section class="asp-container asp-home-bloque" aria-labelledby="home-iniciativas">
		<div class="asp-editorial__cab asp-cab-suelto">
			<h2 id="home-iniciativas" class="asp-seccion__titulo"><?php esc_html_e( 'Iniciativas', 'asp' ); ?></h2>
			<a class="asp-cta-link" href="<?php echo esc_url( (string) get_post_type_archive_link( 'iniciativa' ) ); ?>"><?php esc_html_e( 'Todas las iniciativas', 'asp' ); ?></a>
		</div>
		<div class="asp-grilla-tiles">
			<?php foreach ( $asp_iniciativas as $asp_i => $asp_post ) : ?>
				<?php get_template_part( 'parts/iniciativa/caja', null, [ 'post_id' => $asp_post->ID, 'numero' => $asp_i + 1 ] ); ?>
			<?php endforeach; ?>
		</div>
	</section>
<?php endif; ?>

<?php if ( ! empty( $asp_eventos ) ) : ?>
	<section class="asp-container asp-home-bloque" aria-labelledby="home-eventos">
		<div class="asp-editorial__cab asp-cab-suelto">
			<h2 id="home-eventos" class="asp-seccion__titulo"><?php echo esc_html( $asp_son_prox ? __( 'Próximos eventos', 'asp' ) : __( 'Últimos eventos', 'asp' ) ); ?></h2>
			<a class="asp-cta-link" href="<?php echo esc_url( asp_url_eventos() ); ?>"><?php echo esc_html( $asp_son_prox ? __( 'Todos los eventos', 'asp' ) : __( 'Ver el archivo', 'asp' ) ); ?></a>
		</div>
		<div class="asp-grilla-eventos-mini">
			<?php foreach ( $asp_eventos as $asp_post ) : ?>
				<?php get_template_part( 'parts/evento/mini', null, [ 'post_id' => $asp_post->ID ] ); ?>
			<?php endforeach; ?>
		</div>
	</section>
<?php endif; ?>

<?php if ( ! empty( $asp_predic ) ) : ?>
	<div class="asp-banda-oscura">
		<section class="asp-container asp-home-bloque" aria-labelledby="home-predicaciones">
			<div class="asp-editorial__cab asp-cab-suelto">
				<h2 id="home-predicaciones" class="asp-seccion__titulo"><?php esc_html_e( 'Predicaciones', 'asp' ); ?></h2>
				<a class="asp-cta-link" href="<?php echo esc_url( (string) get_post_type_archive_link( 'predicacion' ) ); ?>"><?php esc_html_e( 'Todas las predicaciones', 'asp' ); ?></a>
			</div>
			<?php asp_buscador_predicaciones( 'banda' ); ?>
			<div class="asp-grilla-predicaciones">
				<?php foreach ( $asp_predic as $asp_post ) : ?>
					<?php get_template_part( 'parts/predicacion/tarjeta', null, [ 'post_id' => $asp_post->ID ] ); ?>
				<?php endforeach; ?>
			</div>
		</section>
	</div>
<?php endif; ?>

<?php /* TODO: la cita la aporta el ministerio (Personalizar → Ante Su Palabra →
	   "Testimonio"): palabras reales de un pastor que participó, con su nombre.
	   Sin texto y nombre cargados, el bloque no existe. No inventar una. */ ?>
<?php if ( $asp_testimonio['texto'] && $asp_testimonio['nombre'] ) : ?>
	<section class="asp-container asp-home-bloque asp-testimonio" aria-label="<?php esc_attr_e( 'Testimonio', 'asp' ); ?>">
		<figure class="asp-testimonio__figura">
			<blockquote class="asp-testimonio__cita"><p><?php echo esc_html( $asp_testimonio['texto'] ); ?></p></blockquote>
			<figcaption class="asp-testimonio__autor">
				<span class="asp-testimonio__nombre"><?php echo esc_html( $asp_testimonio['nombre'] ); ?></span>
				<?php if ( $asp_testimonio['iglesia'] ) : ?><span class="asp-testimonio__iglesia"><?php echo esc_html( $asp_testimonio['iglesia'] ); ?></span><?php endif; ?>
			</figcaption>
		</figure>
	</section>
<?php endif; ?>

<?php if ( ! empty( $asp_articulos ) ) : ?>
	<section class="asp-container asp-home-bloque" aria-labelledby="home-articulos">
		<div class="asp-editorial__cab asp-cab-suelto">
			<h2 id="home-articulos" class="asp-seccion__titulo"><?php esc_html_e( 'Artículos', 'asp' ); ?></h2>
			<a class="asp-cta-link" href="<?php echo esc_url( asp_url_articulos() ); ?>"><?php esc_html_e( 'Todos los artículos', 'asp' ); ?></a>
		</div>
		<div class="asp-grilla-articulos">
			<?php foreach ( $asp_articulos as $asp_post ) : ?>
				<?php get_template_part( 'parts/articulo/tarjeta', null, [ 'post_id' => $asp_post->ID ] ); ?>
			<?php endforeach; ?>
		</div>
	</section>
<?php endif; ?>

<?php /* TODO: el ministerio tiene que escribir la invitación real en
	   Personalizar → Ante Su Palabra → "Sumarse". Mientras esté vacío va una
	   frase genérica que solo invita a escribir. No inventar acá qué se le
	   ofrece a una iglesia que quiere sumarse. */ ?>
<?php if ( $asp_contacto || $asp_sumarse ) : ?>
	<section class="asp-cierre<?php echo $asp_foto_cierre ? ' asp-cierre--con-foto' : ''; ?>" aria-labelledby="home-sumarse">
		<?php echo $asp_foto_cierre; // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<div class="asp-cierre__velo">
			<div class="asp-container asp-cierre__inner">
				<h2 id="home-sumarse" class="asp-cierre__titulo"><?php esc_html_e( 'Sumate', 'asp' ); ?></h2>
				<p class="asp-cierre__texto"><?php
					/* Frase por defecto que solo invita a escribir: no promete nada ni
					   dice qué se ofrece. El ministerio la reemplaza desde el Customizer. */
					echo esc_html( $asp_sumarse ?: __( 'Si sos pastor o tu iglesia quiere participar, escribinos.', 'asp' ) );
				?></p>
				<?php if ( $asp_contacto ) : ?>
					<a class="asp-btn asp-btn--invertido" href="<?php echo esc_url( $asp_contacto ); ?>"><?php esc_html_e( 'Escribir al ministerio', 'asp' ); ?></a>
				<?php endif; ?>
			</div>
		</div>
	</section>
<?php endif; ?>
<?php
get_footer();
