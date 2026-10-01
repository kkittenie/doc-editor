<?php

namespace App\Data;


class ContractStyle
{
    public const FONT_FAMILY = '"Times New Roman", Times, serif';

    public const SIZE_TITLE      = '16pt';
    public const SIZE_BODY       = '11pt';
    public const SIZE_HEADING    = '11pt';
    public const SIZE_TABLE      = '9pt';
    public const SIZE_LETTERHEAD = '9pt';
    public const SIZE_PARAF      = '8pt';

    public const LINE_HEIGHT          = '1.15';
    public const PARA_SPACE_AFTER     = '6pt';
    public const TITLE_SPACE_BEFORE   = '0';
    public const TITLE_SPACE_AFTER    = '12pt';
    public const HEADING_SPACE_BEFORE = '16pt';
    public const HEADING_SPACE_AFTER  = '4pt';
    public const TABLE_SPACE          = '5pt 0';

    public const HEADING_TOP_MARGIN = self::HEADING_SPACE_BEFORE;

    public const LIST_INDENT = [
        1 => '18pt',
        2 => '36pt',
        3 => '54pt',
        4 => '72pt',
    ];

    public const CELL_PADDING = '3pt 4pt';
    public const TABLE_BORDER = '1px solid #000';
    public const TABLE_WIDTH  = '100%';

    public const COVER_SPACE_AFTER = '80pt';
    public const SIZE_COVER_LINE = '13pt';

    public const COVER_SPACE_MIN = '34pt';

    private const COVER_LINE_BOX_FACTOR = 1.55;

    private const PAGE_TEXT_HEIGHT_PT = 841.89 - 107.72 - 121.89; // ≈ 612,3pt

    private const COVER_FIT_RATIO = 0.94;

    /**
     * Jarak antar-baris sampul yang muat di satu halaman.
     *
     * Sampul berisi N baris; total tinggi = Σ tinggi baris + (N-1) × jarak.
     * Jarak dibatasi COVER_SPACE_AFTER di atas dan COVER_SPACE_MIN di bawah,
     * jadi sampul pendek tetap tampil renggang seperti desain, sampul panjang
     * hanya dipadatkan seperlunya.
     *
     * @param string[] $lines Baris sampul apa adanya, urutan sama dengan render.
     */
    public static function coverSpacing(array $lines): string
    {
        $n = count($lines);
        if ($n < 2) {
            return self::COVER_SPACE_AFTER;
        }

        $textHeight = 0.0;
        foreach (array_values($lines) as $i => $line) {
            $size = $i === 0
                ? self::pt(self::SIZE_TITLE)
                : (preg_match('/^(dengan|nomor\b)/iu', trim((string) $line)) === 1
                    ? self::pt(self::SIZE_BODY)
                    : self::pt(self::SIZE_COVER_LINE));

            $textHeight += $size * self::COVER_LINE_BOX_FACTOR;
        }

        $gaps = $n - 1;

        $budget = self::PAGE_TEXT_HEIGHT_PT * self::COVER_FIT_RATIO;
        $fit    = ($budget - $textHeight) / $gaps;
        $fit    = max(self::pt(self::COVER_SPACE_MIN), min(self::pt(self::COVER_SPACE_AFTER), $fit));

        return self::round($fit) . 'pt';
    }

    /** Ubah token ukuran CSS ("13pt") jadi angka (13.0). */
    private static function pt(string $token): float
    {
        return (float) preg_replace('/[^0-9.]/', '', $token);
    }

    /** Bulatkan ke 1 desimal supaya CSS rapi & deterministik. */
    private static function round(float $value): string
    {
        return rtrim(rtrim(number_format($value, 1, '.', ''), '0'), '.') ?: '0';
    }

    public const PAGE_MARGIN_TOP = '38mm';
    public const PAGE_MARGIN_SIDE = '21mm';
    public const PAGE_MARGIN_BOTTOM = '43mm';

    public const RUNNING_HEADER_TOP = '-19mm';
    public const FOOTER_BOTTOM        = '-24mm';

    public const PAGE_NUM_X = 59.5;   
    public const PAGE_NUM_Y = 78.0;   
    public const PAGE_NUM_SIZE = 8.0;
    public const PAGE_NUM_PREFIX = 'Page | ';


    public const FOOTER_NUM_X = 59.5;  
    public const FOOTER_NUM_Y = 731.0; 

   
    public const ALIGN_TITLE   = 'center';
    public const ALIGN_HEADING = 'center';
    public const ALIGN_BODY    = 'justify';

    /** Kelas penanda daftar kontrak (dipakai CSS & pengujian). */
    public const LIST_CLASS = 'ct-list';

    /** Tipe list data ('1|a|A|i|I') → tipe CSS `list-style-type`. */
    public static function listCssType(string $type): string
    {
        return match ($type) {
            'a' => 'lower-alpha',
            'A' => 'upper-alpha',
            'i' => 'lower-roman',
            'I' => 'upper-roman',
            default => 'decimal',
        };
    }


    public static function listQuillValue(string $type): string
    {
        return match ($type) {
            'a' => 'alpha',
            'A' => 'upperalpha',
            'i' => 'lowerroman',
            'I' => 'roman',
            default => 'decimal',
        };
    }

    public static function listStyle(string $type, int $level = 1): string
    {
        return 'list-style-type:' . self::listCssType($type)
            . '; padding-left:' . self::listIndent($level)
            . '; margin:0;';
    }

    public static function listIndent(int $level): string
    {
        $level = max(1, min(4, $level));

        return self::LIST_INDENT[$level] ?? self::LIST_INDENT[4];
    }

    /** CSS paragraf body kontrak. */
    public static function paraStyle(): string
    {
        return 'text-align:' . self::ALIGN_BODY . ';'
            . ' font-size:' . self::SIZE_BODY . ';'
            . ' margin:0 0 ' . self::PARA_SPACE_AFTER . ';';
    }

    /** CSS blok judul (baris pertama preamble). */
    public static function titleStyle(): string
    {
        return 'text-align:' . self::ALIGN_TITLE . ';'
            . ' font-size:' . self::SIZE_TITLE . ';'
            . ' font-weight:bold;'
            // margin-top/bottom terpisah (bukan shorthand `margin:`) supaya
            // selamat dari round-trip Quill — attributor 'phead' & 'pbb'.
            . ' margin-top:' . self::TITLE_SPACE_BEFORE . ';'
            . ' margin-bottom:' . self::TITLE_SPACE_AFTER . ';';
    }

    /** CSS baris display di dalam blok judul (nama pihak, nomor dokumen). */
    public static function displayStyle(): string
    {
        return 'text-align:' . self::ALIGN_TITLE . ';'
            . ' font-size:' . self::SIZE_BODY . ';'
            . ' margin-top:0; margin-bottom:' . self::HEADING_SPACE_AFTER . ';';
    }

    /**
     * CSS satu baris halaman sampul kontrak. Baris pertama = judul dokumen
     *
     * @param string 
     */
    public static function coverStyle(
        string $line,
        bool $first = false,
        bool $last = false,
        string $spaceAfter = self::COVER_SPACE_AFTER
    ): string {
        $plain = preg_match('/^(dengan|nomor\b)/iu', trim($line)) === 1;

        return 'text-align:' . self::ALIGN_TITLE . ';'
            . ' font-size:' . ($first
                ? self::SIZE_TITLE
                : ($plain ? self::SIZE_BODY : self::SIZE_COVER_LINE)) . ';'
            . ' font-weight:' . (($first || ! $plain) ? 'bold' : 'normal') . ';'
            . ' margin-top:0;'
            . ' margin-bottom:' . ($last ? '0' : $spaceAfter) . ';';
    }

    /** CSS heading kontrak (PASAL n, judul pasal, LAMPIRAN, MENIMBANG). */
    public static function headingStyle(bool $center = true): string
    {
        return ($center ? 'text-align:' . self::ALIGN_HEADING . ';' : '')
            . ' font-size:' . self::SIZE_HEADING . ';'
            . ' font-weight:bold;'
            . ' margin-top:' . self::HEADING_SPACE_BEFORE . ';'
            . ' margin-bottom:' . self::HEADING_SPACE_AFTER . ';';
    }

    /** CSS tabel kontrak. Semua tabel kontrak seragam lebar penuh. */
    public static function tableStyle(bool $bordered = true): string
    {
        return 'border-collapse:collapse;'
            . ' table-layout:fixed;'
            . ' width:' . self::TABLE_WIDTH . ';'
            . ' margin:' . self::TABLE_SPACE . ';';
    }

    /**
     * @param  bool       $head         Baris header tabel (bold + center)
     * @param  float|null $widthPercent Lebar kolom efektif (%)
     * @param  bool       $bordered     false → tabel tanpa border (tanda tangan)
     */
    public static function cellStyle(bool $head = false, ?float $widthPercent = null, bool $bordered = true): string
    {
        $style = 'border:' . ($bordered ? self::TABLE_BORDER : 'none') . ';'
            . ' padding:' . self::CELL_PADDING . ';'
            . ' vertical-align:top;'
            . ' font-size:' . self::SIZE_TABLE . ';'
            . ' line-height:' . self::LINE_HEIGHT . ';'
            . ' word-break:break-word;'
            . ' overflow-wrap:anywhere;';

        if ($head) {
            $style .= ' font-weight:bold; text-align:center;';
        }

        if ($widthPercent !== null) {
            $style .= ' width:' . round($widthPercent, 2) . '%;';
        }

        return $style;
    }
}

