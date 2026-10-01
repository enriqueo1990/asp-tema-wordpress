/* Ante Su Palabra — JavaScript mínimo, sin dependencias.
   Cabecera: menú móvil a pantalla completa y estado "scrolled".
   Los desplegables de bio usan <details> nativo. */

/* ---- Menú de compartir del sistema (ficha de evento): solo donde existe ---- */
(function () {
	'use strict';

	if (!navigator.share) {
		return;
	}
	document.querySelectorAll('[data-asp-compartir]').forEach(function (boton) {
		boton.hidden = false;
		boton.addEventListener('click', function () {
			navigator.share({
				title: boton.getAttribute('data-titulo'),
				text: boton.getAttribute('data-texto'),
				url: boton.getAttribute('data-url')
			}).catch(function () {
				/* Cancelado por quien comparte: no hay nada que avisar. */
			});
		});
	});
})();

/* ---- Copiar enlace (artículo y evento): sin portapapeles el botón no aparece ---- */
(function () {
	'use strict';

	if (!navigator.clipboard) {
		return;
	}
	document.querySelectorAll('[data-asp-copiar]').forEach(function (boton) {
		var original = boton.textContent;
		boton.hidden = false;
		boton.addEventListener('click', function () {
			navigator.clipboard.writeText(boton.getAttribute('data-asp-copiar')).then(function () {
				boton.textContent = boton.getAttribute('data-asp-copiado');
				window.setTimeout(function () {
					boton.textContent = original;
				}, 2400);
			});
		});
	});
})();

(function () {
	'use strict';

	var header = document.querySelector('[data-asp-header]');
	var toggle = document.querySelector('[data-asp-toggle]');
	var nav = document.getElementById('asp-nav');
	if (!header || !toggle || !nav) {
		return;
	}

	/* ---- Menú móvil ---- */
	/* Con el menú abierto, lo que queda tapado se vuelve inerte: el Tab y
	   los lectores de pantalla no lo recorren. */
	var fondo = [document.getElementById('contenido'), document.querySelector('.asp-footer')].filter(Boolean);

	function setOpen(open) {
		nav.classList.toggle('is-open', open);
		header.classList.toggle('is-menu-open', open);
		document.documentElement.classList.toggle('asp-menu-abierto', open);
		toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
		fondo.forEach(function (el) {
			el.inert = open;
		});
		if (open) {
			var primero = nav.querySelector('a');
			if (primero) {
				primero.focus();
			}
		}
	}

	toggle.addEventListener('click', function () {
		setOpen(toggle.getAttribute('aria-expanded') !== 'true');
	});

	document.addEventListener('keydown', function (event) {
		if (event.key === 'Escape' && nav.classList.contains('is-open')) {
			setOpen(false);
			toggle.focus();
		}
	});

	nav.addEventListener('click', function (event) {
		if (event.target.closest('a')) {
			setOpen(false);
		}
	});

	var mq = window.matchMedia('(min-width: 1024px)');
	mq.addEventListener('change', function (e) {
		if (e.matches) {
			setOpen(false);
		}
	});

	/* ---- Estado scrolled ---- */
	var umbral = 24;
	var pendiente = false;
	function actualizar() {
		pendiente = false;
		header.classList.toggle('is-scrolled', window.scrollY > umbral);
	}
	window.addEventListener('scroll', function () {
		if (!pendiente) {
			pendiente = true;
			window.requestAnimationFrame(actualizar);
		}
	}, { passive: true });
	actualizar();
})();

/* ---- Índices de años: abrir el año plegado al que se salta ---- */
(function () {
	'use strict';

	function abrir() {
		var id = window.location.hash.slice(1);
		if (!id) {
			return;
		}
		var el = document.getElementById(id);
		if (!el) {
			return;
		}
		var plegable = el.matches('details') ? el : el.querySelector('details');
		if (plegable && !plegable.open) {
			plegable.open = true;
			el.scrollIntoView();
		}
	}

	window.addEventListener('hashchange', abrir);
	abrir();
})();


/* ---- Carril de próximos eventos (inicio): flechas para quien usa mouse ---- */
/* Sin JavaScript el carril igual se desplaza con el dedo, el trackpad o el
   teclado; las flechas solo aparecen si hay más carteles de los que entran. */
(function () {
	'use strict';

	var carril = document.querySelector('[data-asp-carril]');
	var flechas = document.querySelector('[data-asp-carril-flechas]');
	if (!carril || !flechas) {
		return;
	}
	var botones = flechas.querySelectorAll('[data-asp-carril-ir]');
	var quieto = window.matchMedia('(prefers-reduced-motion: reduce)');

	function actualizar() {
		var max = carril.scrollWidth - carril.clientWidth;
		flechas.hidden = max <= 1;
		botones[0].disabled = carril.scrollLeft <= 1;
		botones[1].disabled = carril.scrollLeft >= max - 1;
	}

	botones.forEach(function (boton) {
		boton.addEventListener('click', function () {
			var cartel = carril.querySelector('li');
			var paso = cartel ? cartel.getBoundingClientRect().width : carril.clientWidth;
			var gap = parseFloat(window.getComputedStyle(cartel.parentNode).columnGap) || 0;
			carril.scrollBy({
				left: Number(boton.getAttribute('data-asp-carril-ir')) * (paso + gap),
				behavior: quieto.matches ? 'auto' : 'smooth'
			});
		});
	});

	carril.addEventListener('scroll', actualizar, { passive: true });
	window.addEventListener('resize', actualizar);
	actualizar();
})();
