<?php
/** Decode pest log (UTF-16LE) & extract test status lines. */
$files = [
  'storage/app/tmp-dump/pest_run.log',
  'storage/app/tmp-dump/pest_clean.log',
  'storage/app/tmp-dump/pest_test.log',
  'storage/app/tmp-dump/pest_raw.log',
  'storage/app/tmp-dump/stdout.log',
];
$found = null;
foreach ($files as $f) {
  if (file_exists(__DIR__.'/'.$f) && filesize(__DIR__.'/'.$f) > 8) {
    $found = $f;
    break;
  }
}
if (!$found) { fwrite(STDERR, "no log found\n"); exit(1); }
$raw = file_get_contents(__DIR__.'/'.$found);
$dec = @mb_convert_encoding($raw, 'UTF-8', 'UTF-16LE');
if ($dec === false) $dec = mb_convert_encoding($raw, 'UTF-8', 'UTF-8');
file_put_contents(__DIR__.'/storage/app/tmp-dump/decoded.log', $dec);
$lines = preg_split('/\r\n|\n|\r/', $dec);
foreach ($lines as $i => $l) {
  $t = trim($l);
  if ($t === '') continue;
  if (preg_match('/FAIL|PASS|Tests:|Time:|●|failed|passed|test|⏱|^\d+\)|error|Error/i', $t)) {
    echo ($i+1).": ".$t."\n";
  }
}
