<?php
/**
 * Hero del inicio. El evento manda: si hay próximo evento, ocupa la primera
 * pantalla sobre la fotografía a sangre, con la fecha como numeral grande.
 * Sin evento próximo, el hero cae al versículo (o al eslogan propio) sobre la
 * misma foto. Sin foto, fondo oscuro pleno. Nunca queda vacío.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

$asp_evento  = asp_evento_destacado();
$asp_id      = $asp_evento ? $asp_evento->ID : 0;
$asp_eslogan = (string) get_theme_mod( 'asp_hero_eslogan', '' );
$asp_foto    = asp_imagen_mod( 'asp_hero_imagen', 'asp-portada__foto', 'full', 'eager' );
?>
<section class="asp-portada<?php echo $asp_id ? ' asp-portada--evento' : ''; ?>" aria-label="<?php echo $asp_id ? esc_attr__( 'Próximo evento', 'asp' ) : esc_attr__( 'Presentación', 'asp' ); ?>">
	<?php echo $asp_foto; // phpcs:ignore WordPress.Security.EscapeOutput ?>
	<div class="asp-portada__velo">
		<div class="asp-container asp-portada__inner">
			<?php if ( $asp_id ) :
				$asp_estado = asp_evento_estado( $asp_id );
				$asp_fecha  = asp_evento_fecha_display( $asp_id );
				?>
				<div class="asp-portada__cab">
					<span class="asp-label"><?php esc_html_e( 'Próximo evento', 'asp' ); ?></span>
					<span class="asp-portada__sep" aria-hidden="true"></span>
					<span class="asp-label asp-label--strong"><?php echo esc_html( asp_evento_estado_etiqueta( $asp_estado ) ); ?></span>
				</div>
				<?php if ( $asp_fecha['dias'] ) : ?>
					<p class="asp-portada__fecha">
						<time datetime="<?php echo esc_attr( $asp_fecha['iso'] ); ?>">
							<span class="visually-hidden"><?php echo esc_html( $asp_fecha['texto'] ); ?></span>
							<span class="asp-portada__dias" aria-hidden="true"><?php echo esc_html( str_replace( ' · ', '–', $asp_fecha['dias'] ) ); ?></span>
						</time>
						<span class="asp-portada__mes" aria-hidden="true"><?php echo esc_html( $asp_fecha['mes'] ); ?></span>
					</p>
				<?php endif; ?>
				<h1 class="asp-portada__titulo"><a href="<?php echo esc_url( get_permalink( $asp_id ) ); ?>"><?php echo esc_html( get_the_title( $asp_id ) ); ?></a></h1>
				<div class="asp-portada__pie">
					<?php get_template_part( 'parts/evento/lugar', null, [ 'post_id' => $asp_id, 'variante' => 'md' ] ); ?>
					<?php if ( asp_evento_tiene_boton( $asp_id ) ) : ?>
						<a class="asp-btn asp-btn--invertido" href="<?php echo esc_url( asp_evento_url_registro( $asp_id ) ); ?>" target="_blank" rel="noopener"><?php echo esc_html( asp_evento_boton_texto( $asp_id, true ) ); ?></a>
					<?php else : ?>
						<a class="asp-btn asp-btn--invertido" href="<?php echo esc_url( get_permalink( $asp_id ) ); ?>"><?php esc_html_e( 'Ver el evento', 'asp' ); ?></a>
					<?php endif; ?>
				</div>
			<?php elseif ( $asp_eslogan ) : ?>
				<h1 class="asp-portada__cita"><?php echo esc_html( $asp_eslogan ); ?></h1>
			<?php else : ?>
				<h1 class="asp-portada__cita">«Pero a este miraré: al que es humilde y contrito de espíritu, y que tiembla ante Mi palabra»</h1>
				<span class="asp-label"><?php esc_html_e( 'Isaías 66:2', 'asp' ); ?></span>
			<?php endif; ?>
		</div>
	</div>
</section>
