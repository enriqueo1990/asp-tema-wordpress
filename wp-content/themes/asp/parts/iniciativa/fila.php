<?php
/**
 * Bloque de una iniciativa en /iniciativas/. Args: post_id, numero.
 *
 * Fila compacta: arriba el nombre con la próxima fecha y las ediciones, al
 * costado el flyer en miniatura de la próxima edición o de la última
 * realizada (entero sobre la banda tonal), y debajo, a todo el ancho, qué es. Sin evento con flyer, la fila
 * queda solo con el texto.
 *
 * Compactada el 28-9-2026: con el flyer a todo el ancho cada iniciativa
 * ocupaba una pantalla y media en el teléfono.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

$asp_id      = (int) ( $args['post_id'] ?? get_the_ID() );
$asp_num     = max( 1, (int) ( $args['numero'] ?? 1 ) );
$asp_desc    = (string) get_post_meta( $asp_id, 'iniciativa_descripcion', true );
$asp_resumen = asp_iniciativa_ediciones( $asp_id );
$asp_prox    = $asp_resumen['proximo'];
$asp_vitrina = $asp_resumen['vitrina'];
$asp_titulo  = 'asp-iniciativa-' . $asp_id;
?>
<section class="asp-iniciativa-fila<?php echo $asp_vitrina ? '' : ' asp-iniciativa-fila--sin-vitrina'; ?>" aria-labelledby="<?php echo esc_attr( $asp_titulo ); ?>">
	<div class="asp-iniciativa-fila__cab">
		<h2 class="asp-iniciativa-fila__titulo" id="<?php echo esc_attr( $asp_titulo ); ?>">
			<span class="asp-iniciativa-fila__num" aria-hidden="true"><?php echo esc_html( asp_romano( $asp_num ) ); ?></span>
			<a href="<?php echo esc_url( get_permalink( $asp_id ) ); ?>"><?php echo esc_html( get_the_title( $asp_id ) ); ?></a>
		</h2>
		<?php if ( $asp_resumen['ediciones'] || $asp_prox ) : ?>
			<p class="asp-iniciativa-fila__datos">
				<?php if ( $asp_prox ) : ?>
					<span class="asp-iniciativa-fila__proxima"><?php
						echo esc_html(
							'en_curso' === asp_evento_estado( $asp_prox->ID )
								/* translators: %s: fecha de la edición en curso */
								? sprintf( __( 'En curso: %s', 'asp' ), asp_evento_fecha_texto( $asp_prox->ID ) )
								/* translators: %s: fecha de la próxima edición */
								: sprintf( __( 'Próxima: %s', 'asp' ), asp_evento_fecha_texto( $asp_prox->ID ) )
						);
					?></span>
				<?php endif; ?>
				<?php if ( $asp_resumen['ediciones'] > 1 && $asp_resumen['desde'] ) : ?>
					<span><?php
						/* translators: 1: cantidad de ediciones, 2: año de la primera */
						echo esc_html( sprintf( __( '%1$d ediciones desde %2$d', 'asp' ), $asp_resumen['ediciones'], $asp_resumen['desde'] ) );
					?></span>
				<?php endif; ?>
			</p>
		<?php endif; ?>
	</div>
	<?php if ( $asp_vitrina ) :
		$asp_es_prox = $asp_prox && $asp_prox->ID === $asp_vitrina->ID;
		if ( $asp_es_prox ) {
			$asp_rotulo = 'en_curso' === asp_evento_estado( $asp_vitrina->ID ) ? __( 'En curso', 'asp' ) : __( 'Próxima', 'asp' );
		} else {
			/* translators: %d: año de la última edición */
			$asp_rotulo = sprintf( __( 'Última, %d', 'asp' ), asp_evento_anio( $asp_vitrina->ID ) );
		}
		?>
		<a class="asp-iniciativa-fila__vitrina" href="<?php echo esc_url( get_permalink( $asp_vitrina ) ); ?>">
			<?php
			get_template_part(
				'parts/evento/flyer',
				null,
				[
					'post_id' => $asp_vitrina->ID,
					'tamano'  => 'asp-flyer-card',
					'alt'     => '',
					'sizes'   => '(min-width: 1024px) 144px, 104px',
				]
			);
			?>
			<span class="asp-iniciativa-fila__rotulo"><?php echo esc_html( $asp_rotulo ); ?><span class="screen-reader-text">: <?php echo esc_html( get_the_title( $asp_vitrina ) ); ?></span></span>
		</a>
	<?php endif; ?>
	<?php if ( $asp_desc ) : ?>
		<div class="asp-iniciativa-fila__desc"><?php echo wp_kses_post( wpautop( $asp_desc ) ); ?></div>
	<?php endif; ?>
</section>
