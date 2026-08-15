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
    </style>
</head>

<body>

        <div class="header-table">
            {!! $headerHtml ?? '' !!}
        </div>

    @foreach($pages as $pageHtml)
    <div class="body-content" @if(!$loop->last) style="page-break-after: always;" @endif>
        {!! $pageHtml !!}
    </div>
    @endforeach

    <table class="footer-table">
        <tr>
            <td class="left-col">
                @if(!empty($document->footer_data['tembusan']))
                <strong>TEMBUSAN:</strong><br>
                <span style="white-space: pre-line;">{{ $document->footer_data['tembusan'] }}</span>
                @endif
            </td>
            <td class="right-col">
                {!! $footerHtml ?? '' !!}

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
