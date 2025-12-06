<?php
if ($argc < 3) {
    fwrite(STDERR, "Usage: php coverage-check.php <clover-file> <min-percentage>\n");
    exit(1);
}

[$script, $cloverPath, $threshold] = $argv;

if (!file_exists($cloverPath)) {
    fwrite(STDERR, "Clover file not found: {$cloverPath}\n");
    exit(1);
}

$xml = new SimpleXMLElement(file_get_contents($cloverPath));
$metrics = $xml->xpath('//metrics')[0];
$totalElements = (int) $metrics['elements'];
$coveredElements = (int) $metrics['coveredelements'];

$coverage = $totalElements > 0 ? ($coveredElements / $totalElements) * 100 : 0.0;

if ($coverage < (float) $threshold) {
    $formatted = number_format($coverage, 2);
    fwrite(STDERR, "Coverage {$formatted}% is below required {$threshold}%\n");
    exit(1);
}

echo sprintf("Coverage %.2f%% meets the %.1f%% threshold\n", $coverage, $threshold);
