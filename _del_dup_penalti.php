<?php
/** Hapus baris 'Penalti' duplikat pertama di region colocation definisi. */
$f = __DIR__ . '/app/data/ContractTemplates.php';
$s = file_get_contents($f);
$needle = 'Ruang Lingkup” adalah kerjasama dalam hal layanan yang diberikan oleh PIHAK PERTAMA kepada Pelanggan berupa Colocation';
$pos = strpos($s, $needle);
if ($pos === false) { fwrite(STDERR, "needle not found\n"); exit(1); }

// cari duplikat "Penalti" pertama setelah needle
$idx = strpos($s, '"Penalti” adalah denda', $pos);
if ($idx === false) { fwrite(STDERR, "Penalti not found\n"); exit(1); }

$end = strpos($s, "\n", $idx + 10);
if ($end === false) { fwrite(STDERR, "EOL not found\n"); exit(1); }

$removed = substr($s, $idx, $end - $idx + 1);
$s = substr($s, 0, $idx) . substr($s, $end + 1);
file_put_contents($f, $s);
echo "removed first Penalti line (len=" . strlen($removed) . ")\n";
