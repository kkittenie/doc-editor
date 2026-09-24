<?php
/**
 * Repair v2 — bagian 1/3: bootstrap + util + deteksi template.
 * (Disambung oleh _repair_v2_part2.php dan _repair_v2_part3.php via require.)
 */
require __DIR__.'/vendor/autoload.php';

use App\Data\ContractTemplates;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

$app = require __DIR__.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$apply = in_array('--apply', $argv ?? [], true);
$backupDir = __DIR__.'/storage/app/repair-backup';
$ids = [80, 167, 169, 173, 174, 178, 180, 184, 186];

function v2_norm($s)
{
    $s = html_entity_decode((string) $s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $s = strip_tags($s);
    $s = preg_replace('/[\x{00A0}\s]+/u', ' ', $s);
    return trim($s);
}
function v2_split_blocks($html)
{
    $blocks = [];
    $pos = 0;
    $len = strlen($html);
    while ($pos < $len) {
        if (!preg_match('/<(p|h[1-6]|ol|ul|table)\b/i', $html, $m, PREG_OFFSET_CAPTURE, $pos)) {
            $rest = substr($html, $pos);
            if (trim(strip_tags($rest)) !== '') {
                $blocks[] = ['tag' => 'raw', 'html' => $rest, 'text' => v2_norm($rest)];
            }
            break;
        }
        $tag = strtolower($m[1][0]);
        $start = $m[0][1];
        $inter = substr($html, $pos, $start - $pos);
        if (trim(strip_tags($inter)) !== '' || preg_match('/<(hr|img)/i', $inter)) {
            $blocks[] = ['tag' => 'raw', 'html' => $inter, 'text' => v2_norm($inter)];
        }
        if (!preg_match('/<'.$tag.'\b[^>]*>(.*?)<\/'.$tag.'>/is', $html, $mm, PREG_OFFSET_CAPTURE, $start)) {
            $blocks[] = ['tag' => 'raw', 'html' => substr($html, $start), 'text' => v2_norm(substr($html, $start))];
            break;
        }
        $full = $mm[0][0];
        $fpos = $mm[0][1];
        $blocks[] = ['tag' => $tag, 'html' => $full, 'text' => v2_norm($full)];
        $pos = $fpos + strlen($full);
    }
    return $blocks;
}
function v2_is_pasal_heading($text, &$num = null)
{
    if (preg_match('/^pasal(?:\s|\x{00A0}|&nbsp;|&#160;)*(\d+)\s*$/iu', trim($text), $m)) {
        $num = (int) $m[1];
        return true;
    }
    return false;
}
function v2_is_titleline($b)
{
    if (!in_array($b['tag'], ['p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'], true)) return false;
    if (strpos($b['html'], '<strong') === false) return false;
    return mb_strlen($b['text']) <= 80;
}
function v2_collect_tpl($x, &$out)
{
    if (is_array($x)) {
        foreach ($x as $k => $v) {
            if ($k === 'judul' || $k === 'title' || $k === 'heading') continue;
            v2_collect_tpl($v, $out);
        }
    } elseif (is_string($x)) {
        $t = v2_norm($x);
        if ($t !== '') $out[] = $t;
    }
}
function v2_fp($t) { return mb_substr($t, 0, 55); }
function v2_detect_key($head, $src)
{
    if (str_contains($head, 'JASA COLOCATION')) return 'kontrak-colocation';
    if (str_contains($head, 'JASA MANAGED SERVICE')) return 'kontrak-managed-service';
    if (str_contains($head, 'JASA SOHO')) return 'kontrak-soho';
    if (str_contains($head, 'KONTRAK PAYUNG')) return 'kontrak-payung';
    if (str_contains($head, 'JUAL KEMBALI JASA LAYANAN AKSES INTERNET')) return 'kontrak-kemitraan';
    $map = [
        'JASA COLOCATION' => 'kontrak-colocation',
        'JASA MANAGED SERVICE' => 'kontrak-managed-service',
        'JASA SOHO' => 'kontrak-soho',
        'KONTRAK PAYUNG' => 'kontrak-payung',
        'JUAL KEMBALI JASA LAYANAN AKSES INTERNET' => 'kontrak-kemitraan',
        'Dedicated, Metro' => 'kontrak-managed-service',
        'EMBEGE' => 'kontrak-managed-service',
    ];
    foreach ($map as $needle => $key) {
        if (str_contains($src, $needle)) return $key;
    }
    return null;
}
