<?php
/**
 * Programa por día. Args: post_id. Se autooculta si está vacío.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

$asp_id     = (int) ( $args['post_id'] ?? get_the_ID() );
$asp_grupos = asp_evento_programa( $asp_id );

if ( empty( $asp_grupos ) ) {
	return;
}
$asp_varios_dias = count( $asp_grupos ) > 1;
?>
<div class="asp-bloque">
	<span class="asp-label"><?php esc_html_e( 'Programa', 'asp' ); ?></span>
	<div class="asp-programa">
		<?php foreach ( $asp_grupos as $asp_dia => $asp_filas ) :
			$asp_p        = asp_fecha_partes( (string) $asp_dia );
			$asp_dia_text = $asp_p ? sprintf( __( '%1$d de %2$s', 'asp' ), $asp_p['dia'], asp_nombre_mes( $asp_p['mes'] ) ) : '';
			foreach ( $asp_filas as $asp_i => $asp_fila ) :
				?>
				<div class="asp-programa__fila">
					<span class="asp-programa__dia"><?php echo ( 0 === $asp_i && $asp_varios_dias ) ? esc_html( $asp_dia_text ) : ''; ?></span>
					<span class="asp-programa__hora"><?php echo esc_html( $asp_fila['hora'] ); ?></span>
					<span>
						<span class="asp-programa__titulo"><?php echo esc_html( $asp_fila['titulo'] ); ?></span>
						<?php if ( $asp_fila['orador'] ) : ?>
							<span class="asp-programa__orador"><?php echo esc_html( $asp_fila['orador'] ); ?></span>
						<?php endif; ?>
					</span>
				</div>
			<?php endforeach;
		endforeach; ?>
	</div>
</div>
