<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\Stock;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\DeliveryNote;
use App\Models\DeliveryNoteItem;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\StockMovement;
use App\Models\PriceQuotation;
use App\Models\PriceQuotationItem;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ComprehensiveTestSeeder extends Seeder
{
    private $company;
    private $user;
    
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🌱 Starting Comprehensive Test Seeder...');
        
        // 1. Create Company & User
        $this->createCompanyAndUser();
        
        // 2. Create Customers
        $customers = $this->createCustomers();
        
        // 3. Create Suppliers
        $suppliers = $this->createSuppliers();
        
        // 4. Create Products (CATALOG & STOCK)
        $products = $this->createProducts();
        
        // 5. Create Price Quotations (PH)
        $this->createPriceQuotations($customers, $suppliers, $products);
        
        // 6. Create Initial Stock (with various scenarios)
        $this->createInitialStock($products);
        
        // 7. Create Purchase Orders (complete flow)
        $this->createPurchaseOrders($suppliers, $products);
        
        // 8. Create Stock Movements (various types and scenarios)
        $this->createStockMovements($products);
        
        // 9. Create Delivery Notes (various statuses + anomalies)
        $this->createDeliveryNotes($customers, $products);
        
        // 10. Create Invoices (various payment statuses)
        $this->createInvoices($customers, $products);
        
        // 11. Create some soft deleted records to test duplicate prevention
        $this->createSoftDeletedRecords($customers, $products);
        
        $this->command->info('✅ Comprehensive Test Seeder completed successfully!');
    }
    
    private function createCompanyAndUser()
    {
        $this->command->info('Creating company and user...');
        
        $this->company = Company::firstOrCreate(
            ['company_id' => '01234567-89ab-cdef-0123-456789abcdef'],
            [
                'code' => 'TEST001',
                'name' => 'PT Test Company',
                'address' => 'Jl. Test No. 123',
                'phone' => '021-1234567',
                'email' => 'test@company.com',
            ]
        );
        
        $this->user = User::firstOrCreate(
            ['email' => 'admin@test.com'],
            [
                'username' => 'admin',
                'name' => 'Admin Test',
                'password' => Hash::make('password'),
            ]
        );
        
        session(['selected_company_id' => $this->company->company_id]);
    }
    
    private function createCustomers(): array
    {
        $this->command->info('Creating customers...');
        
        $customers = [];
        
        // PPN Customer
        $customers[] = Customer::create([
            'company_id' => $this->company->company_id,
            'customer_code' => 'CUST-001',
            'name' => 'PT Maju Jaya (PPN)',
            'contact_person' => 'Budi Santoso',
            'email' => 'majujaya@email.com',
            'phone' => '021-111111',
            'address_ship_to' => 'Jl. Sudirman No. 123, Jakarta Pusat',
            'address_bill_to' => 'Jl. Sudirman No. 123, Jakarta Pusat',
            'npwp' => '01.234.567.8-901.000',
            'billing_schedule' => 'Setiap tanggal 5',
            'is_ppn' => true,
            'is_active' => true,
        ]);
        
        // Non-PPN Customer
        $customers[] = Customer::create([
            'company_id' => $this->company->company_id,
            'customer_code' => 'CUST-002',
            'name' => 'CV Sukses Makmur (Non-PPN)',
            'contact_person' => 'Siti Nurhaliza',
            'email' => 'sukses@email.com',
            'phone' => '021-222222',
            'address_ship_to' => 'Jl. Asia Afrika No. 45, Bandung',
            'address_bill_to' => 'Jl. Asia Afrika No. 45, Bandung',
            'billing_schedule' => 'Setiap minggu ke-2',
            'is_ppn' => false,
            'is_active' => true,
        ]);
        
        // Inactive Customer (for testing)
        $customers[] = Customer::create([
            'company_id' => $this->company->company_id,
            'customer_code' => 'CUST-003',
            'name' => 'PT Inactive Customer',
            'contact_person' => 'John Doe',
            'email' => 'inactive@email.com',
            'phone' => '021-333333',
            'address_ship_to' => 'Jl. Thamrin No. 99, Surabaya',
            'address_bill_to' => 'Jl. Thamrin No. 99, Surabaya',
            'npwp' => '98.765.432.1-098.000',
            'is_ppn' => true,
            'is_active' => false,
        ]);
        
        return $customers;
    }
    
    private function createSuppliers(): array
    {
        $this->command->info('Creating suppliers...');
        
        $suppliers = [];
        
        $suppliers[] = Supplier::create([
            'company_id' => $this->company->company_id,
            'supplier_code' => 'SUPP-001',
            'name' => 'PT Supplier Utama',
            'type' => 'Local',
            'email' => 'supplier1@email.com',
            'phone' => '021-444444',
            'address' => 'Jl. Gatot Subroto No. 88, Jakarta Selatan',
            'contact_person' => 'Ahmad Yani',
            'is_active' => true,
        ]);
        
        $suppliers[] = Supplier::create([
            'company_id' => $this->company->company_id,
            'supplier_code' => 'SUPP-002',
            'name' => 'CV Supplier Kedua',
            'type' => 'Local',
            'email' => 'supplier2@email.com',
            'phone' => '021-555555',
            'address' => 'Jl. Raya Serpong No. 12, Tangerang',
            'contact_person' => 'Siti Aminah',
            'is_active' => true,
        ]);
        
        return $suppliers;
    }
    
    private function createProducts(): array
    {
        $this->command->info('Creating products...');
        
        $products = [];
        
        // CATALOG Products (no stock tracking)
        $products['catalog'][] = Product::create([
            'company_id' => $this->company->company_id,
            'product_code' => 'SVC-001',
            'name' => 'Jasa Konsultasi',
            'description' => 'Jasa konsultasi IT per jam',
            'product_type' => 'CATALOG',
            'unit' => 'Hour',
            'base_price' => 500000,
            'default_discount_percent' => 0,
            'category' => 'Services',
            'is_active' => true,
        ]);
        
        $products['catalog'][] = Product::create([
            'company_id' => $this->company->company_id,
            'product_code' => 'SVC-002',
            'name' => 'Jasa Instalasi',
            'description' => 'Jasa instalasi software dan hardware',
            'product_type' => 'CATALOG',
            'unit' => 'Set',
            'base_price' => 1000000,
            'default_discount_percent' => 0,
            'category' => 'Services',
            'is_active' => true,
        ]);
        
        // STOCK Products (with inventory)
        $products['stock'][] = Product::create([
            'company_id' => $this->company->company_id,
            'product_code' => 'PROD-001',
            'name' => 'Laptop Dell Latitude',
            'description' => 'Laptop Dell Latitude 5420, i5, 8GB RAM, 256GB SSD',
            'product_type' => 'STOCK',
            'unit' => 'Unit',
            'base_price' => 10000000,
            'default_discount_percent' => 0,
            'min_stock_alert' => 5,
            'category' => 'Electronics',
            'is_active' => true,
        ]);
        
        $products['stock'][] = Product::create([
            'company_id' => $this->company->company_id,
            'product_code' => 'PROD-002',
            'name' => 'Mouse Wireless Logitech',
            'description' => 'Mouse wireless Logitech M185',
            'product_type' => 'STOCK',
            'unit' => 'Unit',
            'base_price' => 250000,
            'default_discount_percent' => 5,
            'min_stock_alert' => 20,
            'category' => 'Accessories',
            'is_active' => true,
        ]);
        
        $products['stock'][] = Product::create([
            'company_id' => $this->company->company_id,
            'product_code' => 'PROD-003',
            'name' => 'Keyboard Mechanical',
            'description' => 'Keyboard mechanical RGB',
            'product_type' => 'STOCK',
            'unit' => 'Unit',
            'base_price' => 750000,
            'default_discount_percent' => 0,
            'min_stock_alert' => 10,
            'category' => 'Accessories',
            'is_active' => true,
        ]);
        
        // Product with low stock (for testing alerts)
        $products['stock'][] = Product::create([
            'company_id' => $this->company->company_id,
            'product_code' => 'PROD-004',
            'name' => 'Monitor 24 inch',
            'description' => 'Monitor LED 24 inch Full HD',
            'product_type' => 'STOCK',
            'unit' => 'Unit',
            'base_price' => 2000000,
            'default_discount_percent' => 0,
            'min_stock_alert' => 10,
            'category' => 'Electronics',
            'is_active' => true,
        ]);
        
        // Product that will expire soon
        $products['stock'][] = Product::create([
            'company_id' => $this->company->company_id,
            'product_code' => 'PROD-005',
            'name' => 'Tinta Printer (Expirable)',
            'description' => 'Tinta printer HP original',
            'product_type' => 'STOCK',
            'unit' => 'Box',
            'base_price' => 300000,
            'default_discount_percent' => 0,
            'min_stock_alert' => 5,
            'category' => 'Consumables',
            'is_active' => true,
        ]);
        
        return $products;
    }
    
    private function createPriceQuotations(array $customers, array $suppliers, array $products): void
    {
        $this->command->info('Creating price quotations...');
        
        // PH untuk Supplier - Migration HANYA punya supplier_id (TIDAK ada entity_type/customer_id)
        $phSupplier1 = PriceQuotation::create([
            'company_id' => $this->company->company_id,
            'supplier_id' => $suppliers[0]->supplier_id,
            'quotation_number' => 'PH/2025/10/001',
            'quotation_date' => now()->subDays(15),
            'valid_until' => now()->addDays(15),
            'type' => 'PPN',
            'status' => 'Accepted',
            'notes' => 'Request quotation untuk stock keyboard dan mouse',
            'created_by' => $this->user->id,
        ]);
        
        PriceQuotationItem::create([
            'ph_id' => $phSupplier1->ph_id,
            'product_id' => $products['stock'][2]->product_id, // Keyboard
            'qty' => 50,
            'unit' => 'Unit',
            'unit_price' => 500000,
            'discount_percent' => 10,
            'subtotal' => 22500000,
        ]);
        
        PriceQuotationItem::create([
            'ph_id' => $phSupplier1->ph_id,
            'product_id' => $products['stock'][1]->product_id, // Mouse
            'qty' => 100,
            'unit' => 'Unit',
            'unit_price' => 150000,
            'discount_percent' => 5,
            'subtotal' => 14250000,
        ]);
        
        // PH Draft (belum terkirim)
        $phDraft = PriceQuotation::create([
            'company_id' => $this->company->company_id,
            'supplier_id' => $suppliers[1]->supplier_id,
            'quotation_number' => 'PH/2025/10/002',
            'quotation_date' => now()->subDays(5),
            'valid_until' => now()->addDays(25),
            'type' => 'Non-PPN',
            'status' => 'Draft',
            'notes' => 'Draft quotation untuk laptop',
            'created_by' => $this->user->id,
        ]);
        
        PriceQuotationItem::create([
            'ph_id' => $phDraft->ph_id,
            'product_id' => $products['stock'][0]->product_id, // Laptop
            'qty' => 10,
            'unit' => 'Unit',
            'unit_price' => 8000000,
            'discount_percent' => 0,
            'subtotal' => 80000000,
        ]);
    }
    
    private function createInitialStock(array $products): void
    {
        $this->command->info('Creating initial stock...');
        
        // Normal stock
        Stock::create([
            'company_id' => $this->company->company_id,
            'product_id' => $products['stock'][0]->product_id, // Laptop
            'batch_number' => 'BATCH-001',
            'quantity' => 50,
            'available_quantity' => 50,
            'reserved_quantity' => 0,
            'minimum_stock' => 10,
            'unit_cost' => 8000000,
            'location' => 'Gudang A',
        ]);
        
        Stock::create([
            'company_id' => $this->company->company_id,
            'product_id' => $products['stock'][1]->product_id, // Mouse
            'batch_number' => 'BATCH-002',
            'quantity' => 100,
            'available_quantity' => 100,
            'reserved_quantity' => 0,
            'minimum_stock' => 20,
            'unit_cost' => 150000,
            'location' => 'Gudang A',
        ]);
        
        Stock::create([
            'company_id' => $this->company->company_id,
            'product_id' => $products['stock'][2]->product_id, // Keyboard
            'quantity' => 75,
            'available_quantity' => 75,
            'reserved_quantity' => 0,
            'minimum_stock' => 15,
            'unit_cost' => 500000,
            'location' => 'Gudang B',
        ]);
        
        // Low stock (below minimum) - should trigger alert
        Stock::create([
            'company_id' => $this->company->company_id,
            'product_id' => $products['stock'][3]->product_id, // Monitor
            'quantity' => 5,
            'available_quantity' => 5,
            'reserved_quantity' => 0,
            'minimum_stock' => 10,
            'unit_cost' => 1500000,
            'location' => 'Gudang A',
        ]);
        
        // Stock that will expire soon
        Stock::create([
            'company_id' => $this->company->company_id,
            'product_id' => $products['stock'][4]->product_id, // Tinta
            'batch_number' => 'BATCH-EXP-001',
            'quantity' => 30,
            'available_quantity' => 30,
            'reserved_quantity' => 0,
            'minimum_stock' => 10,
            'unit_cost' => 200000,
            'expiry_date' => now()->addDays(25), // Will expire in 25 days
            'location' => 'Gudang C',
        ]);
    }
    
    private function createPurchaseOrders(array $suppliers, array $products): void
    {
        $this->command->info('Creating purchase orders...');
        
        // Complete PO with items - Migration: NO amount fields, hanya type, order_date, expected_delivery, status
        $po1 = PurchaseOrder::create([
            'company_id' => $this->company->company_id,
            'supplier_id' => $suppliers[0]->supplier_id,
            'po_number' => 'PO/2025/10/001',
            'type' => 'PPN',
            'order_date' => now()->subDays(10),
            'expected_delivery' => now()->addDays(5),
            'status' => 'Confirmed',
            'notes' => 'Purchase order untuk stock laptop',
            'created_by' => $this->user->id,
        ]);
        
        PurchaseOrderItem::create([
            'po_id' => $po1->po_id,
            'product_id' => $products['stock'][0]->product_id,
            'qty_ordered' => 10,
            'unit' => 'Unit',
            'unit_price' => 8000000,
            'discount_percent' => 0,
            'subtotal' => 80000000,
        ]);
        
        // PO Pending (menunggu konfirmasi)
        $po2 = PurchaseOrder::create([
            'company_id' => $this->company->company_id,
            'supplier_id' => $suppliers[1]->supplier_id,
            'po_number' => 'PO/2025/10/002',
            'type' => 'PPN',
            'order_date' => now()->subDays(3),
            'expected_delivery' => now()->addDays(10),
            'status' => 'Pending',
            'notes' => 'PO menunggu approval',
            'created_by' => $this->user->id,
        ]);
        
        PurchaseOrderItem::create([
            'po_id' => $po2->po_id,
            'product_id' => $products['stock'][1]->product_id,
            'qty_ordered' => 100,
            'unit' => 'Unit',
            'unit_price' => 150000,
            'discount_percent' => 0,
            'subtotal' => 15000000,
        ]);
    }
    
    private function createStockMovements(array $products): void
    {
        $this->command->info('Creating stock movements...');
        
        // Movement IN - Penerimaan barang
        StockMovement::create([
            'company_id' => $this->company->company_id,
            'product_id' => $products['stock'][0]->product_id,
            'movement_type' => 'in',
            'quantity' => 50,
            'unit_cost' => 8000000,
            'reference_type' => 'purchase_order',
            'reference_id' => Str::uuid(),
            'notes' => 'Penerimaan barang laptop dari supplier',
            'created_by' => $this->user->id,
        ]);
        
        StockMovement::create([
            'company_id' => $this->company->company_id,
            'product_id' => $products['stock'][1]->product_id,
            'movement_type' => 'in',
            'quantity' => 100,
            'unit_cost' => 150000,
            'reference_type' => 'purchase_order',
            'reference_id' => Str::uuid(),
            'notes' => 'Penerimaan mouse',
            'created_by' => $this->user->id,
        ]);
        
        // Movement OUT - Pengiriman barang (akan dikaitkan dengan SJ)
        StockMovement::create([
            'company_id' => $this->company->company_id,
            'product_id' => $products['stock'][0]->product_id,
            'movement_type' => 'out',
            'quantity' => 5,
            'unit_cost' => 8000000,
            'reference_type' => 'delivery_note',
            'reference_id' => Str::uuid(),
            'notes' => 'Pengiriman ke customer',
            'created_by' => $this->user->id,
        ]);
        
        // Movement ADJUSTMENT - Koreksi stock
        StockMovement::create([
            'company_id' => $this->company->company_id,
            'product_id' => $products['stock'][2]->product_id,
            'movement_type' => 'adjustment',
            'quantity' => -5,
            'unit_cost' => 500000,
            'reference_type' => 'manual',
            'reference_id' => Str::uuid(),
            'notes' => 'Koreksi stock - barang rusak',
            'created_by' => $this->user->id,
        ]);
    }
    
    private function createDeliveryNotes(array $customers, array $products): void
    {
        $this->command->info('Creating delivery notes...');
        
        // Draft SJ (not yet sent)
        $sj1 = DeliveryNote::create([
            'company_id' => $this->company->company_id,
            'customer_id' => $customers[0]->customer_id,
            'sj_number' => 'SJ/2025/10/001',
            'type' => 'PPN',
            'delivery_date' => now(),
            'status' => 'Draft',
            'notes' => 'Draft delivery note',
            'created_by' => $this->user->id,
        ]);
        
        DeliveryNoteItem::create([
            'sj_id' => $sj1->sj_id,
            'product_id' => $products['stock'][0]->product_id,
            'qty' => 5,
            'unit' => 'Unit',
            'unit_price' => 10000000,
            'subtotal' => 50000000,
        ]);
        
        // Sent SJ - DENGAN stock movement (normal case)
        $sj2 = DeliveryNote::create([
            'company_id' => $this->company->company_id,
            'customer_id' => $customers[1]->customer_id,
            'sj_number' => 'SJ/2025/10/002',
            'type' => 'Non-PPN',
            'delivery_date' => now()->subDays(2),
            'status' => 'Sent',
            'notes' => 'Sent delivery note with stock movement',
            'created_by' => $this->user->id,
        ]);
        
        $sj2Item1 = DeliveryNoteItem::create([
            'sj_id' => $sj2->sj_id,
            'product_id' => $products['stock'][1]->product_id,
            'qty' => 10,
            'unit' => 'Unit',
            'unit_price' => 250000,
            'subtotal' => 2500000,
        ]);
        
        // Create stock movement for this delivery note
        StockMovement::create([
            'company_id' => $this->company->company_id,
            'product_id' => $products['stock'][1]->product_id,
            'movement_type' => 'out',
            'quantity' => 10,
            'unit_cost' => 150000,
            'reference_type' => 'delivery_note',
            'reference_id' => $sj2->sj_id,
            'notes' => 'Auto generated from delivery note',
            'created_by' => $this->user->id,
        ]);
        
        // ANOMALI 1: Sent SJ - TANPA stock movement (perlu sync)
        $sj3 = DeliveryNote::create([
            'company_id' => $this->company->company_id,
            'customer_id' => $customers[0]->customer_id,
            'sj_number' => 'SJ/2025/10/003',
            'type' => 'PPN',
            'delivery_date' => now()->subDays(3),
            'status' => 'Sent',
            'notes' => '⚠️ ANOMALI - Sent tapi belum ada stock movement',
            'created_by' => $this->user->id,
        ]);
        
        DeliveryNoteItem::create([
            'sj_id' => $sj3->sj_id,
            'product_id' => $products['stock'][2]->product_id,
            'qty' => 8,
            'unit' => 'Unit',
            'unit_price' => 750000,
            'subtotal' => 6000000,
        ]);
        // ⚠️ NOTE: Tidak ada stock movement untuk SJ ini - akan muncul di anomaly report
        
        // ANOMALI 2: Completed SJ - SEBAGIAN ada stock movement
        $sj4 = DeliveryNote::create([
            'company_id' => $this->company->company_id,
            'customer_id' => $customers[1]->customer_id,
            'sj_number' => 'SJ/2025/10/004',
            'type' => 'PPN',
            'delivery_date' => now()->subDays(5),
            'status' => 'Completed',
            'notes' => '⚠️ ANOMALI - Completed dengan 2 item, hanya 1 yang ada stock movement',
            'created_by' => $this->user->id,
        ]);
        
        $sj4Item1 = DeliveryNoteItem::create([
            'sj_id' => $sj4->sj_id,
            'product_id' => $products['stock'][0]->product_id,
            'qty' => 3,
            'unit' => 'Unit',
            'unit_price' => 10000000,
            'subtotal' => 30000000,
        ]);
        
        $sj4Item2 = DeliveryNoteItem::create([
            'sj_id' => $sj4->sj_id,
            'product_id' => $products['stock'][3]->product_id,
            'qty' => 2,
            'unit' => 'Unit',
            'unit_price' => 2000000,
            'subtotal' => 4000000,
        ]);
        
        // Hanya buat stock movement untuk item pertama
        StockMovement::create([
            'company_id' => $this->company->company_id,
            'product_id' => $products['stock'][0]->product_id,
            'movement_type' => 'out',
            'quantity' => 3,
            'unit_cost' => 8000000,
            'reference_type' => 'delivery_note',
            'reference_id' => $sj4->sj_id,
            'notes' => 'Auto generated from delivery note (partial)',
            'created_by' => $this->user->id,
        ]);
        // ⚠️ Item kedua (Monitor) TIDAK ada stock movement - akan muncul di anomaly
        
        // Completed SJ with catalog product (no stock impact)
        $sj5 = DeliveryNote::create([
            'company_id' => $this->company->company_id,
            'customer_id' => $customers[0]->customer_id,
            'sj_number' => 'SJ/2025/10/005',
            'type' => 'PPN',
            'delivery_date' => now()->subDays(7),
            'status' => 'Completed',
            'notes' => 'Service delivery - no stock tracking',
            'created_by' => $this->user->id,
        ]);
        
        DeliveryNoteItem::create([
            'sj_id' => $sj5->sj_id,
            'product_id' => $products['catalog'][0]->product_id,
            'qty' => 8,
            'unit' => 'Hour',
            'unit_price' => 500000,
            'subtotal' => 4000000,
        ]);
    }
    
    private function createInvoices(array $customers, array $products): void
    {
        $this->command->info('Creating invoices...');
        
        // Invoice 1: Unpaid invoice
        $inv1 = Invoice::create([
            'company_id' => $this->company->company_id,
            'customer_id' => $customers[0]->customer_id,
            'invoice_number' => 'INV/2025/10/001',
            'type' => 'PPN',
            'invoice_date' => now()->subDays(15),
            'due_date' => now()->addDays(15),
            'total_amount' => 10000000,
            'ppn_amount' => 1100000,
            'grand_total' => 11100000,
            'status' => 'Unpaid',
            'notes' => 'Unpaid invoice',
            'created_by' => $this->user->id,
        ]);
        
        InvoiceItem::create([
            'invoice_id' => $inv1->invoice_id,
            'product_id' => $products['stock'][0]->product_id,
            'qty' => 1,
            'unit' => 'Unit',
            'unit_price' => 10000000,
            'discount_percent' => 0,
            'subtotal' => 10000000,
        ]);
        
        // Invoice 2: Partially paid invoice
        $inv2 = Invoice::create([
            'company_id' => $this->company->company_id,
            'customer_id' => $customers[1]->customer_id,
            'invoice_number' => 'INV/2025/10/002',
            'type' => 'Non-PPN',
            'invoice_date' => now()->subDays(10),
            'due_date' => now()->addDays(20),
            'total_amount' => 5000000,
            'ppn_amount' => 0,
            'grand_total' => 5000000,
            'status' => 'Partial',
            'notes' => 'Partially paid',
            'created_by' => $this->user->id,
        ]);
        
        InvoiceItem::create([
            'invoice_id' => $inv2->invoice_id,
            'product_id' => $products['stock'][1]->product_id,
            'qty' => 20,
            'unit_price' => 250000,
            'discount_percent' => 0,
            'subtotal' => 5000000,
        ]);
        
        Payment::create([
            'company_id' => $this->company->company_id,
            'invoice_id' => $inv2->invoice_id,
            'customer_id' => $customers[1]->customer_id,
            'payment_date' => now()->subDays(5),
            'amount' => 2500000,
            'payment_method' => 'Transfer',
            'reference_number' => 'TRF-001',
            'notes' => 'Pembayaran sebagian 50%',
            'created_by' => $this->user->id,
        ]);
        
        // Invoice 3: Paid invoice
        $inv3 = Invoice::create([
            'company_id' => $this->company->company_id,
            'customer_id' => $customers[0]->customer_id,
            'invoice_number' => 'INV/2025/10/003',
            'type' => 'PPN',
            'invoice_date' => now()->subDays(20),
            'due_date' => now()->subDays(5),
            'total_amount' => 3000000,
            'ppn_amount' => 330000,
            'grand_total' => 3330000,
            'status' => 'Paid',
            'notes' => 'Paid invoice - Lunas',
            'created_by' => $this->user->id,
        ]);
        
        InvoiceItem::create([
            'invoice_id' => $inv3->invoice_id,
            'product_id' => $products['stock'][2]->product_id,
            'qty' => 4,
            'unit_price' => 750000,
            'discount_percent' => 0,
            'subtotal' => 3000000,
        ]);
        
        Payment::create([
            'company_id' => $this->company->company_id,
            'invoice_id' => $inv3->invoice_id,
            'customer_id' => $customers[0]->customer_id,
            'payment_date' => now()->subDays(10),
            'amount' => 3330000,
            'payment_method' => 'Cash',
            'reference_number' => 'CASH-001',
            'notes' => 'Lunas - Cash payment',
            'created_by' => $this->user->id,
        ]);
        
        // Invoice 4: Overdue invoice
        $inv4 = Invoice::create([
            'company_id' => $this->company->company_id,
            'customer_id' => $customers[1]->customer_id,
            'invoice_number' => 'INV/2025/10/004',
            'type' => 'Non-PPN',
            'invoice_date' => now()->subDays(60),
            'due_date' => now()->subDays(30),
            'total_amount' => 7500000,
            'ppn_amount' => 0,
            'grand_total' => 7500000,
            'status' => 'Overdue',
            'notes' => '⚠️ Overdue invoice - 30 hari lewat jatuh tempo',
            'created_by' => $this->user->id,
        ]);
        
        InvoiceItem::create([
            'invoice_id' => $inv4->invoice_id,
            'product_id' => $products['stock'][3]->product_id,
            'qty' => 3,
            'unit_price' => 2000000,
            'discount_percent' => 10,
            'subtotal' => 5400000,
        ]);
        
        InvoiceItem::create([
            'invoice_id' => $inv4->invoice_id,
            'product_id' => $products['catalog'][1]->product_id,
            'qty' => 2,
            'unit_price' => 1000000,
            'discount_percent' => 5,
            'subtotal' => 1900000,
        ]);
        
        // Invoice 5: Multiple payment records
        $inv5 = Invoice::create([
            'company_id' => $this->company->company_id,
            'customer_id' => $customers[0]->customer_id,
            'invoice_number' => 'INV/2025/10/005',
            'type' => 'PPN',
            'invoice_date' => now()->subDays(25),
            'due_date' => now()->addDays(5),
            'total_amount' => 15000000,
            'ppn_amount' => 1650000,
            'grand_total' => 16650000,
            'status' => 'Partial',
            'notes' => 'Invoice with multiple payment installments',
            'created_by' => $this->user->id,
        ]);
        
        InvoiceItem::create([
            'invoice_id' => $inv5->invoice_id,
            'product_id' => $products['stock'][0]->product_id,
            'qty' => 1,
            'unit_price' => 10000000,
            'discount_percent' => 0,
            'subtotal' => 10000000,
        ]);
        
        InvoiceItem::create([
            'invoice_id' => $inv5->invoice_id,
            'product_id' => $products['catalog'][0]->product_id,
            'qty' => 10,
            'unit_price' => 500000,
            'discount_percent' => 0,
            'subtotal' => 5000000,
        ]);
        
        // Multiple payments
        Payment::create([
            'company_id' => $this->company->company_id,
            'invoice_id' => $inv5->invoice_id,
            'customer_id' => $customers[0]->customer_id,
            'payment_date' => now()->subDays(20),
            'amount' => 5000000,
            'payment_method' => 'Transfer',
            'reference_number' => 'TRF-002',
            'notes' => 'Pembayaran ke-1 (30%)',
            'created_by' => $this->user->id,
        ]);
        
        Payment::create([
            'company_id' => $this->company->company_id,
            'invoice_id' => $inv5->invoice_id,
            'customer_id' => $customers[0]->customer_id,
            'payment_date' => now()->subDays(10),
            'amount' => 5000000,
            'payment_method' => 'Transfer',
            'reference_number' => 'TRF-003',
            'notes' => 'Pembayaran ke-2 (30%)',
            'created_by' => $this->user->id,
        ]);
        
        // Masih kurang 6,650,000 (40%)
        
        // Invoice 6: Cancelled invoice
        $inv6 = Invoice::create([
            'company_id' => $this->company->company_id,
            'customer_id' => $customers[1]->customer_id,
            'invoice_number' => 'INV/2025/10/006',
            'type' => 'Non-PPN',
            'invoice_date' => now()->subDays(8),
            'due_date' => now()->addDays(22),
            'total_amount' => 2000000,
            'ppn_amount' => 0,
            'grand_total' => 2000000,
            'status' => 'Cancelled',
            'notes' => '❌ Cancelled invoice - Customer cancel order',
            'created_by' => $this->user->id,
        ]);
        
        InvoiceItem::create([
            'invoice_id' => $inv6->invoice_id,
            'product_id' => $products['stock'][1]->product_id,
            'qty' => 8,
            'unit_price' => 250000,
            'discount_percent' => 0,
            'subtotal' => 2000000,
        ]);
    }
    
    private function createSoftDeletedRecords(array $customers, array $products): void
    {
        $this->command->info('Creating soft deleted records for testing...');
        
        // Create and delete delivery note to test number generation
        $sjDeleted = DeliveryNote::create([
            'company_id' => $this->company->company_id,
            'customer_id' => $customers[0]->customer_id,
            'sj_number' => 'SJ/2025/10/099',
            'type' => 'PPN',
            'delivery_date' => now(),
            'status' => 'Draft',
            'notes' => 'To be deleted',
            'created_by' => $this->user->id,
        ]);
        $sjDeleted->delete(); // Soft delete
        
        // Create and delete invoice
        $invDeleted = Invoice::create([
            'company_id' => $this->company->company_id,
            'customer_id' => $customers[0]->customer_id,
            'invoice_number' => 'INV/2025/10/099',
            'type' => 'PPN',
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'total_amount' => 1000000,
            'ppn_amount' => 110000,
            'grand_total' => 1110000,
            'status' => 'unpaid',
            'notes' => 'To be deleted',
            'created_by' => $this->user->id,
        ]);
        $invDeleted->delete(); // Soft delete
        
        $this->command->info('✅ Soft deleted records created (SJ/2025/10/099, INV/2025/10/099)');
    }
}
