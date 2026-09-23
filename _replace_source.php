<?php
$file = 'app/data/ContractTemplates.php';
$c = file_get_contents($file);
$c = str_replace("'DOCX sumber'", "'PDF sumber'", $c);
file_put_contents($file, $c);
echo 'Selesai. PDF sumber count: ' . substr_count(file_get_contents($file), "'PDF sumber'") . PHP_EOL;