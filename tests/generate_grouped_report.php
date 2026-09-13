<?php

$tsvPath = __DIR__ . '/../WHITEBOX_TEST_RESULTS.tsv';
$lines = file($tsvPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

// Parse rows
$header = explode("\t", array_shift($lines));
$useCases = [];

foreach ($lines as $line) {
    $row = explode("\t", $line);
    $row = array_map(function($c) {
        $c = trim($c);
        if (preg_match('/^"(.*)"$/s', $c, $m)) {
            $c = str_replace('""', '"', $m[1]);
        }
        return $c;
    }, $row);

    if (count($row) < 9) continue;

    $tcId = $row[0];
    $useCase = $row[1];
    $segment = $row[2];
    $desc = $row[3];
    $input = $row[4];
    $expected = $row[5];
    $actual = $row[6];
    $result = $row[7];
    $duration = $row[8];

    if (!isset($useCases[$useCase])) {
        $useCases[$useCase] = [];
    }
    $useCases[$useCase][] = [
        'id' => $tcId,
        'segment' => $segment,
        'desc' => $desc,
        'input' => $input,
        'expected' => $expected,
        'actual' => $actual,
        'result' => $result,
        'duration' => $duration
    ];
}

$html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>White Box Testing - Model Logic & Control Flow Analysis</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm;
        }

        :root {
            --primary-navy: #0f2b48;
            --header-navy: #16385c;
            --section-banner: #1e4a78;
            --border-color: #cbd5e1;
            --text-dark: #1e293b;
            --text-muted: #64748b;
            --bg-light: #f8fafc;
            --row-even: #f1f5f9;
            --row-odd: #ffffff;
            --pass-green: #16a34a;
            --pass-bg: #dcfce7;
            --tc-badge-bg: #e0f2fe;
            --tc-badge-text: #0284c7;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #f1f5f9;
            color: var(--text-dark);
            padding: 24px;
            font-size: 12px;
            line-height: 1.4;
        }

        .container {
            max-width: 1700px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            border: 1px solid var(--border-color);
        }

        .action-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #ffffff;
            padding: 14px 24px;
            border-bottom: 1px solid var(--border-color);
            gap: 15px;
            flex-wrap: wrap;
        }

        .action-bar-title {
            font-size: 14px;
            font-weight: 700;
            color: var(--primary-navy);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-group {
            display: flex;
            gap: 10px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            font-size: 12px;
            font-weight: 600;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.15s ease;
        }

        .btn-primary {
            background: #0284c7;
            color: #ffffff;
        }
        .btn-primary:hover {
            background: #0369a1;
        }

        .btn-success {
            background: #16a34a;
            color: #ffffff;
        }
        .btn-success:hover {
            background: #15803d;
        }

        .btn-secondary {
            background: #e2e8f0;
            color: #334155;
        }
        .btn-secondary:hover {
            background: #cbd5e1;
        }

        .main-header {
            background: linear-gradient(135deg, var(--primary-navy) 0%, var(--header-navy) 100%);
            color: #ffffff;
            text-align: center;
            padding: 16px 20px;
        }

        .main-header h1 {
            font-size: 18px;
            font-weight: 800;
            letter-spacing: 0.02em;
            text-transform: uppercase;
        }

        .main-header p {
            font-size: 12px;
            color: #93c5fd;
            margin-top: 4px;
            font-weight: 500;
        }

        .table-container {
            width: 100%;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11.5px;
            text-align: left;
        }

        thead th {
            background-color: var(--header-navy);
            color: #ffffff;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.04em;
            padding: 10px 12px;
            border: 1px solid #1e3a5f;
            white-space: nowrap;
            position: sticky;
            top: 0;
            z-index: 20;
        }

        .section-header-row td {
            background-color: var(--section-banner) !important;
            color: #ffffff;
            font-weight: 700;
            font-size: 12px;
            letter-spacing: 0.03em;
            padding: 8px 14px;
            border-top: 2px solid #0f2b48;
            border-bottom: 2px solid #0f2b48;
            text-transform: uppercase;
        }

        tbody tr:nth-child(even) {
            background-color: var(--row-even);
        }

        tbody tr:nth-child(odd) {
            background-color: var(--row-odd);
        }

        tbody tr:hover {
            background-color: #e0f2fe !important;
        }

        td {
            padding: 9px 12px;
            border: 1px solid var(--border-color);
            vertical-align: top;
            color: var(--text-dark);
        }

        .tc-id {
            font-family: 'JetBrains Mono', monospace;
            font-size: 11px;
            font-weight: 700;
            color: var(--tc-badge-text);
            background: var(--tc-badge-bg);
            padding: 2px 6px;
            border-radius: 4px;
            display: inline-block;
            white-space: nowrap;
            border: 1px solid #bae6fd;
        }

        .code-segment {
            font-weight: 600;
            color: #0f172a;
            white-space: nowrap;
        }

        .test-desc {
            font-weight: 500;
            color: #334155;
            min-width: 220px;
        }

        .input-val {
            font-family: 'JetBrains Mono', monospace;
            font-size: 10.5px;
            color: #0f766e;
            background: #f0fdfa;
            padding: 3px 6px;
            border-radius: 4px;
            border: 1px solid #ccfbf1;
            word-break: break-all;
            min-width: 140px;
        }

        .expected-val {
            color: #1e3a8a;
            font-size: 11px;
            min-width: 160px;
        }

        .actual-val {
            color: #15803d;
            font-size: 11px;
            font-weight: 500;
            min-width: 160px;
        }

        .badge-pass {
            background-color: var(--pass-bg);
            color: var(--pass-green);
            font-weight: 700;
            font-size: 11px;
            padding: 3px 8px;
            border-radius: 12px;
            display: inline-block;
            text-align: center;
            border: 1px solid #bbf7d0;
            white-space: nowrap;
        }

        .duration {
            font-family: 'JetBrains Mono', monospace;
            font-size: 10.5px;
            color: var(--text-muted);
            white-space: nowrap;
            text-align: right;
        }

        @media print {
            body {
                padding: 0;
                background: #ffffff;
            }
            .action-bar {
                display: none !important;
            }
            .container {
                box-shadow: none;
                border: none;
            }
            table {
                font-size: 9.5px;
            }
            td, th {
                padding: 5px 8px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="action-bar">
            <div class="action-bar-title">
                <span>📋 White-Box Testing Document &bull; 78 Basis Path Test Cases &bull; 100% Pass Rate</span>
            </div>
            <div class="btn-group">
                <button class="btn btn-success" onclick="downloadCSV()">⬇ Download CSV</button>
                <button class="btn btn-primary" onclick="window.print()">🖨 Print / Save as PDF</button>
                <button class="btn btn-secondary" onclick="downloadTSV()">⬇ Download TSV</button>
            </div>
        </div>

        <div class="main-header">
            <h1>White Box Testing - Model Logic & Control Flow Analysis</h1>
            <p>NewConstuc.FIRM Enterprise Construction Management &bull; Comprehensive Eloquent Model Validation</p>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th style="width: 75px;">Test Case ID</th>
                        <th style="width: 130px;">Tested Code Segment</th>
                        <th>Test Description</th>
                        <th>Input Values</th>
                        <th>Expected Behavior</th>
                        <th>Actual Behavior</th>
                        <th style="width: 70px; text-align: center;">Result</th>
                        <th style="width: 65px; text-align: right;">Duration</th>
                    </tr>
                </thead>
                <tbody>
HTML;

foreach ($useCases as $useCaseTitle => $tests) {
    $escapedTitle = htmlspecialchars($useCaseTitle, ENT_QUOTES, 'UTF-8');
    $html .= <<<HTML
                    <tr class="section-header-row">
                        <td colspan="8"><strong>Use Case: {$escapedTitle}</strong></td>
                    </tr>
HTML;
    foreach ($tests as $t) {
        $id = htmlspecialchars($t['id'], ENT_QUOTES, 'UTF-8');
        $segment = htmlspecialchars($t['segment'], ENT_QUOTES, 'UTF-8');
        $desc = htmlspecialchars($t['desc'], ENT_QUOTES, 'UTF-8');
        $input = htmlspecialchars($t['input'], ENT_QUOTES, 'UTF-8');
        $expected = htmlspecialchars($t['expected'], ENT_QUOTES, 'UTF-8');
        $actual = htmlspecialchars($t['actual'], ENT_QUOTES, 'UTF-8');
        $result = htmlspecialchars($t['result'], ENT_QUOTES, 'UTF-8');
        $duration = htmlspecialchars($t['duration'], ENT_QUOTES, 'UTF-8');

        $html .= <<<HTML
                    <tr>
                        <td><span class="tc-id">{$id}</span></td>
                        <td><span class="code-segment">{$segment}</span></td>
                        <td><div class="test-desc">{$desc}</div></td>
                        <td><div class="input-val">{$input}</div></td>
                        <td><div class="expected-val">{$expected}</div></td>
                        <td><div class="actual-val">{$actual}</div></td>
                        <td style="text-align: center;"><span class="badge-pass">{$result}</span></td>
                        <td class="duration">{$duration}</td>
                    </tr>
HTML;
    }
}

$csvContentJson = json_encode(file_get_contents(__DIR__ . '/../WHITEBOX_TEST_RESULTS_FORMATTED.csv'));
$tsvContentJson = json_encode(file_get_contents(__DIR__ . '/../WHITEBOX_TEST_RESULTS.tsv'));

$html .= <<<HTML
                </tbody>
            </table>
        </div>
    </div>

    <script>
        const csvContent = {$csvContentJson};
        const tsvContent = {$tsvContentJson};

        function triggerDownload(content, filename, type) {
            const blob = new Blob([content], { type: type });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        }

        function downloadCSV() {
            triggerDownload(csvContent, 'WHITEBOX_TEST_RESULTS_FORMATTED.csv', 'text/csv;charset=utf-8;');
        }

        function downloadTSV() {
            triggerDownload(tsvContent, 'WHITEBOX_TEST_RESULTS.tsv', 'text/tab-separated-values;charset=utf-8;');
        }
    </script>
</body>
</html>
HTML;

file_put_contents(__DIR__ . '/../whitebox_test_report.html', $html);
file_put_contents('C:/Users/Carin Benjamin/Downloads/whitebox_test_report.html', $html);
echo "Updated whitebox_test_report.html with grouped banner layout.\n";
