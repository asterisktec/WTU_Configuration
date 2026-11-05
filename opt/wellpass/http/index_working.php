<?php
// ---------------------------------------------------------------
// CONFIG: set the CSV location here (absolute path on the server).
// The first row must be the column headers.
// ---------------------------------------------------------------
$csvPath   = '/home/wpadmin/results.csv';
$delimiter = ',';
$pageTitle = 'CSV report';

$headers = [];
$rows    = [];
$error   = null;

if (!is_readable($csvPath)) {
    $error = 'Cannot read ' . $csvPath . '. Check the path and that the web server user has read permission.';
} elseif (($fh = fopen($csvPath, 'r')) === false) {
    $error = 'Could not open the CSV file.';
} else {
    $headers = fgetcsv($fh, 0, $delimiter, '"', '');
    if ($headers && isset($headers[0])) {
        $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]); // strip BOM
    }
    while (($r = fgetcsv($fh, 0, $delimiter, '"', '')) !== false) {
        if ($r === [null]) continue; // skip blank lines
        $rows[] = array_pad($r, count($headers), '');
    }
    fclose($fh);
    if (!$headers) $error = 'The CSV file is empty.';
}

// ---------------------------------------------------------------
// CALCULATED COLUMN: change these 3 values and the formula below.
// ---------------------------------------------------------------
$colA   = 'Quantity';    // header name of the first column, exactly as in the CSV
$colB   = 'Unit Price';  // header name of the second column
$newCol = 'Total';       // name for the new calculated column

$a = array_search($colA, $headers, true);
$b = array_search($colB, $headers, true);

if ($a !== false && $b !== false) {
    $headers[] = $newCol;
    foreach ($rows as &$r) {
        if (is_numeric($r[$a]) && is_numeric($r[$b])) {
            $r[] = round($r[$a] * $r[$b], 2);   // <- the calculation
        } else {
            $r[] = '';                          // leave blank if either value isn't a number
        }
    }
    unset($r);
}

// Numeric columns = every column where all non-empty values are numbers.
$numericCols = [];
foreach ($headers as $i => $h) {
    $vals = array_filter(array_column($rows, $i), fn($v) => trim($v) !== '');
    if ($vals && count(array_filter($vals, 'is_numeric')) === count($vals)) {
        $numericCols[] = $i;
    }
}

// Chart labels come from the first column that isn't numeric (or the first column).
$labelCol = 0;
foreach ($headers as $i => $h) {
    if (!in_array($i, $numericCols, true)) { $labelCol = $i; break; }
}

$chartData = [
    'labels'  => array_map(fn($r) => $r[$labelCol] ?? '', $rows),
    'columns' => [],
];
foreach ($numericCols as $i) {
    $chartData['columns'][$headers[$i]] = array_map(fn($r) => (float)$r[$i], $rows);
}

function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= h($pageTitle) ?></title>
<style>
  :root {
    --bg: #f6f7f9; --surface: #ffffff; --text: #1d2430; --muted: #667085;
    --line: #e3e6eb; --accent: #2f6fed; --head: #eef1f6;
  }
  @media (prefers-color-scheme: dark) {
    :root {
      --bg: #14171c; --surface: #1c2027; --text: #e8ebf0; --muted: #9aa3b2;
      --line: #2d333d; --accent: #6b9bff; --head: #252b34;
    }
  }
  * { box-sizing: border-box; }
  body { margin: 0; background: var(--bg); color: var(--text);
         font: 15px/1.5 system-ui, -apple-system, "Segoe UI", sans-serif; }
  main { max-width: 1000px; margin: 0 auto; padding: 24px 16px 48px; }
  h1 { font-size: 1.5rem; margin: 0 0 4px; }
  .meta { color: var(--muted); margin: 0 0 20px; }
  .panel { background: var(--surface); border: 1px solid var(--line);
           border-radius: 8px; padding: 16px; margin-bottom: 20px; }
  .controls { display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 12px; }
  label { color: var(--muted); font-size: .9rem; }
  select { margin-left: 6px; padding: 4px 8px; border: 1px solid var(--line);
           border-radius: 6px; background: var(--surface); color: var(--text); font: inherit; }
  .chart-wrap { position: relative; height: 320px; }
  .table-wrap { overflow-x: auto; }
  table { border-collapse: collapse; width: 100%; }
  th, td { padding: 8px 12px; text-align: left; border-bottom: 1px solid var(--line); white-space: nowrap; }
  th { background: var(--head); position: sticky; top: 0; }
  td.num { text-align: right; font-variant-numeric: tabular-nums; }
  th.num { text-align: right; }
  .error { color: #b42318; }
</style>
</head>
<body>
<main>
  <h1><?= h($pageTitle) ?></h1>

  <?php if ($error): ?>
    <div class="panel error"><?= h($error) ?></div>
  <?php else: ?>
    <p class="meta">
      <?= count($rows) ?> rows, <?= count($headers) ?> columns.
      Last updated <?= h(date('j M Y, H:i', filemtime($csvPath))) ?>.
    </p>

    <?php if ($numericCols): ?>
    <section class="panel">
      <div class="controls">
        <label>Column
          <select id="colSelect">
            <?php foreach (array_keys($chartData['columns']) as $name): ?>
              <option><?= h($name) ?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <label>Chart type
          <select id="typeSelect">
            <option value="bar">Bar</option>
            <option value="line">Line</option>
          </select>
        </label>
      </div>
      <div class="chart-wrap"><canvas id="chart"></canvas></div>
    </section>
    <?php else: ?>
      <div class="panel">No numeric columns found, so there is nothing to chart.</div>
    <?php endif; ?>

    <section class="panel table-wrap">
      <table>
        <thead>
          <tr>
            <?php foreach ($headers as $i => $name): ?>
              <th class="<?= in_array($i, $numericCols, true) ? 'num' : '' ?>"><?= h($name) ?></th>
            <?php endforeach; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $r): ?>
            <tr>
              <?php foreach ($r as $i => $cell): ?>
                <td class="<?= in_array($i, $numericCols, true) ? 'num' : '' ?>"><?= h($cell) ?></td>
              <?php endforeach; ?>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </section>
  <?php endif; ?>
</main>

<?php if (!$error && $numericCols): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
  const data = <?= json_encode($chartData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>;
  const colSelect  = document.getElementById('colSelect');
  const typeSelect = document.getElementById('typeSelect');
  let chart;

  function draw() {
    const name = colSelect.value;
    if (chart) chart.destroy();
    chart = new Chart(document.getElementById('chart'), {
      type: typeSelect.value,
      data: {
        labels: data.labels,
        datasets: [{
          label: name,
          data: data.columns[name],
          backgroundColor: 'rgba(47,111,237,0.6)',
          borderColor: '#2f6fed',
          borderWidth: 2,
          tension: 0.25
        }]
      },
      options: { responsive: true, maintainAspectRatio: false }
    });
  }

  colSelect.addEventListener('change', draw);
  typeSelect.addEventListener('change', draw);
  draw();
</script>
<?php endif; ?>
</body>
</html>
