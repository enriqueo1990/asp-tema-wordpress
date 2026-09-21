# Plan de sesiones con Claude Code

Una sesión por bloque. No pedir "hacé el sitio": cada sesión tiene un alcance
cerrable y termina con algo que funciona.

Antes de empezar, en la raíz del proyecto tienen que estar `CLAUDE.md` y la
carpeta `docs/`. Claude Code lee `CLAUDE.md` solo; a `docs/` conviene apuntarlo
explícitamente en el primer prompt de cada sesión.

---

## Sesión 1 — Esqueleto del tema

> Leé `CLAUDE.md` y `docs/plantillas.md`. Creá el esqueleto del tema `asp` con la
> estructura de carpetas de ese documento: `style.css` con la cabecera,
> `functions.php` que solo hace `require` de `inc/`, y `inc/setup.php` y
> `inc/assets.php`. Encolá `tokens.css`, `base.css`, `layout.css` y
> `components.css` en ese orden, versionados con `filemtime`. Copiá
> `assets/tokens.css` desde el kit. Todavía sin plantillas: quiero el tema
> activable y sin avisos con `WP_DEBUG` en true.

## Sesión 2 — Tipos de contenido y taxonomías

> Leé `docs/modelo-de-datos.md`. Registrá los CPT `evento`, `iniciativa`,
> `persona` y `aliado` con los slugs y rewrites de ese documento, más la
> taxonomía `pais` con sus dos términos. Etiquetas del panel en castellano llano.
> `aliado` va con `public => false`. Al terminar, decime qué permalinks hay que
> vaciar.

## Sesión 3a — Campos del evento (meta boxes)

> Leé `docs/modelo-de-datos.md`. Sin plugins: implementá `inc/meta-helpers.php`
> (inputs, nonce, sanitización, editor clásico forzado) y `inc/meta-evento.php`
> con dos meta boxes: el obligatorio arriba y siempre abierto, el opcional plegado.
> Nombres, tipos y etiquetas exactamente como el documento. El país va como radio
> dentro del bloque obligatorio, no en la barra lateral. Después
> `inc/evento-validacion.php`: si al publicar faltan los cinco campos, o falta la
> URL con inscripción abierta, el evento vuelve a borrador con un aviso que dice
> qué falta. Probalo publicando con todo vacío.

## Sesión 3b — Repetidor, relaciones y los otros CPT

> El programa del evento es un repetidor de filas (día, hora, título, orador) en
> JavaScript vanilla con `<template>`, sin jQuery. Oradores y aliados como listas
> de checkboxes; iniciativa como select. Flyer con el selector de medios del core.
> Después `inc/meta-otros.php` con los campos de iniciativa, persona y aliado.
> Probá guardar, editar y vaciar cada campo sin avisos de PHP.

## Sesión 4 — Estados y consultas

> Leé la sección "Lógica de estado" y "Consultas" de `docs/modelo-de-datos.md`.
> Implementá `inc/evento-estado.php` con `asp_evento_estado()` y los helpers de
> render (etiqueta del badge, si va botón, texto del botón), y
> `inc/evento-queries.php` con `asp_eventos_proximos()`, `asp_eventos_pasados()` y
> `asp_evento_destacado()`. Escribí tests manuales: un evento con fechas pasadas,
> uno en curso, uno futuro con cada estado de inscripción, y uno sin fechas.

## Sesión 5 — Ficha de evento

> Leé `docs/plantillas.md`. Construí `single-evento.php` y las parts de
> `parts/evento/`. Regla número 4 de `CLAUDE.md`: cada bloque se renderiza solo si
> tiene contenido, sin "por confirmar" ni secciones vacías. El flyer va contenido
> con `object-fit: contain` sobre una banda tonal, nunca recortado. Probalo con un
> evento que tenga solo título, fechas y ciudad: tiene que verse bien igual.

## Sesión 6 — Listado y archivo

> `archive-evento.php` con los próximos ordenados por fecha, sin filtros, con
> enlace al archivo. `templates/page-eventos-pasados.php` con los pasados
> agrupados por año en orden descendente. Reutilizá `parts/evento/card.php`.

## Sesión 7 — Home

> `front-page.php` según `docs/plantillas.md`. Hero con el evento destacado.
> Importante: si no hay ningún evento próximo, el hero cae a la composición con
> Isaías 66:2 y la declaración de misión. Nunca queda vacío ni muestra un evento
> vencido — eso es exactamente lo que pasa hoy en el sitio viejo.

## Sesión 8 — Iniciativas y personas

> `archive-iniciativa.php`, `single-iniciativa.php` con las dos consultas inversas
> sobre `evento_iniciativa`, y `single-persona.php` que muestra lo que corresponda
> según `persona_roles`. La página Nosotros arma el consejo pastoral consultando
> personas con rol `consejo`.

## Sesión 9 — Recursos y migración

> `home.php` y `single.php` para los artículos, con paginación real (no AJAX).
> Después el import: te paso el WXR exportado del blog viejo. El importador de
> WordPress es la única excepción a "sin plugins": se instala, se corre una vez y
> se borra el mismo día. Mapeá autores a usuarios y vinculalos a fichas `persona`. Generá `docs/redirects.csv` con el
> mapa completo de 301 desde el subdominio y las tres categorías, y
> `inc/redirects.php` que lo lea.

## Sesión 10 — Afirmaciones y Negaciones

> `templates/page-afirmaciones.php`: los 19 artículos con índice lateral fijo,
> anclas `#articulo-i` a `#articulo-xix` y hoja de estilos de impresión. El texto
> se migra verbatim desde `/nosotros/` del sitio actual — es traducción del
> documento de T4G y contenido confesional del consejo. No lo reescribas ni lo
> resumas.

## Sesión 11 — El panel

La sesión que decide si el sitio sigue vivo en dos años.

> Leé la sección "Quién usa el panel" de `CLAUDE.md`. Implementá:
> el rol `editor_eventos` con capacidades acotadas; el podado del menú lateral a
> Eventos y Artículos para ese rol; la acción de fila **"Duplicar edición
> anterior"** que clona un evento entero como borrador y limpia solo fechas y URL
> de registro; columnas útiles en el listado (fecha, país, estado, destacado);
> y una página de ayuda dentro del panel con el paso a paso de la carga.
> Probá todo logueado como `editor_eventos`, no como administrador.

## Sesión 12 — Cierre

> `schema.org/Event` en JSON-LD en la ficha. Sitemap XML. Formulario de contacto.
> Revisión de rendimiento y de contraste AA. Recorrido final en 390 px de ancho.

---

## Cómo pedir las cosas

- **Una pieza por sesión.** "Implementá el CPT evento con estos campos y esta
  lógica de estado" funciona; "hacé el sitio" no.
- **Apuntá al documento**, no repitas el contenido: "leé
  `docs/modelo-de-datos.md`" mantiene una sola fuente de verdad.
- **Pedí el caso vacío explícitamente.** Es la regla que más se olvida y la que
  define si el sistema aguanta.
- Si Claude Code propone instalar un constructor visual, un plugin de eventos, un
  plugin de campos o un framework CSS, es señal de que perdió el `CLAUDE.md`. Recordáselo.

---

## Estado al 11 de septiembre de 2026

El canvas de Claude Design (`docs/diseno/`) se implementó de una vez sobre el
sitio local `asp-newsite.local`. Contra el plan de arriba:

| Sesión | Estado |
|---|---|
| 1 Esqueleto | Hecha y verificada en WordPress 7.1 |
| 2 CPT y taxonomías | Hecha (`inc/cpt.php`, `inc/taxonomias.php`) |
| 3a / 3b Meta boxes | Hechas: formulario partido, país como radio, repetidor del programa y selector de medios en vanilla, validación que devuelve a borrador con la lista de faltantes |
| 4 Estados y consultas | Hecha |
| 5 Ficha | Hecha, con JSON-LD |
| 6 Listado y archivo | Hecha, en una sola vista `/eventos/` |
| 7 Home | Hecha según el canvas |
| 8 Iniciativas y personas | Hecha en versión básica; Nosotros completa |
| 9 Recursos y migración | Plantillas y `inc/redirects.php` hechos, con `redirects.csv` en el tema. **Falta el WXR del blog** para importar los artículos y completar el CSV |
| 10 Afirmaciones | Texto migrado verbatim desde el sitio vivo; PDF pendiente |
| 11 Panel | Hecha: rol `editor_eventos` (ve Eventos y Artículos), columnas Fecha/País/Estado/Inicio, «Duplicar edición anterior», página de ayuda. Probada logueado como ese rol |
| 12 Cierre | schema.org y formulario de contacto propio hechos. Pendiente: fuentes autoalojadas, recorrido final con contenido real |

Lo que falta depende del ministerio, no del código: el WXR del blog, el link de Eventbrite 2026, las fotos, el email de contacto y el logo vectorial.
