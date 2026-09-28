<?php
/**
 * Template Name: Contacto
 * Template Post Type: page
 *
 * Portada con foto de un encuentro y el título. Debajo, el mail en grande,
 * el formulario en su propio panel, el próximo evento —muchas consultas son
 * por eso y la ficha ya tiene sede, programa e inscripción— y las redes.
 * En el teléfono van en ese orden, para que el formulario asome pronto; en
 * escritorio el panel pasa a la derecha y se monta sobre la foto.
 *
 * Rehecha el 28-9-2026: antes era título, una fila de mail, las redes y el
 * formulario, todo en la misma columna y sin imagen.
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
	$asp_previo = asp_contacto_borrador();
	$asp_faltan = asp_contacto_faltan();
	$asp_foto   = asp_contacto_foto();
	$asp_evento = asp_evento_destacado();
	?>
	<section class="asp-contacto-portada<?php echo $asp_foto ? ' asp-contacto-portada--con-foto' : ''; ?>">
		<?php echo $asp_foto; // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<div class="asp-contacto-portada__velo">
			<div class="asp-container asp-contacto-portada__inner">
				<h1 class="asp-contacto-portada__titulo"><?php the_title(); ?></h1>
				<?php if ( get_the_content() ) : ?>
					<div class="asp-contacto-portada__texto"><?php the_content(); ?></div>
				<?php else : ?>
					<p class="asp-contacto-portada__texto"><?php esc_html_e( 'Escribinos por el formulario o directo al mail.', 'asp' ); ?></p>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<div class="asp-container asp-contacto">
		<?php if ( $asp_email ) : ?>
			<div class="asp-contacto__bloque asp-contacto__mail">
				<span class="asp-label"><?php esc_html_e( 'Mail', 'asp' ); ?></span>
				<a class="asp-contacto__email" href="mailto:<?php echo esc_attr( $asp_email ); ?>"><?php echo esc_html( $asp_email ); ?></a>
			</div>
		<?php endif; ?>

		<div class="asp-contacto__panel" id="escribinos">
			<h2 class="asp-contacto__panel-titulo"><?php esc_html_e( 'Mandanos un mensaje', 'asp' ); ?></h2>

			<?php if ( $asp_estado ) : ?>
				<p class="asp-contacto__estado asp-contacto__estado--<?php echo esc_attr( $asp_estado['tipo'] ); ?>" role="status"><?php echo esc_html( $asp_estado['texto'] ); ?></p>
			<?php endif; ?>

			<form class="asp-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="asp_contacto">
				<input type="hidden" name="asp_t" value="<?php echo esc_attr( asp_contacto_marca() ); ?>">
				<?php wp_nonce_field( 'asp_contacto', 'asp_contacto_nonce' ); ?>
				<p class="visually-hidden" aria-hidden="true"><label for="sitio_web"><?php esc_html_e( 'Dejá este campo vacío', 'asp' ); ?></label><input type="text" id="sitio_web" name="sitio_web" tabindex="-1" autocomplete="off"></p>
				<div class="asp-contacto__fila">
					<div class="asp-stack asp-stack--2"><label for="nombre"><?php esc_html_e( 'Nombre', 'asp' ); ?></label><input type="text" id="nombre" name="nombre" required maxlength="<?php echo (int) ASP_CONTACTO_LARGOS['nombre']; ?>" autocomplete="name" value="<?php echo esc_attr( $asp_previo['nombre'] ); ?>"<?php echo in_array( 'nombre', $asp_faltan, true ) ? ' aria-invalid="true"' : ''; ?>></div>
					<div class="asp-stack asp-stack--2"><label for="email"><?php esc_html_e( 'Mail', 'asp' ); ?></label><input type="email" id="email" name="email" required maxlength="<?php echo (int) ASP_CONTACTO_LARGOS['email']; ?>" autocomplete="email" value="<?php echo esc_attr( $asp_previo['email'] ); ?>"<?php echo in_array( 'email', $asp_faltan, true ) ? ' aria-invalid="true"' : ''; ?>></div>
				</div>
				<div class="asp-stack asp-stack--2"><label for="mensaje"><?php esc_html_e( 'Mensaje', 'asp' ); ?></label><textarea id="mensaje" name="mensaje" rows="6" required minlength="10" maxlength="<?php echo (int) ASP_CONTACTO_LARGOS['mensaje']; ?>"<?php echo in_array( 'mensaje', $asp_faltan, true ) ? ' aria-invalid="true"' : ''; ?>><?php echo esc_textarea( $asp_previo['mensaje'] ); ?></textarea></div>
				<button class="asp-btn asp-contacto__enviar" type="submit"><?php esc_html_e( 'Enviar mensaje', 'asp' ); ?></button>
			</form>
		</div>

		<?php if ( $asp_evento ) : ?>
			<div class="asp-contacto__bloque asp-contacto__evento">
				<span class="asp-label"><?php esc_html_e( '¿Consultás por el próximo evento?', 'asp' ); ?></span>
				<a class="asp-contacto__evento-titulo" href="<?php echo esc_url( get_permalink( $asp_evento ) ); ?>"><?php echo esc_html( get_the_title( $asp_evento ) ); ?></a>
				<span class="asp-contacto__evento-dato"><?php echo esc_html( implode( ' · ', array_filter( [ asp_evento_fecha_texto( $asp_evento->ID ), asp_evento_ciudad( $asp_evento->ID ) ] ) ) ); ?></span>
				<span class="asp-contacto__evento-ayuda"><?php esc_html_e( 'Todo lo que ya sabemos del evento está en su ficha.', 'asp' ); ?></span>
			</div>
		<?php endif; ?>

		<?php if ( $asp_redes ) : ?>
			<div class="asp-contacto__bloque">
				<span class="asp-label"><?php esc_html_e( 'Redes', 'asp' ); ?></span>
				<ul class="asp-contacto__redes">
					<?php foreach ( $asp_redes as $asp_nombre => $asp_url ) : ?>
						<li><a href="<?php echo esc_url( $asp_url ); ?>" rel="me noopener" target="_blank"><?php echo esc_html( $asp_nombre ); ?><span class="asp-contacto__flecha" aria-hidden="true">↗</span><?php echo asp_aviso_pestana(); // phpcs:ignore WordPress.Security.EscapeOutput ?></a></li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>
	</div>
<?php
endwhile;
get_footer();
