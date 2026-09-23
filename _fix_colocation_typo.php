<?php
/** Koreksi typo definisi Penalti di ContractTemplates.php. */
$f = __DIR__ . '/app/data/ContractTemplates.php';
$s = file_get_contents($f);

// Typo 1: "syarat-syarah PERJANJIAN" -> "syarat-syarat PERJANJIAN"
$before = $s;
$s = str_replace('syarat-syarah PERJANJIAN', 'syarat-syarat PERJANJIAN', $s);

// Typo 2: "PERJANJAIN ini" -> "PERJANJIAN ini"
$s = str_replace('PERJANJAIN ini', 'PERJANJIAN ini', $s);

file_put_contents($f, $s);
echo "done. changed=" . ($before !== $s ? 'yes' : 'no') . "\n";
