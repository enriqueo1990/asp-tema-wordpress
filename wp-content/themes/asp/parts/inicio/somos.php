<?php
/**
 * Quiénes somos y la franja de confianza: el texto de la misión, la cita y
 * los enlaces, como en el inicio de antes; al lado los datos de
 * trayectoria y debajo el testimonio. Cada parte aparece solo si tiene
 * datos. El consejo pastoral no va acá: se muestra solo en Nosotros
 * (pedido del 29-9-2026).
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

$asp_mision   = (string) get_theme_mod( 'asp_mision_texto', asp_mision_default() );
$asp_nosotros = asp_url_pagina_plantilla( 'templates/page-nosotros.php' );
$asp_af       = asp_afirmaciones();
$asp_cifras   = asp_inicio_cifras();
$asp_testim   = asp_inicio_testimonio();
?>
<section class="asp-inicio-somos" aria-labelledby="inicio-somos">
	<div class="asp-container asp-inicio-somos__grilla">
		<div class="asp-inicio-somos__texto">
			<h2 id="inicio-somos" class="asp-seccion__titulo"><?php esc_html_e( 'Quiénes somos', 'asp' ); ?></h2>
			<?php if ( $asp_mision ) : ?>
				<p class="asp-editorial__texto"><?php echo esc_html( $asp_mision ); ?></p>
			<?php endif; ?>
			<p class="asp-editorial__cita"><?php esc_html_e( '«Pero a este miraré: al que es humilde y contrito de espíritu, y que tiembla ante Mi palabra»', 'asp' ); ?> <span class="asp-label"><?php esc_html_e( 'Isaías 66:2', 'asp' ); ?></span></p>
			<?php if ( $asp_nosotros ) : ?>
				<div class="asp-row asp-row--enlaces">
					<a class="asp-cta-link" href="<?php echo esc_url( $asp_nosotros ); ?>"><?php esc_html_e( 'Conocé el ministerio', 'asp' ); ?></a>
					<?php if ( ! empty( $asp_af['articulos'] ) ) : ?>
						<a class="asp-cta-link" href="<?php echo esc_url( $asp_nosotros . '#afirmaciones-y-negaciones' ); ?>"><?php esc_html_e( 'Lo que creemos', 'asp' ); ?></a>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>

		<?php /* TODO: los datos de trayectoria los aporta el ministerio. */ ?>
		<?php if ( ! empty( $asp_cifras ) ) : ?>
			<ul class="asp-inicio-cifras" aria-label="<?php esc_attr_e( 'Trayectoria', 'asp' ); ?>">
				<?php foreach ( $asp_cifras as $asp_cifra ) : ?>
					<li>
						<span class="asp-inicio-cifras__numero"><?php echo esc_html( $asp_cifra['numero'] ); ?></span>
						<span class="asp-inicio-cifras__texto"><?php echo esc_html( $asp_cifra['texto'] ); ?></span>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php else : ?>
			<div class="asp-inicio-cifras"><?php asp_inicio_falta( __( 'datos de trayectoria (años de ministerio, eventos realizados, países)', 'asp' ), __( 'Personalizar → Inicio', 'asp' ) ); ?></div>
		<?php endif; ?>

		<?php /* TODO: el testimonio lo aporta el ministerio (Personalizar → Ante Su Palabra → «Testimonio»). No inventar uno. */ ?>
		<?php if ( $asp_testim ) : ?>
			<figure class="asp-testimonio__figura asp-inicio-somos__testimonio">
				<blockquote class="asp-testimonio__cita"><p><?php echo esc_html( $asp_testim['texto'] ); ?></p></blockquote>
				<figcaption class="asp-testimonio__autor">
					<span class="asp-testimonio__nombre"><?php echo esc_html( $asp_testim['nombre'] ); ?></span>
					<?php if ( $asp_testim['iglesia'] ) : ?><span class="asp-testimonio__iglesia"><?php echo esc_html( $asp_testim['iglesia'] ); ?></span><?php endif; ?>
				</figcaption>
			</figure>
		<?php endif; ?>
	</div>
</section>
