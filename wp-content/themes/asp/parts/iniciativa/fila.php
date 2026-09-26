<?php
/**
 * Bloque de una iniciativa en /iniciativas/. Args: post_id, numero.
 *
 * Texto a un lado (numeral, nombre, qué es, ediciones y próxima fecha) y al
 * otro el flyer de la próxima edición o de la última realizada: las
 * iniciativas no tienen foto propia cargada y el flyer es la imagen real de
 * lo que pasa. Sin evento con flyer, el bloque queda solo con el texto.
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
<section class="asp-container asp-iniciativa-fila<?php echo $asp_vitrina ? '' : ' asp-iniciativa-fila--sin-vitrina'; ?>" aria-labelledby="<?php echo esc_attr( $asp_titulo ); ?>">
	<div class="asp-iniciativa-fila__texto">
		<span class="asp-iniciativa-fila__num" aria-hidden="true"><?php echo esc_html( asp_romano( $asp_num ) ); ?></span>
		<h2 class="asp-iniciativa-fila__titulo" id="<?php echo esc_attr( $asp_titulo ); ?>">
			<a href="<?php echo esc_url( get_permalink( $asp_id ) ); ?>"><?php echo esc_html( get_the_title( $asp_id ) ); ?></a>
		</h2>
		<?php if ( $asp_desc ) : ?>
			<div class="asp-iniciativa-fila__desc"><?php echo wp_kses_post( wpautop( $asp_desc ) ); ?></div>
		<?php endif; ?>
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
		<a class="asp-cta-link" href="<?php echo esc_url( get_permalink( $asp_id ) ); ?>"><?php esc_html_e( 'Conocé la iniciativa', 'asp' ); ?></a>
	</div>
	<?php if ( $asp_vitrina ) : ?>
		<a class="asp-iniciativa-fila__vitrina" href="<?php echo esc_url( get_permalink( $asp_vitrina ) ); ?>">
			<?php
			get_template_part(
				'parts/evento/flyer',
				null,
				[
					'post_id' => $asp_vitrina->ID,
					'clase'   => 'asp-flyer--tile',
					'tamano'  => 'asp-flyer-card',
					'alt'     => '',
					'sizes'   => '(min-width: 1024px) 520px, 100vw',
				]
			);
			?>
			<span class="asp-iniciativa-fila__pie">
				<span class="asp-label"><?php
					if ( $asp_prox && $asp_prox->ID === $asp_vitrina->ID ) {
						echo 'en_curso' === asp_evento_estado( $asp_vitrina->ID ) ? esc_html__( 'Edición en curso', 'asp' ) : esc_html__( 'Próxima edición', 'asp' );
					} else {
						esc_html_e( 'Última edición', 'asp' );
					}
				?></span>
				<span class="asp-iniciativa-fila__evento"><?php echo esc_html( get_the_title( $asp_vitrina ) ); ?></span>
				<span class="asp-iniciativa-fila__cuando"><?php echo esc_html( implode( ' · ', array_filter( [ asp_evento_fecha_texto( $asp_vitrina->ID ), asp_evento_ciudad( $asp_vitrina->ID ) ] ) ) ); ?></span>
			</span>
		</a>
	<?php endif; ?>
</section>
