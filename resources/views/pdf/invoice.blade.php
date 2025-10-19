<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Invoice - {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .document-info {
            margin-bottom: 20px;
        }
        .customer-info {
            margin-bottom: 20px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table th,
        .items-table td {
            border: 1px solid #333;
            padding: 8px;
            text-align: left;
        }
        .items-table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .total-section {
            float: right;
            width: 300px;
            margin-top: 10px;
        }
        .total-table {
            width: 100%;
            border-collapse: collapse;
        }
        .total-table td {
            padding: 5px;
            border-bottom: 1px solid #ddd;
        }
        .total-table .total-row {
            font-weight: bold;
            border-top: 2px solid #333;
        }
        .footer {
            margin-top: 50px;
            clear: both;
        }
        .signature {
            width: 200px;
            text-align: center;
            margin-top: 50px;
        }
        .info-table td {
            padding: 3px;
            border: none;
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 3px;
            color: white;
            font-weight: bold;
        }
        .status-paid {
            background-color: #28a745;
        }
        .status-unpaid {
            background-color: #dc3545;
        }
        .status-partial {
            background-color: #ffc107;
            color: #000;
        }
        .status-overdue {
            background-color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ $invoice->company->name }}</h2>
        <p>{{ $invoice->company->address }}</p>
        <p>Telp: {{ $invoice->company->phone }} | Email: {{ $invoice->company->email }}</p>
    </div>

    <div class="document-info">
        <h3>INVOICE</h3>
        <table class="info-table">
            <tr>
                <td style="width: 150px;">Nomor Invoice</td>
                <td>: {{ $invoice->invoice_number }}</td>
            </tr>
            <tr>
                <td>Tanggal Invoice</td>
                <td>: {{ $invoice->invoice_date ? $invoice->invoice_date->format('d/m/Y') : '-' }}</td>
            </tr>
            <tr>
                <td>Jatuh Tempo</td>
                <td>: {{ $invoice->due_date ? $invoice->due_date->format('d/m/Y') : '-' }}</td>
            </tr>
            <tr>
                <td>Status</td>
                <td>: 
                    <span class="status-badge status-{{ $invoice->status }}">
                        @if($invoice->status == 'paid') Lunas
                        @elseif($invoice->status == 'partial') Sebagian
                        @elseif($invoice->status == 'overdue') Jatuh Tempo
                        @else Belum Lunas
                        @endif
                    </span>
                </td>
            </tr>
            @if($invoice->deliveryNote)
            <tr>
                <td>Surat Jalan</td>
                <td>: {{ $invoice->deliveryNote->delivery_note_number }}</td>
            </tr>
            @endif
        </table>
    </div>

    <div class="customer-info">
        <h4>Tagihan Kepada:</h4>
        <table class="info-table">
            <tr>
                <td style="width: 150px;">Customer</td>
                <td>: {{ $invoice->customer->name }}</td>
            </tr>
            <tr>
                <td>Alamat</td>
                <td>: {{ $invoice->customer->address }}</td>
            </tr>
            <tr>
                <td>Telp</td>
                <td>: {{ $invoice->customer->phone }}</td>
            </tr>
        </table>
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th class="text-center">No</th>
                <th>Nama Produk</th>
                <th class="text-center">Qty</th>
                <th class="text-right">Harga Satuan</th>
                <th class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $item->product->name }}</td>
                <td class="text-center">{{ number_format($item->quantity) }}</td>
                <td class="text-right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total-section">
        <table class="total-table">
            <tr>
                <td>Subtotal:</td>
                <td class="text-right">Rp {{ number_format($invoice->subtotal_amount, 0, ',', '.') }}</td>
            </tr>
            @if($invoice->ppn_included)
            <tr>
                <td>PPN (11%):</td>
                <td class="text-right">Rp {{ number_format($invoice->ppn_amount, 0, ',', '.') }}</td>
            </tr>
            @endif
            <tr class="total-row">
                <td>Total:</td>
                <td class="text-right">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</td>
            </tr>
            @if($invoice->status != 'paid')
            <tr>
                <td>Sudah Dibayar:</td>
                <td class="text-right">Rp {{ number_format($invoice->getTotalPaid(), 0, ',', '.') }}</td>
            </tr>
            <tr style="font-weight: bold; color: #dc3545;">
                <td>Sisa Tagihan:</td>
                <td class="text-right">Rp {{ number_format($invoice->getRemainingAmount(), 0, ',', '.') }}</td>
            </tr>
            @endif
        </table>
    </div>

    <div class="footer">
        @if($invoice->notes)
        <div style="margin-bottom: 20px;">
            <strong>Catatan:</strong><br>
            {{ $invoice->notes }}
        </div>
        @endif

        <div style="margin-bottom: 20px;">
            <strong>Pembayaran:</strong><br>
            Silakan lakukan pembayaran sebelum tanggal jatuh tempo.<br>
            Pembayaran dapat dilakukan melalui transfer bank atau tunai.
        </div>

        <div class="signature" style="float: right;">
            <p>Hormat kami,</p>
            <br><br><br>
            <p>_________________________</p>
            <p>{{ $invoice->company->name }}</p>
        </div>
    </div>
</body>
</html>