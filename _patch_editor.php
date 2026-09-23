<?php
$file = __DIR__ . '/resources/js/editor.js';
$src = file_get_contents($file);
if ($src === false) { echo "ERR read\n"; exit(1); }
$guard = file_get_contents(__DIR__ . '/_guard.js');
$precheck = file_get_contents(__DIR__ . '/_precheck.js');
$backup = $file . '.bak';
if (!file_exists($backup)) { copy($file, $backup); echo "backup dibuat\n"; }

if (strpos($src, '__guardBlotOptimize') !== false) { echo "SKIP1\n"; }
else {
    $a1 = "QuillTableBetter.register();\n";
    $p = strpos($src, $a1);
    if ($p === false) { echo "ERR anchor1\n"; exit(1); }
    $src = substr_replace($src, $a1 . $guard, $p, strlen($a1));
    echo "OK1 guard disisipkan\n";
}

if (strpos($src, '__zoneWithTable') !== false) { echo "SKIP2\n"; }
else {
    $a2 = "        if (existingHtml.trim()) {\n";
    $p = strpos($src, $a2);
    if ($p === false) { echo "ERR anchor2\n"; exit(1); }
    $src = substr_replace($src, $precheck . $a2, $p, 0);
    echo "OK2 pre-check disisipkan\n";
}

file_put_contents($file, $src);
echo "DONE " . strlen($src) . "\n";
