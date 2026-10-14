<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Document;

$docs = Document::all();
echo "Total documents in DB: " . $docs->count() . PHP_EOL;

foreach ($docs as $doc) {
    echo "ID: {$doc->id}, Title: {$doc->title}, Status: {$doc->status}, Template: " . ($doc->body_content['templateKey'] ?? 'N/A') . PHP_EOL;
    $pages = $doc->body_content['pages'] ?? [];
    echo "  Page count in DB: " . count($pages) . PHP_EOL;
    foreach ($pages as $pIdx => $pageHtml) {
        $hasPasal14 = str_contains($pageHtml, 'PASAL 14') || str_contains($pageHtml, 'Pasal 14');
        $hasLampA = str_contains($pageHtml, 'LAMPIRAN A');
        $hasLampB = str_contains($pageHtml, 'LAMPIRAN B');
        if ($hasPasal14 || $hasLampA || $hasLampB) {
            echo "    Page " . ($pIdx + 1) . ": ";
            if ($hasPasal14) echo "[Pasal 14] ";
            if ($hasLampA) echo "[LAMPIRAN A] ";
            if ($hasLampB) echo "[LAMPIRAN B] ";
            echo PHP_EOL;
        }
    }
}
