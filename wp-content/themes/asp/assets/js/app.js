/* Ante Su Palabra — JavaScript mínimo, sin dependencias.
   Cabecera: menú móvil a pantalla completa y estado "scrolled".
   Los desplegables de bio usan <details> nativo. */

/* ---- Copiar enlace (artículo): sin portapapeles el botón no aparece ---- */
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
	function setOpen(open) {
		nav.classList.toggle('is-open', open);
		header.classList.toggle('is-menu-open', open);
		document.documentElement.classList.toggle('asp-menu-abierto', open);
		toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
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
