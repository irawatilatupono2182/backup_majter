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

        .header-table {
            width: 100%;
            border-bottom: 2px solid #000;
            padding: 10px 15px 20px 15px;
            margin-bottom: 12px;
        }

        .logo-box {
            width: 80px;
            height: 80px;
            border: 0px solid #000;
            display: inline-block;
            text-align: center;
            vertical-align: middle;
            padding: 5px;
        }

        .logo-box img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .company-info {
            font-size: 11px;
            line-height: 1.5;
            font-weight: normal;
        }

        .company-info strong {
            font-size: 28px;
            font-weight: 900;
        }

        .invoice-title {
            font-size: 42px;
            font-weight: 900;
            letter-spacing: 8px;
            line-height: 1;
            margin: 0;
        }

        .info-box {
            font-size: 11px;
            line-height: 1.6;
        }

        .items-table {
            width: 100%;
            margin: 12px 0;
            font-size: 12px;
            border: none;
        }

        .items-table th {
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
            border-left: 1.5px solid #000;
            border-right: 1.5px solid #000;
            padding: 8px 5px;
            text-align: center;
            font-weight: bold;
            font-size: 13px;
            background-color: #fff;
        }

        .items-table td {
            border-left: 1.5px solid #000;
            border-right: 1.5px solid #000;
            border-bottom: 1px solid #ddd;
            padding: 8px 5px;
            text-align: left;
            vertical-align: top;
            font-size: 12px;
        }

        .items-table tbody tr:last-child td {
            border-bottom: 2px solid #000;
        }

        .items-table .col-no {
            width: 50px;
            text-align: center;
        }

        .items-table .col-desc {
            width: auto;
            padding-left: 8px;
        }

        .items-table .col-qty {
            width: 90px;
            text-align: center;
        }

        .items-table .col-price {
            width: 150px;
            text-align: right;
            padding-right: 8px;
        }

        .items-table .col-amount {
            width: 150px;
            text-align: right;
            padding-right: 8px;
        }

        .items-table .col-notes {
            width: 130px;
            text-align: left;
            padding-left: 8px;
            font-size: 11px;
        }

        .total-row {
            font-weight: bold;
            background-color: #fff;
        }

        .footer-section {
            margin-top: 10px;
            font-size: 11px;
        }

        .bank-box {
            border: 1.5px solid #000;
            padding: 12px;
            width: 40%;
            float: left;
            margin-right: 5%;
        }

        .signature-box {
            width: 53%;
            float: right;
            text-align: center;
            padding: 12px;
        }

        .signature-space {
            height: 70px;
            margin: 15px 0;
        }

        .signature-line {
            border-top: 1.5px dotted #000;
            display: inline-block;
            min-width: 200px;
            margin-top: 8px;
            padding-top: 5px;
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
                <!-- Logo + Company -->
                <td style="width: 50%; vertical-align: top;">
                    <table style="width: 100%;">
                        <tr>
                            <td style="width: 90px; vertical-align: top;">
                                <div class="logo-box"><img src="{{ public_path('logo/aj.png') }}" alt="AJ Logo"></div>
                            </td>
                            <td style="vertical-align: top; padding-left: 12px;">
                                <div class="company-info">
                                    <strong>CV. ADAM JAYA</strong><br>
                                    Jl. Sadang, Rahayu, Kab. Bandung<br>
                                    Jawa Barat 40218<br>
                                    Telp: 085721322812 | Email: majter.ads@gmail.com
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
                
                <!-- INVOICE Title -->
                <td style="width: 50%; vertical-align: middle; text-align: right; padding-right: 0;">
                    <div class="invoice-title">INVOICE</div>
                </td>
            </tr>
            <tr><td colspan="2" style="height: 8px;"></td></tr>
        </table>

        <!-- Content Section -->
        <div style="padding: 10px 12px; margin-bottom: 10px;">
        
        <!-- Info Section -->
        <table style="width: 100%; margin-bottom: 8px; border-collapse: collapse;">
            <tr>
                <!-- BILL TO -->
                <td style="width: 55%; vertical-align: top; padding-right: 15px;">
                    <div style="font-size: 12px; line-height: 1.6; margin-bottom: 8px;">
                        <strong style="font-weight: bold;">BILL TO  :  {{ strtoupper($invoice->customer->name ?? 'PT. ARGO MANUNGGAL TRIASTA') }}</strong>
                    </div>
                    <div style="font-size: 11px; line-height: 1.5; margin-top: 8px;">
                        {{ $invoice->customer->address_bill_to ?? $invoice->customer->address_ship_to ?? 'Wisma Argo Manunggal Lt 2, Jalan Jend. Gatat Subroto Kav 22 Karet Semanggi - Setia budi, Jakarta Selatan, DKI Jakarta 12930, Indonesia' }}
                    </div>
                </td>
                
                <!-- Date + Invoice Info -->
                <td style="width: 45%; vertical-align: top; padding-left: 15px;">
                    <div style="text-align: right; padding-right: 25px;">
                        <table style="font-size: 11px; margin-bottom: 8px; margin-left: auto;">
                            <tr>
                                <td colspan="2" style="padding: 2px 0; padding-bottom: 8px;">
                                    <strong>BANDUNG, {{ $invoice->invoice_date ? strtoupper($invoice->invoice_date->translatedFormat('d/F/Y')) : strtoupper(now()->translatedFormat('d/F/Y')) }}</strong>
                                </td>
                            </tr>
                            <tr>
                                <td style="width: 100px; padding: 2px 0;">NO INVOICE</td>
                                <td style="padding: 2px 0;">: {{ $invoice->invoice_number }}</td>
                            </tr>
                            @if($invoice->po_number)
                            <tr>
                                <td style="padding: 2px 0;">PO NO.</td>
                                <td style="padding: 2px 0;">: {{ $invoice->po_number }}</td>
                            </tr>
                            @endif
                            @if($invoice->payment_terms)
                            <tr>
                                <td style="padding: 2px 0;">TOP</td>
                                <td style="padding: 2px 0;">: {{ $invoice->payment_terms }} HARI</td>
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
                            echo '<tr style="height: 28px;"><td class="col-no">&nbsp;</td><td class="col-desc">&nbsp;</td><td class="col-qty">&nbsp;</td><td class="col-price">&nbsp;</td><td class="col-amount">&nbsp;</td></tr>';
                        }
                    }
                @endphp
            </tbody>
        </table>

        <!-- Totals Section -->
        <div style="margin-top: 12px; text-align: right; font-size: 11px;">
            <table style="width: 480px; margin-left: auto; border-collapse: collapse;">
                <tr>
                    <td style="padding: 3px 12px 3px 0; width: 170px; text-align: left;">SUB. TOTAL</td>
                    <td style="padding: 3px 0; width: 310px; text-align: left;">: Rp. {{ number_format($grandTotal, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td style="padding: 3px 12px 3px 0; text-align: left;">DPP NILAI LAIN</td>
                    <td style="padding: 3px 0; text-align: left;">: Rp. {{ number_format(0, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td style="padding: 3px 12px 3px 0; text-align: left;"><strong>PPN</strong></td>
                    <td style="padding: 3px 0; text-align: left;">: Rp. {{ number_format(isset($invoice->tax) ? $invoice->tax : 0, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td style="padding: 3px 12px 3px 0; border-bottom: 2px solid #000; text-align: left;"><strong>GRAND TOTAL</strong></td>
                    <td style="padding: 3px 0; border-bottom: 2px solid #000; text-align: left;">: <strong>Rp. {{ number_format($invoice->grand_total ?? $grandTotal, 0, ',', '.') }}</strong></td>
                </tr>
            </table>
        </div>

        <!-- Footer -->
        <div class="footer-section clearfix" style="margin-top: 5px; margin-bottom: 10px;">
            <!-- Bank Info -->
            <div class="bank-box">
                <strong style="font-size: 12px;">PEMBAYARAN HARAP DI TRANSFER KE :</strong><br>
                <div style="margin-top: 8px; line-height: 1.7;">
                    <table style="width: 100%; font-size: 11px;">
                        <tr>
                            <td style="width: 65px; vertical-align: top;">BCA</td>
                            <td style="vertical-align: top;">- 156 156 2275 A/N <strong>ADAM JAYA CV</strong></td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <!-- Signature -->
            <div class="signature-box">
                <div style="font-size: 12px;">HORMAT KAMI</div>
                <div class="signature-space" style="min-height: 80px; margin: 15px 0 8px 0;"></div>
            </div>
        
        </div><!-- End content -->
        </div>

    </div>
</body>
</html>
