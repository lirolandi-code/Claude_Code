<?php
/**
 * chart_render.php
 * Procesa el CSV según la configuración y renderiza el gráfico con Chart.js.
 */

// ── Parámetros ────────────────────────────────────────────────────────────────
$csvPath    = isset($_GET['csv'])         ? $_GET['csv']         : '';
$chartType  = isset($_GET['chart_type'])  ? $_GET['chart_type']  : '';
$chartTitle = isset($_GET['chart_title']) ? trim($_GET['chart_title']) : 'Gráfico';
$colCat     = isset($_GET['col_category'])? (int)$_GET['col_category'] : -1;
$colDate    = isset($_GET['col_date'])    ? (int)$_GET['col_date']     : -1;
$dateGroup  = isset($_GET['date_group'])  ? $_GET['date_group']        : 'month';
$colX       = isset($_GET['col_x'])       ? (int)$_GET['col_x']        : -1;
$colY       = isset($_GET['col_y'])       ? (int)$_GET['col_y']        : -1;

// Tipos válidos
$validTypes = ['pie','doughnut','bar_count','line_time','bar_time','bar_xy','line_xy'];
if (empty($csvPath) || !in_array($chartType, $validTypes)) {
    die('<p style="color:red">Parámetros inválidos. <a href="javascript:history.back()">Volver</a></p>');
}

$csvPath = realpath($csvPath);
if ($csvPath === false || !file_exists($csvPath) || !is_readable($csvPath)) {
    die('<p style="color:red">Archivo CSV no encontrado. <a href="javascript:history.back()">Volver</a></p>');
}

// ── Lectura del CSV ───────────────────────────────────────────────────────────
function readFullCsv($path) {
    $result = ['headers' => [], 'rows' => []];
    $handle = fopen($path, 'r');
    if (!$handle) return $result;

    $firstLine = fgets($handle);
    rewind($handle);
    $delimiter = (substr_count($firstLine, ';') > substr_count($firstLine, ',')) ? ';' : ',';

    $rowNum = 0;
    while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
        if ($rowNum === 0) {
            $result['headers'] = array_map('trim', $row);
        } else {
            $result['rows'][] = array_map('trim', $row);
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

// ── Procesamiento de datos según tipo ─────────────────────────────────────────

// Paleta de colores base
function palette($n) {
    $colors = [
        'rgba(52,152,219,.85)',  'rgba(46,204,113,.85)',  'rgba(231,76,60,.85)',
        'rgba(155,89,182,.85)', 'rgba(241,196,15,.85)',  'rgba(230,126,34,.85)',
        'rgba(26,188,156,.85)', 'rgba(52,73,94,.85)',    'rgba(149,165,166,.85)',
        'rgba(192,57,43,.85)',  'rgba(39,174,96,.85)',   'rgba(41,128,185,.85)',
    ];
    $out = [];
    for ($i = 0; $i < $n; $i++) $out[] = $colors[$i % count($colors)];
    return $out;
}

$chartLabels  = [];
$chartDatasets = [];
$chartJsType  = 'bar'; // Chart.js type
$chartError   = '';

// ── Distribución por categoría (pie / doughnut / bar_count) ──────────────────
if (in_array($chartType, ['pie','doughnut','bar_count'])) {
    if ($colCat < 0 || $colCat >= count($headers)) {
        $chartError = 'Columna de categorías inválida.';
    } else {
        $counts = [];
        foreach ($rows as $row) {
            $val = isset($row[$colCat]) ? $row[$colCat] : '(vacío)';
            if ($val === '') $val = '(vacío)';
            $counts[$val] = ($counts[$val] ?? 0) + 1;
        }
        arsort($counts);
        $chartLabels = array_keys($counts);
        $values      = array_values($counts);
        $colors      = palette(count($chartLabels));

        $chartJsType = ($chartType === 'pie') ? 'pie'
                     : (($chartType === 'doughnut') ? 'doughnut' : 'bar');

        $chartDatasets = [[
            'label'           => $headers[$colCat],
            'data'            => $values,
            'backgroundColor' => $colors,
            'borderColor'     => array_map(fn($c) => str_replace('.85)', '1)', $c), $colors),
            'borderWidth'     => 1,
        ]];
    }
}

// ── Evolución temporal ────────────────────────────────────────────────────────
if (in_array($chartType, ['line_time','bar_time'])) {
    if ($colDate < 0 || $colDate >= count($headers)) {
        $chartError = 'Columna de fecha inválida.';
    } else {
        $counts = [];
        $parseErrors = 0;
        foreach ($rows as $row) {
            $raw = isset($row[$colDate]) ? trim($row[$colDate]) : '';
            if (empty($raw)) continue;

            // Intentar parsear múltiples formatos de fecha
            $ts = false;
            $formats = ['d/m/Y','Y-m-d','d-m-Y','m/d/Y','d/m/Y H:i:s','Y-m-d H:i:s'];
            foreach ($formats as $fmt) {
                $dt = DateTime::createFromFormat($fmt, $raw);
                if ($dt !== false) { $ts = $dt; break; }
            }
            if ($ts === false) {
                $ts2 = strtotime($raw);
                if ($ts2 !== false) $ts = new DateTime('@' . $ts2);
            }
            if ($ts === false) { $parseErrors++; continue; }

            switch ($dateGroup) {
                case 'year':    $key = $ts->format('Y');          break;
                case 'quarter': $key = 'T' . ceil((int)$ts->format('m') / 3) . '-' . $ts->format('Y'); break;
                default:        $key = $ts->format('m/Y');        break;
            }
            $counts[$key] = ($counts[$key] ?? 0) + 1;
        }

        // Ordenar cronológicamente
        uksort($counts, function($a, $b) use ($dateGroup) {
            if ($dateGroup === 'year') return strcmp($a, $b);
            if ($dateGroup === 'quarter') {
                // "T1-2023" → sortable
                preg_match('/T(\d)-(\d{4})/', $a, $ma);
                preg_match('/T(\d)-(\d{4})/', $b, $mb);
                $va = ($ma[2] ?? 0) * 10 + ($ma[1] ?? 0);
                $vb = ($mb[2] ?? 0) * 10 + ($mb[1] ?? 0);
                return $va - $vb;
            }
            // mm/YYYY
            [$ma, $ya] = explode('/', $a) + [0,0];
            [$mb, $yb] = explode('/', $b) + [0,0];
            return ($ya * 12 + $ma) - ($yb * 12 + $mb);
        });

        $chartLabels = array_keys($counts);
        $values      = array_values($counts);
        $color       = 'rgba(52,152,219,.85)';
        $chartJsType = ($chartType === 'line_time') ? 'line' : 'bar';

        $chartDatasets = [[
            'label'           => 'Cantidad',
            'data'            => $values,
            'backgroundColor' => $color,
            'borderColor'     => 'rgba(41,128,185,1)',
            'borderWidth'     => 2,
            'tension'         => 0.3,
            'fill'            => false,
        ]];

        if ($parseErrors > 0) {
            $chartError = "Nota: $parseErrors filas no pudieron parsearse como fecha y fueron ignoradas.";
        }
    }
}

// ── X vs Y ───────────────────────────────────────────────────────────────────
if (in_array($chartType, ['bar_xy','line_xy'])) {
    if ($colX < 0 || $colX >= count($headers) || $colY < 0 || $colY >= count($headers)) {
        $chartError = 'Columnas X o Y inválidas.';
    } else {
        // Agrupar: sumar Y por cada valor de X
        $grouped = [];
        foreach ($rows as $row) {
            $xVal = isset($row[$colX]) ? $row[$colX] : '(vacío)';
            $yVal = isset($row[$colY]) ? $row[$colY] : '0';
            $yNum = (float) preg_replace('/[^\d.\-]/', '', $yVal);
            $grouped[$xVal] = ($grouped[$xVal] ?? 0) + $yNum;
        }
        arsort($grouped);

        $chartLabels = array_keys($grouped);
        $values      = array_values($grouped);
        $colors      = palette(count($chartLabels));
        $chartJsType = ($chartType === 'line_xy') ? 'line' : 'bar';

        $chartDatasets = [[
            'label'           => $headers[$colY],
            'data'            => $values,
            'backgroundColor' => $colors,
            'borderColor'     => 'rgba(41,128,185,1)',
            'borderWidth'     => 2,
            'tension'         => 0.3,
        ]];
    }
}

$labelsJson   = json_encode($chartLabels, JSON_UNESCAPED_UNICODE);
$datasetsJson = json_encode($chartDatasets, JSON_UNESCAPED_UNICODE);
$titleSafe    = htmlspecialchars($chartTitle, ENT_QUOTES, 'UTF-8');
$configBack   = 'chart_config.php?csv=' . urlencode($csvPath);

// Opciones adicionales para leyenda
$showLegend = in_array($chartType, ['pie','doughnut']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= $titleSafe ?></title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: Arial, sans-serif; background: #f4f6f9; color: #333; padding: 24px; }
  h1  { font-size: 1.4rem; margin-bottom: 4px; color: #2c3e50; }
  .subtitle { font-size: 0.82rem; color: #777; margin-bottom: 20px; }

  .card {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 1px 4px rgba(0,0,0,.12);
    padding: 20px;
    margin-bottom: 20px;
  }

  .chart-container {
    position: relative;
    max-width: 900px;
    margin: 0 auto;
  }

  .stats-row {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
    margin-bottom: 16px;
  }
  .stat-box {
    background: #eaf4fd;
    border-left: 4px solid #2980b9;
    padding: 10px 16px;
    border-radius: 5px;
    flex: 1;
    min-width: 120px;
  }
  .stat-box .val  { font-size: 1.6rem; font-weight: bold; color: #2c3e50; }
  .stat-box .lbl  { font-size: 0.75rem; color: #555; }

  .error-box {
    background: #fef9e7;
    border-left: 4px solid #f39c12;
    padding: 10px 14px;
    border-radius: 5px;
    font-size: 0.85rem;
    margin-bottom: 14px;
  }

  .btn-row { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 16px; }
  .btn {
    padding: 9px 20px;
    border: none;
    border-radius: 6px;
    font-size: 0.88rem;
    cursor: pointer;
    font-weight: bold;
    text-decoration: none;
    display: inline-block;
  }
  .btn-primary   { background: #27ae60; color: #fff; }
  .btn-primary:hover { background: #229954; }
  .btn-secondary { background: #2980b9; color: #fff; }
  .btn-secondary:hover { background: #2471a3; }
  .btn-light     { background: #ecf0f1; color: #555; }
  .btn-light:hover { background: #dde1e4; }
</style>
</head>
<body>

<h1><?= $titleSafe ?></h1>
<p class="subtitle">
  Total de registros: <strong><?= number_format($total) ?></strong> —
  Archivo: <code><?= htmlspecialchars($csvPath) ?></code>
</p>

<?php if (!empty($chartError)): ?>
<div class="error-box"><?= htmlspecialchars($chartError) ?></div>
<?php endif; ?>

<div class="card">
  <div class="chart-container">
    <canvas id="mainChart"></canvas>
  </div>

  <div class="btn-row">
    <button class="btn btn-primary" onclick="downloadChart()">Descargar imagen (PNG)</button>
    <a class="btn btn-secondary" href="<?= $configBack ?>">Cambiar configuración</a>
    <button class="btn btn-light" onclick="history.back()">Volver</button>
  </div>
</div>

<!-- Chart.js desde CDN; reemplazar src por ruta local si no hay internet -->
<script src="chart.min.js"></script>
<script>
const ctx = document.getElementById('mainChart').getContext('2d');

const labels   = <?= $labelsJson ?>;
const datasets = <?= $datasetsJson ?>;
const chartType = <?= json_encode($chartJsType) ?>;
const showLegend = <?= $showLegend ? 'true' : 'false' ?>;

const chart = new Chart(ctx, {
  type: chartType,
  data: { labels, datasets },
  options: {
    responsive: true,
    plugins: {
      legend: {
        display: showLegend,
        position: 'right',
      },
      title: {
        display: true,
        text: <?= json_encode($chartTitle) ?>,
        font: { size: 16 },
        padding: { bottom: 16 },
      },
      tooltip: {
        callbacks: {
          label: function(context) {
            const val = context.parsed.y ?? context.parsed;
            if (chartType === 'pie' || chartType === 'doughnut') {
              const total = context.dataset.data.reduce((a,b) => a+b, 0);
              const pct   = ((val / total) * 100).toFixed(1);
              return ` ${context.label}: ${val} (${pct}%)`;
            }
            return ` ${val}`;
          }
        }
      }
    },
    scales: (chartType === 'pie' || chartType === 'doughnut') ? {} : {
      y: {
        beginAtZero: true,
        ticks: { precision: 0 }
      },
      x: {
        ticks: { maxRotation: 45 }
      }
    }
  }
});

function downloadChart() {
  const link = document.createElement('a');
  link.download = 'grafico.png';
  link.href = document.getElementById('mainChart').toDataURL('image/png');
  link.click();
}
</script>

</body>
</html>
