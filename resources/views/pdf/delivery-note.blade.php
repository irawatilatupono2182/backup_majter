<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Surat Jalan - {{ $deliveryNote->delivery_note_number }}</title>
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
        .footer {
            margin-top: 50px;
            clear: both;
        }
        .signature {
            width: 200px;
            text-align: center;
            margin-top: 50px;
            display: inline-block;
        }
        .info-table td {
            padding: 3px;
            border: none;
        }
        .signature-section {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ $deliveryNote->company->name }}</h2>
        <p>{{ $deliveryNote->company->address }}</p>
        <p>Telp: {{ $deliveryNote->company->phone }} | Email: {{ $deliveryNote->company->email }}</p>
    </div>

    <div class="document-info">
        <h3>SURAT JALAN</h3>
        <table class="info-table">
            <tr>
                <td style="width: 150px;">Nomor SJ</td>
                <td>: {{ $deliveryNote->delivery_note_number }}</td>
            </tr>
            <tr>
                <td>Tanggal</td>
                <td>: {{ $deliveryNote->delivery_date ? $deliveryNote->delivery_date->format('d/m/Y') : '-' }}</td>
            </tr>
            <tr>
                <td>Status</td>
                <td>: {{ ucfirst($deliveryNote->status) }}</td>
            </tr>
            @if($deliveryNote->purchaseOrder)
            <tr>
                <td>Purchase Order</td>
                <td>: {{ $deliveryNote->purchaseOrder->po_number }}</td>
            </tr>
            @endif
        </table>
    </div>

    <div class="customer-info">
        <h4>Dikirim Kepada:</h4>
        <table class="info-table">
            <tr>
                <td style="width: 150px;">Customer</td>
                <td>: {{ $deliveryNote->customer->name }}</td>
            </tr>
            <tr>
                <td>Alamat</td>
                <td>: {{ $deliveryNote->customer->address }}</td>
            </tr>
            <tr>
                <td>Telp</td>
                <td>: {{ $deliveryNote->customer->phone }}</td>
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
            @foreach($deliveryNote->items as $index => $item)
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

    <div style="margin-bottom: 20px;">
        <strong>Total Keseluruhan: Rp {{ number_format($deliveryNote->items->sum('subtotal'), 0, ',', '.') }}</strong>
    </div>

    @if($deliveryNote->notes)
    <div style="margin-bottom: 20px;">
        <strong>Catatan:</strong><br>
        {{ $deliveryNote->notes }}
    </div>
    @endif

    <div class="signature-section">
        <div class="signature">
            <p>Pengirim,</p>
            <br><br><br>
            <p>_________________________</p>
            <p>{{ $deliveryNote->company->name }}</p>
        </div>

        <div class="signature">
            <p>Penerima,</p>
            <br><br><br>
            <p>_________________________</p>
            <p>{{ $deliveryNote->customer->name }}</p>
        </div>
    </div>

    <div style="margin-top: 30px; text-align: center; font-size: 10px;">
        <p>Barang yang sudah diterima tidak dapat dikembalikan kecuali ada kesepakatan khusus.</p>
    </div>
</body>
</html>