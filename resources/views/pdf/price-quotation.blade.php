<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Penawaran Harga - {{ $priceQuotation->quotation_number }}</title>
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
        .company-info {
            text-align: left;
            margin-bottom: 20px;
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
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ $priceQuotation->company->name }}</h2>
        <p>{{ $priceQuotation->company->address }}</p>
        <p>Telp: {{ $priceQuotation->company->phone }} | Email: {{ $priceQuotation->company->email }}</p>
    </div>

    <div class="document-info">
        <h3>PENAWARAN HARGA</h3>
        <table>
            <tr>
                <td>Nomor</td>
                <td>: {{ $priceQuotation->quotation_number }}</td>
            </tr>
            <tr>
                <td>Tanggal</td>
                <td>: {{ $priceQuotation->quotation_date ? $priceQuotation->quotation_date->format('d/m/Y') : '-' }}</td>
            </tr>
            <tr>
                <td>Valid Until</td>
                <td>: {{ $priceQuotation->valid_until ? $priceQuotation->valid_until->format('d/m/Y') : '-' }}</td>
            </tr>
        </table>
    </div>

    <div class="customer-info">
        <h4>Kepada:</h4>
        <table>
            <tr>
                <td>Supplier</td>
                <td>: {{ $priceQuotation->supplier->name }}</td>
            </tr>
            <tr>
                <td>Alamat</td>
                <td>: {{ $priceQuotation->supplier->address }}</td>
            </tr>
            <tr>
                <td>Telp</td>
                <td>: {{ $priceQuotation->supplier->phone }}</td>
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
            @foreach($priceQuotation->items as $index => $item)
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
                <td class="text-right">Rp {{ number_format($priceQuotation->subtotal_amount, 0, ',', '.') }}</td>
            </tr>
            @if($priceQuotation->ppn_included)
            <tr>
                <td>PPN (11%):</td>
                <td class="text-right">Rp {{ number_format($priceQuotation->ppn_amount, 0, ',', '.') }}</td>
            </tr>
            @endif
            <tr class="total-row">
                <td>Total:</td>
                <td class="text-right">Rp {{ number_format($priceQuotation->total_amount, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        @if($priceQuotation->notes)
        <div style="margin-bottom: 20px;">
            <strong>Catatan:</strong><br>
            {{ $priceQuotation->notes }}
        </div>
        @endif

        <div class="signature" style="float: right;">
            <p>Hormat kami,</p>
            <br><br><br>
            <p>_________________________</p>
            <p>{{ $priceQuotation->company->name }}</p>
        </div>
    </div>
</body>
</html>