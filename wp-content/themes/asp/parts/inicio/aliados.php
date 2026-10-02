<?php
/**
 * Aliados del ministerio (2-10-2026): una franja de logos después de la
 * agenda, porque son quienes organizan los eventos junto con el ministerio.
 * Solo aliados con logo; cada logo lleva al sitio del aliado si está
 * cargado. Sin ninguno con logo, no hay franja. Ver asp_inicio_aliados().
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

$asp_aliados = asp_inicio_aliados();
if ( empty( $asp_aliados['con_logo'] ) && empty( $asp_aliados['sin_logo'] ) ) {
	return;
}
?>
<section class="asp-inicio-aliados" aria-labelledby="inicio-aliados">
	<div class="asp-container">
		<div class="asp-inicio-aliados__fila">
			<h2 id="inicio-aliados" class="asp-label"><?php esc_html_e( 'Aliados', 'asp' ); ?></h2>
			<?php if ( ! empty( $asp_aliados['con_logo'] ) ) : ?>
				<ul class="asp-inicio-aliados__lista">
					<?php foreach ( $asp_aliados['con_logo'] as $asp_a ) : ?>
						<li<?php echo $asp_a['proporcion'] ? ' style="--logo-proporcion: ' . esc_attr( (string) $asp_a['proporcion'] ) . '"' : ''; ?>>
							<?php if ( $asp_a['url'] ) : ?>
								<a class="asp-inicio-aliados__enlace" href="<?php echo esc_url( $asp_a['url'] ); ?>" target="_blank" rel="noopener"><?php echo $asp_a['logo']; // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo asp_aviso_pestana(); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
							<?php else : ?>
								<?php echo $asp_a['logo']; // phpcs:ignore WordPress.Security.EscapeOutput ?>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
			<?php if ( ! empty( $asp_aliados['sin_logo'] ) ) : ?>
				<?php asp_inicio_falta( sprintf( /* translators: %s: nombres de aliados */ __( 'el logo de %s (no aparecen en la franja hasta tenerlo)', 'asp' ), implode( ', ', $asp_aliados['sin_logo'] ) ), __( 'Aliados → cada aliado → Logo', 'asp' ) ); ?>
			<?php endif; ?>
		</div>
	</div>
</section>
