<?php
/**
 * Oradores: lista densa con bio desplegable. Args: post_id, grid (bool).
 * Se autooculta si no hay oradores.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

$asp_id       = (int) ( $args['post_id'] ?? get_the_ID() );
$asp_oradores = asp_evento_relacionados( $asp_id, 'evento_oradores', 'persona' );

if ( empty( $asp_oradores ) ) {
	return;
}
?>
<div class="asp-bloque">
	<span class="asp-label"><?php echo esc_html( _n( 'Orador', 'Oradores', count( $asp_oradores ), 'asp' ) ); ?></span>
	<div class="asp-oradores<?php echo ! empty( $args['grid'] ) ? ' asp-oradores--grid' : ''; ?>">
		<?php foreach ( $asp_oradores as $asp_persona ) :
			$asp_bio   = (string) get_post_meta( $asp_persona->ID, 'persona_bio', true );
			$asp_linea = asp_persona_cargo_iglesia( $asp_persona->ID );
			?>
			<?php if ( $asp_bio ) : ?>
				<details class="asp-orador">
					<summary class="asp-orador__fila">
						<?php echo asp_persona_foto( $asp_persona->ID, 'asp-orador__foto' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						<span class="asp-orador__nombre"><strong><?php echo esc_html( get_the_title( $asp_persona ) ); ?></strong><?php if ( $asp_linea ) : ?><span><?php echo esc_html( $asp_linea ); ?></span><?php endif; ?></span>
						<span class="asp-orador__toggle" aria-hidden="true"></span>
						<span class="visually-hidden"><?php esc_html_e( 'Ver bio', 'asp' ); ?></span>
					</summary>
					<div class="asp-orador__bio">
						<?php echo wp_kses_post( wpautop( $asp_bio ) ); ?>
						<a href="<?php echo esc_url( get_permalink( $asp_persona ) ); ?>"><?php esc_html_e( 'Ver ficha', 'asp' ); ?></a>
					</div>
				</details>
			<?php else : ?>
				<div class="asp-orador">
					<a class="asp-orador__fila" href="<?php echo esc_url( get_permalink( $asp_persona ) ); ?>">
						<?php echo asp_persona_foto( $asp_persona->ID, 'asp-orador__foto' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						<span class="asp-orador__nombre"><strong><?php echo esc_html( get_the_title( $asp_persona ) ); ?></strong><?php if ( $asp_linea ) : ?><span><?php echo esc_html( $asp_linea ); ?></span><?php endif; ?></span>
					</a>
				</div>
			<?php endif; ?>
		<?php endforeach; ?>
	</div>
</div>
