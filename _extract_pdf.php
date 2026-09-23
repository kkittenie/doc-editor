<?php
/**
 * Ekstrak teks dari 5 PDF draft kontrak → storage/app/tmp-dump/pdf/*.txt
 * Pakai smalot/pdfparser (sudah ada di composer). Sekali jalan, untuk verifikasi
 * template ContractTemplates.php vs dokumen asli.
 */
require __DIR__ . '/vendor/autoload.php';

use Smalot\PdfParser\Parser;

$src = 'C:/Users/ACER/OneDrive/Documents/AD';
$out = __DIR__ . '/storage/app/tmp-dump/pdf';

if (!is_dir($out)) {
    mkdir($out, 0755, true);
}

$files = [
    'kemitraan'         => 'Draft Kontrak Kemitraan.pdf',
    'colocation'        => 'Draft Kontrak layanan Colocation.pdf',
    'managed-service'   => 'Draft Kontrak layanan Dedicated, Metro, Managed Service.pdf',
    'soho'              => 'Draft Kontrak layanan Soho.pdf',
    'payung'            => 'Draft Kontrak Payung layanan Dedicated, Metro, Managed Service.pdf',
];

$parser = new Parser();

foreach ($files as $key => $name) {
    $path = $src . '/' . $name;

    if (!file_exists($path)) {
        echo "[MISS] {$name}\n";
        continue;
    }

    try {
        $pdf    = $parser->parseFile($path);
        $pages  = $pdf->getPages();
        $chunks = [];

        foreach ($pages as $i => $page) {
            $chunks[] = "=== PAGE " . ($i + 1) . " ===\n" . $page->getText();
        }

        $text = implode("\n", $chunks);
        file_put_contents($out . '/' . $key . '.txt', $text);
        echo "[OK] {$name} → {$key}.txt (" . strlen($text) . " chars, " . count($pages) . " pages)\n";
    } catch (\Throwable $e) {
        echo "[ERR] {$name}: " . $e->getMessage() . "\n";
    }
}
