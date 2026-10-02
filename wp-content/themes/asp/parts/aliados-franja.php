<?php
/**
 * Franja de aliados antes del pie (inicio y Nosotros, 2-10-2026): el rótulo
 * y una fila de logos, cada uno con link al sitio del aliado. Solo aliados
 * con logo; sin ninguno, no hay franja. Ver asp_aliados_franja().
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

$asp_aliados = asp_aliados_franja();
if ( empty( $asp_aliados['con_logo'] ) && empty( $asp_aliados['sin_logo'] ) ) {
	return;
}
?>
<section class="asp-aliados-franja" aria-labelledby="aliados-franja">
	<div class="asp-container asp-aliados-franja__fila">
		<h2 id="aliados-franja" class="asp-label"><?php esc_html_e( 'Aliados', 'asp' ); ?></h2>
		<?php if ( ! empty( $asp_aliados['con_logo'] ) ) : ?>
			<ul class="asp-aliados-franja__lista">
				<?php foreach ( $asp_aliados['con_logo'] as $asp_a ) : ?>
					<li<?php echo $asp_a['proporcion'] ? ' style="--logo-proporcion: ' . esc_attr( (string) $asp_a['proporcion'] ) . '"' : ''; ?>>
						<?php if ( $asp_a['url'] ) : ?>
							<a class="asp-aliados-franja__enlace" href="<?php echo esc_url( $asp_a['url'] ); ?>" target="_blank" rel="noopener"><?php echo $asp_a['logo']; // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo asp_aviso_pestana(); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
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
</section>
