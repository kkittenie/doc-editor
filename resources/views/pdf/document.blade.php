<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #1B2A4A;
        }

        .header-table {
            position: relative;
            overflow: hidden;
            width: 100%;
            border-bottom: 3px double #1B2A4A;
            padding-bottom: 8px;
            margin-bottom: 16px;
        }

        .header-table .document-logo {
            position: absolute;
            z-index: 1;
        }

        .header-table p,
        .header-table h1,
        .header-table h2,
        .header-table h3,
        .header-table h4,
        .header-table h5,
        .header-table h6,
        .header-table ul,
        .header-table ol,
        .header-table table,
        .header-table div {
            position: relative;
            z-index: 2;
        }

        .header-table .document-logo ~ p,
        .header-table .document-logo ~ h1,
        .header-table .document-logo ~ h2,
        .header-table .document-logo ~ h3,
        .header-table .document-logo ~ h4,
        .header-table .document-logo ~ h5,
        .header-table .document-logo ~ h6 {
            margin-left: 190px;
        }

        .logo-img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            margin: 0 auto 8px;
            display: block;
            object-fit: cover;
        }

        .instansi {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            text-align: center;
        }

        .alamat,
        .kontak {
            text-align: center;
            font-size: 9px;
        }

        .info-table {
            margin: 14px 0 20px;
            font-size: 10px;
        }

        .info-table td {
            padding-bottom: 3px;
            vertical-align: top;
        }

        .info-table .label {
            width: 70px;
        }

        .info-table .colon {
            width: 12px;
        }

        .body-content {
            font-size: 11px;
            line-height: 1.6;
            margin-top: 8px;
        }

        /* Alignment Quill disimpan sebagai class ql-align-*, bukan style
           inline. Tanpa rule ini alignment hasil editor (termasuk center
           pada judul & halaman sampul) jatuh ke default di PDF. */
        .body-content .ql-align-left { text-align: left; }
        .body-content .ql-align-center { text-align: center; }
        .body-content .ql-align-right { text-align: right; }
        .body-content .ql-align-justify { text-align: justify; }

        /* Halaman SAMPUL (cover): judul melekat di atas (dekat kop),
           baris-baris pihak direntangkan dengan jarak lega hingga
           "Dengan" berada di sekitar tengah-ke-atas kertas.
           dompdf mematuhi style inline dari buildCoverPageHtml() (baru),
           jadi rule di sini TANPA !important agar tidak menimpa inline —
           hanya berlaku sebagai fallback untuk dokumen lama yang inline
           margin-nya sudah hilang/terstrip Quill. */
        .body-content.cover-page {
            padding-top: 4px;
        }

        .body-content.cover-page h1 {
            margin: 8px 0 70px;
        }

        .body-content.cover-page p {
            margin: 0 0 60px;
        }

        .body-content.cover-page p:last-child {
            margin: 0;
        }

        .body-content p {
            margin: 0 0 8px;
        }

        .body-content h1 {
            font-size: 15px;
            font-weight: bold;
            margin: 10px 0 6px;
        }

        .body-content h2 {
            font-size: 13px;
            font-weight: bold;
            margin: 9px 0 5px;
        }

        .body-content h3 {
            font-size: 12px;
            font-weight: bold;
            margin: 8px 0 4px;
        }

        .body-content ul,
        .body-content ol {
            margin: 0 0 8px 20px;
        }

        .body-content blockquote {
            margin: 8px 20px;
            padding-left: 10px;
            border-left: 3px solid #ccc;
            font-style: italic;
        }

        .body-content table {
            border-collapse: collapse;
            width: 100%;
            margin: 8px 0;
        }

        .body-content table td,
        .body-content table th {
            border: 1px solid #999;
            padding: 4px 6px;
        }

        .body-content a {
            color: #1B2A4A;
        }

        .footer-table {
            width: 100%;
            margin-top: 30px;
        }

        .footer-table .right-col {
            width: 50%;
            text-align: center;
            vertical-align: top;
        }

        .footer-table .left-col {
            width: 50%;
            vertical-align: top;
            font-size: 9px;
        }

        /* FOOTER COVER: tabel full-width (identitas Pihak Pertama | Paraf/Stempel)
           di bawahnya baris Tembusan + Tanda Tangan.
           Rule ini juga pulihkan penampilan compact jika Quill membuang
           style inline (margin/font-size/padding/border) pada round-trip. */
        .footer-cover {
            width: 100%;
            margin-top: 30px;
            page-break-inside: avoid;
        }

        .footer-cover table {
            width: 100%;
            margin: 0;
        }

        .footer-cover table td {
            padding: 0;
            border: none;
            vertical-align: top;
        }

        .footer-cover table td p {
            margin: 0 0 3px;
            font-size: 9px;
        }

        /* FOOTER DEFAULT FIBERTRUST (.fibertrust-footer): diulang di SETIAP
           halaman via position:fixed (seperti .header-table kontrak di atas);
           dompdf mengulang elemen fixed per halaman. Nomor "Page | n" digambar
           callback kanvas (bukan bagian HTML) di atas blok identitas, sejajar
           baris paraf. margin-top 18px memberi ruang baris nomor. */
        .footer-fixed {
            position: fixed;
            bottom: 10mm;
            left: {!! $cs::PAGE_MARGIN_SIDE !!};
            right: {!! $cs::PAGE_MARGIN_SIDE !!};
            width: auto;
            margin-top: 18px;
            font-size: 8.5pt;
        }

        .signature-space {
            height: 70px;
            text-align: center;
        }

        .signature-space img {
            max-height: 65px;
            max-width: 160px;
        }

        .nama-ttd {
            font-weight: bold;
            text-decoration: underline;
            margin-top: 4px;
        }

        .nip-ttd {
            font-size: 9px;
        }

        /* Presentasi khusus lima template kontrak resmi — seluruh nilai
           diambil dari token App\Data\ContractStyle (satu sumber format
           bersama renderer DocumentController & CSS editor
           partials/contract-style.blade.php). Tidak diterapkan kepada
           dokumen bebas, surat, maupun S.O.F.
           Catatan Dompdf: semua satuan pt — jangan rem. */
        @php $cs = \App\Data\ContractStyle::class; @endphp
        .contract-document {
            font-family: {!! $cs::FONT_FAMILY !!};
            font-size: {!! $cs::SIZE_BODY !!};
            line-height: {!! $cs::LINE_HEIGHT !!};
            color: #000;
        }

        .contract-document .header-table {
            border-bottom: 1px solid #000;
            padding-bottom: 10px;
            margin-bottom: 14px;
        }

        .contract-document .body-content {
            font-size: {!! $cs::SIZE_BODY !!};
            line-height: {!! $cs::LINE_HEIGHT !!};
            margin-top: 0;
        }

        .contract-document .body-content p {
            margin: 0 0 {!! $cs::PARA_SPACE_AFTER !!};
            text-align: {!! $cs::ALIGN_BODY !!};
        }

        /* Alignment pilihan user & heading hasil round-trip Quill
           (class ql-align-*) harus mengalahkan justify bawaan di atas. */
        .contract-document .body-content .ql-align-left { text-align: left; }
        .contract-document .body-content .ql-align-center { text-align: center; }
        .contract-document .body-content .ql-align-right { text-align: right; }
        .contract-document .body-content .ql-align-justify { text-align: justify; }

        .contract-document .body-content p[style*="text-align:center"] {
            text-align: center;
        }

        .contract-document .body-content ol,
        .contract-document .body-content ul {
            margin: 0;
            padding-left: {!! $cs::listIndent(1) !!};
        }

        .contract-document .body-content li {
            margin-bottom: {!! $cs::PARA_SPACE_AFTER !!};
            text-align: {!! $cs::ALIGN_BODY !!};
        }

        /* Tipe numbering: sebelum dokumen diedit tipe masih style inline di
           <ol>; sesudah round-trip Quill pindah ke class ql-liststyle-<token>
           di <ol>/<li>. Aturan base menjaga nested list tetap decimal bila
           induknya ber-type huruf. */
        .contract-document .body-content ol > li {
            list-style-type: decimal;
        }
        @foreach (['1', 'a', 'A', 'i', 'I'] as $ctType)
        .contract-document .body-content .ql-liststyle-{{ $cs::listQuillValue($ctType) }},
        .contract-document .body-content .ql-liststyle-{{ $cs::listQuillValue($ctType) }} > li {
            list-style-type: {!! $cs::listCssType($ctType) !!};
        }
        @endforeach

        /* Indentasi level daftar (token LIST_INDENT, satuan pt). */
        @for ($lv = 1; $lv <= 4; $lv++)
        .contract-document .body-content li.ql-indent-{{ $lv }} {
            padding-left: {!! $cs::listIndent($lv) !!};
        }
        @endfor

        .contract-document .body-content table {
            margin: {!! $cs::TABLE_SPACE !!};
        }

        .contract-document .body-content table td,
        .contract-document .body-content table th {
            border: 1px solid #000;
            padding: {!! $cs::CELL_PADDING !!};
            font-size: {!! $cs::SIZE_TABLE !!};
            line-height: {!! $cs::LINE_HEIGHT !!};
        }

        .contract-document .body-content table.contract-table-unstyled td,
        .contract-document .body-content table.contract-table-unstyled th {
            border: none;
        }

        /* ── D3 & D4: kertas & kop berulang — HANYA untuk lima template
           kontrak resmi (digate body class contract-document + flag
           contractTemplate). Margin @page menyediakan ruang kop di atas
           area teks; kop dipasang position:fixed dengan origin tepi kertas
           sehingga dompdf mengulangnya di SETIAP halaman (termasuk sampul),
           persis PDF sumber ("Page | n" + paraf + kop). Semua nilai dari
           token ContractStyle. ── */
        @if($document->body_content['contractTemplate'] ?? false)
        @page {
            margin: {!! $cs::PAGE_MARGIN_TOP !!} {!! $cs::PAGE_MARGIN_SIDE !!} {!! $cs::PAGE_MARGIN_BOTTOM !!} {!! $cs::PAGE_MARGIN_SIDE !!};
        }

        .contract-document .header-table {
            position: fixed;
            top: {!! $cs::RUNNING_HEADER_TOP !!};
            left: {!! $cs::PAGE_MARGIN_SIDE !!};
            right: {!! $cs::PAGE_MARGIN_SIDE !!};
            width: auto;
            margin-bottom: 0;
        }
        @endif
    </style>
</head>

<body class="@if($document->body_content['contractTemplate'] ?? false) contract-document @endif">

        <div class="header-table">
            {!! $headerHtml ?? '' !!}
        </div>

    @if(!empty($sofSummaryHtml))
    {{-- Blok ringkasan S.O.F (Menu S.O.F): identitas kontrak + Barang + Service. --}}
    {!! $sofSummaryHtml !!}
    @endif

    @foreach($pages as $index => $pageHtml)
    <div class="body-content @if($index < ($coverPages ?? 0)) cover-page @endif" @if(!$loop->last) style="page-break-after: always;" @endif>
        {!! $pageHtml !!}
    </div>
    @endforeach

    @if(($isFibertrustFooter ?? false) && !empty(trim(strip_tags($footerHtml ?? ''))))
    {{-- FOOTER DEFAULT FIBERTRUST: identitas + paraf diulang tiap halaman
         (position:fixed .footer-fixed). Nomor "Page | n" digambar callback
         kanvas, bukan bagian HTML. --}}
    <div class="footer-fixed">
        {!! $footerHtml !!}
    </div>
    @endif

    @if(($footerHasTable ?? false) && !($isFibertrustFooter ?? false))
    {{-- FOOTER TIPO COVER: tabel full-width, tidak dikompresi ke kolom 50%. --}}
    <div class="footer-cover">
        {!! $footerHtml !!}
    </div>
    @endif

    <table class="footer-table">
        <tr>
            <td class="left-col">
                @if(!empty($document->footer_data['tembusan']))
                <strong>TEMBUSAN:</strong><br>
                <span style="white-space: pre-line;">{{ $document->footer_data['tembusan'] }}</span>
                @endif
            </td>
            <td class="right-col">
                @if(!($footerHasTable ?? false) && !($isFibertrustFooter ?? false))
                {!! $footerHtml ?? '' !!}
                @endif

            <div class="signature-space">
                @if(!empty($signaturePath))
                <img src="{{ $signaturePath }}" alt="Tanda Tangan">
                @endif
            </div>
        </td>
        </tr>
    </table>

</body>

</html>
