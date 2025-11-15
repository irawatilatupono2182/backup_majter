<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Invoice - {{ $invoice->invoice_number }}</title>
    <style>
        @page {
            margin: 0;
            size: A4 landscape;
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
            padding: 0.8cm 1.2cm;
        }

        .container {
            width: 100%;
            max-width: 28cm;
            margin: 0 auto;
            padding: 0;
        }

        table {
            border-collapse: collapse;
        }

        /* Header Section - Alternative Style */
        .header-table {
            width: 100%;
            border-bottom: 1.5px solid #000;
            padding: 6px 0px 6px 0px;
            margin-bottom: 8px;
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

        .invoice-title {
            text-align: left;
            font-size: 22px;
            font-weight: 900;
            letter-spacing: 6px;
            line-height: 1;
            margin: 0;
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

        .info-box {
            font-size: 7.5px;
            line-height: 1.5;
        }

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
            text-align: left;
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

        .items-table .col-desc {
            width: auto;
            padding-left: 5px;
        }

        .items-table .col-qty {
            width: 65px;
            text-align: center;
        }

        .items-table .col-price {
            width: 120px;
            text-align: right;
            padding-right: 5px;
        }

        .items-table .col-amount {
            width: 120px;
            text-align: right;
            padding-right: 5px;
        }

        .items-table .col-notes {
            width: 100px;
            text-align: left;
            padding-left: 5px;
            font-size: 7px;
        }

        .total-row {
            font-weight: bold;
            background-color: #fff;
        }

        .footer-section {
            margin-top: 5px;
            font-size: 7.5px;
        }

        .bank-box {
            border: 1px solid #000;
            padding: 8px;
            width: 40%;
            float: left;
            margin-right: 5%;
        }

        .signature-box {
            width: 53%;
            float: right;
            text-align: center;
            padding: 8px;
        }

        .signature-space {
            height: 50px;
            margin: 10px 0;
        }

        .signature-line {
            border-top: 1px dotted #000;
            display: inline-block;
            min-width: 150px;
            margin-top: 5px;
            padding-top: 3px;
        }

        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .bold {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        
        <!-- Header - Alternative Style -->
        <table class="header-table">
            <tr>
                <!-- Left: Title -->
                <td style="width: 50%; vertical-align: middle; padding-left: 0;">
                    <div class="invoice-title">INVOICE</div>
                </td>
                
                <!-- Right: Logo + Company Brand -->
                <td style="width: 50%; vertical-align: middle; text-align: right; padding-right: 0;">
                    <div style="display: inline-block; text-align: right;">
                        <div class="logo-box" style="display: inline-block; vertical-align: middle; margin-right: 8px;"><img src="{{ public_path('logo/majter.png') }}" alt="AJ Logo"></div>
                        <span style="display: inline-block; vertical-align: middle; font-size: 20px; font-weight: 300; margin: 0 8px; color: #000;">|</span>
                        <div class="company-logo-section" style="display: inline-block; vertical-align: middle; margin-left: 8px;">
                            <div class="company-brand">MAJTER</div>
                            <div class="company-tagline">BANDUNG - JAWA BARAT 40218</div>
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <!-- Content Section -->
        <div style="padding: 10px 12px; margin-bottom: 10px;">
        
        <!-- Info Section -->
        <table style="width: 100%; margin-bottom: 8px; border-collapse: collapse;">
            <tr>
                <!-- BILL TO -->
                <td style="width: 55%; vertical-align: top; padding-right: 10px;">
                    <div style="font-size: 8px; line-height: 1.6; margin-bottom: 5px;">
                        <strong style="font-weight: bold;">BILL TO  :  {{ strtoupper($invoice->customer->name ?? 'PT. ARGO MANUNGGAL TRIASTA') }}</strong>
                    </div>
                    <div style="font-size: 7px; line-height: 1.4; margin-top: 5px;">
                        {{ $invoice->customer->address_bill_to ?? $invoice->customer->address_ship_to ?? 'Wisma Argo Manunggal Lt 2, Jalan Jend. Gatat Subroto Kav 22 Karet Semanggi - Setia budi, Jakarta Selatan, DKI Jakarta 12930, Indonesia' }}
                    </div>
                </td>
                
                <!-- Date + Invoice Info -->
                <td style="width: 45%; vertical-align: top; padding-left: 10px;">
                    <div style="text-align: right; padding-right: 20px;">
                        <table style="font-size: 7.5px; margin-bottom: 5px; margin-left: auto;">
                            <tr>
                                <td colspan="2" style="padding: 1px 0; padding-bottom: 5px;">
                                    <strong>BANDUNG, {{ $invoice->invoice_date ? strtoupper($invoice->invoice_date->translatedFormat('d/F/Y')) : strtoupper(now()->translatedFormat('d/F/Y')) }}</strong>
                                </td>
                            </tr>
                            <tr>
                                <td style="width: 80px; padding: 1px 0;">NO INVOICE</td>
                                <td style="padding: 1px 0;">: {{ $invoice->invoice_number }}</td>
                            </tr>
                            @if($invoice->po_number)
                            <tr>
                                <td style="padding: 1px 0;">PO NO.</td>
                                <td style="padding: 1px 0;">: {{ $invoice->po_number }}</td>
                            </tr>
                            @endif
                            @if($invoice->payment_terms)
                            <tr>
                                <td style="padding: 1px 0;">TOP</td>
                                <td style="padding: 1px 0;">: {{ $invoice->payment_terms }} HARI</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </td>
            </tr>
        </table>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th class="col-no">NO</th>
                    <th class="col-desc">DESCRIPTION</th>
                    <th class="col-qty">QTY</th>
                    <th class="col-price">HARGA SATUAN (Rp.)</th>
                    <th class="col-amount">AMOUNT (Rp.)</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $no = 1;
                    $grandTotal = 0;
                @endphp
                @forelse($invoice->items as $item)
                @php
                    $amount = $item->subtotal ?? ($item->quantity * $item->unit_price);
                    $grandTotal += $amount;
                @endphp
                <tr>
                    <td class="col-no">{{ $no++ }}.</td>
                    <td class="col-desc">{{ strtoupper($item->product->name ?? 'N/A') }}</td>
                    <td class="col-qty">{{ number_format($item->quantity ?? 0, 0, ',', '.') }} {{ strtoupper($item->unit ?? 'PCS') }}</td>
                    <td class="col-price">Rp. {{ number_format($item->unit_price ?? 0, 0, ',', '.') }}</td>
                    <td class="col-amount">Rp. {{ number_format($amount, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center" style="padding: 15px; font-style: italic;">
                        Tidak ada item
                    </td>
                </tr>
                @endforelse
                
                @php
                    $emptyRows = 8 - count($invoice->items);
                    if ($emptyRows > 0) {
                        for ($i = 0; $i < $emptyRows; $i++) {
                            echo '<tr style="height: 20px;"><td class="col-no">&nbsp;</td><td class="col-desc">&nbsp;</td><td class="col-qty">&nbsp;</td><td class="col-price">&nbsp;</td><td class="col-amount">&nbsp;</td></tr>';
                        }
                    }
                @endphp
            </tbody>
        </table>

        <!-- Totals Section -->
        <div style="margin-top: 8px; text-align: right; font-size: 7.5px;">
            <table style="width: 380px; margin-left: auto; border-collapse: collapse;">
                <tr>
                    <td style="padding: 2px 10px 2px 0; width: 140px; text-align: left;">SUB. TOTAL</td>
                    <td style="padding: 2px 0; width: 240px; text-align: left;">: Rp. {{ number_format($grandTotal, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td style="padding: 2px 10px 2px 0; text-align: left;">DPP NILAI LAIN</td>
                    <td style="padding: 2px 0; text-align: left;">: Rp. {{ number_format(0, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td style="padding: 2px 10px 2px 0; border-bottom: 1.5px solid #000; text-align: left;"><strong>GRAND TOTAL</strong></td>
                    <td style="padding: 2px 0; border-bottom: 1.5px solid #000; text-align: left;">: <strong>Rp. {{ number_format($invoice->grand_total ?? $grandTotal, 0, ',', '.') }}</strong></td>
                </tr>
            </table>
        </div>

        <!-- Footer -->
        <div class="footer-section clearfix" style="margin-top: 5px; margin-bottom: 10px;">
            <!-- Bank Info -->
            <div class="bank-box">
                <strong style="font-size: 8px;">PEMBAYARAN HARAP DI TRANSFER KE :</strong><br>
                <div style="margin-top: 5px; line-height: 1.6;">
                    <table style="width: 100%; font-size: 7.5px;">
                        <tr>
                            <td style="width: 50px; vertical-align: top;">BCA</td>
                            <td style="vertical-align: top;">- 139 0800 645 A/N <strong>DIO GIANI PUTRA</strong></td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <!-- Signature -->
            <div class="signature-box">
                <div style="font-size: 8px;">HORMAT KAMI</div>
                <div class="signature-space" style="min-height: 60px; margin: 10px 0 5px 0;"></div>
            </div>
        
        </div><!-- End content -->
        </div>

    </div>
</body>
</html>
