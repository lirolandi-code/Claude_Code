<?php
/**
 * chart_render.php - Compatible con PHP 5.1+
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
            $val = str_replace(
                array('\\',    '"',    "\n",   "\r",   "\t"),
                array('\\\\', '\\"', '\\n',  '\\r',  '\\t'),
                $val
            );
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

// ── Parámetros ────────────────────────────────────────────────────────────────
$csvPath    = isset($_GET['csv'])          ? $_GET['csv']          : '';
$chartType  = isset($_GET['chart_type'])   ? $_GET['chart_type']   : '';
$chartTitle = isset($_GET['chart_title'])  ? trim($_GET['chart_title']) : 'Grafico';
$colCat     = isset($_GET['col_category']) ? (int)$_GET['col_category'] : -1;
$colDate    = isset($_GET['col_date'])     ? (int)$_GET['col_date']     : -1;
$dateGroup  = isset($_GET['date_group'])   ? $_GET['date_group']        : 'month';
$colX       = isset($_GET['col_x'])        ? (int)$_GET['col_x']        : -1;
$colY       = isset($_GET['col_y'])        ? (int)$_GET['col_y']        : -1;

$validTypes = array('pie','doughnut','bar_count','line_time','bar_time','bar_xy','line_xy');
if (empty($csvPath) || !in_array($chartType, $validTypes)) {
    die('<p style="color:red">Parametros invalidos. <a href="javascript:history.back()">Volver</a></p>');
}

$csvPath = realpath($csvPath);
if ($csvPath === false || !file_exists($csvPath) || !is_readable($csvPath)) {
    die('<p style="color:red">Archivo CSV no encontrado. <a href="javascript:history.back()">Volver</a></p>');
}

// ── Lectura completa del CSV ──────────────────────────────────────────────────
function readFullCsv($path) {
    $result = array('headers' => array(), 'rows' => array());
    $handle = fopen($path, 'r');
    if (!$handle) return $result;

    $firstLine = fgets($handle);
    rewind($handle);
    $delimiter = (substr_count($firstLine, ';') > substr_count($firstLine, ',')) ? ';' : ',';

    $rowNum = 0;
    while (($row = fgetcsv($handle, 4096, $delimiter)) !== false) {
        if ($rowNum === 0) {
            foreach ($row as $cell) $result['headers'][] = trim($cell);
        } else {
            $cleanRow = array();
            foreach ($row as $cell) $cleanRow[] = trim($cell);
            $result['rows'][] = $cleanRow;
        }
        $rowNum++;
    }
    fclose($handle);
    return $result;
}

$data    = readFullCsv($csvPath);
$headers = $data['headers'];
$rows    = $data['rows'];
$total   = count($rows);

// ── Paleta de colores ─────────────────────────────────────────────────────────
function palette($n) {
    $colors = array(
        'rgba(52,152,219,.85)',  'rgba(46,204,113,.85)',  'rgba(231,76,60,.85)',
        'rgba(155,89,182,.85)', 'rgba(241,196,15,.85)',  'rgba(230,126,34,.85)',
        'rgba(26,188,156,.85)', 'rgba(52,73,94,.85)',    'rgba(149,165,166,.85)',
        'rgba(192,57,43,.85)',  'rgba(39,174,96,.85)',   'rgba(41,128,185,.85)',
    );
    $out = array();
    for ($i = 0; $i < $n; $i++) $out[] = $colors[$i % count($colors)];
    return $out;
}

function solidPalette($n) {
    $colors = array(
        'rgba(52,152,219,1)',  'rgba(46,204,113,1)',  'rgba(231,76,60,1)',
        'rgba(155,89,182,1)', 'rgba(241,196,15,1)',  'rgba(230,126,34,1)',
        'rgba(26,188,156,1)', 'rgba(52,73,94,1)',    'rgba(149,165,166,1)',
        'rgba(192,57,43,1)',  'rgba(39,174,96,1)',   'rgba(41,128,185,1)',
    );
    $out = array();
    for ($i = 0; $i < $n; $i++) $out[] = $colors[$i % count($colors)];
    return $out;
}

// ── Parseo de fecha (sin DateTime::createFromFormat) ─────────────────────────
function parseFecha($raw) {
    $raw = trim($raw);
    if (empty($raw)) return false;

    // Formato dd/mm/YYYY o dd/mm/YYYY HH:MM:SS
    if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})/', $raw, $m)) {
        return mktime(0, 0, 0, (int)$m[2], (int)$m[1], (int)$m[3]);
    }
    // Formato YYYY-mm-dd
    if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})/', $raw, $m)) {
        return mktime(0, 0, 0, (int)$m[2], (int)$m[3], (int)$m[1]);
    }
    // Formato dd-mm-YYYY
    if (preg_match('/^(\d{1,2})-(\d{1,2})-(\d{4})/', $raw, $m)) {
        return mktime(0, 0, 0, (int)$m[2], (int)$m[1], (int)$m[3]);
    }
    // Intentar strtotime como ultimo recurso
    $ts = strtotime($raw);
    return ($ts !== false) ? $ts : false;
}

// ── Procesamiento ─────────────────────────────────────────────────────────────
$chartLabels   = array();
$chartDatasets = array();
$chartJsType   = 'bar';
$chartError    = '';
$showLegend    = false;

// Distribución por categoría
if ($chartType === 'pie' || $chartType === 'doughnut' || $chartType === 'bar_count') {
    if ($colCat < 0 || $colCat >= count($headers)) {
        $chartError = 'Columna de categorias invalida.';
    } else {
        $counts = array();
        foreach ($rows as $row) {
            $val = isset($row[$colCat]) ? $row[$colCat] : '';
            if ($val === '') $val = '(vacio)';
            if (isset($counts[$val])) {
                $counts[$val]++;
            } else {
                $counts[$val] = 1;
            }
        }
        arsort($counts);
        $chartLabels = array_keys($counts);
        $values      = array_values($counts);
        $colors      = palette(count($chartLabels));
        $solidColors = solidPalette(count($chartLabels));

        $chartJsType = ($chartType === 'pie') ? 'pie' : (($chartType === 'doughnut') ? 'doughnut' : 'bar');
        $showLegend  = ($chartType === 'pie' || $chartType === 'doughnut');

        $chartDatasets = array(array(
            'label'           => $headers[$colCat],
            'data'            => $values,
            'backgroundColor' => $colors,
            'borderColor'     => $solidColors,
            'borderWidth'     => 1,
        ));
    }
}

// Evolución temporal
if ($chartType === 'line_time' || $chartType === 'bar_time') {
    if ($colDate < 0 || $colDate >= count($headers)) {
        $chartError = 'Columna de fecha invalida.';
    } else {
        $counts      = array();
        $timestamps  = array(); // para ordenar
        $parseErrors = 0;

        foreach ($rows as $row) {
            $raw = isset($row[$colDate]) ? $row[$colDate] : '';
            if (empty($raw)) continue;
            $ts = parseFecha($raw);
            if ($ts === false) { $parseErrors++; continue; }

            $mes  = (int)date('m', $ts);
            $anio = (int)date('Y', $ts);

            if ($dateGroup === 'year') {
                $key      = date('Y', $ts);
                $sortable = $anio;
            } elseif ($dateGroup === 'quarter') {
                $q        = ceil($mes / 3);
                $key      = 'T' . $q . '-' . $anio;
                $sortable = $anio * 10 + $q;
            } else {
                $key      = date('m', $ts) . '/' . date('Y', $ts);
                $sortable = $anio * 100 + $mes;
            }

            if (isset($counts[$key])) {
                $counts[$key]++;
            } else {
                $counts[$key]      = 1;
                $timestamps[$key]  = $sortable;
            }
        }

        // Ordenar por timestamp
        $keys = array_keys($counts);
        for ($i = 0; $i < count($keys) - 1; $i++) {
            for ($j = $i + 1; $j < count($keys); $j++) {
                if ($timestamps[$keys[$i]] > $timestamps[$keys[$j]]) {
                    $tmp      = $keys[$i];
                    $keys[$i] = $keys[$j];
                    $keys[$j] = $tmp;
                }
            }
        }

        $chartLabels = array();
        $values      = array();
        foreach ($keys as $k) {
            $chartLabels[] = $k;
            $values[]      = $counts[$k];
        }

        $chartJsType = ($chartType === 'line_time') ? 'line' : 'bar';

        $chartDatasets = array(array(
            'label'           => 'Cantidad',
            'data'            => $values,
            'backgroundColor' => 'rgba(52,152,219,.85)',
            'borderColor'     => 'rgba(41,128,185,1)',
            'borderWidth'     => 2,
            'tension'         => 0.3,
            'fill'            => false,
        ));

        if ($parseErrors > 0) {
            $chartError = 'Nota: ' . $parseErrors . ' filas no pudieron parsearse como fecha y fueron ignoradas.';
        }
    }
}

// X vs Y
if ($chartType === 'bar_xy' || $chartType === 'line_xy') {
    if ($colX < 0 || $colX >= count($headers) || $colY < 0 || $colY >= count($headers)) {
        $chartError = 'Columnas X o Y invalidas.';
    } else {
        $grouped = array();
        foreach ($rows as $row) {
            $xVal = isset($row[$colX]) ? $row[$colX] : '(vacio)';
            $yVal = isset($row[$colY]) ? $row[$colY] : '0';
            $yVal = preg_replace('/[^0-9.\-]/', '', $yVal);
            $yNum = (float)$yVal;
            if (isset($grouped[$xVal])) {
                $grouped[$xVal] += $yNum;
            } else {
                $grouped[$xVal] = $yNum;
            }
        }
        arsort($grouped);
        $chartLabels = array_keys($grouped);
        $values      = array_values($grouped);
        $colors      = palette(count($chartLabels));
        $chartJsType = ($chartType === 'line_xy') ? 'line' : 'bar';

        $chartDatasets = array(array(
            'label'           => $headers[$colY],
            'data'            => $values,
            'backgroundColor' => $colors,
            'borderColor'     => 'rgba(41,128,185,1)',
            'borderWidth'     => 2,
            'tension'         => 0.3,
        ));
    }
}

$labelsJson   = json_encode($chartLabels);
$datasetsJson = json_encode($chartDatasets);
$titleSafe    = htmlspecialchars($chartTitle, ENT_QUOTES, 'UTF-8');
$configBack   = 'chart_config.php?csv=' . urlencode($csvPath);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo $titleSafe; ?></title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: Arial, sans-serif; background: #f4f6f9; color: #333; padding: 24px; }
  h1  { font-size: 1.4em; margin-bottom: 4px; color: #2c3e50; }
  .subtitle { font-size: 0.82em; color: #777; margin-bottom: 20px; }

  .card {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 6px;
    padding: 20px;
    margin-bottom: 20px;
    max-width: 960px;
  }

  .error-box {
    background: #fef9e7;
    border-left: 4px solid #f39c12;
    padding: 10px 14px;
    border-radius: 4px;
    font-size: 0.85em;
    margin-bottom: 14px;
  }

  .btn {
    padding: 9px 20px;
    border: none;
    border-radius: 5px;
    font-size: 0.88em;
    cursor: pointer;
    font-weight: bold;
    margin-right: 8px;
    text-decoration: none;
    display: inline-block;
  }
  .btn-green  { background: #27ae60; color: #fff; }
  .btn-blue   { background: #2980b9; color: #fff; }
  .btn-light  { background: #ecf0f1; color: #555; }
</style>
</head>
<body>

<h1><?php echo $titleSafe; ?></h1>
<p class="subtitle">
  Total de registros: <strong><?php echo number_format($total); ?></strong> &mdash;
  Archivo: <code><?php echo htmlspecialchars($csvPath); ?></code>
</p>

<?php if (!empty($chartError)): ?>
<div class="error-box"><?php echo htmlspecialchars($chartError); ?></div>
<?php endif; ?>

<div class="card">
  <canvas id="mainChart"></canvas>
  <br>
  <button class="btn btn-green" onclick="downloadChart()">Descargar imagen (PNG)</button>
  <a class="btn btn-blue" href="<?php echo $configBack; ?>">Cambiar configuraci&oacute;n</a>
  <button class="btn btn-light" onclick="history.back()">Volver</button>
</div>

<script type="text/javascript" src="chart.min.js"></script>
<script type="text/javascript">
var ctx        = document.getElementById('mainChart').getContext('2d');
var labels     = <?php echo $labelsJson; ?>;
var datasets   = <?php echo $datasetsJson; ?>;
var chartType  = <?php echo json_encode($chartJsType); ?>;
var showLegend = <?php echo $showLegend ? 'true' : 'false'; ?>;
var chartTitle = <?php echo json_encode($chartTitle); ?>;

var scalesConfig = {};
if (chartType !== 'pie' && chartType !== 'doughnut') {
  scalesConfig = {
    y: { beginAtZero: true, ticks: { precision: 0 } },
    x: { ticks: { maxRotation: 45 } }
  };
}

var chart = new Chart(ctx, {
  type: chartType,
  data: { labels: labels, datasets: datasets },
  options: {
    responsive: true,
    plugins: {
      legend: {
        display: showLegend,
        position: 'right'
      },
      title: {
        display: true,
        text: chartTitle,
        font: { size: 16 },
        padding: { bottom: 16 }
      },
      tooltip: {
        callbacks: {
          label: function(context) {
            var val = context.parsed.y !== undefined ? context.parsed.y : context.parsed;
            if (chartType === 'pie' || chartType === 'doughnut') {
              var total = 0;
              for (var i = 0; i < context.dataset.data.length; i++) total += context.dataset.data[i];
              var pct = ((val / total) * 100).toFixed(1);
              return ' ' + context.label + ': ' + val + ' (' + pct + '%)';
            }
            return ' ' + val;
          }
        }
      }
    },
    scales: scalesConfig
  }
});

function downloadChart() {
  var link = document.createElement('a');
  link.download = 'grafico.png';
  link.href = document.getElementById('mainChart').toDataURL('image/png');
  link.click();
}
</script>

</body>
</html>
