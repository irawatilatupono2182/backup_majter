# Database Schema Documentation - Si-Majter ERP System

## Overview
Sistem Si-Majter adalah aplikasi ERP (Enterprise Resource Planning) yang dirancang untuk mengelola inventori dan penjualan dengan dukungan multi-perusahaan. Database menggunakan UUID sebagai primary key dan mendukung soft delete untuk semua tabel utama.

## Database Flow Diagram

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│    Companies    │    │      Users      │    │  Permissions/   │
│                 │    │                 │    │     Roles       │
│ - company_id    │    │ - id            │    │                 │
│ - name          │    │ - username      │    │ - name          │
│ - code          │    │ - name          │    │ - guard_name    │
│ - address       │    │ - email         │    │                 │
│ - phone         │    │ - phone         │    └─────────────────┘
│ - email         │    │ - password      │              │
│ - tax_number    │    │ - is_active     │              │
│ - is_active     │    └─────────────────┘              │
└─────────────────┘             │                       │
         │                      │                       │
         │                      ▼                       ▼
         │            ┌─────────────────┐    ┌─────────────────┐
         │            │UserCompanyRoles │    │  model_has_     │
         │            │                 │    │  permissions/   │
         │            │ - user_id       │    │     roles       │
         │            │ - company_id    │    │                 │
         │            │ - role          │    └─────────────────┘
         │            │ - is_default    │
         │            └─────────────────┘
         │
         ▼
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│    Customers    │    │    Suppliers    │    │    Products     │
│                 │    │                 │    │                 │
│ - customer_id   │    │ - supplier_id   │    │ - product_id    │
│ - company_id    │    │ - company_id    │    │ - company_id    │
│ - name          │    │ - name          │    │ - name          │
│ - email         │    │ - email         │    │ - code          │
│ - phone         │    │ - phone         │    │ - description   │
│ - address       │    │ - address       │    │ - category      │
│ - city          │    │ - city          │    │ - unit          │
│ - postal_code   │    │ - postal_code   │    │ - purchase_price│
│ - tax_number    │    │ - tax_number    │    │ - sale_price    │
│ - credit_limit  │    │ - payment_terms │    │ - min_stock     │
│ - payment_terms │    │ - is_active     │    │ - max_stock     │
│ - is_active     │    └─────────────────┘    │ - product_type  │
└─────────────────┘             │             │ - is_active     │
         │                      │             └─────────────────┘
         │                      │                      │
         ▼                      ▼                      ▼
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│ Price Quotation │    │ Purchase Order  │    │     Stocks      │
│                 │    │                 │    │                 │
│ - quotation_id  │    │ - po_id         │    │ - stock_id      │
│ - company_id    │    │ - company_id    │    │ - company_id    │
│ - customer_id   │    │ - supplier_id   │    │ - product_id    │
│ - quotation_no  │    │ - po_number     │    │ - batch_number  │
│ - quotation_date│    │ - order_date    │    │ - quantity      │
│ - valid_until   │    │ - expected_date │    │ - unit_cost     │
│ - subtotal      │    │ - subtotal      │    │ - expiry_date   │
│ - tax_amount    │    │ - tax_amount    │    │ - purchase_date │
│ - total_amount  │    │ - total_amount  │    │ - is_active     │
│ - status        │    │ - status        │    └─────────────────┘
│ - notes         │    │ - notes         │             │
└─────────────────┘    └─────────────────┘             │
         │                      │                      ▼
         │                      │             ┌─────────────────┐
         ▼                      ▼             │ Stock Movements │
┌─────────────────┐    ┌─────────────────┐    │                 │
│PriceQuotationItem│   │PurchaseOrderItem│    │ - movement_id   │
│                 │    │                 │    │ - company_id    │
│ - quotation_id  │    │ - po_id         │    │ - product_id    │
│ - product_id    │    │ - product_id    │    │ - movement_type │
│ - quantity      │    │ - quantity      │    │ - quantity      │
│ - unit_price    │    │ - unit_price    │    │ - unit_cost     │
│ - total_price   │    │ - total_price   │    │ - reference_type│
│ - notes         │    │ - notes         │    │ - reference_id  │
└─────────────────┘    └─────────────────┘    │ - batch_number  │
                                             │ - expiry_date   │
                                             │ - notes         │
                                             └─────────────────┘
         │                      │
         ▼                      ▼
┌─────────────────┐    ┌─────────────────┐
│ Delivery Notes  │    │    Invoices     │
│                 │    │                 │
│ - delivery_id   │    │ - invoice_id    │
│ - company_id    │    │ - company_id    │
│ - customer_id   │    │ - customer_id   │
│ - sj_number     │    │ - invoice_number│
│ - delivery_date │    │ - invoice_date  │
│ - subtotal      │    │ - due_date      │
│ - tax_amount    │    │ - subtotal      │
│ - total_amount  │    │ - tax_amount    │
│ - status        │    │ - total_amount  │
│ - notes         │    │ - paid_amount   │
└─────────────────┘    │ - status        │
         │             │ - notes         │
         │             └─────────────────┘
         ▼                      │
┌─────────────────┐             ▼
│DeliveryNoteItems│    ┌─────────────────┐
│                 │    │  Invoice Items  │
│ - delivery_id   │    │                 │
│ - product_id    │    │ - invoice_id    │
│ - quantity      │    │ - product_id    │
│ - unit_price    │    │ - quantity      │
│ - total_price   │    │ - unit_price    │
│ - notes         │    │ - total_price   │
└─────────────────┘    │ - notes         │
                       └─────────────────┘
                                │
                                ▼
                       ┌─────────────────┐
                       │    Payments     │
                       │                 │
                       │ - payment_id    │
                       │ - company_id    │
                       │ - invoice_id    │
                       │ - payment_date  │
                       │ - amount        │
                       │ - payment_method│
                       │ - reference_no  │
                       │ - notes         │
                       └─────────────────┘
```

## Business Flow Process

### 1. Setup Awal (Initial Setup)
```
Companies → Users → UserCompanyRoles → Permissions/Roles
```

### 2. Master Data Management
```
Products ← Entry produk yang akan dijual/dibeli
Customers ← Entry data pelanggan
Suppliers ← Entry data pemasok
```

### 3. Purchasing Flow (Alur Pembelian)
```
1. Price Quotation (PH) - Permintaan Harga
   ↓
2. Purchase Order (PO) - Order Pembelian
   ↓
3. Goods Receipt (SP) - Penerimaan Barang
   ↓ (Automatic)
4. Stock In - Penambahan Stok
   ↓ (Automatic)
5. Stock Movement Record - Pencatatan Pergerakan Stok
```

### 4. Sales Flow (Alur Penjualan)
```
1. Delivery Note (SJ) - Surat Jalan
   ↓ (Automatic)
2. Stock Out - Pengurangan Stok
   ↓ (Automatic)
3. Stock Movement Record - Pencatatan Pergerakan Stok
   ↓
4. Invoice - Faktur Penjualan
   ↓
5. Payment - Pembayaran
```

### 5. Inventory Management (Pengelolaan Stok)
```
Stocks ←→ Stock Movements (Bidirectional relationship)
   ↑
   └── Triggers dari Purchase/Sales processes
```

## Table Relationships

### Core Relationships
- **Companies** (1) → (N) **Users** (via UserCompanyRoles)
- **Companies** (1) → (N) **Customers**
- **Companies** (1) → (N) **Suppliers**
- **Companies** (1) → (N) **Products**

### Transaction Relationships
- **Customers** (1) → (N) **PriceQuotations**
- **Suppliers** (1) → (N) **PurchaseOrders**
- **PriceQuotations** (1) → (N) **PriceQuotationItems**
- **PurchaseOrders** (1) → (N) **PurchaseOrderItems**

### Sales Relationships
- **Customers** (1) → (N) **DeliveryNotes**
- **Customers** (1) → (N) **Invoices**
- **DeliveryNotes** (1) → (N) **DeliveryNoteItems**
- **Invoices** (1) → (N) **InvoiceItems**
- **Invoices** (1) → (N) **Payments**

### Inventory Relationships
- **Products** (1) → (N) **Stocks**
- **Products** (1) → (N) **StockMovements**
- **Stocks** (1) → (N) **StockMovements** (Optional reference)

## Key Features

### 1. Multi-Company Support
- Setiap transaksi terisolasi per company
- User dapat memiliki akses ke multiple companies
- Data separation yang ketat antar perusahaan

### 2. Role-Based Access Control (RBAC)
- **Super Admin**: Full access ke semua fitur
- **Admin**: Manajemen company-specific
- **Finance**: Fokus pada invoice, payment, reporting
- **Warehouse**: Fokus pada inventory, delivery
- **Viewer**: Read-only access

### 3. Stock Management
- FIFO (First In, First Out) method
- Batch tracking dengan expiry date
- Automatic stock movements dari transaksi
- Minimum/maximum stock alerts

### 4. Document Management
- Auto-generated document numbers
- PDF generation untuk semua dokumen
- Status tracking untuk setiap dokumen

### 5. Financial Integration
- Credit limit tracking per customer
- Payment terms management
- Tax calculation (PPN)
- Outstanding invoice tracking

## Performance Considerations

### Indexes
Database telah dioptimasi dengan indexes pada:
- company_id (isolation)
- Foreign key relationships
- Date fields (untuk reporting)
- Status fields (untuk filtering)
- Document numbers (untuk searching)

### Soft Deletes
Semua tabel menggunakan soft delete untuk:
- Data integrity maintenance
- Audit trail purposes
- Recovery capabilities

### UUID Primary Keys
- Better security (tidak predictable)
- Distributed system friendly
- Menghindari collision antar companies

## Migration Sequence
1. `create_companies_table`
2. `create_users_table`
3. `create_user_company_roles_table`
4. `create_customers_table`
5. `create_suppliers_table`
6. `create_products_table`
7. `create_price_quotations_table`
8. `create_price_quotation_items_table`
9. `create_purchase_orders_table`
10. `create_purchase_order_items_table`
11. `create_delivery_notes_table`
12. `create_delivery_note_items_table`
13. `create_invoices_table`
14. `create_invoice_items_table`
15. `create_payments_table`
16. `create_stocks_table`
17. `create_stock_movements_table`
18. `create_permission_tables` (Spatie)

## Seeder Sequence
1. `PermissionSeeder` - Roles dan permissions
2. `CompanySeeder` - Demo companies
3. `UserSeeder` - Demo users dengan roles
4. `CustomerSeeder` - Demo customers
5. `SupplierSeeder` - Demo suppliers
6. `ProductSeeder` - Demo products
7. `TransactionSeeder` - Demo transactions (optional)

---

*Dokumentasi ini mencakup seluruh skema database dan flow bisnis dari sistem Si-Majter ERP.*