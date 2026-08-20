<?php
declare(strict_types=1);

require_once __DIR__ . '/functions.php';

$codigo = isset($_GET['codigo']) ? strtoupper(trim((string) $_GET['codigo'])) : '';

if (!validarCodigo($codigo)) {
    http_response_code(400);
    $error = 'El código de dependencia indicado no tiene un formato válido.';
} else {
    try {
        $pdo = obtenerConexion();
        $dependencia = obtenerDependenciaPorCodigo($pdo, $codigo);
        if (!$dependencia) {
            http_response_code(404);
            $error = 'No se encontró ninguna dependencia con el código ' . htmlspecialchars($codigo, ENT_QUOTES, 'UTF-8') . '.';
        } else {
            $error = null;
            $nivel = nivelDeCodigo($codigo);
            $codigoPadre = codigoPadre($codigo);
            $padre = $codigoPadre !== null ? obtenerDependenciaPorCodigo($pdo, $codigoPadre) : null;
            $todas = obtenerDependencias($pdo);
            $hijos = obtenerHijosDirectos($todas, $codigo);
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
<title>Detalle de Dependencia<?= isset($dependencia) ? ' - ' . htmlspecialchars($dependencia['descripcion'], ENT_QUOTES, 'UTF-8') : '' ?></title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="cabecera">
    <h1>Detalle de Dependencia</h1>
    <p><a class="volver" href="index.php">&larr; Volver al organigrama</a></p>
</header>

<main class="detalle">
<?php if ($error): ?>
    <p class="mensaje-error"><?= $error ?></p>
<?php else: ?>
    <section class="ficha">
        <h2><?= htmlspecialchars($dependencia['descripcion'], ENT_QUOTES, 'UTF-8') ?></h2>
        <dl>
            <dt>Código de dependencia</dt>
            <dd class="codigo"><?= htmlspecialchars($dependencia['codigo_dependencia'], ENT_QUOTES, 'UTF-8') ?></dd>

            <dt>Nivel jerárquico</dt>
            <dd><?= (int) $nivel ?></dd>

            <dt>Depende de</dt>
            <dd>
                <?php if ($padre): ?>
                    <a href="detalle.php?codigo=<?= urlencode($padre['codigo_dependencia']) ?>">
                        <span class="codigo"><?= htmlspecialchars($padre['codigo_dependencia'], ENT_QUOTES, 'UTF-8') ?></span>
                        &mdash; <?= htmlspecialchars($padre['descripcion'], ENT_QUOTES, 'UTF-8') ?>
                    </a>
                <?php elseif ($codigoPadre !== null): ?>
                    <em>Código superior <?= htmlspecialchars($codigoPadre, ENT_QUOTES, 'UTF-8') ?> no está cargado en la tabla.</em>
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
                        <a href="detalle.php?codigo=<?= urlencode($hijo['codigo_dependencia']) ?>">
                            <span class="codigo"><?= htmlspecialchars($hijo['codigo_dependencia'], ENT_QUOTES, 'UTF-8') ?></span>
                            <span class="descripcion"><?= htmlspecialchars($hijo['descripcion'], ENT_QUOTES, 'UTF-8') ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>
<?php endif; ?>
</main>
</body>
</html>
