<?php
/**
 * Meta boxes del evento: el bloque obligatorio arriba y siempre abierto,
 * el opcional plegado. Guardado en save_post_evento.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registra las dos cajas.
 *
 * @return void
 */
function asp_meta_boxes_evento(): void {
	add_meta_box( 'asp_evento_obligatorio', __( 'Datos del evento · obligatorios para publicar', 'asp' ), 'asp_render_evento_obligatorio', 'evento', 'normal', 'high' );
	add_meta_box( 'asp_evento_opcional', __( 'Más detalles · se pueden completar después', 'asp' ), 'asp_render_evento_opcional', 'evento', 'normal', 'default' );
}
add_action( 'add_meta_boxes_evento', 'asp_meta_boxes_evento' );

/**
 * La caja opcional arranca plegada. WordPress recuerda si la abrieron.
 *
 * @param string[] $clases Clases.
 * @return string[]
 */
function asp_plegar_opcional( array $clases ): array {
	if ( ! in_array( 'closed', $clases, true ) ) {
		$clases[] = 'closed';
	}
	return $clases;
}
add_filter( 'postbox_classes_evento_asp_evento_opcional', 'asp_plegar_opcional' );

/**
 * Bloque obligatorio.
 *
 * @param WP_Post $post Evento.
 * @return void
 */
function asp_render_evento_obligatorio( WP_Post $post ): void {
	asp_nonce_campo( 'evento' );
	$id     = $post->ID;
	$pais   = get_the_terms( $id, 'pais' );
	$pais   = ( is_array( $pais ) && $pais ) ? $pais[0]->slug : '';
	$estado = (string) get_post_meta( $id, 'evento_estado_inscripcion', true ) ?: 'reserva';
	?>
	<p class="asp-campo__intro"><?php esc_html_e( 'Con estos datos el evento ya se puede publicar. El flyer y el resto van abajo y se completan cuando estén.', 'asp' ); ?></p>
	<div class="asp-grid-campos">
		<?php asp_campo_fecha( 'evento_fecha_inicio', __( 'Primer día del evento', 'asp' ), (string) get_post_meta( $id, 'evento_fecha_inicio', true ), '', true ); ?>
		<?php asp_campo_fecha( 'evento_fecha_fin', __( 'Último día del evento', 'asp' ), (string) get_post_meta( $id, 'evento_fecha_fin', true ), __( 'Si dura un solo día, poné la misma fecha. Cuando pasa esta fecha el evento se archiva solo.', 'asp' ), true ); ?>
		<?php asp_campo_texto( 'evento_ciudad', __( 'Ciudad', 'asp' ), (string) get_post_meta( $id, 'evento_ciudad', true ), '', true, __( 'Ej. Buenos Aires', 'asp' ) ); ?>
		<?php asp_campo_radio_terminos( 'evento_pais', __( 'País', 'asp' ), 'pais', $pais, '', true ); ?>
		<?php asp_campo_select( 'evento_estado_inscripcion', __( '¿Se puede inscribir?', 'asp' ), $estado, asp_estados_inscripcion(), __( 'Si todavía no hay link, dejá "Reservá la fecha": la ficha se publica igual, sin botón.', 'asp' ), true ); ?>
		<?php asp_campo_url( 'evento_url_registro', __( 'Link de inscripción', 'asp' ), (string) get_post_meta( $id, 'evento_url_registro', true ), __( 'Eventbrite, Entrada27 u otro. Obligatorio solo cuando la inscripción está abierta.', 'asp' ) ); ?>
	</div>
	<?php
}

/**
 * Bloque opcional.
 *
 * @param WP_Post $post Evento.
 * @return void
 */
function asp_render_evento_opcional( WP_Post $post ): void {
	$id          = $post->ID;
	$iniciativas = get_posts( [ 'post_type' => 'iniciativa', 'post_status' => 'publish', 'posts_per_page' => -1, 'orderby' => 'menu_order title', 'order' => 'ASC' ] );
	$oradores    = asp_personas_por_rol( 'orador' );
	$aliados     = get_posts( [ 'post_type' => 'aliado', 'post_status' => 'publish', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC' ] );
	$programa    = asp_sanitizar_programa( get_post_meta( $id, 'evento_programa', true ) );
	?>
	<div class="asp-grid-campos">
		<?php asp_campo_imagen( 'evento_flyer', __( 'Flyer', 'asp' ), absint( get_post_meta( $id, 'evento_flyer', true ) ), __( 'El mismo de Instagram, cuadrado o 4:5. Se muestra entero, nunca recortado.', 'asp' ) ); ?>
		<?php asp_campo_select_posts( 'evento_iniciativa', __( 'Iniciativa', 'asp' ), $iniciativas, absint( get_post_meta( $id, 'evento_iniciativa', true ) ), __( 'A qué iniciativa pertenece (Conferencia, Cánticos, Taller…).', 'asp' ) ); ?>
		<?php asp_campo_texto( 'evento_sede_nombre', __( 'Sede', 'asp' ), (string) get_post_meta( $id, 'evento_sede_nombre', true ), '', false, __( 'Ej. Iglesia Gracia Soberana', 'asp' ) ); ?>
		<?php asp_campo_textarea( 'evento_sede_direccion', __( 'Dirección de la sede', 'asp' ), (string) get_post_meta( $id, 'evento_sede_direccion', true ), __( 'Ej. 8300 Helgerman Ct, Gaithersburg, MD', 'asp' ), 2 ); ?>
		<?php asp_campo_texto( 'evento_precio', __( 'Precio', 'asp' ), (string) get_post_meta( $id, 'evento_precio', true ), __( 'Texto libre, ej. "Entrada libre" o "USD 25 · ARS 15.000".', 'asp' ) ); ?>
		<?php asp_campo_checkbox( 'evento_destacado', __( 'Mostrar como evento principal en el inicio', 'asp' ), (bool) get_post_meta( $id, 'evento_destacado', true ), __( 'Si ninguno está marcado, el inicio muestra el próximo por fecha.', 'asp' ) ); ?>
	</div>
	<?php asp_campo_checkboxes_posts( 'evento_oradores', __( 'Oradores', 'asp' ), $oradores, array_map( 'absint', (array) get_post_meta( $id, 'evento_oradores', false ) ), __( 'Aparecen los que tienen el rol "Orador" en Personas.', 'asp' ), __( 'Todavía no hay personas con rol "Orador". Se agregan en Personas.', 'asp' ) ); ?>
	<?php asp_campo_checkboxes_posts( 'evento_aliados', __( 'Aliados', 'asp' ), $aliados, array_map( 'absint', (array) get_post_meta( $id, 'evento_aliados', false ) ), '', __( 'Todavía no hay aliados cargados.', 'asp' ) ); ?>
	<?php asp_campo_wysiwyg( 'evento_descripcion', __( 'Descripción', 'asp' ), (string) get_post_meta( $id, 'evento_descripcion', true ), __( 'De qué se trata, para quién es. Lo que hoy va en el texto del posteo.', 'asp' ) ); ?>
	<?php asp_campo_programa( 'evento_programa', __( 'Programa', 'asp' ), $programa, __( 'Una fila por sesión. El día va porque las conferencias duran dos.', 'asp' ) ); ?>
	<?php
}

/**
 * Guardado.
 *
 * @param int $post_id ID.
 * @return void
 */
function asp_guardar_evento( int $post_id ): void {
	if ( ! asp_puede_guardar( $post_id, 'evento' ) ) {
		return;
	}

	asp_guardar_meta( $post_id, 'evento_fecha_inicio', asp_fecha_input_a_ymd( asp_post_texto( 'evento_fecha_inicio' ) ) );
	asp_guardar_meta( $post_id, 'evento_fecha_fin', asp_fecha_input_a_ymd( asp_post_texto( 'evento_fecha_fin' ) ) );
	asp_guardar_meta( $post_id, 'evento_ciudad', sanitize_text_field( asp_post_texto( 'evento_ciudad' ) ) );
	asp_guardar_meta( $post_id, 'evento_estado_inscripcion', asp_sanitizar_estado_inscripcion( asp_post_texto( 'evento_estado_inscripcion' ) ) );
	asp_guardar_meta( $post_id, 'evento_url_registro', esc_url_raw( asp_post_texto( 'evento_url_registro' ) ) );

	$pais = sanitize_key( asp_post_texto( 'evento_pais' ) );
	wp_set_object_terms( $post_id, $pais ? $pais : [], 'pais' );

	asp_guardar_meta( $post_id, 'evento_flyer', absint( asp_post_texto( 'evento_flyer' ) ) );
	asp_guardar_meta( $post_id, 'evento_iniciativa', absint( asp_post_texto( 'evento_iniciativa' ) ) );
	asp_guardar_meta( $post_id, 'evento_sede_nombre', sanitize_text_field( asp_post_texto( 'evento_sede_nombre' ) ) );
	asp_guardar_meta( $post_id, 'evento_sede_direccion', sanitize_textarea_field( asp_post_texto( 'evento_sede_direccion' ) ) );
	asp_guardar_meta( $post_id, 'evento_precio', sanitize_text_field( asp_post_texto( 'evento_precio' ) ) );
	asp_guardar_meta( $post_id, 'evento_destacado', '' !== asp_post_texto( 'evento_destacado' ) ? '1' : '' );
	asp_guardar_meta_multiple( $post_id, 'evento_oradores', asp_post_ids( 'evento_oradores' ) );
	asp_guardar_meta_multiple( $post_id, 'evento_aliados', asp_post_ids( 'evento_aliados' ) );
	asp_guardar_meta( $post_id, 'evento_descripcion', wp_kses_post( asp_post_texto( 'evento_descripcion' ) ) );

	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	$programa = $_POST['evento_programa'] ?? [];
	$programa = is_array( $programa ) ? wp_unslash( $programa ) : [];
	foreach ( $programa as &$fila ) {
		if ( is_array( $fila ) && isset( $fila['dia'] ) ) {
			$fila['dia'] = str_replace( '-', '', (string) $fila['dia'] );
		}
	}
	unset( $fila );
	asp_guardar_meta( $post_id, 'evento_programa', asp_sanitizar_programa( $programa ) );
}
add_action( 'save_post_evento', 'asp_guardar_evento' );
