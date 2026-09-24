<?php
// Tampilkan blok 179-199 doc 173: apa isi PASAL 18 sebelum LAMPIRAN IV?
require __DIR__.'/vendor/autoload.php';

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

$app = require __DIR__.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
require __DIR__.'/_repair_v2_part1.php';

$id = (int) ($argv[1] ?? 173);
$a = (int) ($argv[2] ?? 179);
$c = (int) ($argv[3] ?? 200);
$row = DB::table('documents')->where('id', $id)->first();
$body = json_decode((string) $row->body_content, true);
$blocks = [];
foreach (($body['pages'] ?? []) as $pg) {
    foreach (v2_split_blocks($pg) as $b) $blocks[] = $b;
}
for ($bi = $a; $bi <= $c && $bi < count($blocks); $bi++) {
    $b = $blocks[$bi];
    echo "[$bi][{$b['tag']}] \"".mb_substr($b['text'], 0, 100)."\"\n";
}
