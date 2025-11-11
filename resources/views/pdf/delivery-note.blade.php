<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Surat Jalan - {{ $deliveryNote->delivery_note_number ?? 'N/A' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
        }

        body {
            background: white;
            padding: 5mm;
            margin: 0;
        }

        .container {
            width: 100%;
            max-width: 19cm;
            height: auto;
            padding: 0.8cm 0.6cm; /* Padding lebih kecil agar fit portrait */
            background: white;
            margin: 0 auto;
        }

        /* Hapus semua class flex */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .header-table td {
            vertical-align: top;
            padding: 3px 2px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 10px;
        }
        .info-table td {
            vertical-align: top;
            padding: 2px 0;
        }
        .info-table div {
            margin-bottom: 3px;
            line-height: 1.3;
        }
        .signatures-table {
            width: 100%;
            margin: 15px 0 8px 0;
            font-size: 9px;
            border-collapse: collapse;
        }
        .signatures-table td {
            padding: 5px 3px;
            height: 40px;
        }
        .label {
            font-weight: normal;
            min-width: 70px;
            display: inline-block;
            vertical-align: top;
        }

        /* Tetap pertahankan style tabel barang */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
        }
        .items-table th,
        .items-table td {
            padding: 4px 3px;
            text-align: left;
            vertical-align: middle;
            font-size: 10px;
        }
        .items-table th {
            font-weight: bold;
            text-align: center;
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            padding: 5px 3px;
            height: 18px;
        }
        .col-no { width: 40px; text-align: center; }
        .col-item { width: auto; padding-left: 8px; }
        .col-qty { width: 120px; text-align: center; }
        .col-notes { width: 150px; text-align: center; }

        .signature-line {
            margin: 20px auto 3px auto;
            height: 1px;
            border-bottom: 1px solid #000;
            width: 60%;
        }

        .notes {
            margin-top: 12px;
            font-size: 10px;
            line-height: 1.3;
        }
        .notes-title {
            font-weight: bold;
            display: inline;
        }
        .notes-content {
            display: inline;
            text-transform: uppercase;
            margin-left: 5px;
        }
    </style>
</head>
<body>
    <div class="container">

        <!-- Header -->
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 5px;">
            <tr>
                <td style="width: 80px; vertical-align: top; padding-right: 10px;">
                    <div style="width: 70px; height: 70px; border: 2px solid #000; display: flex; align-items: center; justify-content: center;">
                        <div style="text-align: center; font-size: 24px; font-weight: bold;">AJ</div>
                    </div>
                </td>
                <td style="vertical-align: top; width: 60%;">
                    <div style="font-weight: bold; font-size: 14px; margin-bottom: 1px;">CV. ADAM JAYA</div>
                    <div style="font-size: 8px; line-height: 1.4;">
                        Jalan Holis Indah Blok G4 No.5 RT/RW: 005/008<br>
                        Cigondewah Rahayu, Bandung Kulon<br>
                        Bandung - 40215<br>
                        Telp: 62 - 22 - 6079185
                    </div>
                </td>
 
            </tr>
        </table>
        
        <!-- Surat Jalan Title -->
        <div style="text-align: center; font-size: 20px; font-weight: bold; margin: 15px 0 10px 0;">Surat Jalan</div>

        <!-- Info Section -->
        <div style="font-size: 9px; margin: 8px 0; line-height: 1.6;">
            <table style="width: 100%; border-collapse: collapse; font-size: 9px; margin-bottom: 8px;">
            <tr>
                <td style="width: 50%; vertical-align: top;">
                <div style="margin-bottom: 3px;">
                    <strong>No.</strong> <span style="margin-left: 10px;">{{ $deliveryNote->sj_number ?? '2350' }}</span>
                </div>
                </td>
                <td style="width: 50%; vertical-align: top; text-align: right;">
                <div style="text-align: right;">
                    Bandung, {{ isset($deliveryNote->delivery_date) ? $deliveryNote->delivery_date->translatedFormat('d F Y') : now()->translatedFormat('d F Y') }}
                </div>
                </td>
            </tr>
            </table>
            
            <div style="margin-bottom: 8px;">
            Mohon diterima dan diperiksa, barang-barang kiriman kami
            </div>
            
            <table style="width: 100%; border-collapse: collapse; font-size: 9px;">
            <tr>
                <td style="width: 50%; vertical-align: top;">
                <div style="margin-bottom: 3px;">
                    Dikirim dengan kendaraan : {{ $deliveryNote->vehicle_type ?? '_______________' }}
                </div>
                <div style="margin-left: 100px;">
                    No. Pol. : {{ $deliveryNote->vehicle_number ?? '_______________' }}
                </div>
                </td>
                <td style="width: 50%; vertical-align: top; text-align: right;">
                <div style="text-align: right;">
                    Kepada Yth. <span style="display: inline-block; width: 150px; border-bottom: 1px dotted #000; text-align: center;">{{ isset($deliveryNote->customer) ? strtoupper($deliveryNote->customer->name) : 'N/A' }}</span><br>
                    <span style="display: inline-block; width: 150px; border-bottom: 1px dotted #000; text-align: center; margin-top: 2px;">{{ $deliveryNote->customer->city ?? 'BANDUNG' }}</span>
                </div>
                </td>
            </tr>
            </table>
        </div>


        <!-- Items Table -->
        <table style="width: 100%; border-collapse: collapse; border: 1px solid #000; margin-top: 5px;">
            <thead>
                <tr style="border-bottom: 1px solid #000;">
                    <th style="width: 50px; text-align: center; padding: 6px 4px; font-size: 10px; font-weight: normal; border-right: 1px solid #000;">No.</th>
                    <th style="text-align: center; padding: 6px 4px; font-size: 10px; font-weight: normal; border-right: 1px solid #000;">N a m a &nbsp;&nbsp; B a r a n g</th>
                    <th style="width: 120px; text-align: center; padding: 6px 4px; font-size: 10px; font-weight: normal; border-right: 1px solid #000;">Banyaknya</th>
                    <th style="width: 150px; text-align: center; padding: 6px 4px; font-size: 10px; font-weight: normal;">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($deliveryNote->items as $index => $item)
                    <tr style="border-bottom: 1px solid #000;">
                        <td style="text-align: center; padding: 8px 4px; font-size: 9px; border-right: 1px solid #000; vertical-align: top;">{{ $index + 1 }}</td>
                        <td style="text-align: left; padding: 8px 8px; font-size: 9px; border-right: 1px solid #000; vertical-align: top;">
                            {{ strtoupper($item->product->name ?? 'N/A') }}
                            @if(!empty($item->product->description))
                                {{ strtoupper($item->product->description) }}
                            @endif
                        </td>
                        <td style="text-align: center; padding: 8px 4px; font-size: 9px; border-right: 1px solid #000; vertical-align: top;">
                            {{ number_format($item->quantity, 0, ',', '.') }}
                            {{ strtoupper($item->unit ?? 'PCS') }}
                        </td>
                        <td style="text-align: center; padding: 8px 4px; font-size: 9px; vertical-align: top;">{{ $item->notes ?? '' }}</td>
                    </tr>
                @empty
                    <tr style="border-bottom: 1px solid #000;">
                        <td style="text-align: center; padding: 8px 4px; font-size: 9px; border-right: 1px solid #000;">1</td>
                        <td style="text-align: left; padding: 8px 8px; font-size: 9px; border-right: 1px solid #000;">—</td>
                        <td style="text-align: center; padding: 8px 4px; font-size: 9px; border-right: 1px solid #000;">—</td>
                        <td style="text-align: center; padding: 8px 4px; font-size: 9px;">—</td>
                    </tr>
                @endforelse

                @php
                    $rowCount = $deliveryNote->items->count() ?? 0;
                    $emptyRows = max(0, 3 - $rowCount);
                @endphp

                @for($i = 0; $i < $emptyRows; $i++)
                    <tr style="border-bottom: 1px solid #000;">
                        <td style="padding: 25px 4px; border-right: 1px solid #000;">&nbsp;</td>
                        <td style="padding: 25px 4px; border-right: 1px solid #000;">&nbsp;</td>
                        <td style="padding: 25px 4px; border-right: 1px solid #000;">&nbsp;</td>
                        <td style="padding: 25px 4px;">&nbsp;</td>
                    </tr>
                @endfor
            </tbody>
        </table>

        <!-- Signatures -->
        <table style="width: 100%; margin-top: 30px; font-size: 9px; border-collapse: collapse;">
            <tr>
                <td style="width: 50%; text-align: center; vertical-align: top; padding: 5px;">
                    <div style="margin-bottom: 60px;">Penerimaan Barang</div>
                    <div style="text-align: center;">
                        ( <span style="display: inline-block; width: 200px; border-bottom: 1px dotted #000;"></span> )
                    </div>
                </td>
                <td style="width: 50%; text-align: center; vertical-align: top; padding: 5px;">
                    <div style="margin-bottom: 60px;">Hormat kami</div>
                    <div style="text-align: center;">
                        ( <span style="display: inline-block; width: 200px; border-bottom: 1px dotted #000;"></span> )
                    </div>
                </td>
            </tr>
        </table>

    </div>
</body>
</html>