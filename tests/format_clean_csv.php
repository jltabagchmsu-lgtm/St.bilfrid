<?php

$tsvPath = __DIR__ . '/../WHITEBOX_TEST_RESULTS.tsv';
$lines = file($tsvPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

function formatCsvCell($val) {
    $val = trim($val);
    // If enclosed in outer quotes from TSV, remove them
    if (preg_match('/^"(.*)"$/s', $val, $m)) {
        $val = str_replace('""', '"', $m[1]);
    }
    
    // Check if quoting is needed (contains comma, newline, or quote)
    if (strpos($val, ',') !== false || strpos($val, '"') !== false || strpos($val, "\n") !== false) {
        return '"' . str_replace('"', '""', $val) . '"';
    }
    return $val;
}

$outputRows = [];
foreach ($lines as $line) {
    $parts = explode("\t", $line);
    $formattedRow = array_map('formatCsvCell', $parts);
    $outputRows[] = implode(',', $formattedRow);
}

$finalCsv = implode("\n", $outputRows);

file_put_contents(__DIR__ . '/../WHITEBOX_TEST_RESULTS_FORMATTED.csv', $finalCsv);
file_put_contents(__DIR__ . '/../WHITEBOX_TEST_RESULTS.csv', $finalCsv);
file_put_contents('C:/Users/Carin Benjamin/Downloads/WHITEBOX_TEST_RESULTS_FORMATTED.csv', $finalCsv);
file_put_contents('C:/Users/Carin Benjamin/Downloads/WHITEBOX_TEST_RESULTS.csv', $finalCsv);

echo "Formatted CSV generated successfully. Total lines: " . count($outputRows) . "\n";
