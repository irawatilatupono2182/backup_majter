<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Surat Jalan - {{ $deliveryNote->sj_number ?? 'N/A' }}</title>
    <style>
        @page {
            margin: 0;
            size: A4 portrait;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background: white;
            margin: 0;
            padding: 15mm 10mm;
        }

        .container {
            width: 100%;
            max-width: 19cm;
            margin: 0 auto;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        .header-table {
            width: 100%;
            margin-bottom: 15px;
        }

        .logo-box {
            width: 80px;
            height: 80px;
            border: 2px solid #000;
            display: inline-block;
            text-align: center;
            line-height: 76px;
            font-size: 32px;
            font-weight: bold;
        }

        .company-info {
            font-size: 9px;
            line-height: 1.5;
        }

        .company-info strong {
            font-size: 11px;
        }

        .title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin: 15px 0 10px 0;
            text-decoration: underline;
            letter-spacing: 2px;
        }

        .info-section {
            font-size: 10px;
            margin-bottom: 15px;
            line-height: 1.8;
        }

        .info-section table {
            width: 100%;
        }

        .info-section td {
            padding: 2px 0;
            vertical-align: top;
        }

        .items-table {
            width: 100%;
            margin: 10px 0;
            font-size: 10px;
        }

        .items-table th {
            border-top: 1.5px solid #000;
            border-bottom: 1.5px solid #000;
            padding: 6px 4px;
            text-align: center;
            font-weight: bold;
            font-size: 10px;
        }

        .items-table td {
            padding: 5px 4px;
            text-align: left;
            vertical-align: top;
            border-bottom: 0.5px solid #ccc;
        }

        .items-table .col-no {
            width: 40px;
            text-align: center;
        }

        .items-table .col-item {
            width: auto;
            padding-left: 8px;
        }

        .items-table .col-qty {
            width: 120px;
            text-align: center;
        }

        .items-table .col-notes {
            width: 180px;
            text-align: left;
            padding-left: 8px;
        }

        .signature-section {
            margin-top: 30px;
            font-size: 10px;
        }

        .signature-section table {
            width: 100%;
        }

        .signature-section td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            padding: 5px;
        }

        .signature-box {
            min-height: 60px;
            margin: 5px 0;
        }

        .signature-name {
            border-top: 1px dotted #000;
            display: inline-block;
            min-width: 150px;
            margin-top: 5px;
            padding-top: 3px;
        }

        .text-note {
            font-size: 9px;
            font-style: italic;
            margin-top: 10px;
            line-height: 1.4;
        }
    </style>
</head>
<body>
    <div class="container">
        
        <!-- Header: Logo + Company Info + Date/Customer Info -->
        <table class="header-table">
            <tr>
                <td style="width: 100px; vertical-align: top;">
                    <div class="logo-box">AJ</div>
                </td>
                <td style="width: 50%; vertical-align: top; padding-left: 15px;">
                    <div class="company-info">
                        <strong>CV. ADAM JAYA</strong><br>
                        Jalan Holis Indah Blok G4 No.5<br>
                        RT/RW: 005/008<br>
                        Cigondewah Rahayu, Bandung Kulon<br>
                        Bandung - 40215<br>
                        Telp: 62 - 22 - 6079185
                    </div>
                </td>
                <td style="vertical-align: top; text-align: right; font-size: 9px; padding-right: 10px;">
                    <div style="margin-bottom: 8px;">
                        <strong>Tanggal:</strong> {{ $deliveryNote->delivery_date ? $deliveryNote->delivery_date->format('d/m/Y') : now()->format('d/m/Y') }}
                    </div>
                    <div style="line-height: 1.6;">
                        <strong>Kepada Yth:</strong><br>
                        {{ strtoupper($deliveryNote->customer->name ?? 'N/A') }}<br>
                        {{ $deliveryNote->customer->address_ship_to ?? $deliveryNote->customer->address_bill_to ?? '-' }}<br>
                        @if($deliveryNote->customer->phone)
                            Telp: {{ $deliveryNote->customer->phone }}
                        @endif
                    </div>
                </td>
            </tr>
        </table>

        <!-- Title -->
        <div class="title">SURAT JALAN</div>

        <!-- Info Section -->
        <div class="info-section">
            <table>
                <tr>
                    <td style="width: 80px;"><strong>No.</strong></td>
                    <td>: {{ $deliveryNote->sj_number ?? 'N/A' }}</td>
                </tr>
            </table>
            <div style="margin-top: 10px; font-size: 9px; line-height: 1.6;">
                Mohon diterima barang-barang tersebut dibawah ini dengan baik:
            </div>
            <table style="margin-top: 8px;">
                <tr>
                    <td style="width: 140px;">Jenis Kendaraan</td>
                    <td style="width: 10px;">:</td>
                    <td>{{ $deliveryNote->vehicle_type ?? '-' }}</td>
                </tr>
                <tr>
                    <td>No. Pol.</td>
                    <td>:</td>
                    <td>{{ $deliveryNote->vehicle_number ?? '-' }}</td>
                </tr>
            </table>
        </div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th class="col-no">No.</th>
                    <th class="col-item">Nama Barang</th>
                    <th class="col-qty">Banyaknya</th>
                    <th class="col-notes">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $no = 1;
                @endphp
                @forelse($deliveryNote->items as $item)
                <tr>
                    <td class="col-no">{{ $no++ }}</td>
                    <td class="col-item">{{ $item->product->name ?? 'N/A' }}</td>
                    <td class="col-qty">{{ number_format($item->quantity, 0, ',', '.') }} {{ $item->unit }}</td>
                    <td class="col-notes">{{ $item->notes ?? '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="text-align: center; padding: 20px; font-style: italic; color: #666;">
                        Tidak ada item
                    </td>
                </tr>
                @endforelse
                
                @php
                    $emptyRows = 8 - count($deliveryNote->items);
                    if ($emptyRows > 0) {
                        for ($i = 0; $i < $emptyRows; $i++) {
                            echo '<tr><td class="col-no">&nbsp;</td><td class="col-item">&nbsp;</td><td class="col-qty">&nbsp;</td><td class="col-notes">&nbsp;</td></tr>';
                        }
                    }
                @endphp
            </tbody>
        </table>

        <!-- Signature Section -->
        <div class="signature-section">
            <table>
                <tr>
                    <td>
                        <strong>Penerimaan Barang</strong>
                        <div class="signature-box"></div>
                        <div class="signature-name">
                            (...................................)
                        </div>
                    </td>
                    <td>
                        <strong>Hormat kami,</strong>
                        <div class="signature-box"></div>
                        <div class="signature-name">
                            (...................................)
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Note -->
        <div class="text-note">
            * Barang yang sudah dibeli tidak dapat dikembalikan kecuali ada kesepakatan<br>
            * Harap periksa barang sebelum meninggalkan toko
        </div>

    </div>
</body>
</html>
