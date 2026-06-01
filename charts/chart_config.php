<?php
/**
 * chart_config.php - Compatible con PHP 5.1+
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

// ── Polyfill json_encode para PHP < 5.2 ──────────────────────────────────────
if (!function_exists('json_encode')) {
    function json_encode($val) {
        if (is_null($val))    return 'null';
        if (is_bool($val))    return $val ? 'true' : 'false';
        if (is_int($val))     return (string)$val;
        if (is_float($val))   return (string)$val;
        if (is_string($val)) {
            $val = str_replace(array('\\','"',"\n","\r","\t"), array('\\\\','\\"','\\n','\\r','\\t'), $val);
            return '"' . $val . '"';
        }
        if (is_array($val)) {
            $isAssoc = false;
            $i = 0;
            foreach ($val as $k => $v) {
                if ($k !== $i) { $isAssoc = true; break; }
                $i++;
            }
            if ($isAssoc) {
                $parts = array();
                foreach ($val as $k => $v) {
                    $parts[] = json_encode((string)$k) . ':' . json_encode($v);
                }
                return '{' . implode(',', $parts) . '}';
            } else {
                $parts = array();
                foreach ($val as $v) $parts[] = json_encode($v);
                return '[' . implode(',', $parts) . ']';
            }
        }
        return 'null';
    }
}

// ── Validación del parámetro CSV ──────────────────────────────────────────────
$csvPath = isset($_GET['csv']) ? $_GET['csv'] : '';

if (empty($csvPath)) {
    die('<p style="color:red">Error: par&aacute;metro <code>csv</code> no especificado.</p>');
}

$csvPath = realpath($csvPath);
if ($csvPath === false || !file_exists($csvPath) || !is_readable($csvPath)) {
    die('<p style="color:red">Error: el archivo CSV no existe o no es accesible. Ruta recibida: <code>' . htmlspecialchars($_GET['csv']) . '</code></p>');
}

// ── Lectura del CSV ───────────────────────────────────────────────────────────
function readCsvPreview($path, $previewRows) {
    $result = array('headers' => array(), 'rows' => array());
    $handle = fopen($path, 'r');
    if (!$handle) return $result;

    $firstLine = fgets($handle);
    rewind($handle);
    $delimiter = (substr_count($firstLine, ';') > substr_count($firstLine, ',')) ? ';' : ',';

    $rowNum = 0;
    while (($row = fgetcsv($handle, 4096, $delimiter)) !== false) {
        if ($rowNum === 0) {
            foreach ($row as $cell) {
                $result['headers'][] = trim($cell);
            }
        } else {
            $cleanRow = array();
            foreach ($row as $cell) $cleanRow[] = trim($cell);
            $result['rows'][] = $cleanRow;
            if ($rowNum >= $previewRows) break;
        }
        $rowNum++;
    }
    fclose($handle);
    return $result;
}

$preview = readCsvPreview($csvPath, 3);
$headers = $preview['headers'];
$rows    = $preview['rows'];

if (empty($headers)) {
    die('<p style="color:red">Error: el archivo CSV est&aacute; vac&iacute;o o no tiene encabezados.</p>');
}

$csvEncoded = htmlspecialchars($csvPath, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Configurar Gr&aacute;fico</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: Arial, sans-serif; background: #f4f6f9; color: #333; padding: 24px; }
  h1  { font-size: 1.4em; margin-bottom: 6px; color: #2c3e50; }
  .subtitle { font-size: 0.85em; color: #666; margin-bottom: 24px; }

  .card {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 6px;
    padding: 20px;
    margin-bottom: 20px;
  }
  .card h2 { font-size: 1em; margin-bottom: 14px; color: #34495e; border-bottom: 1px solid #eee; padding-bottom: 8px; }

  .preview-wrap { overflow-x: auto; }
  table { border-collapse: collapse; width: 100%; font-size: 0.82em; }
  th { background: #2c3e50; color: #fff; padding: 7px 10px; text-align: left; white-space: nowrap; }
  td { padding: 6px 10px; border-bottom: 1px solid #eee; white-space: nowrap; }
  .more-rows { font-size: 0.78em; color: #888; margin-top: 6px; }

  .config-row { margin-bottom: 14px; overflow: hidden; }
  .field-half { float: left; width: 48%; margin-right: 2%; }
  .field-half:last-child { margin-right: 0; }
  .field-full { width: 100%; }
  .clearfix { clear: both; }

  label { display: block; font-size: 0.82em; font-weight: bold; margin-bottom: 4px; color: #555; }
  select, input[type=text] {
    width: 100%;
    padding: 7px 9px;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 0.88em;
    background: #fafafa;
  }
  .hint { font-size: 0.75em; color: #888; margin-top: 3px; }

  #sec-count, #sec-time, #sec-xy { display: none; }

  .btn {
    padding: 9px 20px;
    border: none;
    border-radius: 5px;
    font-size: 0.9em;
    cursor: pointer;
    font-weight: bold;
    margin-right: 8px;
  }
  .btn-primary   { background: #2980b9; color: #fff; }
  .btn-secondary { background: #ecf0f1; color: #555; }
</style>
</head>
<body>

<h1>Configurar Gr&aacute;fico</h1>
<p class="subtitle">Archivo: <code><?php echo $csvEncoded; ?></code></p>

<div class="card">
  <h2>Vista previa de datos</h2>
  <div class="preview-wrap">
    <table>
      <thead>
        <tr>
          <?php foreach ($headers as $h): ?>
            <th><?php echo htmlspecialchars($h); ?></th>
          <?php endforeach; ?>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $row): ?>
          <tr>
            <?php foreach ($row as $cell): ?>
              <td><?php echo htmlspecialchars($cell); ?></td>
            <?php endforeach; ?>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <p class="more-rows">(Mostrando primeras <?php echo count($rows); ?> filas de datos)</p>
</div>

<div class="card">
  <h2>Configuraci&oacute;n del Gr&aacute;fico</h2>
  <form action="chart_render.php" method="GET" id="chartForm">
    <input type="hidden" name="csv" value="<?php echo $csvEncoded; ?>">

    <div class="config-row">
      <div class="field-half">
        <label for="chart_type">Tipo de gr&aacute;fico</label>
        <select name="chart_type" id="chart_type" onchange="updateSections()">
          <optgroup label="Distribuci&oacute;n (1 columna)">
            <option value="pie">Torta &mdash; distribuci&oacute;n de categor&iacute;as</option>
            <option value="doughnut">Dona &mdash; distribuci&oacute;n de categor&iacute;as</option>
            <option value="bar_count">Barras &mdash; conteo por categor&iacute;a</option>
          </optgroup>
          <optgroup label="Evoluci&oacute;n temporal (1 columna fecha)">
            <option value="line_time">L&iacute;nea &mdash; altas por per&iacute;odo</option>
            <option value="bar_time">Barras &mdash; altas por per&iacute;odo</option>
          </optgroup>
          <optgroup label="Comparaci&oacute;n (X vs Y)">
            <option value="bar_xy">Barras &mdash; columna X vs columna Y</option>
            <option value="line_xy">L&iacute;nea &mdash; columna X vs columna Y</option>
          </optgroup>
        </select>
      </div>
      <div class="field-half">
        <label for="chart_title">T&iacute;tulo del gr&aacute;fico</label>
        <input type="text" name="chart_title" id="chart_title" placeholder="Ej: Distribuci&oacute;n por tipo de contrataci&oacute;n">
      </div>
      <div class="clearfix"></div>
    </div>

    <!-- Distribucion por categoria -->
    <div id="sec-count" class="config-row">
      <div class="field-half">
        <label for="col_category">Columna de categor&iacute;as</label>
        <select name="col_category" id="col_category">
          <option value="">-- Seleccionar --</option>
          <?php foreach ($headers as $i => $h): ?>
            <option value="<?php echo $i; ?>"><?php echo htmlspecialchars($h); ?></option>
          <?php endforeach; ?>
        </select>
        <p class="hint">Ej: "Tipo de contrataci&oacute;n", "Departamento"</p>
      </div>
      <div class="clearfix"></div>
    </div>

    <!-- Evolucion temporal -->
    <div id="sec-time" class="config-row">
      <div class="field-half">
        <label for="col_date">Columna de fecha</label>
        <select name="col_date" id="col_date">
          <option value="">-- Seleccionar --</option>
          <?php foreach ($headers as $i => $h): ?>
            <option value="<?php echo $i; ?>"><?php echo htmlspecialchars($h); ?></option>
          <?php endforeach; ?>
        </select>
        <p class="hint">Ej: "Fecha de ingreso", "Fecha de alta"</p>
      </div>
      <div class="field-half">
        <label for="date_group">Agrupar por</label>
        <select name="date_group" id="date_group">
          <option value="month">Mes y a&ntilde;o</option>
          <option value="year">A&ntilde;o</option>
          <option value="quarter">Trimestre</option>
        </select>
      </div>
      <div class="clearfix"></div>
    </div>

    <!-- X vs Y -->
    <div id="sec-xy" class="config-row">
      <div class="field-half">
        <label for="col_x">Columna eje X (categor&iacute;as)</label>
        <select name="col_x" id="col_x">
          <option value="">-- Seleccionar --</option>
          <?php foreach ($headers as $i => $h): ?>
            <option value="<?php echo $i; ?>"><?php echo htmlspecialchars($h); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field-half">
        <label for="col_y">Columna eje Y (valores num&eacute;ricos)</label>
        <select name="col_y" id="col_y">
          <option value="">-- Seleccionar --</option>
          <?php foreach ($headers as $i => $h): ?>
            <option value="<?php echo $i; ?>"><?php echo htmlspecialchars($h); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="clearfix"></div>
    </div>

    <button type="submit" class="btn btn-primary">Generar Gr&aacute;fico</button>
    <button type="button" class="btn btn-secondary" onclick="history.back()">Volver</button>
  </form>
</div>

<script type="text/javascript">
function updateSections() {
  var type = document.getElementById('chart_type').value;
  var isCount = (type === 'pie' || type === 'doughnut' || type === 'bar_count');
  var isTime  = (type === 'line_time' || type === 'bar_time');
  var isXY    = (type === 'bar_xy' || type === 'line_xy');

  document.getElementById('sec-count').style.display = isCount ? 'block' : 'none';
  document.getElementById('sec-time').style.display  = isTime  ? 'block' : 'none';
  document.getElementById('sec-xy').style.display    = isXY    ? 'block' : 'none';
}
updateSections();

document.getElementById('chartForm').onsubmit = function() {
  var type = document.getElementById('chart_type').value;
  var isCount = (type === 'pie' || type === 'doughnut' || type === 'bar_count');
  var isTime  = (type === 'line_time' || type === 'bar_time');
  var isXY    = (type === 'bar_xy' || type === 'line_xy');

  if (isCount && !document.getElementById('col_category').value) {
    alert('Por favor selecciona la columna de categorias.');
    return false;
  }
  if (isTime && !document.getElementById('col_date').value) {
    alert('Por favor selecciona la columna de fecha.');
    return false;
  }
  if (isXY && (!document.getElementById('col_x').value || !document.getElementById('col_y').value)) {
    alert('Por favor selecciona ambas columnas (X e Y).');
    return false;
  }
  return true;
};
</script>

</body>
</html>
