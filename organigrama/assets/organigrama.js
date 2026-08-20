(function () {
    'use strict';

    var contenedor = document.getElementById('contenedor-organigrama');
    var etiqueta = document.getElementById('zoom-nivel');
    var botonMenos = document.getElementById('zoom-menos');
    var botonMas = document.getElementById('zoom-mas');
    var botonReset = document.getElementById('zoom-reset');
    var botonAjustar = document.getElementById('zoom-ajustar');

    if (!contenedor || !etiqueta) {
        return;
    }

    var ZOOM_MIN = 0.2;
    var ZOOM_MAX = 2;
    var nivelActual = 1;

    function aplicarZoom(nuevoNivel) {
        nivelActual = Math.max(ZOOM_MIN, Math.min(ZOOM_MAX, nuevoNivel));
        // "zoom" (a diferencia de "transform: scale") también achica el
        // área que ocupa el contenido, así el scroll horizontal/vertical
        // se ajusta solo. Es una propiedad no estándar pero soportada
        // por todos los navegadores modernos (Chrome, Edge, Safari,
        // Firefox 126+).
        contenedor.style.zoom = nivelActual;
        etiqueta.textContent = Math.round(nivelActual * 100) + '%';
    }

    function ajustarParaVerTodo() {
        var arbol = contenedor.querySelector('ul.organigrama');
        if (!arbol) {
            return;
        }
        // Mide en tamaño real (zoom 1) para calcular la proporción.
        contenedor.style.zoom = 1;
        var anchoDisponible = contenedor.clientWidth;
        var anchoArbol = arbol.scrollWidth;
        var altoDisponible = window.innerHeight - contenedor.getBoundingClientRect().top - 24;
        var altoArbol = arbol.scrollHeight;
        var proporcion = 1;
        if (anchoArbol > 0) {
            proporcion = Math.min(proporcion, anchoDisponible / anchoArbol);
        }
        if (altoArbol > 0 && altoDisponible > 0) {
            proporcion = Math.min(proporcion, altoDisponible / altoArbol);
        }
        aplicarZoom(proporcion);
    }

    if (botonMenos) {
        botonMenos.addEventListener('click', function () {
            aplicarZoom(nivelActual - 0.1);
        });
    }
    if (botonMas) {
        botonMas.addEventListener('click', function () {
            aplicarZoom(nivelActual + 0.1);
        });
    }
    if (botonReset) {
        botonReset.addEventListener('click', function () {
            aplicarZoom(1);
        });
    }
    if (botonAjustar) {
        botonAjustar.addEventListener('click', ajustarParaVerTodo);
    }
})();
