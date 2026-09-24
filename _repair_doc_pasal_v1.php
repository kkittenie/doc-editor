<?php
/**
 * Susun ulang isi pasal dokumen lama agar urut sesuai template (PASAL 1..N).
 *
 * Prinsip: teks TIDAK diubah & TIDAK digenerate ulang — hanya urutan blok
 * disusun ulang mengikuti urutan template hasil render, heading PASAL di-emit
 * ulang dari template (penomoran pasti 1..N), lalu diverifikasi.
 *
 * Pakai :
 *   php _repair_doc_pasal.php            Dry-run: laporan saja, DB tidak disentuh.
 *   php _repair_doc_pasal.php --apply    Backup dulu ke storage/app/repair-backup,
 *                                        lalu tulis urutan baru.
 *
 * Dokumen yang sudah URUT otomatis dilewati (idempoten).
 */
require __DIR__ . '/vendor/autoload.php';

use App\Data\ContractTemplates;
use App\Http\Controllers\DocumentController;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$apply = in_array('--apply', $argv, true);

/** Normalisasi teks utk pembanding: decode entity, buang tag, rapikan spasi. */
function rt_norm($s)
{
    $s = html_entity_decode((string)$s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $s = strip_tags($s);
    $s = preg_replace('/[\x{00A0}\s]+/u', ' ', $s);
    return trim($s);
}

/** Kumpulkan seluruh string di dalam struktur template (kecuali judul). */
function rt_collect($x, &$out)
{
    if (is_array($x)) {
        foreach ($x as $k => $v) {
            if ($k === 'judul' || $k === 'title' || $k === 'heading') {
                continue;
            }
            rt_collect($v, $out);
        }
    } elseif (is_string($x)) {
        $t = rt_norm($x);
        if ($t !== '') {
            $out[] = $t;
        }
    }
}

/** Deteksi key template dari kata penanda isi/judul dokumen. */
function rt_detect_key($src)
{
    $map = [
        'JASA COLOCATION'                        => 'kontrak-colocation',
        'JASA MANAGED SERVICE'                   => 'kontrak-managed-service',
        'JASA SOHO'                              => 'kontrak-soho',
        'KONTRAK PAYUNG'                         => 'kontrak-payung',
        'JUAL KEMBALI JASA LAYANAN AKSES INTERNET' => 'kontrak-kemitraan',
        'Dedicated, Metro'                       => 'kontrak-managed-service',
        'EMBEGE'                                 => 'kontrak-managed-service',
    ];
    foreach ($map as $needle => $key) {
        if (str_contains($src, $needle)) {
            return $key;
        }
    }
    return null;
}

/**
 * Belah HTML fragment jadi blok top-level: <p>/<h*>/<ol>/<ul>/<table>.
 * Teks polos di antara tag ikut jadi blok ('raw') supaya tidak ada yang hilang.
 *
 * @return array<int, array{tag: string, html: string, text: string}>
 */
function rt_split_blocks($html)
{
    $blocks = [];
    $pos = 0;
    $len = strlen($html);
    while ($pos < $len) {
        if (!preg_match('/<(p|h[1-6]|ol|ul|table)\b/i', $html, $m, PREG_OFFSET_CAPTURE, $pos)) {
            $rest = substr($html, $pos);
            if (trim(strip_tags($rest)) !== '') {
                $blocks[] = ['tag' => 'raw', 'html' => $rest, 'text' => rt_norm($rest)];
            }
            break;
        }
        $tag = strtolower($m[1][0]);
        $start = $m[0][1];

        $inter = substr($html, $pos, $start - $pos);
        if (trim(strip_tags($inter)) !== '' || preg_match('/<(hr|img)/i', $inter)) {
            $blocks[] = ['tag' => 'raw', 'html' => $inter, 'text' => rt_norm($inter)];
        }

        if ($tag === 'p' || $tag[0] === 'h') {
            $close = stripos($html, '</' . $tag . '>', $start);
            if ($close === false) {
                break;
            }
            $end = $close + strlen('</' . $tag . '>');
        } else {
            preg_match_all('/<' . $tag . '\b/i', $html, $os, PREG_OFFSET_CAPTURE, $start);
            preg_match_all('/<\/' . $tag . '>/i', $html, $cs, PREG_OFFSET_CAPTURE, $start);
            $tokens = [];
            foreach ($os[0] as $t) {
                $tokens[] = [$t[1], 1];
            }
            foreach ($cs[0] as $t) {
                $tokens[] = [$t[1], 0];
            }
            usort($tokens, function ($a, $b) {
                if ($a[0] === $b[0]) {
                    return 0;
                }
                return $a[0] < $b[0] ? -1 : 1;
            });
            $depth = 0;
            $end = null;
            foreach ($tokens as $t) {
                if ($t[0] < $start) {
                    continue;
                }
                if ($t[1] === 1) {
                    $depth++;
                } else {
                    $depth--;
                    if ($depth === 0) {
                        $gt = strpos(substr($html, $t[0]), '>');
                        $end = $t[0] + $gt + 1;
                        break;
                    }
                }
            }
            if ($end === null) {
                $end = $len;
            }
        }

        $frag = substr($html, $start, $end - $start);
        $blocks[] = ['tag' => $tag, 'html' => $frag, 'text' => rt_norm($frag)];
        $pos = $end;
    }
    return $blocks;
}

/**
 * Render HTML template (urutan kanonik) lalu kembalikan + indeks heading PASAL.
 *
 * @return array<int, array{tag: string, html: string, text: string}>
 */
function rt_template_blocks($tplKey, &$headingIdx)
{
    static $ctrl = null;
    static $mBuild = null;
    static $mNorm = null;
    if ($ctrl === null) {
        $ctrl = new DocumentController();
        $mBuild = new ReflectionMethod(DocumentController::class, 'buildTemplateBodyHtml');
        $mBuild->setAccessible(true);
        $mNorm = new ReflectionMethod(DocumentController::class, 'normalizePasalNumbering');
        $mNorm->setAccessible(true);
    }
    $tpl = ContractTemplates::find($tplKey);
    if (!$tpl) {
        return [];
    }
    $html = $mBuild->invoke($ctrl, $tpl['body_content'] ?? [], true);
    $html = $mNorm->invoke($ctrl, [$html]);
    if (is_array($html)) {
        $html = $html[0];
    }
    $blocks = rt_split_blocks((string)$html);
    $headingIdx = [];
    foreach ($blocks as $i => $b) {
        if (preg_match('/^PASAL\s+(\d+)$/i', $b['text'], $m)) {
            $headingIdx[(int)$m[1]] = $i;
        }
    }
    ksort($headingIdx);
    return $blocks;
}

/** Posisi teks tiap pasal template di dokumen (fingerprint isi) → urutan aktual. */
function rt_fingerprint_order($fullText, $tplKey)
{
    $tpl = ContractTemplates::find($tplKey);
    $low = mb_strtolower(rt_norm($fullText));
    $found = [];
    $missing = [];
    $isi = $tpl['body_content']['isi'] ?? [];
    foreach ($isi as $idx => $item) {
        $k = $idx + 1;
        $parts = [];
        rt_collect($item, $parts);
        $pos = -1;
        foreach (array_slice($parts, 0, 5) as $p) {
            $fp = mb_substr(mb_strtolower($p), 0, 55);
            if (mb_strlen($fp) < 20) {
                continue;
            }
            $pp = mb_strpos($low, $fp);
            if ($pp !== false) {
                $pos = $pp;
                break;
            }
        }
        if ($pos === -1 && is_array($item)) {
            $j = '';
            foreach (['judul', 'title', 'heading'] as $jk) {
                if (!empty($item[$jk])) {
                    $j = rt_norm($item[$jk]);
                    break;
                }
            }
            if ($j !== '') {
                $pj = mb_strpos($low, mb_strtolower($j));
                if ($pj !== false) {
                    $pos = $pj;
                }
            }
        }
        if ($pos === -1) {
            $missing[] = $k;
        } else {
            $found[] = [$pos, $k];
        }
    }
    usort($found, function ($a, $b) {
        if ($a[0] === $b[0]) {
            return 0;
        }
        return $a[0] < $b[0] ? -1 : 1;
    });
    $seq = [];
    foreach ($found as $f) {
        $seq[] = $f[1];
    }
    return [$seq, $missing];
}

/** Urutan nomor heading PASAL pada HTML (cek utama: pasal harus 1..N). */
function rt_heading_seq($html)
{
    preg_match_all('/<p[^>]*>\s*<strong>PASAL\s+(\d+)<\/strong>\s*<\/p>/iu', (string)$html, $m);
    return array_map('intval', $m[1]);
}

/**
 * Peta blok dokumen → indeks blok template: exact > fuzzy (similar_text >= 35%).
 * null = wildcard (ikut tetangga terakhir, tidak hilang).
 *
 * @return array{0: ?int, 1: string}
 */
function rt_match_indices($text, $tplTexts, $exactMap)
{
    $t = mb_strtolower($text);
    if ($t !== '' && isset($exactMap[$t])) {
        return [$exactMap[$t], 'exact'];
    }
    if (mb_strlen($t) < 8) {
        return [null, 'wildcard'];
    }
    $probe = mb_substr($t, 0, 160);
    $best = -1;
    $bestPct = 0.0;
    foreach ($tplTexts as $i => $pt) {
        similar_text($probe, mb_substr(mb_strtolower($pt), 0, 160), $pct);
        if ($pct > $bestPct) {
            $bestPct = $pct;
            $best = $i;
        }
    }
    if ($bestPct >= 35) {
        return [$best, 'fuzzy' . round($bestPct)];
    }
    return [null, 'wildcard'];
}

/* --------------------------------------------------------------- */

$ids = [80, 167, 169, 173, 174, 178, 180, 184, 186];
$backupDir = __DIR__ . '/storage/app/repair-backup';
$plan = [];

echo 'MODE: ' . ($apply ? 'APPLY (backup lalu tulis)' : 'DRY-RUN (tidak menulis apa pun)') . "\n\n";

foreach ($ids as $id) {
    $row = DB::table('documents')->where('id', $id)->first();
    if (!$row) {
        echo "== DOC $id: TIDAK ADA\n\n";
        continue;
    }
    $body = json_decode((string)$row->body_content, true);
    if (!is_array($body) || !isset($body['pages'])) {
        echo "== DOC $id: body_content tidak terbaca\n\n";
        continue;
    }
    $pages = $body['pages'];
    $cover = (int)($body['coverPages'] ?? 0);
    $bodyPages = array_slice($pages, $cover);
    $origBody = implode("\n", $bodyPages);

    $tplKey = $body['templateKey'] ?? null;
    if (!$tplKey || !ContractTemplates::find($tplKey)) {
        $tplKey = rt_detect_key($row->title . "\n" . $origBody);
    }

    echo '== DOC ' . $id . ' | ' . mb_substr((string)$row->title, 0, 60)
        . ' | status=' . $row->status . "\n";
    if (!$tplKey) {
        echo "   template: TIDAK TERDETEKSI → LOMPATI (perlu manual)\n\n";
        continue;
    }
    echo '   template: ' . $tplKey . ' | halaman=' . count($pages) . " (sampul=$cover)\n";

    [$seqBefore, $missBefore] = rt_fingerprint_order($origBody, $tplKey);
    $isi = ContractTemplates::find($tplKey)['body_content']['isi'] ?? [];
    $expected = range(1, count($isi));
    $beforeOk = ($seqBefore === $expected && !$missBefore);
    echo '   SEBELUM: fingerprint=' . implode(',', $seqBefore)
        . ($missBefore ? ' | HILANG: ' . implode(',', $missBefore) : '')
        . ' → ' . ($beforeOk ? 'URUT (dilewati)' : 'TERBUKAR') . "\n";

    if ($beforeOk) {
        continue;
    }

    $headingIdx = [];
    $tplBlocks = rt_template_blocks($tplKey, $headingIdx);
    if (!$tplBlocks || !$headingIdx) {
        echo "   ERROR: render template gagal → LOMPATI\n\n";
        continue;
    }
    $tplTexts = [];
    foreach ($tplBlocks as $b) {
        $tplTexts[] = $b['text'];
    }
    $exactMap = [];
    foreach ($tplTexts as $i => $t) {
        $lt = mb_strtolower($t);
        if ($lt !== '' && !isset($exactMap[$lt])) {
            $exactMap[$lt] = $i;
        }
    }

    $docBlocks = rt_split_blocks($origBody);

    // Kelasifikasi: heading PASAL dibuang (di-emit ulang dari template),
    // sisanya blok isi. Heading menempel ("PASAL 5Judul...") dibiarkan utuh.
    $contentBlocks = [];
    $droppedHeadings = 0;
    $gluedHeadings = [];
    foreach ($docBlocks as $pos => $b) {
        $t = $b['text'];
        if (preg_match('/^PASAL\s+\d+$/i', $t)) {
            $droppedHeadings++;
            continue;
        }
        if (preg_match('/^PASAL\s+\d+/i', $t)) {
            $gluedHeadings[] = mb_substr($t, 0, 50);
        }
        $contentBlocks[] = ['pos' => $pos, 'block' => $b, 'anchor' => null, 'src' => ''];
    }
    if ($gluedHeadings) {
        echo '   WARNING heading menempel (ikut sebagai isi): '
            . implode(' | ', $gluedHeadings) . "\n";
    }

    // Anchor monotone greedy: kandidat terkecil >= anchor terakhir.
    // exact boleh menurunkan anchor (koreksi overshot fuzzy); fuzzy tak boleh mundur.
    $last = -1;
    $stat = ['exact' => 0, 'fuzzy' => 0, 'wildcard' => 0];
    foreach ($contentBlocks as &$cb) {
        [$idx, $src] = rt_match_indices($cb['block']['text'], $tplTexts, $exactMap);
        if ($idx === null) {
            $cb['anchor'] = $last;             // ikut tetangga terakhir (tidak hilang)
            $cb['src'] = 'wildcard';
            $stat['wildcard']++;
            continue;
        }
        $isFuzzy = str_starts_with($src, 'fuzzy');
        if ($isFuzzy && $idx < $last) {
            $idx = $last;
        }
        $cb['anchor'] = $idx;
        if ($isFuzzy) {
            $cb['src'] = 'fuzzy';
            $stat['fuzzy']++;
        } else {
            $cb['src'] = 'exact';
            $stat['exact']++;
        }
        if ($idx > $last || !$isFuzzy) {
            $last = $idx;
        }
    }
    unset($cb);

    // Kelompokkan: pembuka (sebelum heading PASAL 1) | pasal 1..N | ekor.
    $hSeq = array_values($headingIdx);
    $nPasal = count($hSeq);
    $buckets = [0 => []];
    for ($n = 1; $n <= $nPasal; $n++) {
        $buckets[$n] = [];
    }
    $buckets['tail'] = [];
    foreach ($contentBlocks as $cb) {
        $a = $cb['anchor'];
        if ($a !== null && $a < $hSeq[0]) {
            $buckets[0][] = $cb;
            continue;
        }
        $placed = false;
        for ($n = 1; $n <= $nPasal; $n++) {
            $hi = $hSeq[$n - 1];
            $next = ($n < $nPasal) ? $hSeq[$n] : PHP_INT_MAX;
            if ($a !== null && $a >= $hi && $a < $next) {
                $buckets[$n][] = $cb;
                $placed = true;
                break;
            }
        }
        if (!$placed) {
            $buckets['tail'][] = $cb;
        }
    }

    $emptySections = [];
    for ($n = 1; $n <= $nPasal; $n++) {
        if (!$buckets[$n]) {
            $emptySections[] = $n;
        }
    }
    $inCount = count($contentBlocks);

    // Emit ulang: heading template kanonik (pasti 1..N) + isi bucket
    // yang diurutkan menurut posisi aslinya (teks tidak disentuh).
    $out = [];
    foreach ($buckets[0] as $cb) {
        $out[] = $cb['block']['html'];
    }
    for ($n = 1; $n <= $nPasal; $n++) {
        $out[] = $tplBlocks[$hSeq[$n - 1]]['html'];
        usort($buckets[$n], function ($a, $b) {
            if ($a['pos'] === $b['pos']) {
                return 0;
            }
            return $a['pos'] < $b['pos'] ? -1 : 1;
        });
        foreach ($buckets[$n] as $cb) {
            $out[] = $cb['block']['html'];
        }
    }
    foreach ($buckets['tail'] as $cb) {
        $out[] = $cb['block']['html'];
    }
    $newBodyHtml = implode("\n", $out);

    // ---------- Verifikasi pasca-susun (semua harus lulus) ----------
    $errs = [];
    $outBlocks = rt_split_blocks($newBodyHtml);
    $outCount = 0;
    foreach ($outBlocks as $b) {
        if (!preg_match('/^PASAL\s+\d+$/i', $b['text'])) {
            $outCount++;
        }
    }
    if ($inCount !== $outCount) {
        $errs[] = "jumlah blok berubah: masuk=$inCount keluar=$outCount";
    }
    $headSeq = rt_heading_seq($newBodyHtml);
    $want = range(1, $nPasal);
    if ($headSeq !== $want) {
        $errs[] = 'heading tidak urut: ' . implode(',', $headSeq);
    }
    // Cek struktur fatal: judul tiap pasal harus berada DI dalam pasalnya.
    // Fingerprint isi TIDAK dipakai sbg syarat fatal — teks umum bisa muncul
    // lintas pasal sehingga posisi kemunculan pertamanya bisa menipu
    // (false-positive, terbukti pada doc 169/173/184).
    $judulSource = ContractTemplates::find($tplKey)['body_content']['isi'] ?? [];
    $titleMissing = [];
    for ($n = 1; $n <= $nPasal; $n++) {
        $jNorm = mb_strtolower(rt_norm($judulSource[$n - 1]['judul'] ?? ''));
        if ($jNorm === '') {
            continue;
        }
        $foundT = false;
        foreach ($buckets[$n] as $cb) {
            if (mb_strpos(mb_strtolower($cb['block']['text']), $jNorm) !== false) {
                $foundT = true;
                break;
            }
        }
        if (!$foundT) {
            $titleMissing[] = $n;
        }
    }
    if ($titleMissing) {
        $errs[] = 'judul pasal tidak ada di dalam pasalnya: ' . implode(',', $titleMissing);
    }
    [$seqAfter, $missAfter] = rt_fingerprint_order($newBodyHtml, $tplKey);
    $fpInfo = ($seqAfter === $expected && !$missAfter)
        ? 'persis 1..N'
        : implode(',', $seqAfter) . ' (INFORMATIF saja — bisa menipu utk teks umum lintas pasal)';
    if ($missAfter) {
        $fpInfo .= ' | tak ketemu: ' . implode(',', $missAfter);
    }
    if ($emptySections) {
        $errs[] = 'pasal kosong tanpa blok: ' . implode(',', $emptySections);
    }

    echo '   blok: masuk=' . $inCount . ' keluar=' . $outCount
        . " | heading dibuang=$droppedHeadings | anchor: exact={$stat['exact']}"
        . " fuzzy={$stat['fuzzy']} wildcard={$stat['wildcard']}\n";
    echo '   SESUDAH: heading=' . implode(',', $headSeq)
        . ' | fingerprint=' . $fpInfo . "\n";
    if ($errs) {
        echo "   VERIFIKASI: GAGAL\n";
        foreach ($errs as $e) {
            echo "      - $e\n";
        }
        // Trace: tampilkan isi awal tiap bucket pasal agar tahu section mana
        // yang kecampur (diagnosa dry-run; tidak menulis apa pun).
        for ($n = 1; $n <= $nPasal; $n++) {
            if (!$buckets[$n]) {
                echo "      [$n] (KOSONG)\n";
                continue;
            }
            $first = $buckets[$n][0]['block']['text'];
            $second = (count($buckets[$n]) > 1)
                ? $buckets[$n][1]['block']['text']
                : '';
            $jt = '';
            $jj = ContractTemplates::find($tplKey)['body_content']['isi'][$n - 1]['judul'] ?? '';
            $jNorm = mb_strtolower(rt_norm($jj));
            $hasTitle = false;
            foreach ($buckets[$n] as $cb) {
                if (mb_strtolower($cb['block']['text']) === $jNorm) {
                    $hasTitle = true;
                    break;
                }
            }
            $flag = $hasTitle ? 'OK' : 'JUDUL-HILANG';
            echo "      [$n] ($flag, " . count($buckets[$n]) . " blok) "
                . mb_substr($first, 0, 55) . ' || ' . mb_substr($second, 0, 45) . "\n";
        }
        echo "   → TIDAK ditulis (perlu review manual)\n\n";
        continue;
    }
    echo "   VERIFIKASI: LULUS → siap ditulis\n\n";

    $newBody = $body;
    $newBody['pages'] = array_merge(array_slice($pages, 0, $cover), [$newBodyHtml]);
    $plan[$id] = ['body' => $newBody, 'orig' => $body];

}

echo str_repeat('=', 64) . "\n";
echo 'RINGKASAN: ' . count($plan) . ' dokumen siap diubah, '
    . (count($ids) - count($plan)) . " dilewati.\n";

if (!$apply) {
    echo "MODE DRY-RUN — tidak ada data yang ditulis.\n";
    echo "Jalankan ulang dengan --apply untuk menulis hasilnya.\n";
    exit(0);
}

if (!$plan) {
    echo "Tidak ada yang perlu ditulis.\n";
    exit(0);
}

if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

foreach ($plan as $id => $item) {
    $backupFile = $backupDir . '/doc-' . $id . '.json';
    file_put_contents(
        $backupFile,
        json_encode($item['orig'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
    );

    DB::table('documents')->where('id', $id)->update([
        'body_content' => json_encode(
            $item['body'],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        ),
    ]);

    // Verifikasi ulang langsung dari DB setelah tulis.
    $fresh = DB::table('documents')->where('id', $id)->first();
    $check = json_decode((string)$fresh->body_content, true);
    $seq = rt_heading_seq(implode("\n", $check['pages'] ?? []));
    $ok = ($seq === range(1, count($seq)));
    echo "TULIS doc $id → backup storage/app/repair-backup/doc-$id.json | heading="
        . implode(',', $seq) . ' | ' . ($ok ? 'OK' : 'PERIKSA!') . "\n";
}

echo "Selesai. Pulihkan dengan: php _restore_doc_repair.php <id>\n";




