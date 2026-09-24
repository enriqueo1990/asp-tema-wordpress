<?php
/**
 * /recursos/ — índice de recursos, y /recursos/articulos/ — el archivo.
 *
 * El índice muestra una selección de cada tipo, cada uno con su forma:
 * artículos destacados (uno grande y dos medianos), la serie en banda de
 * color, la última tanda de predicaciones en banda oscura, artículos
 * recientes en lista compacta y las conferencias realizadas. Cada bloque
 * tiene su salida al listado completo. Una sección sin contenido no se
 * imprime.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

if ( asp_es_archivo_articulos() ) {
	get_template_part( 'parts/recursos/archivo' );
	return;
}

get_header();

$asp_pagina_id  = (int) get_option( 'page_for_posts' );
$asp_titulo     = $asp_pagina_id ? get_the_title( $asp_pagina_id ) : __( 'Recursos', 'asp' );
$asp_bajada     = $asp_pagina_id ? wp_strip_all_tags( (string) get_post_field( 'post_content', $asp_pagina_id ) ) : '';
$asp_temas      = asp_recursos_temas();
$asp_series     = asp_recursos_series();
$asp_destacados = asp_articulos_sueltos( 3 );
$asp_recientes  = asp_articulos_sueltos( 5, wp_list_pluck( $asp_destacados, 'ID' ) );
$asp_total_art  = (int) wp_count_posts( 'post' )->publish;
$asp_coleccion  = asp_predicaciones_coleccion_reciente( 9 );
$asp_pasados    = asp_eventos_pasados_por_anio();
$asp_n_pasados  = array_sum( array_map( 'count', $asp_pasados ) );
$asp_buscar_id  = 'buscar-recursos';
?>
<div class="asp-container">
	<header class="asp-recursos__cab">
		<div class="asp-stack asp-stack--3">
			<h1 class="asp-pagina__titulo"><?php echo esc_html( $asp_titulo ); ?></h1>
			<?php if ( $asp_bajada ) : ?><p class="asp-recursos__bajada"><?php echo esc_html( $asp_bajada ); ?></p><?php endif; ?>
		</div>
		<div class="asp-recursos__entrada">
			<form class="asp-buscador" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<label class="visually-hidden" for="<?php echo esc_attr( $asp_buscar_id ); ?>"><?php esc_html_e( 'Buscar artículos', 'asp' ); ?></label>
				<input class="asp-buscador__campo" id="<?php echo esc_attr( $asp_buscar_id ); ?>" type="search" name="s" placeholder="<?php esc_attr_e( 'Buscar artículos', 'asp' ); ?>" enterkeyhint="search">
				<input type="hidden" name="post_type" value="post">
				<button class="asp-buscador__boton" type="submit"><?php esc_html_e( 'Buscar', 'asp' ); ?></button>
			</form>
			<?php if ( ! empty( $asp_temas ) ) : ?>
				<nav aria-label="<?php esc_attr_e( 'Temas', 'asp' ); ?>">
					<ul class="asp-temas">
						<?php foreach ( $asp_temas as $asp_tema ) : ?>
							<li><a class="asp-tema" href="<?php echo esc_url( get_category_link( $asp_tema ) ); ?>"><?php echo esc_html( $asp_tema->name ); ?> <span class="asp-tema__n"><?php echo esc_html( (string) $asp_tema->count ); ?></span></a></li>
						<?php endforeach; ?>
					</ul>
				</nav>
			<?php endif; ?>
		</div>
	</header>
</div>

<?php if ( ! empty( $asp_destacados ) ) : ?>
	<section class="asp-container asp-recursos-bloque asp-recursos-bloque--primero" aria-labelledby="recursos-destacados">
		<h2 id="recursos-destacados" class="visually-hidden"><?php esc_html_e( 'Artículos destacados', 'asp' ); ?></h2>
		<div class="asp-recursos-destacados<?php echo count( $asp_destacados ) > 1 ? '' : ' asp-recursos-destacados--solo'; ?>">
			<?php get_template_part( 'parts/articulo/destacado', null, [ 'post_id' => $asp_destacados[0]->ID, 'rotulo' => __( 'Destacado', 'asp' ) ] ); ?>
			<?php if ( count( $asp_destacados ) > 1 ) : ?>
				<div class="asp-recursos-destacados__lado">
					<?php foreach ( array_slice( $asp_destacados, 1 ) as $asp_post ) : ?>
						<?php get_template_part( 'parts/articulo/tarjeta', null, [ 'post_id' => $asp_post->ID, 'variante' => 'mediana', 'fecha' => true ] ); ?>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</section>
<?php endif; ?>

<?php foreach ( $asp_series as $asp_term ) : ?>
	<?php get_template_part( 'parts/articulo/serie-banda', null, [ 'term' => $asp_term ] ); ?>
<?php endforeach; ?>

<?php if ( $asp_coleccion ) : ?>
	<div class="asp-banda-oscura">
		<section class="asp-container asp-recursos-bloque" aria-labelledby="recursos-predicaciones">
			<div class="asp-editorial__cab asp-cab-suelto">
				<h2 id="recursos-predicaciones" class="asp-seccion__titulo"><?php esc_html_e( 'Predicaciones', 'asp' ); ?></h2>
				<a class="asp-cta-link" href="<?php echo esc_url( (string) get_post_type_archive_link( 'predicacion' ) ); ?>"><?php esc_html_e( 'Todas las predicaciones', 'asp' ); ?></a>
			</div>
			<?php asp_buscador_predicaciones( 'banda' ); ?>
			<div class="asp-coleccion">
				<div class="asp-coleccion__cab">
					<span class="asp-label"><?php esc_html_e( 'Lo último', 'asp' ); ?></span>
					<?php if ( $asp_coleccion['titulo'] ) : ?>
						<a class="asp-coleccion__titulo" href="<?php echo esc_url( get_permalink( $asp_coleccion['evento'] ) ); ?>"><?php echo esc_html( $asp_coleccion['titulo'] ); ?></a>
					<?php endif; ?>
					<span class="asp-coleccion__meta">
						<?php
						echo esc_html(
							implode(
								' · ',
								array_filter(
									[
										$asp_coleccion['fecha'],
										/* translators: %d: cantidad de mensajes */
										count( $asp_coleccion['items'] ) < $asp_coleccion['total']
											/* translators: 1: mensajes mostrados, 2: total */
											? sprintf( __( '%1$d de %2$d mensajes', 'asp' ), count( $asp_coleccion['items'] ), $asp_coleccion['total'] )
											: sprintf( _n( '%d mensaje', '%d mensajes', $asp_coleccion['total'], 'asp' ), $asp_coleccion['total'] ),
									]
								)
							)
						);
						?>
					</span>
				</div>
				<div class="asp-grilla-predicaciones">
					<?php foreach ( $asp_coleccion['items'] as $asp_post ) : ?>
						<?php get_template_part( 'parts/predicacion/tarjeta', null, [ 'post_id' => $asp_post->ID ] ); ?>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	</div>
<?php endif; ?>

<?php if ( ! empty( $asp_recientes ) ) : ?>
	<section class="asp-container asp-recursos-bloque" aria-labelledby="recursos-recientes">
		<div class="asp-editorial__cab asp-cab-suelto">
			<h2 id="recursos-recientes" class="asp-seccion__titulo"><?php esc_html_e( 'Más artículos', 'asp' ); ?></h2>
			<a class="asp-cta-link" href="<?php echo esc_url( asp_url_articulos() ); ?>">
				<?php
				/* translators: %d: cantidad total de artículos */
				echo esc_html( sprintf( __( 'Ver los %d artículos', 'asp' ), $asp_total_art ) );
				?>
			</a>
		</div>
		<div class="asp-lista-compacta">
			<?php foreach ( $asp_recientes as $asp_post ) : ?>
				<?php get_template_part( 'parts/articulo/compacto', null, [ 'post_id' => $asp_post->ID ] ); ?>
			<?php endforeach; ?>
		</div>
	</section>
<?php endif; ?>

<?php if ( $asp_n_pasados > 0 && $asp_n_pasados < 3 ) : ?>
	<?php
	/* Con uno o dos eventos realizados, un archivo por año con un "2026"
	   enorme se veía vacío: alcanza con una línea. */
	?>
	<section class="asp-container asp-recursos-bloque asp-recursos-bloque--linea" aria-labelledby="recursos-conferencias">
		<div class="asp-conferencias-linea">
			<h2 id="recursos-conferencias" class="asp-label"><?php esc_html_e( 'Conferencias realizadas', 'asp' ); ?></h2>
			<p class="asp-conferencias-linea__lista">
				<?php
				$asp_links = [];
				foreach ( $asp_pasados as $asp_anio => $asp_eventos ) {
					foreach ( $asp_eventos as $asp_post ) {
						$asp_links[] = '<a href="' . esc_url( get_permalink( $asp_post ) ) . '">' . esc_html( get_the_title( $asp_post ) ) . '</a> <span class="asp-conferencias-linea__anio">· ' . esc_html( (string) $asp_anio ) . '</span>';
					}
				}
				echo implode( ' · ', $asp_links ); // phpcs:ignore WordPress.Security.EscapeOutput
				?>
			</p>
			<a class="asp-cta-link" href="<?php echo esc_url( asp_url_eventos() ); ?>"><?php esc_html_e( 'Archivo de eventos', 'asp' ); ?></a>
		</div>
	</section>
<?php elseif ( $asp_n_pasados >= 3 ) : ?>
	<section class="asp-container asp-recursos-bloque" aria-labelledby="recursos-conferencias">
		<div class="asp-editorial__cab asp-cab-suelto">
			<h2 id="recursos-conferencias" class="asp-seccion__titulo"><?php esc_html_e( 'Conferencias realizadas', 'asp' ); ?></h2>
			<a class="asp-cta-link" href="<?php echo esc_url( asp_url_eventos() ); ?>"><?php esc_html_e( 'Archivo de eventos', 'asp' ); ?></a>
		</div>
		<div class="asp-stack asp-stack--6">
			<?php foreach ( $asp_pasados as $asp_anio => $asp_eventos ) : ?>
				<div class="asp-stack asp-stack--3">
					<div class="asp-anio"><span class="asp-anio__num"><?php echo esc_html( (string) $asp_anio ); ?></span></div>
					<div>
						<?php foreach ( $asp_eventos as $asp_post ) : ?>
							<?php get_template_part( 'parts/evento/archivo-item', null, [ 'post_id' => $asp_post->ID ] ); ?>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</section>
<?php endif; ?>

<?php if ( empty( $asp_destacados ) && empty( $asp_series ) && ! $asp_coleccion && ! $asp_n_pasados ) : ?>
	<div class="asp-container asp-section"><p class="asp-muted"><?php esc_html_e( 'Todavía no hay recursos publicados.', 'asp' ); ?></p></div>
<?php endif; ?>
<?php
get_footer();
