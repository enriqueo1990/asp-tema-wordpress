<?php
/**
 * Hero del inicio nuevo: el evento con inscripción abierta, en orden de
 * importancia (priorizado el 29-9-2026): tipo y título, fecha y lugar,
 * quiénes enseñan con foto chica, una línea secundaria con para quién es y
 * el cupo, y el botón. La identidad del ministerio es el H1, oculto a la vista. El precio y los detalles quedan en la ficha.
 * Sin evento abierto, solo la identidad y el link a Eventos. Sin foto,
 * fondo oscuro pleno.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

$asp_evento = asp_evento_hero();
$asp_ident  = asp_inicio_identidad();
$asp_nombre = get_bloginfo( 'name' );
$asp_foto   = asp_inicio_foto_id() ? (string) wp_get_attachment_image( asp_inicio_foto_id(), 'full', false, [ 'class' => 'asp-portada__foto', 'loading' => 'eager', 'fetchpriority' => 'high', 'alt' => '' ] ) : '';
$asp_d      = $asp_evento ? asp_inicio_hero_datos( $asp_evento->ID ) : null;
?>
<section class="asp-portada asp-inicio-hero<?php echo $asp_d ? ' asp-inicio-hero--evento' : ''; ?>" aria-labelledby="<?php echo $asp_d ? 'inicio-evento' : 'inicio-identidad'; ?>">
	<?php echo $asp_foto; // phpcs:ignore WordPress.Security.EscapeOutput ?>
	<div class="asp-portada__velo">
		<div class="asp-container asp-portada__inner asp-inicio-hero__inner">
			<?php /* Con evento, la identidad no se ve (29-9-2026: el hero arranca por el
			   evento), pero sigue siendo el único H1 para lectores de pantalla y
			   buscadores. Sin evento, es el titular. */ ?>
			<h1 id="inicio-identidad" class="<?php echo $asp_d ? 'visually-hidden' : 'asp-inicio-hero__lema'; ?>">
				<?php if ( '' !== $asp_ident ) : ?>
					<span class="visually-hidden"><?php echo esc_html( $asp_nombre . ': ' ); ?></span><?php echo esc_html( $asp_ident ); ?>
				<?php else : ?>
					<?php echo esc_html( $asp_nombre ); ?>
				<?php endif; ?>
			</h1>

			<?php if ( $asp_d ) : ?>
				<div class="asp-inicio-hero__evento<?php echo ! empty( $asp_d['oradores'] ) ? ' asp-inicio-hero__evento--con-oradores' : ''; ?>">
					<div class="asp-inicio-hero__principal">
						<h2 id="inicio-evento" class="asp-inicio-hero__titulo">
							<a href="<?php echo esc_url( $asp_d['url'] ); ?>">
								<?php if ( $asp_d['tipo'] ) : ?>
									<span class="asp-inicio-hero__tipo"><?php echo esc_html( $asp_d['tipo'] ); ?></span><span class="visually-hidden"> · </span>
								<?php endif; ?>
								<span class="asp-inicio-hero__nombre"><?php echo esc_html( $asp_d['titulo'] ); ?></span>
							</a>
						</h2>

						<?php if ( $asp_d['fecha'] || $asp_d['lugar'] ) : ?>
							<p class="asp-inicio-hero__datos"><?php if ( $asp_d['fecha'] ) : ?><time datetime="<?php echo esc_attr( $asp_d['iso'] ); ?>"><?php echo esc_html( $asp_d['fecha'] ); ?></time><?php endif; ?><?php echo ( $asp_d['fecha'] && $asp_d['lugar'] ) ? ' · ' : ''; ?><?php echo esc_html( $asp_d['lugar'] ); ?></p>
						<?php endif; ?>

						<?php if ( $asp_d['publico'] || $asp_d['cupo'] ) : ?>
							<p class="asp-inicio-hero__secundario">
								<?php if ( $asp_d['publico'] ) : ?>
									<span><span class="asp-label"><?php esc_html_e( 'Para', 'asp' ); ?></span> <?php echo esc_html( $asp_d['publico'] ); ?></span>
								<?php endif; ?>
								<?php if ( $asp_d['cupo'] ) : ?>
									<span><span class="asp-label"><?php esc_html_e( 'Cupo', 'asp' ); ?></span> <?php echo esc_html( $asp_d['cupo'] ); ?></span>
								<?php endif; ?>
							</p>
						<?php endif; ?>
						<?php if ( ! $asp_d['publico'] && $asp_d['tipo'] ) : ?>
							<?php /* TODO: «Para quién» de cada iniciativa lo define el ministerio. */ ?>
							<?php asp_inicio_falta( __( 'para quién es este tipo de evento', 'asp' ), sprintf( /* translators: %s: iniciativa */ __( 'Iniciativas → %s → Para quién', 'asp' ), $asp_d['tipo'] ) ); ?>
						<?php endif; ?>

						<?php /* El botón va pegado a la fecha: es lo que se viene a hacer. Los dos del mismo alto. */ ?>
						<div class="asp-inicio-hero__acciones">
							<a class="asp-btn asp-btn--invertido" href="<?php echo esc_url( $asp_d['registro'] ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Inscribirse', 'asp' ); ?><?php echo asp_aviso_pestana(); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
							<a class="asp-btn asp-inicio-hero__ficha" href="<?php echo esc_url( $asp_d['url'] ); ?>"><?php esc_html_e( 'Ver el evento', 'asp' ); ?></a>
						</div>
					</div>

					<?php /* Quiénes enseñan, en su propio bloque: a la derecha en escritorio, debajo de los botones en el teléfono. */ ?>
					<?php if ( ! empty( $asp_d['oradores'] ) ) : ?>
						<div class="asp-inicio-hero__oradores">
							<h3 class="asp-label" id="inicio-ensenan"><?php esc_html_e( 'Enseñan', 'asp' ); ?></h3>
							<ul aria-labelledby="inicio-ensenan">
								<?php /* Solo foto y nombre (29-9-2026): la iglesia está en la ficha de cada uno. */ ?>
								<?php foreach ( $asp_d['oradores'] as $asp_persona ) : ?>
									<li>
										<?php echo asp_persona_foto( $asp_persona->ID, 'asp-inicio-hero__avatar' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
										<span class="asp-inicio-hero__persona-nombre"><?php echo esc_html( get_the_title( $asp_persona ) ); ?></span>
									</li>
								<?php endforeach; ?>
							</ul>
						</div>
					<?php endif; ?>
				</div>
			<?php else : ?>
				<a class="asp-btn asp-btn--invertido" href="<?php echo esc_url( asp_url_eventos() ); ?>"><?php esc_html_e( 'Ver los eventos', 'asp' ); ?></a>
			<?php endif; ?>
		</div>
	</div>
</section>
