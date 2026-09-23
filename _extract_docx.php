<?php
/**
 * Ekstrak struktur terpilih (paragraf bernilai + tabel) dari 5 DOCX draft kontrak
 * → storage/app/tmp-dump/docx/*.txt — dipakai sebagai pembanding struktur
 * (list/tabel) karena PDF hanya menghasilkan teks datar.
 * Pakai phpoffice/phpword (sudah ada di composer). Sekali jalan.
 */
require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Element\AbstractElement;

$src = 'C:/Users/ACER/OneDrive/Documents/AD';
$out = __DIR__ . '/storage/app/tmp-dump/docx';

if (!is_dir($out)) {
    mkdir($out, 0755, true);
}

$files = [
    'kemitraan'         => 'Draft Kontrak Kemitraan.docx',
    'colocation'        => 'Draft Kontrak layanan Colocation.docx',
    'managed-service'   => 'Draft Kontrak layanan Dedicated, Metro, Managed Service.docx',
    'soho'              => 'Draft Kontrak layanan Soho.docx',
    'payung'            => 'Draft Kontrak Payung layanan Dedicated, Metro, Managed Service.docx',
];

function renderElements(array $elements, int $depth = 0): string
{
    $lines = [];
    $pad  = str_repeat('  ', $depth);

    foreach ($elements as $el) {
        if ($el instanceof \PhpOffice\PhpWord\Element\TextRun) {
            $txt = '';
            foreach ($el->getElements() as $child) {
                if ($child instanceof \PhpOffice\PhpWord\Element\Text) {
                    $txt .= $child->getText();
                } elseif ($child instanceof \PhpOffice\PhpWord\Element\TextBreak) {
                    $txt .= "\n";
                }
            }
            if (trim($txt) !== '') {
                $lines[] = $pad . '[T] ' . trim($txt);
            }
        } elseif ($el instanceof \PhpOffice\PhpWord\Element\ListItem) {
            $txt = $el->getText();
            if (is_array($txt)) {
                $txt = implode('', array_map(
                    fn ($t) => $t instanceof \PhpOffice\PhpWord\Element\Text ? $t->getText() : (string) $t,
                    $txt
                ));
            }
            if (trim((string) $txt) !== '') {
                $lines[] = $pad . '[LI' . $el->getDepth() . '] ' . trim((string) $txt);
            }
        } elseif ($el instanceof \PhpOffice\PhpWord\Element\Table) {
            $lines[] = $pad . '[TABLE start]';
            foreach ($el->getRows() as $row) {
                $cells = [];
                foreach ($row->getCells() as $cell) {
                    $cellTxt = [];
                    foreach ($cell->getElements() as $cel) {
                        if ($cel instanceof \PhpOffice\PhpWord\Element\TextRun) {
                            $t = '';
                            foreach ($cel->getElements() as $cc) {
                                if ($cc instanceof \PhpOffice\PhpWord\Element\Text) {
                                    $t .= $cc->getText();
                                }
                            }
                            $cellTxt[] = trim($t);
                        } elseif ($cel instanceof \PhpOffice\PhpWord\Element\Text) {
                            $cellTxt[] = trim($cel->getText());
                        } elseif ($cel instanceof \PhpOffice\PhpWord\Element\ListItem) {
                            $cellTxt[] = trim((string) $cel->getText());
                        }
                    }
                    $cells[] = trim(preg_replace('/\s+/u', ' ', implode(' | ', array_filter($cellTxt, fn ($c) => $c !== ''))));
                }
                $lines[] = $pad . '  [TR] ' . implode(' || ', $cells);
            }
            $lines[] = $pad . '[TABLE end]';
        } elseif ($el instanceof \PhpOffice\PhpWord\Element\Paragraph) {
            $txt = '';
            foreach ($el->getElements() as $child) {
                if ($child instanceof \PhpOffice\PhpWord\Element\Text) {
                    $txt .= $child->getText();
                } elseif ($child instanceof \PhpOffice\PhpWord\Element\TextBreak) {
                    $txt .= ' ';
                }
            }
            if (trim($txt) !== '') {
                $style = $el->getStyle();
                $pStyle = method_exists($style, 'getName') ? (string) $style->getName() : '';
                $lines[] = $pad . '[P' . ($pStyle !== '' ? ':' . $pStyle : '') . '] ' . trim($txt);
            }
        }
    }

    return implode("\n", $lines);
}

foreach ($files as $key => $name) {
    $path = $src . '/' . $name;

    if (!file_exists($path)) {
        echo "[MISS] {$name}\n";
        continue;
    }

    try {
        $doc = (new \PhpOffice\PhpWord\Reader\Word2007())->load($path);
        $body = renderElements($doc->getElements());
        file_put_contents($out . '/' . $key . '.txt', $body);
        echo "[OK] {$name} → {$key}.txt (" . strlen($body) . " chars)\n";
    } catch (\Throwable $e) {
        echo "[ERR] {$name}: " . $e->getMessage() . "\n";
    }
}
