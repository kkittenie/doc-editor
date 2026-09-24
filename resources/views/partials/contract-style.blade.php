{{-- ═══ HOUSE STYLE EDITOR — Lima template kontrak resmi ═══
     Bagian dari tiga jaring nilai format yang sama:
       1. App\Data\ContractStyle            — satu-satunya sumber token
       2. DocumentController                — render HTML memakai token itu
       3. CSS ini (editor) + views/pdf/document.blade.php (PDF)
     Jangan menulis ukuran/jarak kontrak hardcode di sini — ubah tokennya di
     App\Data\ContractStyle supaya editor, HTML tersimpan, dan PDF ikut. --}}
@php
    $cs = \App\Data\ContractStyle::class;
    // Tipe numbering data ('1|a|A|i|I') → kelas Quill & nama counter CSS.
    $ctListTypes = ['1', 'a', 'A', 'i', 'I'];
@endphp
<style>
    /* ── Penomoran daftar (selector global: fitur editor, bukan hanya
       kontrak). Kelas ql-liststyle-<token> menempel di <li> setelah
       konversi Quill (renderer memasangnya di <ol>; format block diwariskan
       Quill ke tiap baris). Nilai token & nama counter diambil dari
       ContractStyle::listQuillValue() / listCssType(). ── */
    @foreach ($ctListTypes as $ctType)
        @php
            $qv = $cs::listQuillValue($ctType);
            $ct = $cs::listCssType($ctType);
        @endphp
        @for ($lv = 0; $lv <= 8; $lv++)
            .doc-sheet .ql-editor li[data-list=ordered].ql-liststyle-{{ $qv }}@if($lv > 0).ql-indent-{{ $lv }}@endif > .ql-ui:before {
                content: counter(list-{{ $lv }}, {{ $ct }}) '. ';
            }
        @endfor
    @endforeach

    /* ── Kertas & tipografi dasar kontrak ── */
    .contract-template-canvas .doc-sheet {
        padding: 19mm 21mm;
        font-family: {!! $cs::FONT_FAMILY !!};
        font-size: {!! $cs::SIZE_BODY !!};
        line-height: {!! $cs::LINE_HEIGHT !!};
        color: #000;
    }

    .contract-template-canvas .doc-sheet-header {
        min-height: 0;
        padding-bottom: 4mm;
        border-bottom: 1px solid #000;
    }

    .contract-template-canvas .doc-sheet-footer {
        min-height: 0;
        padding-top: 4mm;
    }

    .contract-template-canvas .doc-sheet .ql-container,
    .contract-template-canvas .doc-sheet .ql-editor {
        font-family: {!! $cs::FONT_FAMILY !!};
        font-size: {!! $cs::SIZE_BODY !!};
        line-height: {!! $cs::LINE_HEIGHT !!};
        color: #000;
    }

    /* Body: ukuran & ritme dari token (SIZE_BODY, PARA_SPACE_AFTER),
       justify (ALIGN_BODY). Ukuran & spasi berlaku untuk SEMUA paragraf;
       justify hanya untuk paragraf TANPA
       class ql-align-* — pilihan alignment user (termasuk judul/heading
       yang di-Quill menjadi ql-align-center) tidak boleh tertimpa. */
    .contract-template-canvas .doc-sheet .ql-editor p {
        font-size: {!! $cs::SIZE_BODY !!};
        margin: 0 0 {!! $cs::PARA_SPACE_AFTER !!};
    }

    .contract-template-canvas .doc-sheet .ql-editor p:not([class*="ql-align-"]) {
        text-align: {!! $cs::ALIGN_BODY !!};
    }

    /* Judul/heading sebelum round-trip Quill masih membawa style inline. */
    .contract-template-canvas .doc-sheet .ql-editor p[style*="text-align:center"] {
        text-align: center;
    }

    /* ── Daftar: ritme & indent dari token ── */
    .contract-template-canvas .doc-sheet .ql-editor ol,
    .contract-template-canvas .doc-sheet .ql-editor ul {
        margin: 0;
        padding-left: {!! $cs::listIndent(1) !!};
    }

    .contract-template-canvas .doc-sheet .ql-editor li {
        margin-bottom: {!! $cs::PARA_SPACE_AFTER !!};
        padding-left: 1.5mm;
        text-align: {!! $cs::ALIGN_BODY !!};
    }

    /* Indentasi level (token LIST_INDENT — satuan pt, bukan rem). */
    @for ($lv = 1; $lv <= 4; $lv++)
        .contract-template-canvas .doc-sheet .ql-editor li.ql-indent-{{ $lv }} {
            padding-left: {!! $cs::listIndent($lv) !!};
        }
    @endfor

    /* ── Tabel ── */
    .contract-template-canvas .doc-sheet .ql-editor table {
        margin: {!! $cs::TABLE_SPACE !!};
    }

    .contract-template-canvas .doc-sheet .ql-editor table td,
    .contract-template-canvas .doc-sheet .ql-editor table th {
        min-width: 0;
        height: auto;
        border: {!! $cs::TABLE_BORDER !!} !important;
        padding: {!! $cs::CELL_PADDING !!} !important;
        font-family: {!! $cs::FONT_FAMILY !!};
        font-size: {!! $cs::SIZE_TABLE !!};
        line-height: {!! $cs::LINE_HEIGHT !!};
        color: #000;
    }

    .contract-template-canvas .doc-sheet .ql-editor table th {
        background: transparent;
    }

    .contract-template-canvas .doc-sheet .ql-editor table.contract-table-unstyled td,
    .contract-template-canvas .doc-sheet .ql-editor table.contract-table-unstyled th {
        border: none !important;
    }
</style>
