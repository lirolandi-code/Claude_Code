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
 * Códigos de dependencia con personal activo asignado (SQL_CODIGOS_ACTIVOS),
 * como array de códigos. Se cachea en memoria: dentro de un mismo request
 * se pide varias veces (organigrama, detalle, hijos) y es la misma consulta.
 */
function obtenerCodigosActivos($pdo)
{
    static $codigos = null;
    if ($codigos === null) {
        $stmt = $pdo->query(SQL_CODIGOS_ACTIVOS);
        $codigos = array();
        $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($filas as $fila) {
            // No se asume el nombre de columna que devuelve la
            // subconsulta: se toma el primer valor de cada fila.
            $valores = array_values($fila);
            if (isset($valores[0]) && $valores[0] !== null) {
                $codigos[] = $valores[0];
            }
        }
    }
    return $codigos;
}

/**
 * Verdadero si $codigo cumple con PREFIJO_CODIGO_FILTRO y, si
 * FILTRAR_SOLO_ACTIVAS está activo, es una dependencia visible (activa
 * o ancestro de alguna activa).
 */
function codigoEsVisible($pdo, $codigo)
{
    if (!codigoPasaFiltro($codigo)) {
        return false;
    }
    $indice = indiceDeDependencias(obtenerDependencias($pdo));
    return isset($indice[$codigo]);
}

/**
 * Trae todas las dependencias ordenadas por código, limitadas a las que
 * empiezan con PREFIJO_CODIGO_FILTRO (si está configurado). Si
 * FILTRAR_SOLO_ACTIVAS está activo, se queda estrictamente con los
 * códigos que devuelve SQL_CODIGOS_ACTIVOS MÁS la cadena de ancestros
 * reales de cada uno (para que el árbol llegue conectado hasta la raíz
 * en vez de quedar roto); no se agrega ninguna otra dependencia. Cada
 * fila devuelta trae 'activa' (true/false): false para los ancestros
 * que se incluyen solo por conectar el árbol pero no tienen personal
 * activo propio. Se cachea en memoria: dentro de un mismo request se
 * pide varias veces (organigrama, detalle, hijos) y es siempre la
 * misma consulta.
 */
function obtenerDependencias($pdo)
{
    static $resultado = null;
    if ($resultado !== null) {
        return $resultado;
    }

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
    $todas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!FILTRAR_SOLO_ACTIVAS) {
        $resultado = $todas;
        return $resultado;
    }

    $indice = indiceDeDependencias($todas);
    $activosLista = obtenerCodigosActivos($pdo);
    $activos = array_flip($activosLista);

    // Visibles: cada código activo (que exista realmente en la tabla) +
    // toda su cadena de códigos padre reales, para que el árbol quede
    // conectado. Nada más se agrega.
    $visibles = array();
    foreach ($activosLista as $codigo) {
        if (!isset($indice[$codigo])) {
            continue; // el código activo no está en la tabla (o no pasa el prefijo)
        }
        $actual = $codigo;
        while ($actual !== null && !isset($visibles[$actual])) {
            $visibles[$actual] = true;
            $actual = codigoPadre($actual);
        }
    }

    $resultado = array();
    foreach ($todas as $dep) {
        if (isset($visibles[$dep['codigo_dependencia']])) {
            $dep['activa'] = isset($activos[$dep['codigo_dependencia']]);
            $resultado[] = $dep;
        }
    }
    return $resultado;
}

/**
 * Busca una dependencia puntual por código (respeta PREFIJO_CODIGO_FILTRO
 * y FILTRAR_SOLO_ACTIVAS).
 */
function obtenerDependenciaPorCodigo($pdo, $codigo)
{
    if (!codigoEsVisible($pdo, $codigo)) {
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
 * Arma un índice codigo => true a partir de un listado de dependencias,
 * para poder chequear rápido "¿este código está en la lista?".
 */
function indiceDeDependencias($dependencias)
{
    $indice = array();
    foreach ($dependencias as $d) {
        $indice[$d['codigo_dependencia']] = true;
    }
    return $indice;
}

/**
 * Sube por la cadena de códigos padre de $codigo hasta encontrar uno
 * presente en $indice, salteando los niveles intermedios que no están
 * (por ejemplo, por FILTRAR_SOLO_ACTIVAS o porque esa fila directamente
 * no existe en la tabla). Devuelve null si ningún ancestro está presente
 * (osea, $codigo debe mostrarse como raíz).
 */
function ancestroVisible($codigo, $indice)
{
    $actual = codigoPadre($codigo);
    while ($actual !== null && !isset($indice[$actual])) {
        $actual = codigoPadre($actual);
    }
    return $actual;
}

/**
 * Compara dos códigos de dependencia por nivel jerárquico y, dentro del
 * mismo nivel, por código. Así una subsecretaría (nivel 3) queda antes
 * que una dirección (nivel 4) aunque ambas sean hijas directas de la
 * misma secretaría.
 */
function compararCodigosPorNivelYCodigo($a, $b)
{
    $nivelA = nivelDeCodigo($a);
    $nivelB = nivelDeCodigo($b);
    if ($nivelA !== $nivelB) {
        return $nivelA < $nivelB ? -1 : 1;
    }
    return strcmp($a, $b);
}

/**
 * Igual que compararCodigosPorNivelYCodigo() pero para filas planas de
 * la base (clave 'codigo_dependencia'), como las que devuelve
 * obtenerHijosDirectos().
 */
function compararDependenciasPorNivelYCodigo($a, $b)
{
    return compararCodigosPorNivelYCodigo($a['codigo_dependencia'], $b['codigo_dependencia']);
}

/**
 * Arma recursivamente, a partir de una lista de códigos hermanos, sus
 * nodos ya ordenados y con 'hijos' anidado. $nodos es codigo => datos
 * planos (sin 'hijos'); $hijosDe es codigo => array de códigos hijos.
 *
 * Deliberadamente NO usa referencias de PHP (&) para armar el árbol:
 * arma arrays de valores nuevos en cada nivel. Guardar referencias en
 * 'hijos' y después ordenarlas con usort() puede perder o duplicar
 * elementos según la versión de PHP -este proyecto apunta a poder
 * correr desde PHP 5.1-, así que se evita esa combinación por completo.
 */
function armarRamas($codigos, $nodos, $hijosDe, $profundidad)
{
    usort($codigos, 'compararCodigosPorNivelYCodigo');
    $resultado = array();
    foreach ($codigos as $codigo) {
        $nodo = $nodos[$codigo];
        // 'profundidad' = posición real en el árbol dibujado (raíz = 1),
        // a diferencia de 'nivel' (calculado a partir del propio código).
        // Casi siempre coinciden, pero pueden no hacerlo si un código
        // "salta" niveles en su numeración (por ejemplo, una secretaría
        // codificada como si fuera nivel 4). Para el color/despliegue
        // automático del raviol se usa la profundidad real, así el nodo
        // se ve y se comporta según dónde cuelga de verdad, no según cómo
        // esté numerado su código.
        $nodo['profundidad'] = $profundidad;
        $hijosCodigos = isset($hijosDe[$codigo]) ? $hijosDe[$codigo] : array();
        $nodo['hijos'] = armarRamas($hijosCodigos, $nodos, $hijosDe, $profundidad + 1);
        $resultado[] = $nodo;
    }
    return $resultado;
}

/**
 * Construye el árbol jerárquico a partir del listado plano de dependencias.
 * Devuelve un arreglo con los nodos raíz; cada nodo tiene 'hijos' con sus
 * descendientes directos, ordenados por nivel jerárquico y código. Si el
 * ancestro directo de un código no está en la lista (nivel intermedio sin
 * personal activo, por ejemplo), el nodo se cuelga del ancestro visible
 * más cercano en vez de quedar como raíz.
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
            // Si FILTRAR_SOLO_ACTIVAS está desactivado no hay dato de
            // actividad: se trata como activa para no marcar nada.
            'activa'      => isset($dep['activa']) ? $dep['activa'] : true,
        );
    }

    $indice = indiceDeDependencias($dependencias);
    $hijosDe = array();
    $raicesCodigos = array();
    foreach ($nodos as $codigo => $datos) {
        $ancestro = ancestroVisible($codigo, $indice);
        if ($ancestro !== null) {
            if (!isset($hijosDe[$ancestro])) {
                $hijosDe[$ancestro] = array();
            }
            $hijosDe[$ancestro][] = $codigo;
        } else {
            $raicesCodigos[] = $codigo;
        }
    }

    return armarRamas($raicesCodigos, $nodos, $hijosDe, 1);
}

/**
 * Devuelve las dependencias hijas "directas" de $codigo, saltando los
 * niveles intermedios ausentes (ver ancestroVisible()); usa el mismo
 * criterio que construirArbol() para que el listado de la ficha de
 * detalle coincida con lo que se ve en el organigrama.
 */
function obtenerHijosDirectos($todasLasDependencias, $codigo, $indice = null)
{
    if ($indice === null) {
        $indice = indiceDeDependencias($todasLasDependencias);
    }
    $hijos = array();
    foreach ($todasLasDependencias as $d) {
        if (ancestroVisible($d['codigo_dependencia'], $indice) === $codigo) {
            $hijos[] = $d;
        }
    }
    usort($hijos, 'compararDependenciasPorNivelYCodigo');
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
    // Nodo puramente estructural (sin personal activo directo, mostrado
    // solo porque algún descendiente sí tiene): se marca con una clase
    // aparte para distinguirlo visualmente de las unidades con personal.
    $claseSinPersonal = empty($nodo['activa']) ? ' sin-personal' : '';
    printf(
        '<span class="nodo nivel-%d%s">' .
            '<a class="nodo-cuerpo" href="detalle.php?codigo=%s" title="Ver detalle">' .
                '<span class="codigo">%s</span><span class="descripcion">%s</span>' .
            '</a>' .
            '<a class="nodo-boton" href="%s" target="_blank" rel="noopener" title="Ver información relacionada">Ver ficha &rarr;</a>' .
        '</span>',
        (int) $nodo['profundidad'],
        $claseSinPersonal,
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
            $abierto = $nodo['profundidad'] <= NIVELES_EXPANDIDOS_POR_DEFECTO ? ' open' : '';
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
