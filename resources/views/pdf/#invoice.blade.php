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
            padding: 10mm 15mm;
        }

        .container {
            width: 100%;
            max-width: 28cm;
            margin: 0 auto;
        }

        table {
            border-collapse: collapse;
        }

        .header-table {
            width: 100%;
            margin-bottom: 10px;
        }

        .logo-box {
            width: 100px;
            height: 100px;
            border: 2px solid #000;
            display: inline-block;
            text-align: center;
            line-height: 96px;
            font-size: 36px;
            font-weight: bold;
        }

        .company-info {
            font-size: 8px;
            line-height: 1.5;
        }

        .company-info strong {
            font-size: 10px;
        }

        .invoice-title {
            writing-mode: vertical-rl;
            text-orientation: upright;
            font-size: 42px;
            font-weight: bold;
            letter-spacing: 8px;
            line-height: 1;
        }

        .info-box {
            font-size: 9px;
            line-height: 1.6;
        }

        .items-table {
            width: 100%;
            margin: 10px 0;
            font-size: 9px;
        }

        .items-table th {
            border: 1px solid #000;
            padding: 5px 3px;
            text-align: center;
            font-weight: bold;
            background-color: #f5f5f5;
        }

        .items-table td {
            border: 1px solid #000;
            padding: 4px 3px;
            text-align: left;
            vertical-align: middle;
        }

        .items-table .col-no {
            width: 35px;
            text-align: center;
        }

        .items-table .col-date {
            width: 80px;
            text-align: center;
        }

        .items-table .col-desc {
            width: auto;
            padding-left: 6px;
        }

        .items-table .col-qty {
            width: 60px;
            text-align: center;
        }

        .items-table .col-price {
            width: 110px;
            text-align: right;
            padding-right: 6px;
        }

        .items-table .col-amount {
            width: 120px;
            text-align: right;
            padding-right: 6px;
        }

        .items-table .col-subtotal {
            width: 130px;
            text-align: right;
            padding-right: 6px;
        }

        .total-row {
            font-weight: bold;
            background-color: #f0f0f0;
        }

        .footer-section {
            margin-top: 15px;
            font-size: 9px;
        }

        .bank-box {
            border: 1px solid #000;
            padding: 8px;
            width: 48%;
            float: left;
            margin-right: 2%;
        }

        .signature-box {
            width: 48%;
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
        
        <!-- Header -->
        <table class="header-table">
            <tr>
                <!-- Logo + Company + Bill To -->
                <td style="width: 60%; vertical-align: top;">
                    <table style="width: 100%;">
                        <tr>
                            <td style="width: 120px; vertical-align: top;">
                                <div class="logo-box">AJ</div>
                            </td>
                            <td style="vertical-align: top; padding-left: 15px;">
                                <div class="company-info">
                                    <strong>CV. ADAM JAYA</strong><br>
                                    Jalan Holis Indah Blok G4 No.5<br>
                                    RT/RW: 005/008<br>
                                    Cigondewah Rahayu, Bandung Kulon<br>
                                    Bandung - 40215<br>
                                    Telp: 62 - 22 - 6079185
                                </div>
                                
                                <div style="margin-top: 12px;">
                                    <strong style="font-size: 10px;">BILL TO:</strong><br>
                                    <div style="font-size: 9px; margin-top: 3px;">
                                        <strong>{{ strtoupper($invoice->customer->name ?? 'N/A') }}</strong><br>
                                        {{ $invoice->customer->address_bill_to ?? $invoice->customer->address_ship_to ?? '-' }}<br>
                                        @if($invoice->customer->phone)
                                            Telp: {{ $invoice->customer->phone }}
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
                
                <!-- INVOICE Title + Info -->
                <td style="width: 40%; vertical-align: top; text-align: right; padding-right: 15px;">
                    <table style="width: 100%;">
                        <tr>
                            <td style="text-align: right; vertical-align: top;">
                                <div style="display: inline-block;">
                                    <div class="invoice-title">INVOICE</div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align: right; padding-top: 15px;">
                                <div class="info-box" style="text-align: left; display: inline-block;">
                                    <table style="font-size: 9px;">
                                        <tr>
                                            <td style="width: 80px; padding: 2px 0;"><strong>DATE</strong></td>
                                            <td style="padding: 2px 0;">: {{ $invoice->invoice_date ? $invoice->invoice_date->format('d/m/Y') : now()->format('d/m/Y') }}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 2px 0;"><strong>TO NO</strong></td>
                                            <td style="padding: 2px 0;">: {{ $invoice->customer->customer_code ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 2px 0;"><strong>INVOICE NO</strong></td>
                                            <td style="padding: 2px 0;">: {{ $invoice->invoice_number }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th class="col-no">NO</th>
                    <th class="col-date">PENJUALAN<br>TANGGAL/TGL</th>
                    <th class="col-desc">DESKRIPSI</th>
                    <th class="col-qty">QTY</th>
                    <th class="col-price">HARGA<br>SATUAN (RP)</th>
                    <th class="col-amount">PRINCIPAL<br>AMOUNT</th>
                    <th class="col-subtotal">SUB TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $no = 1;
                    $grandTotal = 0;
                @endphp
                @forelse($invoice->items as $item)
                @php
                    $principal = $item->quantity * $item->unit_price;
                    $subtotal = $item->subtotal;
                    $grandTotal += $subtotal;
                @endphp
                <tr>
                    <td class="col-no">{{ $no++ }}</td>
                    <td class="col-date">{{ $invoice->invoice_date ? $invoice->invoice_date->format('d/m/Y') : now()->format('d/m/Y') }}</td>
                    <td class="col-desc">{{ $item->product->name ?? 'N/A' }}</td>
                    <td class="col-qty">{{ number_format($item->quantity, 0, ',', '.') }}</td>
                    <td class="col-price">{{ number_format($item->unit_price, 0, ',', '.') }}</td>
                    <td class="col-amount">{{ number_format($principal, 0, ',', '.') }}</td>
                    <td class="col-subtotal">{{ number_format($subtotal, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 15px; font-style: italic; color: #666;">
                        Tidak ada item
                    </td>
                </tr>
                @endforelse
                
                @php
                    $emptyRows = 8 - count($invoice->items);
                    if ($emptyRows > 0) {
                        for ($i = 0; $i < $emptyRows; $i++) {
                            echo '<tr><td class="col-no">&nbsp;</td><td class="col-date">&nbsp;</td><td class="col-desc">&nbsp;</td><td class="col-qty">&nbsp;</td><td class="col-price">&nbsp;</td><td class="col-amount">&nbsp;</td><td class="col-subtotal">&nbsp;</td></tr>';
                        }
                    }
                @endphp
                
                <!-- Grand Total Row -->
                <tr class="total-row">
                    <td colspan="6" style="text-align: right; padding-right: 10px;">
                        <strong>AMOUNT (RP)</strong>
                    </td>
                    <td class="col-subtotal">
                        <strong>{{ number_format($invoice->grand_total ?? $grandTotal, 0, ',', '.') }}</strong>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Footer -->
        <div class="footer-section clearfix">
            <!-- Bank Info -->
            <div class="bank-box">
                <strong>Pembayaran dapat dilakukan melalui:</strong><br>
                <div style="margin-top: 8px; line-height: 1.8;">
                    <table style="width: 100%; font-size: 9px;">
                        <tr>
                            <td style="width: 60px; vertical-align: top;">Bank</td>
                            <td style="vertical-align: top;">: <strong>BCA 0875 8725</strong></td>
                        </tr>
                        <tr>
                            <td style="vertical-align: top;">A.N.</td>
                            <td style="vertical-align: top;">: <strong>DR DJAJAYA</strong></td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <!-- Signature -->
            <div class="signature-box">
                <strong>Hormat kami,</strong><br>
                <strong>CV. ADAM JAYA</strong>
                <div class="signature-space"></div>
                <div class="signature-line">
                    (...................................)
                </div>
            </div>
        </div>

    </div>
</body>
</html>
