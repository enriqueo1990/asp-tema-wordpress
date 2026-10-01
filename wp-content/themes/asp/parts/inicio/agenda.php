<?php
/**
 * Próximos eventos: los seis que vienen, incluido el del hero (1-10-2026).
 *
 * Es el bloque de imagen del inicio: viene entre dos listas de texto
 * (Cómo servimos) y de filas con miniatura (Predicaciones), así que acá
 * cada evento es un cartel, con el flyer grande. Debajo, en el orden del
 * hero: tipo y título, la línea de cuándo y dónde y, si corresponde, el
 * estado. Todo el cartel lleva a la ficha. Ver asp_inicio_agenda_datos().
 *
 * Los carteles van en un carril que se desplaza de costado: en el teléfono
 * asoma el siguiente; en escritorio entran tres y las flechas de la
 * cabecera (app.js) aparecen solo si hay más. Un evento solo se acuesta a
 * todo el ancho. Sin eventos para mostrar, no hay sección.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

$asp_eventos = asp_inicio_proximos( 6 );
if ( empty( $asp_eventos ) ) {
	return;
}
?>
<section class="asp-inicio-agenda" aria-labelledby="inicio-agenda">
	<div class="asp-container">
		<div class="asp-inicio-agenda__cab">
			<h2 id="inicio-agenda" class="asp-seccion__titulo"><?php esc_html_e( 'Próximos eventos', 'asp' ); ?></h2>
			<div class="asp-inicio-agenda__acciones">
				<a class="asp-cta-link" href="<?php echo esc_url( asp_url_eventos() ); ?>"><?php esc_html_e( 'Todos los eventos', 'asp' ); ?></a>
				<?php /* Las flechas nacen ocultas: app.js las muestra solo si el carril desborda. */ ?>
				<div class="asp-inicio-agenda__flechas" data-asp-carril-flechas hidden>
					<button type="button" class="asp-inicio-agenda__flecha" data-asp-carril-ir="-1" aria-controls="inicio-agenda-carril" aria-label="<?php esc_attr_e( 'Eventos anteriores', 'asp' ); ?>"><span aria-hidden="true">&larr;</span></button>
					<button type="button" class="asp-inicio-agenda__flecha" data-asp-carril-ir="1" aria-controls="inicio-agenda-carril" aria-label="<?php esc_attr_e( 'Más eventos', 'asp' ); ?>"><span aria-hidden="true">&rarr;</span></button>
				</div>
			</div>
		</div>
		<div class="asp-inicio-agenda__carril" id="inicio-agenda-carril" data-asp-carril role="region" aria-labelledby="inicio-agenda" tabindex="0">
			<ol class="asp-inicio-agenda__grilla asp-inicio-agenda__grilla--<?php echo (int) count( $asp_eventos ); ?>">
				<?php foreach ( $asp_eventos as $asp_post ) :
					$asp_d = asp_inicio_agenda_datos( $asp_post->ID );
					?>
					<li class="asp-inicio-agenda__evento">
						<span class="asp-inicio-agenda__visual asp-inicio-agenda__visual--<?php echo esc_attr( $asp_d['clase'] ); ?>" aria-hidden="true">
							<?php if ( '' !== $asp_d['imagen'] ) : ?>
								<?php echo $asp_d['imagen']; // phpcs:ignore WordPress.Security.EscapeOutput ?>
							<?php else : ?>
								<span class="asp-inicio-agenda__dias"><?php echo esc_html( $asp_d['dias'] ); ?></span>
								<span class="asp-inicio-agenda__mes"><?php echo esc_html( $asp_d['mes'] ); ?></span>
							<?php endif; ?>
						</span>
						<div class="asp-inicio-agenda__texto">
							<h3 class="asp-inicio-agenda__titulo">
								<a class="asp-inicio-agenda__enlace" href="<?php echo esc_url( $asp_d['url'] ); ?>">
									<?php if ( $asp_d['tipo'] ) : ?>
										<span class="asp-inicio-agenda__tipo"><?php echo esc_html( $asp_d['tipo'] ); ?></span><span class="visually-hidden"> · </span>
									<?php endif; ?>
									<span class="asp-inicio-agenda__nombre"><?php echo esc_html( $asp_d['titulo'] ); ?></span>
								</a>
							</h3>
							<p class="asp-inicio-agenda__datos">
								<time datetime="<?php echo esc_attr( $asp_d['iso'] ); ?>"><?php echo esc_html( $asp_d['fecha'] ); ?></time>
								<?php if ( $asp_d['lugar'] ) : ?>
									<span class="visually-hidden">, </span><span class="asp-inicio-agenda__lugar"><?php echo esc_html( $asp_d['lugar'] ); ?></span>
								<?php endif; ?>
							</p>
							<?php if ( $asp_d['estado'] ) : ?>
								<?php get_template_part( 'parts/evento/badge', null, [ 'post_id' => $asp_post->ID ] ); ?>
							<?php endif; ?>
						</div>
					</li>
				<?php endforeach; ?>
			</ol>
		</div>
	</div>
</section>
