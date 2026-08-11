<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #1B2A4A; }
        .header-table { width: 100%; border-bottom: 3px double #1B2A4A; padding-bottom: 8px; margin-bottom: 16px; }
        .logo-img { width: 60px; height: 60px; border-radius: 50%; margin: 0 auto 8px; display: block; object-fit: cover; }
        .instansi { font-size: 16px; font-weight: bold; text-transform: uppercase; text-align: center; }
        .alamat, .kontak { text-align: center; font-size: 9px; }

        .info-table { margin: 14px 0 20px; font-size: 10px; }
        .info-table td { padding-bottom: 3px; vertical-align: top; }
        .info-table .label { width: 70px; }
        .info-table .colon { width: 12px; }

        .body-content { font-size: 11px; line-height: 1.6; margin-top: 8px; }
        .body-content p { margin: 0 0 8px; }
        .body-content h1 { font-size: 15px; font-weight: bold; margin: 10px 0 6px; }
        .body-content h2 { font-size: 13px; font-weight: bold; margin: 9px 0 5px; }
        .body-content h3 { font-size: 12px; font-weight: bold; margin: 8px 0 4px; }
        .body-content ul, .body-content ol { margin: 0 0 8px 20px; }
        .body-content blockquote { margin: 8px 20px; padding-left: 10px; border-left: 3px solid #ccc; font-style: italic; }
        .body-content table { border-collapse: collapse; width: 100%; margin: 8px 0; }
        .body-content table td, .body-content table th { border: 1px solid #999; padding: 4px 6px; }
        .body-content a { color: #1B2A4A; }

        .footer-table { width: 100%; margin-top: 30px; }
        .footer-table .right-col { width: 50%; text-align: center; vertical-align: top; }
        .footer-table .left-col { width: 50%; vertical-align: top; font-size: 9px; }
        .signature-space { height: 70px; text-align: center; }
        .signature-space img { max-height: 65px; max-width: 160px; }
        .nama-ttd { font-weight: bold; text-decoration: underline; margin-top: 4px; }
        .nip-ttd { font-size: 9px; }
    </style>
</head>
<body>

    <div class="header-table">
        @if(!empty($logoPath))
            <img src="{{ $logoPath }}" class="logo-img" alt="Logo Perusahaan">
        @endif
        <div class="instansi">{{ $document->header_data['kopInstansi'] ?? '' }}</div>
        <div class="alamat">{{ $document->header_data['kopAlamat'] ?? '' }}</div>
        <div class="kontak">{{ $document->header_data['kopKontrak'] ?? '' }}</div>
    </div>

    <table class="info-table" width="100%">
        <tr>
            <td class="label">Nomor</td><td class="colon">:</td>
            <td>{{ $document->header_data['nomorSurat'] ?? '' }}</td>
        </tr>
        <tr>
            <td class="label">Tanggal</td><td class="colon">:</td>
            <td>{{ $document->header_data['tanggalSurat'] ?? '' }}</td>
        </tr>
        <tr>
            <td class="label">Perihal</td><td class="colon">:</td>
            <td>{{ $document->header_data['perihalSurat'] ?? '' }}</td>
        </tr>
        @if(!empty($document->header_data['sifatSurat']))
        <tr>
            <td class="label">Sifat</td><td class="colon">:</td>
            <td>{{ $document->header_data['sifatSurat'] }}</td>
        </tr>
        @endif
    </table>

    <div class="body-content">
        {!! $document->body_content['content'] ?? '' !!}
    </div>

    <table class="footer-table">
        <tr>
            <td class="left-col">
                @if(!empty($document->footer_data['tembusan']))
                    <strong>TEMBUSAN:</strong><br>
                    <span style="white-space: pre-line;">{{ $document->footer_data['tembusan'] }}</span>
                @endif
            </td>
            <td class="right-col">
                {{ $document->footer_data['kotaTtd'] ?? '' }}, {{ $document->header_data['tanggalSurat'] ?? '' }}<br>
                <strong>{{ strtoupper($document->footer_data['jabatanPenandatangan'] ?? '') }}</strong>

                <div class="signature-space">
                    @if(!empty($signaturePath))
                        <img src="{{ $signaturePath }}" alt="Tanda Tangan">
                    @endif
                </div>

                <div class="nama-ttd">{{ $document->footer_data['namaPenandatangan'] ?? '' }}</div>
                <div class="nip-ttd">{{ $document->footer_data['nipPenandatangan'] ?? '' }}</div>
            </td>
        </tr>
    </table>

</body>
</html>