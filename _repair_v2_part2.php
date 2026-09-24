<?php
// Repair v2 — bagian 2/3: identifikasi section -> nomor pasal template.
// Input: $sections, $tplIsi, $tplJudul. Output: $assign, $used.
$tplJudul = [];
foreach ($tplIsi as $p) $tplJudul[] = $p['judul'] ?? '';
$tplJudulNorm = array_map(function ($j) { return mb_strtolower(v2_norm($j)); }, $tplJudul);

// Korpus teks template per pasal (untuk voting konten).
$tplIsiTexts = [];
foreach ($tplIsi as $pi => $p) {
    $cc = [];
    v2_collect_tpl($p, $cc);
    $tplIsiTexts[$pi] = $cc;
}
$exactPasal = [];
foreach ($tplIsiTexts as $pi => $cc) {
    foreach ($cc as $ct) {
        if (!isset($exactPasal[$ct])) $exactPasal[$ct] = $pi + 1;
    }
}

$used = [];
$assign = [];
foreach ($sections as $si => $s) {
    $titleBlk = $s['blocks'][1] ?? null;
    $titleTxt = ($titleBlk && v2_is_titleline($titleBlk)) ? mb_strtolower(v2_norm($titleBlk['text'])) : '';
    $byTitle = null;
    if ($titleTxt !== '') {
        foreach ($tplJudulNorm as $pi => $tj) {
            if ($tj !== '' && $titleTxt === $tj) { $byTitle = $pi + 1; break; }
        }
        if ($byTitle === null) {
            foreach ($tplJudulNorm as $pi => $tj) {
                if ($tj !== '' && (str_contains($tj, $titleTxt) || str_contains($titleTxt, $tj))) {
                    $byTitle = $pi + 1;
                    break;
                }
            }
        }
    }
    // Voting konten: blok panjang (>=25 char) non-heading/non-judul.
    $votePasal = [];
    foreach ($s['blocks'] as $bi2 => $b) {
        if ($bi2 === 0) continue;
        if ($bi2 === 1 && $titleTxt !== '') continue;
        $t = $b['text'];
        if (mb_strlen($t) < 25) continue;
        $nn = null;
        if (in_array($b['tag'], ['p','h1','h2','h3','h4','h5','h6'], true) && v2_is_pasal_heading($t, $nn)) continue;
        if (isset($exactPasal[$t])) { $votePasal[] = $exactPasal[$t]; continue; }
        $hit = null;
        foreach ($tplIsiTexts as $pi => $cc) {
            foreach ($cc as $ct) {
                if ($ct === '') continue;
                if (mb_stripos($ct, $t, 0, 'UTF-8') !== false || mb_stripos($t, $ct, 0, 'UTF-8') !== false) {
                    $hit = $pi + 1;
                    break 2;
                }
            }
        }
        if ($hit !== null) $votePasal[] = $hit;
    }
    $byContent = null;
    if ($votePasal) {
        $cnt = array_count_values($votePasal);
        arsort($cnt);
        $byContent = (int) array_key_first($cnt);
    }
    $chosen = $byTitle ?? $byContent ?? $s['num'];
    if (isset($used[$chosen])) {
        if ($byTitle !== null && !isset($used[$byTitle])) {
            $chosen = $byTitle;
        } elseif ($byContent !== null && !isset($used[$byContent])) {
            $chosen = $byContent;
        } else {
            $found = null;
            if ($titleTxt !== '') {
                $best = null;
                $bestPct = 0;
                foreach ($tplJudulNorm as $pi => $tj) {
                    if (isset($used[$pi + 1]) || $tj === '') continue;
                    similar_text($titleTxt, $tj, $pct);
                    if ($pct > $bestPct) { $bestPct = $pct; $best = $pi + 1; }
                }
                if ($bestPct >= 50) $found = $best;
            }
            if ($found === null) {
                foreach (range(1, $nPasal) as $cand) {
                    if (!isset($used[$cand])) { $found = $cand; break; }
                }
            }
            $chosen = $found ?? $s['num'];
        }
    }
    $used[$chosen] = true;
    $assign[$si] = $chosen;
    $jt = $s['blocks'][1]['text'] ?? '(tanpa judul)';
    echo '   section['.$s['num'].'] "'.mb_substr($jt, 0, 50).'" -> pasal '.$chosen
        .($byTitle ? ' (judul)' : ($byContent ? ' (konten:'.$byContent.')' : ' (fallback)'))."\n";
}
