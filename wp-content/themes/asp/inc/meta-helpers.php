<?php
/**
 * Helpers de meta boxes: inputs, nonces, sanitización y assets del panel.
 *
 * Cada helper imprime etiqueta, input y texto de ayuda, escapados.
 * Etiquetas en castellano llano: nada de "meta", "slug" ni "taxonomía".
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

/**
 * Tipos de contenido con meta boxes propios.
 *
 * @return string[]
 */
function asp_cpts_con_meta(): array {
	return [ 'evento', 'iniciativa', 'persona', 'aliado', 'predicacion' ];
}

/**
 * Campo oculto con el nonce del CPT.
 *
 * @param string $cpt Tipo.
 * @return void
 */
function asp_nonce_campo( string $cpt ): void {
	wp_nonce_field( 'asp_guardar_' . $cpt, 'asp_' . $cpt . '_nonce' );
}

/**
 * ¿Se puede guardar este post en este request?
 *
 * @param int    $post_id ID.
 * @param string $cpt     Tipo esperado.
 * @return bool
 */
function asp_puede_guardar( int $post_id, string $cpt ): bool {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return false;
	}
	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
		return false;
	}
	if ( get_post_type( $post_id ) !== $cpt ) {
		return false;
	}
	$nonce = $_POST[ 'asp_' . $cpt . '_nonce' ] ?? ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
	if ( ! is_string( $nonce ) || ! wp_verify_nonce( $nonce, 'asp_guardar_' . $cpt ) ) {
		return false;
	}
	return current_user_can( 'edit_post', $post_id );
}

/**
 * Guarda un meta simple: vacío = se borra, nunca se guarda ''.
 *
 * @param int    $post_id ID.
 * @param string $clave   Clave.
 * @param mixed  $valor   Valor ya sanitizado.
 * @return void
 */
function asp_guardar_meta( int $post_id, string $clave, $valor ): void {
	if ( '' === $valor || null === $valor || false === $valor || [] === $valor ) {
		delete_post_meta( $post_id, $clave );
		return;
	}
	update_post_meta( $post_id, $clave, $valor );
}

/**
 * Guarda un meta múltiple como filas separadas.
 *
 * @param int    $post_id ID.
 * @param string $clave   Clave.
 * @param array  $valores Valores ya sanitizados.
 * @return void
 */
function asp_guardar_meta_multiple( int $post_id, string $clave, array $valores ): void {
	delete_post_meta( $post_id, $clave );
	foreach ( array_unique( array_filter( $valores ) ) as $valor ) {
		add_post_meta( $post_id, $clave, $valor );
	}
}

/**
 * Valor del POST como texto, o cadena vacía.
 *
 * @param string $clave Clave.
 * @return string
 */
function asp_post_texto( string $clave ): string {
	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	$v = $_POST[ $clave ] ?? '';
	return is_string( $v ) ? wp_unslash( $v ) : '';
}

/**
 * Valor del POST como array de enteros.
 *
 * @param string $clave Clave.
 * @return int[]
 */
function asp_post_ids( string $clave ): array {
	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	$v = $_POST[ $clave ] ?? [];
	return is_array( $v ) ? array_values( array_filter( array_map( 'absint', $v ) ) ) : [];
}

/**
 * Fecha del <input type="date"> (Y-m-d) a Ymd.
 *
 * @param string $valor Valor del input.
 * @return string
 */
function asp_fecha_input_a_ymd( string $valor ): string {
	return asp_sanitizar_fecha_ymd( str_replace( '-', '', $valor ) );
}

/**
 * Ymd a Y-m-d para el <input type="date">.
 *
 * @param string $ymd Fecha guardada.
 * @return string
 */
function asp_fecha_ymd_a_input( string $ymd ): string {
	return asp_fecha_iso( $ymd );
}

/* ------------------------------------------------------------------------
   Render de campos
   --------------------------------------------------------------------- */

/**
 * Envoltorio común: etiqueta, control y ayuda.
 *
 * @param string $nombre   Nombre del campo.
 * @param string $etiqueta Etiqueta.
 * @param string $control  HTML del control (ya escapado).
 * @param string $ayuda    Texto de ayuda.
 * @param bool   $requerido Marca visual de obligatorio.
 * @return void
 */
function asp_campo_envoltorio( string $nombre, string $etiqueta, string $control, string $ayuda = '', bool $requerido = false ): void {
	?>
	<div class="asp-campo asp-campo--<?php echo esc_attr( $nombre ); ?>">
		<label class="asp-campo__etiqueta" for="<?php echo esc_attr( $nombre ); ?>">
			<?php echo esc_html( $etiqueta ); ?>
			<?php if ( $requerido ) : ?><span class="asp-campo__req" aria-hidden="true">*</span><span class="screen-reader-text"><?php esc_html_e( '(obligatorio)', 'asp' ); ?></span><?php endif; ?>
		</label>
		<?php echo $control; // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<?php if ( $ayuda ) : ?><p class="asp-campo__ayuda"><?php echo esc_html( $ayuda ); ?></p><?php endif; ?>
	</div>
	<?php
}

/**
 * Input de texto.
 */
function asp_campo_texto( string $nombre, string $etiqueta, string $valor, string $ayuda = '', bool $requerido = false, string $placeholder = '' ): void {
	$control = sprintf(
		'<input type="text" id="%1$s" name="%1$s" value="%2$s" class="regular-text" placeholder="%3$s">',
		esc_attr( $nombre ),
		esc_attr( $valor ),
		esc_attr( $placeholder )
	);
	asp_campo_envoltorio( $nombre, $etiqueta, $control, $ayuda, $requerido );
}

/**
 * Input de URL.
 */
function asp_campo_url( string $nombre, string $etiqueta, string $valor, string $ayuda = '', bool $requerido = false ): void {
	$control = sprintf(
		'<input type="url" id="%1$s" name="%1$s" value="%2$s" class="regular-text code" placeholder="https://" inputmode="url">',
		esc_attr( $nombre ),
		esc_attr( $valor )
	);
	asp_campo_envoltorio( $nombre, $etiqueta, $control, $ayuda, $requerido );
}

/**
 * Input de fecha nativo. El valor llega y se guarda como Ymd.
 */
function asp_campo_fecha( string $nombre, string $etiqueta, string $ymd, string $ayuda = '', bool $requerido = false ): void {
	$control = sprintf(
		'<input type="date" id="%1$s" name="%1$s" value="%2$s">',
		esc_attr( $nombre ),
		esc_attr( asp_fecha_ymd_a_input( $ymd ) )
	);
	asp_campo_envoltorio( $nombre, $etiqueta, $control, $ayuda, $requerido );
}

/**
 * Textarea.
 */
function asp_campo_textarea( string $nombre, string $etiqueta, string $valor, string $ayuda = '', int $filas = 3 ): void {
	$control = sprintf(
		'<textarea id="%1$s" name="%1$s" rows="%3$d" class="large-text">%2$s</textarea>',
		esc_attr( $nombre ),
		esc_textarea( $valor ),
		$filas
	);
	asp_campo_envoltorio( $nombre, $etiqueta, $control, $ayuda );
}

/**
 * Select simple.
 *
 * @param array<string,string> $opciones valor => etiqueta.
 */
function asp_campo_select( string $nombre, string $etiqueta, string $valor, array $opciones, string $ayuda = '', bool $requerido = false ): void {
	$html = sprintf( '<select id="%1$s" name="%1$s">', esc_attr( $nombre ) );
	foreach ( $opciones as $v => $texto ) {
		$html .= sprintf( '<option value="%1$s"%3$s>%2$s</option>', esc_attr( (string) $v ), esc_html( $texto ), selected( $valor, (string) $v, false ) );
	}
	$html .= '</select>';
	asp_campo_envoltorio( $nombre, $etiqueta, $html, $ayuda, $requerido );
}

/**
 * Radio de términos de una taxonomía.
 *
 * @param string $nombre     Nombre del campo.
 * @param string $etiqueta   Etiqueta.
 * @param string $taxonomia  Taxonomía.
 * @param string $slug_actual Slug seleccionado.
 */
function asp_campo_radio_terminos( string $nombre, string $etiqueta, string $taxonomia, string $slug_actual, string $ayuda = '', bool $requerido = false ): void {
	$terminos = get_terms( [ 'taxonomy' => $taxonomia, 'hide_empty' => false ] );
	$html     = '<div class="asp-radios" role="radiogroup">';
	if ( is_array( $terminos ) ) {
		foreach ( $terminos as $t ) {
			$id    = $nombre . '_' . $t->slug;
			$html .= sprintf(
				'<label for="%1$s"><input type="radio" id="%1$s" name="%2$s" value="%3$s"%5$s> %4$s</label>',
				esc_attr( $id ),
				esc_attr( $nombre ),
				esc_attr( $t->slug ),
				esc_html( $t->name ),
				checked( $slug_actual, $t->slug, false )
			);
		}
	}
	$html .= '</div>';
	asp_campo_envoltorio( $nombre, $etiqueta, $html, $ayuda, $requerido );
}

/**
 * Checkbox booleano.
 */
function asp_campo_checkbox( string $nombre, string $etiqueta, bool $marcado, string $ayuda = '' ): void {
	?>
	<div class="asp-campo asp-campo--<?php echo esc_attr( $nombre ); ?>">
		<label for="<?php echo esc_attr( $nombre ); ?>" class="asp-campo__check">
			<input type="checkbox" id="<?php echo esc_attr( $nombre ); ?>" name="<?php echo esc_attr( $nombre ); ?>" value="1" <?php checked( $marcado ); ?>>
			<?php echo esc_html( $etiqueta ); ?>
		</label>
		<?php if ( $ayuda ) : ?><p class="asp-campo__ayuda"><?php echo esc_html( $ayuda ); ?></p><?php endif; ?>
	</div>
	<?php
}

/**
 * Lista de checkboxes de posts (relación múltiple).
 *
 * @param WP_Post[] $posts        Opciones.
 * @param int[]     $seleccionados IDs marcados.
 * @param string    $vacio        Texto si no hay opciones.
 */
function asp_campo_checkboxes_posts( string $nombre, string $etiqueta, array $posts, array $seleccionados, string $ayuda = '', string $vacio = '' ): void {
	$html = '<div class="asp-checks">';
	if ( empty( $posts ) ) {
		$html .= '<p class="asp-campo__ayuda">' . esc_html( $vacio ) . '</p>';
	}
	foreach ( $posts as $p ) {
		$id    = $nombre . '_' . $p->ID;
		$html .= sprintf(
			'<label for="%1$s"><input type="checkbox" id="%1$s" name="%2$s[]" value="%3$d"%5$s> %4$s</label>',
			esc_attr( $id ),
			esc_attr( $nombre ),
			$p->ID,
			esc_html( get_the_title( $p ) ),
			checked( in_array( $p->ID, $seleccionados, true ), true, false )
		);
	}
	$html .= '</div>';
	asp_campo_envoltorio( $nombre, $etiqueta, $html, $ayuda );
}

/**
 * Select de posts (relación única) con opción "Ninguna".
 *
 * @param WP_Post[] $posts Opciones.
 */
function asp_campo_select_posts( string $nombre, string $etiqueta, array $posts, int $actual, string $ayuda = '', string $ninguna = '' ): void {
	$opciones = [ '0' => $ninguna ?: __( 'Ninguna', 'asp' ) ];
	foreach ( $posts as $p ) {
		$opciones[ (string) $p->ID ] = get_the_title( $p );
	}
	asp_campo_select( $nombre, $etiqueta, (string) $actual, $opciones, $ayuda );
}

/**
 * Selector de imagen con el selector de medios del core.
 */
function asp_campo_imagen( string $nombre, string $etiqueta, int $attachment_id, string $ayuda = '' ): void {
	$preview = $attachment_id ? wp_get_attachment_image( $attachment_id, 'medium', false, [ 'class' => 'asp-imagen__preview' ] ) : '';
	$html    = sprintf(
		'<div class="asp-imagen" data-asp-imagen>
			<input type="hidden" id="%1$s" name="%1$s" value="%2$d">
			<div class="asp-imagen__marco"%5$s>%3$s</div>
			<p class="asp-imagen__botones">
				<button type="button" class="button" data-asp-elegir>%4$s</button>
				<button type="button" class="button-link-delete" data-asp-quitar%6$s>%7$s</button>
			</p>
		</div>',
		esc_attr( $nombre ),
		$attachment_id,
		$preview, // phpcs:ignore
		esc_html__( 'Elegir imagen', 'asp' ),
		$preview ? '' : ' hidden',
		$attachment_id ? '' : ' hidden',
		esc_html__( 'Quitar', 'asp' )
	);
	asp_campo_envoltorio( $nombre, $etiqueta, $html, $ayuda );
}

/**
 * Editor de texto enriquecido del core.
 */
function asp_campo_wysiwyg( string $nombre, string $etiqueta, string $valor, string $ayuda = '' ): void {
	?>
	<div class="asp-campo asp-campo--<?php echo esc_attr( $nombre ); ?>">
		<label class="asp-campo__etiqueta" for="<?php echo esc_attr( $nombre ); ?>"><?php echo esc_html( $etiqueta ); ?></label>
		<?php
		wp_editor(
			$valor,
			$nombre,
			[
				'textarea_name' => $nombre,
				'textarea_rows' => 8,
				'media_buttons' => false,
				'teeny'         => true,
				'quicktags'     => false,
			]
		);
		?>
		<?php if ( $ayuda ) : ?><p class="asp-campo__ayuda"><?php echo esc_html( $ayuda ); ?></p><?php endif; ?>
	</div>
	<?php
}

/**
 * Repetidor del programa: filas de día, hora, título y orador.
 *
 * @param array<int, array<string,string>> $filas Filas guardadas.
 */
function asp_campo_programa( string $nombre, string $etiqueta, array $filas, string $ayuda = '' ): void {
	$fila_html = static function ( int $i, array $fila ) use ( $nombre ): string {
		return sprintf(
			'<div class="asp-repetidor__fila" data-asp-fila>
				<input type="date" name="%1$s[%2$s][dia]" value="%3$s" aria-label="%7$s">
				<input type="text" name="%1$s[%2$s][hora]" value="%4$s" placeholder="%8$s" aria-label="%8$s" class="asp-repetidor__hora">
				<input type="text" name="%1$s[%2$s][titulo]" value="%5$s" placeholder="%9$s" aria-label="%9$s" class="asp-repetidor__titulo">
				<input type="text" name="%1$s[%2$s][orador]" value="%6$s" placeholder="%10$s" aria-label="%10$s">
				<button type="button" class="button-link-delete" data-asp-quitar-fila aria-label="%11$s">&times;</button>
			</div>',
			esc_attr( $nombre ),
			esc_attr( (string) $i ),
			esc_attr( asp_fecha_ymd_a_input( $fila['dia'] ?? '' ) ),
			esc_attr( $fila['hora'] ?? '' ),
			esc_attr( $fila['titulo'] ?? '' ),
			esc_attr( $fila['orador'] ?? '' ),
			esc_attr__( 'Día', 'asp' ),
			esc_attr__( 'Hora', 'asp' ),
			esc_attr__( 'Título de la sesión', 'asp' ),
			esc_attr__( 'Quién la da', 'asp' ),
			esc_attr__( 'Quitar fila', 'asp' )
		);
	};
	$html = '<div class="asp-repetidor" data-asp-repetidor data-asp-nombre="' . esc_attr( $nombre ) . '">';
	$html .= '<div class="asp-repetidor__cabecera"><span>' . esc_html__( 'Día', 'asp' ) . '</span><span>' . esc_html__( 'Hora', 'asp' ) . '</span><span>' . esc_html__( 'Sesión', 'asp' ) . '</span><span>' . esc_html__( 'Quién', 'asp' ) . '</span><span></span></div>';
	$html .= '<div data-asp-filas>';
	foreach ( array_values( $filas ) as $i => $fila ) {
		$html .= $fila_html( $i, $fila );
	}
	$html .= '</div>';
	$html .= '<template data-asp-plantilla>' . $fila_html( 0, [] ) . '</template>';
	$html .= '<p><button type="button" class="button" data-asp-agregar-fila>' . esc_html__( 'Agregar fila', 'asp' ) . '</button></p>';
	$html .= '</div>';
	asp_campo_envoltorio( $nombre, $etiqueta, $html, $ayuda );
}

/* ------------------------------------------------------------------------
   Assets del panel
   --------------------------------------------------------------------- */

/**
 * CSS y JS solo en las pantallas de edición de nuestros CPT.
 *
 * @param string $hook Pantalla actual.
 * @return void
 */
function asp_admin_assets( string $hook ): void {
	if ( ! in_array( $hook, [ 'post.php', 'post-new.php' ], true ) ) {
		return;
	}
	$screen = get_current_screen();
	if ( ! $screen || ! in_array( $screen->post_type, asp_cpts_con_meta(), true ) ) {
		return;
	}
	wp_enqueue_media();
	wp_enqueue_style( 'asp-admin', asp_asset_url( 'assets/css/admin.css' ), [], asp_asset_version( 'assets/css/admin.css' ) );
	wp_enqueue_script( 'asp-admin', asp_asset_url( 'assets/js/admin.js' ), [ 'media-editor' ], asp_asset_version( 'assets/js/admin.js' ), [ 'in_footer' => true ] );
	wp_localize_script(
		'asp-admin',
		'aspAdmin',
		[
			'elegirTitulo' => __( 'Elegir imagen', 'asp' ),
			'elegirBoton'  => __( 'Usar esta imagen', 'asp' ),
		]
	);
}
add_action( 'admin_enqueue_scripts', 'asp_admin_assets' );

/**
 * Quita de la pantalla de nuestros CPT las cajas que confunden.
 *
 * @return void
 */
function asp_quitar_cajas_nativas(): void {
	foreach ( asp_cpts_con_meta() as $cpt ) {
		remove_meta_box( 'postcustom', $cpt, 'normal' );
		remove_meta_box( 'slugdiv', $cpt, 'normal' );
		remove_meta_box( 'trackbacksdiv', $cpt, 'normal' );
		remove_meta_box( 'commentstatusdiv', $cpt, 'normal' );
		remove_meta_box( 'commentsdiv', $cpt, 'normal' );
	}
}
add_action( 'add_meta_boxes', 'asp_quitar_cajas_nativas', 20 );

/**
 * Placeholder del título en castellano llano.
 *
 * @param string  $texto Placeholder.
 * @param WP_Post $post  Post.
 * @return string
 */
function asp_placeholder_titulo( string $texto, WP_Post $post ): string {
	$placeholders = [
		'evento'     => __( 'Nombre del evento, ej. El cuidado de las almas', 'asp' ),
		'iniciativa' => __( 'Nombre de la iniciativa', 'asp' ),
		'persona'    => __( 'Nombre y apellido', 'asp' ),
		'aliado'     => __( 'Nombre del ministerio o iglesia', 'asp' ),
		'predicacion' => __( 'Título de la predicación o sesión', 'asp' ),
	];
	return $placeholders[ $post->post_type ] ?? $texto;
}
add_filter( 'enter_title_here', 'asp_placeholder_titulo', 10, 2 );
