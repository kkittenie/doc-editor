<?php
// Perbaiki posisi JUDUL pasal yang nyasar ke dalam section lain:
// pindahkan blok judul ke posisi #2 di section yang benar.
// Aman: hanya memindahkan blok <p><strong>JUDUL</strong></p> yang persis
// sama dengan judul template; blok lain tidak disentuh.
require __DIR__.'/vendor/autoload.php';

use App\Data\ContractTemplates;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

$app = require __DIR__.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
require __DIR__.'/_repair_v2_part1.php'; // v2_norm/split/is_heading/is_titleline

$apply = in_array('--apply', $argv ?? [], true);
$ids = [169, 173, 184];

echo $apply ? "MODE: APPLY\n\n" : "MODE: DRY-RUN\n\n";
foreach ($ids as $id) {
    $row = DB::table('documents')->where('id', $id)->first();
    if (!$row) { echo "== DOC $id TIDAK ADA\n\n"; continue; }
    $body = json_decode((string) $row->body_content, true);
    $pages = $body['pages'] ?? [];

    $tpl = ContractTemplates::find('kontrak-kemitraan');
    $juduls = [];
    foreach ($tpl['body_content']['isi'] as $p) $juduls[] = $p['judul'] ?? '';
    $jNorm = array_map(function ($j) { return mb_strtolower(v2_norm($j)); }, $juduls);

    // Section per heading.
    $blocks = [];
    foreach ($pages as $pg) {
        foreach (v2_split_blocks($pg) as $b) $blocks[] = $b;
    }
    $firstHead = null;
    foreach ($blocks as $bi => $b) {
        $nn = null;
        if (in_array($b['tag'], ['p','h1','h2','h3','h4','h5','h6'], true) && v2_is_pasal_heading($b['text'], $nn)) {
            $firstHead = $bi;
            break;
        }
    }
    if ($firstHead === null) { echo "== DOC $id tanpa heading\n\n"; continue; }

    echo "== DOC $id\n";
    // Untuk tiap pasal n: pastikan blok #2 section-n adalah judul-n.
    // Cari blok judul-n di mana pun dalam area pasal; pindahkan ke #2.
    // Area pasal = dari heading pertama s.d. blok penutup/lampiran.
    $endHead = count($blocks);
    foreach ($blocks as $bi => $b) {
        if ($bi <= $firstHead) continue;
        $t = $b['text'];
        if (preg_match('/^(hormat kami|tertanda|ditandatangani di|mengetahui|tanda tangan|lampiran\b)/iu', $t)
            || preg_match('/(dibuat rangkap|materai cukup|demikian perjanjian ini)/iu', $t)) {
            $endHead = $bi;
            break;
        }
    }

    $moves = 0;
    for ($n = 1; $n <= count($juduls); $n++) {
        $want = $jNorm[$n - 1];
        if ($want === '') continue;
        // 1) Lokasi heading PASAL n.
        $hIdx = null;
        $seen = 0;
        foreach ($blocks as $bi => $b) {
            $nn = null;
            if ($bi < $firstHead || $bi >= $endHead) continue;
            if (in_array($b['tag'], ['p','h1','h2','h3','h4','h5','h6'], true) && v2_is_pasal_heading($b['text'], $nn)) {
                $seen++;
                if ($seen === $n) { $hIdx = $bi; break; }
            }
        }
        if ($hIdx === null) { echo "   pasal $n: heading tak ketemu\n"; continue; }
        // 2) Apakah blok tepat setelah heading sudah judul benar?
        $next = $blocks[$hIdx + 1] ?? null;
        if ($next && mb_strtolower($next['text']) === $want) continue; // sudah benar
        // 3) Cari blok judul-n di area pasal (bukan heading), ambil kemunculan pertama.
        // Fallback: bila tak ketemu di area pasal (kasus judul-18 yang
        // tercecer di area lampiran), cari di SELURUH blok dokumen.
        $found = null;
        foreach ($blocks as $bi => $b) {
            if ($bi < $firstHead || $bi >= $endHead || $bi === $hIdx + 1) continue;
            $nn2 = null;
            if (in_array($b['tag'], ['p','h1','h2','h3','h4','h5','h6'], true) && v2_is_pasal_heading($b['text'], $nn2)) continue;
            if (!v2_is_titleline($b)) continue;
            if (mb_strtolower($b['text']) === $want) { $found = $bi; break; }
        }
        if ($found === null) {
            foreach ($blocks as $bi => $b) {
                if ($bi === $hIdx + 1) continue;
                $nn2 = null;
                if (in_array($b['tag'], ['p','h1','h2','h3','h4','h5','h6'], true) && v2_is_pasal_heading($b['text'], $nn2)) continue;
                if (!v2_is_titleline($b)) continue;
                if (mb_strtolower($b['text']) === $want) { $found = $bi; break; }
            }
        }
        if ($found === null) { echo "   pasal $n: judul tidak ditemukan di area pasal\n"; continue; }
        // 4) Pindahkan: hapus dari posisi lama, sisipkan tepat setelah heading.
        $blk = $blocks[$found];
        array_splice($blocks, $found, 1);
        $hIdx2 = null;
        $seen2 = 0;
        foreach ($blocks as $bi => $b) {
            $nn3 = null;
            if (in_array($b['tag'], ['p','h1','h2','h3','h4','h5','h6'], true) && v2_is_pasal_heading($b['text'], $nn3)) {
                $seen2++;
                if ($seen2 === $n) { $hIdx2 = $bi; break; }
            }
        }
        array_splice($blocks, $hIdx2 + 1, 0, [$blk]);
        // endHead bergeser bila found < hIdx2? splice hapus lalu sisip: hitung ulang sederhana
        $moves++;
        echo '   pasal '.$n.': pindah judul dari blok '.$found.' ke setelah heading (blok '.$hIdx2.")\n";
    }
    echo "   total pindah: $moves\n\n";

    if (!$apply || $moves === 0) continue;
    // Rakit ulang pages: page-0 (sampul) utuh; body = semua blok setelahnya.
    // Cari batas blok milik page-0: blok page-0 = hasil split pages[0] asli.
    $page0count = count(v2_split_blocks($pages[0]));
    $page0blocks = array_slice($blocks, 0, $page0count);
    $restblocks = array_slice($blocks, $page0count);
    $toHtml = function ($arr) {
        return implode("\n", array_map(function ($b) { return $b['html']; }, $arr));
    };
    $newPages = [$toHtml($page0blocks), $toHtml($restblocks)];
    $body['pages'] = $newPages;
    DB::table('documents')->where('id', $id)->update([
        'body_content' => json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
    ]);
    echo "   DITULIS doc $id (sampul ".count($page0blocks)." blok dipertahankan)\n\n";
}
echo "Selesai.\n";
