<?php
/**
 * Próximos eventos, sin el que ya está en el hero. Todas las filas con la
 * misma forma: bloque de fecha, tipo, título, ciudad y país, estado y una
 * miniatura (la foto del evento o de su iniciativa; nunca el flyer, que trae
 * el texto incrustado). Sin foto, un placeholder con la marca, así todas
 * las filas miden lo mismo. Sin eventos para mostrar, no hay sección.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

$asp_eventos = asp_inicio_proximos( 4 );
if ( empty( $asp_eventos ) ) {
	return;
}
?>
<section class="asp-inicio-agenda" aria-labelledby="inicio-agenda">
	<div class="asp-container">
		<div class="asp-inicio-agenda__cab">
			<h2 id="inicio-agenda" class="asp-seccion__titulo"><?php esc_html_e( 'Próximos eventos', 'asp' ); ?></h2>
			<a class="asp-cta-link" href="<?php echo esc_url( asp_url_eventos() ); ?>"><?php esc_html_e( 'Todos los eventos', 'asp' ); ?></a>
		</div>
		<ol class="asp-inicio-agenda__lista">
			<?php foreach ( $asp_eventos as $asp_post ) :
				$asp_id    = $asp_post->ID;
				$asp_fecha = asp_evento_fecha_grande( $asp_id );
				$asp_inic  = asp_evento_iniciativa( $asp_id );
				$asp_lugar = implode( ', ', array_filter( [ asp_evento_ciudad( $asp_id ), asp_evento_pais( $asp_id ) ] ) );
				$asp_foto  = asp_inicio_evento_foto( $asp_id );
				?>
				<li class="asp-inicio-agenda__item">
					<p class="asp-inicio-agenda__fecha">
						<time datetime="<?php echo esc_attr( asp_fecha_iso( (string) get_post_meta( $asp_id, 'evento_fecha_inicio', true ) ) ); ?>">
							<span class="asp-inicio-agenda__dias"><?php echo esc_html( $asp_fecha['dias'] ); ?></span>
							<span class="asp-inicio-agenda__mes"><?php echo esc_html( $asp_fecha['mes'] ); ?></span>
						</time>
					</p>
					<div class="asp-inicio-agenda__cuerpo">
						<?php if ( $asp_inic ) : ?>
							<span class="asp-label"><?php echo esc_html( get_the_title( $asp_inic ) ); ?></span>
						<?php endif; ?>
						<h3 class="asp-inicio-agenda__titulo"><a class="asp-inicio-agenda__enlace" href="<?php echo esc_url( get_permalink( $asp_id ) ); ?>"><?php echo esc_html( get_the_title( $asp_id ) ); ?></a></h3>
						<?php if ( $asp_lugar ) : ?>
							<p class="asp-inicio-agenda__lugar"><?php echo esc_html( $asp_lugar ); ?></p>
						<?php endif; ?>
						<?php get_template_part( 'parts/evento/badge', null, [ 'post_id' => $asp_id ] ); ?>
					</div>
					<span class="asp-inicio-agenda__visual<?php echo $asp_foto ? '' : ' asp-inicio-agenda__visual--sin-foto'; ?>" aria-hidden="true">
						<?php echo $asp_foto ?: asp_inicio_marca(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					</span>
				</li>
			<?php endforeach; ?>
		</ol>
	</div>
</section>
