<?php
/**
 * Pie del sitio: oscuro, con la misión completa, navegación, redes y email.
 * El pie como segunda portada.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

$asp_email  = (string) get_theme_mod( 'asp_email', '' );
$asp_redes  = asp_redes();
$asp_mision = (string) get_theme_mod( 'asp_mision_texto', asp_mision_default() );
?>
<footer class="asp-footer">
	<div class="asp-container asp-footer__inner">
		<div class="asp-footer__marca">
			<?php asp_logo( 'asp-footer__logo' ); ?>
			<?php if ( $asp_mision ) : ?><p class="asp-footer__mision"><?php echo esc_html( $asp_mision ); ?></p><?php endif; ?>
			<p class="asp-label"><?php esc_html_e( 'Argentina · Estados Unidos', 'asp' ); ?></p>
		</div>
		<nav aria-label="<?php esc_attr_e( 'Navegación del pie', 'asp' ); ?>">
			<span class="asp-label"><?php esc_html_e( 'Sitio', 'asp' ); ?></span>
			<?php asp_menu( has_nav_menu( 'pie' ) ? 'pie' : 'principal' ); ?>
		</nav>
		<div class="asp-footer__contacto">
			<span class="asp-label"><?php esc_html_e( 'Contacto', 'asp' ); ?></span>
			<?php if ( $asp_email ) : ?><a href="mailto:<?php echo esc_attr( $asp_email ); ?>"><?php echo esc_html( $asp_email ); ?></a><?php endif; ?>
			<?php if ( ! empty( $asp_redes ) ) : ?>
				<div class="asp-footer__redes">
					<?php foreach ( $asp_redes as $asp_nombre => $asp_url ) : ?>
						<a href="<?php echo esc_url( $asp_url ); ?>" rel="me noopener" target="_blank"><?php echo esc_html( $asp_nombre ); ?></a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</footer>
