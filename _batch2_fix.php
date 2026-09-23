<?php
/** Perbaikan batch 2 ContractTemplates.php. */
$f = __DIR__ . '/app/data/ContractTemplates.php';
$s = file_get_contents($f);

// --- 1. kemitraan: ganti item ke-7 (konfigurasi ke Pelanggan) ---
$old = "Pihak Kedua melakukan konfigurasi ke Pelanggan. info@fibertrust.id | www.fibertrust.id LAMPIRAN II PERJANJIAN PAKET LAYANAN NOMOR: 196/FBT/PKS/III/2026";
$new = "Paket layanan meliputi persyaratan sebagai berikut: 1. Nama Brand FIBERTRUST 2. Harga setiap paket minimal Rp 100.000/2Mbps 3. Biaya instalasi disesuaikan dengan kebutuhan pelanggan 4. Paket Layanan yang dibuat harus diinformasikan ke Pihak Pertama untuk persetujuan dan jika ada perubahan maka maksimal menginformasikan perubahan tersebut 7 hari kalender info@fibertrust.id | www.fibertrust.id LAMPIRAN III PERJANJIAN PENGADUAN PELANGGAN NOMOR: 196/FBT/PKS/III/2026";
$c = substr_count($s, $old);
$s = str_replace($old, $new, $s);
echo "kemitraan block7 replace count=$c\n";

// --- 2. managed-service JW efektif: pastikan sudah ada di 'text' ---
$jwAnchor = "Jangka Waktu Efektif Layanan sebagaimana dimaksud dalam Syarat dan Ketentuan Berlangganan ini adalah tanggal sebagaimana dimaksud dalam lampiran A Perjanjian Berlangganan Jasa ini dan atau Service Order Form.";
$c2 = (strpos($s, $jwAnchor) !== false) ? 1 : 0;
echo "managed JW anchor count=$c2\n";

// --- 3. colocation rekening: perbaiki "kenomor" -> "ke nomor" ---
$s = str_replace("sistem transfer kenomor rekening:", "sistem transfer ke nomor rekening:", $s);
echo "colocation rekening typo fixed\n";

file_put_contents($f, $s);
echo "done\n";

