<?php
// Periksa: apakah judul tiap pasal template MUNCUL di dokumen 169/173/184?
// Kalau judulnya tidak ada sama sekali -> judul hilang di sumber, bukan sekadar nyasar.
require __DIR__.'/vendor/autoload.php';

use App\Data\ContractTemplates;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

$app = require __DIR__.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$tpl = ContractTemplates::find('kontrak-kemitraan');
$juduls = [];
foreach ($tpl['body_content']['isi'] as $p) $juduls[] = $p['judul'] ?? '';

foreach ([169, 173, 184] as $id) {
    $row = DB::table('documents')->where('id', $id)->first();
    $body = json_decode((string) $row->body_content, true);
    $full = implode("\n", $body['pages'] ?? []);
    $norm = html_entity_decode(strip_tags($full), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $norm = preg_replace('/[\x{00A0}\s]+/u', ' ', $norm);
    echo "== DOC $id\n";
    foreach ($juduls as $i => $j) {
        $found = mb_stripos($norm, $j, 0, 'UTF-8') !== false ? 'ADA' : 'HILANG';
        echo '  '.($i + 1).'. '.$j.' -> '.$found."\n";
    }
    echo "\n";
}
