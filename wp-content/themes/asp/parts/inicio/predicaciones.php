<?php
/**
 * Predicaciones en la banda oscura, agrupadas por conferencia: el rótulo
 * (iniciativa y año), el nombre de la conferencia y sus predicaciones. En
 * cada una manda el pasaje bíblico (campo manual de la predicación), después
 * el título y el orador. Sin pasaje cargado, el título pasa adelante: el
 * pasaje nunca se deduce del título de YouTube.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

$asp_grupos = asp_predicaciones_por_conferencia( 2, 4 );
if ( empty( $asp_grupos ) ) {
	return;
}
$asp_sin_pasaje = true;
?>
<div class="asp-banda-oscura asp-inicio-predicaciones">
	<section class="asp-container" aria-labelledby="inicio-predicaciones">
		<div class="asp-inicio-predicaciones__cab">
			<h2 id="inicio-predicaciones" class="asp-seccion__titulo"><?php esc_html_e( 'Predicaciones', 'asp' ); ?></h2>
			<?php asp_buscador_predicaciones( 'banda' ); ?>
		</div>

		<div class="asp-inicio-predicaciones__grupos">
			<?php foreach ( $asp_grupos as $asp_g ) :
				$asp_ev = $asp_g['evento'];
				?>
				<section class="asp-inicio-conferencia" aria-labelledby="inicio-conf-<?php echo esc_attr( (string) $asp_ev->ID ); ?>">
					<div class="asp-inicio-conferencia__cab">
						<?php if ( $asp_g['rotulo'] ) : ?>
							<span class="asp-label"><?php echo esc_html( $asp_g['rotulo'] ); ?></span>
						<?php endif; ?>
						<h3 id="inicio-conf-<?php echo esc_attr( (string) $asp_ev->ID ); ?>" class="asp-inicio-conferencia__titulo"><a href="<?php echo esc_url( get_permalink( $asp_ev ) ); ?>"><?php echo esc_html( get_the_title( $asp_ev ) ); ?></a></h3>
					</div>
					<ol class="asp-inicio-conferencia__lista">
						<?php foreach ( $asp_g['items'] as $asp_p ) :
							$asp_pasaje = trim( (string) get_post_meta( $asp_p->ID, 'predicacion_pasaje', true ) );
							$asp_partes = asp_predicacion_titulo_partes( $asp_p->ID );
							$asp_sin_pasaje = $asp_sin_pasaje && '' === $asp_pasaje;
							/* Miniatura: la imagen destacada (la de YouTube en las importadas). */
							$asp_mini   = asp_imagen_destacada( $asp_p->ID, 'asp-tarjeta', 'asp-inicio-sermon__miniatura' );
							$asp_video  = 'video' === asp_predicacion_tipo( $asp_p->ID );
							$asp_clases = 'asp-inicio-sermon' . ( '' === $asp_pasaje ? ' asp-inicio-sermon--sin-pasaje' : '' ) . ( $asp_mini ? ' asp-inicio-sermon--con-miniatura' : '' );
							?>
							<li>
								<a class="<?php echo esc_attr( $asp_clases ); ?>" href="<?php echo esc_url( get_permalink( $asp_p ) ); ?>">
									<?php if ( $asp_mini ) : ?>
										<span class="asp-inicio-sermon__visual" aria-hidden="true"><?php echo $asp_mini; // phpcs:ignore WordPress.Security.EscapeOutput ?><?php if ( $asp_video ) : ?><span class="asp-play"></span><?php endif; ?></span>
									<?php endif; ?>
									<span class="asp-inicio-sermon__texto">
										<?php if ( '' !== $asp_pasaje ) : ?>
											<span class="asp-inicio-sermon__pasaje"><?php echo esc_html( $asp_pasaje ); ?></span>
										<?php endif; ?>
										<span class="asp-inicio-sermon__titulo"><?php echo esc_html( $asp_partes['titulo'] ); ?></span>
										<?php if ( $asp_partes['orador'] ) : ?>
											<span class="asp-inicio-sermon__orador"><?php echo esc_html( $asp_partes['orador'] ); ?></span>
										<?php endif; ?>
									</span>
								</a>
							</li>
						<?php endforeach; ?>
					</ol>
					<?php if ( $asp_g['total'] > count( $asp_g['items'] ) ) : ?>
						<a class="asp-cta-link" href="<?php echo esc_url( get_permalink( $asp_ev ) ); ?>"><?php
							/* translators: %d: cantidad de predicaciones de la conferencia */
							echo esc_html( sprintf( __( 'Las %d predicaciones', 'asp' ), $asp_g['total'] ) );
						?></a>
					<?php endif; ?>
				</section>
			<?php endforeach; ?>
		</div>

		<?php if ( $asp_sin_pasaje ) : ?>
			<?php /* TODO: cargar el pasaje de cada predicación a mano (campo «Pasaje bíblico»). */ ?>
			<?php asp_inicio_falta( __( 'el pasaje bíblico de las predicaciones', 'asp' ), __( 'Predicaciones → editar → Pasaje bíblico', 'asp' ) ); ?>
		<?php endif; ?>

		<a class="asp-cta-link" href="<?php echo esc_url( (string) get_post_type_archive_link( 'predicacion' ) ); ?>"><?php esc_html_e( 'Todas las predicaciones', 'asp' ); ?></a>
	</section>
</div>
