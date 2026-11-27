# Autoconsulta v4 · nueva imagen

Rediseño visual de la Autoconsulta (autoconsulta_v3) con la imagen y la navegación nuevas de NEWROL.

**La funcionalidad no cambia.** Controladores, modelos, rutas, consultas y servicios externos quedan como están. Solo se reemplazan las vistas del sitio del empleado y se agregan una hoja de estilos, un script y una clase de ayuda.

## Qué se reemplaza

Haga una copia de seguridad de estos archivos antes de reemplazarlos:

| Archivo | Pantalla |
|---|---|
| `resources/views/layouts/header-landing.blade.php` | Diseño general: menú lateral, barra superior, ventanas de observaciones y cambio de clave |
| `resources/views/general.blade.php` | Datos personales |
| `resources/views/verificar-datos.blade.php` | Documentación requerida, Evaluaciones, Carrera administrativa |
| `resources/views/recibos.blade.php` | Haberes, Formulario 1359, detalle de Ganancias |
| `resources/views/asistencia.blade.php` | Calendario, reclamo de inconsistencias, solicitud de licencia |
| `resources/views/bandas.blade.php` | Bandas horarias |
| `resources/views/licencias.blade.php` | Licencias |
| `resources/views/acceso.blade.php` | Accesos y salidas autorizadas |
| `resources/views/cursos.blade.php` | Capacitación |
| `resources/views/contratos.blade.php` | Contrato y manejo de expedientes |
| `resources/views/auth/login.blade.php` | Ingreso |
| `resources/views/partials/message-list.blade.php` | Lista de observaciones |
| `resources/views/partials/no-messages.blade.php` | Sin observaciones |

## Qué se agrega

```
resources/views/partials/nr-iconos.blade.php          íconos
resources/views/partials/nr-nav.blade.php             menú lateral
resources/views/partials/nr-topbar.blade.php          barra superior
resources/views/partials/nr-tabs-*.blade.php          pestañas de Datos personales, Asistencia y Carrera
app/http/services/Vista.php                           escape de HTML y sección activa del menú
storage/app/assets/css/newrol.css                     estilos (también adapta Bootstrap y el calendario)
storage/app/assets/js/newrol.js                       menú en celular y modo claro/oscuro
```

## Instalación

1. Copie el contenido de esta carpeta sobre `autoconsulta_v3`, respetando las rutas.
2. Borre las cachés de Baradur para que tome las vistas y la clase nueva:
   - todo el contenido de `storage/framework/views/`;
   - `storage/framework/config/classes.php`.
3. Entre a la Autoconsulta y recorra las pantallas.

Para volver atrás, restaure los archivos de la copia de seguridad y vuelva a borrar las cachés.

## Qué se mantiene igual

- **Calendario**: FullCalendar con las mismas cinco fuentes de la API de Calendario (novedades, pendientes, feriados, fichadas y situaciones particulares), el detalle de novedad al hacer clic, la carga de adjuntos y los días sin registro marcados en rojo.
- **Reclamo de inconsistencia**: el mismo formulario, que adjunta opcionalmente la banda horaria desde la API de novedades y se envía a `envioReclamo`.
- **Solicitud de licencia**: la misma ventana, que carga `rrhh.novedades.crear` por AJAX.
- **Observaciones**: el contador se actualiza cada 10 segundos, se cargan al abrir la ventana y se marcan como leídas. Ahora se abren desde la barra superior y desde el menú.
- **Recibos**: el visor de PDF, la descarga, el detalle de Ganancias y el Formulario 1359. El año se cambia con el mismo `datepicker.js`.
- **Bandas horarias, Documentación, Evaluaciones y Carrera administrativa**: los mismos sistemas externos, dentro de un marco.
- **Contrato y expedientes**: las mismas consultas a la API de expedientes.
- Las mismas librerías (Bootstrap 5.3.0, FullCalendar 6.1.15, jQuery 3.5.1) desde las mismas direcciones.

## Cambios menores, a propósito

- Todos los datos se imprimen con `Vista::e()`, porque en Baradur `{{ }}` no escapa el HTML. Un nombre o una observación con `<` o `&` ahora se ve tal cual, en lugar de interpretarse como código.
- En Contrato, los datos que llegan de la API se escapan antes de mostrarse, y se bloquean los enlaces `javascript:`.
- Tres protecciones en los scripts:
  - el calendario solo se inicia donde existe;
  - el formulario `formSalida` solo se envía donde existe;
  - los enlaces de Bandas y Accesos se actualizan en las pestañas y en el menú.
- Se quitó la ventana "Reclamo PRUEBA", porque su botón estaba comentado y repetía el id `formReclamo`.
- `route(general)` (sin comillas) pasó a `route('general')`. En PHP 8 la forma sin comillas da error.
- El modo oscuro también se aplica a las ventanas de Bootstrap.

## Pruebas realizadas

Las vistas se compilaron y ejecutaron en PHP 8.3 con un banco de pruebas que imita las funciones de Baradur que usan (`Auth`, `Route::is`, `route`, `asset`, modelos y paginadores) y con datos ficticios. Resultado:

- todas las pantallas se generan sin errores ni avisos de PHP;
- se revisaron capturas en escritorio y celular;
- la ventana de reclamo abre y se ve con el diseño nuevo;
- el escape de HTML funciona.

**No se pudo probar** con su servidor, su base de datos ni sus API (calendario, bandas, expedientes), porque no hay acceso desde este entorno. Por eso, en las capturas de prueba los días hábiles del calendario aparecen en rojo: el script no recibe fichadas y los marca como sin registro.

Antes de publicarlo, conviene probarlo en un entorno de prueba con un usuario real y recorrer:
- el calendario y el detalle de una novedad;
- el reclamo, con y sin banda horaria;
- la solicitud de licencia;
- el visor de un recibo;
- las observaciones.

## Fuera de este paquete

- Las vistas **gerenciales** (`resources/views/gerencial/*` y `layouts/header-gerencial`), que usa el tablero para ver a un empleado, siguen con el diseño anterior. Pueden adaptarse con los mismos componentes en un segundo paso.
- El panel de RR.HH. (`rrhh/agente-consulta-crear`), que se abre dentro de "Solicitud de licencia", hereda los estilos nuevos de Bootstrap pero no se rediseñó.
