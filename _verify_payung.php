<?php

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

require_once __DIR__.'/app/Http/Controllers/DocumentController.php';

$ctrl = new ReflectionClass(\App\Http\Controllers\DocumentController::class);
$inst = $ctrl->newInstanceWithoutConstructor();
$m = $ctrl->getMethod('buildTemplateBodyHtml');
$m->setAccessible(true);

$tpl = \App\Data\ContractTemplates::find('kontrak-payung');
$html = $m->invoke($inst, $tpl['body_content'], true);

echo 'jumlah <ol>: ' . substr_count($html, '<ol') . PHP_EOL;
echo 'jumlah <li value= : ' . substr_count($html, '<li value=') . PHP_EOL;
echo 'jumlah <table: ' . substr_count($html, '<table') . PHP_EOL;
echo 'start="3" ada: ' . (str_contains($html, 'start="3"') ? 'ya' : 'tidak') . PHP_EOL;

// Pasal 3 (Aktivasi) & Pasal 12 (Kerahasiaan) harus tetap <p>, bukan <ol>.
foreach (['Aktivasi Layanan' => 'Aktivasi', 'Kerahasiaan' => 'Kerahasiaan'] as $judul => $k) {
    echo "cari blok $judul ..." . PHP_EOL;
}

// Ambil potongan HTML untuk Aktivasi & Kerahasiaan dari isi pasal langsung.
foreach ($tpl['body_content']['isi'] as $pasal) {
    $j = $pasal['judul'] ?? '?';
    $first = $pasal['blocks'][0] ?? [];
    $kind = isset($first['p']) ? 'P' : (isset($first['ol']) ? 'OL('.count($first['ol']['items']).' item)' : (isset($first['table']) ? 'TABLE' : '?'));
    echo str_pad($j, 45) . " => block0: $kind  (total blocks: ".count($pasal['blocks']).')' . PHP_EOL;
}
