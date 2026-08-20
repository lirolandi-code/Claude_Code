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
