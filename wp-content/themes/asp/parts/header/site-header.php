<?php
/**
 * Cabecera: logo, navegación, acceso al próximo evento y menú móvil a
 * pantalla completa. En la portada con fotografía arranca transparente y
 * se vuelve sólida al hacer scroll (clase is-scrolled desde app.js).
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

$asp_proximo = asp_evento_destacado();
$asp_fecha   = $asp_proximo ? asp_evento_fecha_display( $asp_proximo->ID ) : null;
?>
<a class="asp-skip" href="#contenido"><?php esc_html_e( 'Ir al contenido', 'asp' ); ?></a>
<header class="asp-header" data-asp-header>
	<div class="asp-container asp-header__inner">
		<?php asp_logo(); ?>

		<nav id="asp-nav" class="asp-nav" aria-label="<?php esc_attr_e( 'Navegación principal', 'asp' ); ?>">
			<?php asp_menu( 'principal' ); ?>
			<?php if ( $asp_proximo ) : ?>
				<a class="asp-nav__proximo" href="<?php echo esc_url( get_permalink( $asp_proximo ) ); ?>">
					<span class="asp-label"><?php esc_html_e( 'Próximo evento', 'asp' ); ?></span>
					<span class="asp-nav__proximo-titulo"><?php echo esc_html( get_the_title( $asp_proximo ) ); ?></span>
					<?php if ( $asp_fecha && $asp_fecha['dias'] ) : ?>
						<span class="asp-nav__proximo-fecha"><?php echo esc_html( str_replace( ' · ', '–', $asp_fecha['dias'] ) . ' ' . $asp_fecha['mes'] ); ?></span>
					<?php endif; ?>
				</a>
			<?php endif; ?>
		</nav>

		<div class="asp-header__derecha">
			<?php if ( $asp_proximo ) : ?>
				<a class="asp-header__cta" href="<?php echo esc_url( get_permalink( $asp_proximo ) ); ?>">
					<span class="asp-header__cta-label"><?php esc_html_e( 'Próximo evento', 'asp' ); ?></span>
					<?php if ( $asp_fecha && $asp_fecha['dias'] ) : ?>
						<span class="asp-header__cta-fecha"><?php echo esc_html( str_replace( ' · ', '–', $asp_fecha['dias'] ) . ' ' . mb_substr( $asp_fecha['mes'], 0, 3 ) ); ?></span>
					<?php endif; ?>
				</a>
			<?php endif; ?>
			<button class="asp-header__toggle" type="button" aria-expanded="false" aria-controls="asp-nav" data-asp-toggle>
				<span class="asp-header__toggle-abrir"><?php esc_html_e( 'Menú', 'asp' ); ?></span>
				<span class="asp-header__toggle-cerrar"><?php esc_html_e( 'Cerrar', 'asp' ); ?></span>
			</button>
		</div>
	</div>
</header>
