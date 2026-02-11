<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Purchase Order - <?php echo e($purchaseOrder->po_number); ?></title>
    <style>
        @page {
            margin: 15mm 15mm 20mm 15mm;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            margin: 0;
            padding: 0;
            line-height: 1.4;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        
        .header h2 {
            margin: 0 0 5px 0;
            font-size: 16px;
        }
        
        .header p {
            margin: 2px 0;
            font-size: 10px;
        }
        
        .document-info {
            margin-bottom: 15px;
        }
        
        .document-info h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
            border-bottom: 1px solid #333;
            padding-bottom: 5px;
        }
        
        .supplier-info {
            margin-bottom: 15px;
        }
        
        .supplier-info h4 {
            margin: 0 0 8px 0;
            font-size: 12px;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        
        .items-table th,
        .items-table td {
            border: 1px solid #333;
            padding: 6px 8px;
            text-align: left;
        }
        
        .items-table th {
            background-color: #f5f5f5;
            font-weight: bold;
            font-size: 10px;
        }
        
        .items-table td {
            font-size: 10px;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .total-section {
            width: 100%;
            margin-bottom: 20px;
            page-break-inside: avoid;
        }
        
        .total-wrapper {
            width: 350px;
            margin-left: auto;
        }
        
        .total-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .total-table td {
            padding: 6px 8px;
            border-bottom: 1px solid #ddd;
            font-size: 11px;
        }
        
        .total-table .total-row {
            font-weight: bold;
            border-top: 2px solid #333;
            font-size: 12px;
        }
        
        .notes-section {
            margin-bottom: 20px;
            page-break-inside: avoid;
            min-height: 40px;
        }
        
        .notes-section strong {
            display: block;
            margin-bottom: 5px;
        }
        
        .signature-section {
            width: 100%;
            margin-top: 30px;
            page-break-inside: avoid;
        }
        
        .signature-wrapper {
            width: 200px;
            text-align: center;
            margin-left: auto;
        }
        
        .signature-wrapper p {
            margin: 5px 0;
        }
        
        .signature-space {
            height: 60px;
        }
        
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 5px;
            padding-top: 5px;
        }
        
        .info-table {
            width: 100%;
        }
        
        .info-table td {
            padding: 3px 0;
            border: none;
            font-size: 10px;
        }
        
        /* Prevent page breaks inside important sections */
        .document-info,
        .supplier-info,
        .total-section,
        .notes-section,
        .signature-section {
            page-break-inside: avoid;
        }
        
        /* Keep at least 2 rows together in table */
        .items-table tr {
            page-break-inside: avoid;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2><?php echo e($purchaseOrder->company->name); ?></h2>
        <p><?php echo e($purchaseOrder->company->address); ?></p>
        <p>Telp: <?php echo e($purchaseOrder->company->phone); ?> | Email: <?php echo e($purchaseOrder->company->email); ?></p>
    </div>

    <div class="document-info">
        <h3>PURCHASE ORDER</h3>
        <table class="info-table">
            <tr>
                <td width="120">Nomor PO</td>
                <td>: <?php echo e($purchaseOrder->po_number); ?></td>
                <td width="120">Tanggal PO</td>
                <td>: <?php echo e($purchaseOrder->order_date ? $purchaseOrder->order_date->format('d/m/Y') : '-'); ?></td>
            </tr>
            <tr>
                <td>Expected Delivery</td>
                <td>: <?php echo e($purchaseOrder->expected_delivery ? $purchaseOrder->expected_delivery->format('d/m/Y') : '-'); ?></td>
                <td>Status</td>
                <td>: <?php echo e(ucfirst($purchaseOrder->status)); ?></td>
            </tr>
            <tr>
                <td>Jenis</td>
                <td>: <?php echo e($purchaseOrder->type === 'PPN' ? 'PPN' : 'Non-PPN'); ?></td>
                <td></td>
                <td></td>
            </tr>
        </table>
    </div>

    <div class="supplier-info">
        <h4>Kepada Supplier:</h4>
        <table class="info-table">
            <tr>
                <td width="100"><strong>Nama</strong></td>
                <td>: <?php echo e($purchaseOrder->supplier->name); ?></td>
            </tr>
            <tr>
                <td><strong>Alamat</strong></td>
                <td>: <?php echo e($purchaseOrder->supplier->address); ?></td>
            </tr>
            <tr>
                <td><strong>Telepon</strong></td>
                <td>: <?php echo e($purchaseOrder->supplier->phone); ?></td>
            </tr>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($purchaseOrder->supplier->email): ?>
            <tr>
                <td><strong>Email</strong></td>
                <td>: <?php echo e($purchaseOrder->supplier->email); ?></td>
            </tr>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </table>
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th class="text-center" width="30">No</th>
                <th>Nama Produk</th>
                <th class="text-center" width="60">Satuan</th>
                <th class="text-center" width="60">Qty</th>
                <th class="text-right" width="90">Harga Satuan</th>
                <th class="text-right" width="60">Diskon %</th>
                <th class="text-right" width="100">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $purchaseOrder->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td class="text-center"><?php echo e($index + 1); ?></td>
                <td><?php echo e($item->product->name); ?></td>
                <td class="text-center"><?php echo e($item->unit); ?></td>
                <td class="text-center"><?php echo e(number_format($item->qty_ordered, 2)); ?></td>
                <td class="text-right">Rp <?php echo e(number_format($item->unit_price, 0, ',', '.')); ?></td>
                <td class="text-right"><?php echo e(number_format($item->discount_percent, 2)); ?>%</td>
                <td class="text-right">Rp <?php echo e(number_format($item->subtotal, 0, ',', '.')); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </tbody>
    </table>

    <div class="total-section">
        <div class="total-wrapper">
            <table class="total-table">
                <tr>
                    <td>Subtotal</td>
                    <td class="text-right">Rp <?php echo e(number_format($purchaseOrder->getTotalAmount(), 0, ',', '.')); ?></td>
                </tr>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($purchaseOrder->isPPN()): ?>
                <tr>
                    <td>PPN (11%)</td>
                    <td class="text-right">Rp <?php echo e(number_format($purchaseOrder->getPPNAmount(), 0, ',', '.')); ?></td>
                </tr>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <tr class="total-row">
                    <td><strong>Grand Total</strong></td>
                    <td class="text-right"><strong>Rp <?php echo e(number_format($purchaseOrder->getGrandTotal(), 0, ',', '.')); ?></strong></td>
                </tr>
            </table>
        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($purchaseOrder->notes): ?>
    <div class="notes-section">
        <strong>Catatan:</strong>
        <div style="padding: 5px 0;"><?php echo e($purchaseOrder->notes); ?></div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="signature-section">
        <div class="signature-wrapper">
            <p>Hormat kami,</p>
            <div class="signature-space"></div>
            <div class="signature-line">
                <strong><?php echo e($purchaseOrder->company->name); ?></strong>
            </div>
        </div>
    </div>
</body>
</html><?php /**PATH C:\laragon\www\adamjaya\resources\views/pdf/purchase-order.blade.php ENDPATH**/ ?>