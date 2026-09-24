<?php
/**
 * Inventaris FORMAT dari 5 DOCX draft kontrak → storage/app/tmp-dump/style-report.md
 *
 * Dipakai sebagai baseline terukur untuk menyeragamkan tampilan kelima template
 * kontrak (alignment, ukuran font, spacing, indent, tipe numbering).
 *
 * Sumber: word/document.xml (+ word/styles.xml untuk docDefaults).
 * Nilai w:sz adalah half-point (24 = 12pt).
 *
 * Sekali jalan, read-only terhadap dokumen sumber.
 */

$src = 'C:/Users/ACER/OneDrive/Documents/AD';
$out = __DIR__ . '/storage/app/tmp-dump/style-report.md';

$files = [
    'kemitraan'       => 'Draft Kontrak Kemitraan.docx',
    'colocation'      => 'Draft Kontrak layanan Colocation.docx',
    'managed-service' => 'Draft Kontrak layanan Dedicated, Metro, Managed Service.docx',
    'soho'            => 'Draft Kontrak layanan Soho.docx',
    'payung'          => 'Draft Kontrak Payung layanan Dedicated, Metro, Managed Service.docx',
];

if (!class_exists('ZipArchive')) {
    fwrite(STDERR, "ZipArchive tidak tersedia.\n");
    exit(1);
}

/** Baca entri zip menjadi string. */
function zipEntry(string $zipPath, string $entry): ?string
{
    $zip = new ZipArchive();
    if ($zip->open($zipPath) !== true) {
        return null;
    }
    $data = $zip->getFromName($entry);
    $zip->close();

    return $data === false ? null : $data;
}

/** Teks visibel satu paragraf <w:p>. */
function paragraphText(string $xml): string
{
    $out = '';
    if (preg_match_all('/<w:t[^>]*>([^<]*)<\/w:t>/', $xml, $m)) {
        foreach ($m[1] as $t) {
            $out .= html_entity_decode($t, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
    }

    return trim(preg_replace('/\s+/u', ' ', $out));
}

/** Nilai atribut pertama yang cocok. */
function attr(string $xml, string $pattern): ?string
{
    return preg_match($pattern, $xml, $m) ? $m[1] : null;
}

$lines = [];
$lines[] = '# Inventaris Format DOCX Draft Kontrak';
$lines[] = '';
$lines[] = 'Dibuat: ' . date('Y-m-d H:i:s');
$lines[] = '';

$summary = [];

foreach ($files as $key => $name) {
    $path = $src . '/' . $name;

    $lines[] = '## ' . $key;
    $lines[] = '';

    if (!is_file($path)) {
        $lines[] = '- [MISS] ' . $name;
        $lines[] = '';
        continue;
    }

    $docXml  = zipEntry($path, 'word/document.xml');
    $styXml  = zipEntry($path, 'word/styles.xml');

    $defaults = '(tidak ada docDefaults)';
    if ($styXml && preg_match('/(?s)<w:docDefaults>.*?<\/w:docDefaults>/', $styXml, $dd)) {
        $blk = $dd[0];
        $sz  = attr($blk, '/w:sz w:val="(\d+)"/');
        $rf  = attr($blk, '/<w:rFonts ([^>]*)\/>/');
        $sp  = attr($blk, '/<w:spacing ([^>]*)\/>/');
        $defaults = 'size='
            . ($sz !== null ? (((float) $sz) / 2) . 'pt' : '(inherit)')
            . '; font=' . ($rf ?? '-')
            . '; spacing=' . ($sp ?? '-');
    }
    $lines[] = '- docDefaults: ' . $defaults;

    if (!$docXml) {
        $lines[] = '- [MISS] word/document.xml';
        $lines[] = '';
        continue;
    }

    // Distribusi jc / sz / numFmt
    $tally = static function (string $xml, string $pattern): string {
        if (!preg_match_all($pattern, $xml, $m)) {
            return '(kosong)';
        }
        $counts = array_count_values($m[1]);
        arsort($counts);
        $parts = [];
        foreach ($counts as $val => $n) {
            $parts[] = $val . '=' . $n;
        }

        return implode(', ', $parts);
    };

    $lines[] = '- jc       : ' . $tally($docXml, '/w:jc w:val="([a-zA-Z]+)"/');
    $lines[] = '- sz (pt)  : ' . $tally($docXml, '/w:sz w:val="(\d+)"/') . ' (half-point)';
    $lines[] = '- numId    : ' . $tally($docXml, '/w:numId w:val="(\d+)"/');
    $lines[] = '- numFmt   : ' . $tally($docXml, '/w:numFmt w:val="([a-zA-Z]+)"/');

    // Paragraf penting: heading pasal/lampiran, ukuran, alignment
    $lines[] = '';
    $lines[] = '| # | jc | sz | spacing | text |';
    $lines[] = '|---|----|----|---------|------|';

    preg_match_all('/(?s)<w:p\b.*?<\/w:p>/', $docXml, $ps);
    $shown = 0;
    $i     = 0;

    foreach ($ps[0] as $p) {
        $i++;
        $text = paragraphText($p);
        if ($text === '') {
            continue;
        }

        $isHeading = preg_match('/^(PASAL|Pasal|LAMPIRAN|Lampiran|DEFINISI|Definisi|BAB)\b/u', $text) === 1
            || stripos($text, 'PERJANJIAN') === 0;

        if (!$isHeading) {
            continue;
        }

        $jc  = attr($p, '/w:jc w:val="([a-zA-Z]+)"/') ?? 'default';
        $sz  = attr($p, '/w:sz w:val="(\d+)"/');
        $szv = $sz !== null ? (((float) $sz) / 2) . 'pt' : '-';
        $spc = attr($p, '/<w:spacing ([^>]*)\/>/') ?? '-';

        $lines[] = '| ' . $i . ' | ' . $jc . ' | ' . $szv . ' | ' . $spc . ' | '
            . mb_substr($text, 0, 60) . ' |';
        $shown++;

        if ($shown >= 24) {
            break;
        }
    }

    if ($shown === 0) {
        $lines[] = '| - | - | - | - | (tidak ada heading terdeteksi) |';
    }

    $summary[$key] = $defaults;
    $lines[] = '';
}

$lines[] = '## Ringkasan docDefaults';
$lines[] = '';
foreach ($summary as $key => $value) {
    $lines[] = '- ' . $key . ': ' . $value;
}

$report = implode("\n", $lines) . "\n";
file_put_contents($out, $report);

echo "style-report.md ditulis (" . strlen($report) . " bytes)\n";
echo "lokasi: " . $out . "\n";
