/* NEWROL · Autogestión del empleado
   JavaScript sin dependencias (ES5): menú en celular, tema claro/oscuro y ocultar importes.
   El sitio funciona sin JavaScript; esto solo agrega comodidades. */
(function () {
  var raiz = document.documentElement;
  var cuerpo = document.body;

  function leer(clave) {
    try { return window.localStorage ? localStorage.getItem(clave) : null; } catch (e) { return null; }
  }
  function guardar(clave, valor) {
    try { if (window.localStorage) { localStorage.setItem(clave, valor); } } catch (e) {}
  }
  function cerca(el, selector) {
    while (el && el.nodeType === 1) {
      if (el.matches ? el.matches(selector) : (el.msMatchesSelector && el.msMatchesSelector(selector))) { return el; }
      el = el.parentNode;
    }
    return null;
  }

  // Importes: ocultos por defecto, salvo que el empleado elija mostrarlos.
  function pintarImportes() {
    var visibles = leer('ag_importes') === 'visibles';
    cuerpo.className = cuerpo.className.replace(/\s*ag-oculto/g, '') + (visibles ? '' : ' ag-oculto');
    var botones = document.querySelectorAll('[data-importes]');
    for (var i = 0; i < botones.length; i++) {
      var t = botones[i].querySelector('span');
      if (t) { t.innerHTML = visibles ? 'Ocultar importes' : 'Mostrar importes'; }
    }
  }
  pintarImportes();

  document.addEventListener('click', function (ev) {
    var app = document.getElementById('app');

    if (cerca(ev.target, '[data-importes]')) {
      guardar('ag_importes', leer('ag_importes') === 'visibles' ? 'ocultos' : 'visibles');
      pintarImportes();
      return;
    }

    if (cerca(ev.target, '[data-tema]')) {
      var oscuro = raiz.getAttribute('data-theme')
        ? raiz.getAttribute('data-theme') === 'dark'
        : (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches);
      raiz.setAttribute('data-theme', oscuro ? 'light' : 'dark');
      guardar('ag_tema', oscuro ? 'light' : 'dark');
      return;
    }

    if (cerca(ev.target, '#menuBtn')) {
      if (app) { app.className = app.className.indexOf(' open') >= 0 ? app.className.replace(' open', '') : app.className + ' open'; }
      return;
    }

    // Cerrar el menú al tocar fuera de él o al elegir una opción.
    if (app && app.className.indexOf(' open') >= 0 && (!cerca(ev.target, '.nav') || cerca(ev.target, 'a'))) {
      app.className = app.className.replace(' open', '');
    }
  });
})();
