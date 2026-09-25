<?php
/**
 * Template Name: Contacto
 * Template Post Type: page
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$asp_email  = asp_email();
	$asp_redes  = asp_redes();
	$asp_estado = asp_contacto_estado();
	?>
	<div class="asp-container asp-section">
		<div class="asp-column asp-section__inner asp-section__inner--loose">
			<header class="asp-stack">
				<h1 class="asp-pagina__titulo"><?php the_title(); ?></h1>
				<?php if ( get_the_content() ) : ?><div class="asp-prose"><?php the_content(); ?></div><?php endif; ?>
			</header>

			<?php if ( $asp_email || $asp_redes ) : ?>
				<div class="asp-stack asp-stack--3">
					<?php if ( $asp_email ) : ?>
						<div class="asp-row"><span class="asp-label"><?php esc_html_e( 'Email', 'asp' ); ?></span><a href="mailto:<?php echo esc_attr( $asp_email ); ?>"><?php echo esc_html( $asp_email ); ?></a></div>
					<?php endif; ?>
					<?php if ( $asp_redes ) : ?>
						<div class="asp-footer__redes">
							<?php foreach ( $asp_redes as $asp_nombre => $asp_url ) : ?>
								<a href="<?php echo esc_url( $asp_url ); ?>" rel="me noopener" target="_blank"><?php echo esc_html( $asp_nombre ); ?></a>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<?php if ( $asp_estado ) : ?>
				<p class="asp-caja <?php echo 'ok' === $asp_estado['tipo'] ? 'asp-caja--surface' : ''; ?>" role="status"><?php echo esc_html( $asp_estado['texto'] ); ?></p>
			<?php endif; ?>

			<form class="asp-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="asp_contacto">
				<input type="hidden" name="asp_t" value="<?php echo esc_attr( (string) time() ); ?>">
				<?php wp_nonce_field( 'asp_contacto', 'asp_contacto_nonce' ); ?>
				<p class="visually-hidden" aria-hidden="true"><label for="sitio_web"><?php esc_html_e( 'Dejá este campo vacío', 'asp' ); ?></label><input type="text" id="sitio_web" name="sitio_web" tabindex="-1" autocomplete="off"></p>
				<div class="asp-stack asp-stack--2"><label for="nombre"><?php esc_html_e( 'Nombre', 'asp' ); ?></label><input type="text" id="nombre" name="nombre" required autocomplete="name"></div>
				<div class="asp-stack asp-stack--2"><label for="email"><?php esc_html_e( 'Email', 'asp' ); ?></label><input type="email" id="email" name="email" required autocomplete="email"></div>
				<div class="asp-stack asp-stack--2"><label for="mensaje"><?php esc_html_e( 'Mensaje', 'asp' ); ?></label><textarea id="mensaje" name="mensaje" rows="6" required minlength="10"></textarea></div>
				<button class="asp-btn asp-btn--inline" type="submit"><?php esc_html_e( 'Enviar', 'asp' ); ?></button>
			</form>
		</div>
	</div>
<?php
endwhile;
get_footer();
