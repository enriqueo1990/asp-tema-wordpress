<?php
/**
 * Caja de iniciativa para el inicio. Args: post_id, numero.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

$asp_id      = (int) ( $args['post_id'] ?? get_the_ID() );
$asp_num     = (int) ( $args['numero'] ?? 0 );
$asp_resumen = asp_iniciativa_resumen( $asp_id );
?>
<a class="asp-caja-iniciativa" href="<?php echo esc_url( get_permalink( $asp_id ) ); ?>">
	<?php if ( $asp_num ) : ?>
		<span class="asp-caja-iniciativa__num" aria-hidden="true"><?php echo esc_html( asp_romano( $asp_num ) ); ?></span>
	<?php endif; ?>
	<span class="asp-caja-iniciativa__titulo"><?php echo esc_html( get_the_title( $asp_id ) ); ?></span>
	<?php if ( $asp_resumen ) : ?>
		<span class="asp-caja-iniciativa__bajada"><?php echo esc_html( $asp_resumen ); ?></span>
	<?php endif; ?>
</a>
