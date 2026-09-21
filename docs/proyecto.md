# Ante Su Palabra — Rediseño del sitio

**Documento maestro del proyecto.**
Relevamiento, decisiones tomadas, alcance y modelo de datos.

- Sitios relevados: `antesupalabra.com` · `blog.antesupalabra.com`
- Fecha de relevamiento: 9 de septiembre de 2026
- Estado del documento: alcance de fase 1 cerrado — incluye tema y sistema de diseño nuevos. Pendiente de validación con el ministerio.

---

## 0. Acción inmediata (no espera al rediseño)

Hay un evento el **25-26 de septiembre de 2026 en Washington DC** cuyo botón de registro en la home apunta a un Eventbrite de **2024**. Falta menos de tres semanas.

Corregir ese enlace esta semana, antes de cualquier trabajo de rediseño.

---

## 1. El encargo

Nueva página web para el ministerio Ante Su Palabra.

- Sitio mayormente estático, pero con **eventos que deben ir actualizándose**.
- Requiere **blog**.
- Los **eventos son el principal brazo del ministerio** y tienen que estar destacados.
- Hay además **otras iniciativas** que deben tener lugar propio.

### Qué es el ministerio

> Ante Su Palabra es una comunión de pastores e iglesias locales, que cree en la autoridad y la suficiencia de las Escrituras, cuyo propósito es contribuir a la salud de las iglesias locales.

Versículo de identidad, en el hero actual:

> «Pero a este miraré: al que es humilde y contrito de espíritu, y que tiembla ante Mi palabra» — Isaías 66:2

---

## 2. Situación actual

### 2.1 Arquitectura técnica

Son **dos instalaciones de WordPress separadas y sin relación entre sí**.

| | antesupalabra.com | blog.antesupalabra.com |
|---|---|---|
| Rol | Sitio institucional | Blog / contenidos |
| CMS | WordPress 7.0.4 | WordPress (instalación aparte) |
| Tema | Kava + Kava Child (Crocoblock) | Gridlove (tema de revista, ThemeForest) |
| Constructor | Elementor + Elementor Pro | Nativo del tema (widgets Meks) |
| Plugins clave | JetEngine, JetElements, JetTabs, JetThemeCore, JetBlocks, ElementsKit, Event Tickets, Contact Form 7 | Contact Form 7, familia Meks |
| Seguimiento | Google Tag Manager, Facebook Pixel | No detectado |
| REST API | Deshabilitada / bloqueada | Deshabilitada / bloqueada |
| Sitemap XML | No encontrado | 404 en `sitemap_index.xml` |

**Dato relevante:** el plugin *Event Tickets* (The Events Calendar) ya está instalado en el sitio institucional, y JetEngine permite crear tipos de contenido personalizados. La capacidad de gestionar eventos ya está pagada e instalada — nunca se usó. Los eventos se maquetan a mano en Elementor.

### 2.2 Mapa del sitio actual

| URL | Título | Estado |
|---|---|---|
| `antesupalabra.com/` | Inicio | Hero, misión, 2 tarjetas de evento, 4 iniciativas |
| `antesupalabra.com/nosotros/` | Nosotros | Declaración, consejo pastoral, 19 artículos |
| `antesupalabra.com/eventos/` | Eventos | 1 evento vencido, sin archivo |
| `blog.antesupalabra.com/` | Blog | Home del blog (subdominio aparte) |
| `blog.antesupalabra.com/inicio/` | Inicio (blog) | Duplicado de la home del blog |
| `.../category/pastorado/` | Pastorado | Categoría |
| `.../category/devocional/` | Devocional | Categoría |
| `.../category/vida-cristiana/` | Vida Cristiana | Categoría |

No existe: página de contacto, donaciones, recursos, iniciativas individuales, ni archivo de eventos.

### 2.3 Navegación

- **Institucional:** Inicio · Nosotros · Blog · Eventos
- **Blog:** Inicio · Nosotros (va al sitio institucional) · buscador

El blog **no tiene enlace a Eventos**. Quien llega a un artículo desde redes o búsqueda queda en un callejón sin salida respecto del contenido más importante del ministerio.

### 2.4 Estado real de los eventos

| Evento / referencia | Fecha mostrada | Enlace de destino | Estado |
|---|---|---|---|
| Home — Washington DC (*El cuidado de las almas*, Iglesia Gracia Soberana, Gaithersburg MD) | 25-26 SEP 2026 | Eventbrite "Conferencia USA ASP **2024**" | Fecha vigente, enlace roto |
| Home — Buenos Aires (*Un libro en un día*, c/ Nigel Styles, Cross Connections, TeoLibros) | 28 ABR 2026 | entrada27.com.ar/unlibroenundia | Fecha ya pasada |
| Página Eventos — Taller de Predicación | Feb. 26-27 2026 | Eventbrite "Simeon Trust ASP **2025**" | Vencido |
| Referencia Simeon Trust | 2026 | simeontrust.org/es/workshop/denton-estados-unidos-2026/ | Vigente |
| Referencia histórica | 2025 | Eventbrite "Arrepentíos y Creed — ASP 2025" | Pasado |
| Referencia histórica | s/f | cambiosprofundos.eventbrite.com.ar | Pasado |

**Home y `/eventos/` muestran eventos distintos entre sí.** No hay una única fuente de verdad.

---

## 3. Hallazgos

### Críticos

1. **Los eventos no son contenido.** Están maquetados a mano en Elementor. No hay tipo de contenido, ni campos, ni estados, ni archivo. Todo depende de edición manual y ya falló.
2. **Enlaces de registro rotos o vencidos.** Al menos tres CTA apuntan a ediciones de 2024 y 2025.
3. **Inconsistencia entre Home y Eventos.** Muestran cosas distintas.
4. **Dos sitios, dos marcas.** El blog parece de otra organización.
5. **No hay forma de contactar al ministerio.** Ni email, ni formulario, ni teléfono.

### Importantes

- Sin archivo de eventos pasados: se pierde toda la historia del ministerio.
- Las iniciativas no tienen página propia ni enlace: cuatro párrafos sin salida.
- Textos desactualizados ("Este 2018, iniciamos…") y un error de tipeo en la home ("Dios nos ha **permito** servir").
- El blog pagina por AJAX y `/page/2/` devuelve 404 → artículos antiguos casi invisibles para buscadores.
- No hay sitemap.xml en ninguno de los dos sitios.
- Fotos del consejo pastoral con formatos, recortes y calidades distintas.

### Menores

- Logotipo en PNG de 2018, sin versión vectorial ni variante claro/oscuro.
- Tres familias tipográficas conviviendo sin criterio (Montserrat, Roboto, Scope One).
- Página `/inicio/` duplicada en el blog.
- Plugins instalados sin usar (Event Tickets, ElementsKit).
- Contador de vistas visible en cada artículo (45 a 122 vistas): comunica poco alcance más que credibilidad.

---

## 4. Decisiones tomadas

| Tema | Decisión | Razón |
|---|---|---|
| **Stack** | WordPress clásico, admin nativo, plantillas del lado del servidor. **No headless.** | La carga la hace el ministerio. Con headless, cualquier problema del front lo tiene que resolver el desarrollador y ellos quedan sin poder hacer nada. |
| **Quién carga** | Alguien del **área de redes** del ministerio. | Ya trabaja con flyers, fechas y links de inscripción. Piensa en el mismo formato que necesita el sitio. |
| **Volumen** | **4 a 6 eventos por año.** | El desafío no es el volumen sino la frecuencia: quien carga entra cada dos o tres meses y no recuerda nada de la vez anterior. |
| **Publicación progresiva** | Sí. A veces se tiene la fecha pero no los detalles. | Define los estados del evento y la regla de ficha vacía. |
| **Unificación** | Un solo sitio, un solo WordPress, una sola marca. | Si se posterga, se arrastran dos temas y dos menús durante todo el proyecto. |
| **Tema** | **Tema clásico a medida.** PHP con plantillas propias y meta boxes propios para los campos. Sin plugins ni constructor visual. | Máximo control sobre el admin, que es donde está el riesgo real del proyecto. Nada de Elementor: es lo que produjo el problema actual. |
| **Identidad** | **Se conserva únicamente el logo.** Tipografía, paleta, layout, componentes y sistema de diseño se hacen de cero. | El logo funciona y no requiere ronda de aprobación de marca. Todo lo demás hoy no es un sistema, son tres tipografías conviviendo por acumulación. |

---

## 5. Alcance de fase 1

Criterio: **todo lo que entra tiene que quedar funcionando sin que nadie lo toque después.**

1. **Un solo sitio.** El blog se muda al dominio principal. No es negociable ni divisible.
2. **Eventos como contenido estructurado.** Tipo de contenido con campos, estado automático por fecha, listado de próximos, ficha individual, evento destacado en el home. Una sola fuente de datos alimentando todas las vistas.
3. **Archivo de eventos pasados, versión mínima.** Al pasar la fecha, la ficha queda publicada con flyer, fecha, sede y oradores, navegable por año. Sin galería ni video todavía. Se llena con lo que exista; si no hay material viejo, arranca con 2026 en adelante y crece solo.
4. **Iniciativas.** Las cuatro con página propia sobre una misma plantilla: qué es, para quién, y los eventos vinculados traídos automáticamente. Textos actualizados.
5. **Nosotros.** Declaración, consejo pastoral con fotos normalizadas, y *Afirmaciones y Negaciones* fuera del acordeón, en página propia con índice y anclas por artículo. Texto migrado verbatim.
6. **Blog migrado.** ~25 artículos con categorías y autores, paginación real (no AJAX), 301 desde el subdominio.
7. **Contacto.** Formulario, email visible, redes.
8. **Tipo "Persona" compartido.** Consejo pastoral, oradores y autores del blog son hoy tres listas que se superponen (Ricardo Daglio y Joselo Mercado están en dos). Unificarlo evita mantener tres versiones de la misma ficha.
9. **Base técnica.** Mobile-first real, `schema.org/Event`, sitemap, mapeo completo de redirects, analytics.
10. **Tema y sistema de diseño nuevos.** Ver sección 11. Logo vectorizado con variantes claro/oscuro (misma forma), tipografía institucional nueva, paleta nueva, escala tipográfica y de espaciado, biblioteca de componentes, y tratamiento uniforme de las fotos del consejo.
11. **Ayuda incorporada.** Página de ayuda dentro del propio panel (no un PDF que se pierde) y video de tres minutos mostrando la carga de un evento.

### Ajustes respecto de la propuesta inicial

- **Se saca el filtro por país e iniciativa.** Con 4-6 eventos anuales el listado entra en una pantalla; un filtro sobre seis ítems es ruido. La bandera queda como distintivo visual en la tarjeta. Si algún día hay veinte eventos al año, se agrega.
- **Sube de prioridad el archivo por año.** Con eventos recurrentes se ordena solo y a los tres años muestra la trayectoria del ministerio.
- **Se amplía el trabajo de identidad.** La propuesta original decía "normalizar lo que hay". La decisión final es tema y sistema de diseño nuevos, conservando solo el logo. Suma horas de diseño, pero evita construir un sitio nuevo sobre decisiones visuales heredadas que nadie tomó.

---

## 6. Fuera de fase 1

Ninguna está descartada. Van anotadas como fase 2, con el sitio construido de modo que entren sin rehacer nada.

- Sitio bilingüe (español / inglés)
- Donaciones
- Registro e inscripción propios — sigue todo en Eventbrite y Entrada27
- Biblioteca de recursos con el canal de YouTube
- Listado de iglesias en comunión y proceso de adhesión
- Newsletter y automatizaciones de email
- Galería de fotos y video por evento
- Rebranding: logo y símbolo nuevos *(el resto de la identidad sí entra en fase 1 — ver sección 11)*

---

## 7. Arquitectura de información

| Sección | URL | Contenido |
|---|---|---|
| Inicio | `/` | Hero + próximo evento destacado + eventos próximos + iniciativas + últimos artículos + CTA de contacto |
| Eventos | `/eventos/` | Listado de próximos |
| Ficha de evento | `/eventos/{slug}/` | Flyer, fecha, sede, oradores, programa, precio, registro |
| Eventos pasados | `/eventos/pasados/` | Archivo por año |
| Iniciativas | `/iniciativas/` | Las cuatro como tarjetas |
| Ficha de iniciativa | `/iniciativas/{slug}/` | Qué es, para quién, historia, ediciones anteriores, próxima fecha |
| Nosotros | `/nosotros/` | Quiénes somos + consejo pastoral + enlace a doctrina |
| Afirmaciones y Negaciones | `/afirmaciones-y-negaciones/` | Los 19 artículos con índice, anclas y PDF descargable |
| Recursos / Blog | `/recursos/` | Artículos con categorías y autores |
| Artículo | `/recursos/{slug}/` | Artículo con autor, categoría y relacionados |
| Autor / Persona | `/personas/{slug}/` | Bio, iglesia, artículos y participación en eventos |
| Contacto | `/contacto/` | Formulario, email, redes |

---

## 8. Modelo de datos

### 8.1 Evento — el núcleo del sitio

| Campo | Tipo | Obligatorio | Uso |
|---|---|---|---|
| Título | Texto | **Sí** | Ej. "El cuidado de las almas" |
| Fecha inicio | Fecha | **Sí** | Define el estado automático |
| Fecha fin | Fecha | **Sí** | Dispara el paso a *Realizado* |
| Ciudad y país | Texto + taxonomía | **Sí** | Bandera en la tarjeta |
| URL de registro | Enlace | **Sí** (salvo estado *Reservá la fecha*) | Eventbrite, Entrada27 u otro |
| Estado de inscripción | Selección | Sí (default: *Reservá la fecha*) | Ver 8.2 |
| Iniciativa | Relación | No | Conferencia ASP / ASP EE.UU. / Cánticos / Taller de Predicación |
| Sede | Texto + dirección | No | Ej. Iglesia Gracia Soberana, 8300 Helgerman Ct, Gaithersburg MD |
| Oradores | Relación → Persona | No | |
| Flyer | Imagen | No | Arte oficial |
| Descripción y programa | Texto enriquecido | No | Cuerpo de la ficha |
| Aliados | Relación → Aliado | No | Simeon Trust, Cross Connections, TeoLibros |
| Precio | Texto | No | |
| Destacado | Sí / No | No | Marca el evento del hero |
| Galería y videos | Media | No | Se completa después del evento (fase 2 en la práctica) |

**Regla clave:** ninguna vista del sitio contiene un evento escrito a mano. Home, `/eventos/`, el archivo y los widgets consultan el mismo modelo.

### 8.2 Estados del evento

Los tres primeros los elige quien carga. El último es automático.

| Estado | Qué significa | Comportamiento |
|---|---|---|
| **Reservá la fecha** | Hay fecha y ciudad, no hay inscripción todavía | La ficha se publica igual. Sin botón de registro. |
| **Inscripción abierta** | El link está activo | Único estado con botón de registro visible |
| **Inscripción cerrada** | Cerró el registro | Ficha visible, botón apagado con el motivo |
| **Agotado** | Sin cupo | Ídem anterior |
| **Realizado** | *(automático)* La fecha de fin pasó | Sale de "próximos", entra al archivo |

Efecto secundario deseado: el home tiene contenido todo el año. Entre conferencias muestra el próximo evento en *Reservá la fecha* con la cuenta regresiva, en vez de quedar vacío.

### 8.3 Regla de ficha vacía

**La ficha tiene que verse bien con tres datos cargados.** Es lo que define el diseño.

- Cada bloque —oradores, programa, sede, aliados, precio— **aparece solo si tiene contenido y desaparece si no**.
- Nada de "Oradores: por confirmar" ni secciones vacías con placeholder.
- El evento se publica el día que se define la fecha y va creciendo solo.

### 8.4 Otros tipos de contenido

- **Iniciativa** — nombre, descripción, público, historia, eventos vinculados.
- **Persona** — nombre, foto, iglesia, ciudad, bio. Unifica consejo pastoral, oradores y autores del blog.
- **Artículo** — el blog migrado, con categorías Pastorado, Devocional y Vida Cristiana.
- **Aliado** — Simeon Trust, Cross Connections, TeoLibros, iglesias anfitrionas.

---

## 9. Experiencia de carga (admin)

Todo el diseño del panel asume **un usuario que siempre es primerizo**: entra cada dos o tres meses y no recuerda nada.

### El flujo principal es "duplicar", no "crear"

Los eventos se repiten: la conferencia anual, el taller con Simeon Trust, Cánticos. El 90% de las veces lo que se carga es la edición del año siguiente de algo que ya existe.

Un botón **"Duplicar edición anterior"** que clona la ficha entera y solo pide cambiar fecha, sede y link de inscripción convierte una carga de veinte minutos en una de tres. **Eso es lo que hay que diseñar bien, más que el formulario en blanco.**

### Formulario partido

- **Arriba, siempre visible:** los cinco campos obligatorios.
- **Abajo, plegado:** oradores, programa, sede, aliados, precio, flyer.

Que puedan publicar el día que se define la fecha y completar el resto después. Hoy el patrón es al revés: como cargar es caro, no se carga nada hasta último momento, y por eso el sitio queda desactualizado.

### Otras reglas

- Etiquetas en castellano llano, con textos de ayuda. Nada de "meta", "slug" ni "taxonomía".
- Rol de usuario propio, **"Editor de eventos"**, con el panel podado a lo indispensable: Eventos, Artículos, nada más. Quince menús a alguien que entra dos veces al año paraliza.

### Riesgo específico de que cargue alguien de redes

Que suba el flyer y deje los campos vacíos, porque "toda la info ya está en el flyer". Es lo natural para quien viene de Instagram, y es lo que rompe el sitio: el texto dentro de una imagen no lo lee Google, no lo lee un lector de pantalla, y no sirve para armar el listado ni el archivo.

**Por eso los cinco campos mínimos bloquean la publicación, no son una sugerencia.**

### Detalle del flyer

El flyer se diseña para Instagram (cuadrado o 4:5) y el sitio lo necesita apaisado. Hoy el sitio recorta el cuadrado a 1024×577 y por eso varias tarjetas quedan con el título cortado.

Dos opciones:
1. Pedir a quien diseña una exportación apaisada de cada flyer.
2. **Diseñar la tarjeta con el flyer contenido, sin recorte.** ← recomendada, porque no depende de que nadie se acuerde de exportar dos versiones.

---

## 10. Contenido a migrar

### 10.1 Home

**Hero:** Isaías 66:2 (ver sección 1). Imagen de fondo: manos sosteniendo una Biblia abierta, tono oscuro.

**Misión:** ver sección 1. CTA: "SOBRE NOSOTROS".

**¿Cómo buscamos ayudar?**
> Buscamos ayudar a las iglesias locales a través de diferentes iniciativas como conferencias, eventos y capacitaciones, Dios nos ha permito servir a nuestros hermanos en Argentina, y ahora también en Estados Unidos.

⚠️ "permito" → "permitido". Corregir en la migración.

### 10.2 Las cuatro iniciativas

| Iniciativa | Texto actual |
|---|---|
| **Conferencia Ante Su Palabra** | La conferencia Ante Su Palabra nació como una iniciativa para jóvenes pero debido a la demanda fue abierta al público en general. Hoy es nuestra conferencia anual para cientos de personas. |
| **Conferencia Ante Su Palabra en Estados Unidos** | Este 2018, iniciamos nuestra primer conferencia en Estados Unidos para hispanos. Esta conferencia se llevó a cabo en Denton, Texas. |
| **Cánticos Espirituales** | Un evento pensado para líderes de alabanza. Nuestro propósito es alabar a nuestro Dios por su gracia, celebrando juntos el Evangelio, por medio de canciones espirituales y la exposición de las Escrituras. |
| **Taller de Predicación Expositiva** | En Argentina, junto con el ministerio The Charles Simeon Trust buscamos ayudar a cientos de pastores y predicadores a perfeccionarse en la tarea de manejar con precisión la Palabra de Verdad. |

⚠️ Ambos textos de conferencia están desactualizados. El de EE.UU. sigue en 2018; el del Taller dice "En Argentina" cuando la edición vigente es en Denton, Texas. **Los reescribe el ministerio, no nosotros.**

### 10.3 Nosotros — Confiamos en la Palabra de Dios

> Dios ha hablado. Y no hay otra autoridad mayor a la del Creador del Universo. Buscamos someternos a la autoridad de la Palabra de Dios en cada aspecto de nuestras vidas. Reconociendo que el Evangelio es lo que nos motiva a vivir para Dios. Afirmando lo que el apóstol Pablo dijo: «El me amó, y se entregó a sí mismo por mí» (Gal. 2:20).

### 10.4 Consejo Pastoral

> El Consejo Pastoral da dirección y cuidado al ministerio y las iniciativas del mismo.

| Nombre | Iglesia | Ciudad |
|---|---|---|
| Greg Travis | Iglesia Bíblica Reformada | Denton, EE.UU. |
| Dardo Leandi | Iglesia Bautista Misionera de C.A.B.A. | Buenos Aires, Argentina |
| Ernesto Harris | Iglesia Bautista Misionera de Campana | Campana, Argentina |
| Cristian Palomares | Iglesia Bíblica Ciudad de Dios | Rosario, Argentina |
| Ricardo Daglio | Iglesia Bíblica de Villa Regina (UCB) | Villa Regina, Argentina |
| Joselo Mercado | Iglesia Gracia Soberana (pastor principal) | Gaithersburg, Maryland, EE.UU. |

Fotos inconsistentes: tres PNG de 2019 exportados de un artboard, una PNG de 2019, dos JPG de 2021. Rehacer el set con tratamiento uniforme.

### 10.5 Afirmaciones y Negaciones

Introducción de cinco párrafos + 19 artículos (I a XIX) con estructura "Afirmamos que… / Negamos que…".

Traducción con pequeñas adaptaciones del documento original de **T4G (Juntos por el Evangelio)**, redactado por J. Ligon Duncan III, Mark E. Dever, C. J. Mahaney y R. Albert Mohler, Jr.

> **Migrar verbatim.** Es texto doctrinal y confesional del consejo. No se reescribe, no se resume, no se regenera.

### 10.6 Blog

| Aspecto | Situación |
|---|---|
| Lema visible | "Recursos de pastores para pastores." |
| Volumen | ~20-25 artículos |
| Categorías | Pastorado · Devocional · Vida Cristiana |
| Autores | Ricardo Daglio (mayoría) · Joselo Mercado |
| Metadatos por artículo | Categoría, autor con foto, tiempo de lectura, contador de vistas |

Artículos identificados: *El pastor frente a los falsos maestros (1), (2) y (3)* · *Dando Gracias por Pastores Fieles* · *¿Te han dicho que en tu iglesia no hay amor?* · *Capacitados para cualquier situación* · *Apto para enseñar* · *Equilibrando el ministerio y la búsqueda de Dios* · *La clave para un ministerio perdurable* · *Lidiando con la ansiedad en el ministerio*

---

## 11. Identidad visual y sistema de diseño

### 11.1 La decisión

**Tema nuevo, diseñado de cero. Del sitio actual se conserva únicamente el logo.**

No es un maquillaje del tema Kava ni una plantilla comprada adaptada. Se diseña un sistema propio y se construye un tema clásico a medida sobre él.

### 11.2 Qué se conserva y qué se rehace

| | Decisión |
|---|---|
| **Logo** | Se conserva. Misma forma, mismo símbolo. Se vectoriza (hoy es un PNG de 2018) y se generan variantes claro/oscuro y una versión reducida para favicon y redes. |
| **Fotografía documental** | Se conserva como recurso, no como decisión heredada. Es el mayor activo visual del ministerio: auditorios llenos, púlpito, Biblia abierta. El sistema nuevo se diseña para lucirla. |
| **Tipografía** | **Nueva.** Hoy hay tres familias sin criterio (Montserrat, Roboto, Scope One). El sistema define una de títulos y una de texto, con escala completa. |
| **Paleta** | **Nueva.** Se documenta con tokens, incluyendo estados y contrastes verificados. El cian actual no está justificado por nada. |
| **Layout y grilla** | **Nuevos.** |
| **Componentes** | **Nuevos.** Tarjeta de evento, ficha, badges de estado, tarjeta de artículo, ficha de persona, bloques de iniciativa, formularios, pie. |
| **Iconografía** | **Nueva.** Incluye reemplazar las banderas PNG por un tratamiento propio de país. |

### 11.3 Entregables de diseño

1. **Fundaciones:** paleta con tokens, escala tipográfica, escala de espaciado, radios, sombras, breakpoints.
2. **Biblioteca de componentes**, con todos los estados (incluidos los vacíos y los de error).
3. **Pantallas clave diseñadas:** ficha de evento en sus cuatro estados, listado de eventos, archivo, home, iniciativa, artículo, nosotros, contacto.
4. **Logo vectorizado** con sus variantes.
5. **Documento de handoff** que conecta cada token con su variable CSS en el tema.

### 11.4 Criterios de diseño

- **El evento manda.** Cada decisión visual se valida primero contra la ficha de evento y su tarjeta, no contra el home.
- **Tiene que verse bien vacío.** Un evento con tres datos cargados y sin flyer no puede quedar roto. Ver 8.3.
- **El flyer no se recorta.** Ver sección 9.
- **Mobile primero, de verdad.** El tráfico llega de Instagram.
- **Sobrio.** Es una comunión de pastores reformados, no una conferencia de tecnología. El peso visual lo aporta la fotografía y la tipografía, no los efectos.
- **Contraste AA como mínimo** en todo texto sobre fotografía.

### 11.5 Punto de partida — lo que hay hoy

| Elemento | Situación actual |
|---|---|
| Logotipo | Marca denominativa "ANTE SU PALABRA" en dos líneas, negro sólido, con símbolo tipo cruz/libro a la derecha. `Asset-1.png` y `Asset-1@2x.png`, junio 2018. **No hay SVG.** |
| Tipografías | Tres familias simultáneas: Montserrat (títulos), Roboto (texto), Scope One (citas serif). Sin jerarquía definida. |
| Paleta | Blanco `#FFFFFF` · Casi negro `#1C1C1C` · Gris `#4C4C4C` · Cian/turquesa como acento |
| Fotografía | Fotos reales de conferencias (auditorio, púlpito, Biblia abierta) a pantalla completa con overlay oscuro. **Es el mayor activo visual del sitio.** |
| Flyers | JPG/PNG de 1024px, algunos subidos como capturas de WhatsApp. Calidad inconsistente. |
| Iconografía | Banderas de país en PNG para Argentina / Estados Unidos |

### Redes sociales

- Facebook — `facebook.com/ConferenciaAnteSuPalabra/`
- Twitter / X — `twitter.com/antesupalabra`
- Instagram — `instagram.com/antesupalabra/`
- YouTube — `youtube.com/channel/UCzBclEQZPuu7qy7rQRpUdaA`

El canal de YouTube tiene predicaciones y sesiones de conferencias que hoy no aparecen en el sitio salvo un ícono en el pie. Material para fase 2.

---

## 12. Requerimientos técnicos

- Estado de evento automático por fecha, sin intervención manual.
- Cuenta regresiva o fecha destacada del próximo evento en el hero.
- Archivo histórico navegable por año.
- Formulario de contacto.
- Responsive mobile-first (la mayor parte del tráfico llega desde Instagram).
- Datos estructurados `schema.org/Event`.
- Redirecciones 301 de todas las URLs del blog.
- Sitemap XML en ambos idiomas de URL viejas y nuevas.
- Panel de carga simple: publicar un evento en cinco minutos, sin formación técnica.

### Base del tema

- Tema clásico a medida, PHP con plantillas propias. **Sin Elementor ni ningún constructor visual.**
- Meta boxes propios, sin plugin, para los campos de Evento, Iniciativa, Persona y Aliado. Editor clásico forzado en esos CPT.
- CSS propio con custom properties como tokens — los mismos nombres que el sistema de diseño, para que el handoff sea directo.
- JavaScript mínimo y sin framework.
- Rol `editor_eventos` con capacidades acotadas y panel podado.
- Sin plugins. Formulario, sitemap y SEO básico se resuelven en el tema y el core; backups y seguridad en el hosting. Única excepción: el importador WXR para migrar el blog, de un solo uso.

---

## 13. Preguntas abiertas

Para cerrar con el consejo pastoral antes de diseñar.

1. **Nombre y apellido de quien carga, más un respaldo.** Son dos países y seis pastores; una cuenta compartida termina siendo la cuenta de nadie.
2. Eventos de los próximos 12 meses con fecha, sede, oradores y link de inscripción. Es el contenido que lanza el sitio.
3. Assets: logo vectorial, fotos de conferencias en alta, flyers originales, fotos del consejo. Si el logo vectorial no aparece, hay que revectorizarlo y suma horas.
4. ¿Hay material fotográfico de ediciones anteriores para armar el archivo histórico?
5. ~~¿Se conserva la identidad visual actual o el rediseño incluye rehacer la marca?~~ **Resuelta:** tema y sistema de diseño nuevos, se conserva solo el logo. Ver sección 11.
6. ¿El sitio debe ser bilingüe dado el trabajo en Estados Unidos? *(Preliminar: fase 2.)*
7. ¿Se mantienen los 19 artículos de Afirmaciones y Negaciones tal cual, o se revisa la traducción?
8. ¿Existe un listado de iglesias en comunión que deba publicarse, y un proceso para sumarse? *(Preliminar: fase 2.)*
9. ¿El ministerio quiere recibir donaciones desde el sitio? *(Preliminar: fase 2.)*
10. Quién reescribe los textos desactualizados de las iniciativas.

---

## 14. Cómo trabajar el proyecto con Claude

**Fase 0 — Fuente de verdad.** Repo con `CLAUDE.md` que contenga el modelo de datos, el mapa del sitio aprobado, los tokens de diseño y las convenciones de código. Todo lo que se le pida a Claude parte de ahí.

**Fase 1 — Contenido antes que diseño.** Cerrar las preguntas de la sección 13 y reunir los assets. Claude sirve para redactar el cuestionario y ordenar respuestas, no para decidir.

**Fase 2 — Diseñar la ficha de evento primero, no el home.** El home es consecuencia del modelo de eventos. Pedir tres direcciones distintas de la ficha como artefactos HTML y comparar en pantalla en vez de discutir en abstracto. Diseñar los cuatro estados, incluido el de ficha casi vacía.

**Fase 3 — Build.** Una pieza por sesión, con el `CLAUDE.md` cargado. "Hacé el sitio" no funciona; "implementá el CPT evento con estos campos y esta lógica de estado" sí.

**Fase 4 — Migración y redirects.** Artículos, mapeo 301, `schema.org/Event`. Trabajo mecánico donde Claude rinde mucho.

### Dos advertencias

- **Claude no escribe contenido del ministerio.** Fechas, oradores, sedes, y sobre todo los 19 artículos de Afirmaciones y Negaciones. Migrar verbatim. Si un dato falta, dejar el hueco marcado en vez de que se rellene con algo plausible.
- **Cuidado con el scope.** El relevamiento abre puertas que el ministerio no pidió. Alcance de fase 1 chico y cerrable; el resto anotado como fase 2. Un sitio entregado en tres meses le sirve más al ministerio que uno perfecto en un año.

---

## 15. Próximos pasos

1. Corregir el enlace del evento del 25-26 de septiembre. **Esta semana.**
2. Validar este documento con el consejo y responder la sección 13.
3. Armar el `CLAUDE.md` del repo con el modelo de datos definitivo.
4. Definir las fundaciones del sistema de diseño: tipografía, paleta y escalas.
5. Diseñar la ficha de evento en sus cuatro estados, sobre esas fundaciones.
6. Diseñar listado, archivo, home, iniciativas, artículo, nosotros y contacto.
7. Vectorizar el logo y generar variantes.
8. Construir el tema, migrar contenido, mapear 301 y lanzar.
