<?php
/**
 * Página de ayuda dentro del panel: el paso a paso de la carga, para
 * quien entra cada dos o tres meses y no recuerda nada.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

/**
 * Submenú bajo Eventos.
 *
 * @return void
 */
function asp_menu_ayuda(): void {
	add_submenu_page(
		'edit.php?post_type=evento',
		__( 'Cómo cargar un evento', 'asp' ),
		__( 'Cómo cargar un evento', 'asp' ),
		'edit_eventos',
		'asp-ayuda',
		'asp_render_ayuda'
	);
}
add_action( 'admin_menu', 'asp_menu_ayuda' );

/**
 * Contenido.
 *
 * @return void
 */
function asp_render_ayuda(): void {
	$url_eventos = admin_url( 'edit.php?post_type=evento' );
	$url_nuevo   = admin_url( 'post-new.php?post_type=evento' );
	?>
	<div class="wrap asp-ayuda">
		<h1><?php esc_html_e( 'Cómo cargar un evento', 'asp' ); ?></h1>
		<p><?php esc_html_e( 'El sitio arma solo el inicio, el listado y el archivo a partir de lo que cargues acá. No hay que tocar ninguna página.', 'asp' ); ?></p>

		<h2><?php esc_html_e( 'Lo más común: la edición siguiente de un evento que ya existe', 'asp' ); ?></h2>
		<ol>
			<li><?php echo wp_kses_post( sprintf( __( 'Entrá a <a href="%s">Eventos</a> y buscá la edición anterior (la conferencia del año pasado, el taller anterior).', 'asp' ), esc_url( $url_eventos ) ) ); ?></li>
			<li><?php esc_html_e( 'Pasá el mouse por el título y hacé clic en «Duplicar edición anterior».', 'asp' ); ?></li>
			<li><?php esc_html_e( 'Se abre una copia como borrador con todo cargado. Cambiá el nombre si hace falta, poné las fechas nuevas y revisá la sede.', 'asp' ); ?></li>
			<li><?php esc_html_e( 'Si ya hay link de inscripción, elegí «Inscripción abierta» y pegalo. Si todavía no, dejá «Reservá la fecha».', 'asp' ); ?></li>
			<li><?php esc_html_e( 'Publicar. Listo: aparece en el inicio y en Eventos.', 'asp' ); ?></li>
		</ol>

		<h2><?php esc_html_e( 'Un evento nuevo desde cero', 'asp' ); ?></h2>
		<ol>
			<li><?php echo wp_kses_post( sprintf( __( '<a href="%s">Cargar evento</a>.', 'asp' ), esc_url( $url_nuevo ) ) ); ?></li>
			<li><?php esc_html_e( 'Completá el bloque de arriba: nombre, primer y último día, ciudad, país y si se puede inscribir. Con eso alcanza para publicar.', 'asp' ); ?></li>
			<li><?php esc_html_e( 'El flyer, la sede, los oradores, el programa y el precio van en «Más detalles». Se pueden completar otro día.', 'asp' ); ?></li>
		</ol>

		<h2><?php esc_html_e( 'Cosas que conviene saber', 'asp' ); ?></h2>
		<ul>
			<li><?php esc_html_e( 'El flyer se muestra entero, nunca recortado. Subí el mismo de Instagram.', 'asp' ); ?></li>
			<li><?php esc_html_e( 'El texto que está dentro del flyer no lo lee Google ni sirve para armar el listado. Por eso el nombre, las fechas y la ciudad se escriben aparte.', 'asp' ); ?></li>
			<li><?php esc_html_e( 'Cuando pasa el último día, el evento sale de «próximos» y entra al archivo solo. No hay que hacer nada.', 'asp' ); ?></li>
			<li><?php esc_html_e( 'Si el link de inscripción cambia o se cierra, editá el evento y cambiá «¿Se puede inscribir?». El botón del sitio se actualiza solo.', 'asp' ); ?></li>
			<li><?php esc_html_e( 'Las fechas se toman con el horario del sitio, que es el de Buenos Aires.', 'asp' ); ?></li>
			<li><?php esc_html_e( 'Si al publicar dice que falta algo, el evento quedó guardado como borrador: completá lo que pide y volvé a publicar.', 'asp' ); ?></li>
		</ul>
	</div>
	<?php
}
