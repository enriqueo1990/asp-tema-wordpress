<?php
/**
 * Caja "Predicaciones de este evento" en la pantalla del evento.
 *
 * Vincular una por una desde cada predicación sirve para la que se sube
 * suelta; para una conferencia entera (nueve plenarias, un panel) es un
 * trámite. Acá se marcan todas juntas: las predicaciones sin evento
 * aparecen agrupadas por fecha, y cada grupo se marca con un solo tilde.
 * Lo que se guarda es el mismo campo predicacion_evento de cada una.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registra la caja, plegada como la de detalles opcionales.
 *
 * @return void
 */
function asp_meta_box_evento_predicaciones(): void {
	add_meta_box( 'asp_evento_predicaciones', __( 'Predicaciones de este evento', 'asp' ), 'asp_render_evento_predicaciones', 'evento', 'normal', 'default' );
}
add_action( 'add_meta_boxes_evento', 'asp_meta_box_evento_predicaciones' );
add_filter( 'postbox_classes_evento_asp_evento_predicaciones', 'asp_plegar_opcional' );

/**
 * Predicaciones que se pueden vincular a un evento: las que no tienen
 * evento y las que ya son de este, agrupadas por fecha (la más nueva
 * primero). Las de otro evento no se ofrecen, para no moverlas sin querer.
 *
 * @param int $evento_id ID del evento.
 * @return array<string, WP_Post[]> Ymd => predicaciones.
 */
function asp_predicaciones_vinculables( int $evento_id ): array {
	$grupos = [];
	foreach ( asp_predicaciones() as $p ) {
		$actual = absint( get_post_meta( $p->ID, 'predicacion_evento', true ) );
		if ( $actual && $actual !== $evento_id ) {
			continue;
		}
		$grupos[ asp_predicacion_fecha_ymd( $p->ID ) ][] = $p;
	}
	krsort( $grupos );
	return $grupos;
}

/**
 * La caja.
 *
 * @param WP_Post $post Evento.
 * @return void
 */
function asp_render_evento_predicaciones( WP_Post $post ): void {
	$grupos = asp_predicaciones_vinculables( $post->ID );
	?>
	<input type="hidden" name="asp_evento_predicaciones_caja" value="1">
	<p class="asp-campo__intro"><?php esc_html_e( 'Marcá las predicaciones que se dieron en este evento. Van a aparecer en la ficha del evento y agrupadas con su nombre en Recursos. Están ordenadas por la fecha en que se cargaron: los mensajes de una misma conferencia suelen quedar juntos, y el tilde del grupo los marca a todos.', 'asp' ); ?></p>
	<?php if ( empty( $grupos ) ) : ?>
		<p class="asp-campo__ayuda"><?php esc_html_e( 'No hay predicaciones sin evento para vincular.', 'asp' ); ?></p>
		<?php
		return;
	endif;
	?>
	<div class="asp-vincular">
		<?php
		foreach ( $grupos as $ymd => $predicaciones ) :
			$partes   = asp_fecha_partes( (string) $ymd );
			$titulo   = $partes ? sprintf( __( '%1$d de %2$s de %3$d', 'asp' ), $partes['dia'], asp_nombre_mes( $partes['mes'] ), $partes['anio'] ) : __( 'Sin fecha', 'asp' );
			$marcadas = [];
			foreach ( $predicaciones as $p ) {
				if ( absint( get_post_meta( $p->ID, 'predicacion_evento', true ) ) === $post->ID ) {
					$marcadas[] = $p->ID;
				}
			}
			$total    = count( $predicaciones );
			$grupo_id = 'asp-vincular-' . $ymd;
			?>
			<details class="asp-vincular__grupo"<?php echo $marcadas ? ' open' : ''; ?>>
				<summary>
					<strong><?php echo esc_html( $titulo ); ?></strong>
					<span class="asp-campo__ayuda">
						<?php
						echo esc_html(
							$marcadas
								/* translators: 1: marcadas, 2: total del grupo */
								? sprintf( __( '%1$d de %2$d marcadas', 'asp' ), count( $marcadas ), $total )
								/* translators: %d: predicaciones del grupo */
								: sprintf( _n( '%d predicación', '%d predicaciones', $total, 'asp' ), $total )
						);
						?>
					</span>
				</summary>
				<div class="asp-vincular__lista" data-asp-grupo>
					<label for="<?php echo esc_attr( $grupo_id ); ?>" class="asp-vincular__todas">
						<input type="checkbox" id="<?php echo esc_attr( $grupo_id ); ?>" data-asp-marcar-grupo<?php checked( count( $marcadas ) === $total ); ?>>
						<?php
						/* translators: %d: predicaciones del grupo */
						echo esc_html( sprintf( _n( 'Marcar la predicación de este día', 'Marcar las %d de este día', $total, 'asp' ), $total ) );
						?>
					</label>
					<?php foreach ( $predicaciones as $p ) : ?>
						<label for="asp-pred-<?php echo esc_attr( (string) $p->ID ); ?>">
							<input type="checkbox" id="asp-pred-<?php echo esc_attr( (string) $p->ID ); ?>" name="asp_evento_predicaciones[]" value="<?php echo esc_attr( (string) $p->ID ); ?>"<?php checked( in_array( $p->ID, $marcadas, true ) ); ?>>
							<?php echo esc_html( get_the_title( $p ) ); ?>
						</label>
					<?php endforeach; ?>
				</div>
			</details>
		<?php endforeach; ?>
	</div>
	<?php
}

/**
 * Guarda el vínculo en cada predicación. Solo actúa si la caja estuvo en
 * el formulario; si no, desmarcaría todo.
 *
 * @param int $post_id ID del evento.
 * @return void
 */
function asp_guardar_evento_predicaciones( int $post_id ): void {
	if ( ! asp_puede_guardar( $post_id, 'evento' ) || '' === asp_post_texto( 'asp_evento_predicaciones_caja' ) ) {
		return;
	}
	$marcadas = asp_post_ids( 'asp_evento_predicaciones' );

	foreach ( asp_predicaciones_vinculables( $post_id ) as $predicaciones ) {
		foreach ( $predicaciones as $p ) {
			if ( ! current_user_can( 'edit_post', $p->ID ) ) {
				continue;
			}
			$actual = absint( get_post_meta( $p->ID, 'predicacion_evento', true ) );
			if ( in_array( $p->ID, $marcadas, true ) && $actual !== $post_id ) {
				update_post_meta( $p->ID, 'predicacion_evento', $post_id );
			} elseif ( ! in_array( $p->ID, $marcadas, true ) && $actual === $post_id ) {
				delete_post_meta( $p->ID, 'predicacion_evento' );
			}
		}
	}
}
add_action( 'save_post_evento', 'asp_guardar_evento_predicaciones', 20 );
