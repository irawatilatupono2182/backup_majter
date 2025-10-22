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

/**
 * Comprehensive Test Seeder - Disesuaikan dengan SEMUA migration
 * 
 * Struktur yang digunakan berdasarkan migration files:
 * - Companies: code, name, address, phone, email, npwp, logo_url
 * - Users: username, name, email, password, phone, is_active
 * - Customers: customer_code, name, contact_person, address_ship_to, address_bill_to, npwp, billing_schedule, is_ppn, phone, email, is_active
 * - Suppliers: supplier_code, name, type (Local/Import), address, phone, email, contact_person, is_active
 * - Products: product_code, name, description, unit, base_price, default_discount_percent, min_stock_alert, category, product_type (STOCK/CATALOG), is_active
 * - Stocks: batch_number, quantity, reserved_quantity, available_quantity, minimum_stock, unit_cost, expiry_date, location, notes
 * - PriceQuotations: entity_type, entity_id, customer_id, supplier_id, quotation_number, type, quotation_date, valid_until, status, notes
 * - PriceQuotationItems: qty, unit, unit_price, discount_percent, subtotal
 * - PurchaseOrders: ph_id, type, order_date, expected_delivery, status, notes
 * - PurchaseOrderItems: qty_ordered, qty_received, unit, unit_price, discount_percent, subtotal
 * - DeliveryNotes: type, delivery_date, status, notes
 * - DeliveryNoteItems: qty, unit, unit_price, subtotal
 * - Invoices: sj_id, type, invoice_date, due_date, total_amount, ppn_amount, grand_total, status, notes
 * - InvoiceItems: qty, unit, unit_price, discount_percent, subtotal
 * - StockMovements: movement_type (in/out/adjustment), quantity, unit_cost, reference_type, reference_id, batch_number, expiry_date, notes
 */
class ComprehensiveTestSeederFixed extends Seeder
{
    private $company;
    private $user;
    
    public function run(): void
    {
        $this->command->info('🌱 Starting Comprehensive Test Seeder (Fixed Version)...');
        
        // 1. Company & User
        $this->createCompanyAndUser();
        
        // 2. Customers
        $customers = $this->createCustomers();
        
        // 3. Suppliers
        $suppliers = $this->createSuppliers();
        
        // 4. Products
        $products = $this->createProducts();
        
        // 5. Price Quotations (dengan entity_type support)
        $this->createPriceQuotations($customers, $suppliers, $products);
        
        // 6. Initial Stock
        $this->createInitialStock($products);
        
        // 7. Purchase Orders
        $this->createPurchaseOrders($suppliers, $products);
        
        // 8. Stock Movements
        $this->createStockMovements($products);
        
        // 9. Delivery Notes (dengan anomali)
        $this->createDeliveryNotes($customers, $products);
        
        // 10. Invoices (berbagai status)
        $this->createInvoices($customers, $products);
        
        // 11. Soft Deleted Records
        $this->createSoftDeletedRecords($customers, $products);
        
        $this->command->info('✅ Comprehensive Test Seeder completed successfully!');
        $this->command->info('📊 Summary:');
        $this->command->info('   - 3 Customers (2 active, 1 inactive)');
        $this->command->info('   - 2 Suppliers');
        $this->command->info('   - 6 Products (2 CATALOG, 4 STOCK)');
        $this->command->info('   - 3 Price Quotations (2 untuk supplier, 1 untuk customer)');
        $this->command->info('   - 5 Stock records (dengan low stock & expiring)');
        $this->command->info('   - 2 Purchase Orders');
        $this->command->info('   - 5 Delivery Notes (dengan 2 anomali)');
        $this->command->info('   - 6 Invoices (Unpaid, Partial, Paid, Overdue, Multiple Payment, Cancelled)');
        $this->command->info('   - 2 Soft deleted records (untuk test duplicate prevention)');
    }
    
    private function createCompanyAndUser()
    {
        $this->command->info('Creating company and user...');
        
        $this->company = Company::firstOrCreate(
            ['company_id' => '01234567-89ab-cdef-0123-456789abcdef'],
            [
                'code' => 'TEST001',
                'name' => 'PT Test Company',
                'address' => 'Jl. Test No. 123, Jakarta Pusat',
                'phone' => '021-1234567',
                'email' => 'test@company.com',
                'npwp' => '01.234.567.8-901.000',
            ]
        );
        
        $this->user = User::firstOrCreate(
            ['email' => 'admin@test.com'],
            [
                'username' => 'admin',
                'name' => 'Admin Test',
                'password' => Hash::make('password'),
                'phone' => '081234567890',
                'is_active' => true,
            ]
        );
        
        session(['selected_company_id' => $this->company->company_id]);
    }
    
    private function createCustomers(): array
    {
        $this->command->info('Creating customers...');
        
        return [
            Customer::create([
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
            ]),
            Customer::create([
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
            ]),
            Customer::create([
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
            ]),
        ];
    }
    
    private function createSuppliers(): array
    {
        $this->command->info('Creating suppliers...');
        
        return [
            Supplier::create([
                'company_id' => $this->company->company_id,
                'supplier_code' => 'SUPP-001',
                'name' => 'PT Supplier Utama',
                'type' => 'Local',
                'email' => 'supplier1@email.com',
                'phone' => '021-444444',
                'address' => 'Jl. Gatot Subroto No. 88, Jakarta Selatan',
                'contact_person' => 'Ahmad Yani',
                'is_active' => true,
            ]),
            Supplier::create([
                'company_id' => $this->company->company_id,
                'supplier_code' => 'SUPP-002',
                'name' => 'CV Supplier Kedua',
                'type' => 'Local',
                'email' => 'supplier2@email.com',
                'phone' => '021-555555',
                'address' => 'Jl. Raya Serpong No. 12, Tangerang',
                'contact_person' => 'Siti Aminah',
                'is_active' => true,
            ]),
        ];
    }
    
    private function createProducts(): array
    {
        $this->command->info('Creating products...');
        
        $products = ['catalog' => [], 'stock' => []];
        
        // CATALOG Products
        $products['catalog'][] = Product::create([
            'company_id' => $this->company->company_id,
            'product_code' => 'SVC-001',
            'name' => 'Jasa Konsultasi',
            'description' => 'Jasa konsultasi IT per jam',
            'product_type' => 'CATALOG',
            'unit' => 'Hour',
            'base_price' => 500000,
            'default_discount_percent' => 0,
            'min_stock_alert' => 0,
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
            'min_stock_alert' => 0,
            'category' => 'Services',
            'is_active' => true,
        ]);
        
        // STOCK Products
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
        
        // PH #1: Untuk Supplier (Purchasing - minta penawaran)
        $ph1 = PriceQuotation::create([
            'company_id' => $this->company->company_id,
            'entity_type' => 'supplier',
            'entity_id' => $suppliers[0]->supplier_id,
            'supplier_id' => $suppliers[0]->supplier_id,
            'quotation_number' => 'PH/2025/10/001',
            'type' => 'PPN',
            'quotation_date' => now()->subDays(15),
            'valid_until' => now()->addDays(15),
            'status' => 'Accepted',
            'notes' => 'Request quotation untuk stock keyboard dan mouse',
            'created_by' => $this->user->id,
        ]);
        
        PriceQuotationItem::create([
            'ph_id' => $ph1->ph_id,
            'product_id' => $products['stock'][2]->product_id,
            'qty' => 50,
            'unit' => 'Unit',
            'unit_price' => 500000,
            'discount_percent' => 10,
            'subtotal' => 22500000,
        ]);
        
        PriceQuotationItem::create([
            'ph_id' => $ph1->ph_id,
            'product_id' => $products['stock'][1]->product_id,
            'qty' => 100,
            'unit' => 'Unit',
            'unit_price' => 150000,
            'discount_percent' => 5,
            'subtotal' => 14250000,
        ]);
        
        // PH #2: Untuk Supplier - Draft
        $ph2 = PriceQuotation::create([
            'company_id' => $this->company->company_id,
            'entity_type' => 'supplier',
            'entity_id' => $suppliers[1]->supplier_id,
            'supplier_id' => $suppliers[1]->supplier_id,
            'quotation_number' => 'PH/2025/10/002',
            'type' => 'Non-PPN',
            'quotation_date' => now()->subDays(5),
            'valid_until' => now()->addDays(25),
            'status' => 'Draft',
            'notes' => 'Draft quotation untuk laptop',
            'created_by' => $this->user->id,
        ]);
        
        PriceQuotationItem::create([
            'ph_id' => $ph2->ph_id,
            'product_id' => $products['stock'][0]->product_id,
            'qty' => 10,
            'unit' => 'Unit',
            'unit_price' => 8000000,
            'discount_percent' => 0,
            'subtotal' => 80000000,
        ]);
        
        // PH #3: Untuk Customer (Sales - penawaran jual)
        $ph3 = PriceQuotation::create([
            'company_id' => $this->company->company_id,
            'entity_type' => 'customer',
            'entity_id' => $customers[0]->customer_id,
            'customer_id' => $customers[0]->customer_id,
            'quotation_number' => 'PH/2025/10/003',
            'type' => 'PPN',
            'quotation_date' => now()->subDays(20),
            'valid_until' => now()->addDays(10),
            'status' => 'Sent',
            'notes' => 'Penawaran laptop ke PT Maju Jaya',
            'created_by' => $this->user->id,
        ]);
        
        PriceQuotationItem::create([
            'ph_id' => $ph3->ph_id,
            'product_id' => $products['stock'][0]->product_id,
            'qty' => 2,
            'unit' => 'Unit',
            'unit_price' => 10000000,
            'discount_percent' => 0,
            'subtotal' => 20000000,
        ]);
    }
    
    private function createInitialStock(array $products): void
    {
        $this->command->info('Creating initial stock...');
        
        Stock::create([
            'company_id' => $this->company->company_id,
            'product_id' => $products['stock'][0]->product_id,
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
            'product_id' => $products['stock'][1]->product_id,
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
            'product_id' => $products['stock'][2]->product_id,
            'batch_number' => 'BATCH-003',
            'quantity' => 75,
            'available_quantity' => 75,
            'reserved_quantity' => 0,
            'minimum_stock' => 15,
            'unit_cost' => 500000,
            'location' => 'Gudang B',
        ]);
        
        // Low stock (untuk testing alert)
        Stock::create([
            'company_id' => $this->company->company_id,
            'product_id' => $products['stock'][3]->product_id,
            'batch_number' => 'BATCH-004',
            'quantity' => 5,
            'available_quantity' => 5,
            'reserved_quantity' => 0,
            'minimum_stock' => 10,
            'unit_cost' => 1500000,
            'location' => 'Gudang A',
        ]);
        
        // Stock dengan expiry date
        Stock::create([
            'company_id' => $this->company->company_id,
            'product_id' => $products['stock'][4]->product_id,
            'batch_number' => 'BATCH-EXP-001',
            'quantity' => 30,
            'available_quantity' => 30,
            'reserved_quantity' => 0,
            'minimum_stock' => 10,
            'unit_cost' => 200000,
            'expiry_date' => now()->addDays(25),
            'location' => 'Gudang C',
        ]);
    }
    
    private function createPurchaseOrders(array $suppliers, array $products): void
    {
        $this->command->info('Creating purchase orders...');
        
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
            'qty_received' => 0,
            'unit' => 'Unit',
            'unit_price' => 8000000,
            'discount_percent' => 0,
            'subtotal' => 80000000,
        ]);
        
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
            'qty_received' => 0,
            'unit' => 'Unit',
            'unit_price' => 150000,
            'discount_percent' => 0,
            'subtotal' => 15000000,
        ]);
    }
    
    private function createStockMovements(array $products): void
    {
        $this->command->info('Creating stock movements...');
        
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
        
        // SJ #1: Draft
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
        
        // SJ #2: Sent dengan stock movement (normal)
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
        
        DeliveryNoteItem::create([
            'sj_id' => $sj2->sj_id,
            'product_id' => $products['stock'][1]->product_id,
            'qty' => 10,
            'unit' => 'Unit',
            'unit_price' => 250000,
            'subtotal' => 2500000,
        ]);
        
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
        
        // SJ #3: ANOMALI - Sent tanpa stock movement
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
        
        // SJ #4: ANOMALI - Completed dengan sebagian stock movement
        $sj4 = DeliveryNote::create([
            'company_id' => $this->company->company_id,
            'customer_id' => $customers[1]->customer_id,
            'sj_number' => 'SJ/2025/10/004',
            'type' => 'PPN',
            'delivery_date' => now()->subDays(5),
            'status' => 'Completed',
            'notes' => '⚠️ ANOMALI - 2 item, hanya 1 yang ada stock movement',
            'created_by' => $this->user->id,
        ]);
        
        DeliveryNoteItem::create([
            'sj_id' => $sj4->sj_id,
            'product_id' => $products['stock'][0]->product_id,
            'qty' => 3,
            'unit' => 'Unit',
            'unit_price' => 10000000,
            'subtotal' => 30000000,
        ]);
        
        DeliveryNoteItem::create([
            'sj_id' => $sj4->sj_id,
            'product_id' => $products['stock'][3]->product_id,
            'qty' => 2,
            'unit' => 'Unit',
            'unit_price' => 2000000,
            'subtotal' => 4000000,
        ]);
        
        // Hanya item pertama yang ada stock movement
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
        
        // SJ #5: Completed dengan CATALOG product
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
        
        // Invoice #1: Unpaid
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
        
        // Invoice #2: Partial
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
            'unit' => 'Unit',
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
            'notes' => 'Pembayaran 50%',
            'created_by' => $this->user->id,
        ]);
        
        // Invoice #3: Paid
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
            'notes' => 'Lunas',
            'created_by' => $this->user->id,
        ]);
        
        InvoiceItem::create([
            'invoice_id' => $inv3->invoice_id,
            'product_id' => $products['stock'][2]->product_id,
            'qty' => 4,
            'unit' => 'Unit',
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
            'notes' => 'Lunas',
            'created_by' => $this->user->id,
        ]);
        
        // Invoice #4: Overdue
        $inv4 = Invoice::create([
            'company_id' => $this->company->company_id,
            'customer_id' => $customers[1]->customer_id,
            'invoice_number' => 'INV/2025/10/004',
            'type' => 'Non-PPN',
            'invoice_date' => now()->subDays(60),
            'due_date' => now()->subDays(30),
            'total_amount' => 7300000,
            'ppn_amount' => 0,
            'grand_total' => 7300000,
            'status' => 'Overdue',
            'notes' => '⚠️ Overdue 30 hari',
            'created_by' => $this->user->id,
        ]);
        
        InvoiceItem::create([
            'invoice_id' => $inv4->invoice_id,
            'product_id' => $products['stock'][3]->product_id,
            'qty' => 3,
            'unit' => 'Unit',
            'unit_price' => 2000000,
            'discount_percent' => 10,
            'subtotal' => 5400000,
        ]);
        
        InvoiceItem::create([
            'invoice_id' => $inv4->invoice_id,
            'product_id' => $products['catalog'][1]->product_id,
            'qty' => 2,
            'unit' => 'Set',
            'unit_price' => 1000000,
            'discount_percent' => 5,
            'subtotal' => 1900000,
        ]);
        
        // Invoice #5: Multiple payments
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
            'notes' => 'Multiple payment installments',
            'created_by' => $this->user->id,
        ]);
        
        InvoiceItem::create([
            'invoice_id' => $inv5->invoice_id,
            'product_id' => $products['stock'][0]->product_id,
            'qty' => 1,
            'unit' => 'Unit',
            'unit_price' => 10000000,
            'discount_percent' => 0,
            'subtotal' => 10000000,
        ]);
        
        InvoiceItem::create([
            'invoice_id' => $inv5->invoice_id,
            'product_id' => $products['catalog'][0]->product_id,
            'qty' => 10,
            'unit' => 'Hour',
            'unit_price' => 500000,
            'discount_percent' => 0,
            'subtotal' => 5000000,
        ]);
        
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
        
        // Invoice #6: Cancelled
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
            'notes' => '❌ Cancelled - Customer cancel order',
            'created_by' => $this->user->id,
        ]);
        
        InvoiceItem::create([
            'invoice_id' => $inv6->invoice_id,
            'product_id' => $products['stock'][1]->product_id,
            'qty' => 8,
            'unit' => 'Unit',
            'unit_price' => 250000,
            'discount_percent' => 0,
            'subtotal' => 2000000,
        ]);
    }
    
    private function createSoftDeletedRecords(array $customers, array $products): void
    {
        $this->command->info('Creating soft deleted records for testing...');
        
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
        $sjDeleted->delete();
        
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
            'status' => 'Unpaid',
            'notes' => 'To be deleted',
            'created_by' => $this->user->id,
        ]);
        $invDeleted->delete();
        
        $this->command->info('✅ Soft deleted records created');
    }
}
