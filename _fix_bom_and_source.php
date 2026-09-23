<?php
$file = 'app/data/ContractTemplates.php';
$c = file_get_contents($file);
// Hapus BOM jika ada
$c = preg_replace('/^\xEF\xBB\xBF/', '', $c);
// Ganti marker sumber
$c = str_replace("'DOCX sumber'", "'PDF sumber'", $c);
file_put_contents($file, $c);
echo 'Fixed. PDF sumber count: ' . substr_count(file_get_contents($file), "'PDF sumber'") . PHP_EOL;
echo 'DOCX sumber count: ' . substr_count(file_get_contents($file), "'DOCX sumber'") . PHP_EOL;