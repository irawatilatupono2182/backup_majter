// REFERENCE: Updated Invoice PDF Generation Function
// This matches the Filament templates exactly for both PPN and Non-PPN

function generateInvoicePDFContent(invoiceId) {
    const invoice = invoices.find(inv => inv.id === parseInt(invoiceId));
    if (!invoice) return '';

    const currentDate = new Date().toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }).toUpperCase();
    const invoiceDate = new Date(invoice.invoice_date).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }).toUpperCase();
    const dueDate = new Date(invoice.due_date).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }).toUpperCase();
    
    const isPPN = invoice.type === 'PPN';
    
    // Get customer info
    const customer = customers.find(c => c.id === invoice.customer_id);
    
    // Generate different HTML based on PPN/Non-PPN
    if (isPPN) {
        // PPN Template - Standard Layout (Logo Left, Company Middle, Title Right)
        return `
            <!DOCTYPE html>
            <html lang="id">
            <head>
                <meta charset="utf-8">
                <title>Invoice - ${invoice.invoice_no}</title>
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
                        padding: 0.6cm 1cm;
                    }

                    .container {
                        width: 100%;
                        max-width: 21cm;
                        margin: 0 auto;
                        padding: 0;
                    }

                    table {
                        border-collapse: collapse;
                    }

                    .header-table {
                        width: 100%;
                        border-bottom: 2px solid #000;
                        padding: 8px 10px 12px 10px;
                        margin-bottom: 10px;
                    }

                    .logo-box {
                        width: 84px;
                        height: 84px;
                        border: 0px solid #000;
                        display: inline-block;
                        text-align: center;
                        vertical-align: middle;
                        padding: 4px;
                    }

                    .company-info {
                        font-size: 13px;
                        line-height: 1.4;
                        font-weight: normal;
                    }

                    .company-info strong {
                        font-size: 28px;
                        font-weight: 900;
                    }

                    .invoice-title {
                        font-size: 39px;
                        font-weight: 900;
                        letter-spacing: 8px;
                        line-height: 1;
                        margin: 0;
                    }

                    .info-box {
                        font-size: 13px;
                        line-height: 1.5;
                    }

                    .items-table {
                        width: 100%;
                        margin: 8px 0;
                        font-size: 13px;
                        border: none;
                    }

                    .items-table th {
                        border-top: 2px solid #000;
                        border-bottom: 2px solid #000;
                        border-left: 1.5px solid #000;
                        border-right: 1.5px solid #000;
                        padding: 8px 6px;
                        text-align: center;
                        font-weight: bold;
                        font-size: 14px;
                        background-color: #fff;
                    }

                    .items-table td {
                        border-left: 1.5px solid #000;
                        border-right: 1.5px solid #000;
                        border-bottom: 1px solid #ddd;
                        padding: 8px 6px;
                        text-align: left;
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

                    .items-table .col-desc {
                        width: auto;
                        padding-left: 6px;
                    }

                    .items-table .col-qty {
                        width: 70px;
                        text-align: center;
                    }

                    .items-table .col-price {
                        width: 100px;
                        text-align: right;
                        padding-right: 6px;
                    }

                    .items-table .col-amount {
                        width: 100px;
                        text-align: right;
                        padding-right: 6px;
                    }

                    .items-table .col-notes {
                        width: 126px;
                        text-align: left;
                        padding-left: 8px;
                        font-size: 11px;
                    }

                    .total-section {
                        margin-top: 10px;
                        margin-bottom: 10px;
                    }

                    .total-table {
                        margin-left: auto;
                        width: 300px;
                        border: 2px solid #000;
                    }

                    .total-table td {
                        border: 1.5px solid #000;
                        padding: 8px 10px;
                        font-size: 13px;
                    }

                    .grand-total-row td {
                        border-top: 2px solid #000;
                        font-weight: 900;
                        font-size: 16px;
                        padding: 12px 10px;
                    }

                    .bank-box {
                        border: 1.5px solid #000;
                        padding: 8px;
                        width: 48%;
                        float: left;
                        margin-right: 4%;
                        font-size: 13px;
                    }

                    .signature-box {
                        width: 48%;
                        float: right;
                        text-align: center;
                        padding: 8px;
                        font-size: 13px;
                    }

                    .signature-space {
                        height: 50px;
                        margin: 10px 0;
                    }

                    .footer-section {
                        margin-top: 8px;
                        font-size: 13px;
                    }

                    .clearfix::after {
                        content: "";
                        display: table;
                        clear: both;
                    }
                </style>
            </head>
            <body>
                <div class="container">
                    
                    <!-- Header -->
                    <table class="header-table">
                        <tr>
                            <td style="width: 90px; vertical-align: middle;">
                                <div class="logo-box">
                                    <div style="width: 84px; height: 84px; background: #e5e7eb; display: flex; align-items: center; justify-content: center; font-size: 12px; color: #6b7280; border-radius: 4px;">AJ LOGO</div>
                                </div>
                            </td>
                            <td style="vertical-align: middle; padding: 0 12px;">
                                <div class="company-info">
                                    <strong>CV. ADAM JAYA</strong><br>
                                    Jl. Sadang, Rahayu, Kab. Bandung<br>
                                    Jawa Barat 40218<br>
                                    Telp: 085721322812 | Email: majter.ads@gmail.com
                                </div>
                            </td>
                            <td style="vertical-align: middle; text-align: right;">
                                <div class="invoice-title">INVOICE</div>
                            </td>
                        </tr>
                    </table>

                    <!-- Info Section -->
                    <table style="width: 100%; margin-bottom: 8px;">
                        <tr>
                            <td style="width: 50%; vertical-align: top; padding-right: 10px;">
                                <div style="margin-bottom: 8px;">
                                    <strong>BANDUNG, ${invoiceDate}</strong>
                                </div>
                                <table style="width: 100%; font-size: 13px;">
                                    <tr><td style="width: 80px;">Invoice No</td><td>: ${invoice.invoice_no}</td></tr>
                                    ${invoice.reference ? `<tr><td>Reference</td><td>: ${invoice.reference}</td></tr>` : ''}
                                    <tr><td>Due Date</td><td>: ${dueDate}</td></tr>
                                </table>
                            </td>
                            <td style="width: 50%; vertical-align: top; padding-left: 10px;">
                                <table style="width: 100%; font-size: 13px;">
                                    <tr><td style="width: 80px;">To</td><td>: <strong>${invoice.customer.toUpperCase()} (PPN)</strong></td></tr>
                                    <tr><td>Up</td><td>: ${invoice.contact_person}</td></tr>
                                </table>
                                ${customer && customer.address_bill_to ? `
                                <div style="margin-top: 5px; font-size: 13px;">
                                    <strong>BILL TO:</strong> ${customer.address_bill_to}
                                </div>
                                ` : ''}
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
                                <th class="col-price">UNIT PRICE</th>
                                <th class="col-amount">AMOUNT</th>
                                <th class="col-notes">NOTES</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${invoice.items.map((item, index) => `
                                <tr>
                                    <td class="col-no">${index + 1}.</td>
                                    <td class="col-desc">${item.product.toUpperCase()}</td>
                                    <td class="col-qty">${item.quantity.toLocaleString('id-ID')} PCS</td>
                                    <td class="col-price">Rp. ${item.price.toLocaleString('id-ID')}</td>
                                    <td class="col-amount">Rp. ${item.total.toLocaleString('id-ID')}</td>
                                    <td class="col-notes"></td>
                                </tr>
                            `).join('')}
                            ${invoice.items.length === 0 ? `
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 15px; font-style: italic;">
                                        Tidak ada item
                                    </td>
                                </tr>
                            ` : ''}
                        </tbody>
                    </table>

                    <!-- Totals Section -->
                    <div class="total-section">
                        <table class="total-table">
                            <tr>
                                <td style="font-weight: bold;">SUB TOTAL</td>
                                <td style="text-align: right;">Rp. ${invoice.subtotal.toLocaleString('id-ID')}</td>
                            </tr>
                            <tr>
                                <td style="font-weight: bold;">PPN 11%</td>
                                <td style="text-align: right;">Rp. ${invoice.ppn.toLocaleString('id-ID')}</td>
                            </tr>
                            <tr class="grand-total-row">
                                <td>GRAND TOTAL</td>
                                <td style="text-align: right;">Rp. ${invoice.total.toLocaleString('id-ID')}</td>
                            </tr>
                        </table>
                    </div>

                    <!-- Footer Section -->
                    <div class="footer-section clearfix">
                        <div class="bank-box">
                            <strong>BANK PAYMENT :</strong><br>
                            BANK BCA<br>
                            ACC NO : 123-456-7890<br>
                            A/N : CV. ADAM JAYA
                        </div>
                        <div class="signature-box">
                            <div>HORMAT KAMI</div>
                            <div class="signature-space"></div>
                            <div style="border-top: 1px solid #000; display: inline-block; padding-top: 3px; min-width: 150px;">
                                <strong>CV. ADAM JAYA</strong>
                            </div>
                        </div>
                    </div>

                    ${invoice.notes ? `
                    <div style="clear: both; margin-top: 20px; padding: 8px; border: 1px solid #000; font-size: 12px;">
                        <strong>Catatan:</strong> ${invoice.notes}
                    </div>
                    ` : ''}

                </div>
            </body>
            </html>
        `;
    } else {
        // Non-PPN Template - Alternative Layout (Title Left, Logo+Branding Right)
        return `
            <!DOCTYPE html>
            <html lang="id">
            <head>
                <meta charset="utf-8">
                <title>Invoice - ${invoice.invoice_no}</title>
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
                        padding: 0.6cm 1cm;
                    }

                    .container {
                        width: 100%;
                        max-width: 21cm;
                        margin: 0 auto;
                        padding: 0;
                    }

                    table {
                        border-collapse: collapse;
                    }

                    .header-table {
                        width: 100%;
                        border-bottom: 2px solid #000;
                        padding: 8px 0px 10px 0px;
                        margin-bottom: 10px;
                    }

                    .logo-box {
                        width: 112px;
                        height: 49px;
                        border: 0px solid #000;
                        display: inline-block;
                        text-align: center;
                        vertical-align: middle;
                        margin-right: 8px;
                        padding: 3px;
                    }

                    .invoice-title {
                        text-align: left;
                        font-size: 28px;
                        font-weight: 900;
                        letter-spacing: 7px;
                        line-height: 1;
                        margin: 0;
                    }

                    .company-logo-section {
                        text-align: left;
                        font-size: 14px;
                        line-height: 1.3;
                        display: inline-block;
                        vertical-align: middle;
                    }

                    .company-brand {
                        font-size: 20px;
                        font-weight: 900;
                        letter-spacing: 3px;
                    }

                    .company-tagline {
                        font-size: 11px;
                        margin-top: 2px;
                    }

                    .info-box {
                        font-size: 13px;
                        line-height: 1.5;
                    }

                    .items-table {
                        width: 100%;
                        margin: 8px 0;
                        font-size: 13px;
                        border: 2px solid #000;
                    }

                    .items-table th {
                        border-top: none;
                        border-bottom: 2px solid #000;
                        border-left: 1.5px solid #000;
                        border-right: 1.5px solid #000;
                        padding: 8px 6px;
                        text-align: center;
                        font-weight: bold;
                        font-size: 14px;
                        background-color: #fff;
                    }

                    .items-table td {
                        border-left: 1.5px solid #000;
                        border-right: 1.5px solid #000;
                        border-bottom: 1px solid #ddd;
                        padding: 8px 6px;
                        text-align: left;
                        vertical-align: top;
                        font-size: 13px;
                    }

                    .items-table tbody tr:last-child td {
                        border-bottom: none;
                    }

                    .items-table .col-no {
                        width: 35px;
                        text-align: center;
                    }

                    .items-table .col-desc {
                        width: auto;
                        padding-left: 6px;
                    }

                    .items-table .col-qty {
                        width: 70px;
                        text-align: center;
                    }

                    .items-table .col-price {
                        width: 100px;
                        text-align: right;
                        padding-right: 6px;
                    }

                    .items-table .col-amount {
                        width: 100px;
                        text-align: right;
                        padding-right: 6px;
                    }

                    .items-table .col-notes {
                        width: 126px;
                        text-align: left;
                        padding-left: 8px;
                        font-size: 11px;
                    }

                    .total-section {
                        margin-top: 10px;
                        margin-bottom: 10px;
                    }

                    .total-table {
                        margin-left: auto;
                        width: 300px;
                        border: 2px solid #000;
                    }

                    .total-table td {
                        border: 1.5px solid #000;
                        padding: 8px 10px;
                        font-size: 13px;
                    }

                    .grand-total-row td {
                        border-top: 2px solid #000;
                        font-weight: 900;
                        font-size: 16px;
                        padding: 12px 10px;
                    }

                    .bank-box {
                        border: 1.5px solid #000;
                        padding: 8px;
                        width: 48%;
                        float: left;
                        margin-right: 4%;
                        font-size: 13px;
                    }

                    .signature-box {
                        width: 48%;
                        float: right;
                        text-align: center;
                        padding: 8px;
                        font-size: 13px;
                    }

                    .signature-space {
                        height: 50px;
                        margin: 10px 0;
                    }

                    .footer-section {
                        margin-top: 8px;
                        font-size: 13px;
                    }

                    .clearfix::after {
                        content: "";
                        display: table;
                        clear: both;
                    }
                </style>
            </head>
            <body>
                <div class="container">
                    
                    <!-- Header - Alternative Style -->
                    <table class="header-table">
                        <tr>
                            <td style="width: 50%; vertical-align: middle; padding-left: 0;">
                                <div class="invoice-title">INVOICE</div>
                            </td>
                            <td style="width: 50%; vertical-align: middle; text-align: right; padding-right: 0;">
                                <div style="display: inline-block; text-align: right;">
                                    <div class="logo-box" style="display: inline-block; vertical-align: middle; margin-right: 8px;">
                                        <div style="width: 112px; height: 49px; background: #e5e7eb; display: flex; align-items: center; justify-content: center; font-size: 12px; color: #6b7280; border-radius: 4px;">MAJTER LOGO</div>
                                    </div>
                                    <div style="display: inline-block; vertical-align: middle; height: 49px; width: 1px; background-color: #000; margin: 0 8px;"></div>
                                    <div class="company-logo-section" style="display: inline-block; vertical-align: middle; margin-left: 8px;">
                                        <div class="company-brand">AJT</div>
                                        <div class="company-tagline">BANDUNG - JAWA BARAT 40218</div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </table>

                    <!-- Info Section -->
                    <table style="width: 100%; margin-bottom: 8px;">
                        <tr>
                            <td style="width: 50%; vertical-align: top; padding-right: 10px;">
                                <div style="margin-bottom: 8px;">
                                    <strong>BANDUNG, ${invoiceDate}</strong>
                                </div>
                                <table style="width: 100%; font-size: 13px;">
                                    <tr><td style="width: 80px;">Invoice No</td><td>: ${invoice.invoice_no}</td></tr>
                                    ${invoice.reference ? `<tr><td>Reference</td><td>: ${invoice.reference}</td></tr>` : ''}
                                    <tr><td>Due Date</td><td>: ${dueDate}</td></tr>
                                </table>
                            </td>
                            <td style="width: 50%; vertical-align: top; padding-left: 10px;">
                                <table style="width: 100%; font-size: 13px;">
                                    <tr><td style="width: 80px;">To</td><td>: <strong>${invoice.customer.toUpperCase()}</strong></td></tr>
                                    <tr><td>Up</td><td>: ${invoice.contact_person}</td></tr>
                                </table>
                                ${customer && customer.address_bill_to ? `
                                <div style="margin-top: 5px; font-size: 13px;">
                                    <strong>BILL TO:</strong> ${customer.address_bill_to}
                                </div>
                                ` : ''}
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
                                <th class="col-price">UNIT PRICE</th>
                                <th class="col-amount">AMOUNT</th>
                                <th class="col-notes">NOTES</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${invoice.items.map((item, index) => `
                                <tr>
                                    <td class="col-no">${index + 1}.</td>
                                    <td class="col-desc">${item.product.toUpperCase()}</td>
                                    <td class="col-qty">${item.quantity.toLocaleString('id-ID')} PCS</td>
                                    <td class="col-price">Rp. ${item.price.toLocaleString('id-ID')}</td>
                                    <td class="col-amount">Rp. ${item.total.toLocaleString('id-ID')}</td>
                                    <td class="col-notes"></td>
                                </tr>
                            `).join('')}
                            ${invoice.items.length === 0 ? `
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 15px; font-style: italic;">
                                        Tidak ada item
                                    </td>
                                </tr>
                            ` : ''}
                        </tbody>
                    </table>

                    <!-- Totals Section (No PPN for Non-PPN) -->
                    <div class="total-section">
                        <table class="total-table">
                            <tr class="grand-total-row">
                                <td>GRAND TOTAL</td>
                                <td style="text-align: right;">Rp. ${invoice.total.toLocaleString('id-ID')}</td>
                            </tr>
                        </table>
                    </div>

                    <!-- Footer Section -->
                    <div class="footer-section clearfix">
                        <div class="bank-box">
                            <strong>BANK PAYMENT :</strong><br>
                            BANK BCA<br>
                            ACC NO : 123-456-7890<br>
                            A/N : CV. ADAM JAYA
                        </div>
                        <div class="signature-box">
                            <div>HORMAT KAMI</div>
                            <div class="signature-space"></div>
                            <div style="border-top: 1px solid #000; display: inline-block; padding-top: 3px; min-width: 150px;">
                                <strong>CV. ADAM JAYA</strong>
                            </div>
                        </div>
                    </div>

                    ${invoice.notes ? `
                    <div style="clear: both; margin-top: 20px; padding: 8px; border: 1px solid #000; font-size: 12px;">
                        <strong>Catatan:</strong> ${invoice.notes}
                    </div>
                    ` : ''}

                </div>
            </body>
            </html>
        `;
    }
}
