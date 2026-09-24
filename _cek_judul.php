<?php
// Cek: judul setelah tiap heading PASAL vs judul template (kontrak-kemitraan).
// Menjawab: apakah isi pasal sudah di tempat yang benar, bukan sekadar nomornya urut.
require __DIR__.'/vendor/autoload.php';

use App\Data\ContractTemplates;
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

// Judul template kemitraan (urutan benar)
$tpl = ContractTemplates::find('kontrak-kemitraan');
$expJudul = [];
foreach ($tpl['body_content']['isi'] as $p) {
    $expJudul[] = $p['judul'] ?? '';
}
echo 'TEMPLATE kemitraan ('.count($expJudul).' pasal):'."\n";
foreach ($expJudul as $i => $j) echo '  '.($i + 1).'. '.$j."\n";
echo "\n";

$ids = [169, 173, 184];
foreach ($ids as $id) {
    $row = DB::table('documents')->where('id', $id)->first();
    if (!$row) { echo "DOC $id TIDAK ADA\n"; continue; }
    $body = json_decode((string) $row->body_content, true);
    $html = implode("\n", $body['pages'] ?? []);

    // Pecah jadi paragraf, cari heading PASAL -> ambil paragraf non-kosong berikutnya sbg judul
    preg_match_all('/<(p|h[1-6])\b[^>]*>(.*?)<\/\1>/is', $html, $mm);
    $paras = array_map('vx_norm', $mm[2]);

    $heads = []; // [index, nomor, judulBerikut]
    foreach ($paras as $i => $t) {
        if (preg_match('/^pasal(?:\s|\x{00A0}|&nbsp;|&#160;)*(\d+)\s*$/iu', $t, $m)) {
            $judul = '(KOSONG)';
            for ($j = $i + 1; $j < count($paras) && $j < $i + 4; $j++) {
                if ($paras[$j] !== '') { $judul = $paras[$j]; break; }
            }
            $heads[] = ['no' => (int) $m[1], 'judul' => $judul];
        }
    }
    echo "== DOC $id (".count($heads)." heading)\n";
    $ok = 0;
    foreach ($heads as $i => $h) {
        $exp = $expJudul[$i] ?? '(?)';
        $match = (mb_strtolower(vx_norm($exp)) === mb_strtolower(vx_norm($h['judul']))) ? 'OK' : 'BEDA';
        if ($match === 'OK') $ok++;
        echo '  ['.$h['no'].'] => "'.$h['judul'].'" | template: "'.$exp.'" '.$match."\n";
    }
    echo "  => $ok/".count($heads)." judul cocok\n\n";
}
