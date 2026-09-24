<?php
// Dump potongan HTML di sekitar tiap heading PASAL doc 169 untuk diagnosa.
require __DIR__.'/vendor/autoload.php';

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

$app = require __DIR__.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$id = (int) ($argv[1] ?? 169);
$row = DB::table('documents')->where('id', $id)->first();
$body = json_decode((string) $row->body_content, true);
$html = implode("\n", $body['pages'] ?? []);

// Tandai posisi tiap heading PASAL
preg_match_all('/<p\b[^>]*>.*?pasal(?:\s|\x{00A0}|&nbsp;|&#160;)*\d+.*?<\/p>/is', $html, $mm, PREG_OFFSET_CAPTURE);
echo 'ditemukan '.count($mm[0])." heading kandidat\n\n";
foreach ($mm[0] as $i => $m) {
    $pos = $m[1];
    $frag = substr($html, $pos, 900);
    $frag = preg_replace('/\s+/', ' ', $frag);
    echo '--- HEADING #'.($i + 1)." @ $pos ---\n";
    echo mb_substr($frag, 0, 700)."\n\n";
    if ($i >= 9) { echo "... (dipangkas, 10 pertama saja)\n"; break; }
}
