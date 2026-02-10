<?php

namespace Database\Seeders;

use App\Models\PriceQuotation;
use App\Models\PriceQuotationItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\DeliveryNote;
use App\Models\DeliveryNoteItem;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companyId = '01234567-89ab-cdef-0123-456789abcdef'; // PT. Adam Jaya Utama
        
        // Get first customer, supplier, and products
        $customer = Customer::where('company_id', $companyId)->first();
        $supplier = Supplier::where('company_id', $companyId)->first();
        $products = Product::where('company_id', $companyId)->where('product_type', 'STOCK')->take(3)->get();
        
        if (!$customer || !$supplier || $products->count() < 3) {
            $this->command->error('Not enough master data to create sample transactions');
            return;
        }

        // 1. Create Price Quotation
        $quotation = PriceQuotation::create([
            'quotation_id' => Str::uuid(),
            'company_id' => $companyId,
            'customer_id' => $customer->customer_id,
            'quotation_number' => 'PH-2025-001',
            'quotation_date' => now()->subDays(10),
            'valid_until' => now()->addDays(20),
            'subtotal' => 11950000,
            'tax_amount' => 1195000,
            'total_amount' => 13145000,
            'status' => 'approved',
            'notes' => 'Penawaran harga untuk pembelian komputer dan aksesoris',
        ]);

        // Quotation items
        PriceQuotationItem::create([
            'quotation_id' => $quotation->quotation_id,
            'product_id' => $products[0]->product_id, // Laptop
            'quantity' => 1,
            'unit_price' => 10000000,
            'total_price' => 10000000,
            'notes' => 'Laptop Dell untuk staff',
        ]);

        PriceQuotationItem::create([
            'quotation_id' => $quotation->quotation_id,
            'product_id' => $products[1]->product_id, // Mouse
            'quantity' => 2,
            'unit_price' => 450000,
            'total_price' => 900000,
            'notes' => 'Mouse wireless',
        ]);

        PriceQuotationItem::create([
            'quotation_id' => $quotation->quotation_id,
            'product_id' => $products[2]->product_id, // Keyboard
            'quantity' => 1,
            'unit_price' => 950000,
            'total_price' => 950000,
            'notes' => 'Keyboard mechanical',
        ]);

        // 2. Create Purchase Order
        $purchaseOrder = PurchaseOrder::create([
            'po_id' => Str::uuid(),
            'company_id' => $companyId,
            'supplier_id' => $supplier->supplier_id,
            'po_number' => 'PO-2025-001',
            'order_date' => now()->subDays(8),
            'expected_date' => now()->subDays(1),
            'subtotal' => 9650000,
            'tax_amount' => 965000,
            'total_amount' => 10615000,
            'status' => 'completed',
            'notes' => 'Purchase order untuk restok inventory',
        ]);

        // Purchase order items
        PurchaseOrderItem::create([
            'po_id' => $purchaseOrder->po_id,
            'product_id' => $products[0]->product_id,
            'quantity' => 1,
            'unit_price' => 8500000,
            'total_price' => 8500000,
            'notes' => 'Laptop Dell Inspiron',
        ]);

        PurchaseOrderItem::create([
            'po_id' => $purchaseOrder->po_id,
            'product_id' => $products[1]->product_id,
            'quantity' => 3,
            'unit_price' => 350000,
            'total_price' => 1050000,
            'notes' => 'Mouse Logitech',
        ]);

        // 3. Create Stocks (simulate goods receipt)
        foreach ($products->take(2) as $index => $product) {
            $quantities = [1, 3][$index]; // Laptop: 1, Mouse: 3
            $unitCosts = [8500000, 350000][$index];
            
            $stock = Stock::create([
                'stock_id' => Str::uuid(),
                'company_id' => $companyId,
                'product_id' => $product->product_id,
                'product_code' => $product->product_code,
                'product_name' => $product->name,
                'product_type' => $index === 0 ? 'Import' : 'Local',
                'unit' => $product->unit,
                'category' => $product->category,
                'base_price' => $product->base_price,
                'batch_number' => 'BATCH-' . date('Ymd') . '-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                'quantity' => $quantities,
                'available_quantity' => $quantities,
                'reserved_quantity' => 0,
                'minimum_stock' => 5,
                'unit_cost' => $unitCosts,
                'expiry_date' => $product->category === 'Elektronik' ? now()->addYears(2) : null,
                'location' => 'Gudang Utama',
            ]);

            // Create stock movement for purchase
            StockMovement::create([
                'stock_movement_id' => Str::uuid(),
                'company_id' => $companyId,
                'product_id' => $product->product_id,
                'stock_id' => $stock->stock_id,
                'movement_type' => 'in',
                'quantity' => $quantities,
                'unit_cost' => $unitCosts,
                'reference_type' => 'purchase_order',
                'reference_id' => $purchaseOrder->po_id,
                'batch_number' => $stock->batch_number,
                'expiry_date' => $stock->expiry_date,
                'notes' => 'Stock masuk dari PO-2025-001',
            ]);
        }

        // 4. Create Delivery Note (Sales)
        $deliveryNote = DeliveryNote::create([
            'delivery_id' => Str::uuid(),
            'company_id' => $companyId,
            'customer_id' => $customer->customer_id,
            'sj_number' => 'SJ-2025-001',
            'delivery_date' => now()->subDays(3),
            'subtotal' => 10450000,
            'tax_amount' => 1045000,
            'total_amount' => 11495000,
            'status' => 'delivered',
            'notes' => 'Pengiriman laptop dan mouse ke customer',
        ]);

        // Delivery note items
        DeliveryNoteItem::create([
            'delivery_id' => $deliveryNote->delivery_id,
            'product_id' => $products[0]->product_id,
            'quantity' => 1,
            'unit_price' => 10000000,
            'total_price' => 10000000,
            'notes' => 'Laptop Dell untuk kantor customer',
        ]);

        DeliveryNoteItem::create([
            'delivery_id' => $deliveryNote->delivery_id,
            'product_id' => $products[1]->product_id,
            'quantity' => 1,
            'unit_price' => 450000,
            'total_price' => 450000,
            'notes' => 'Mouse wireless',
        ]);

        // Create stock movements for sales (stock out)
        foreach ($products->take(2) as $index => $product) {
            $salesQuantities = [1, 1][$index]; // Laptop: 1, Mouse: 1
            $stocks = Stock::where('product_id', $product->product_id)->where('quantity', '>', 0)->get();
            
            foreach ($stocks as $stock) {
                if ($salesQuantities > 0) {
                    $moveQty = min($salesQuantities, $stock->quantity);
                    
                    StockMovement::create([
                        'stock_movement_id' => Str::uuid(),
                        'company_id' => $companyId,
                        'product_id' => $product->product_id,
                        'stock_id' => $stock->stock_id,
                        'movement_type' => 'out',
                        'quantity' => $moveQty,
                        'unit_cost' => $stock->unit_cost,
                        'reference_type' => 'delivery_note',
                        'reference_id' => $deliveryNote->delivery_id,
                        'batch_number' => $stock->batch_number,
                        'expiry_date' => $stock->expiry_date,
                        'notes' => 'Stock keluar untuk SJ-2025-001',
                    ]);

                    // Update stock quantity
                    $stock->quantity -= $moveQty;
                    $stock->save();
                    
                    $salesQuantities -= $moveQty;
                }
            }
        }

        // 5. Create Invoice
        $invoice = Invoice::create([
            'invoice_id' => Str::uuid(),
            'company_id' => $companyId,
            'customer_id' => $customer->customer_id,
            'delivery_note_id' => $deliveryNote->delivery_id,
            'invoice_number' => 'INV-2025-001',
            'invoice_date' => now()->subDays(2),
            'due_date' => now()->addDays(28),
            'subtotal' => 10450000,
            'tax_amount' => 1045000,
            'total_amount' => 11495000,
            'paid_amount' => 0,
            'status' => 'pending',
            'notes' => 'Invoice untuk pengiriman SJ-2025-001',
        ]);

        // Invoice items (copy from delivery note)
        InvoiceItem::create([
            'invoice_id' => $invoice->invoice_id,
            'product_id' => $products[0]->product_id,
            'quantity' => 1,
            'unit_price' => 10000000,
            'total_price' => 10000000,
            'notes' => 'Laptop Dell untuk kantor customer',
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->invoice_id,
            'product_id' => $products[1]->product_id,
            'quantity' => 1,
            'unit_price' => 450000,
            'total_price' => 450000,
            'notes' => 'Mouse wireless',
        ]);

        // 6. Create partial payment
        Payment::create([
            'payment_id' => Str::uuid(),
            'company_id' => $companyId,
            'invoice_id' => $invoice->invoice_id,
            'payment_date' => now()->subDays(1),
            'amount' => 5000000,
            'payment_method' => 'transfer',
            'reference_number' => 'TRF-2025-001',
            'notes' => 'Pembayaran DP 50%',
        ]);

        // Update invoice paid amount
        $invoice->paid_amount = 5000000;
        $invoice->status = 'partial';
        $invoice->save();

        $this->command->info('Sample transactions created successfully!');
        $this->command->info('- 1 Price Quotation (PH-2025-001)');
        $this->command->info('- 1 Purchase Order (PO-2025-001)');
        $this->command->info('- Stock movements for purchases');
        $this->command->info('- 1 Delivery Note (SJ-2025-001)');
        $this->command->info('- Stock movements for sales');
        $this->command->info('- 1 Invoice (INV-2025-001)');
        $this->command->info('- 1 Partial payment');
    }
}