        // PDF Generation Functions for Surat Jalan
        function generateSuratJalanPDFContent(deliveryNoteId) {
            const deliveryNote = deliveryNotes.find(dn => dn.id === parseInt(deliveryNoteId));
            if (!deliveryNote) {
                console.error('Delivery note not found:', deliveryNoteId);
                return '';
            }

            const currentDate = new Date().toLocaleDateString('id-ID');
            const deliveryDate = new Date(deliveryNote.delivery_date).toLocaleDateString('id-ID', { 
                day: 'numeric', 
                month: 'long', 
                year: 'numeric' 
            }).toUpperCase();
            
            // Get customer info
            const customer = customers.find(c => c.id === deliveryNote.customer_id);
            const isPPN = deliveryNote.type === 'PPN';
            
            return `
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Surat Jalan - ${deliveryNote.delivery_no}</title>
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
            padding: 1cm 1.2cm;
        }

        .container {
            width: 100%;
            max-width: 19cm;
            margin: 0 auto;
            padding: 0;
        }

        table {
            border-collapse: collapse;
        }

        /* Header Section */
        .header {
            border-bottom: 1.5px solid #000;
            padding: 6px 10px;
        }

        .header-table {
            width: 100%;
        }

        .logo-box {
            width: 60px;
            height: 60px;
            border: 0px solid #000;
            display: inline-block;
            text-align: center;
            vertical-align: middle;
            padding: 3px;
        }

        .company-info {
            font-size: 13px;
            line-height: 1.4;
            font-weight: normal;
        }

        .company-info strong {
            font-size: 22px;
            font-weight: 900;
        }

        /* Content Section */
        .content {
            padding: 10px 12px;
        }

        .title {
            text-align: right;
            font-size: 28px;
            font-weight: 900;
            margin: 0;
            letter-spacing: 5px;
        }

        .doc-info {
            font-size: 13px;
            margin-bottom: 8px;
            line-height: 1.5;
        }

        .doc-info table {
            width: 100%;
        }

        .doc-info td {
            padding: 1px 0;
            vertical-align: top;
        }

        /* Items Table */
        .items-table {
            width: 100%;
            margin: 8px 0;
            font-size: 13px;
            border: none;
        }

        .items-table th {
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
            border-left: none;
            border-right: none;
            padding: 8px 6px;
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            background-color: #fff;
        }

        .items-table td {
            border: none;
            border-bottom: 1px solid #ddd;
            padding: 8px 6px;
            vertical-align: top;
            font-size: 13px;
        }

        .items-table tbody tr:last-child td {
            border-bottom: 2px solid #000;
        }

        .items-table .col-no {
            width: 35px;
            text-align: center;
        }

        .items-table .col-item {
            width: auto;
            text-align: left;
            padding-left: 5px;
        }

        .items-table .col-qty {
            width: 100px;
            text-align: center;
        }

        .items-table .col-notes {
            width: 150px;
            text-align: center;
        }

        /* Signature Section */
        .signature-section {
            margin-top: 20px;
            margin-bottom: 10px;
            font-size: 13px;
        }

        .signatures-table {
            width: 100%;
        }

        .signatures-table td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            padding: 5px;
        }

        .signature-space {
            min-height: 60px;
            margin: 10px 0 5px 0;
        }

        /* Notes */
        .notes {
            font-size: 13px;
            margin-top: 5px;
            line-height: 1.4;
        }

        .notes strong {
            font-weight: bold;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        
        <!-- Header -->
        <div class="header">
            <table class="header-table">
                <tr>
                    <td style="width: 70px; vertical-align: middle;">
                        <div class="logo-box">
                            <div style="width: 60px; height: 60px; background: #e5e7eb; display: flex; align-items: center; justify-content: center; font-size: 10px; color: #6b7280; border-radius: 4px;">AJ LOGO</div>
                        </div>
                    </td>
                    <td style="width: 48%; vertical-align: middle; padding-left: 8px;">
                        <div class="company-info">
                            <strong>CV. ADAM JAYA</strong><br>
                            Jl. Sadang, Rahayu, Kab. Bandung<br>
                            Jawa Barat 40218<br>
                            Telp: 085721322812 | Email: majter.ads@gmail.com
                        </div>
                    </td>
                    <td style="vertical-align: middle; text-align: right;">
                        <div class="title">SURAT JALAN</div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Content -->
        <div class="content">
            <!-- Document Info -->
            <div class="doc-info">
                <table>
                    <tr>
                        <td style="width: 55%; vertical-align: top; padding-right: 10px;">
                            <!-- Left Column -->
                            <div style="margin-bottom: 5px;">
                                <strong>BANDUNG, ${deliveryDate}</strong>
                            </div>
                            <table style="width: 100%; margin-bottom: 5px;">
                                <tr>
                                    <td style="width: 60px;">NOMOR</td>
                                    <td>: ${deliveryNote.delivery_no}</td>
                                </tr>
                            </table>
                        </td>
                        <td style="width: 45%; vertical-align: top; padding-left: 10px;">
                            <!-- Right Column -->
                            <table style="width: 100%; font-size: 13px;">
                                <tr>
                                    <td style="width: 90px; padding: 2px 0;">TO</td>
                                    <td style="padding: 2px 0;">: <strong>${deliveryNote.customer.toUpperCase()}</strong></td>
                                </tr>
                            </table>
                            <div style="margin-top: 5px; font-size: 13px;">
                                <strong>SHIP TO :</strong> ${deliveryNote.shipping_address || customer?.address_ship_to || ''}
                            </div>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Items Table -->
            <table class="items-table">
                <thead>
                    <tr>
                        <th class="col-no">NO</th>
                        <th class="col-item">NAMA BARANG</th>
                        <th class="col-qty">BANYAKNYA</th>
                        <th class="col-notes">KETERANGAN</th>
                    </tr>
                </thead>
                <tbody>
                    ${deliveryNote.items.map((item, index) => `
                        <tr>
                            <td class="col-no">${index + 1}.</td>
                            <td class="col-item">${item.product.toUpperCase()}</td>
                            <td class="col-qty">${item.quantity.toLocaleString('id-ID')} PCS</td>
                            <td class="col-notes">${item.notes || ''}</td>
                        </tr>
                    `).join('')}
                    ${deliveryNote.items.length === 0 ? `
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 15px; font-style: italic;">
                                Tidak ada item
                            </td>
                        </tr>
                    ` : ''}
                </tbody>
            </table>

            <!-- Signature Section -->
            <div class="signature-section">
                <table class="signatures-table">
                    <tr>
                        <td style="text-align: center;">
                            <div>Yang Menyerahkan</div>
                            <div class="signature-space"></div>
                        </td>
                        <td style="text-align: center;">
                            <div>Yang Menerima</div>
                            <div class="signature-space"></div>
                        </td>
                    </tr>
                </table>
            </div>

            ${deliveryNote.notes ? `
            <!-- Notes -->
            <div class="notes">
                <strong>Catatan:</strong> ${deliveryNote.notes}
            </div>
            ` : ''}
        </div>

    </div>
</body>
</html>
            `;
        }