<?php
require_once dirname(__FILE__) . '/functions.php';

try {
    $pdo = obtenerConexion();
    $dependencias = obtenerDependencias($pdo);
    $arbol = construirArbol($dependencias);
    $error = null;
} catch (PDOException $e) {
    $arbol = array();
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
    <p class="mensaje-error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
<?php elseif (empty($arbol)): ?>
    <p class="mensaje-info">No hay dependencias cargadas todavía.</p>
<?php else: ?>
    <div class="zoom-controles">
        <button type="button" id="zoom-ajustar" title="Achicar hasta que entre todo el organigrama desplegado">Ver todo</button>
        <button type="button" id="zoom-menos" title="Alejar" aria-label="Alejar">&minus;</button>
        <span id="zoom-nivel">100%</span>
        <button type="button" id="zoom-mas" title="Acercar" aria-label="Acercar">+</button>
        <button type="button" id="zoom-reset" title="Volver al tamaño normal">100%</button>
    </div>
    <div class="contenedor-organigrama" id="contenedor-organigrama">
        <?php renderizarArbol($arbol); ?>
    </div>
    <script src="assets/organigrama.js"></script>
<?php endif; ?>
</main>
</body>
</html>
