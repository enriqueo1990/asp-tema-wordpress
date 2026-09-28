<?php
/**
 * Fila de resultado de búsqueda para lo que no tiene una fila propia:
 * personas, iniciativas y páginas. Args: post_id.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

$asp_post   = get_post( (int) ( $args['post_id'] ?? get_the_ID() ) );
if ( ! $asp_post ) {
	return;
}
$asp_bajada = asp_busqueda_bajada( $asp_post );
?>
<a class="asp-busqueda-fila" href="<?php echo esc_url( get_permalink( $asp_post ) ); ?>">
	<span class="asp-busqueda-fila__titulo"><?php echo esc_html( get_the_title( $asp_post ) ); ?></span>
	<?php if ( '' !== $asp_bajada ) : ?>
		<span class="asp-busqueda-fila__bajada"><?php echo esc_html( $asp_bajada ); ?></span>
	<?php endif; ?>
</a>
