<?php
/**
 * Formas de participar: una grilla de tarjetas con la misma forma (título,
 * texto o lista, y acciones). Las tarjetas salen de asp_inicio_participar();
 * una sin datos no entra y la grilla se reacomoda sola, así que sumar
 * «Donar» más adelante es agregar una entrada ahí.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

$asp_tarjetas = asp_inicio_participar();
$asp_faltan   = asp_inicio_participar_faltan();
if ( empty( $asp_tarjetas ) && ! current_user_can( 'edit_theme_options' ) ) {
	return;
}
?>
<section class="asp-inicio-participar" aria-labelledby="inicio-participar">
	<div class="asp-container">
		<h2 id="inicio-participar" class="asp-seccion__titulo"><?php esc_html_e( 'Formas de participar', 'asp' ); ?></h2>
		<?php if ( ! empty( $asp_tarjetas ) ) : ?>
			<ul class="asp-inicio-participar__grilla">
				<?php foreach ( $asp_tarjetas as $asp_clave => $asp_t ) : ?>
					<li class="asp-inicio-participar__tarjeta asp-inicio-participar__tarjeta--<?php echo esc_attr( $asp_clave ); ?>">
						<h3 class="asp-inicio-participar__titulo"><?php echo esc_html( $asp_t['titulo'] ); ?></h3>
						<?php if ( $asp_t['texto'] ) : ?>
							<p class="asp-inicio-participar__texto"><?php echo esc_html( $asp_t['texto'] ); ?></p>
						<?php endif; ?>
						<?php if ( ! empty( $asp_t['lista'] ) ) : ?>
							<ul class="asp-inicio-participar__lista">
								<?php foreach ( $asp_t['lista'] as $asp_item ) : ?>
									<li><?php echo esc_html( $asp_item ); ?></li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
						<?php if ( ! empty( $asp_t['acciones'] ) ) : ?>
							<div class="asp-inicio-participar__acciones">
								<?php foreach ( $asp_t['acciones'] as $asp_a ) : ?>
									<a class="asp-cta-link" href="<?php echo esc_url( $asp_a['url'] ); ?>"<?php echo $asp_a['externo'] ? ' target="_blank" rel="noopener"' : ''; ?>><?php echo esc_html( $asp_a['texto'] ); ?><?php echo $asp_a['externo'] ? asp_aviso_pestana() : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
		<?php /* TODO: suscripción, WhatsApp, kit y motivos de oración los carga el ministerio. */ ?>
		<?php foreach ( $asp_faltan as $asp_f ) : ?>
			<?php asp_inicio_falta( $asp_f['falta'], $asp_f['donde'] ); ?>
		<?php endforeach; ?>
	</div>
</section>
