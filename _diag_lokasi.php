<?php
// Lokasi tiap judul template di dalam dokumen: di section PASAL nomor berapa?
require __DIR__.'/vendor/autoload.php';

use App\Data\ContractTemplates;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

$app = require __DIR__.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
require __DIR__.'/_repair_v2_part1.php';

$tpl = ContractTemplates::find('kontrak-kemitraan');
$juduls = [];
foreach ($tpl['body_content']['isi'] as $p) $juduls[] = $p['judul'] ?? '';

$ids = isset($argv[1]) ? [(int) $argv[1]] : [169];
foreach ($ids as $id) {
    $row = DB::table('documents')->where('id', $id)->first();
    $body = json_decode((string) $row->body_content, true);
    $blocks = [];
    foreach (($body['pages'] ?? []) as $pg) {
        foreach (v2_split_blocks($pg) as $b) $blocks[] = $b;
    }
    // section per heading
    $cur = null;
    $secs = [];
    foreach ($blocks as $bi => $b) {
        $nn = null;
        if (in_array($b['tag'], ['p','h1','h2','h3','h4','h5','h6'], true) && v2_is_pasal_heading($b['text'], $nn)) {
            if ($cur !== null) $secs[] = $cur;
            $cur = ['num' => $nn, 'start' => $bi, 'blocks' => [$b]];
            continue;
        }
        if ($cur === null) continue;
        $cur['blocks'][] = $b;
        $cur['end'] = $bi;
    }
    if ($cur !== null) $secs[] = $cur;

    foreach ($juduls as $ji => $j) {
        $jn = mb_strtolower(v2_norm($j));
        $locs = [];
        foreach ($secs as $s) {
            foreach ($s['blocks'] as $k => $b) {
                if ($k === 0) continue;
                if (mb_strtolower($b['text']) === $jn) {
                    $locs[] = 'PASAL'.$s['num'].'#'.($k + 1);
                    break;
                }
            }
        }
        $ok = (count($locs) === 1 && $locs[0] === 'PASAL'.($ji + 1).'#2') ? 'OK' : '<< NYASAR';
        echo ($ji + 1).'. '.$j.' => '.implode(',', $locs).' '.$ok."\n";
    }
    echo "\n";
}
