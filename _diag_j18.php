<?php
// Cek posisi judul-18 di doc 173 & 184: ada di blok ke berapa & apa tags-nya?
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
    echo "== DOC $id total=".count($blocks)."\n";
    foreach ($blocks as $bi => $b) {
        if (mb_stripos($b['text'], 'PENYELESAIAN SENGKETA', 0, 'UTF-8') !== false) {
            echo "   blok[$bi][{$b['tag']}] strong=".var_export(strpos($b['html'], '<strong') !== false, true)
                ." len=".mb_strlen($b['text'])."\n";
            echo '   html: '.mb_substr(preg_replace('/\s+/', ' ', $b['html']), 0, 300)."\n";
            echo '   text: '.mb_substr($b['text'], 0, 200)."\n\n";
        }
    }
}
