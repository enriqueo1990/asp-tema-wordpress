<?php
/**
 * Ficha de evento (rediseño del handoff de Claude Design, 2-10-2026).
 *
 * Orden: flyer, título, datos clave (fecha, lugar, costo) en un panel,
 * oradores, descripción, programa y predicaciones si existen, aliados y la
 * banda de acción. Sin rótulos de sección salvo el estado y sin filetes:
 * la única caja es el panel de datos. Cada bloque se imprime solo si tiene
 * contenido; los datos ya llegan decididos según el estado
 * (asp_ficha_datos(), en inc/ficha-evento.php). Emite schema.org/Event.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$asp_id     = get_the_ID();
	$asp_d      = asp_ficha_datos( $asp_id );
	$asp_predic = asp_predicaciones_de_evento( $asp_id );
	$asp_anio   = asp_evento_anio( $asp_id );
	?>
	<script type="application/ld+json"><?php echo wp_json_encode( asp_evento_schema( $asp_id ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ); ?></script>

	<article class="ev">

		<?php /* 1. Flyer: a sangre en el teléfono, al ancho del contenedor en escritorio. Nunca recortado. */ ?>
		<?php if ( $asp_d['flyer'] ) : ?>
			<figure class="ev-flyer"><?php echo $asp_d['flyer']; // phpcs:ignore WordPress.Security.EscapeOutput ?></figure>
		<?php endif; ?>

		<?php /* 2. Título */ ?>
		<header class="ev-head ev-wrap">
			<h1 class="ev-title"><?php the_title(); ?></h1>
		</header>

		<?php /* 3. Datos clave: la única caja de la página */ ?>
		<section class="ev-facts" aria-label="<?php esc_attr_e( 'Datos del evento', 'asp' ); ?>">
			<?php if ( $asp_d['fecha'] ) : ?>
				<div class="ev-fact ev-fact--date">
					<?php echo asp_ficha_icono( 'calendar' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<div class="ev-fact__body">
						<p class="ev-fact__main"><time datetime="<?php echo esc_attr( $asp_d['iso'] ); ?>"><?php echo esc_html( $asp_d['fecha'] ); ?></time></p>
						<?php get_template_part( 'parts/evento/menu-calendario', null, [ 'agenda' => $asp_d['agenda'], 'variante' => 'link' ] ); ?>
					</div>
				</div>
			<?php endif; ?>

			<?php if ( $asp_d['lugar'] || $asp_d['sede'] || $asp_d['direccion'] ) : ?>
				<div class="ev-fact">
					<?php echo asp_ficha_icono( 'map-pin' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<div class="ev-fact__body">
						<?php if ( $asp_d['lugar'] ) : ?>
							<?php /* La bandera va en línea: si el renglón corta, baja con la última palabra. */ ?>
							<?php if ( $asp_d['bandera'] ) :
								$asp_corte = (int) strrpos( $asp_d['lugar'], ' ' );
								?>
								<p class="ev-fact__main"><?php echo esc_html( substr( $asp_d['lugar'], 0, $asp_corte ) ); ?> <span class="ev-fact__ultima"><?php echo esc_html( ltrim( substr( $asp_d['lugar'], $asp_corte ) ) ); ?><img class="ev-flag" src="<?php echo esc_url( $asp_d['bandera']['src'] ); ?>" alt="<?php echo esc_attr( $asp_d['bandera']['alt'] ); ?>" width="33" height="22"></span></p>
							<?php else : ?>
								<p class="ev-fact__main"><?php echo esc_html( $asp_d['lugar'] ); ?></p>
							<?php endif; ?>
						<?php endif; ?>
						<?php if ( $asp_d['sede'] ) : ?>
							<p class="ev-fact__sub ev-fact__sub--fuerte"><?php echo esc_html( $asp_d['sede'] ); ?></p>
						<?php endif; ?>
						<?php if ( $asp_d['direccion'] ) : ?>
							<p class="ev-fact__sub"><?php echo nl2br( esc_html( $asp_d['direccion'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
						<?php endif; ?>
						<?php if ( $asp_d['mapa'] ) : ?>
							<a class="ev-link" href="<?php echo esc_url( $asp_d['mapa'] ); ?>" target="_blank" rel="noopener"><span class="ev-u"><?php esc_html_e( 'Cómo llegar', 'asp' ); ?></span><?php echo asp_ficha_icono( 'arrow', 'ev-ico--xs' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span class="screen-reader-text"> <?php esc_html_e( '(abre Google Maps)', 'asp' ); ?></span></a>
						<?php endif; ?>
					</div>
				</div>
			<?php endif; ?>

			<?php /* Costo: solo con inscripción abierta y si hay precio cargado. */ ?>
			<?php if ( $asp_d['monto'] ) : ?>
				<div class="ev-fact">
					<?php echo asp_ficha_icono( 'ticket' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<div class="ev-fact__body">
						<p class="ev-fact__main"><span class="screen-reader-text"><?php esc_html_e( 'Costo:', 'asp' ); ?> </span><?php echo esc_html( $asp_d['monto'] ); ?></p>
						<?php if ( $asp_d['detalle'] ) : ?>
							<p class="ev-fact__sub"><?php echo esc_html( $asp_d['detalle'] ); ?></p>
						<?php endif; ?>
					</div>
				</div>
			<?php endif; ?>
		</section>

		<?php /* 4. Oradores, sin rótulo. La bio se abre en una ventana encima de la
		   página (1-10-2026: desplegada en el lugar corría al otro orador y se
		   leía apretada). Sin JavaScript, «Ver bio» lleva a la ficha de la persona. */ ?>
		<?php if ( ! empty( $asp_d['oradores'] ) ) : ?>
			<section class="ev-speakers ev-wrap" aria-label="<?php echo esc_attr( _n( 'Orador', 'Oradores', count( $asp_d['oradores'] ), 'asp' ) ); ?>">
				<?php foreach ( $asp_d['oradores'] as $asp_o ) :
					$asp_p     = $asp_o['persona'];
					$asp_linea = $asp_p ? asp_persona_cargo_iglesia( $asp_p->ID ) : '';
					$asp_bio   = $asp_p ? (string) get_post_meta( $asp_p->ID, 'persona_bio', true ) : '';
					?>
					<div class="ev-speaker">
						<?php if ( $asp_p ) : ?>
							<?php echo asp_persona_foto( $asp_p->ID, 'ev-speaker__foto' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						<?php endif; ?>
						<div class="ev-speaker__texto">
							<p class="ev-speaker__name"><?php echo esc_html( $asp_o['nombre'] ); ?></p>
							<?php if ( $asp_linea ) : ?>
								<p class="ev-speaker__role"><?php echo esc_html( $asp_linea ); ?></p>
							<?php endif; ?>
							<?php if ( $asp_bio ) : ?>
								<a class="ev-bio-abrir" href="<?php echo esc_url( get_permalink( $asp_p ) ); ?>" data-asp-dialogo="bio-<?php echo (int) $asp_p->ID; ?>" aria-haspopup="dialog"><span class="ev-u"><?php esc_html_e( 'Ver bio', 'asp' ); ?></span></a>
							<?php endif; ?>
						</div>
					</div>
				<?php endforeach; ?>
			</section>

			<?php foreach ( $asp_d['oradores'] as $asp_o ) :
				$asp_p   = $asp_o['persona'];
				$asp_bio = $asp_p ? (string) get_post_meta( $asp_p->ID, 'persona_bio', true ) : '';
				if ( ! $asp_bio ) {
					continue;
				}
				$asp_linea = asp_persona_cargo_iglesia( $asp_p->ID );
				?>
				<dialog class="ev-dialogo" id="bio-<?php echo (int) $asp_p->ID; ?>" aria-labelledby="bio-<?php echo (int) $asp_p->ID; ?>-nombre">
					<div class="ev-dialogo__cab">
						<?php echo asp_persona_foto( $asp_p->ID, 'ev-speaker__foto' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						<div class="ev-speaker__texto">
							<h2 class="ev-speaker__name" id="bio-<?php echo (int) $asp_p->ID; ?>-nombre"><?php echo esc_html( $asp_o['nombre'] ); ?></h2>
							<?php if ( $asp_linea ) : ?>
								<p class="ev-speaker__role"><?php echo esc_html( $asp_linea ); ?></p>
							<?php endif; ?>
						</div>
						<button type="button" class="ev-dialogo__cerrar" data-asp-cerrar aria-label="<?php esc_attr_e( 'Cerrar', 'asp' ); ?>"><span aria-hidden="true">&times;</span></button>
					</div>
					<div class="ev-dialogo__texto"><?php echo wp_kses_post( wpautop( $asp_bio ) ); ?></div>
					<a class="ev-link" href="<?php echo esc_url( get_permalink( $asp_p ) ); ?>"><span class="ev-u"><?php esc_html_e( 'Ver ficha completa', 'asp' ); ?></span><?php echo asp_ficha_icono( 'arrow', 'ev-ico--xs' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
				</dialog>
			<?php endforeach; ?>
		<?php endif; ?>

		<?php /* 5. Descripción */ ?>
		<?php if ( $asp_d['descripcion'] ) : ?>
			<section class="ev-desc ev-wrap" aria-label="<?php esc_attr_e( 'Descripción', 'asp' ); ?>">
				<?php echo wp_kses_post( wpautop( $asp_d['descripcion'] ) ); ?>
			</section>
		<?php endif; ?>

		<?php /* Programa y predicaciones: no están en el handoff, pero son datos
		   reales del evento y no se pierden. Van con sus piezas de siempre. */ ?>
		<?php if ( asp_evento_programa( $asp_id ) ) : ?>
			<div class="ev-extra ev-wrap"><?php get_template_part( 'parts/evento/programa', null, [ 'post_id' => $asp_id ] ); ?></div>
		<?php endif; ?>
		<?php if ( ! empty( $asp_predic ) ) : ?>
			<div class="ev-extra ev-wrap">
				<div class="asp-bloque">
					<h2 class="asp-label"><?php esc_html_e( 'Predicaciones de este evento', 'asp' ); ?></h2>
					<div>
						<?php foreach ( $asp_predic as $asp_pr ) : ?>
							<?php get_template_part( 'parts/predicacion/fila', null, [ 'post_id' => $asp_pr->ID, 'sin_evento' => true ] ); ?>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		<?php endif; ?>

		<?php /* 6. Aliados: solo logos con link; sin logo, el nombre. */ ?>
		<?php if ( ! empty( $asp_d['aliados'] ) ) : ?>
			<section class="ev-allies ev-wrap" aria-label="<?php esc_attr_e( 'Aliados', 'asp' ); ?>">
				<?php foreach ( $asp_d['aliados'] as $asp_a ) :
					$asp_url  = (string) get_post_meta( $asp_a->ID, 'aliado_url', true );
					$asp_logo = absint( get_post_meta( $asp_a->ID, 'aliado_logo', true ) );
					$asp_html = $asp_logo ? wp_get_attachment_image( $asp_logo, 'medium', false, [ 'alt' => get_the_title( $asp_a ), 'loading' => 'lazy' ] ) : '';
					if ( ! $asp_html ) {
						$asp_html = '<span class="ev-allies__nombre">' . esc_html( get_the_title( $asp_a ) ) . '</span>';
					}
					?>
					<?php if ( $asp_url ) : ?>
						<a href="<?php echo esc_url( $asp_url ); ?>" target="_blank" rel="noopener"><?php echo $asp_html; // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo asp_aviso_pestana(); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
					<?php else : ?>
						<?php echo $asp_html; // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<?php endif; ?>
				<?php endforeach; ?>
			</section>
		<?php endif; ?>

		<?php /* 7. Banda de acción */ ?>
		<section class="ev-cta" aria-label="<?php esc_attr_e( 'Inscripción', 'asp' ); ?>">
			<div class="ev-cta__inner ev-wrap">
				<p class="ev-status"><?php echo esc_html( $asp_d['etiqueta'] ); ?></p>
				<div class="ev-cta__actions<?php echo ( ! $asp_d['registro'] && ! ( 'reserva' === $asp_d['estado'] && $asp_d['agenda'] ) ) ? ' ev-cta__actions--sin-primario' : ''; ?>">
					<?php if ( $asp_d['registro'] ) : ?>
						<a class="ev-btn ev-btn--primary" href="<?php echo esc_url( $asp_d['registro'] ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Inscribirse', 'asp' ); ?><?php echo asp_ficha_icono( 'arrow', 'ev-ico--sm' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo asp_aviso_pestana(); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
						<?php get_template_part( 'parts/evento/menu-calendario', null, [ 'agenda' => $asp_d['agenda'], 'variante' => 'secundario' ] ); ?>
					<?php elseif ( 'reserva' === $asp_d['estado'] ) : ?>
						<?php get_template_part( 'parts/evento/menu-calendario', null, [ 'agenda' => $asp_d['agenda'], 'variante' => 'primario' ] ); ?>
					<?php else : ?>
						<?php get_template_part( 'parts/evento/menu-calendario', null, [ 'agenda' => $asp_d['agenda'], 'variante' => 'secundario' ] ); ?>
					<?php endif; ?>
					<?php get_template_part( 'parts/evento/menu-compartir', null, [ 'compartir' => $asp_d['compartir'], 'titulo' => html_entity_decode( get_the_title(), ENT_QUOTES, 'UTF-8' ) ] ); ?>
				</div>
				<?php if ( 'realizado' === $asp_d['estado'] && $asp_anio ) : ?>
					<a class="ev-link ev-cta__archivo" href="<?php echo esc_url( asp_url_eventos() . '#archivo-' . $asp_anio ); ?>"><span class="ev-u"><?php
						/* translators: %d: año */
						echo esc_html( sprintf( __( 'Ver el archivo %d', 'asp' ), $asp_anio ) );
					?></span></a>
				<?php endif; ?>
			</div>
		</section>

	</article>
<?php
endwhile;
get_footer();
