<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Kwitansi - {{ $payment->payment_number ?? 'N/A' }}</title>
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
            padding: 15mm 12mm;
        }

        .container {
            width: 100%;
            max-width: 19cm;
            margin: 0 auto;
            border: 2px solid #000;
            padding: 15px;
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
            font-size: 20px;
            font-weight: bold;
            margin: 15px 0;
            letter-spacing: 3px;
            text-decoration: underline;
        }

        .receipt-number {
            text-align: right;
            font-size: 10px;
            margin-bottom: 15px;
        }

        .content-section {
            font-size: 11px;
            line-height: 2;
            margin: 20px 0;
        }

        .content-section table {
            width: 100%;
        }

        .content-section td {
            padding: 5px 0;
            vertical-align: top;
        }

        .amount-box {
            border: 1px solid #000;
            padding: 10px;
            margin: 15px 0;
            text-align: center;
            font-size: 12px;
        }

        .amount-words {
            font-style: italic;
            margin-top: 5px;
            font-size: 11px;
        }

        .signature-section {
            margin-top: 40px;
            font-size: 10px;
        }

        .signature-section table {
            width: 100%;
        }

        .signature-section td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            padding: 10px;
        }

        .signature-space {
            min-height: 70px;
            margin: 10px 0;
        }

        .signature-line {
            border-top: 1px dotted #000;
            display: inline-block;
            min-width: 180px;
            margin-top: 5px;
            padding-top: 5px;
        }

        .note-section {
            margin-top: 20px;
            font-size: 9px;
            font-style: italic;
            border-top: 1px solid #ddd;
            padding-top: 10px;
            color: #666;
        }

        .bold {
            font-weight: bold;
        }

        .underline {
            border-bottom: 1px dotted #000;
            display: inline-block;
            min-width: 300px;
        }
    </style>
</head>
<body>
    <div class="container">
        
        <!-- Header -->
        <table class="header-table">
            <tr>
                <td style="width: 100px; vertical-align: top;">
                    <div class="logo-box">AJ</div>
                </td>
                <td style="vertical-align: middle; padding-left: 15px;">
                    <div class="company-info">
                        <strong>CV. ADAM JAYA</strong><br>
                        Jalan Holis Indah Blok G4 No.5<br>
                        RT/RW: 005/008<br>
                        Cigondewah Rahayu, Bandung Kulon<br>
                        Bandung - 40215<br>
                        Telp: 62 - 22 - 6079185
                    </div>
                </td>
            </tr>
        </table>

        <!-- Title -->
        <div class="title">KWITANSI</div>

        <!-- Receipt Number -->
        <div class="receipt-number">
            <strong>No: {{ $payment->payment_number ?? 'N/A' }}</strong>
        </div>

        <!-- Content -->
        <div class="content-section">
            <table>
                <tr>
                    <td style="width: 150px;"><strong>Sudah terima dari</strong></td>
                    <td>: <span class="bold">{{ strtoupper($payment->invoice->customer->name ?? 'N/A') }}</span></td>
                </tr>
                <tr>
                    <td><strong>Uang sejumlah</strong></td>
                    <td>: <span class="bold">Rp {{ number_format($payment->amount ?? 0, 0, ',', '.') }},-</span></td>
                </tr>
                <tr>
                    <td><strong>Terbilang</strong></td>
                    <td>: <span class="bold underline">{{ $amountInWords ?? 'N/A' }} Rupiah</span></td>
                </tr>
                <tr>
                    <td><strong>Untuk pembayaran</strong></td>
                    <td>: Invoice No. <strong>{{ $payment->invoice->invoice_number ?? 'N/A' }}</strong></td>
                </tr>
                @if($payment->notes)
                <tr>
                    <td><strong>Keterangan</strong></td>
                    <td>: {{ $payment->notes }}</td>
                </tr>
                @endif
            </table>
        </div>

        <!-- Amount Box -->
        <div class="amount-box">
            <div style="font-size: 11px; margin-bottom: 5px;">JUMLAH PEMBAYARAN</div>
            <div style="font-size: 18px; font-weight: bold;">
                Rp {{ number_format($payment->amount ?? 0, 0, ',', '.') }},-
            </div>
            <div class="amount-words">
                ({{ $amountInWords ?? 'N/A' }} Rupiah)
            </div>
        </div>

        <!-- Signature Section -->
        <div class="signature-section">
            <table>
                <tr>
                    <td>
                        <div>Bandung, {{ $payment->payment_date ? $payment->payment_date->format('d F Y') : now()->format('d F Y') }}</div>
                        <div style="margin-top: 5px;"><strong>Yang Menerima,</strong></div>
                        <div class="signature-space"></div>
                        <div class="signature-line">
                            (...................................)
                        </div>
                    </td>
                    <td>
                        <div>&nbsp;</div>
                        <div style="margin-top: 5px;"><strong>Hormat kami,</strong></div>
                        <div class="signature-space"></div>
                        <div class="signature-line">
                            (...................................)
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Note -->
        <div class="note-section">
            * Kwitansi ini merupakan bukti pembayaran yang sah<br>
            * Harap disimpan dengan baik untuk keperluan administrasi
        </div>

    </div>
</body>
</html>
