/* Ante Su Palabra — panel. Selector de imágenes y repetidor del programa.
   Vanilla; wp.media es del core. */
(function () {
	'use strict';

	/* ---- Selector de imagen ---- */
	document.querySelectorAll('[data-asp-imagen]').forEach(function (campo) {
		var input = campo.querySelector('input[type="hidden"]');
		var marco = campo.querySelector('.asp-imagen__marco');
		var elegir = campo.querySelector('[data-asp-elegir]');
		var quitar = campo.querySelector('[data-asp-quitar]');
		var frame = null;

		elegir.addEventListener('click', function () {
			if (!window.wp || !wp.media) {
				return;
			}
			if (!frame) {
				frame = wp.media({
					title: (window.aspAdmin && aspAdmin.elegirTitulo) || 'Elegir imagen',
					button: { text: (window.aspAdmin && aspAdmin.elegirBoton) || 'Usar esta imagen' },
					library: { type: 'image' },
					multiple: false
				});
				frame.on('select', function () {
					var att = frame.state().get('selection').first().toJSON();
					var url = (att.sizes && att.sizes.medium) ? att.sizes.medium.url : att.url;
					input.value = att.id;
					marco.innerHTML = '';
					var img = document.createElement('img');
					img.src = url;
					img.alt = '';
					img.className = 'asp-imagen__preview';
					marco.appendChild(img);
					marco.hidden = false;
					quitar.hidden = false;
				});
			}
			frame.open();
		});

		quitar.addEventListener('click', function () {
			input.value = '';
			marco.innerHTML = '';
			marco.hidden = true;
			quitar.hidden = true;
		});
	});

	/* ---- Repetidor ---- */
	document.querySelectorAll('[data-asp-repetidor]').forEach(function (rep) {
		var filas = rep.querySelector('[data-asp-filas]');
		var plantilla = rep.querySelector('[data-asp-plantilla]');
		var nombre = rep.getAttribute('data-asp-nombre');

		function renumerar() {
			filas.querySelectorAll('[data-asp-fila]').forEach(function (fila, i) {
				fila.querySelectorAll('input').forEach(function (inp) {
					inp.name = inp.name.replace(/\[\d+\]/, '[' + i + ']');
				});
			});
		}

		rep.addEventListener('click', function (event) {
			var boton = event.target.closest('button');
			if (!boton) {
				return;
			}
			if (boton.hasAttribute('data-asp-agregar-fila')) {
				var nueva = plantilla.content.firstElementChild.cloneNode(true);
				filas.appendChild(nueva);
				renumerar();
				var primero = nueva.querySelector('input');
				if (primero) {
					primero.focus();
				}
			}
			if (boton.hasAttribute('data-asp-quitar-fila')) {
				var fila = boton.closest('[data-asp-fila]');
				if (fila) {
					fila.remove();
					renumerar();
				}
			}
		});

		void nombre;
	});
})();

/* ---- Predicaciones del evento: un tilde marca todo el grupo del día ---- */
(function () {
	'use strict';

	document.querySelectorAll('[data-asp-grupo]').forEach(function (grupo) {
		var todas = grupo.querySelector('[data-asp-marcar-grupo]');
		var items = grupo.querySelectorAll('input[name="asp_evento_predicaciones[]"]');
		if (!todas) {
			return;
		}
		function sincronizar() {
			var n = 0;
			items.forEach(function (i) { if (i.checked) { n++; } });
			todas.checked = n === items.length;
			todas.indeterminate = n > 0 && n < items.length;
		}
		todas.addEventListener('change', function () {
			items.forEach(function (i) { i.checked = todas.checked; });
		});
		items.forEach(function (i) { i.addEventListener('change', sincronizar); });
		sincronizar();
	});
})();
