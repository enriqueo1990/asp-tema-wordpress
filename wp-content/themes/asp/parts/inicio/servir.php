<?php
/**
 * Cómo servimos: una tarjeta por iniciativa con foto real (si hay; nunca un
 * bloque de color en su lugar), el país, el nombre, qué es en una línea,
 * para quién y la próxima fecha. Toda la tarjeta lleva a la iniciativa.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

$asp_iniciativas = asp_iniciativas_portada( 6 );
if ( empty( $asp_iniciativas ) ) {
	return;
}
?>
<section class="asp-inicio-servir" aria-labelledby="inicio-servir">
	<div class="asp-container">
		<div class="asp-inicio-servir__cab">
			<h2 id="inicio-servir" class="asp-seccion__titulo"><?php esc_html_e( 'Cómo servimos', 'asp' ); ?></h2>
		</div>
		<ul class="asp-inicio-servir__lista">
			<?php foreach ( $asp_iniciativas as $asp_post ) : ?>
				<?php $asp_i = asp_inicio_iniciativa( $asp_post->ID ); ?>
				<li class="asp-inicio-servir__item<?php echo $asp_i['foto'] ? ' asp-inicio-servir__item--con-foto' : ''; ?>">
					<?php if ( $asp_i['foto'] ) : ?>
						<span class="asp-inicio-servir__visual"><?php echo $asp_i['foto']; // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
					<?php endif; ?>
					<div class="asp-inicio-servir__cuerpo">
						<?php if ( $asp_i['pais'] ) : ?>
							<span class="asp-label"><?php echo esc_html( $asp_i['pais'] ); ?></span>
						<?php endif; ?>
						<h3 class="asp-inicio-servir__titulo"><a class="asp-inicio-servir__enlace" href="<?php echo esc_url( get_permalink( $asp_post ) ); ?>"><?php echo esc_html( get_the_title( $asp_post ) ); ?></a></h3>
						<?php if ( $asp_i['bajada'] ) : ?>
							<p class="asp-inicio-servir__bajada"><?php echo esc_html( $asp_i['bajada'] ); ?></p>
						<?php else : ?>
							<?php /* TODO: la bajada de cada iniciativa la escribe el ministerio. */ ?>
							<?php asp_inicio_falta( __( 'qué es, en una línea', 'asp' ), __( 'Iniciativas → editar → Bajada', 'asp' ) ); ?>
						<?php endif; ?>
						<?php if ( $asp_i['publico'] || $asp_i['proxima'] ) : ?>
							<dl class="asp-inicio-servir__datos">
								<?php if ( $asp_i['publico'] ) : ?>
									<div><dt class="asp-label"><?php esc_html_e( 'Para', 'asp' ); ?></dt><dd><?php echo esc_html( $asp_i['publico'] ); ?></dd></div>
								<?php endif; ?>
								<?php if ( $asp_i['proxima'] ) : ?>
									<div><dt class="asp-label"><?php esc_html_e( 'Próxima', 'asp' ); ?></dt><dd><?php echo esc_html( $asp_i['proxima'] ); ?></dd></div>
								<?php endif; ?>
							</dl>
						<?php endif; ?>
						<?php if ( ! $asp_i['foto'] ) : ?>
							<?php asp_inicio_falta( __( 'una foto de un encuentro, sin texto encima', 'asp' ), __( 'Iniciativas → editar → Imagen', 'asp' ) ); ?>
						<?php endif; ?>
					</div>
				</li>
			<?php endforeach; ?>
		</ul>
		<a class="asp-cta-link" href="<?php echo esc_url( (string) get_post_type_archive_link( 'iniciativa' ) ); ?>"><?php esc_html_e( 'Conocé cada iniciativa', 'asp' ); ?></a>
	</div>
</section>
