<?php
/**
 * Artículos a todo el ancho: el último en grande (con su foto si tiene) y
 * al costado una lista de los siguientes, cada uno con su categoría arriba
 * del título.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

$asp_articulos = asp_articulos_portada( 5 );
if ( empty( $asp_articulos ) ) {
	return;
}
$asp_primero = array_shift( $asp_articulos );
?>
<section class="asp-inicio-articulos" aria-labelledby="inicio-articulos">
	<div class="asp-container">
		<h2 id="inicio-articulos" class="asp-seccion__titulo"><?php esc_html_e( 'Artículos', 'asp' ); ?></h2>
		<div class="asp-inicio-articulos__grilla<?php echo empty( $asp_articulos ) ? ' asp-inicio-articulos__grilla--uno' : ''; ?>">
			<div class="asp-inicio-articulos__principal">
				<?php get_template_part( 'parts/articulo/destacado', null, [ 'post_id' => $asp_primero->ID ] ); ?>
			</div>
			<?php if ( ! empty( $asp_articulos ) ) : ?>
				<ul class="asp-inicio-articulos__lista">
					<?php foreach ( $asp_articulos as $asp_post ) :
						$asp_rotulo = asp_inicio_articulo_rotulo( $asp_post->ID );
						$asp_autor  = asp_autor_articulo( $asp_post->ID );
						?>
						<li class="asp-inicio-articulos__item">
							<?php if ( $asp_rotulo ) : ?><span class="asp-chip"><?php echo esc_html( $asp_rotulo ); ?></span><?php endif; ?>
							<h3 class="asp-inicio-articulos__titulo"><a class="asp-inicio-articulos__enlace" href="<?php echo esc_url( get_permalink( $asp_post ) ); ?>"><?php echo esc_html( get_the_title( $asp_post ) ); ?></a></h3>
							<p class="asp-inicio-articulos__meta"><?php echo esc_html( implode( ' · ', array_filter( [ $asp_autor['nombre'], asp_fecha_articulo( $asp_post->ID ) ] ) ) ); ?></p>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
		<a class="asp-cta-link" href="<?php echo esc_url( asp_url_articulos() ); ?>"><?php esc_html_e( 'Todos los artículos', 'asp' ); ?></a>
	</div>
</section>
