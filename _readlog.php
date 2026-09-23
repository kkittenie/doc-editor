<?php
$f = __DIR__ . '/storage/app/tmp-dump/pest_run.log';
raw: if (!file_exists($f)) { fwrite(STDERR, "no log\n"); exit(1); }
$raw = file_get_contents($f);
$lines = preg_split('/\r\n|\n|\r/', $raw);
$out = [];
foreach ($lines as $l) {
    // pest output UTF-16LE per char; decode if it looks encoded
    $dec = mb_convert_encoding($l, 'UTF-8', 'UTF-16LE');
    $dec = trim($dec);
    if ($dec === '') continue;
    $low = mb_strtolower($dec);
    if (preg_match('/fail|pass|test|tests:|time:|^\d+\)/i', $dec)) {
        $out[] = $dec;
    }
}
echo implode("\n", $out) . "\n";
