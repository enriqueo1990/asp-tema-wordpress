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
	$asp_agenda  = asp_evento_agendable( $asp_id );
	$asp_predic    = asp_predicaciones_de_evento( $asp_id );
	$asp_izq_vacia = $asp_vacia && ! $asp_desc && empty( asp_evento_programa( $asp_id ) ) && empty( asp_evento_relacionados( $asp_id, 'evento_oradores', 'persona' ) ) && empty( asp_evento_relacionados( $asp_id, 'evento_aliados', 'aliado' ) ) && empty( $asp_predic ) && 'realizado' !== $asp_estado;
	?>
	<script type="application/ld+json"><?php echo wp_json_encode( asp_evento_schema( $asp_id ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ); ?></script>

	<article class="asp-container">
		<div class="asp-ficha<?php echo $asp_vacia ? ' asp-ficha--vacia' : ''; ?>">

			<?php /* ---- Cabecera: en escritorio va arriba de la grilla; en móvil el flyer va primero ---- */ ?>
			<div class="asp-solo-movil">
				<?php get_template_part( 'parts/evento/flyer', null, [ 'post_id' => $asp_id, 'clase' => 'asp-flyer--natural', 'loading' => 'eager' ] ); ?>
			</div>

			<?php /* Arriba solo el título y la fecha (2-10-2026): el estado, el tipo,
			   el lugar y el resto de los datos van a la columna lateral (en el
			   teléfono, al bloque de datos de abajo). */ ?>
			<header class="asp-ficha__cabecera">
				<?php /* El título manda también con flyer: en compacto (24 px) la ficha se veía más débil que sin flyer. */ ?>
				<h1 class="asp-ficha__titulo"><?php the_title(); ?></h1>

				<div class="asp-solo-escritorio asp-ficha__linea">
					<?php get_template_part( 'parts/evento/fecha', null, [ 'post_id' => $asp_id, 'variante' => 'xl' ] ); ?>
				</div>
			</header>

			<?php /* ---- Móvil: la fecha, grande si no hay flyer; después el estado y el botón ---- */ ?>
			<div class="asp-solo-movil asp-stack asp-stack--5">
				<?php get_template_part( 'parts/evento/fecha', null, [ 'post_id' => $asp_id, 'variante' => $asp_vacia ? 'grande' : '' ] ); ?>
				<div class="asp-bloque">
					<?php get_template_part( 'parts/evento/badge', null, [ 'post_id' => $asp_id ] ); ?>
					<?php if ( $asp_hay_cta ) : ?>
						<?php get_template_part( 'parts/evento/cta', null, [ 'post_id' => $asp_id, 'con_plataforma' => true, 'bloque' => true ] ); ?>
					<?php endif; ?>
				</div>
				<?php /* Tipo, precio, lugar y calendario en un solo bloque de datos:
				   bloques con filete seguidos se leían como un formulario. */ ?>
				<div class="asp-bloque asp-bloque--datos">
					<?php if ( $asp_inic ) : ?>
						<div class="asp-bloque__dato"><h2 class="asp-label"><?php esc_html_e( 'Tipo de evento', 'asp' ); ?></h2><a class="asp-sede__nombre asp-ficha__tipo" href="<?php echo esc_url( get_permalink( $asp_inic ) ); ?>"><?php echo esc_html( get_the_title( $asp_inic ) ); ?></a></div>
					<?php endif; ?>
					<?php if ( $asp_precio ) : ?>
						<div class="asp-bloque__dato"><h2 class="asp-label"><?php esc_html_e( 'Precio', 'asp' ); ?></h2><span class="asp-bloque__valor"><?php echo esc_html( $asp_precio ); ?></span></div>
					<?php endif; ?>
					<?php if ( $asp_sede || $asp_ciudad || $asp_pais ) : ?>
						<div class="asp-bloque__dato"><?php get_template_part( 'parts/evento/sede', null, [ 'post_id' => $asp_id, 'con_lugar' => true ] ); ?></div>
					<?php endif; ?>
					<?php if ( $asp_agenda ) : ?>
						<div class="asp-bloque__dato"><?php get_template_part( 'parts/evento/agenda', null, [ 'post_id' => $asp_id ] ); ?></div>
					<?php endif; ?>
				</div>
				<div class="asp-bloque"><?php get_template_part( 'parts/evento/compartir', null, [ 'post_id' => $asp_id ] ); ?></div>
			</div>

			<?php /* ---- Cuerpo: grilla 7/4 en escritorio ---- */ ?>
			<?php
			/* Sin cuerpo a la izquierda, la columna lateral ocupa el ancho de
			   lectura. Nunca está vacía: Compartir va siempre. */
			$asp_grid_clase = 'asp-grid-ficha';
			if ( $asp_izq_vacia ) {
				$asp_grid_clase .= ' asp-grid-ficha--solo-aside';
			}
			if ( ! $asp_vacia ) {
				$asp_grid_clase .= ' asp-grid-ficha--rule';
			}
			?>
			<div class="<?php echo esc_attr( $asp_grid_clase ); ?>">
				<div class="asp-stack asp-stack--6">
					<div class="asp-solo-escritorio">
						<?php get_template_part( 'parts/evento/flyer', null, [ 'post_id' => $asp_id, 'clase' => 'asp-flyer--natural', 'loading' => 'eager' ] ); ?>
					</div>
					<?php if ( $asp_desc ) : ?>
						<div class="asp-bloque asp-bloque--sin-filete">
							<h2 class="asp-label"><?php esc_html_e( 'Descripción', 'asp' ); ?></h2>
							<div class="asp-prose"><?php echo wp_kses_post( wpautop( $asp_desc ) ); ?></div>
						</div>
					<?php endif; ?>
					<?php if ( ! empty( $asp_predic ) ) : ?>
						<div class="asp-bloque">
							<h2 class="asp-label"><?php esc_html_e( 'Predicaciones de este evento', 'asp' ); ?></h2>
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

				<?php /* Compartir va siempre: con eso solo, la columna ya tiene sentido. */ ?>
				<aside class="asp-ficha-aside asp-solo-escritorio asp-sticky" aria-label="<?php esc_attr_e( 'Datos del evento', 'asp' ); ?>">
					<?php /* Todos los datos del evento menos el título y la fecha, que van
					   arriba (2-10-2026): estado, precio y botón; tipo; lugar y sede;
					   calendario y compartir. */ ?>
					<div class="asp-ficha-aside__cta">
						<?php get_template_part( 'parts/evento/badge', null, [ 'post_id' => $asp_id ] ); ?>
						<?php if ( $asp_precio ) : ?>
							<div class="asp-ficha-aside__precio"><h2 class="asp-label"><?php esc_html_e( 'Precio', 'asp' ); ?></h2><span class="asp-bloque__valor"><?php echo esc_html( $asp_precio ); ?></span></div>
						<?php endif; ?>
						<?php if ( $asp_hay_cta ) : ?>
							<?php get_template_part( 'parts/evento/cta', null, [ 'post_id' => $asp_id, 'con_plataforma' => true, 'bloque' => true ] ); ?>
						<?php endif; ?>
					</div>
					<?php if ( $asp_inic ) : ?>
						<div class="asp-ficha-aside__bloque">
							<div class="asp-stack">
								<h2 class="asp-label"><?php esc_html_e( 'Tipo de evento', 'asp' ); ?></h2>
								<a class="asp-sede__nombre asp-ficha__tipo" href="<?php echo esc_url( get_permalink( $asp_inic ) ); ?>"><?php echo esc_html( get_the_title( $asp_inic ) ); ?></a>
							</div>
						</div>
					<?php endif; ?>
					<?php if ( $asp_sede || $asp_ciudad || $asp_pais ) : ?>
						<div class="asp-ficha-aside__bloque">
							<?php echo asp_icono_ubicacion(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
							<div class="asp-stack"><?php get_template_part( 'parts/evento/sede', null, [ 'post_id' => $asp_id, 'con_lugar' => true ] ); ?></div>
						</div>
					<?php endif; ?>
					<?php if ( $asp_agenda ) : ?>
						<div class="asp-ficha-aside__bloque">
							<?php get_template_part( 'parts/evento/agenda', null, [ 'post_id' => $asp_id ] ); ?>
						</div>
					<?php endif; ?>
					<div class="asp-ficha-aside__bloque">
						<?php get_template_part( 'parts/evento/compartir', null, [ 'post_id' => $asp_id ] ); ?>
					</div>
					</aside>
			</div>
		</div>
	</article>
<?php
endwhile;
get_footer();
