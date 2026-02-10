<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\User;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\Stock;
use App\Models\PriceQuotation;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchasePayment;
use App\Models\DeliveryNote;
use App\Models\DeliveryNoteItem;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ComprehensiveTestSeeder extends Seeder
{
    private $company;
    private $user;
    private $customers = [];
    private $suppliers = [];
    private $products = [];

    public function run(): void
    {
        $this->command->info('🚀 Starting Comprehensive Test Data Seeding...');

        // Clear existing test data first
        $this->clearTestData();

        // 1. Create Company & User
        $this->createCompanyAndUser();
        
        // 2. Create Master Data
        $this->createCustomers(15);
        $this->createSuppliers(10);
        $this->createProducts(50);
        
        // 3. Create Stock Data
        $this->createStockData();
        
        // 4. Create Purchase Flow
        $this->createPurchaseFlow(20); // 20 PO with various scenarios
        
        // 5. Create Sales Flow
        $this->createSalesFlow(30); // 30 Invoices with various scenarios
        
        $this->command->info('✅ Comprehensive Test Data Seeding Completed!');
    }

    private function clearTestData(): void
    {
        $this->command->info('🗑️  Clearing existing test data...');
        
        // Find test company
        $testCompany = Company::where('code', 'TEST001')->first();
        
        if ($testCompany) {
            $companyId = $testCompany->company_id;
            
            // Delete in correct order to respect foreign keys
            Payment::where('company_id', $companyId)->forceDelete();
            InvoiceItem::whereIn('invoice_id', function($q) use ($companyId) {
                $q->select('invoice_id')->from('invoices')->where('company_id', $companyId);
            })->forceDelete();
            Invoice::where('company_id', $companyId)->forceDelete();
            
            DeliveryNoteItem::whereIn('sj_id', function($q) use ($companyId) {
                $q->select('sj_id')->from('delivery_notes')->where('company_id', $companyId);
            })->forceDelete();
            DeliveryNote::where('company_id', $companyId)->forceDelete();
            
            PurchasePayment::where('company_id', $companyId)->forceDelete();
            PurchaseOrderItem::whereIn('po_id', function($q) use ($companyId) {
                $q->select('po_id')->from('purchase_orders')->where('company_id', $companyId);
            })->forceDelete();
            PurchaseOrder::where('company_id', $companyId)->forceDelete();
            
            \App\Models\PriceQuotationItem::whereIn('ph_id', function($q) use ($companyId) {
                $q->select('ph_id')->from('price_quotations')->where('company_id', $companyId);
            })->forceDelete();
            PriceQuotation::where('company_id', $companyId)->forceDelete();
            
            \App\Models\StockMovement::where('company_id', $companyId)->forceDelete();
            Stock::where('company_id', $companyId)->forceDelete();
            Product::where('company_id', $companyId)->forceDelete();
            
            Customer::where('company_id', $companyId)->forceDelete();
            Supplier::where('company_id', $companyId)->forceDelete();
        }
        
        $this->command->info('✅ Test data cleared!');
    }

    private function createCompanyAndUser(): void
    {
        $this->command->info('Creating Company & User...');

        $this->company = Company::firstOrCreate(
            ['code' => 'TEST001'],
            [
                'name' => 'PT Test Jaya Abadi',
                'address' => 'Jl. Test No. 123, Jakarta',
                'phone' => '021-1234567',
                'email' => 'test@testjaya.com',
                'npwp' => '01.234.567.8-901.000',
            ]
        );

        // Cari user berdasarkan username atau email
        $this->user = User::where('username', 'admin')
            ->orWhere('email', 'admin@test.com')
            ->first();
        
        // Jika tidak ada, buat baru
        if (!$this->user) {
            $this->user = User::create([
                'id' => Str::uuid(),
                'name' => 'Admin Test',
                'username' => 'admintest',
                'email' => 'admin@test.com',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
        }

        // Attach user to company
        if (!$this->user->companies()->where('user_company_roles.company_id', $this->company->company_id)->exists()) {
            $this->user->companies()->attach($this->company->company_id, [
                'role' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function createCustomers(int $count): void
    {
        $this->command->info("Creating {$count} Customers...");

        $cities = ['Jakarta', 'Surabaya', 'Bandung', 'Medan', 'Semarang'];
        $schedules = ['Weekly', 'Bi-Weekly', 'Monthly'];

        for ($i = 1; $i <= $count; $i++) {
            $this->customers[] = Customer::create([
                'customer_id' => Str::uuid(),
                'company_id' => $this->company->company_id,
                'customer_code' => 'CUST' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'name' => fake()->company(),
                'contact_person' => fake()->name(),
                'email' => fake()->companyEmail(),
                'phone' => fake()->phoneNumber(),
                'address_ship_to' => fake()->address(),
                'address_bill_to' => fake()->address(),
                'city' => fake()->randomElement($cities),
                'npwp' => fake()->numerify('##.###.###.#-###.###'),
                'billing_schedule' => fake()->randomElement($schedules),
                'is_ppn' => fake()->boolean(70),
                'is_active' => true,
                'credit_limit' => fake()->randomElement([5000000000, 10000000000]), // Credit limit 5-10 miliar untuk test
            ]);
        }
    }

    private function createSuppliers(int $count): void
    {
        $this->command->info("Creating {$count} Suppliers...");

        $types = ['Local', 'Import'];

        for ($i = 1; $i <= $count; $i++) {
            $this->suppliers[] = Supplier::create([
                'supplier_id' => Str::uuid(),
                'company_id' => $this->company->company_id,
                'supplier_code' => 'SUPP' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'name' => fake()->company() . ' Supplier',
                'type' => fake()->randomElement($types),
                'contact_person' => fake()->name(),
                'email' => fake()->companyEmail(),
                'phone' => fake()->phoneNumber(),
                'address' => fake()->address(),
                'is_active' => true,
            ]);
        }
    }

    private function createProducts(int $count): void
    {
        $this->command->info("Creating {$count} Products...");

        $categories = ['Electronics', 'Furniture', 'Office Supplies', 'Food & Beverage', 'Hardware'];
        $units = ['pcs', 'box', 'unit', 'pack', 'kg'];

        for ($i = 1; $i <= $count; $i++) {
            $productCode = 'PRD' . str_pad($i, 5, '0', STR_PAD_LEFT);
            $this->products[] = Product::create([
                'product_id' => Str::uuid(),
                'company_id' => $this->company->company_id,
                'product_code' => $productCode,
                'original_product_code' => 'ORG-' . $productCode,
                'name' => fake()->randomElement(['Laptop', 'Mouse', 'Keyboard', 'Monitor', 'Desk', 'Chair', 'Printer', 'Scanner', 'Cable', 'Adapter']) . ' ' . fake()->word(),
                'category' => fake()->randomElement($categories),
                'product_type' => 'STOCK',
                'unit' => fake()->randomElement($units),
                'base_price' => fake()->numberBetween(100000, 8000000),
                'default_discount_percent' => fake()->randomElement([0, 5, 10, 15, 20]),
                'min_stock_alert' => fake()->numberBetween(10, 50),
                'description' => fake()->sentence(),
                'is_active' => true,
            ]);
        }
    }

    private function createStockData(): void
    {
        $this->command->info('Creating Stock Data with various scenarios...');

        foreach ($this->products as $index => $product) {
            // Scenario distribution:
            // 40% Normal stock
            // 30% Low stock (below minimum)
            // 20% Expired or near expiry
            // 10% Zero stock

            $scenario = $index % 10;

            if ($scenario < 4) {
                // Normal stock
                $quantity = fake()->numberBetween(100, 500);
                $minimum = fake()->numberBetween(20, 50);
                $expiryDate = now()->addMonths(rand(6, 24));
            } elseif ($scenario < 7) {
                // Low stock (below minimum) - untuk notifikasi
                $minimum = fake()->numberBetween(50, 100);
                $quantity = fake()->numberBetween(5, $minimum - 1);
                $expiryDate = now()->addMonths(rand(6, 12));
            } elseif ($scenario < 9) {
                // Expired or near expiry - untuk notifikasi
                $quantity = fake()->numberBetween(10, 100);
                $minimum = fake()->numberBetween(10, 30);
                $expiryDate = now()->subDays(rand(1, 90)); // Already expired
            } else {
                // Zero stock
                $quantity = 0;
                $minimum = fake()->numberBetween(10, 50);
                $expiryDate = null;
            }

            Stock::create([
                'stock_id' => Str::uuid(),
                'company_id' => $this->company->company_id,
                'product_id' => $product->product_id,
                'product_code' => $product->product_code,
                'product_name' => $product->name,
                'product_type' => $index % 2 === 0 ? 'Local' : 'Import',
                'unit' => $product->unit,
                'category' => $product->category,
                'base_price' => $product->base_price,
                'batch_number' => 'BATCH-' . now()->format('Ymd') . '-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                'quantity' => $quantity,
                'reserved_quantity' => 0,
                'available_quantity' => $quantity,
                'minimum_stock' => $minimum,
                'unit_cost' => $product->base_price,
                'expiry_date' => $expiryDate,
                'location' => fake()->randomElement(['Gudang A', 'Gudang B', 'Gudang C']),
                'created_by' => $this->user->id,
            ]);
        }
    }

    private function createPurchaseFlow(int $count): void
    {
        $this->command->info("Creating {$count} Purchase Orders with payments...");

        for ($i = 1; $i <= $count; $i++) {
            $supplier = fake()->randomElement($this->suppliers);
            $orderDate = now()->subDays(rand(1, 90));
            $expectedDelivery = $orderDate->copy()->addDays(rand(7, 30));
            $paymentTerms = fake()->randomElement([7, 14, 30, 45, 60]);
            $dueDate = $orderDate->copy()->addDays($paymentTerms);
            $type = fake()->randomElement(['PPN', 'Non-PPN']);

            $po = PurchaseOrder::create([
                'po_id' => Str::uuid(),
                'company_id' => $this->company->company_id,
                'supplier_id' => $supplier->supplier_id,
                'po_number' => 'PO-' . now()->format('Ym') . '-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'type' => $type,
                'order_date' => $orderDate,
                'expected_delivery' => $expectedDelivery,
                'due_date' => $dueDate,
                'payment_terms_days' => $paymentTerms,
                'status' => 'completed',
                'payment_status' => 'unpaid',
                'notes' => 'Test Purchase Order ' . $i,
                'created_by' => $this->user->id,
            ]);

            // Add 2-5 items per PO
            $itemCount = rand(2, 5);
            $totalAmount = 0;

            for ($j = 0; $j < $itemCount; $j++) {
                $product = fake()->randomElement($this->products);
                $qty = rand(10, 100);
                $price = $product->base_price;
                $subtotal = $qty * $price;
                $totalAmount += $subtotal;

                PurchaseOrderItem::create([
                    'po_item_id' => Str::uuid(),
                    'po_id' => $po->po_id,
                    'product_id' => $product->product_id,
                    'qty_ordered' => $qty,
                    'qty_received' => $qty,
                    'unit' => $product->unit,
                    'unit_price' => $price,
                    'subtotal' => $subtotal,
                ]);
            }

            // Payment scenarios:
            // 30% Fully paid
            // 30% Partially paid
            // 20% Unpaid but not overdue
            // 20% Unpaid and overdue (untuk notifikasi)

            $grandTotal = $totalAmount;
            if ($type === 'PPN') {
                $grandTotal = $totalAmount * 1.11;
            }

            $scenario = $i % 10;

            if ($scenario < 3) {
                // Fully paid
                PurchasePayment::create([
                    'payment_id' => Str::uuid(),
                    'company_id' => $this->company->company_id,
                    'po_id' => $po->po_id,
                    'supplier_id' => $supplier->supplier_id,
                    'payment_date' => $orderDate->copy()->addDays(rand(1, max(1, $paymentTerms - 5))),
                    'amount' => $grandTotal,
                    'payment_method' => fake()->randomElement(['transfer', 'cash', 'check', 'giro']),
                    'reference_number' => 'PAY-' . fake()->numerify('######'),
                    'notes' => 'Full payment',
                    'created_by' => $this->user->id,
                ]);
                $po->update(['payment_status' => 'paid']);
            } elseif ($scenario < 6) {
                // Partially paid
                $paidAmount = $grandTotal * fake()->randomFloat(2, 0.3, 0.7);
                PurchasePayment::create([
                    'payment_id' => Str::uuid(),
                    'company_id' => $this->company->company_id,
                    'po_id' => $po->po_id,
                    'supplier_id' => $supplier->supplier_id,
                    'payment_date' => $orderDate->copy()->addDays(rand(5, 15)),
                    'amount' => $paidAmount,
                    'payment_method' => fake()->randomElement(['transfer', 'cash']),
                    'reference_number' => 'PAY-' . fake()->numerify('######'),
                    'notes' => 'Partial payment',
                    'created_by' => $this->user->id,
                ]);
                $po->update(['payment_status' => 'partial']);
            } elseif ($scenario < 8) {
                // Unpaid but not overdue yet (due date in future)
                $po->update([
                    'due_date' => now()->addDays(rand(1, 7)),
                    'payment_status' => 'unpaid'
                ]);
            } else {
                // Unpaid and overdue - untuk notifikasi hutang
                $po->update([
                    'due_date' => now()->subDays(rand(1, 30)),
                    'payment_status' => 'unpaid'
                ]);
            }
        }
    }

    private function createSalesFlow(int $count): void
    {
        $this->command->info("Creating {$count} Invoices with various payment scenarios...");

        for ($i = 1; $i <= $count; $i++) {
            $customer = fake()->randomElement($this->customers);
            $issueDate = now()->subDays(rand(1, 90));
            $paymentTerms = fake()->randomElement([7, 14, 30, 45, 60]);
            $dueDate = $issueDate->copy()->addDays($paymentTerms);
            $type = fake()->randomElement(['PPN', 'Non-PPN']);

            // Create Delivery Note first
            $sj = DeliveryNote::create([
                'sj_id' => Str::uuid(),
                'company_id' => $this->company->company_id,
                'customer_id' => $customer->customer_id,
                'sj_number' => 'SJ-' . now()->format('Ym') . '-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'delivery_date' => $issueDate,
                'driver_name' => fake()->name(),
                'vehicle_number' => fake()->regexify('[A-Z]{1}[0-9]{4}[A-Z]{2}'),
                'notes' => 'Test delivery ' . $i,
                'created_by' => $this->user->id,
            ]);

            // Add items to delivery note
            $itemCount = rand(2, 5);
            $totalAmount = 0;

            for ($j = 0; $j < $itemCount; $j++) {
                $product = fake()->randomElement($this->products);
                $qty = rand(5, 50);
                $price = $product->base_price;
                $subtotal = $qty * $price;
                $totalAmount += $subtotal;

                DeliveryNoteItem::create([
                    'sj_item_id' => Str::uuid(),
                    'sj_id' => $sj->sj_id,
                    'product_id' => $product->product_id,
                    'qty' => $qty,
                    'unit' => $product->unit,
                    'unit_price' => $price,
                    'subtotal' => $subtotal,
                ]);
            }

            // Create Invoice
            $ppnAmount = 0;
            if ($type === 'PPN') {
                $ppnAmount = $totalAmount * 0.11;
            }

            $invoice = Invoice::create([
                'invoice_id' => Str::uuid(),
                'company_id' => $this->company->company_id,
                'sj_id' => $sj->sj_id,
                'customer_id' => $customer->customer_id,
                'invoice_number' => 'INV-' . now()->format('Ym') . '-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'type' => $type,
                'invoice_date' => $issueDate,
                'due_date' => $dueDate,
                'total_amount' => $totalAmount,
                'ppn_amount' => $ppnAmount,
                'grand_total' => $totalAmount + $ppnAmount,
                'status' => 'Unpaid',
                'payment_terms' => $paymentTerms,
                'notes' => 'Test invoice ' . $i,
                'created_by' => $this->user->id,
            ]);

            // Copy items from delivery note to invoice
            foreach ($sj->items as $sjItem) {
                InvoiceItem::create([
                    'invoice_item_id' => Str::uuid(),
                    'invoice_id' => $invoice->invoice_id,
                    'product_id' => $sjItem->product_id,
                    'qty' => $sjItem->qty,
                    'unit' => $sjItem->unit,
                    'unit_price' => $sjItem->unit_price,
                    'subtotal' => $sjItem->subtotal,
                ]);
            }

            // Payment scenarios for invoice (piutang):
            // 30% Fully paid
            // 30% Partially paid
            // 20% Unpaid but not overdue
            // 20% Unpaid and overdue (untuk notifikasi piutang)

            $grandTotal = $totalAmount + $ppnAmount;
            $scenario = $i % 10;

            if ($scenario < 3) {
                // Fully paid
                Payment::create([
                    'payment_id' => Str::uuid(),
                    'company_id' => $this->company->company_id,
                    'invoice_id' => $invoice->invoice_id,
                    'customer_id' => $customer->customer_id,
                    'payment_date' => $issueDate->copy()->addDays(rand(1, $customer->payment_terms - 5)),
                    'amount' => $grandTotal,
                    'payment_method' => fake()->randomElement(['transfer', 'cash', 'check', 'giro']),
                    'reference_number' => 'PAY-' . fake()->numerify('######'),
                    'notes' => 'Full payment',
                    'created_by' => $this->user->id,
                ]);
                $invoice->update(['status' => 'Paid']);
            } elseif ($scenario < 6) {
                // Partially paid
                $paidAmount = $grandTotal * fake()->randomFloat(2, 0.3, 0.7);
                Payment::create([
                    'payment_id' => Str::uuid(),
                    'company_id' => $this->company->company_id,
                    'invoice_id' => $invoice->invoice_id,
                    'customer_id' => $customer->customer_id,
                    'payment_date' => $issueDate->copy()->addDays(rand(5, 15)),
                    'amount' => $paidAmount,
                    'payment_method' => fake()->randomElement(['transfer', 'cash']),
                    'reference_number' => 'PAY-' . fake()->numerify('######'),
                    'notes' => 'Partial payment',
                    'created_by' => $this->user->id,
                ]);
                $invoice->update(['status' => 'Partial']);
            } elseif ($scenario < 8) {
                // Unpaid but not overdue yet (due date in future)
                $invoice->update([
                    'due_date' => now()->addDays(rand(1, 7)),
                    'status' => 'Unpaid'
                ]);
            } else {
                // Unpaid and overdue - untuk notifikasi piutang
                $invoice->update([
                    'due_date' => now()->subDays(rand(1, 30)),
                    'status' => 'Unpaid'
                ]);
            }
        }
    }
}
