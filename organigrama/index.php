<?php
declare(strict_types=1);

require_once __DIR__ . '/functions.php';

try {
    $pdo = obtenerConexion();
    $dependencias = obtenerDependencias($pdo);
    $arbol = construirArbol($dependencias);
    $error = null;
} catch (PDOException $e) {
    $arbol = [];
    $error = 'No se pudo conectar a la base de datos. Verifique config.php.';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Organigrama de la Empresa</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="cabecera">
    <h1>Organigrama</h1>
    <p>Haga clic en una dependencia para ver su detalle.</p>
</header>

<main>
<?php if ($error): ?>
    <p class="mensaje-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
<?php elseif (empty($arbol)): ?>
    <p class="mensaje-info">No hay dependencias cargadas todavía.</p>
<?php else: ?>
    <div class="contenedor-organigrama">
        <?php renderizarArbol($arbol); ?>
    </div>
<?php endif; ?>
</main>
</body>
</html>
