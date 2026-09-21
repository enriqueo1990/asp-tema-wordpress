<?php
/**
 * Ficha de evento. Cada bloque se renderiza solo si tiene contenido.
 * Sin flyer, la fecha grande ocupa su lugar. Emite schema.org/Event.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$asp_id      = get_the_ID();
	$asp_estado  = asp_evento_estado( $asp_id );
	$asp_flyer   = absint( get_post_meta( $asp_id, 'evento_flyer', true ) );
	$asp_inic    = asp_evento_iniciativa( $asp_id );
	$asp_precio  = (string) get_post_meta( $asp_id, 'evento_precio', true );
	$asp_desc    = (string) get_post_meta( $asp_id, 'evento_descripcion', true );
	$asp_sede    = (string) get_post_meta( $asp_id, 'evento_sede_nombre', true ) . (string) get_post_meta( $asp_id, 'evento_sede_direccion', true );
	$asp_anio    = asp_evento_anio( $asp_id );
	$asp_vacia   = ! $asp_flyer;
	$asp_ciudad  = asp_evento_ciudad( $asp_id );
	$asp_pais    = asp_evento_pais( $asp_id );
	$asp_hay_cta = asp_evento_tiene_boton( $asp_id ) || in_array( $asp_estado, [ 'cerrada', 'agotado' ], true );
	$asp_predic    = asp_predicaciones_de_evento( $asp_id );
	$asp_izq_vacia = $asp_vacia && ! $asp_desc && empty( asp_evento_programa( $asp_id ) ) && empty( asp_evento_relacionados( $asp_id, 'evento_oradores', 'persona' ) ) && empty( asp_evento_relacionados( $asp_id, 'evento_aliados', 'aliado' ) ) && empty( $asp_predic ) && 'realizado' !== $asp_estado;
	?>
	<script type="application/ld+json"><?php echo wp_json_encode( asp_evento_schema( $asp_id ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ); ?></script>

	<article class="asp-container">
		<div class="asp-ficha<?php echo $asp_vacia ? ' asp-ficha--vacia' : ''; ?>">

			<?php /* ---- Cabecera: en escritorio va arriba de la grilla; en móvil el flyer va primero ---- */ ?>
			<div class="asp-solo-movil">
				<?php get_template_part( 'parts/evento/flyer', null, [ 'post_id' => $asp_id, 'loading' => 'eager' ] ); ?>
			</div>

			<header class="asp-ficha__cabecera">
				<div class="asp-row">
					<?php get_template_part( 'parts/evento/badge', null, [ 'post_id' => $asp_id ] ); ?>
					<?php if ( $asp_inic ) : ?>
						<a class="asp-label" href="<?php echo esc_url( get_permalink( $asp_inic ) ); ?>"><?php echo esc_html( get_the_title( $asp_inic ) ); ?></a>
					<?php endif; ?>
				</div>
				<h1 class="asp-ficha__titulo<?php echo ( ! $asp_vacia ) ? ' asp-ficha__titulo--compacto' : ''; ?>"><?php the_title(); ?></h1>

				<div class="asp-solo-escritorio asp-ficha__linea">
					<?php get_template_part( 'parts/evento/fecha', null, [ 'post_id' => $asp_id, 'variante' => 'xl' ] ); ?>
					<?php if ( $asp_ciudad || $asp_pais ) : ?>
						<span class="asp-lugar__sep" aria-hidden="true"></span>
						<?php get_template_part( 'parts/evento/lugar', null, [ 'post_id' => $asp_id, 'variante' => 'md' ] ); ?>
					<?php endif; ?>
				</div>
			</header>

			<?php /* ---- Móvil: fecha y lugar, grandes si no hay flyer ---- */ ?>
			<div class="asp-solo-movil asp-stack asp-stack--5">
				<?php if ( $asp_vacia ) : ?>
					<?php get_template_part( 'parts/evento/fecha', null, [ 'post_id' => $asp_id, 'variante' => 'grande' ] ); ?>
					<?php get_template_part( 'parts/evento/lugar', null, [ 'post_id' => $asp_id, 'variante' => 'grande' ] ); ?>
				<?php else : ?>
					<?php get_template_part( 'parts/evento/fecha', null, [ 'post_id' => $asp_id, 'variante' => 'xl' ] ); ?>
					<?php get_template_part( 'parts/evento/lugar', null, [ 'post_id' => $asp_id ] ); ?>
				<?php endif; ?>
				<?php if ( $asp_hay_cta ) : ?>
					<div class="asp-bloque"><?php get_template_part( 'parts/evento/cta', null, [ 'post_id' => $asp_id, 'con_plataforma' => true, 'bloque' => true ] ); ?></div>
				<?php endif; ?>
				<?php if ( $asp_precio ) : ?>
					<div class="asp-bloque"><span class="asp-label"><?php esc_html_e( 'Precio', 'asp' ); ?></span><span class="asp-bloque__valor"><?php echo esc_html( $asp_precio ); ?></span></div>
				<?php endif; ?>
				<?php if ( $asp_sede ) : ?>
					<div class="asp-bloque"><?php get_template_part( 'parts/evento/sede', null, [ 'post_id' => $asp_id ] ); ?></div>
				<?php endif; ?>
			</div>

			<?php /* ---- Cuerpo: grilla 7/4 en escritorio ---- */ ?>
			<?php
			/* Sin cuerpo a la izquierda y sin datos para la columna lateral, la grilla no existe en escritorio.
			   Sin cuerpo pero con columna lateral, esta ocupa el ancho de lectura. */
			$asp_aside_vacio = $asp_vacia && ! $asp_hay_cta && ! $asp_precio && ! $asp_sede;
			$asp_grid_clase  = 'asp-grid-ficha';
			if ( $asp_izq_vacia && $asp_aside_vacio ) {
				$asp_grid_clase .= ' asp-solo-movil';
			} elseif ( $asp_izq_vacia ) {
				$asp_grid_clase .= ' asp-grid-ficha--solo-aside';
			}
			if ( ! $asp_vacia ) {
				$asp_grid_clase .= ' asp-grid-ficha--rule';
			}
			?>
			<div class="<?php echo esc_attr( $asp_grid_clase ); ?>">
				<div class="asp-stack asp-stack--6">
					<div class="asp-solo-escritorio">
						<?php get_template_part( 'parts/evento/flyer', null, [ 'post_id' => $asp_id, 'clase' => 'asp-flyer--desktop-wide', 'loading' => 'eager' ] ); ?>
					</div>
					<?php if ( $asp_desc ) : ?>
						<div class="asp-bloque asp-bloque--sin-filete">
							<span class="asp-label"><?php esc_html_e( 'Descripción', 'asp' ); ?></span>
							<div class="asp-prose"><?php echo wp_kses_post( wpautop( $asp_desc ) ); ?></div>
						</div>
					<?php endif; ?>
					<?php if ( ! empty( $asp_predic ) ) : ?>
						<div class="asp-bloque">
							<span class="asp-label"><?php esc_html_e( 'Predicaciones de este evento', 'asp' ); ?></span>
							<div>
								<?php foreach ( $asp_predic as $asp_p ) : ?>
									<?php get_template_part( 'parts/predicacion/fila', null, [ 'post_id' => $asp_p->ID, 'sin_evento' => true ] ); ?>
								<?php endforeach; ?>
							</div>
						</div>
					<?php endif; ?>
					<?php get_template_part( 'parts/evento/programa', null, [ 'post_id' => $asp_id ] ); ?>
					<?php get_template_part( 'parts/evento/oradores', null, [ 'post_id' => $asp_id, 'grid' => true ] ); ?>
					<?php get_template_part( 'parts/evento/aliados', null, [ 'post_id' => $asp_id ] ); ?>
					<?php /* La ficha es larga en móvil y el botón queda arriba de todo: se repite al final. En escritorio la columna lateral es pegajosa y no hace falta. */ ?>
					<?php if ( asp_evento_tiene_boton( $asp_id ) && ! $asp_izq_vacia ) : ?>
						<div class="asp-solo-movil asp-bloque">
							<?php get_template_part( 'parts/evento/cta', null, [ 'post_id' => $asp_id, 'con_plataforma' => true, 'bloque' => true ] ); ?>
						</div>
					<?php endif; ?>
					<?php if ( 'realizado' === $asp_estado && $asp_anio ) : ?>
						<a class="asp-link-archivo" href="<?php echo esc_url( asp_url_eventos() . '#archivo-' . $asp_anio ); ?>"><?php
							/* translators: %d: año */
							echo esc_html( sprintf( __( 'Ver el archivo %d', 'asp' ), $asp_anio ) );
						?></a>
					<?php endif; ?>
				</div>

				<?php if ( $asp_hay_cta || $asp_precio || $asp_sede || ! $asp_vacia ) : ?>
					<aside class="asp-ficha-aside asp-solo-escritorio asp-sticky" aria-label="<?php esc_attr_e( 'Datos del evento', 'asp' ); ?>">
						<?php if ( $asp_hay_cta || $asp_precio ) : ?>
							<div class="asp-ficha-aside__cta">
								<?php if ( $asp_precio ) : ?>
									<div class="asp-ficha-aside__precio"><span class="asp-label"><?php esc_html_e( 'Precio', 'asp' ); ?></span><span class="asp-bloque__valor"><?php echo esc_html( $asp_precio ); ?></span></div>
								<?php endif; ?>
								<?php get_template_part( 'parts/evento/cta', null, [ 'post_id' => $asp_id, 'con_plataforma' => true, 'bloque' => true ] ); ?>
							</div>
						<?php endif; ?>
						<div class="asp-ficha-aside__bloque">
							<div class="asp-stack">
								<span class="asp-label"><?php esc_html_e( 'Fechas', 'asp' ); ?></span>
								<?php get_template_part( 'parts/evento/fecha', null, [ 'post_id' => $asp_id ] ); ?>
							</div>
						</div>
						<?php if ( $asp_sede ) : ?>
							<div class="asp-ficha-aside__bloque">
								<?php echo asp_icono_ubicacion(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
								<div class="asp-stack"><?php get_template_part( 'parts/evento/sede', null, [ 'post_id' => $asp_id, 'con_pais' => true ] ); ?></div>
							</div>
						<?php endif; ?>
					</aside>
				<?php endif; ?>
			</div>
		</div>
	</article>
<?php
endwhile;
get_footer();
