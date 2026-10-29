<?php
/**
 * Suscripción por mail a Kit (29-10-2026): el formulario propio que va en
 * el pie de todo el sitio y bajo «Próximos eventos» en el inicio. Ver
 * parts/suscripcion.php y el bloque «Suscripción» de assets/js/app.js.
 *
 * No usa el script de Kit: el formulario manda directo al endpoint del
 * formulario publicado allá. Sin JavaScript es un POST común y Kit muestra
 * su propia página de confirmación.
 *
 * @package asp
 */

defined( 'ABSPATH' ) || exit;

/** Formulario «Recibí los próximos eventos» en Kit. */
const ASP_SUSCRIPCION_ENDPOINT = 'https://app.kit.com/forms/10117047/subscriptions';

/**
 * Prefijo único para los id de cada formulario: en el inicio hay dos en la
 * misma página (agenda y pie) y los label necesitan id distintos.
 *
 * @return string
 */
function asp_suscripcion_id(): string {
	static $n = 0;
	++$n;
	return 'suscripcion-' . $n;
}
