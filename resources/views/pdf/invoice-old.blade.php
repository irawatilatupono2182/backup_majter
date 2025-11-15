<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Invoice - {{ $invoice->invoice_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            line-height: 1.3;
            padding: 10mm;
            background: white;
        }
        
        .container {
            width: 100%;
            max-width: 28cm; /* A4 Landscape width */
            margin: 0 auto;
        }
        
        table {
            border-collapse: collapse;
        }
        
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
    </style>
</head>
<body>
    <div class="container">
        
        <!-- Header with Logo, Company Info, and Invoice Title Vertical -->
        <table style="width: 100%; margin-bottom: 8px; border-collapse: collapse;">
            <tr>
                <!-- Left: Logo + Company Info -->
                <td style="width: 60%; vertical-align: top;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="width: 150px; vertical-align: top; padding-right: 15px;">
                                <!-- Logo Box -->
                                <div style="width: 120px; height: 120px; border: 2px solid #000; display: flex; align-items: center; justify-content: center;">
                                    <div style="text-align: center; font-size: 36px; font-weight: bold;">AJ</div>
                                </div>
                            </td>
                            <td style="vertical-align: middle; padding-left: 10px;">
                                <!-- Company Info -->
                                <div style="font-size: 10px; line-height: 1.5;">
                                    <div style="margin-top: 8px; font-size: 9px;">
                                        <strong>CV. ADAM JAYA</strong><br>
                                        Jalan Holis Indah Blok G4 No.5<br>
                                        RT/RW: 005/008<br>
                                        Cigondewah Rahayu, Bandung Kulon<br>
                                        Bandung - 40215<br>
                                        Telp: 62 - 22 - 6079185
                                    </div>
                                    
                                    <div style="font-weight: bold; margin-top: 10px; margin-bottom: 3px; font-size: 10px;">BILL TO:</div>
                                    <div style="font-size: 9px;">
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
                
                <!-- Right: INVOICE Title Vertical + Info -->
                <td style="width: 40%; vertical-align: top; text-align: right; padding-right: 20px;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="text-align: right; vertical-align: top;">
                                <!-- INVOICE Title Vertical -->
                                <div style="display: inline-block; text-align: center; margin-bottom: 15px;">
                                    <div style="writing-mode: vertical-rl; text-orientation: upright; font-size: 48px; font-weight: bold; letter-spacing: 12px; line-height: 1;">
                                        INVOICE
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align: right; vertical-align: top; padding-top: 10px;">
                                <!-- Info Section: DATE, TO NO, INVOICE NO vertikal -->
                                <table style="width: 100%; border-collapse: collapse; font-size: 9px;">
                                    <tr>
                                        <td style="width: 80px; text-align: left; padding: 2px 0;">DATE</td>
                                        <td style="text-align: left; padding: 2px 0;">: {{ $invoice->invoice_date ? $invoice->invoice_date->format('d/m/Y') : now()->format('d/m/Y') }}</td>
                                    </tr>
                                    <tr>
                                        <td style="text-align: left; padding: 2px 0;">TO NO</td>
                                        <td style="text-align: left; padding: 2px 0;">: {{ $invoice->customer->customer_code ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td style="text-align: left; padding: 2px 0;">INVOICE NO</td>
                                        <td style="text-align: left; padding: 2px 0;">: {{ $invoice->invoice_number }}</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <!-- Items Table -->
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 8px;">
            <thead>
                <tr>
                    <th style="width: 35px; border: 1px solid #000; padding: 6px 3px; text-align: center; font-size: 8px; font-weight: bold; vertical-align: middle;">NO</th>
                    <th style="width: 70px; border: 1px solid #000; padding: 6px 3px; text-align: center; font-size: 8px; font-weight: bold; vertical-align: middle; line-height: 1.2;">PENJUALAN<br>TANGGAL/TGL</th>
                    <th style="border: 1px solid #000; padding: 6px 5px; text-align: center; font-size: 8px; font-weight: bold; vertical-align: middle;">DESKRIPSI</th>
                    <th style="width: 50px; border: 1px solid #000; padding: 6px 3px; text-align: center; font-size: 8px; font-weight: bold; vertical-align: middle;">QTY</th>
                    <th style="width: 90px; border: 1px solid #000; padding: 6px 3px; text-align: center; font-size: 8px; font-weight: bold; vertical-align: middle; line-height: 1.2;">HARGA<br>SATUAN(RP)</th>
                    <th style="width: 90px; border: 1px solid #000; padding: 6px 3px; text-align: center; font-size: 8px; font-weight: bold; vertical-align: middle; line-height: 1.2;">PRINCIPAL<br>AMOUNT</th>
                    <th style="width: 100px; border: 1px solid #000; padding: 6px 3px; text-align: center; font-size: 8px; font-weight: bold; vertical-align: middle;">SUB TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalQty = 0;
                    $totalPrincipal = 0;
                    $totalSubtotal = 0;
                @endphp
                
                @forelse($invoice->items as $index => $item)
                    @php
                        $principal = $item->quantity * $item->unit_price;
                        $subtotal = $item->subtotal;
                        $totalQty += $item->quantity;
                        $totalPrincipal += $principal;
                        $totalSubtotal += $subtotal;
                    @endphp
                    <tr>
                        <td style="border: 1px solid #000; padding: 6px 3px; text-align: center; font-size: 8px; vertical-align: middle;">{{ $index + 1 }}</td>
                        <td style="border: 1px solid #000; padding: 6px 3px; text-align: center; font-size: 8px; vertical-align: middle;">{{ $invoice->invoice_date ? $invoice->invoice_date->format('d/m/Y') : '' }}</td>
                        <td style="border: 1px solid #000; padding: 6px 5px; text-align: left; font-size: 8px; vertical-align: middle;">{{ strtoupper($item->product->name ?? 'N/A') }}</td>
                        <td style="border: 1px solid #000; padding: 6px 3px; text-align: center; font-size: 8px; vertical-align: middle;">{{ number_format($item->quantity, 0, ',', '.') }}</td>
                        <td style="border: 1px solid #000; padding: 6px 3px; text-align: right; font-size: 8px; vertical-align: middle;">{{ number_format($item->unit_price, 0, ',', '.') }}</td>
                        <td style="border: 1px solid #000; padding: 6px 3px; text-align: right; font-size: 8px; vertical-align: middle;">{{ number_format($principal, 0, ',', '.') }}</td>
                        <td style="border: 1px solid #000; padding: 6px 3px; text-align: right; font-size: 8px; vertical-align: middle;">{{ number_format($subtotal, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td style="border: 1px solid #000; padding: 20px; text-align: center; font-size: 9px;" colspan="7">Tidak ada data</td>
                    </tr>
                @endforelse
                
                <!-- Empty rows for spacing -->
                @php
                    $itemCount = $invoice->items->count();
                    $emptyRows = max(0, 3 - $itemCount);
                @endphp
                
                @for($i = 0; $i < $emptyRows; $i++)
                    <tr>
                        <td style="border: 1px solid #000; padding: 18px 3px;">&nbsp;</td>
                        <td style="border: 1px solid #000; padding: 18px 3px;">&nbsp;</td>
                        <td style="border: 1px solid #000; padding: 18px 3px;">&nbsp;</td>
                        <td style="border: 1px solid #000; padding: 18px 3px;">&nbsp;</td>
                        <td style="border: 1px solid #000; padding: 18px 3px;">&nbsp;</td>
                        <td style="border: 1px solid #000; padding: 18px 3px;">&nbsp;</td>
                        <td style="border: 1px solid #000; padding: 18px 3px;">&nbsp;</td>
                    </tr>
                @endfor
                
                <!-- Grand Total Row -->
                <tr>
                    <td colspan="5" style="border: 1px solid #000; padding: 10px 8px; text-align: center; font-weight: bold; font-size: 9px; background-color: #f0f0f0;">GRAND TOTAL</td>
                    <td style="border: 1px solid #000; padding: 10px 8px; text-align: center; font-weight: bold; font-size: 8px; background-color: #f0f0f0;">AMOUNT (RP)</td>
                    <td style="border: 1px solid #000; padding: 10px 8px; text-align: right; font-weight: bold; font-size: 9px; background-color: #f0f0f0;">{{ number_format($invoice->total_amount, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Footer Section -->
        <table style="width: 100%; margin-top: 12px; border-collapse: collapse;">
            <tr>
                <td style="width: 50%; vertical-align: top; padding-right: 15px;">
                    <!-- Bank Transfer Info Box -->
                    <div style="border: 2px solid #000; padding: 10px 12px; font-size: 9px; line-height: 1.5;">
                        <div style="font-weight: bold; margin-bottom: 6px; font-size: 9px;">PEMBAYARAN HARUS DI TRANSFER KE</div>
                        <table style="width: 100%; border-collapse: collapse; font-size: 9px;">
                            <tr>
                                <td style="width: 50px; padding: 2px 0;">Bank</td>
                                <td style="padding: 2px 0;">: <strong>BCA 0875 8725</strong></td>
                            </tr>
                            <tr>
                                <td style="padding: 2px 0;">A.N.</td>
                                <td style="padding: 2px 0;">: <strong>DR DJAJAYA</strong></td>
                            </tr>
                        </table>
                    </div>
                </td>
                <td style="width: 50%; vertical-align: top; text-align: center; padding-left: 15px;">
                    <!-- Signature Section -->
                    <div style="font-size: 9px; font-weight: bold; margin-bottom: 55px;">HORMAT KAMI</div>
                    <div style="text-align: center; font-size: 8px;">
                        ( <span style="display: inline-block; width: 180px; border-bottom: 1px solid #000;"></span> )
                    </div>
                </td>
            </tr>
        </table>

    </div>
</body>
</html>