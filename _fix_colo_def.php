<?php
/** Perbaiki urutan definisi colocation agar Ruang Lingkup ke-4 (sebelum Biaya Layanan). */
$f = __DIR__ . '/app/data/ContractTemplates.php';
$s = file_get_contents($f);

$src = <<<PHP
"Ruang Lingkup” adalah kerjasama dalam hal layanan yang diberikan oleh PIHAK PERTAMA kepada Pelanggan berupa Colocation, sebagaimana tercantum dalam PERJANJIAN ini.

"Biaya Layanan” adalah biaya yang timbul atas layanan Colocation yang dikenakan kepada Pelanggan dan wajib dibayarkan setiap bulan oleh Pelanggan kepada PIHAK PERTAMA.

"Penalti” adalah denda yang dikenakan kepada Pelanggan sebagai akibat dari adanya pelanggaran terhadap  ketentuan-ketentuan dan syarat-syarat PERJANJIAN ini atau permintaan perpindahan atau perubahan tertentu atas jasa layanan yang telah disetujui kedua belah pihak dalam PERJANJIAN ini. Penalti yang dikenakan kepada Pelanggan disesuaikan dengan jenis pelanggaran yang dilakukan Pelanggan atau jenis perubahan jasa layanan yang diminta oleh Pelanggan.

Apabila terdapat istilah yang belum diatur dalam Perjanjian ini, maka akan ditafsirkan sesuai ketentuan peraturan perundang-undangan yang berlaku.
PHP;

$dst = <<<PHP
"Ruang Lingkup” adalah kerjasama dalam hal layanan yang diberikan oleh PIHAK PERTAMA kepada Pelanggan berupa Colocation, sebagaimana tercantum dalam PERJANJIAN ini.

"Biaya Layanan” adalah biaya yang timbul atas layanan Colocation yang dikenakan kepada Pelanggan dan wajib dibayarkan setiap bulan oleh Pelanggan kepada PIHAK PERTAMA.

"Penalti” adalah denda yang dikenakan kepada Pelanggan sebagai akibat dari adanya pelanggaran terhadap ketentuan-ketentuan dan syarat-syarat PERJANJIAN ini atau permintaan perpindahan atau perubahan tertentu atas jasa layanan yang telah disetujui kedua belah pihak dalam PERJANJIAN ini. Penalti yang dikenakan kepada Pelanggan disesuaikan dengan jenis pelanggaran yang dilakukan Pelanggan atau jenis perubahan jasa layanan yang diminta oleh Pelanggan.

Apabila terdapat istilah yang belum diatur dalam Perjanjian ini, maka akan ditafsirkan sesuai ketentuan peraturan perundang-undangan yang berlaku.
PHP;

$cnt = 0;
$s = str_replace($src, $dst, $s, $cnt);
file_put_contents($f, $s);
echo "replaced colocation definisi: $cnt\n";
