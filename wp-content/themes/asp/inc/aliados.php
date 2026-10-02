<?php
/**
 * Aliados del ministerio fuera de la ficha del evento: la franja de logos
 * que va antes del pie en el inicio y en Nosotros. La ficha usa
 * parts/evento/aliados.php con los aliados de cada evento.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

/**
 * Aliados para la franja de logos (inicio y Nosotros, 2-10-2026): solo los
 * que tienen logo, para que la franja sea una fila pareja. Los que no
 * tienen quedan en la lista de faltantes, que llega vacía salvo para quien
 * administra.
 *
 * @return array{con_logo: array<int, array{nombre:string,url:string,logo:string,proporcion:float}>, sin_logo: string[]}
 */
function asp_aliados_franja(): array {
	$con = [];
	$sin = [];
	$aliados = get_posts(
		[
			'post_type'      => 'aliado',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => [ 'menu_order' => 'ASC', 'title' => 'ASC' ],
			'no_found_rows'  => true,
		]
	);
	foreach ( $aliados as $aliado ) {
		$nombre = get_the_title( $aliado );
		$logo   = absint( get_post_meta( $aliado->ID, 'aliado_logo', true ) );
		$img    = $logo ? (string) wp_get_attachment_image( $logo, 'medium', false, [ 'alt' => $nombre, 'loading' => 'lazy', 'class' => 'asp-aliados-franja__logo' ] ) : '';
		if ( '' === $img ) {
			if ( current_user_can( 'edit_theme_options' ) ) {
				$sin[] = $nombre;
			}
			continue;
		}
		$meta  = wp_get_attachment_metadata( $logo );
		$con[] = [
			'nombre'     => $nombre,
			'url'        => (string) get_post_meta( $aliado->ID, 'aliado_url', true ),
			'logo'       => $img,
			/* Ancho sobre alto: el CSS iguala el peso de los logos con esto. */
			'proporcion' => ( is_array( $meta ) && ! empty( $meta['width'] ) && ! empty( $meta['height'] ) ) ? round( $meta['width'] / $meta['height'], 3 ) : 0.0,
		];
	}
	return [ 'con_logo' => $con, 'sin_logo' => $sin ];
}
