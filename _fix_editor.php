<?php
$file = __DIR__ . '/resources/js/editor.js';
$src = file_get_contents($file);
$before = $src;
$src = str_replace("\xEF\xBB\xBF", "", $src);
$bomRemoved = ($src === $before) ? 0 : 1;
$dup = "        if (existingHtml.trim()) {\n        if (existingHtml.trim()) {\n";
$single = "        if (existingHtml.trim()) {\n";
$dupFixed = 0;
while (strpos($src, $dup) !== false) { $src = str_replace($dup, $single, $src); $dupFixed++; }
file_put_contents($file, $src);
echo "bomRemoved=$bomRemoved dupFixed=$dupFixed size=" . strlen($src) . "\n";
echo "bomSisa=" . (strpos($src, "\xEF\xBB\xBF") === false ? "no" : "ADA") . "\n";
echo "anchorCount=" . substr_count($src, $single) . "\n";
