<?php
// Repair v2 — bagian 3/3: susun ulang + verifikasi + tulis.
// Input: $ordered-sections ($sections+$assign), $preBlocks, $postBlocks,
//        $tplJudul, $nPasal, $secBlocks. Output: $newBodyHtml, $errs (array).
$gotVals = array_values($assign);
sort($gotVals);
$errs = [];
if ($gotVals !== range(1, $nPasal)) {
    $errs[] = 'pemetaan tak lengkap: dapat ['.implode(',', $gotVals).'] vs perlu [1..'.$nPasal.']';
}

$orderIdx = array_keys($sections);
usort($orderIdx, function ($a, $b) use ($assign) { return $assign[$a] <=> $assign[$b]; });

$newHtml = [];
foreach ($preBlocks as $b) $newHtml[] = $b['html'];
$inCount = count($secBlocks);
$outCount = 0;
foreach ($orderIdx as $si) {
    $s = $sections[$si];
    $n = $assign[$si];
    $newHtml[] = '<p style="text-align:center; margin-top:30px;"><strong>PASAL '.$n.'</strong></p>';
    $outCount++;
    if (($tplJudul[$n - 1] ?? '') !== '') {
        $newHtml[] = '<p class="ql-align-center"><strong>'.$tplJudul[$n - 1].'</strong></p>';
        $outCount++;
    }
    $skipTitle = (($s['blocks'][1] ?? null) && v2_is_titleline($s['blocks'][1]));
    foreach (array_slice($s['blocks'], 1) as $k => $b) {
        if ($k === 0 && $skipTitle) continue; // judul lama diganti judul template
        $nn2 = null;
        if (in_array($b['tag'], ['p','h1','h2','h3','h4','h5','h6'], true) && v2_is_pasal_heading($b['text'], $nn2)) continue;
        $newHtml[] = $b['html'];
        $outCount++;
    }
}
foreach ($postBlocks as $b) $newHtml[] = $b['html'];

// Konservasi blok isi (non-heading): keluar + judul-diganti harus == masuk.
$nonHeadIn = 0;
foreach ($secBlocks as $b) {
    $nn4 = null;
    if (in_array($b['tag'], ['p','h1','h2','h3','h4','h5','h6'], true) && v2_is_pasal_heading($b['text'], $nn4)) continue;
    $nonHeadIn++;
}
$emitted = 0;
for ($n = 1; $n <= $nPasal; $n++) $emitted += (($tplJudul[$n - 1] ?? '') !== '' ? 2 : 1);
$nonHeadOut = $outCount - $emitted;
$droppedTitles = 0;
foreach ($sections as $s) {
    $tb = $s['blocks'][1] ?? null;
    if ($tb && v2_is_titleline($tb)) $droppedTitles++;
}
echo '   blok isi: masuk='.$nonHeadIn.' keluar='.$nonHeadOut.' judul-diganti='.$droppedTitles."\n";
if (($nonHeadOut + $droppedTitles) !== $nonHeadIn) {
    $errs[] = 'blok isi tidak konservasi (masuk '.$nonHeadIn.' vs keluar '.$nonHeadOut.' + judul '.$droppedTitles.')';
}
