# Mapa del tema

Estado al 11 de septiembre de 2026: construido según el canvas de Claude
Design guardado en `docs/diseno/Ante Su Palabra - Canvas.dc.html`.

```
wp-content/themes/asp/
├── style.css                    Cabecera del tema únicamente
├── functions.php                Solo requires de inc/
├── header.php / footer.php      Apertura y cierre del documento; llaman a parts/header/
│
├── inc/
│   ├── setup.php                supports, menús, tamaños de imagen
│   ├── assets.php               encolado: fuentes → tokens → base → layout → components, app.js
│   ├── cpt.php                  evento, iniciativa, persona, aliado · editor clásico forzado
│   ├── taxonomias.php           pais (eventos) y serie (artículos)
│   ├── meta-campos.php          register_post_meta: nombres, tipos y sanitización
│   ├── evento-estado.php        asp_evento_estado(), fechas en castellano, helpers de render
│   ├── evento-queries.php       proximos, pasados por año, destacado, inversas, personas, series
│   ├── predicaciones.php        consultas, fecha efectiva, formato y reproductor de predicaciones
│   ├── template-tags.php        logo, menú con fallback, ícono de lugar, foto de persona, schema.org
│   ├── customizer.php           fotos de heros y galería, eslogan, misión, redes, email, PDF
│   ├── afirmaciones.php         acceso a los datos y numeración romana
│   ├── afirmaciones-datos.php   los 19 artículos, VERBATIM del sitio actual
│   ├── meta-helpers.php         inputs, nonces, sanitización, assets del panel
│   ├── meta-evento.php          meta boxes de evento: obligatorio arriba, opcional plegado
│   ├── meta-otros.php           meta boxes de iniciativa, persona y aliado
│   ├── evento-validacion.php    bloqueo de publicación sin los cinco campos
│   ├── admin-rol.php            rol editor_eventos y capacidades por CPT
│   ├── admin-ui.php             podado del panel, columnas, orden
│   ├── admin-duplicar.php       "Duplicar edición anterior"
│   ├── admin-ayuda.php          página de ayuda dentro del panel
│   ├── contacto.php             formulario de contacto sin plugin
│   └── redirects.php            301 del blog viejo, lee redirects.csv
│
├── redirects.csv                mapa origen,destino del blog viejo
│
├── templates/
│   ├── page-nosotros.php        Nosotros: hero, qué nos une, consejo, Afirmaciones y Negaciones, próximo evento
│   └── page-contacto.php        Contacto: email, redes y formulario
│
├── parts/
│   ├── header/site-header.php   logo, nav, acceso al próximo evento, menú móvil a pantalla completa;
│   │                            transparente sobre la portada con foto y sólida al hacer scroll (app.js)
│   ├── header/site-footer.php   logo, nav, redes, email
│   ├── evento/hero.php          Hero del inicio: foto con velo, versículo o eslogan, último evento
│   ├── evento/franja.php        Franja "Próximo evento" bajo el hero
│   ├── evento/card.php          Tarjeta de evento (listado); variante ancha en escritorio
│   ├── evento/archivo-item.php  Fila del archivo por año
│   ├── evento/fila.php          Fila compacta (home)
│   ├── evento/badge.php         Badge de estado
│   ├── evento/fecha.php         Fecha: inline, xl o grande entre filetes
│   ├── evento/lugar.php         Ciudad y país, tipográfico, sin banderas
│   ├── evento/flyer.php         Banda tonal con el flyer contenido; nada si no hay flyer
│   ├── evento/cta.php           Botón según estado; nada en reserva/realizado
│   ├── evento/sede.php          Se autooculta si está vacío
│   ├── evento/oradores.php      Lista densa con bio en <details>; se autooculta
│   ├── evento/programa.php      Filas por día; se autooculta
│   ├── evento/aliados.php       Se autooculta
│   ├── articulo/card.php        Fila de artículo
│   ├── articulo/serie.php       Caja de serie con artículos numerados
│   ├── persona/card.php         Box del consejo pastoral
│   ├── persona/retrato.php      Retrato 4:5 para filas (home)
│   ├── predicacion/fila.php     Fila de predicación: formato, título, orador, pasaje, evento, fecha
│   └── iniciativa/card.php
│
├── assets/
│   ├── css/tokens.css           Custom properties. Único lugar con valores. Dirección del canvas
│   ├── css/base.css             Reset, tipografía, rótulos, prosa
│   ├── css/layout.css           Contenedores, cabecera, pie, grillas
│   ├── css/components.css       Badges, botones, flyer, tarjetas, ficha, hero, personas, afirmaciones…
│   ├── js/app.js                Menú móvil y estado scrolled de la cabecera. Nada más
│   ├── js/admin.js              Selector de imagen y repetidor del programa (vanilla)
│   ├── css/admin.css            Formulario partido del panel
│   └── img/logo-asp.png         Logo actual (PNG 2018); se reemplaza por el vectorial
│
├── front-page.php               Inicio
├── archive-evento.php           /eventos/ — próximos arriba, archivo por año abajo (#archivo-2026)
├── single-evento.php            Ficha de evento + JSON-LD schema.org/Event
├── archive-iniciativa.php       /iniciativas/
├── single-iniciativa.php        Ficha de iniciativa con próximos y ediciones anteriores
├── single-persona.php           Ficha de persona: bio, predicaciones, eventos, artículos
├── archive-predicacion.php      /recursos/predicaciones/ — todas por año
├── single-predicacion.php       Reproductor de video o audio, o texto; del mismo evento al lado
├── home.php                     /recursos/ — blog (artículos, series, categorías) + archivo de conferencias por año
├── single.php                   Artículo con navegación de serie
├── archive.php                  Categorías, series, etiquetas y autores
├── search.php · searchform.php
├── page.php · 404.php · index.php
```

## Decisiones que cambiaron respecto del plan original

- **No hay página `/eventos/pasados/`.** El canvas pone el archivo por año en
  `/eventos/`, debajo de los próximos, con anclas `#archivo-AAAA`. Eso además
  elimina el choque entre esa página y el rewrite `eventos` del CPT.
- **Afirmaciones y Negaciones viven dentro de Nosotros**, con índice de
  numerales, anclas `#articulo-i` … `#articulo-xix` y estilos de impresión.
  El texto está en `inc/afirmaciones-datos.php`, verbatim.
- **Contenido de muestra.** `tools/seed-demo.php` carga eventos, personas,
  artículos y flyers generados marcados con `_asp_demo`; `tools/limpiar-demo.php`
  los borra enteros. Correr la limpieza antes de migrar a producción.
- **La home y los tokens se rediseñaron después del canvas.** Tipografía
  Newsreader (display, cuerpo, numerales) y Archivo (rótulos); hueso, casi
  negro y tinta azul `#1E3350` como único acento; radios a 2 px. Las demás
  plantillas heredan la paleta sin cambios estructurales.
- **Fotografías y galería se cargan desde el Customizer** (Apariencia →
  Personalizar → Ante Su Palabra). Sin foto, el hero es un bloque oscuro con
  el velo; nunca se muestra un placeholder.
- **Adelantado de fase 2 por decisión del ministerio (11-9-2026):** el archivo
  de predicaciones en video, audio o texto (CPT `predicacion`). Sigue afuera:
  materiales PDF y galería por evento.

## Qué renderiza cada vista

**`front-page.php`** — Dirección editorial (11-9-2026, posterior al canvas).
El evento manda: `parts/evento/hero.php` pone el próximo evento sobre la
fotografía a sangre con velo en degradado solo abajo, fecha como numeral
grande en serif, título, lugar y botón. Sin evento próximo, el mismo hero
muestra el versículo de Isaías 66:2 (o el eslogan propio). Después: "Quiénes
somos" con la misión en serif grande; iniciativas como lista con numerales
romanos y filetes; una fotografía a sangre y una tira de tres; eventos como
filas; consejo pastoral en seis retratos 4:5 en blanco y negro; recursos
cuando haya. Pie oscuro con la misión completa, navegación, redes y email.
Ninguna tarjeta con borde ni esquina redondeada. El mismo criterio se aplicó
al resto: badges como rótulos con filete corto, tarjetas y cajas convertidas
en filetes, retratos 4:5 en blanco y negro en Nosotros, filtros de Recursos
como enlaces subrayados, Recursos con rótulo a la izquierda y cuerpo a la
derecha, y heros de página con velo en degradado solo abajo.

**`archive-evento.php`** — Próximos ascendentes como tarjetas anchas, sin
filtros. Archivo agrupado por año descendente. Una sección sin contenido no se
imprime; si no hay nada, una sola línea.

**`single-evento.php`** — Sin flyer, la fecha grande entre filetes ocupa su
lugar y la ficha es una columna de texto. Con flyer, banda 1:1 en móvil y 4:3
en escritorio, y grilla 7/4 con columna lateral fija (precio, botón, fechas,
sede). Todo bloque vacío desaparece con su filete y su rótulo.

**`templates/page-nosotros.php`** — Una columna de 720 px. Consejo pastoral
con `asp_personas_por_rol('consejo')`, ordenado por "Orden".

**`home.php`** — Recursos es el blog y el archivo de conferencias en una sola
página. Cabecera con el lema (contenido editable de la página Recursos) y tres
saltos: Artículos, Predicaciones y Conferencias. Artículos: el último en grande, filtros por
categoría y serie como enlaces, listado con paginación real, series al final
de la primera página. Predicaciones: las seis más recientes con enlace al
archivo completo. Conferencias y talleres realizados: `asp_eventos_pasados_por_anio()`,
filas por año que llevan a la ficha del evento.

**`single.php`** — Chip de serie con posición "02 de 03" o categoría, autor
(la ficha de persona si está vinculada), fecha, cuerpo y lista de la serie.

## Encabezado y navegación

Menú único: Eventos · Iniciativas · Recursos · Nosotros · Contacto. Si no hay
menú asignado, el tema arma ese mismo con lo que exista. El encabezado es el
mismo en todas las vistas, artículos incluidos.
