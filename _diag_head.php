<?php
// Diagnosa: tampilkan semua blok yang mengandung kata "pasal" per dokumen,
// pakai rt_split_blocks v1 yang terbukti jalan (179 blok utk doc 169).
require __DIR__.'/vendor/autoload.php';

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

$app = require __DIR__.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

function d_norm($s)
{
    $s = html_entity_decode((string) $s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $s = strip_tags($s);
    $s = preg_replace('/[\x{00A0}\s]+/u', ' ', $s);
    return trim($s);
}
function d_split($html)
{
    $blocks = [];
    $pos = 0;
    $len = strlen($html);
    while ($pos < $len) {
        if (!preg_match('/<(p|h[1-6]|ol|ul|table)\b/i', $html, $m, PREG_OFFSET_CAPTURE, $pos)) {
            $rest = substr($html, $pos);
            if (trim(strip_tags($rest)) !== '') {
                $blocks[] = ['tag' => 'raw', 'html' => $rest, 'text' => d_norm($rest)];
            }
            break;
        }
        $tag = strtolower($m[1][0]);
        $start = $m[0][1];
        $inter = substr($html, $pos, $start - $pos);
        if (trim(strip_tags($inter)) !== '' || preg_match('/<(hr|img)/i', $inter)) {
            $blocks[] = ['tag' => 'raw', 'html' => $inter, 'text' => d_norm($inter)];
        }
        if ($tag === 'p' || $tag[0] === 'h') {
            $close = stripos($html, '</'.$tag.'>', $start);
            if ($close === false) break;
            $end = $close + strlen('</'.$tag.'>');
        } else {
            preg_match_all('/<'.$tag.'\b/i', $html, $os, PREG_OFFSET_CAPTURE, $start);
            preg_match_all('/<\/'.$tag.'>/i', $html, $cs, PREG_OFFSET_CAPTURE, $start);
            $tokens = [];
            foreach ($os[0] as $t) $tokens[] = [$t[1], 1];
            foreach ($cs[0] as $t) $tokens[] = [$t[1], 0];
            usort($tokens, fn($a, $b) => $a[0] <=> $b[0]);
            $depth = 0;
            $end = null;
            foreach ($tokens as $t) {
                if ($t[0] < $start) continue;
                if ($t[1] === 1) $depth++;
                else {
                    $depth--;
                    if ($depth === 0) {
                        $gt = strpos(substr($html, $t[0]), '>');
                        $end = $t[0] + $gt + 1;
                        break;
                    }
                }
            }
            if ($end === null) $end = $len;
        }
        $frag = substr($html, $start, $end - $start);
        $blocks[] = ['tag' => $tag, 'html' => $frag, 'text' => d_norm($frag)];
        $pos = $end;
    }
    return $blocks;
}

$ids = [169, 186];
foreach ($ids as $id) {
    $row = DB::table('documents')->where('id', $id)->first();
    $body = json_decode((string) $row->body_content, true);
    $pages = $body['pages'] ?? [];
    echo "== DOC $id halaman=".count($pages)."\n";
    $blocks = [];
    foreach ($pages as $pg) {
        foreach (d_split($pg) as $b) $blocks[] = $b;
    }
    echo "   total blok=".count($blocks)."\n";
    $n = 0;
    foreach ($blocks as $bi => $b) {
        if (mb_stripos($b['text'], 'pasal', 0, 'UTF-8') === false) continue;
        if (mb_strlen($b['text']) > 120) continue; // lewati isi panjang, fokus kandidat heading
        $n++;
        echo "   [$bi][{$b['tag']}] \"".mb_substr($b['text'], 0, 90)."\"\n";
        if ($n >= 40) { echo "   ... dipangkas\n"; break; }
    }
    echo "\n";
}
