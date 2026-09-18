{{--
    Ringkasan S.O.F (Surat Order Formulir) — fragmen HTML yang disisipkan ke
    dalam resources/views/pdf/document.blade.php lewat variabel
    $sofSummaryHtml (diisi DocumentController::buildSofSummaryHtml()).

    Isi sesuai alur "Gabungkan Data Header + Barang + Service + Canvas":
    identitas pelanggan/kontrak, tabel Barang, tabel Service, lalu kop dan
    halaman canvas dokumen menyusul di bawahnya (footer + tanda tangan).

    Variabel: $document, $customer, $barang (Collection), $services (Collection).
--}}
@php
    $formatRupiah = fn ($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');

    $totalBarang = $barang->sum(fn ($item) => (float) $item->price * (int) $item->quantity);
    $totalService = $services->sum(fn ($item) => (float) $item->price);

    $identityRows = [
        ['label' => 'Nama Pelanggan', 'value' => $customer->name],
        ['label' => 'Pelanggan ID', 'value' => 'PLG-' . str_pad((string) $customer->id, 5, '0', STR_PAD_LEFT)],
        ['label' => 'Nomer Pelanggan', 'value' => $customer->customer_number ?: '—'],
        ['label' => 'Nomer S.O.F / Kontrak', 'value' => $customer->contract_number ?: '—'],
        ['label' => 'Nama Kontrak', 'value' => $customer->contract_name ?: '—'],
        ['label' => 'Tanggal Aktif', 'value' => $customer->active_date ? $customer->active_date->format('d/m/Y') : '—'],
        ['label' => 'Masa Aktif', 'value' => $customer->active_months ? $customer->active_months . ' bulan' : '—'],
        ['label' => 'Tanggal Selesai', 'value' => $customer->finish_date ? $customer->finish_date->format('d/m/Y') : '—'],
    ];
@endphp

<style>
    .sof-title {
        border: 1px solid #1B2A4A;
        border-bottom: none;
        background-color: #F3EFE7;
        padding: 6px 8px;
        text-align: center;
        font-size: 12px;
        font-weight: bold;
        letter-spacing: 1px;
        text-transform: uppercase;
    }

    .sof-identity {
        border-collapse: collapse;
        width: 100%;
        margin: 0;
        font-size: 10px;
    }

    .sof-identity td {
        border: 1px solid #999;
        padding: 4px 6px;
        vertical-align: top;
    }

    .sof-identity td.sof-label {
        width: 20%;
        background-color: #FAF8F4;
        font-weight: bold;
    }

    .sof-identity td.sof-spacer {
        border: none;
        padding: 0;
        width: 0;
    }

    .sof-section {
        font-size: 11px;
        font-weight: bold;
        text-transform: uppercase;
        margin: 0 0 6px;
    }

    .sof-table {
        border-collapse: collapse;
        width: 100%;
        margin: 0;
        font-size: 10px;
    }

    .sof-table th,
    .sof-table td {
        border: 1px solid #999;
        padding: 4px 6px;
    }

    .sof-table thead th {
        background-color: #F3EFE7;
        font-size: 9px;
        font-weight: bold;
        text-transform: uppercase;
        text-align: left;
    }

    .sof-table td.sof-number {
        text-align: center;
        width: 6%;
    }

    .sof-table td.sof-money {
        text-align: right;
        white-space: nowrap;
    }

    .sof-table tr.sof-total td {
        font-weight: bold;
        background-color: #FAF8F4;
    }
</style>

<div style="margin: 0 0 18px; page-break-inside: avoid;">
    <div class="sof-title">Surat Order Formulir (S.O.F)</div>

    <table class="sof-identity">
        @foreach (array_chunk($identityRows, 2) as $pair)
            <tr>
                @foreach ($pair as $row)
                    <td class="sof-label">{{ $row['label'] }}</td>
                    <td>{{ $row['value'] }}</td>
                @endforeach

                @if (count($pair) === 1)
                    {{-- sel penyeimbang supaya baris terakhir tetap 4 kolom --}}
                    <td class="sof-spacer" colspan="2"></td>
                @endif
            </tr>
        @endforeach
    </table>
</div>
@if ($barang->isNotEmpty())
    <div style="margin: 0 0 14px; page-break-inside: avoid;">
        <p class="sof-section">Daftar Barang</p>

        <table class="sof-table">
            <thead>
                <tr>
                    <th style="width: 6%; text-align: center;">No</th>
                    <th>Nama Barang</th>
                    <th style="width: 10%; text-align: center;">Qty</th>
                    <th style="width: 20%;">Harga</th>
                    <th style="width: 22%;">Subtotal</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($barang as $index => $item)
                    <tr>
                        <td class="sof-number">{{ $index + 1 }}</td>
                        <td>{{ $item->name }}</td>
                        <td class="sof-number">{{ (int) $item->quantity }}</td>
                        <td class="sof-money">{{ $formatRupiah($item->price) }}</td>
                        <td class="sof-money">
                            {{ $formatRupiah((float) $item->price * (int) $item->quantity) }}
                        </td>
                    </tr>
                @endforeach

                <tr class="sof-total">
                    <td colspan="4" class="sof-money">Total Barang</td>
                    <td class="sof-money">{{ $formatRupiah($totalBarang) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
@endif

@if ($services->isNotEmpty())
    <div style="margin: 0 0 14px; page-break-inside: avoid;">
        <p class="sof-section">Daftar Service</p>

        <table class="sof-table">
            <thead>
                <tr>
                    <th style="width: 6%; text-align: center;">No</th>
                    <th>Nama Service</th>
                    <th style="width: 22%;">Harga</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($services as $index => $item)
                    <tr>
                        <td class="sof-number">{{ $index + 1 }}</td>
                        <td>{{ $item->name }}</td>
                        <td class="sof-money">{{ $formatRupiah($item->price) }}</td>
                    </tr>
                @endforeach

                <tr class="sof-total">
                    <td colspan="2" class="sof-money">Total Service</td>
                    <td class="sof-money">{{ $formatRupiah($totalService) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
@endif