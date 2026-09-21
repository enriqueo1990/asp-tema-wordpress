<?php
/**
 * Meta boxes de iniciativa, persona y aliado.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

/* ---------------------------------------------------------------- Iniciativa */

function asp_meta_boxes_iniciativa(): void {
	add_meta_box( 'asp_iniciativa_datos', __( 'Datos de la iniciativa', 'asp' ), 'asp_render_iniciativa', 'iniciativa', 'normal', 'high' );
}
add_action( 'add_meta_boxes_iniciativa', 'asp_meta_boxes_iniciativa' );

function asp_render_iniciativa( WP_Post $post ): void {
	asp_nonce_campo( 'iniciativa' );
	$id = $post->ID;
	?>
	<div class="asp-grid-campos">
		<?php asp_campo_texto( 'iniciativa_bajada', __( 'Bajada', 'asp' ), (string) get_post_meta( $id, 'iniciativa_bajada', true ), __( 'Una línea que resume la iniciativa. Aparece en las tarjetas.', 'asp' ) ); ?>
		<?php asp_campo_texto( 'iniciativa_publico', __( 'Para quién', 'asp' ), (string) get_post_meta( $id, 'iniciativa_publico', true ), __( 'Ej. Líderes de alabanza.', 'asp' ) ); ?>
		<?php asp_campo_imagen( 'iniciativa_imagen', __( 'Imagen', 'asp' ), absint( get_post_meta( $id, 'iniciativa_imagen', true ) ) ); ?>
	</div>
	<?php asp_campo_wysiwyg( 'iniciativa_descripcion', __( 'Qué es', 'asp' ), (string) get_post_meta( $id, 'iniciativa_descripcion', true ) ); ?>
	<?php asp_campo_wysiwyg( 'iniciativa_historia', __( 'Historia', 'asp' ), (string) get_post_meta( $id, 'iniciativa_historia', true ), __( 'Cómo empezó y cómo fue creciendo. Los eventos anteriores se listan solos.', 'asp' ) ); ?>
	<?php
}

function asp_guardar_iniciativa( int $post_id ): void {
	if ( ! asp_puede_guardar( $post_id, 'iniciativa' ) ) {
		return;
	}
	asp_guardar_meta( $post_id, 'iniciativa_bajada', sanitize_text_field( asp_post_texto( 'iniciativa_bajada' ) ) );
	asp_guardar_meta( $post_id, 'iniciativa_publico', sanitize_text_field( asp_post_texto( 'iniciativa_publico' ) ) );
	asp_guardar_meta( $post_id, 'iniciativa_imagen', absint( asp_post_texto( 'iniciativa_imagen' ) ) );
	asp_guardar_meta( $post_id, 'iniciativa_descripcion', wp_kses_post( asp_post_texto( 'iniciativa_descripcion' ) ) );
	asp_guardar_meta( $post_id, 'iniciativa_historia', wp_kses_post( asp_post_texto( 'iniciativa_historia' ) ) );
}
add_action( 'save_post_iniciativa', 'asp_guardar_iniciativa' );

/* ------------------------------------------------------------------- Persona */

function asp_meta_boxes_persona(): void {
	add_meta_box( 'asp_persona_datos', __( 'Datos de la persona', 'asp' ), 'asp_render_persona', 'persona', 'normal', 'high' );
}
add_action( 'add_meta_boxes_persona', 'asp_meta_boxes_persona' );

function asp_render_persona( WP_Post $post ): void {
	asp_nonce_campo( 'persona' );
	$id       = $post->ID;
	$roles    = array_map( 'sanitize_key', (array) get_post_meta( $id, 'persona_roles', false ) );
	$usuarios = get_users( [ 'orderby' => 'display_name', 'fields' => [ 'ID', 'display_name' ] ] );
	$opciones = [ '0' => __( 'Ninguno', 'asp' ) ];
	foreach ( $usuarios as $u ) {
		$opciones[ (string) $u->ID ] = $u->display_name;
	}
	?>
	<div class="asp-campo">
		<span class="asp-campo__etiqueta"><?php esc_html_e( 'Dónde aparece', 'asp' ); ?></span>
		<div class="asp-checks">
			<?php foreach ( asp_roles_persona() as $clave => $etiqueta ) : ?>
				<label for="persona_roles_<?php echo esc_attr( $clave ); ?>"><input type="checkbox" id="persona_roles_<?php echo esc_attr( $clave ); ?>" name="persona_roles[]" value="<?php echo esc_attr( $clave ); ?>" <?php checked( in_array( $clave, $roles, true ) ); ?>> <?php echo esc_html( $etiqueta ); ?></label>
			<?php endforeach; ?>
		</div>
		<p class="asp-campo__ayuda"><?php esc_html_e( 'Una misma persona puede estar en el consejo, dar charlas y escribir artículos.', 'asp' ); ?></p>
	</div>
	<div class="asp-grid-campos">
		<?php asp_campo_imagen( 'persona_foto', __( 'Foto', 'asp' ), absint( get_post_meta( $id, 'persona_foto', true ) ), __( 'Cuadrada, de frente. Se muestra recortada en círculo.', 'asp' ) ); ?>
		<?php asp_campo_texto( 'persona_cargo', __( 'Cargo', 'asp' ), (string) get_post_meta( $id, 'persona_cargo', true ), __( 'Ej. Pastor, Pastor principal.', 'asp' ) ); ?>
		<?php asp_campo_texto( 'persona_iglesia', __( 'Iglesia', 'asp' ), (string) get_post_meta( $id, 'persona_iglesia', true ) ); ?>
		<?php asp_campo_texto( 'persona_ciudad', __( 'Ciudad', 'asp' ), (string) get_post_meta( $id, 'persona_ciudad', true ) ); ?>
		<?php asp_campo_texto( 'persona_pais', __( 'País', 'asp' ), (string) get_post_meta( $id, 'persona_pais', true ), __( 'Ej. Argentina, Estados Unidos.', 'asp' ) ); ?>
		<?php asp_campo_select( 'persona_usuario', __( 'Usuario de WordPress', 'asp' ), (string) absint( get_post_meta( $id, 'persona_usuario', true ) ), $opciones, __( 'Solo para quienes escriben artículos: vincula la ficha con su usuario.', 'asp' ) ); ?>
	</div>
	<?php asp_campo_textarea( 'persona_bio', __( 'Bio', 'asp' ), (string) get_post_meta( $id, 'persona_bio', true ), __( 'Dos o tres líneas. Se muestra desplegada en la ficha del evento.', 'asp' ), 4 ); ?>
	<?php
}

function asp_guardar_persona( int $post_id ): void {
	if ( ! asp_puede_guardar( $post_id, 'persona' ) ) {
		return;
	}
	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	$roles = $_POST['persona_roles'] ?? [];
	$roles = is_array( $roles ) ? array_map( 'sanitize_key', $roles ) : [];
	$roles = array_intersect( $roles, array_keys( asp_roles_persona() ) );
	asp_guardar_meta_multiple( $post_id, 'persona_roles', array_values( $roles ) );
	asp_guardar_meta( $post_id, 'persona_foto', absint( asp_post_texto( 'persona_foto' ) ) );
	asp_guardar_meta( $post_id, 'persona_cargo', sanitize_text_field( asp_post_texto( 'persona_cargo' ) ) );
	asp_guardar_meta( $post_id, 'persona_iglesia', sanitize_text_field( asp_post_texto( 'persona_iglesia' ) ) );
	asp_guardar_meta( $post_id, 'persona_ciudad', sanitize_text_field( asp_post_texto( 'persona_ciudad' ) ) );
	asp_guardar_meta( $post_id, 'persona_pais', sanitize_text_field( asp_post_texto( 'persona_pais' ) ) );
	asp_guardar_meta( $post_id, 'persona_usuario', absint( asp_post_texto( 'persona_usuario' ) ) );
	asp_guardar_meta( $post_id, 'persona_bio', sanitize_textarea_field( asp_post_texto( 'persona_bio' ) ) );
}
add_action( 'save_post_persona', 'asp_guardar_persona' );

/* -------------------------------------------------------------------- Aliado */

function asp_meta_boxes_aliado(): void {
	add_meta_box( 'asp_aliado_datos', __( 'Datos del aliado', 'asp' ), 'asp_render_aliado', 'aliado', 'normal', 'high' );
}
add_action( 'add_meta_boxes_aliado', 'asp_meta_boxes_aliado' );

function asp_render_aliado( WP_Post $post ): void {
	asp_nonce_campo( 'aliado' );
	$id = $post->ID;
	?>
	<div class="asp-grid-campos">
		<?php asp_campo_imagen( 'aliado_logo', __( 'Logo', 'asp' ), absint( get_post_meta( $id, 'aliado_logo', true ) ), __( 'Si no hay logo, se muestra el nombre.', 'asp' ) ); ?>
		<?php asp_campo_url( 'aliado_url', __( 'Sitio web', 'asp' ), (string) get_post_meta( $id, 'aliado_url', true ) ); ?>
	</div>
	<?php
}

function asp_guardar_aliado( int $post_id ): void {
	if ( ! asp_puede_guardar( $post_id, 'aliado' ) ) {
		return;
	}
	asp_guardar_meta( $post_id, 'aliado_logo', absint( asp_post_texto( 'aliado_logo' ) ) );
	asp_guardar_meta( $post_id, 'aliado_url', esc_url_raw( asp_post_texto( 'aliado_url' ) ) );
}
add_action( 'save_post_aliado', 'asp_guardar_aliado' );

/* --------------------------------------------------------------- Predicación */

function asp_meta_boxes_predicacion(): void {
	add_meta_box( 'asp_predicacion_datos', __( 'Datos de la predicación', 'asp' ), 'asp_render_predicacion', 'predicacion', 'normal', 'high' );
}
add_action( 'add_meta_boxes_predicacion', 'asp_meta_boxes_predicacion' );

function asp_render_predicacion( WP_Post $post ): void {
	asp_nonce_campo( 'predicacion' );
	$id      = $post->ID;
	$eventos = get_posts( [ 'post_type' => 'evento', 'post_status' => 'publish', 'posts_per_page' => -1, 'meta_key' => 'evento_fecha_inicio', 'orderby' => 'meta_value_num', 'order' => 'DESC' ] );
	$oradores = asp_personas_por_rol( 'orador' );
	?>
	<p class="asp-campo__intro"><?php esc_html_e( 'Elegí el formato y pegá el link. Si es texto, va en el cuadro grande de arriba.', 'asp' ); ?></p>
	<div class="asp-grid-campos">
		<?php asp_campo_select( 'predicacion_tipo', __( 'Formato', 'asp' ), (string) get_post_meta( $id, 'predicacion_tipo', true ) ?: 'texto', asp_tipos_predicacion(), '', true ); ?>
		<?php asp_campo_url( 'predicacion_video_url', __( 'Link de YouTube', 'asp' ), (string) get_post_meta( $id, 'predicacion_video_url', true ), __( 'El link del video, tal cual está en la barra del navegador.', 'asp' ) ); ?>
		<?php asp_campo_url( 'predicacion_audio_url', __( 'Link del audio', 'asp' ), (string) get_post_meta( $id, 'predicacion_audio_url', true ), __( 'Subí el mp3 a Medios y pegá acá su dirección.', 'asp' ) ); ?>
		<?php asp_campo_select_posts( 'predicacion_evento', __( 'Evento', 'asp' ), $eventos, absint( get_post_meta( $id, 'predicacion_evento', true ) ), __( 'La conferencia o taller donde se dio. La fecha se toma de ahí si no cargás una.', 'asp' ), __( 'Ninguno', 'asp' ) ); ?>
		<?php asp_campo_select_posts( 'predicacion_orador', __( 'Orador', 'asp' ), $oradores, absint( get_post_meta( $id, 'predicacion_orador', true ) ), __( 'Personas con el rol "Orador".', 'asp' ), __( 'Ninguno', 'asp' ) ); ?>
		<?php asp_campo_texto( 'predicacion_pasaje', __( 'Pasaje bíblico', 'asp' ), (string) get_post_meta( $id, 'predicacion_pasaje', true ), '', false, __( 'Ej. 2 Timoteo 4:1-5', 'asp' ) ); ?>
		<?php asp_campo_texto( 'predicacion_duracion', __( 'Duración', 'asp' ), (string) get_post_meta( $id, 'predicacion_duracion', true ), '', false, __( 'Ej. 48 min', 'asp' ) ); ?>
		<?php asp_campo_fecha( 'predicacion_fecha', __( 'Fecha', 'asp' ), (string) get_post_meta( $id, 'predicacion_fecha', true ), __( 'Solo si no está vinculada a un evento o fue otro día.', 'asp' ) ); ?>
	</div>
	<?php
}

function asp_guardar_predicacion( int $post_id ): void {
	if ( ! asp_puede_guardar( $post_id, 'predicacion' ) ) {
		return;
	}
	asp_guardar_meta( $post_id, 'predicacion_tipo', asp_sanitizar_tipo_predicacion( asp_post_texto( 'predicacion_tipo' ) ) );
	asp_guardar_meta( $post_id, 'predicacion_video_url', esc_url_raw( asp_post_texto( 'predicacion_video_url' ) ) );
	asp_guardar_meta( $post_id, 'predicacion_audio_url', esc_url_raw( asp_post_texto( 'predicacion_audio_url' ) ) );
	asp_guardar_meta( $post_id, 'predicacion_evento', absint( asp_post_texto( 'predicacion_evento' ) ) );
	asp_guardar_meta( $post_id, 'predicacion_orador', absint( asp_post_texto( 'predicacion_orador' ) ) );
	asp_guardar_meta( $post_id, 'predicacion_pasaje', sanitize_text_field( asp_post_texto( 'predicacion_pasaje' ) ) );
	asp_guardar_meta( $post_id, 'predicacion_duracion', sanitize_text_field( asp_post_texto( 'predicacion_duracion' ) ) );
	asp_guardar_meta( $post_id, 'predicacion_fecha', asp_fecha_input_a_ymd( asp_post_texto( 'predicacion_fecha' ) ) );
}
add_action( 'save_post_predicacion', 'asp_guardar_predicacion' );
