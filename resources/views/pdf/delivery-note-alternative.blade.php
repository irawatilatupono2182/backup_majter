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
            padding: 1cm 1.2cm;
        }

        .container {
            width: 100%;
            max-width: 19cm;
            margin: 0 auto;
            padding: 0;
        }

        table {
            border-collapse: collapse;
        }

        /* Header Section - Alternative Style */
        .header {
            border-bottom: 1.5px solid #000;
            padding: 6px 0px 6px 0px;
        }

        .header-table {
            width: 100%;
        }

        .logo-box {
            width: 110px;
            height: 45px;
            border: 0px solid #000;
            display: inline-block;
            text-align: center;
            vertical-align: middle;
            margin-right: 8px;
            padding: 2px;
        }

        .logo-box img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .company-info {
            font-size: 7px;
            line-height: 1.35;
            font-weight: normal;
        }

        .company-info strong {
            font-size: 22px;
            font-weight: 900;
        }

        .title {
            text-align: left;
            font-size: 22px;
            font-weight: 900;
            margin: 0;
            letter-spacing: 6px;
        }

        .company-logo-section {
            text-align: left;
            font-size: 11px;
            line-height: 1.3;
            display: inline-block;
            vertical-align: middle;
        }

        .company-brand {
            font-size: 16px;
            font-weight: 900;
            letter-spacing: 2px;
        }

        .company-tagline {
            font-size: 7px;
            margin-top: 2px;
        }

        /* Content Section */
        .content {
            padding: 10px 12px;
        }

        .doc-info {
            font-size: 7.5px;
            margin-bottom: 8px;
            line-height: 1.6;
        }

        .doc-info table {
            width: 100%;
        }

        .doc-info td {
            padding: 1px 0;
            vertical-align: top;
        }

        /* Items Table */
        .items-table {
            width: 100%;
            margin: 8px 0;
            font-size: 8px;
            border: 1.5px solid #000;
        }

        .items-table th {
            border-top: none;
            border-bottom: 1.5px solid #000;
            border-left: 1px solid #000;
            border-right: 1px solid #000;
            padding: 5px 3px;
            text-align: center;
            font-weight: bold;
            font-size: 9px;
            background-color: #fff;
        }

        .items-table td {
            border-left: 1px solid #000;
            border-right: 1px solid #000;
            border-bottom: 0.5px solid #ddd;
            padding: 5px 3px;
            vertical-align: top;
            font-size: 8px;
        }

        .items-table tbody tr:last-child td {
            border-bottom: none;
        }

        .items-table .col-no {
            width: 35px;
            text-align: center;
        }

        .items-table .col-item {
            width: auto;
            text-align: left;
            padding-left: 5px;
        }

        .items-table .col-qty {
            width: 100px;
            text-align: center;
        }

        .items-table .col-notes {
            width: 150px;
            text-align: center;
        }

        /* Signature Section */
        .signature-section {
            margin-top: 20px;
            margin-bottom: 10px;
            font-size: 8px;
        }

        .signatures-table {
            width: 100%;
        }

        .signatures-table td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            padding: 5px;
        }

        .signature-space {
            min-height: 60px;
            margin: 10px 0 5px 0;
        }

        /* Notes */
        .notes {
            font-size: 6.5px;
            margin-top: 5px;
            line-height: 1.4;
        }

        .notes strong {
            font-weight: bold;
            font-size: 7px;
        }
    </style>
</head>
<body>
    <div class="container">
        
        <!-- Header - Alternative Style -->
        <div class="header">
            <table class="header-table">
                <tr>
                    <!-- Left: Title -->
                    <td style="width: 50%; vertical-align: middle; padding-left: 0;">
                        <div class="title">SURAT JALAN</div>
                    </td>
                    
                    <!-- Right: Logo + Company Brand -->
                    <td style="width: 50%; vertical-align: middle; text-align: right; padding-right: 0;">
                    <div style="display: inline-block; text-align: right;">
                        <div class="logo-box" style="display: inline-block; vertical-align: middle; margin-right: 8px;"><img src="{{ public_path('logo/majter.png') }}" alt="AJ Logo"></div>
                             <!-- Garis vertikal kustom yang lebih tinggi -->
                            <div style="display: inline-block; vertical-align: middle; height: 45px; width: 1px; background-color: #000; margin: 0 8px;"></div>

                            <div class="company-logo-section" style="display: inline-block; vertical-align: middle; margin-left: 8px;">
                                <div class="company-brand">AJT</div>
                                <div class="company-tagline">BANDUNG - JAWA BARAT 40218</div>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Content -->
        <div class="content">
            <!-- Document Info -->
            <div class="doc-info">
                <table>
                    <tr>
                        <td style="width: 55%; vertical-align: top; padding-right: 10px;">
                            <!-- Left Column -->
                            <div style="margin-bottom: 5px;">
                                <strong>BANDUNG, {{ $deliveryNote->delivery_date ? strtoupper($deliveryNote->delivery_date->translatedFormat('d/F/Y')) : strtoupper(now()->translatedFormat('d/F/Y')) }}</strong>
                            </div>
                            <table style="width: 100%; margin-bottom: 5px;">
                                <tr>
                                    <td style="width: 60px;">NOMOR</td>
                                    <td>: {{ $deliveryNote->sj_number ?? '-' }}</td>
                                </tr>
                                @if($deliveryNote->po_number)
                                <tr>
                                    <td>PO. NO</td>
                                    <td>: {{ $deliveryNote->po_number }}</td>
                                </tr>
                                @endif
                                @if($deliveryNote->po_date)
                                <tr>
                                    <td>TGL. PO</td>
                                    <td>: {{ $deliveryNote->po_date->format('d/m/Y') }}</td>
                                </tr>
                                @endif
                                @if($deliveryNote->top)
                                <tr>
                                    <td>TOP</td>
                                    <td>: {{ $deliveryNote->top }} HARI</td>
                                </tr>
                                @endif
                            </table>
                        </td>
                        <td style="width: 45%; vertical-align: top; padding-left: 10px;">
                            <!-- Right Column -->
                            <table style="width: 100%; font-size: 8px;">
                                <tr>
                                    <td style="width: 50px; padding: 1px 0;">TO</td>
                                    <td style="padding: 1px 0;">: <strong>{{ strtoupper($deliveryNote->customer->name ?? '-') }}@if($deliveryNote->customer->is_ppn ?? false) (PPN)@endif</strong></td>
                                </tr>
                                @if($deliveryNote->customer->contact_person)
                                <tr>
                                    <td style="padding: 1px 0; font-size: 7px;">Up</td>
                                    <td style="padding: 1px 0; font-size: 7px;">: {{ $deliveryNote->customer->contact_person }}</td>
                                </tr>
                                @endif
                            </table>
                            @if($deliveryNote->customer->address_ship_to || $deliveryNote->customer->address_bill_to)
                            <div style="margin-top: 5px; font-size: 8px;">
                                <strong>SHIP TO :</strong> {{ $deliveryNote->customer->address_ship_to ?? $deliveryNote->customer->address_bill_to }}
                            </div>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Items Table -->
            <table class="items-table">
                <thead>
                    <tr>
                        <th class="col-no">NO</th>
                        <th class="col-item">NAMA BARANG</th>
                        <th class="col-qty">BANYAKNYA</th>
                        <th class="col-notes">KETERANGAN</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $no = 1;
                    @endphp
                    @forelse($deliveryNote->items as $item)
                    <tr>
                        <td class="col-no">{{ $no++ }}.</td>
                        <td class="col-item">{{ strtoupper($item->product->name ?? 'N/A') }}</td>
                        <td class="col-qty">{{ number_format($item->quantity, 0, ',', '.') }} {{ strtoupper($item->unit) }}</td>
                        <td class="col-notes">{{ $item->notes ?? '' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 15px; font-style: italic;">
                            Tidak ada item
                        </td>
                    </tr>
                    @endforelse
                    
                    @php
                        $emptyRows = max(0, 10 - count($deliveryNote->items));
                        for ($i = 0; $i < $emptyRows; $i++) {
                            echo '<tr style="height: 18px;"><td class="col-no">&nbsp;</td><td class="col-item">&nbsp;</td><td class="col-qty">&nbsp;</td><td class="col-notes">&nbsp;</td></tr>';
                        }
                    @endphp
                </tbody>
            </table>

            <!-- Signature Section -->
            <div class="signature-section">
                <table class="signatures-table">
                    <tr>
                        <td style="text-align: center;">
                            <div>Yang Menyerahkan</div>
                            <div class="signature-space"></div>
                        </td>
                        <td style="text-align: center;">
                            <div>Yang Menerima</div>
                            <div class="signature-space"></div>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Notes -->
            @if($deliveryNote->notes)
            <div class="notes">
                <strong>Catatan:</strong> {{ $deliveryNote->notes }}
            </div>
            @endif
        </div>

    </div>
</body>
</html>
