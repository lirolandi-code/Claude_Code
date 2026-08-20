<?php
declare(strict_types=1);

// --- Conexión a la base de datos --------------------------------------
define('DB_HOST', 'localhost');
define('DB_NAME', 'organigrama');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

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
define('SEGMENT_LENGTHS', [1, 1, 2, 2, 2, 2]);
