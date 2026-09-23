<?php
$f = __DIR__ . '/app/data/ContractTemplates.php';
$s = file_get_contents($f);
$cnt = 0;
// Hapus blank-line ganda d. Biaya Layanan <-> Penalti (hanya 1 yang ingin 1 blank line)
$src = "\"Biaya Layanan” adalah biaya yang timbul atas layanan Colocation yang dikenakan kepada Pelanggan dan wajib dibayarkan setiap bulan oleh Pelanggan kepada PIHAK PERTAMA.\n\n\n\"Penalti” adalah denda yang dikenakan kepada Pelanggan sebagai akibat dari adanya pelanggaran terhadap";
$dst = "\"Biaya Layanan” adalah biaya yang timbul atas layanan Colocation yang dikenakan kepada Pelanggan dan wajib dibayarkan setiap bulan oleh Pelanggan kepada PIHAK PERTAMA.\n\n\"Penalti” adalah denda yang dikenakan kepada Pelanggan sebagai akibat dari adanya pelanggaran terhadap";
$s = str_replace($src, $dst, $s, $cnt);
file_put_contents($f, $s);
echo "blank-line fix: $cnt\n";
