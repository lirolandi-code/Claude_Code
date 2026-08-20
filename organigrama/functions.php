<?php
// Compatible con PHP 5.1 en adelante: sin type hints escalares/de
// retorno, sin funciones anónimas (closures), sin sintaxis corta de
// arrays.

require_once dirname(__FILE__) . '/config.php';

/**
 * Devuelve una conexión PDO reutilizable a la base de datos.
 */
function obtenerConexion()
{
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME;
        $opciones = array(
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            // El parámetro "charset" en el DSN recién se soportó en
            // PHP 5.3.6; para versiones anteriores hay que fijar el
            // charset con un comando de inicio de sesión.
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES '" . DB_CHARSET . "'",
        );
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $opciones);
    }
    return $pdo;
}

/**
 * Verifica que el código tenga el formato esperado: 1 letra + dígitos,
 * con la longitud total definida por SEGMENT_LENGTHS.
 */
function validarCodigo($codigo)
{
    global $SEGMENT_LENGTHS;
    $largoTotal = array_sum($SEGMENT_LENGTHS);
    if (strlen($codigo) !== $largoTotal) {
        return false;
    }
    return preg_match('/^[A-Z][0-9]{' . ($largoTotal - 1) . '}$/', $codigo) === 1;
}

/**
 * Divide el código en los segmentos jerárquicos definidos en SEGMENT_LENGTHS.
 */
function segmentosDeCodigo($codigo)
{
    global $SEGMENT_LENGTHS;
    $segmentos = array();
    $pos = 0;
    foreach ($SEGMENT_LENGTHS as $largo) {
        $segmentos[] = substr($codigo, $pos, $largo);
        $pos += $largo;
    }
    return $segmentos;
}

function segmentoEsCero($segmento)
{
    return preg_match('/^0+$/', $segmento) === 1;
}

/**
 * Nivel jerárquico = índice (1-based) del último segmento no nulo.
 */
function nivelDeCodigo($codigo)
{
    $segmentos = segmentosDeCodigo($codigo);
    $nivel = 1;
    $n = count($segmentos);
    for ($i = 1; $i < $n; $i++) {
        if (!segmentoEsCero($segmentos[$i])) {
            $nivel = $i + 1;
        }
    }
    return $nivel;
}

/**
 * Código de la dependencia de la cual depende $codigo, o null si es raíz
 * (nivel 1). Se obtiene anulando el último segmento no nulo.
 */
function codigoPadre($codigo)
{
    $segmentos = segmentosDeCodigo($codigo);
    $ultimoNoNulo = null;
    for ($i = count($segmentos) - 1; $i >= 1; $i--) {
        if (!segmentoEsCero($segmentos[$i])) {
            $ultimoNoNulo = $i;
            break;
        }
    }
    if ($ultimoNoNulo === null) {
        return null;
    }
    $segmentos[$ultimoNoNulo] = str_repeat('0', strlen($segmentos[$ultimoNoNulo]));
    return implode('', $segmentos);
}

/**
 * Trae todas las dependencias ordenadas por código.
 */
function obtenerDependencias($pdo)
{
    $sql = sprintf(
        'SELECT %s AS codigo_dependencia, %s AS descripcion FROM %s ORDER BY %s ASC',
        CAMPO_CODIGO,
        CAMPO_DESCRIPCION,
        TABLA_DEPENDENCIAS,
        CAMPO_CODIGO
    );
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}

/**
 * Busca una dependencia puntual por código.
 */
function obtenerDependenciaPorCodigo($pdo, $codigo)
{
    $sql = sprintf(
        'SELECT %s AS codigo_dependencia, %s AS descripcion FROM %s WHERE %s = ?',
        CAMPO_CODIGO,
        CAMPO_DESCRIPCION,
        TABLA_DEPENDENCIAS,
        CAMPO_CODIGO
    );
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array($codigo));
    $fila = $stmt->fetch();
    if ($fila) {
        return $fila;
    }
    return null;
}

/**
 * Construye el árbol jerárquico a partir del listado plano de dependencias.
 * Devuelve un arreglo con los nodos raíz; cada nodo tiene 'hijos' con sus
 * descendientes directos.
 */
function construirArbol($dependencias)
{
    $nodos = array();
    foreach ($dependencias as $dep) {
        $codigo = $dep['codigo_dependencia'];
        $nodos[$codigo] = array(
            'codigo'      => $codigo,
            'descripcion' => $dep['descripcion'],
            'nivel'       => nivelDeCodigo($codigo),
            'hijos'       => array(),
        );
    }

    $raices = array();
    foreach ($nodos as $codigo => &$nodo) {
        $padre = codigoPadre($codigo);
        if ($padre !== null && isset($nodos[$padre])) {
            $nodos[$padre]['hijos'][] = &$nodo;
        } else {
            $raices[] = &$nodo;
        }
    }
    unset($nodo);

    return $raices;
}

/**
 * Devuelve las dependencias hijas directas de $codigo (calculado, no
 * requiere columna de padre en la tabla).
 */
function obtenerHijosDirectos($todasLasDependencias, $codigo)
{
    $hijos = array();
    foreach ($todasLasDependencias as $d) {
        if (codigoPadre($d['codigo_dependencia']) === $codigo) {
            $hijos[] = $d;
        }
    }
    return $hijos;
}

/**
 * Imprime recursivamente el árbol como lista <ul>/<li> anidada, base del
 * organigrama visual (los conectores se dibujan con CSS).
 */
function renderizarArbol($nodos)
{
    if (empty($nodos)) {
        return;
    }
    echo '<ul class="organigrama">';
    foreach ($nodos as $nodo) {
        echo '<li>';
        printf(
            '<a class="nodo nivel-%d" href="detalle.php?codigo=%s" title="Ver detalle">' .
                '<span class="codigo">%s</span><span class="descripcion">%s</span>' .
                '</a>',
            (int) $nodo['nivel'],
            urlencode($nodo['codigo']),
            htmlspecialchars($nodo['codigo'], ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($nodo['descripcion'], ENT_QUOTES, 'UTF-8')
        );
        if (!empty($nodo['hijos'])) {
            renderizarArbol($nodo['hijos']);
        }
        echo '</li>';
    }
    echo '</ul>';
}
