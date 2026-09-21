<?php
/**
 * Tarjeta de iniciativa. Args: post_id, numero (int, opcional).
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

$asp_id     = (int) ( $args['post_id'] ?? get_the_ID() );
$asp_num    = (int) ( $args['numero'] ?? 0 );
$asp_bajada = (string) get_post_meta( $asp_id, 'iniciativa_bajada', true );
?>
<a class="asp-iniciativa-card" href="<?php echo esc_url( get_permalink( $asp_id ) ); ?>">
	<?php if ( $asp_num ) : ?>
		<span class="asp-iniciativa-card__num" aria-hidden="true"><?php echo esc_html( str_pad( (string) $asp_num, 2, '0', STR_PAD_LEFT ) ); ?></span>
	<?php else : ?>
		<span class="asp-label"><?php esc_html_e( 'Iniciativa', 'asp' ); ?></span>
	<?php endif; ?>
	<span class="asp-iniciativa-card__titulo"><?php echo esc_html( get_the_title( $asp_id ) ); ?></span>
	<?php if ( $asp_bajada ) : ?>
		<span class="asp-iniciativa-card__bajada"><?php echo esc_html( $asp_bajada ); ?></span>
	<?php endif; ?>
</a>
