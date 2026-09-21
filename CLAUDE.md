# Ante Su Palabra — tema WordPress a medida

Contexto permanente del proyecto. Leelo entero antes de escribir código.

## Qué estamos construyendo

Sitio nuevo para **Ante Su Palabra**, una comunión de pastores e iglesias locales
(Argentina y Estados Unidos). Reemplaza dos WordPress separados —el institucional
y `blog.antesupalabra.com`— por uno solo.

El brazo principal del ministerio son los **eventos**: 4 a 6 conferencias y
talleres por año. Todo el sitio se ordena alrededor de eso.

Documentos de referencia en `docs/`:

- `proyecto.md` — relevamiento, decisiones y alcance de fase 1. La fuente de verdad.
- `modelo-de-datos.md` — CPTs, campos (meta boxes propios), lógica de estados.
- `plantillas.md` — mapa de archivos del tema.
- `sesiones.md` — el orden en que construir.

## Stack

- WordPress clásico, tema a medida. Slug del tema: `asp`. Text domain: `asp`.
- PHP 8.2+, plantillas propias.
- **Sin plugins.** Los campos son meta boxes propios en `inc/`, guardados en post
  meta con `register_post_meta`. Ninguna dependencia de terceros ni licencia.
  Única excepción: el importador de WordPress se usa **una vez** para migrar el
  blog y se borra el mismo día.
- CSS propio con custom properties. Sin framework CSS.
- JavaScript mínimo, vanilla, sin build step ni framework.

## Reglas duras

Estas no se negocian. Si una tarea parece pedir lo contrario, pará y preguntá.

1. **Sin Elementor ni ningún constructor visual.** El sitio anterior se rompió
   justamente por eso: los eventos estaban maquetados a mano dentro de páginas de
   Elementor y quedaron desactualizados durante dos años. No instalar Elementor,
   Divi, WPBakery, Bricks ni bloques de terceros. Tampoco plugins de campos (ACF,
   Meta Box, Pods): los campos se escriben en el tema. Formulario, sitemap y SEO
   básico salen del tema y del core; backups y seguridad son del hosting.
2. **Ninguna vista contiene un evento escrito a mano.** Home, listado, archivo y
   widgets consultan el mismo CPT. Una sola fuente de datos.
3. **El estado del evento se calcula, no se carga.** Cuando pasa la fecha de fin,
   el evento sale de "próximos" y entra al archivo sin que nadie toque nada.
4. **La ficha tiene que verse bien vacía.** Cada bloque —oradores, programa, sede,
   aliados, precio— se renderiza solo si tiene contenido. Nunca imprimir
   "Por confirmar", "TBD", ni secciones vacías con placeholder.
5. **El flyer no se recorta.** Se muestra contenido sobre una banda tonal
   (`object-fit: contain`). Los flyers se diseñan para Instagram en 4:5 o cuadrado;
   recortarlos a apaisado corta los títulos. Esto ya pasa en el sitio actual.
6. **Los cinco campos mínimos bloquean la publicación.** Título, fecha inicio,
   fecha fin, ciudad/país y —solo cuando el estado es "Inscripción abierta"— URL
   de registro.
   Quien carga viene de redes y su instinto es subir el flyer y nada más; el texto
   dentro de una imagen no lo lee Google ni un lector de pantalla, y no sirve para
   armar el listado ni el archivo.
7. **Nunca inventar contenido del ministerio.** Fechas, oradores, sedes, nombres de
   iglesias, textos doctrinales. Si falta un dato, dejar el hueco marcado con un
   `TODO:` visible en el código, no rellenar con algo plausible. Los 19 artículos de
   *Afirmaciones y Negaciones* se migran **verbatim**: son traducción del documento
   de T4G y contenido confesional del consejo pastoral. No reescribir, no resumir,
   no regenerar.
8. **Mobile primero.** La mayor parte del tráfico llega desde Instagram.
9. **Contraste AA como mínimo**, sobre todo en texto sobre fotografía.

## Quién usa el panel

Alguien del **área de redes** del ministerio, que entra cada dos o tres meses y no
recuerda nada de la vez anterior. Todo el admin se diseña asumiendo un usuario
siempre primerizo:

- El flujo principal de carga es **"Duplicar edición anterior"**, no "crear nuevo".
  Los eventos se repiten año a año; el 90% de las cargas son la edición siguiente
  de algo que ya existe.
- Formulario partido: los cinco campos obligatorios arriba y siempre visibles, el
  resto plegado abajo. Que puedan publicar el día que se define la fecha.
- Etiquetas en castellano llano con textos de ayuda. Nada de "meta", "slug",
  "taxonomía", "custom field".
- Rol `editor_eventos` con el panel podado a Eventos y Artículos. Nada más.

## Convenciones de código

- Prefijo de funciones, hooks, handles y opciones: `asp_`.
- Claves de post meta: `<cpt>_<campo>` (ej. `evento_fecha_inicio`), registradas
  con `register_post_meta` y sanitización propia. Meta boxes con id
  `asp_<cpt>_<bloque>` y nonce `asp_<cpt>_nonce`.
- Editor clásico forzado para los cuatro CPT con `use_block_editor_for_post_type`:
  es la única forma de que los cinco campos obligatorios queden arriba y juntos.
- Un archivo por responsabilidad en `inc/`, cargados desde `functions.php`.
- Nada de lógica en las plantillas: consultas y cálculos en `inc/`, las plantillas
  solo imprimen.
- Escapar siempre en la salida: `esc_html`, `esc_attr`, `esc_url`, `wp_kses_post`.
- Textos de interfaz con `__()` / `esc_html__()` y text domain `asp`, aunque el
  sitio sea monolingüe por ahora (fase 2 contempla inglés).
- CSS: custom properties en `assets/css/tokens.css`. Ningún valor de color,
  tipografía o espaciado hardcodeado fuera de ese archivo.
- Sin jQuery.

## Definición de "listo" para cada pieza

Una tarea no está terminada hasta que:

- Funciona con datos mínimos (evento con tres campos, sin flyer, sin oradores).
- Funciona en 390 px de ancho.
- No emite avisos de PHP con `WP_DEBUG` en true.
- El usuario `editor_eventos` puede hacer lo que tiene que hacer y no ve lo que no.

## Lo que NO entra en fase 1

Sitio bilingüe · donaciones · registro e inscripción propios (sigue Eventbrite y
Entrada27) · listado de iglesias en comunión · newsletter · galería de fotos por
evento · rebranding (el logo se conserva).

Adelantado a fase 1 el 11-9-2026 por decisión del ministerio: el archivo de
predicaciones de las conferencias en video (YouTube por oEmbed), audio o texto,
como CPT `predicacion` dentro de Recursos.

Si una tarea empieza a derivar hacia alguna de estas, pará y preguntá antes de
seguir.
