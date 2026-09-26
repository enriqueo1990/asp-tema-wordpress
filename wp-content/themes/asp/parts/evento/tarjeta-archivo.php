<?php
/**
 * Tarjeta de la grilla del archivo de /eventos/: flyer entero sobre la banda
 * tonal y debajo iniciativa, título, fecha y ciudad.
 * Args: post_id, con_anio (bool: fecha con año, para grillas sin encabezado
 * de año), con_iniciativa (bool, por defecto true: en la ficha de la
 * iniciativa el rótulo repetiría el título de la página).
 *
 * Sin flyer, la banda muestra la fecha en numerales grandes —como la ficha—
 * para que la grilla no quede desalineada; nunca un placeholder.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

$asp_id     = (int) ( $args['post_id'] ?? get_the_ID() );
$asp_inic   = ( $args['con_iniciativa'] ?? true ) ? asp_evento_iniciativa( $asp_id ) : null;
$asp_flyer  = absint( get_post_meta( $asp_id, 'evento_flyer', true ) );
$asp_grande = $asp_flyer ? [] : asp_evento_fecha_grande( $asp_id );
$asp_ciudad = asp_evento_ciudad( $asp_id );
?>
<a class="asp-evento-tile" href="<?php echo esc_url( get_permalink( $asp_id ) ); ?>">
	<?php if ( $asp_flyer ) : ?>
		<?php
		get_template_part(
			'parts/evento/flyer',
			null,
			[
				'post_id' => $asp_id,
				'clase'   => 'asp-flyer--tile',
				'tamano'  => 'asp-flyer-card',
				'alt'     => '',
				'sizes'   => '(min-width: 1024px) 300px, (min-width: 768px) 33vw, 50vw',
			]
		);
		?>
	<?php elseif ( '' !== $asp_grande['dias'] ) : ?>
		<span class="asp-evento-tile__sinflyer" aria-hidden="true">
			<span class="asp-evento-tile__dias"><?php echo esc_html( $asp_grande['dias'] ); ?></span>
			<span class="asp-evento-tile__mes"><?php echo esc_html( $asp_grande['mes'] ); ?></span>
		</span>
	<?php endif; ?>
	<span class="asp-evento-tile__cuerpo">
		<?php /* El título es el tema ("Amós", "Efesios"): sin la iniciativa no se sabe qué fue. */ ?>
		<?php if ( $asp_inic ) : ?>
			<span class="asp-evento-tile__iniciativa"><?php echo esc_html( get_the_title( $asp_inic ) ); ?></span>
		<?php endif; ?>
		<span class="asp-evento-tile__titulo"><?php echo esc_html( get_the_title( $asp_id ) ); ?></span>
		<?php /* Fecha y ciudad en renglones separados: en dos columnas de 390 px la línea unida cortaba en el punto medio. */ ?>
		<span class="asp-evento-tile__meta">
			<span><?php echo esc_html( asp_evento_fecha_texto( $asp_id, ! empty( $args['con_anio'] ) ) ); ?></span>
			<?php if ( $asp_ciudad ) : ?>
				<span><?php echo esc_html( $asp_ciudad ); ?></span>
			<?php endif; ?>
		</span>
	</span>
</a>
