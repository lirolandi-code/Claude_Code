/* Autoconsulta v4 · comportamiento del diseño nuevo (menú en celular y modo claro/oscuro).
   No reemplaza ningún script existente: el calendario, los reclamos y las observaciones
   siguen usando su propio código. */
(function () {
  var raiz = document.documentElement;

  function leer(clave) { try { return localStorage.getItem(clave); } catch (e) { return null; } }
  function guardar(clave, valor) { try { localStorage.setItem(clave, valor); } catch (e) {} }

  function aplicarTema(tema) {
    if (tema) {
      raiz.setAttribute('data-theme', tema);
      raiz.setAttribute('data-bs-theme', tema);   // Bootstrap (ventanas emergentes, formularios)
    }
  }

  function temaActual() {
    var t = raiz.getAttribute('data-theme');
    if (t) { return t; }
    return (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) ? 'dark' : 'light';
  }

  aplicarTema(leer('nr_tema') || temaActual());

  function cerca(el, selector) {
    while (el && el.nodeType === 1) {
      if ((el.matches || el.msMatchesSelector).call(el, selector)) { return el; }
      el = el.parentNode;
    }
    return null;
  }

  document.addEventListener('click', function (ev) {
    var app = document.getElementById('nr-app');

    if (cerca(ev.target, '[data-nr-tema]')) {
      var nuevo = temaActual() === 'dark' ? 'light' : 'dark';
      aplicarTema(nuevo);
      guardar('nr_tema', nuevo);
      return;
    }

    if (cerca(ev.target, '#nr-menu')) {
      if (app) { app.classList.toggle('open'); }
      return;
    }

    if (app && app.classList.contains('open') && (!cerca(ev.target, '.sidenav') || cerca(ev.target, 'a, button'))) {
      app.classList.remove('open');
    }
  });
})();
