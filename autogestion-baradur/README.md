# Autogestión del empleado · NEWROL

Sitio de autogestión para Baradur (PHP 5.1.6) y MySQL 5.7, con el mismo diseño que los prototipos de NEWROL RRHH.

Pantallas: ingreso, inicio, datos personales, documentación requerida (con subida de archivos), haberes (recibos y Formulario 1359), asistencia (calendario, bandas horarias, licencias y accesos), carrera, contrato, mensajes y formularios de solicitud (licencia, reclamo y actualización de datos).

## Contenido

```
app/autogestion/Ag.php                       utilidades: escape HTML, formatos, sesión del empleado, archivos
app/autogestion/AutogestionRepositorio.php   TODAS las consultas SQL (lo único a adaptar a su base)
app/autogestion/AutogestionServicio.php      lógica: arma los datos de cada pantalla
app/http/controllers/AutogestionController.php
routes/autogestion.php                       rutas para pegar en routes/web.php
resources/views/autogestion/                 vistas Blade
storage/app/assets/autogestion/              CSS y JS (sin dependencias)
storage/app/autogestion/                     carpeta privada para recibos y documentos
database/autogestion_schema.sql              tablas (prefijo ag_)
database/autogestion_demo.sql                datos de ejemplo (ficticios)
```

## Instalación

1. Copie las carpetas `app`, `resources` y `storage` sobre su proyecto Baradur (no pisan archivos existentes).
2. Pegue el contenido de `routes/autogestion.php` al final de `routes/web.php`.
3. Borre la caché de clases para que Baradur encuentre las nuevas: `storage/framework/config/classes.php` (y `artisan_classes.php` si existe).
4. Cree las tablas: `mysql -u USUARIO -p BASE < database/autogestion_schema.sql`.
5. Opcional, para probar: `mysql -u USUARIO -p BASE < database/autogestion_demo.sql`.
6. Dé permisos de escritura al servidor web sobre `storage/app/autogestion` (ahí se guardan los documentos que suben los empleados).
7. Entre a `/autogestion/login`.

### Contraseña de prueba

Las contraseñas se validan con `Hash::check()` de Baradur, que depende del `APP_KEY` de su instalación. Por eso el hash no puede venir armado en el SQL. Para darle una contraseña al empleado de prueba (legajo 3172), agregue temporalmente esta ruta, ábrala una vez y **bórrela**:

```php
Route::get('ag-clave-demo', function () {
    DB::statement('UPDATE ag_empleado SET clave = ? WHERE legajo = ?', array(Hash::make('demo1234'), 3172));
    return 'listo';
});
```

Después ingrese con CUIL `20-25817734-6` (o legajo `3172`) y contraseña `demo1234`.

Los datos de ejemplo corresponden a noviembre de 2026; en otro mes el calendario se ve vacío hasta cargar fichadas reales.

## Adaptarlo a su base de RR.HH.

El sitio no depende de las tablas `ag_*`. Todas las consultas están en `AutogestionRepositorio.php`. Si los datos ya existen en otras tablas:

- Cambie el `FROM`/`JOIN` de cada consulta y use alias para devolver los mismos nombres de columna (por ejemplo `SELECT nro_leg AS legajo …`).
- No cambie los parámetros `?` ni el filtro por `empleado_id`: es lo que impide ver datos de otro empleado.
- Los recibos y documentos se guardan como ruta relativa dentro de `storage/app/autogestion/` (por ejemplo `recibos/1/2026-10_mensual.pdf`). Si sus PDF están en otro lado, cambie `Ag::carpetaArchivos()`.
- Si los empleados ya tienen una contraseña en otro sistema, cambie solo `Ag::verificarClave()` y la consulta `empleadoParaIngreso()`.

## Decisiones técnicas

- **Compatible con PHP 5.1.6 sin depender del conversor de Baradur**: no hay closures, namespaces, `[]` para arrays, `?:`, `json_*` ni `DateTime`. Las rutas usan la forma `'Controlador@metodo'`.
- **Escape de HTML**: en Baradur `{{ }}` imprime sin escapar (en Laravel sí escapa). Por eso todas las vistas usan `{{ Ag::e($valor) }}`. Si agrega campos, mantenga esa forma.
- **Sesión propia del empleado**, separada de los usuarios del sistema central (`$_SESSION['ag_autogestion']`). Al ingresar se regenera el id de sesión. Hay un límite de 5 intentos fallidos con espera de 5 minutos.
- **CSRF**: todos los formularios POST llevan `@csrf`, que valida el middleware de Baradur. Salir también es POST.
- **Descargas**: el controlador verifica que el recibo sea del empleado de la sesión y que la ruta del archivo esté dentro de la carpeta privada.
- **Subida de documentos**: solo PDF, JPG o PNG de hasta 10 MB, con nombre aleatorio y fuera de la carpeta pública. El documento pasa a "En revisión".
- **Base**: tablas InnoDB en `utf8` (no `utf8mb4`, que la librería cliente de PHP 5.1.6 no reconoce). PHP 5.1.6 ignora el `charset` del DSN: verifique que la conexión de Baradur ejecute `SET NAMES utf8` y que los acentos se vean bien.
- **Tipografías**: se cargan de Google Fonts. Sin salida a internet, el sitio usa las fuentes del sistema y se ve correcto. Para usarlas sin internet, descárguelas a `storage/app/assets/autogestion/fonts` y reemplace el `<link>` de las vistas.
- **JavaScript opcional**: solo agrega el menú en celular, el modo oscuro y ocultar o mostrar importes. Sin JavaScript el sitio funciona.

## Qué se probó y qué no

Probado en PHP 8.3 con MariaDB, usando un banco de pruebas que imita las funciones de Baradur que usa el sitio (`DB::select`, `DB::statement`, `view`, `redirect`, `asset`, `@csrf`) y un compilador Blade que, como el de Baradur, no escapa `{{ }}`:

- ingreso fallido, ingreso correcto y salida;
- las 14 pantallas, en escritorio y celular;
- validación de formularios y guardado de solicitudes;
- intento de ver un recibo o un mensaje de otro empleado (redirige sin mostrar nada);
- revisión automática de sintaxis no compatible con PHP 5.1.6.

No se pudo probar en PHP 5.1.6, en Baradur real ni en MySQL 5.7. Antes de publicarlo, recorra las pantallas en su servidor de pruebas.

Para revisar en la primera prueba:

- que `@include` pase las variables de la vista padre (es el comportamiento normal de Blade);
- que `redirect()` y `asset()` armen bien las URL con su `APP_URL`;
- que `storage/app/autogestion` no se pueda descargar escribiendo la URL en el navegador.
