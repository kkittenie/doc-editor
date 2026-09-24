<?php
// Periksa backup pra-perbaikan vs kondisi DB sekarang untuk doc 169/173/184:
// apakah TERBUKAR-nya warisan backup, atau hasil tulisan skrip repair?
require __DIR__.'/vendor/autoload.php';

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

$app = require __DIR__.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

function vx_norm($s) {
    $s = html_entity_decode((string) $s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $s = strip_tags($s);
    $s = preg_replace('/[\x{00A0}\s]+/u', ' ', $s);
    return trim($s);
}
function vx_heads($html) {
    preg_match_all('/<(p|h[1-6])\b[^>]*>(.*?)<\/\1>/is', $html, $mm);
    $paras = array_map('vx_norm', $mm[2]);
    $heads = [];
    foreach ($paras as $i => $t) {
        if (preg_match('/^pasal(?:\s|\x{00A0}|&nbsp;|&#160;)*(\d+)\s*$/iu', $t, $m)) {
            $judul = '(KOSONG)';
            for ($j = $i + 1; $j < count($paras) && $j < $i + 4; $j++) {
                if ($paras[$j] !== '') { $judul = $paras[$j]; break; }
            }
            $heads[] = $m[1].'=>'.$judul;
        }
    }
    return $heads;
}

foreach ([169, 173, 184] as $id) {
    $file = __DIR__.'/storage/app/repair-backup/doc-'.$id.'.json';
    echo "== DOC $id ==\n";
    if (!file_exists($file)) { echo "  backup TIDAK ADA\n\n"; continue; }
    $bak = json_decode(file_get_contents($file), true);
    $bakHeads = vx_heads(implode("\n", $bak['pages'] ?? []));
    echo '  BACKUP ('.count($bakHeads)." heading):\n";
    foreach ($bakHeads as $h) echo '    '.$h."\n";

    $row = DB::table('documents')->where('id', $id)->first();
    $body = json_decode((string) $row->body_content, true);
    $nowHeads = vx_heads(implode("\n", $body['pages'] ?? []));
    echo '  SEKARANG ('.count($nowHeads)." heading):\n";
    foreach ($nowHeads as $h) echo '    '.$h."\n";
    echo "\n";
}
