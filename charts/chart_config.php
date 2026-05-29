<?php
/**
 * chart_config.php
 * Pantalla de configuración de gráficos.
 * Uso: chart_config.php?csv=/ruta/al/archivo.csv
 */

// ── Validación del parámetro CSV ──────────────────────────────────────────────
$csvPath = isset($_GET['csv']) ? $_GET['csv'] : '';

if (empty($csvPath)) {
    die('<p style="color:red">Error: parámetro <code>csv</code> no especificado.</p>');
}

// Bloquear path traversal básico
$csvPath = realpath($csvPath);
if ($csvPath === false || !file_exists($csvPath) || !is_readable($csvPath)) {
    die('<p style="color:red">Error: el archivo CSV no existe o no es accesible.</p>');
}

// ── Lectura del CSV ───────────────────────────────────────────────────────────
function readCsvPreview($path, $previewRows = 3) {
    $result = ['headers' => [], 'rows' => []];
    $handle = fopen($path, 'r');
    if (!$handle) return $result;

    // Detectar delimitador (coma o punto y coma)
    $firstLine = fgets($handle);
    rewind($handle);
    $delimiter = (substr_count($firstLine, ';') > substr_count($firstLine, ',')) ? ';' : ',';

    $rowNum = 0;
    while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
        if ($rowNum === 0) {
            $result['headers'] = array_map('trim', $row);
        } else {
            $result['rows'][] = array_map('trim', $row);
            if ($rowNum >= $previewRows) break;
        }
        $rowNum++;
    }
    fclose($handle);
    return $result;
}

$preview = readCsvPreview($csvPath);
$headers = $preview['headers'];
$rows    = $preview['rows'];

if (empty($headers)) {
    die('<p style="color:red">Error: el archivo CSV está vacío o no tiene encabezados.</p>');
}

$csvEncoded = htmlspecialchars($csvPath, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Configurar Gráfico</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: Arial, sans-serif; background: #f4f6f9; color: #333; padding: 24px; }
  h1  { font-size: 1.4rem; margin-bottom: 6px; color: #2c3e50; }
  .subtitle { font-size: 0.85rem; color: #666; margin-bottom: 24px; }

  .card {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 1px 4px rgba(0,0,0,.12);
    padding: 20px;
    margin-bottom: 20px;
  }
  .card h2 { font-size: 1rem; margin-bottom: 14px; color: #34495e; border-bottom: 1px solid #eee; padding-bottom: 8px; }

  /* Preview table */
  .preview-wrap { overflow-x: auto; }
  table { border-collapse: collapse; width: 100%; font-size: 0.82rem; }
  th { background: #2c3e50; color: #fff; padding: 7px 10px; text-align: left; white-space: nowrap; }
  td { padding: 6px 10px; border-bottom: 1px solid #eee; white-space: nowrap; }
  tr:last-child td { border-bottom: none; }
  .more-rows { font-size: 0.78rem; color: #888; margin-top: 6px; }

  /* Configuración */
  .config-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
  }
  @media (max-width: 600px) { .config-grid { grid-template-columns: 1fr; } }

  .field label { display: block; font-size: 0.82rem; font-weight: bold; margin-bottom: 4px; color: #555; }
  .field select, .field input {
    width: 100%;
    padding: 8px 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 0.88rem;
    background: #fafafa;
  }
  .field select:focus, .field input:focus { outline: none; border-color: #3498db; background: #fff; }
  .field .hint { font-size: 0.75rem; color: #888; margin-top: 3px; }

  /* Sección dinámica por tipo de gráfico */
  #sec-count, #sec-xy { display: none; }

  /* Botones */
  .btn-row { display: flex; gap: 10px; flex-wrap: wrap; }
  .btn {
    padding: 10px 22px;
    border: none;
    border-radius: 6px;
    font-size: 0.9rem;
    cursor: pointer;
    font-weight: bold;
  }
  .btn-primary { background: #2980b9; color: #fff; }
  .btn-primary:hover { background: #2471a3; }
  .btn-secondary { background: #ecf0f1; color: #555; }
  .btn-secondary:hover { background: #dde1e4; }
</style>
</head>
<body>

<h1>Configurar Gráfico</h1>
<p class="subtitle">Archivo: <code><?= $csvEncoded ?></code></p>

<!-- Vista previa -->
<div class="card">
  <h2>Vista previa de datos</h2>
  <div class="preview-wrap">
    <table>
      <thead>
        <tr>
          <?php foreach ($headers as $h): ?>
            <th><?= htmlspecialchars($h) ?></th>
          <?php endforeach; ?>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $row): ?>
          <tr>
            <?php foreach ($row as $cell): ?>
              <td><?= htmlspecialchars($cell) ?></td>
            <?php endforeach; ?>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <p class="more-rows">(Mostrando primeras <?= count($rows) ?> filas de datos)</p>
</div>

<!-- Configuración -->
<div class="card">
  <h2>Configuración del Gráfico</h2>
  <form action="chart_render.php" method="GET" id="chartForm">
    <input type="hidden" name="csv" value="<?= $csvEncoded ?>">

    <div class="config-grid" style="margin-bottom:16px">

      <!-- Tipo de gráfico -->
      <div class="field">
        <label for="chart_type">Tipo de gráfico</label>
        <select name="chart_type" id="chart_type" onchange="updateSections()">
          <optgroup label="Distribución (1 columna)">
            <option value="pie">Torta — distribución de categorías</option>
            <option value="doughnut">Dona — distribución de categorías</option>
            <option value="bar_count">Barras — conteo por categoría</option>
          </optgroup>
          <optgroup label="Evolución temporal (1 columna fecha)">
            <option value="line_time">Línea — altas por período</option>
            <option value="bar_time">Barras — altas por período</option>
          </optgroup>
          <optgroup label="Comparación (X vs Y)">
            <option value="bar_xy">Barras — columna X vs columna Y</option>
            <option value="line_xy">Línea — columna X vs columna Y</option>
          </optgroup>
        </select>
      </div>

      <!-- Título del gráfico -->
      <div class="field">
        <label for="chart_title">Título del gráfico</label>
        <input type="text" name="chart_title" id="chart_title" placeholder="Ej: Distribución por tipo de contratación">
      </div>
    </div>

    <!-- Sección: gráficos de conteo (1 columna) -->
    <div id="sec-count" class="config-grid" style="margin-bottom:16px">
      <div class="field">
        <label for="col_category">Columna de categorías</label>
        <select name="col_category" id="col_category">
          <option value="">— Seleccionar —</option>
          <?php foreach ($headers as $i => $h): ?>
            <option value="<?= $i ?>"><?= htmlspecialchars($h) ?></option>
          <?php endforeach; ?>
        </select>
        <p class="hint">Ej: "Tipo de contratación", "Departamento", "Sexo"</p>
      </div>
    </div>

    <!-- Sección: evolución temporal -->
    <div id="sec-time" class="config-grid" style="margin-bottom:16px; display:none">
      <div class="field">
        <label for="col_date">Columna de fecha</label>
        <select name="col_date" id="col_date">
          <option value="">— Seleccionar —</option>
          <?php foreach ($headers as $i => $h): ?>
            <option value="<?= $i ?>"><?= htmlspecialchars($h) ?></option>
          <?php endforeach; ?>
        </select>
        <p class="hint">Ej: "Fecha de ingreso", "Fecha de alta"</p>
      </div>
      <div class="field">
        <label for="date_group">Agrupar por</label>
        <select name="date_group" id="date_group">
          <option value="month">Mes y año (ene-2024)</option>
          <option value="year">Año</option>
          <option value="quarter">Trimestre</option>
        </select>
      </div>
    </div>

    <!-- Sección: X vs Y -->
    <div id="sec-xy" class="config-grid" style="margin-bottom:16px">
      <div class="field">
        <label for="col_x">Columna eje X (categorías)</label>
        <select name="col_x" id="col_x">
          <option value="">— Seleccionar —</option>
          <?php foreach ($headers as $i => $h): ?>
            <option value="<?= $i ?>"><?= htmlspecialchars($h) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field">
        <label for="col_y">Columna eje Y (valores numéricos)</label>
        <select name="col_y" id="col_y">
          <option value="">— Seleccionar —</option>
          <?php foreach ($headers as $i => $h): ?>
            <option value="<?= $i ?>"><?= htmlspecialchars($h) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div class="btn-row">
      <button type="submit" class="btn btn-primary">Generar Gráfico</button>
      <button type="button" class="btn btn-secondary" onclick="history.back()">Volver</button>
    </div>
  </form>
</div>

<script>
function updateSections() {
  const type = document.getElementById('chart_type').value;
  const isCount = ['pie','doughnut','bar_count'].includes(type);
  const isTime  = ['line_time','bar_time'].includes(type);
  const isXY    = ['bar_xy','line_xy'].includes(type);

  document.getElementById('sec-count').style.display = isCount ? 'grid' : 'none';
  document.getElementById('sec-time').style.display  = isTime  ? 'grid' : 'none';
  document.getElementById('sec-xy').style.display    = isXY    ? 'grid' : 'none';
}
// Inicializar al cargar
updateSections();

// Validación antes de enviar
document.getElementById('chartForm').addEventListener('submit', function(e) {
  const type = document.getElementById('chart_type').value;
  const isCount = ['pie','doughnut','bar_count'].includes(type);
  const isTime  = ['line_time','bar_time'].includes(type);
  const isXY    = ['bar_xy','line_xy'].includes(type);

  if (isCount && !document.getElementById('col_category').value) {
    e.preventDefault(); alert('Por favor seleccioná la columna de categorías.');
  } else if (isTime && !document.getElementById('col_date').value) {
    e.preventDefault(); alert('Por favor seleccioná la columna de fecha.');
  } else if (isXY && (!document.getElementById('col_x').value || !document.getElementById('col_y').value)) {
    e.preventDefault(); alert('Por favor seleccioná ambas columnas (X e Y).');
  }
});
</script>

</body>
</html>
