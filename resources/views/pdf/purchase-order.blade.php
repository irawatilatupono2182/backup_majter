<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Purchase Order - {{ $purchaseOrder->po_number }}</title>
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
        .supplier-info {
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
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ $purchaseOrder->company->name }}</h2>
        <p>{{ $purchaseOrder->company->address }}</p>
        <p>Telp: {{ $purchaseOrder->company->phone }} | Email: {{ $purchaseOrder->company->email }}</p>
    </div>

    <div class="document-info">
        <h3>PURCHASE ORDER</h3>
        <table class="info-table">
            <tr>
                <td width="100">Nomor PO</td>
                <td>: {{ $purchaseOrder->po_number }}</td>
            </tr>
            <tr>
                <td>Tanggal PO</td>
                <td>: {{ $purchaseOrder->po_date ? $purchaseOrder->po_date->format('d/m/Y') : '-' }}</td>
            </tr>
            <tr>
                <td>Expected Date</td>
                <td>: {{ $purchaseOrder->expected_date ? $purchaseOrder->expected_date->format('d/m/Y') : '-' }}</td>
            </tr>
            <tr>
                <td>Status</td>
                <td>: {{ ucfirst($purchaseOrder->status) }}</td>
            </tr>
        </table>
    </div>

    <div class="supplier-info">
        <h4>Kepada Supplier:</h4>
        <table class="info-table">
            <tr>
                <td width="100">Supplier</td>
                <td>: {{ $purchaseOrder->supplier->name }}</td>
            </tr>
            <tr>
                <td>Alamat</td>
                <td>: {{ $purchaseOrder->supplier->address }}</td>
            </tr>
            <tr>
                <td>Telp</td>
                <td>: {{ $purchaseOrder->supplier->phone }}</td>
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
            @foreach($purchaseOrder->items as $index => $item)
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
                <td class="text-right">Rp {{ number_format($purchaseOrder->subtotal_amount, 0, ',', '.') }}</td>
            </tr>
            @if($purchaseOrder->ppn_included)
            <tr>
                <td>PPN (11%):</td>
                <td class="text-right">Rp {{ number_format($purchaseOrder->ppn_amount, 0, ',', '.') }}</td>
            </tr>
            @endif
            <tr class="total-row">
                <td>Total:</td>
                <td class="text-right">Rp {{ number_format($purchaseOrder->total_amount, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        @if($purchaseOrder->notes)
        <div style="margin-bottom: 20px;">
            <strong>Catatan:</strong><br>
            {{ $purchaseOrder->notes }}
        </div>
        @endif

        <div class="signature" style="float: right;">
            <p>Hormat kami,</p>
            <br><br><br>
            <p>_________________________</p>
            <p>{{ $purchaseOrder->company->name }}</p>
        </div>
    </div>
</body>
</html>