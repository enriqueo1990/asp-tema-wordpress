# Ante Su Palabra — tema WordPress a medida

Tema propio para el sitio de **Ante Su Palabra**, una comunión de pastores e
iglesias locales de Argentina y Estados Unidos. Reemplaza dos WordPress
separados —el institucional y el blog en un subdominio— por uno solo, ordenado
alrededor de los eventos: las 4 a 6 conferencias y talleres que el ministerio
hace cada año.

## El problema que resuelve

En el sitio anterior los eventos no eran contenido: estaban maquetados a mano
dentro de páginas de Elementor. No había tipo de contenido, ni campos, ni
estados, ni archivo. La consecuencia previsible es que quedaron desactualizados
durante dos años, con botones de inscripción apuntando a ediciones vencidas y
una home que decía una cosa y un listado que decía otra.

Acá el evento es un tipo de contenido con campos propios. Cuando pasa la fecha
de fin sale solo de "próximos" y entra al archivo, sin que nadie toque nada.

## Reglas de diseño

1. **Sin plugins.** Ningún constructor visual, ningún plugin de campos, ninguna
   dependencia con licencia. Los campos son meta boxes escritos en el tema y
   guardados con `register_post_meta`. El formulario de contacto, el sitemap y
   el SEO básico salen del tema y del core.
2. **Una sola fuente de datos.** Ninguna vista contiene un evento escrito a
   mano: home, listado, archivo y fichas consultan el mismo CPT.
3. **El estado se calcula, no se carga.** Lo derivan las fechas.
4. **La ficha se ve bien vacía.** Cada bloque —oradores, programa, sede,
   aliados, precio— se renderiza solo si tiene contenido. Nunca un "Por
   confirmar" ni una sección con placeholder.
5. **El flyer no se recorta.** Se muestra contenido sobre una banda tonal: los
   flyers se diseñan para Instagram en 4:5 o cuadrado y recortarlos a apaisado
   corta los títulos.
6. **Cinco campos mínimos bloquean la publicación.** Título, fecha de inicio,
   fecha de fin, ciudad/país y —solo con inscripción abierta— URL de registro.
   El texto dentro de una imagen no lo lee Google ni un lector de pantalla.
7. **Mobile primero** y contraste AA como mínimo. La mayor parte del tráfico
   llega desde Instagram.

## Stack

WordPress clásico con editor clásico forzado para los CPT, PHP 8.2+, CSS propio
con custom properties y JavaScript vanilla. Sin framework CSS, sin build step,
sin jQuery, sin dependencias de terceros.

## Contenido

| Tipo | Slug | Para qué |
|---|---|---|
| `evento` | `/eventos/` | El núcleo. Fechas, sede, oradores, programa, aliados, flyer, estado calculado |
| `persona` | `/personas/` | Consejo pastoral, oradores y autores |
| `iniciativa` | `/iniciativas/` | Las cuatro líneas de trabajo del ministerio |
| `predicacion` | `/recursos/predicaciones/` | Archivo de conferencias en video, audio o texto |
| `post` | `/recursos/` | Artículos, agrupables en series |

Taxonomías: `pais` sobre eventos (se carga como radio dentro del meta box
obligatorio) y `serie` sobre artículos.

## El panel

Está diseñado para alguien del área de redes que entra cada dos o tres meses y
no recuerda nada de la vez anterior:

- El flujo principal es **"Duplicar edición anterior"**, no "crear nuevo": los
  eventos se repiten año a año y casi toda carga es la edición siguiente de algo
  que ya existe. Clona el evento entero como borrador y limpia solo las fechas y
  la URL de registro.
- **Formulario partido**: los cinco campos obligatorios arriba y siempre
  visibles, el resto plegado abajo. Se puede publicar el día que se define la
  fecha.
- La validación devuelve el evento a borrador con la lista de lo que falta.
- Rol `editor_eventos` con el menú podado a Eventos y Artículos, y una página de
  ayuda con el paso a paso.
- Etiquetas en castellano llano. Nada de "meta", "slug" ni "taxonomía".

## Estructura

```
wp-content/themes/asp/
├── functions.php          solo carga los módulos de inc/
├── inc/                   una responsabilidad por archivo: CPT, meta boxes,
│                          estados, consultas, panel, redirects, contacto
├── parts/                 fragmentos por tipo de contenido
├── templates/             plantillas de página
├── assets/css/            fuentes → tokens → base → layout → components
└── assets/fonts/          Newsreader y Archivo autoalojadas (OFL 1.1)
docs/                      relevamiento, modelo de datos, mapa de plantillas
tools/                     scripts de carga y mantenimiento del sitio local
```

Los valores de color, tipografía y espaciado viven **solo** en
`assets/css/tokens.css`. Nada hardcodeado fuera de ese archivo.

## Trabajar en local

El tema se enlaza dentro de un WordPress local:

```
ln -s "$PWD/wp-content/themes/asp" /ruta/al/wordpress/wp-content/themes/asp
```

Los scripts de `tools/` cargan WordPress desde `tools/arranque.php`, que toma la
ruta de la variable de entorno `ASP_WP_PATH` (si no está, prueba la ubicación
por defecto de Local) y el dominio de `ASP_WP_HOST`:

```
ASP_WP_PATH="/ruta/al/app/public" php tools/seed-local.php
```

| Script | Para qué |
|---|---|
| `seed-local.php` | Carga el contenido real del relevamiento |
| `seed-demo.php` | Contenido de muestra para ver las vistas llenas. Todo marcado con `_asp_demo` |
| `limpiar-demo.php` | Borra todo lo de muestra. Correr antes de ir a producción |
| `fotos-consejo.php` | Trae las fotos del consejo pastoral del sitio actual |
| `importar-youtube.php` | Importa videos del canal como predicaciones, idempotente por id |

## Estado

El tema está completo en código y probado en local con `WP_DEBUG` en true y el
panel recorrido como `editor_eventos`, no como administrador.

Pendiente, y depende del ministerio más que del código:

- El WXR del blog viejo, para importar los artículos y completar `redirects.csv`.
- La URL de inscripción de la próxima edición de "El cuidado de las almas".
- Fotos, email de contacto y logo vectorial (hoy solo existe un PNG de 2018).
- Recorrido final con contenido real en 390 px de ancho.

Fuera del alcance de esta fase: sitio bilingüe, donaciones, inscripción propia
(sigue en Eventbrite y Entrada27), listado de iglesias, newsletter y galería de
fotos por evento.

## Documentación

| Archivo | Qué contiene |
|---|---|
| `CLAUDE.md` | Contexto permanente: stack, reglas duras, convenciones, quién usa el panel |
| `docs/proyecto.md` | Relevamiento del sitio anterior, decisiones y alcance de fase 1 |
| `docs/modelo-de-datos.md` | CPTs, campos, lógica de estados y consultas |
| `docs/plantillas.md` | Mapa de archivos del tema y qué renderiza cada vista |
| `docs/sesiones.md` | El orden en que se construyó, sesión por sesión |

## Licencia

GPL v2 o posterior, como WordPress.
