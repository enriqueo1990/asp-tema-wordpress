<?php
/**
 * Template Name: Suscripción
 * Template Post Type: page
 *
 * /suscribite/, el link para compartir en redes con «suscribite acá»
 * (30-10-2026): logo, título, bajada y el formulario de Kit, nada más. Una
 * sola columna centrada; casi todos llegan desde Instagram o WhatsApp en el
 * teléfono. El pie no repite la caja (asp_suscripcion_en_pie()) y los
 * metadatos para compartir salen de asp_seo_datos().
 *
 * El título es el de la página; la bajada y la nota son textos fijos, como
 * los del formulario. El contenido de la página no se usa.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<section class="asp-container asp-suscribite" aria-labelledby="suscribite-titulo">
		<div class="asp-suscribite__inner">
			<?php asp_logo( 'asp-suscribite__logo' ); ?>
			<h1 id="suscribite-titulo" class="asp-suscribite__titulo"><?php the_title(); ?></h1>
			<p class="asp-suscribite__bajada"><?php esc_html_e( 'Recibí por email los próximos eventos, talleres y novedades del ministerio. Desde 2015 queremos ayudar a pastores e iglesias a ser más bíblicos.', 'asp' ); ?></p>
			<?php
			get_template_part(
				'parts/suscripcion',
				null,
				[
					'variante'  => 'clara',
					'solo_form' => true,
					'nota'      => __( 'Sin spam. Te podés dar de baja cuando quieras.', 'asp' ),
				]
			);
			?>
		</div>
	</section>
<?php
endwhile;
get_footer();
