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
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            // No se usa PDO::ATTR_DEFAULT_FETCH_MODE: en algunos builds
            // de PDO muy antiguos/recortados esa constante no está
            // definida. En vez de fijar un modo por defecto, cada
            // consulta pide PDO::FETCH_ASSOC explícitamente.
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
 * Verdadero si $codigo cumple con PREFIJO_CODIGO_FILTRO (o si el filtro
 * está desactivado, es decir, la constante está vacía).
 */
function codigoPasaFiltro($codigo)
{
    if (PREFIJO_CODIGO_FILTRO === '') {
        return true;
    }
    return strpos($codigo, PREFIJO_CODIGO_FILTRO) === 0;
}

/**
 * Trae todas las dependencias ordenadas por código, limitadas a las que
 * empiezan con PREFIJO_CODIGO_FILTRO (si está configurado).
 */
function obtenerDependencias($pdo)
{
    $sql = sprintf(
        'SELECT %s AS codigo_dependencia, %s AS descripcion FROM %s',
        CAMPO_CODIGO,
        CAMPO_DESCRIPCION,
        TABLA_DEPENDENCIAS
    );
    $parametros = array();
    if (PREFIJO_CODIGO_FILTRO !== '') {
        $sql .= sprintf(' WHERE %s LIKE ?', CAMPO_CODIGO);
        $parametros[] = PREFIJO_CODIGO_FILTRO . '%';
    }
    $sql .= sprintf(' ORDER BY %s ASC', CAMPO_CODIGO);

    $stmt = $pdo->prepare($sql);
    $stmt->execute($parametros);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Busca una dependencia puntual por código (respeta PREFIJO_CODIGO_FILTRO).
 */
function obtenerDependenciaPorCodigo($pdo, $codigo)
{
    if (!codigoPasaFiltro($codigo)) {
        return null;
    }
    $sql = sprintf(
        'SELECT %s AS codigo_dependencia, %s AS descripcion FROM %s WHERE %s = ?',
        CAMPO_CODIGO,
        CAMPO_DESCRIPCION,
        TABLA_DEPENDENCIAS,
        CAMPO_CODIGO
    );
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array($codigo));
    $fila = $stmt->fetch(PDO::FETCH_ASSOC);
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
 * Arma la URL del botón externo de un nodo, agregando "Dep=<codigo>" ya
 * sea con "?" o con "&" según si URL_DETALLE_EXTERNA ya trae parámetros.
 */
function urlDetalleExterna($codigo)
{
    $separador = strpos(URL_DETALLE_EXTERNA, '?') === false ? '?' : '&';
    return URL_DETALLE_EXTERNA . $separador . 'Dep=' . urlencode($codigo);
}

/**
 * Imprime la "caja" de un nodo: el cuerpo (código + descripción, enlaza
 * al detalle interno) y el botón hacia la URL externa configurable.
 */
function renderizarNodo($nodo)
{
    printf(
        '<span class="nodo nivel-%d">' .
            '<a class="nodo-cuerpo" href="detalle.php?codigo=%s" title="Ver detalle">' .
                '<span class="codigo">%s</span><span class="descripcion">%s</span>' .
            '</a>' .
            '<a class="nodo-boton" href="%s" target="_blank" rel="noopener" title="Ver información relacionada">Ver ficha &rarr;</a>' .
        '</span>',
        (int) $nodo['nivel'],
        urlencode($nodo['codigo']),
        htmlspecialchars($nodo['codigo'], ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($nodo['descripcion'], ENT_QUOTES, 'UTF-8'),
        htmlspecialchars(urlDetalleExterna($nodo['codigo']), ENT_QUOTES, 'UTF-8')
    );
}

/**
 * Imprime recursivamente el árbol como lista <ul>/<li> anidada, base del
 * organigrama visual (los conectores se dibujan con CSS). Los nodos con
 * hijos se envuelven en <details>/<summary> (sin JavaScript) para poder
 * plegar/desplegar sus ramas; los niveles hasta
 * NIVELES_EXPANDIDOS_POR_DEFECTO arrancan desplegados.
 */
function renderizarArbol($nodos)
{
    if (empty($nodos)) {
        return;
    }
    echo '<ul class="organigrama">';
    foreach ($nodos as $nodo) {
        echo '<li>';
        if (!empty($nodo['hijos'])) {
            $abierto = $nodo['nivel'] <= NIVELES_EXPANDIDOS_POR_DEFECTO ? ' open' : '';
            echo '<details class="rama"' . $abierto . '>';
            echo '<summary>';
            renderizarNodo($nodo);
            echo '<span class="toggle-icon" aria-hidden="true"></span>';
            echo '</summary>';
            renderizarArbol($nodo['hijos']);
            echo '</details>';
        } else {
            renderizarNodo($nodo);
        }
        echo '</li>';
    }
    echo '</ul>';
}
