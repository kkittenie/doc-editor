<?php
// Diagnosa lanjutan: untuk tiap section PASAL, tampilkan judul + 1 isi.
require __DIR__.'/vendor/autoload.php';

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

$app = require __DIR__.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
require __DIR__.'/_repair_v2_part1.php'; // v2_norm + v2_split_blocks + v2_is_pasal_heading

$ids = isset($argv[1]) ? [(int) $argv[1]] : [169, 173, 184];
foreach ($ids as $id) {
    $row = DB::table('documents')->where('id', $id)->first();
    $body = json_decode((string) $row->body_content, true);
    $blocks = [];
    foreach (($body['pages'] ?? []) as $pg) {
        foreach (v2_split_blocks($pg) as $b) $blocks[] = $b;
    }
    echo "== DOC $id total=".count($blocks)."\n";
    $cur = null;
    $secs = [];
    foreach ($blocks as $b) {
        $nn = null;
        if (in_array($b['tag'], ['p','h1','h2','h3','h4','h5','h6'], true) && v2_is_pasal_heading($b['text'], $nn)) {
            if ($cur !== null) $secs[] = $cur;
            $cur = ['num' => $nn, 'blocks' => [$b]];
            continue;
        }
        if ($cur === null) continue;
        $cur['blocks'][] = $b;
    }
    if ($cur !== null) $secs[] = $cur;
    foreach ($secs as $s) {
        echo '  ['.$s['num'].'] nblok='.count($s['blocks'])."\n";
        for ($i = 1; $i < min(4, count($s['blocks'])); $i++) {
            echo '     - ['.$s['blocks'][$i]['tag'].'] "'.mb_substr($s['blocks'][$i]['text'], 0, 80)."\"\n";
        }
    }
    echo "\n";
}
