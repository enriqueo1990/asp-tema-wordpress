<?php
/**
 * /eventos/ — próximos arriba, archivo por año abajo. Sin filtros,
 * sin estados vacíos ni contadores: una sección sin contenido no existe.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

get_header();

$asp_proximos = asp_eventos_proximos()->posts;
$asp_por_anio = asp_eventos_pasados_por_anio();
?>
<div class="asp-container asp-section">
	<div class="asp-section__inner asp-section__inner--loose">
		<h1 class="asp-pagina__titulo"><?php echo esc_html( post_type_archive_title( '', false ) ); ?></h1>

		<?php if ( ! empty( $asp_proximos ) ) : ?>
			<section class="asp-stack asp-stack--5" aria-label="<?php esc_attr_e( 'Próximos eventos', 'asp' ); ?>">
				<span class="asp-label"><?php esc_html_e( 'Próximos', 'asp' ); ?></span>
				<?php foreach ( $asp_proximos as $asp_post ) : ?>
					<?php get_template_part( 'parts/evento/card', null, [ 'post_id' => $asp_post->ID, 'ancha' => true ] ); ?>
				<?php endforeach; ?>
			</section>
		<?php endif; ?>

		<?php if ( ! empty( $asp_por_anio ) ) : ?>
			<section class="asp-stack asp-stack--5" id="archivo" aria-label="<?php esc_attr_e( 'Archivo de eventos', 'asp' ); ?>">
				<span class="asp-label"><?php esc_html_e( 'Archivo', 'asp' ); ?></span>
				<?php foreach ( $asp_por_anio as $asp_anio => $asp_eventos ) : ?>
					<div class="asp-stack" id="archivo-<?php echo esc_attr( (string) $asp_anio ); ?>">
						<div class="asp-anio"><span class="asp-anio__num"><?php echo esc_html( (string) $asp_anio ); ?></span></div>
						<div>
							<?php foreach ( $asp_eventos as $asp_post ) : ?>
								<?php get_template_part( 'parts/evento/archivo-item', null, [ 'post_id' => $asp_post->ID, 'ancho' => true ] ); ?>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endforeach; ?>
			</section>
		<?php endif; ?>

		<?php if ( empty( $asp_proximos ) && empty( $asp_por_anio ) ) : ?>
			<p class="asp-muted"><?php esc_html_e( 'Todavía no hay eventos publicados.', 'asp' ); ?></p>
		<?php endif; ?>
	</div>
</div>
<?php
get_footer();
