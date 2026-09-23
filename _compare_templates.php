<?php
/**
 * Bandingkan isi ContractTemplates.php dengan teks hasil ekstraksi PDF
 * (storage/app/tmp-dump/pdf/*.txt). Output: laporan baris PDF yang TIDAK
 * ditemukan di template, dan baris template yang tidak ada di PDF.
 *
 * Normalisasi: lowercase, buang semua karakter non-alphanumeric (awalan
 * justified-text/kerning seperti "Laya nan" == "Layanan").
 * Sekali jalan → storage/app/tmp-dump/report.txt
 */
require __DIR__ . '/vendor/autoload.php';

use App\Data\ContractTemplates;

$map = [
    'kontrak-kemitraan'      => 'kemitraan',
    'kontrak-colocation'     => 'colocation',
    'kontrak-managed-service' => 'managed-service',
    'kontrak-soho'           => 'soho',
    'kontrak-payung'         => 'payung',
];

/** Gabung seluruh string dalam struktur template apa pun. */
function tplFlatten($v): string
{
    if (is_string($v)) {
        return $v;
    }
    if (is_array($v)) {
        $out = [];
        foreach ($v as $x) {
            $out[] = tplFlatten($x);
        }
        return implode("\n", $out);
    }
    return '';
}

/** Normalisasi: lowercase + hanya huruf/angka. */
function nz(string $s): string
{
    return preg_replace('/[^a-z0-9]/', '', mb_strtolower($s));
}

/** Hilangkan tag HTML & entitas lalu jadikan teks polos per baris. */
function stripHtml(string $s): string
{
    $s = preg_replace('/<br\s*\/?>/i', "\n", $s);
    $s = preg_replace('/<\/(p|div|li|h[1-6]|tr)>/i', "\n", $s);
    $s = strip_tags($s);
    $s = html_entity_decode($s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    return $s;
}

/** Baris furnitur PDF yang bukan isi dokumen (kop & penanda halaman). */
function isFurniture(string $line): bool
{
    $t = trim($line);
    if ($t === '') {
        return true;
    }
    $patterns = [
        '/^=== PAGE/',
        '/^P a g e \|/',
        '/^Paraf PIHAK/',
        '/^PT Bina Informatika Solusi$/',
        '/^Jl\. Prakarsa Muda No\.258 Kel/',
        '/^Kota Cirebon, Jawa Barat 45131$/',
        '/^Tlp\. 0231-247618/',
        '/^info@fibertrust\.id \| www\.fibertrust\.id$/',
        '/^\s*$/',
    ];
    foreach ($patterns as $p) {
        if (preg_match($p, $t)) {
            return true;
        }
    }
    return false;
}

$report = [];
$perKey = [];

foreach ($map as $key => $pdfKey) {
    $tpl = ContractTemplates::find($key);
    if (!$tpl) {
        $report[] = "[$key] TEMPLATE TIDAK ADA";
        continue;
    }

    $pdfPath = __DIR__ . '/storage/app/tmp-dump/pdf/' . $pdfKey . '.txt';
    if (!file_exists($pdfPath)) {
        $report[] = "[$key] PDF dump tidak ada: $pdfPath";
        continue;
    }

    $tplText = stripHtml(tplFlatten($tpl));
    $tplNorm = nz($tplText);

    $pdfRaw   = file_get_contents($pdfPath);
    $pdfLines = preg_split('/\r?\n/', $pdfRaw);

    $missing = [];
    foreach ($pdfLines as $line) {
        if (isFurniture($line)) {
            continue;
        }
        $n = nz($line);
        if ($n === '') {
            continue;
        }
        if (!str_contains($tplNorm, $n)) {
            $missing[] = trim($line);
        }
    }

    // Arah sebaliknya: baris template yang tidak muncul di PDF mana pun.
    $pdfNorm = nz(implode("\n", array_filter($pdfLines, fn ($l) => !isFurniture($l))));
    $extra   = [];
    foreach (preg_split('/\r?\n/', $tplText) as $line) {
        $n = nz($line);
        if ($n === '') {
            continue;
        }
        if (!str_contains($pdfNorm, $n)) {
            $extra[] = trim($line);
        }
    }

    $start = count($report);
    $report[] = "==================== $key ====================";
    $report[] = "--- PDF → template (ada di PDF, TIDAK ada di template): " . count($missing);
    foreach ($missing as $m) {
        $report[] = "  MIS: " . $m;
    }
    $report[] = "--- Template → PDF (ada di template, TIDAK ada di PDF): " . count($extra);
    foreach ($extra as $m) {
        $report[] = "  EXT: " . $m;
    }
    $report[] = '';
    file_put_contents(__DIR__ . '/storage/app/tmp-dump/report-' . $pdfKey . '.txt', implode("\n", array_slice($report, $start)) . "\n");
}

$out = implode("\n", $report);
file_put_contents(__DIR__ . '/storage/app/tmp-dump/report.txt', $out);
echo "report.txt ditulis (" . strlen($out) . " bytes)\n";
