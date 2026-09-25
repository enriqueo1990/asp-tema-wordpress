<?php
/**
 * Pie del sitio: marca, navegación, contacto o redes, y una línea legal.
 *
 * La bajada es la descripción del sitio (Ajustes → Generales), no la misión:
 * la misión ya está en el inicio y repetirla palabra por palabra en el pie
 * se notaba.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

$asp_email  = asp_email();
$asp_redes  = asp_redes();
$asp_bajada = (string) get_bloginfo( 'description' );
?>
<footer class="asp-footer">
	<div class="asp-container asp-footer__inner">
		<div class="asp-footer__marca">
			<?php asp_logo( 'asp-footer__logo' ); ?>
			<?php if ( $asp_bajada ) : ?><p class="asp-footer__mision"><?php echo esc_html( $asp_bajada ); ?></p><?php endif; ?>
			<p class="asp-label"><?php esc_html_e( 'Argentina · Estados Unidos', 'asp' ); ?></p>
		</div>
		<nav aria-label="<?php esc_attr_e( 'Navegación del pie', 'asp' ); ?>">
			<span class="asp-label"><?php esc_html_e( 'Sitio', 'asp' ); ?></span>
			<?php asp_menu( has_nav_menu( 'pie' ) ? 'pie' : 'principal' ); ?>
		</nav>
		<?php $asp_form = asp_url_pagina_plantilla( 'templates/page-contacto.php' ); ?>
		<?php if ( $asp_email || $asp_form || ! empty( $asp_redes ) ) : ?>
			<div class="asp-footer__contacto">
				<span class="asp-label"><?php esc_html_e( 'Contacto', 'asp' ); ?></span>
				<?php if ( $asp_email ) : ?>
					<a class="asp-footer__email" href="mailto:<?php echo esc_attr( $asp_email ); ?>"><?php echo esc_html( $asp_email ); ?></a>
				<?php elseif ( $asp_form ) : ?>
					<a class="asp-footer__email" href="<?php echo esc_url( $asp_form ); ?>"><?php esc_html_e( 'Escribinos por el formulario', 'asp' ); ?></a>
				<?php endif; ?>
				<?php if ( ! empty( $asp_redes ) ) : ?>
					<ul class="asp-footer__redes">
						<?php foreach ( $asp_redes as $asp_nombre => $asp_url ) : ?>
							<li><a href="<?php echo esc_url( $asp_url ); ?>" rel="me noopener" target="_blank"><?php echo esc_html( $asp_nombre ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
	<div class="asp-container asp-footer__legal">
		<span><?php
			/* translators: %1$s: año, %2$s: nombre del sitio */
			echo esc_html( sprintf( __( '© %1$s %2$s', 'asp' ), wp_date( 'Y' ), get_bloginfo( 'name' ) ) );
		?></span>
	</div>
</footer>
