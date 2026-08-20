<?php
require_once dirname(__FILE__) . '/functions.php';

$codigo = isset($_GET['codigo']) ? strtoupper(trim((string) $_GET['codigo'])) : '';

// http_response_code() recién existe desde PHP 5.4; para versiones
// anteriores hay que fijar el estado HTTP a mano con header().
if (!validarCodigo($codigo)) {
    header('HTTP/1.1 400 Bad Request');
    $error = 'El código de dependencia indicado no tiene un formato válido.';
} else {
    try {
        $pdo = obtenerConexion();
        $dependencia = obtenerDependenciaPorCodigo($pdo, $codigo);
        if (!$dependencia) {
            header('HTTP/1.1 404 Not Found');
            $error = 'No se encontró ninguna dependencia con el código ' . htmlspecialchars($codigo, ENT_QUOTES, 'UTF-8') . '.';
        } else {
            $error = null;
            $nivel = nivelDeCodigo($codigo);
            $todas = obtenerDependencias($pdo);
            $indice = indiceDeDependencias($todas);
            // Ancestro visible más cercano, no el padre estricto: si el
            // nivel inmediatamente superior no tiene personal activo (o
            // no está cargado), se salta hasta el que sí está visible.
            $codigoPadre = ancestroVisible($codigo, $indice);
            $padre = $codigoPadre !== null ? obtenerDependenciaPorCodigo($pdo, $codigoPadre) : null;
            $hijos = obtenerHijosDirectos($todas, $codigo, $indice);
        }
    } catch (PDOException $e) {
        $error = 'No se pudo conectar a la base de datos. Verifique config.php.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Detalle de Dependencia<?php echo isset($dependencia) ? ' - ' . htmlspecialchars($dependencia['descripcion'], ENT_QUOTES, 'UTF-8') : ''; ?></title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="cabecera">
    <h1>Detalle de Dependencia</h1>
    <p><a class="volver" href="index.php">&larr; Volver al organigrama</a></p>
</header>

<main class="detalle">
<?php if ($error): ?>
    <p class="mensaje-error"><?php echo $error; ?></p>
<?php else: ?>
    <section class="ficha">
        <h2><?php echo htmlspecialchars($dependencia['descripcion'], ENT_QUOTES, 'UTF-8'); ?></h2>
        <dl>
            <dt>Código de dependencia</dt>
            <dd class="codigo"><?php echo htmlspecialchars($dependencia['codigo_dependencia'], ENT_QUOTES, 'UTF-8'); ?></dd>

            <dt>Nivel jerárquico</dt>
            <dd><?php echo (int) $nivel; ?></dd>

            <dt>Depende de</dt>
            <dd>
                <?php if ($padre): ?>
                    <a href="detalle.php?codigo=<?php echo urlencode($padre['codigo_dependencia']); ?>">
                        <span class="codigo"><?php echo htmlspecialchars($padre['codigo_dependencia'], ENT_QUOTES, 'UTF-8'); ?></span>
                        &mdash; <?php echo htmlspecialchars($padre['descripcion'], ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                <?php elseif ($codigoPadre !== null): ?>
                    <em>Código superior <?php echo htmlspecialchars($codigoPadre, ENT_QUOTES, 'UTF-8'); ?> no está cargado en la tabla.</em>
                <?php elseif ($nivel > 1): ?>
                    <em>No hay ninguna dependencia superior visible con los filtros configurados.</em>
                <?php else: ?>
                    <em>Es la máxima autoridad (nivel 1).</em>
                <?php endif; ?>
            </dd>
        </dl>
    </section>

    <section class="hijos">
        <h3>Dependencias que reportan directamente a esta unidad</h3>
        <?php if (empty($hijos)): ?>
            <p class="mensaje-info">No tiene dependencias subordinadas cargadas.</p>
        <?php else: ?>
            <ul class="lista-hijos">
                <?php foreach ($hijos as $hijo): ?>
                    <li>
                        <a class="fila-hijo-cuerpo" href="detalle.php?codigo=<?php echo urlencode($hijo['codigo_dependencia']); ?>">
                            <span class="codigo"><?php echo htmlspecialchars($hijo['codigo_dependencia'], ENT_QUOTES, 'UTF-8'); ?></span>
                            <span class="descripcion"><?php echo htmlspecialchars($hijo['descripcion'], ENT_QUOTES, 'UTF-8'); ?></span>
                        </a>
                        <a class="fila-hijo-boton" href="<?php echo htmlspecialchars(urlDetalleExterna($hijo['codigo_dependencia']), ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener" title="Ver información relacionada">Ver ficha &rarr;</a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>
<?php endif; ?>
</main>
</body>
</html>
