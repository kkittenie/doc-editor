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

    /* ── Sampul kontrak (cover, halaman 1 PDF sumber) ──────────── */
    /** Jarak antar-baris halaman sampul (batas atas, dipakai sampul 6 baris). */
    public const COVER_SPACE_AFTER = '80pt';
    /** Ukuran baris pihak (bukan judul, bukan "DENGAN"/"Nomor:") pada sampul. */
    public const SIZE_COVER_LINE = '13pt';
    /**
     * Batas bawah jarak antar-baris sampul. Sampul 6 baris tetap memakai
     * COVER_SPACE_AFTER; sampul yang lebih panjang (mis. kontrak-kemitraan
     * 7 baris) memakai jarak yang diturunkan rumus di coverSpacing() supaya
     * bloknya tidak meluber ke halaman kedua.
     */
    public const COVER_SPACE_MIN = '34pt';

    /**
     * Faktor tinggi baris EFEKTIF saat menghitung ruang sampul.
     *
     * PENTING: ini BUKAN LINE_HEIGHT (1,15). DomPDF memberi tinggi baris
     * riil ≈ 1,55 × font-size (leading + descent font, bukan sekadar
     * line-height CSS), sehingga memakai 1,15 membuat perkiraan terlalu
     * optimistis: sampul kontrak-kemitraan (7 baris) estimada 583pt dari
     * 612pt tersedia, padahal render nyata sudah meluber di 80pt (limit
     * terukur 79,8pt). Nilai ini dikalibrasi dari pengukuran output DomPDF
     * dan dijaga oleh test "sampul tiap template kontrak muat utuh di satu
     * halaman A4" — ubah salah satu, keduanya harus ikut disesuaikan.
     */
    private const COVER_LINE_BOX_FACTOR = 1.55;

    /**
     * Tinggi area teks satu halaman kontrak (pt) — dipakai menghitung
     * jarak antar-baris sampul. A4 = 297mm = 841,89pt, dikurangi margin
     * @page atas & bawah (lihat PAGE_MARGIN_TOP / PAGE_MARGIN_BOTTOM).
     */
    private const PAGE_TEXT_HEIGHT_PT = 841.89 - 107.72 - 121.89; // ≈ 612,3pt

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

        // Tinggi teks: judul (baris pertama) memakai SIZE_TITLE, "DENGAN"/
        // "Nomor:" memakai SIZE_BODY, sisanya SIZE_COVER_LINE. Faktor tinggi
        // baris memakai COVER_LINE_BOX_FACTOR (ukuran riil DomPDF), bukan
        // LINE_HEIGHT — lihat catatan pada konstanta itu.
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
        $fit  = (self::PAGE_TEXT_HEIGHT_PT - $textHeight) / $gaps;
        $fit  = max(self::pt(self::COVER_SPACE_MIN), min(self::pt(self::COVER_SPACE_AFTER), $fit));

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

    /* ── Kertas & kop berulang PDF kontrak (D3/D4) ─────────────── */
    /**
     * Margin halaman — dipakai rule @page di views/pdf/document.blade.php.
     *
     * Nilainya diturunkan dari PADDING KARTU DI EDITOR
     * (resources/views/partials/contract-style.blade.php → `padding: 19mm 21mm`)
     * ditambah ruang yang dipakai header & footer kartu:
     *   - atas  : 19mm (padding kartu) + tinggi blok kop
     *              (logo 56px = 14,82mm + padding-bottom kop 4mm)  ≈ 38mm
     *              Sisa 0,2mm untuk garis 1px kop. Blok kop di PDF sengaja
     *              memakai margin paragraf 0 & img display:block agar
     *              blok kop setinggi persis nilai ini (lihat
     *              views/pdf/document.blade.php).
     *   - bawah : 19mm + blok footer (≈23mm: baris "Page | n" + margin
     *              tabel 24px + 4 baris identitas 8,5pt)          ≈ 43mm
     * Area teks PDF dengan demikian sama dengan area teks kartu di editor dan
     * kop/footer tidak pernah bertabrakan dengan teks meski halaman penuh.
     */
    public const PAGE_MARGIN_TOP = '38mm';
    public const PAGE_MARGIN_SIDE = '21mm';
    public const PAGE_MARGIN_BOTTOM = '43mm';

    /**
     * Posisi kop & footer kontrak yang memakai `position: fixed`.
     *
     * PENTING: dompdf mengukur offset `fixed` dari AREA KONTEN (kotak halaman
     * SETELAH dikurangi margin @page), BUKAN dari tepi kertas. Karena itu
     * offset positif lama (top: 5mm) placing kop 5mm di DALAM area teks — itu
     * penyebab logo menimpa paragraf di PDF. Nilai di bawah sengaja negatif
     * supaya kop/footer duduk DI DALAM margin, persis seperti header/footer
     * kartu di editor: 19mm dari tepi atas dan 19mm dari tepi bawah kertas.
     */
    public const RUNNING_HEADER_TOP = '-19mm';
    public const FOOTER_BOTTOM        = '-24mm';

    /**
     * Nomor halaman "Page | n" digambar kanvas dompdf (callback end_document)
     * karena counter(page) CSS selalu bernilai 1 di dompdf. Satuan titik (pt),
     * origin kiri-ATAS halaman (y bertambah ke bawah).
     */
    public const PAGE_NUM_X = 59.5;   // 21mm — sejajar margin kiri kartu
    public const PAGE_NUM_Y = 78.0;   // di baris kop (kop mulai 19mm dari atas)
    public const PAGE_NUM_SIZE = 8.0;
    public const PAGE_NUM_PREFIX = 'Page | ';

    /**
     * Nomor halaman footer default ("Page | n"): baris PERTAMA di dalam blok
     * footer-fixed — sama seperti CSS counter di editor
     * (.doc-sheet-footer::before, `top: 8px`). Satuan titik (pt), origin
     * kiri-atas halaman.
     *
     * X = margin kiri (21mm). Y = baseline ±8,5pt di bawah tepi atas blok
     * footer; tepi atas blok itu 19mm + tinggi blok (≈42mm) dari tepi bawah
     * kertas A4 (842pt) → 842 - 119 ≈ 722pt, + baseline ≈ 731pt.
     */
    public const FOOTER_NUM_X = 59.5;  // 21mm — sejajar margin kiri kartu
    public const FOOTER_NUM_Y = 731.0; // baseline baris "Page | n" di atas tabel footer

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

    /**
     * CSS satu baris halaman sampul kontrak. Baris pertama = judul dokumen
     * (SIZE_TITLE), baris "DENGAN"/"Nomor:" reguler (SIZE_BODY, tanpa tebal),
     * baris lain baris pihak (SIZE_COVER_LINE, tebal). Margin dipisah
     * top/bottom supaya selamat round-trip Quill (attributor phead/pbb).
     *
     * @param string $spaceAfter Jarak ke baris berikutnya; default COVER_SPACE_AFTER
     *                           (sampul pendek). Sampul panjang memakai hasil
     *                           ContractStyle::coverSpacing().
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

