<?php
// Cek: judul pasal vs LAMPIRAN — apakah judul-18 nyasar ke area lampiran?
require __DIR__.'/vendor/autoload.php';

use App\Data\ContractTemplates;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

$app = require __DIR__.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
require __DIR__.'/_repair_v2_part1.php';

foreach ([173, 184] as $id) {
    $row = DB::table('documents')->where('id', $id)->first();
    $body = json_decode((string) $row->body_content, true);
    $blocks = [];
    foreach (($body['pages'] ?? []) as $pg) {
        foreach (v2_split_blocks($pg) as $b) $blocks[] = $b;
    }
    echo "== DOC $id\n";
    // blok berisi kata LAMPIRAN (pendek saja)
    foreach ($blocks as $bi => $b) {
        if (mb_strlen($b['text']) < 60 && mb_stripos($b['text'], 'lampiran', 0, 'UTF-8') !== false) {
            echo "   blok[$bi][{$b['tag']}] \"".mb_substr($b['text'], 0, 60)."\"\n";
        }
    }
    // posisi heading PASAL 18
    $seen = 0;
    foreach ($blocks as $bi => $b) {
        $nn = null;
        if (in_array($b['tag'], ['p','h1','h2','h3','h4','h5','h6'], true) && v2_is_pasal_heading($b['text'], $nn)) {
            $seen++;
            if ($nn == 18 || $seen == 18) echo "   heading-18 di blok[$bi] text=\"{$b['text']}\"\n";
        }
    }
    echo "\n";
}
