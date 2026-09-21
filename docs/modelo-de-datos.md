# Modelo de datos

Sin plugins. Los campos son **meta boxes propios** definidos en `inc/`, guardados
en post meta con `register_post_meta` y sanitización explícita. Los nombres de
campo son la clave de post meta; no hay una segunda capa de claves.

## Tipos de contenido

| CPT | Slug | Rewrite | Jerárquico | Notas |
|---|---|---|---|---|
| Evento | `evento` | `eventos` | no | El núcleo del sitio |
| Iniciativa | `iniciativa` | `iniciativas` | no | Las cuatro del ministerio |
| Persona | `persona` | `personas` | no | Consejo, oradores y autores en una sola ficha |
| Aliado | `aliado` | — | no | `public => false`, `show_ui => true`, solo para relacionar |
| Predicación | `predicacion` | `recursos/predicaciones` | no | Sesiones de las conferencias: video, audio o texto. Adelantada de fase 2 el 11-9-2026 |
| Artículo | `post` nativo | `recursos` | no | El blog migrado |

Los cuatro CPT usan el **editor clásico**, forzado con
`use_block_editor_for_post_type`. Es la única forma de que el bloque obligatorio
quede arriba, junto al título, y el país dentro de ese bloque y no en la barra
lateral.

**Persona unifica tres listas que hoy se superponen.** Ricardo Daglio y Joselo
Mercado están a la vez en el consejo pastoral y como autores del blog. Un campo
`persona_roles` (consejo / orador / autor) decide dónde aparece.

### Taxonomías

- `pais` — términos `argentina` y `estados-unidos`. Aplica a `evento`. Se registra
  con `meta_box_cb => false`: el radio se dibuja dentro del meta box obligatorio
  y se guarda con `wp_set_object_terms`.
- `category` y `post_tag` nativas para artículos: Pastorado, Devocional, Vida Cristiana.

---

## Convenciones de los meta boxes

- Id del meta box: `asp_<cpt>_<bloque>`. Nonce: `asp_<cpt>_nonce`, acción
  `asp_guardar_<cpt>`.
- Cada campo se registra con `register_post_meta( $cpt, $nombre, [ 'single',
  'type', 'sanitize_callback', 'show_in_rest' => false ] )`.
- El guardado corre en `save_post_<cpt>`: verifica nonce, capacidad y que no sea
  autosave ni revisión; sanitiza campo por campo; un campo vacío se borra con
  `delete_post_meta`, nunca se guarda `''`.
- Los helpers de render viven en `inc/meta-helpers.php`: `asp_campo_texto()`,
  `asp_campo_fecha()`, `asp_campo_select()`, `asp_campo_url()`,
  `asp_campo_imagen()`, `asp_campo_checkboxes_posts()`, `asp_campo_repetidor()`.
  Cada uno imprime etiqueta, input y texto de ayuda escapados.
- Etiquetas y ayudas en castellano llano, con `__()` y text domain `asp`.

### Tipos de almacenamiento

| Tipo lógico | Cómo se guarda | Input |
|---|---|---|
| fecha | cadena `Ymd`, ej. `20260925` | `<input type="date">` nativo; se convierte al guardar y al mostrar |
| texto / url | cadena | `sanitize_text_field` / `esc_url_raw` |
| select | una de las claves permitidas, validada contra la lista | `<select>` |
| booleano | `'1'` o meta borrada | `<input type="checkbox">` |
| imagen | id de adjunto (int) | selector de medios del core (`wp.media`) |
| relación única | id de post (int) | `<select>` |
| relación múltiple | **varias filas de meta con la misma clave**, una por id | lista de checkboxes |
| repetidor | un array serializado de filas | filas con `<template>` en `assets/js/admin.js` |
| wysiwyg | HTML filtrado con `wp_kses_post` | `wp_editor()` del core |

La relación múltiple se guarda como filas separadas y no como array serializado
para que las consultas inversas (`persona → eventos donde fue orador`,
`iniciativa → sus eventos`) sean un `meta_query` exacto y no un `LIKE`.

---

## Campos — Evento

### Bloque obligatorio: meta box `asp_evento_obligatorio` (arriba, siempre abierto)

| Nombre | Tipo | Obligatorio | Etiqueta en el panel |
|---|---|---|---|
| *(título nativo)* | — | sí | Nombre del evento |
| `evento_fecha_inicio` | fecha | sí | Primer día del evento |
| `evento_fecha_fin` | fecha | sí | Último día del evento |
| `evento_ciudad` | texto | sí | Ciudad |
| *(taxonomía `pais`)* | radio | sí | País |
| `evento_estado_inscripcion` | select | sí | ¿Se puede inscribir? |
| `evento_url_registro` | url | condicional | Link de inscripción |

`evento_estado_inscripcion` — opciones y default:

```
reserva  => Reservá la fecha (todavía no hay inscripción)   [default]
abierta  => Inscripción abierta
cerrada  => Inscripción cerrada
agotado  => Sin cupo
```

`evento_url_registro` es obligatorio **solo** cuando el estado es `abierta`.

### Bloque opcional: meta box `asp_evento_opcional` (plegado por defecto)

| Nombre | Tipo | Notas |
|---|---|---|
| `evento_iniciativa` | relación única → `iniciativa` | Select con "Ninguna" |
| `evento_sede_nombre` | texto | Ej. Iglesia Gracia Soberana |
| `evento_sede_direccion` | textarea | Ej. 8300 Helgerman Ct, Gaithersburg MD |
| `evento_oradores` | relación múltiple → `persona` | Solo personas con rol `orador` |
| `evento_flyer` | imagen | Se muestra contenido, nunca recortado |
| `evento_descripcion` | wysiwyg | Cuerpo de la ficha |
| `evento_programa` | repetidor | Filas: `dia` (fecha), `hora` (texto), `titulo` (texto), `orador` (texto) |
| `evento_aliados` | relación múltiple → `aliado` | Simeon Trust, Cross Connections, TeoLibros |
| `evento_precio` | texto | Texto libre, no número: los precios cambian por país |
| `evento_destacado` | booleano | Marca el evento del hero |

El repetidor lleva `dia` porque las conferencias duran dos días y un programa sin
día no se puede leer. `orador` es texto libre y no relación: en el programa
aparecen nombres que no siempre tienen ficha.

El meta box opcional se pliega con la clase `closed` vía el filtro
`postbox_classes_evento_asp_evento_opcional`. WordPress recuerda por usuario si
lo abrió, lo cual está bien.

### Campos de archivo (se completan después del evento — fase 2)

`evento_galeria` (array de ids de adjunto), `evento_videos` (array de URLs).
Registrarlos ahora para que el modelo no cambie después; no se renderizan ni se
muestran en el panel en fase 1.

---

## Validación que bloquea la publicación

`conditional_logic` y el atributo `required` del navegador no alcanzan: el
segundo se salta con "Guardar borrador" y ninguno protege contra un post que se
publica desde otro lado. La validación va en el servidor, en
`inc/evento-validacion.php`:

1. Filtro `wp_insert_post_data`, solo para `post_type == evento`, solo cuando
   `post_status` que entra es `publish` o `future`, y nunca en autosave.
2. Se leen los valores del `$_POST` actual (verificando el nonce del meta box),
   no del meta guardado: el meta todavía no se escribió en ese punto.
3. Faltantes: título, fecha inicio, fecha fin, ciudad, país; y URL de registro si
   el estado es `abierta`. También fecha fin anterior a fecha inicio.
4. Si falta algo, `post_status` se fuerza a `draft`, la lista de errores se guarda
   en un transient por usuario (`asp_errores_evento_<user_id>`, 60 segundos) y
   `redirect_post_location` agrega `asp_error=1`.
5. `admin_notices` lee el transient, muestra "No se publicó. Falta: …" con cada
   campo por su etiqueta, y lo borra.

El meta se guarda igual en `save_post_evento`, aunque el post quede en borrador:
lo cargado no se pierde.

---

## Lógica de estado

Cinco estados visibles. Los cuatro primeros los elige quien carga; `realizado` y
`en_curso` son automáticos y ganan siempre.

```php
/**
 * Estado efectivo del evento.
 *
 * @return string reserva|abierta|cerrada|agotado|en_curso|realizado
 */
function asp_evento_estado( int $post_id ): string {
    $hoy    = current_time( 'Ymd' );
    $inicio = (string) get_post_meta( $post_id, 'evento_fecha_inicio', true );
    $fin    = (string) get_post_meta( $post_id, 'evento_fecha_fin', true );

    if ( '' === $inicio || '' === $fin ) {
        return 'reserva';
    }
    if ( $fin < $hoy ) {
        return 'realizado';
    }
    if ( $inicio <= $hoy && $hoy <= $fin ) {
        return 'en_curso';
    }
    $estado = (string) get_post_meta( $post_id, 'evento_estado_inscripcion', true );
    return in_array( $estado, [ 'reserva', 'abierta', 'cerrada', 'agotado' ], true ) ? $estado : 'reserva';
}
```

Reglas de render derivadas:

- El botón de inscripción se muestra **solo** en estado `abierta`.
- En `cerrada` y `agotado` la ficha sigue visible con el botón apagado y el motivo.
- En `reserva` no hay botón: se muestra la fecha y "Reservá la fecha".
- En `en_curso` se muestra "Hoy" o "En curso" y ningún botón.
- En `realizado` la ficha queda publicada y se muda al archivo.

Badges: `en_curso` usa los tokens de `abierta`; `agotado` usa los de `cerrada`.
Si el diseño quiere distinguirlos, se agregan tokens propios en `tokens.css`,
nunca valores sueltos en `components.css`.

El horario del sitio es el de WordPress (`current_time`), no el del servidor.
El ministerio opera en dos husos; se usa la fecha local del sitio como referencia
única y se documenta en la ayuda del panel.

---

## Consultas

Las fechas se guardan como `Ymd` (`20260925`), así que la comparación
lexicográfica de cadenas funciona y `type => 'NUMERIC'` también. Usar `NUMERIC`
para que `orderby` ordene bien.

```php
function asp_eventos_proximos( int $cantidad = -1 ): WP_Query {
    return new WP_Query( [
        'post_type'      => 'evento',
        'posts_per_page' => $cantidad,
        'meta_key'       => 'evento_fecha_inicio',
        'orderby'        => 'meta_value_num',
        'order'          => 'ASC',
        'meta_query'     => [
            [
                'key'     => 'evento_fecha_fin',
                'value'   => current_time( 'Ymd' ),
                'compare' => '>=',
                'type'    => 'NUMERIC',
            ],
        ],
    ] );
}
```

`asp_eventos_pasados( ?int $anio = null )` — igual pero con `<` y orden `DESC`,
agrupando por año a partir de `evento_fecha_inicio`.

`asp_evento_destacado()` — el primero de `asp_eventos_proximos()` que tenga
`evento_destacado`; si ninguno lo tiene, el próximo por fecha. Nunca devolver
nada si no hay eventos próximos: la home tiene que resolver ese caso sin romperse.

Consultas inversas, gracias a las relaciones guardadas como filas separadas:

```php
// Eventos de una iniciativa.
'meta_query' => [ [ 'key' => 'evento_iniciativa', 'value' => $iniciativa_id ] ]

// Eventos donde una persona fue oradora.
'meta_query' => [ [ 'key' => 'evento_oradores', 'value' => $persona_id ] ]
```

---

## Campos de los otros CPT

**Iniciativa** (meta box `asp_iniciativa_datos`): `iniciativa_bajada` (texto),
`iniciativa_publico` (texto), `iniciativa_descripcion` (wysiwyg),
`iniciativa_historia` (wysiwyg), `iniciativa_imagen` (imagen). Los eventos se
traen por consulta inversa sobre `evento_iniciativa`, no con un campo espejo.

**Persona** (meta box `asp_persona_datos`): `persona_iglesia` (texto),
`persona_ciudad` (texto), `persona_bio` (textarea), `persona_foto` (imagen),
`persona_roles` (relación múltiple de valores fijos `consejo`, `orador`, `autor`,
una fila por rol), `persona_usuario` (id de usuario, select opcional — vincula la
ficha con el autor de WordPress para los artículos migrados). El orden del
consejo pastoral en Nosotros sale de `menu_order`, editable con "Atributos".

**Aliado** (meta box `asp_aliado_datos`): `aliado_logo` (imagen), `aliado_url` (url).

**Predicación** (meta box `asp_predicacion_datos`, con editor clásico para el
texto): `predicacion_tipo` (select `video|audio|texto`), `predicacion_video_url`
(url de YouTube, se incrusta con el oEmbed del core), `predicacion_audio_url`
(url de un mp3 subido a Medios, se reproduce con `<audio>`), `predicacion_evento`
(relación única → evento), `predicacion_orador` (relación única → persona),
`predicacion_pasaje` (texto), `predicacion_duracion` (texto), `predicacion_fecha`
(fecha, opcional: si falta se usa la del evento). El formato efectivo lo
calcula `asp_predicacion_tipo()`: sin link, cae a texto. Consultas en
`inc/predicaciones.php`: todas por año, por evento, por persona.

---

## Migración del blog

El blog corre en una instalación aparte con REST API deshabilitada y `/page/2/`
devolviendo 404, así que la paginación por AJAX es la única forma de ver los
artículos viejos. Son unos 20 a 25.

Camino recomendado:

1. Export WXR desde el blog (Herramientas → Exportar → Entradas).
2. Import en el sitio nuevo con el importador de WordPress, trayendo adjuntos.
   **Es la única excepción a "sin plugins"**: se instala, se corre una vez y se
   borra el mismo día.
3. Mapear autores a usuarios reales y vincularlos a fichas `persona`.
4. Permalink de entradas bajo `/recursos/`.
5. Mapa de 301 desde `blog.antesupalabra.com/<slug>/` a
   `antesupalabra.com/recursos/<slug>/`, más las tres categorías. Pendiente de
   decidir en hosting: el subdominio tiene que seguir apuntando a algo que sirva
   esos 301 después de apagar el blog.
6. Sitemap XML: el del core de WordPress, activo desde la versión 5.5.

Guardar el mapa de redirects como CSV versionado en `docs/redirects.csv`, no
escrito a mano en el `.htaccess`.
