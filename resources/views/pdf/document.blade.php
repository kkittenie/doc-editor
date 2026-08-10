<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #1B2A4A; }
        .header-table { width: 100%; border-bottom: 3px double #1B2A4A; padding-bottom: 8px; margin-bottom: 16px; }
        
        /* Style Logo Bulat & Border */
        .logo-img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            margin: 0 auto 8px;
            display: block;
            object-fit: cover;
        }

        .instansi { font-size: 16px; font-weight: bold; text-transform: uppercase; text-align: center; }
        .alamat, .kontak { text-align: center; font-size: 9px; }
        .judul { text-align: center; font-size: 14px; font-weight: bold; text-decoration: underline; margin: 16px 0 4px; }
        .nomor, .perihal { text-align: center; font-size: 10px; }
        .perihal { font-style: italic; }
        .section-title { font-weight: bold; margin-top: 12px; }
        .content-table td { vertical-align: top; padding-bottom: 4px; }
        .content-table .label { width: 90px; font-weight: bold; }
        .content-table .colon { width: 15px; }
        .memutuskan { text-align: center; font-weight: bold; text-decoration: underline; margin: 16px 0; }
        .footer-table { width: 100%; margin-top: 40px; }
        .footer-table .right-col { width: 50%; text-align: center; vertical-align: top; }
        .footer-table .left-col { width: 50%; vertical-align: top; font-size: 9px; }
        .signature-space { height: 70px; text-align: center; }
        .signature-space img { max-height: 65px; max-width: 160px; }
        .nama-ttd { font-weight: bold; text-decoration: underline; margin-top: 4px; }
        .nip-ttd { font-size: 9px; }
    </style>
</head>
<body>

    <div class="header-table" style="text-align:center;">
        @if(!empty($logoPath))
            <!-- Logo dengan ukuran fixed 60x60px, membulat, dan ber-border -->
            <img src="{{ $logoPath }}" class="logo-img" alt="Logo Perusahaan">
        @endif
        <div class="instansi">{{ $document->header_data['kopInstansi'] ?? '' }}</div>
        <div class="alamat">{{ $document->header_data['kopAlamat'] ?? '' }}</div>
        <div class="kontak">{{ $document->header_data['kopKontrak'] ?? '' }}</div>
    </div>

    <div class="judul">KEPUTUSAN DIREKSI</div>
    <div class="nomor">Nomor: {{ $document->header_data['nomorSurat'] ?? '' }}</div>
    <div class="perihal">Tentang: {{ $document->header_data['perihalSurat'] ?? '' }}</div>

    <p style="margin-top:16px;">{{ $document->body_content['tujuanSurat'] ?? '' }}</p>

    <div class="section-title">MENIMBANG:</div>
    <p style="white-space: pre-line;">{{ $document->body_content['menimbang'] ?? '' }}</p>

    <div class="section-title">MENGINGAT:</div>
    <p style="white-space: pre-line;">{{ $document->body_content['mengingat'] ?? '' }}</p>

    <div class="memutuskan">MEMUTUSKAN</div>

    <table class="content-table" width="100%">
        <tr>
            <td class="label">KESATU</td>
            <td class="colon">:</td>
            <td>{{ strip_tags($document->body_content['isiPasal1'] ?? '') }}</td>
        </tr>
        <tr>
            <td class="label">KEDUA</td>
            <td class="colon">:</td>
            <td>{{ strip_tags($document->body_content['isiPasal2'] ?? '') }}</td>
        </tr>
    </table>

    <table class="footer-table">
        <tr>
            <td class="left-col">
                <strong>TEMBUSAN:</strong><br>
                <span style="white-space: pre-line;">{{ $document->footer_data['tembusan'] ?? '' }}</span>
            </td>
            <td class="right-col">
                Ditetapkan di {{ $document->footer_data['kotaTtd'] ?? '' }}<br>
                Pada tanggal {{ $document->header_data['tanggalSurat'] ?? '' }}<br>
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