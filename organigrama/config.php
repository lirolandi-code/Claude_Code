<?php
// Compatible con PHP 5.1 en adelante (hosting sin posibilidad de
// actualizar la versión de PHP): sin declare(strict_types), sin type
// hints escalares, sin arrays como constantes.

// --- Conexión a la base de datos --------------------------------------
define('DB_HOST', 'localhost');
define('DB_NAME', 'milegajo');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8');

// --- Tabla y campos de dependencias -------------------------------------
define('TABLA_DEPENDENCIAS', 'sv_dpnpdat0');
define('CAMPO_CODIGO', 'DPNDEP');
define('CAMPO_DESCRIPCION', 'DPNDES');

// Solo se muestran/aceptan dependencias cuyo código empiece con este
// prefijo (por ejemplo, todas las que dependen del ministerio "A").
// Dejar en '' (cadena vacía) para no filtrar y mostrar todos los códigos.
define('PREFIJO_CODIGO_FILTRO', 'A');

// Cantidad de niveles que se muestran ya desplegados al entrar al
// organigrama (los niveles más profundos arrancan colapsados y se
// despliegan haciendo clic en el circulito +/- debajo de cada raviol).
define('NIVELES_EXPANDIDOS_POR_DEFECTO', 4);

// URL (propia o de otro sistema) a la que apunta el botón adicional de
// cada raviol. Se le agrega automáticamente "?Dep=<codigo>" (o
// "&Dep=<codigo>" si la URL ya trae parámetros) para que esa página
// sepa de qué dependencia mostrar información.
define('URL_DETALLE_EXTERNA', 'https://tudominio.com/ficha-dependencia.php');

// Si es true, se ocultan las ramas del organigrama que no tengan
// personal activo en ningún punto (según SQL_CODIGOS_ACTIVOS).
define('FILTRAR_SOLO_ACTIVAS', true);

// Nivel jerárquico que se usa como "unidad de decisión" para el filtro
// de arriba: se agrupan las dependencias por su ancestro de este nivel
// (por ejemplo, 2 = la Secretaría). Si ESE grupo tiene personal activo
// en cualquier punto de su estructura, se muestra COMPLETO -todas sus
// subsecretarías, direcciones, etc., aunque esos niveles intermedios no
// tengan personal propio-, en vez de mostrar solo el camino mínimo
// hasta cada código con personal. Así se conserva la estructura
// administrativa real del área activa. Solo desaparecen del todo los
// grupos (secretarías, en el ejemplo) que no tienen NINGÚN personal
// activo en ninguno de sus niveles.
// Bajarlo (por ej. a 1) muestra ramas más grandes con menos recorte;
// subirlo (por ej. a 3) recorta más agresivo, a nivel subsecretaría.
define('NIVEL_CORTE_ACTIVAS', 2);

// Subconsulta que devuelve los códigos de dependencia con personal
// activo asignado. Se usa tal cual (entre paréntesis) dentro de un
// "IN (...)"; ajustar si cambia el criterio de "activo".
define('SQL_CODIGOS_ACTIVOS',
    'SELECT DISTINCT(P20V72) FROM sv_usrpp20 ' .
    'LEFT JOIN sv_usrpo10 ON P20A01 = O10A01 ' .
    'LEFT JOIN sv_usrpa01 ON P20A01 = A01A01 ' .
    "WHERE O10O08 > 0 AND O10O08 < 7 AND A01A41 = 0 AND A01A05 <> 'S' AND A01A18 = 'M'"
);

// --- Estructura del código de dependencia (10 posiciones) --------------
// El código se compone de "segmentos" concatenados; cada segmento
// representa un nivel jerárquico y su cantidad de caracteres.
// Ejemplo con [1, 1, 2, 2, 2, 2] (suma = 10):
//   A000000000  -> nivel 1 (MINISTERIO)
//   A100000000  -> nivel 2 (SECRETARIA), segmento 2 = "1"
//   A110000000  -> nivel 3 (SUBSECRETARIA), segmento 3 = "10"
//   A110100000  -> nivel 4 (DIRECCION), segmento 4 = "10"
// Un código pertenece al nivel del ÚLTIMO segmento distinto de cero;
// su padre se obtiene poniendo ese segmento en ceros.
// Si el esquema real de códigos de la empresa difiere, basta con
// ajustar este arreglo (los valores deben sumar 10).
//
// PHP anterior a la versión 7 no permite guardar un array como
// constante con define(), por eso se usa una variable global.
$GLOBALS['SEGMENT_LENGTHS'] = array(1, 1, 2, 2, 2, 2);
