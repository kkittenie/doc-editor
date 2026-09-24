<?php

namespace App\Data;

/**
 * Token tampilan (house style) untuk LIMA template kontrak resmi.
 *
 * Satu-satunya sumber nilai format: dipakai oleh renderer HTML
 * (DocumentController), CSS editor (partials/contract-style.blade.php),
 * dan CSS PDF (pdf/document.blade.php). Jangan menulis ukuran/jarak kontrak
 * secara hardcode di tempat lain — tambahkan token di sini.
 *
 * Patokan terpilih — preset "kompak" (di-approve user; baseline DOCX awal
 * ada di storage/app/tmp-dump/style-report.md):
 *  - body     : 11pt, justify, line-height 1.15
 *  - judul    : 16pt, bold, center
 *  - heading  : 11pt, bold, center
 *  - tabel    : 9pt, padding 3pt 4pt, border 1px #000
 *  - spasi    : paragraf 6pt, heading-atas 16pt / bawah 4pt
 *
 * Catatan Dompdf: pakai satuan pt/mm/px, JANGAN rem (tidak dikenal Dompdf
 * sehingga indent list jatuh ke nol di PDF).
 */
class ContractStyle
{
    public const FONT_FAMILY = '"Times New Roman", Times, serif';

    /* ── Ukuran (pt) — preset "kompak" (di-approve) ────────────── */
    public const SIZE_TITLE      = '16pt';
    public const SIZE_BODY       = '11pt';
    public const SIZE_HEADING    = '11pt';
    public const SIZE_TABLE      = '9pt';
    public const SIZE_LETTERHEAD = '9pt';
    public const SIZE_PARAF      = '8pt';

    /* ── Ritme paragraf ────────────────────────────────────────── */
    public const LINE_HEIGHT          = '1.15';
    public const PARA_SPACE_AFTER     = '6pt';
    public const TITLE_SPACE_BEFORE   = '0';
    public const TITLE_SPACE_AFTER    = '12pt';
    public const HEADING_SPACE_BEFORE = '16pt';
    public const HEADING_SPACE_AFTER  = '4pt';
    public const TABLE_SPACE          = '5pt 0';

    /** Nilai margin-top heading — dipakai attributor 'phead' di editor.js. */
    public const HEADING_TOP_MARGIN = self::HEADING_SPACE_BEFORE;

    /* ── Indent daftar (pt, bukan rem) ─────────────────────────── */
    public const LIST_INDENT = [
        1 => '18pt',
        2 => '36pt',
        3 => '54pt',
        4 => '72pt',
    ];

    /* ── Tabel ─────────────────────────────────────────────────── */
    public const CELL_PADDING = '3pt 4pt';
    public const TABLE_BORDER = '1px solid #000';
    public const TABLE_WIDTH  = '100%';

    /* ── Alignment ─────────────────────────────────────────────── */
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

    /**
     * Tipe list data → nilai kelas attributor Quill (`ql-liststyle-<value>`).
     * PENTING: nilai wajib SATU token tanpa tanda hubung, karena Parchment
     * ClassAttributor memotong segmen terakhir nama class saat membaca nilai
     * ('ql-liststyle-upper-alpha' terbaca sebagai prefix 'ql-liststyle-upper').
     */
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

    /** CSS untuk satu daftar kontrak (elemen <ol>).
     *  Spasi antar-item daftar dipegang CSS per-<li> (PARA_SPACE_AFTER),
     *  sehingga container <ol> sendiri bermargin nol. */
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
     * CSS satu sel tabel kontrak.
     *
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

