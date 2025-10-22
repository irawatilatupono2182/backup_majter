# 📋 BUSINESS FLOW DOCUMENTATION - END TO END
## Adam Jaya ERP System - Complete Business Scenarios

**Document Version**: 2.1  
**Last Updated**: 22 Oktober 2025  
**Status**: Production Guide  
**Purpose**: Panduan operasional lengkap untuk semua scenario bisnis

**⚠️ IMPORTANT UPDATE:**
- **PH (Price Quotation)** sekarang support **2 ARAH**:
  - 📤 **Untuk Customer** (Sales - Penawaran dari kita ke customer)
  - 📥 **Untuk Supplier** (Purchasing - Minta penawaran dari supplier)

---

# 📚 TABLE OF CONTENTS

1. [PH (PRICE QUOTATION) - 2 Arah System](#ph-system)
   - PH untuk Customer (Sales Flow)
   - PH untuk Supplier (Purchasing Flow)

2. [SALES FLOWS - Customer Order to Payment](#sales-flows)
   - Case 1: Customer Order Normal (Stock Tersedia)
   - Case 2: Customer Order (Stock Tidak Cukup)
   - Case 3: Customer Order Produk Catalog
   - Case 4: Customer Order dengan Partial Payment
   - Case 5: Customer Order Urgent (Same Day Delivery)
   - Case 6: Customer Return/Komplain

3. [PURCHASING FLOWS - Supplier Order to Receiving](#purchasing-flows)
   - Case 7: Pembelian Normal dengan PH & PO
   - Case 8: Pembelian Urgent (Skip PH)
   - Case 9: Pembelian dengan Barang Rusak
   - Case 10: Restock Otomatis (Min Stock Alert)

4. [INVENTORY FLOWS - Stock Management](#inventory-flows)
   - Case 11: Stock Opname (Perhitungan Fisik)
   - Case 12: Stock Adjustment (Koreksi)
   - Case 13: Barang Expired/Damaged
   - Case 14: Transfer Stock Antar Lokasi

5. [EXCEPTIONAL FLOWS - Error Handling](#exceptional-flows)
   - Case 15: Warehouse Lupa Catat Stock Movement
   - Case 16: Invoice Salah Input
   - Case 17: Payment Dobel Entry
   - Case 18: Customer Batalkan Order

6. [REPORTING & MONITORING](#reporting-monitoring)
   - Daily Operations Checklist
   - Monthly Closing Process
   - Audit & Reconciliation

---

# 📋 PH (PRICE QUOTATION) - 2 ARAH SYSTEM {#ph-system}

## **KONSEP: PH Dual-Purpose**

**PH (Price Quotation)** di sistem Adam Jaya dapat digunakan untuk **2 arah**:

### **📤 PH UNTUK CUSTOMER (Sales Flow)**

**Fungsi:**
- PT Adam Jaya memberikan penawaran harga ke **CUSTOMER**
- Dibuat oleh: **Sales Staff**
- Tujuan: Menawarkan produk dengan harga tertentu
- Navigation Group: **Sales** atau **Purchasing** (mixed)

**Flow:**
```
Customer Inquiry → Sales Buat PH → Send ke Customer → 
Customer Approved → Buat SJ → Invoice → Payment
```

**Contoh:**
```
Customer: "Berapa harga Laptop HP 15 untuk 10 unit?"
Sales: Buat PH dengan entity_type = 'customer'
       - Customer: PT Maju Jaya
       - Items: Laptop HP 15 - 10 unit @ Rp 11.000.000
       - Total: Rp 110 juta + PPN
       - Valid: 30 hari
```

**Database:**
```sql
INSERT INTO price_quotations (
    entity_type = 'customer',  -- ⚠️ Customer!
    entity_id = 'customer-uuid',
    customer_id = 'customer-uuid',
    supplier_id = NULL,
    ...
);
```

---

### **📥 PH UNTUK SUPPLIER (Purchasing Flow)**

**Fungsi:**
- PT Adam Jaya meminta penawaran harga dari **SUPPLIER**
- Dibuat oleh: **Purchasing Staff**
- Tujuan: Mendapatkan harga terbaik sebelum buat PO
- Navigation Group: **Purchasing**

**Flow:**
```
Low Stock Alert → Purchasing Buat PH → Send ke Supplier → 
Supplier Reply → Accepted → Buat PO → SP → Stock Bertambah
```

**Contoh:**
```
Purchasing: "Stock Laptop rendah, butuh restock"
Purchasing: Buat PH dengan entity_type = 'supplier'
            - Supplier: PT Distributor Laptop
            - Items: Laptop HP 15 - 20 unit
            - Minta best price
Supplier Reply: "Rp 10 juta/unit, ready 5 hari"
Purchasing: Update PH status = 'Accepted'
            → Buat PO berdasarkan PH
```

**Database:**
```sql
INSERT INTO price_quotations (
    entity_type = 'supplier',  -- ⚠️ Supplier!
    entity_id = 'supplier-uuid',
    customer_id = NULL,
    supplier_id = 'supplier-uuid',
    ...
);
```

---

## **PERBANDINGAN: 2 Arah PH**

| **Aspek** | **PH untuk CUSTOMER** | **PH untuk SUPPLIER** |
|-----------|----------------------|----------------------|
| **Entity Type** | `customer` | `supplier` |
| **Dibuat Oleh** | Sales Staff | Purchasing Staff |
| **Tujuan** | Kasih penawaran (outgoing) | Minta penawaran (incoming) |
| **Badge** | 📤 Customer | 📥 Supplier |
| **Next Step** | SJ (Surat Jalan) | PO (Purchase Order) |
| **Stock Impact** | Tidak ada | Tidak ada |
| **Business Flow** | Sales Flow | Purchasing Flow |
| **Customer/Supplier** | Required customer_id | Required supplier_id |

---

## **CARA PENGGUNAAN DI SISTEM**

### **Step 1: Buat PH Baru**

**Menu:** Purchasing → Penawaran Harga (PH) → New

**Form:**
```
1. Pilih "Tipe Penawaran":
   - 📤 Untuk Customer (Sales - Penawaran Jual)
   - 📥 Untuk Supplier (Purchasing - Minta Penawaran Beli)

2. Jika pilih "Customer":
   → Muncul dropdown "Customer"
   → Pilih customer yang akan diberi penawaran
   
3. Jika pilih "Supplier":
   → Muncul dropdown "Supplier"
   → Pilih supplier yang akan diminta penawaran

4. Input items, harga, dll (sama untuk kedua tipe)

5. Save → PH created
```

---

### **Step 2: Filter & View**

**Table Columns:**
```
| Nomor PH | Tipe | Customer/Supplier | Jenis | Tanggal | Status |
|----------|------|-------------------|-------|---------|--------|
| PH/001   | 📤 Customer | 👤 PT Maju Jaya | PPN | 22 Okt | Sent |
| PH/002   | 📥 Supplier | 🏢 PT Distributor | PPN | 22 Okt | Accepted |
```

**Filters:**
- **Tipe Penawaran**: Customer / Supplier
- **Status**: Draft / Sent / Accepted / Rejected
- **Jenis**: PPN / Non-PPN

---

## **DATABASE SCHEMA UPDATE**

```sql
-- Table: price_quotations (AFTER UPDATE)
CREATE TABLE price_quotations (
    ph_id UUID PRIMARY KEY,
    company_id UUID NOT NULL,
    
    -- Polymorphic relation (NEW!)
    entity_type VARCHAR(50),        -- 'customer' atau 'supplier'
    entity_id UUID,                 -- customer_id atau supplier_id
    
    -- Explicit fields
    customer_id UUID NULLABLE,      -- Jika entity_type = 'customer'
    supplier_id UUID NULLABLE,      -- Jika entity_type = 'supplier'
    
    quotation_number VARCHAR(50),
    type ENUM('PPN', 'Non-PPN'),
    quotation_date DATE,
    valid_until DATE,
    status ENUM('Draft', 'Sent', 'Accepted', 'Rejected'),
    notes TEXT,
    created_by UUID,
    
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id),
    FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id),
    INDEX idx_entity_polymorphic (entity_type, entity_id)
);
```

---

## **MIGRATION NOTES**

**File:** `2024_10_22_000001_add_entity_polymorphic_to_price_quotations_table.php`

**Changes:**
1. ✅ Added `entity_type` column
2. ✅ Added `entity_id` column
3. ✅ Added `customer_id` column
4. ✅ Made `supplier_id` NULLABLE
5. ✅ Migrated existing data (assumed all are for suppliers)

**Migration Command:**
```bash
php artisan migrate
```

**Existing Data Handling:**
```sql
-- All existing PH records will be marked as 'supplier' type
UPDATE price_quotations 
SET entity_type = 'supplier',
    entity_id = supplier_id
WHERE supplier_id IS NOT NULL;
```

---

## **✅ KESIMPULAN**

**PH (Price Quotation) sekarang fleksibel:**
- ✅ Bisa untuk Customer (Sales penawaran jual)
- ✅ Bisa untuk Supplier (Purchasing minta penawaran beli)
- ✅ Terpisah dengan jelas via entity_type
- ✅ Filter & search by tipe
- ✅ Backward compatible (existing data migrated)

**Best Practices:**
1. ✅ Sales Staff → Pilih entity_type = 'customer'
2. ✅ Purchasing Staff → Pilih entity_type = 'supplier'
3. ✅ Selalu isi entity_type sebelum save
4. ✅ System auto sync entity_id dengan customer_id/supplier_id

---

# 🛒 SALES FLOWS - Customer Order to Payment {#sales-flows}

---

## **CASE 1: Customer Order Normal (Stock Tersedia)** ✅

### **Scenario:**
Customer "PT Maju Jaya" kirim email order:
- Laptop HP 15 - Qty: 5 unit
- Mouse Logitech - Qty: 10 unit
- Keyboard Mechanical - Qty: 3 unit

Stock di gudang cukup. Proses normal sampai customer bayar.

---

### **STEP-BY-STEP PROCESS:**

#### **📧 STEP 1: Sales Terima Email Customer**
**Actor:** Sales Staff (Budi)  
**Time:** 09:00 WIB

**Action:**
1. Buka email dari customer
2. Baca detail pesanan
3. Copy list barang ke notepad

**Email Content:**
```
From: purchasing@majujaya.com
Subject: Order Bulanan - Oktober 2025

Kepada Yth,
PT Adam Jaya

Mohon diproses order berikut:
1. Laptop HP 15 - 5 unit
2. Mouse Logitech - 10 unit  
3. Keyboard Mechanical - 3 unit

Kirim ke alamat kantor kami.
Terima kasih.

PIC: Pak Andi
HP: 08123456789
```

---

#### **🖥️ STEP 2: Cek Stock di Sistem**
**Actor:** Sales Staff (Budi)  
**System:** Menu Inventory → Stok Barang

**Action:**
1. Login ke sistem
2. Pilih company: "PT Adam Jaya"
3. Buka menu "Stok Barang"
4. Search "Laptop HP 15"
   - ✅ Stok tersedia: 20 unit
5. Search "Mouse Logitech"
   - ✅ Stok tersedia: 50 unit
6. Search "Keyboard Mechanical"
   - ✅ Stok tersedia: 15 unit

**Decision:** ✅ Semua stock cukup, lanjut proses

---

#### **📝 STEP 3: Buat Surat Jalan (SJ)**
**Actor:** Sales Staff (Budi)  
**System:** Menu Sales → Surat Jalan (SJ) → New

**Action di Sistem:**

**Section 1: Informasi SJ**
```
- Nomor SJ: [Auto] SJ/2025/10/045
- Customer: [Dropdown] PT Maju Jaya
  → System auto isi:
     • U.P.: Pak Andi
     • Alamat SHIP TO: Jl. Sudirman No. 123, Jakarta
     • Jenis: PPN (karena customer.is_ppn = true)
- Tanggal Kirim: 21/10/2025
- Status: Draft
- Catatan: [Optional] Order via email tanggal 21 Okt 2025
```

**Section 2: Items**
```
Item 1:
- Produk: [Dropdown] Laptop HP 15
  → System auto isi:
     • Satuan: unit
     • Harga Satuan: Rp 10.000.000
     • Diskon: 10% (dari product.default_discount_percent)
- Qty: 5
- Subtotal: [Auto] Rp 45.000.000

Item 2:
- Produk: [Dropdown] Mouse Logitech
  → System auto isi:
     • Satuan: pcs
     • Harga Satuan: Rp 150.000
     • Diskon: 0%
- Qty: 10
- Subtotal: [Auto] Rp 1.500.000

Item 3:
- Produk: [Dropdown] Keyboard Mechanical
  → System auto isi:
     • Satuan: unit
     • Harga Satuan: Rp 500.000
     • Diskon: 5%
- Qty: 3
- Subtotal: [Auto] Rp 1.425.000

TOTAL: Rp 47.925.000
```

**Action:**
1. Klik "Save" → SJ tersimpan dengan status "Draft"
2. Review data
3. Klik "Update Status" → Pilih "Sent"
4. Klik "Download PDF"
5. Print SJ (2 copy: 1 untuk warehouse, 1 untuk customer)

**Database Impact:**
```sql
INSERT INTO delivery_notes (
    sj_id = 'uuid-xxx',
    company_id = 'company-uuid',
    customer_id = 'majujaya-uuid',
    sj_number = 'SJ/2025/10/045',
    type = 'PPN',
    delivery_date = '2025-10-21',
    status = 'Sent',
    notes = 'Order via email tanggal 21 Okt 2025',
    created_by = 'budi-user-id'
);

INSERT INTO delivery_note_items (sj_item_id, sj_id, product_id, qty, unit, unit_price, discount_percent, subtotal)
VALUES
    ('item-1-uuid', 'uuid-xxx', 'laptop-uuid', 5, 'unit', 10000000, 10, 45000000),
    ('item-2-uuid', 'uuid-xxx', 'mouse-uuid', 10, 'pcs', 150000, 0, 1500000),
    ('item-3-uuid', 'uuid-xxx', 'keyboard-uuid', 3, 'unit', 500000, 5, 1425000);
```

---

#### **📦 STEP 4: Warehouse Packing & Pengiriman**
**Actor:** Warehouse Staff (Dedi)  
**Time:** 10:00 WIB

**Action:**
1. Terima print SJ dari sales
2. Buka sistem → Menu Inventory → Surat Jalan
3. Cari SJ/2025/10/045
4. Print barcode/picking list (optional)

**Physical Process:**
1. Ambil trolley
2. Ke rak penyimpanan:
   - Ambil 5 unit Laptop HP 15 dari batch terlama (FIFO)
   - Ambil 10 pcs Mouse Logitech
   - Ambil 3 unit Keyboard Mechanical
3. Cek kondisi fisik barang (tidak rusak)
4. Packing dalam kardus
5. Label alamat customer
6. Serahkan ke kurir/driver

**⚠️ CRITICAL: Input Stock Movement**

**Action di Sistem:**

**Menu:** Inventory → Stock Movement → New

**Item 1: Laptop HP 15**
```
- Movement Type: [Dropdown] out (barang keluar)
- Product: [Dropdown] Laptop HP 15
- Quantity: 5
- Reference Type: [Auto] delivery_note_item
- Reference ID: [Link to] SJ/2025/10/045 - Laptop HP 15
- Batch Number: BATCH-2025-001 (pilih batch terlama)
- Notes: Kirim ke PT Maju Jaya sesuai SJ/2025/10/045
- Created By: [Auto] Dedi
```

**Item 2: Mouse Logitech**
```
- Movement Type: out
- Product: Mouse Logitech
- Quantity: 10
- Reference Type: delivery_note_item
- Reference ID: [Link to] SJ/2025/10/045 - Mouse
- Batch Number: BATCH-2025-002
- Notes: Kirim ke PT Maju Jaya sesuai SJ/2025/10/045
```

**Item 3: Keyboard Mechanical**
```
- Movement Type: out
- Product: Keyboard Mechanical
- Quantity: 3
- Reference Type: delivery_note_item
- Reference ID: [Link to] SJ/2025/10/045 - Keyboard
- Batch Number: BATCH-2025-003
- Notes: Kirim ke PT Maju Jaya sesuai SJ/2025/10/045
```

**Action:**
1. Klik "Save All" → Stock movement tercatat
2. Update SJ status → "Completed"
3. Foto barang + SJ → Upload ke sistem (optional)

**Database Impact:**
```sql
-- Stock Movement
INSERT INTO stock_movements (stock_movement_id, product_id, movement_type, quantity, reference_type, reference_id, batch_number, created_by)
VALUES
    ('sm-1', 'laptop-uuid', 'out', 5, 'delivery_note_item', 'item-1-uuid', 'BATCH-2025-001', 'dedi-uuid'),
    ('sm-2', 'mouse-uuid', 'out', 10, 'delivery_note_item', 'item-2-uuid', 'BATCH-2025-002', 'dedi-uuid'),
    ('sm-3', 'keyboard-uuid', 'out', 3, 'delivery_note_item', 'item-3-uuid', 'BATCH-2025-003', 'dedi-uuid');

-- Update Stocks
UPDATE stocks 
SET quantity = quantity - 5,
    available_quantity = available_quantity - 5
WHERE product_id = 'laptop-uuid';

UPDATE stocks 
SET quantity = quantity - 10,
    available_quantity = available_quantity - 10
WHERE product_id = 'mouse-uuid';

UPDATE stocks 
SET quantity = quantity - 3,
    available_quantity = available_quantity - 3
WHERE product_id = 'keyboard-uuid';

-- Update Inventory Batches (FIFO)
UPDATE inventory_batches 
SET remaining_qty = remaining_qty - 5,
    status = CASE WHEN remaining_qty - 5 = 0 THEN 'USED' ELSE 'STOCK' END
WHERE batch_id = 'BATCH-2025-001';

-- Update SJ Status
UPDATE delivery_notes 
SET status = 'Completed',
    updated_at = NOW()
WHERE sj_id = 'uuid-xxx';
```

**Verification:**
```
✅ Stock Movement tercatat (3 records)
✅ Stocks.quantity berkurang
✅ Inventory batch updated (FIFO)
✅ SJ status = Completed
✅ TIDAK muncul di Anomaly Report
```

---

#### **💰 STEP 5: Finance Buat Invoice**
**Actor:** Finance Staff (Siti)  
**Time:** 14:00 WIB (same day atau H+1)

**Action di Sistem:**

**Menu:** Sales → Invoice → New

**Option 1: Auto dari SJ** (Recommended)
```
- Create From: [Dropdown] Surat Jalan
- Pilih SJ: SJ/2025/10/045
- [Klik] Load Items
  → System auto copy semua items dari SJ
```

**Option 2: Manual Input**
```
- Input customer, items manual
```

**Form Invoice:**
```
Section 1: Informasi Invoice
- Nomor Invoice: [Auto] INV/2025/10/123
- Customer: [Auto dari SJ] PT Maju Jaya
- No. SJ: [Link] SJ/2025/10/045
- Jenis: [Auto dari customer] PPN
- Tanggal Invoice: 21/10/2025
- Jatuh Tempo: 21/11/2025 (30 hari dari customer.billing_schedule)

Section 2: Items (Auto copy dari SJ)
- Laptop HP 15: 5 × Rp 10.000.000 (-10%) = Rp 45.000.000
- Mouse Logitech: 10 × Rp 150.000 = Rp 1.500.000
- Keyboard Mechanical: 3 × Rp 500.000 (-5%) = Rp 1.425.000

Section 3: Calculation
- Subtotal: Rp 47.925.000
- PPN 11%: Rp 5.271.750
- Grand Total: Rp 53.196.750

Section 4: Notes
- Payment to: Bank BCA 1234567890 a/n PT Adam Jaya
- Due date: 21 November 2025
```

**Action:**
1. Review semua data
2. Klik "Save" → Invoice created
3. Klik "Download PDF"
4. Print invoice
5. Send via email ke customer

**Email to Customer:**
```
To: purchasing@majujaya.com
Subject: Invoice INV/2025/10/123 - PT Maju Jaya

Dear Pak Andi,

Terlampir invoice untuk pengiriman tanggal 21 Oktober 2025.

Invoice Number: INV/2025/10/123
Amount: Rp 53.196.750
Due Date: 21 November 2025

Payment to:
Bank BCA 1234567890
a/n PT Adam Jaya

Terima kasih.

Best regards,
Finance Team
PT Adam Jaya
```

**Database Impact:**
```sql
INSERT INTO invoices (
    invoice_id = 'inv-uuid',
    company_id = 'company-uuid',
    customer_id = 'majujaya-uuid',
    sj_id = 'uuid-xxx',
    invoice_number = 'INV/2025/10/123',
    type = 'PPN',
    invoice_date = '2025-10-21',
    due_date = '2025-11-21',
    total_amount = 47925000,
    ppn_amount = 5271750,
    grand_total = 53196750,
    status = 'Unpaid',
    created_by = 'siti-uuid'
);

INSERT INTO invoice_items (invoice_item_id, invoice_id, product_id, qty, unit, unit_price, discount_percent, subtotal)
SELECT 
    UUID(),
    'inv-uuid',
    product_id,
    qty,
    unit,
    unit_price,
    discount_percent,
    subtotal
FROM delivery_note_items
WHERE sj_id = 'uuid-xxx';

-- Create Reminder
INSERT INTO reminders (
    reminder_id = UUID(),
    company_id = 'company-uuid',
    reference_type = 'Invoice',
    reference_id = 'inv-uuid',
    due_date = '2025-11-21',
    title = 'Invoice Jatuh Tempo',
    description = 'INV/2025/10/123 - PT Maju Jaya - Rp 53.196.750',
    status = 'Upcoming'
);
```

---

#### **💳 STEP 6: Customer Bayar & Record Payment**
**Actor:** Finance Staff (Siti)  
**Time:** 25/10/2025 (H+4, customer bayar lebih cepat)

**Physical Process:**
1. Customer transfer ke rekening BCA
2. Finance cek mutasi rekening
3. Ada transfer masuk Rp 53.196.750

**Verification:**
- ✅ Jumlah sesuai: Rp 53.196.750
- ✅ Berita transfer: "INV/2025/10/123"
- ✅ Dari rekening: PT Maju Jaya

**Action di Sistem:**

**Menu:** Sales → Payment → New

**Form Payment:**
```
- Invoice: [Dropdown] INV/2025/10/123 - PT Maju Jaya
  → System show:
     • Grand Total: Rp 53.196.750
     • Paid: Rp 0
     • Remaining: Rp 53.196.750
- Customer: [Auto] PT Maju Jaya
- Tanggal Bayar: 25/10/2025
- Jumlah: Rp 53.196.750
- Metode: [Dropdown] Transfer Bank
- No. Referensi: [Input] BCA-20251025-123456 (dari mutasi bank)
- Notes: Lunas sesuai transfer BCA
```

**Action:**
1. Klik "Save" → Payment recorded
2. System auto update invoice status → "Paid"
3. Print bukti payment (optional)
4. File payment record

**Database Impact:**
```sql
INSERT INTO payments (
    payment_id = 'pay-uuid',
    company_id = 'company-uuid',
    invoice_id = 'inv-uuid',
    customer_id = 'majujaya-uuid',
    payment_date = '2025-10-25',
    amount = 53196750,
    payment_method = 'Transfer Bank',
    reference_number = 'BCA-20251025-123456',
    notes = 'Lunas sesuai transfer BCA',
    created_by = 'siti-uuid'
);

-- Auto Update Invoice Status
UPDATE invoices 
SET status = 'Paid',
    updated_at = NOW()
WHERE invoice_id = 'inv-uuid';

-- Update Reminder
UPDATE reminders 
SET status = 'Completed',
    is_read = true
WHERE reference_id = 'inv-uuid' 
  AND reference_type = 'Invoice';
```

---

### **✅ CASE 1 SUMMARY - TIMELINE**

| **Time** | **Actor** | **Action** | **System Module** | **Status** |
|----------|-----------|------------|-------------------|------------|
| 09:00 | Sales | Terima email customer | Email | - |
| 09:15 | Sales | Cek stock | Inventory | Stock OK |
| 09:30 | Sales | Buat SJ | Sales → SJ | Draft → Sent |
| 10:00 | Warehouse | Pick & pack barang | Physical | - |
| 10:30 | Warehouse | Input stock movement | Inventory → Stock Movement | 3 movements created |
| 10:35 | Warehouse | Update SJ completed | Sales → SJ | Completed |
| 14:00 | Finance | Buat invoice | Sales → Invoice | Unpaid |
| 14:05 | Finance | Send email customer | Email | Sent |
| 25/10 | Customer | Transfer bayar | Bank | - |
| 25/10 | Finance | Record payment | Sales → Payment | Paid ✅ |

**Total Process Time:** 4 hari (21-25 Okt)  
**Anomaly:** ❌ Tidak ada  
**Stock Movement:** ✅ Tercatat  
**Invoice Status:** ✅ Paid  

---

## **CASE 2: Customer Order (Stock Tidak Cukup)** ⚠️

### **Scenario:**
Customer "CV Sejahtera" order:
- Laptop Dell XPS - Qty: 20 unit
- Stock di gudang: Hanya 8 unit
- Perlu koordinasi dengan purchasing untuk restock

---

### **STEP-BY-STEP PROCESS:**

#### **📧 STEP 1-2: Sales Terima Order & Cek Stock**
**Actor:** Sales Staff (Budi)

**Stock Check Result:**
```
Product: Laptop Dell XPS
- Requested: 20 unit
- Available: 8 unit
- Shortage: 12 unit ❌
```

**Decision Point:**
```
Option A: Jual sebagian (8 unit dulu)
Option B: Tunggu restock dulu
Option C: Tawarkan alternatif produk
Option D: Kombinasi (8 unit dulu + PO untuk 12 unit)
```

**Action:** Hubungi customer untuk konfirmasi

---

#### **📞 STEP 3: Koordinasi dengan Customer**
**Actor:** Sales Staff (Budi)  
**Channel:** Phone Call / WhatsApp

**Conversation:**
```
Budi: "Pak, untuk Laptop Dell XPS stock kami saat ini 8 unit.
       Untuk 12 unit lagi, butuh waktu 5-7 hari dari supplier.
       
       Pilihan Bapak:
       1. Kirim 8 unit dulu, sisanya menyusul
       2. Tunggu semua ready (7 hari)
       3. Ganti model lain (Laptop HP ada 20 unit ready)"

Customer: "Kirim 8 unit dulu, 12 unit menyusul tidak apa-apa."

Budi: "Baik Pak, saya proses segera."
```

**Decision:** ✅ Split order (partial delivery)

---

#### **📝 STEP 4A: Buat SJ untuk 8 Unit (Stock Tersedia)**
**Actor:** Sales Staff (Budi)

**Action:** Sama seperti Case 1, tapi:
```
- SJ Number: SJ/2025/10/046
- Customer: CV Sejahtera
- Items:
  • Laptop Dell XPS - Qty: 8 unit
- Notes: "Partial delivery 1 of 2. Sisa 12 unit menyusul setelah restock."
- Status: Sent
```

**Warehouse Action:**
1. Pick 8 unit
2. Create stock movement (out) - 8 unit
3. Update SJ completed
4. Kirim ke customer

**Result:**
- ✅ 8 unit terkirim
- ✅ Customer terima partial
- ⏳ 12 unit pending

---

#### **📋 STEP 4B: Koordinasi dengan Purchasing**
**Actor:** Sales Staff (Budi) → Purchasing Staff (Rina)

**Internal Communication:**
```
Budi: "Bu Rina, kita perlu restock Laptop Dell XPS.
       Ada order customer 12 unit lagi yang pending.
       Bisa di-follow up ke supplier?"

Rina: "Baik, saya cek supplier. Biasanya 5 hari sampai."
```

**Action by Purchasing:**
[Lanjut ke CASE 7: Pembelian Normal dengan PO]

---

#### **📦 STEP 5: Setelah Restock Masuk (H+7)**
**Time:** 28/10/2025

**Warehouse Action:**
1. Terima barang dari supplier (20 unit)
2. Input SP → Auto create inventory batch
3. Stock bertambah → Laptop Dell XPS: 8 + 20 = 28 unit

**Sales Follow-up:**
1. Cek stock: ✅ 12 unit ready
2. Hubungi customer: "Pak, 12 unit sudah ready"
3. Customer: "OK, kirim"

**Create SJ Kedua:**
```
- SJ Number: SJ/2025/10/052
- Customer: CV Sejahtera
- Items:
  • Laptop Dell XPS - Qty: 12 unit
- Notes: "Partial delivery 2 of 2. Melengkapi order sebelumnya (SJ/2025/10/046)"
- Status: Sent
```

**Warehouse Action:**
1. Pick 12 unit (dari batch baru)
2. Create stock movement (out) - 12 unit
3. Update SJ completed
4. Kirim ke customer

---

#### **💰 STEP 6: Finance Buat Invoice (untuk TOTAL order)**
**Actor:** Finance Staff (Siti)

**Decision Point:**
```
Option A: 1 Invoice untuk 2 SJ (Total 20 unit)
Option B: 2 Invoice terpisah (8 unit + 12 unit)
```

**Company Policy:** 1 Invoice untuk 1 customer order  
**Action:** Buat 1 invoice dengan 2 SJ reference

**Form Invoice:**
```
- Invoice Number: INV/2025/10/124
- Customer: CV Sejahtera
- No. SJ: SJ/2025/10/046 + SJ/2025/10/052
- Items:
  • Laptop Dell XPS - Qty: 20 unit × Rp 15.000.000 = Rp 300.000.000
- Subtotal: Rp 300.000.000
- PPN 11%: Rp 33.000.000
- Grand Total: Rp 333.000.000
- Due Date: 30 hari
- Notes: "2x pengiriman: SJ/2025/10/046 (8 unit) + SJ/2025/10/052 (12 unit)"
```

**Customer Payment:** Normal flow (Case 1 Step 6)

---

### **✅ CASE 2 SUMMARY - KEY LEARNINGS**

**Challenges:**
- ❌ Stock tidak cukup saat order masuk
- ⏳ Lead time supplier 5-7 hari

**Solutions:**
- ✅ Split order (partial delivery)
- ✅ Koordinasi sales-purchasing
- ✅ Transparansi ke customer
- ✅ Follow-up after restock

**Best Practices:**
1. ✅ Selalu cek stock SEBELUM janji customer
2. ✅ Komunikasi jujur dengan customer
3. ✅ Set expectation yang realistis
4. ✅ Follow-up proaktif setelah restock
5. ✅ 1 Invoice untuk total order (bukan per SJ)

---

## **CASE 3: Customer Order Produk CATALOG** 📋

### **Scenario:**
Customer "PT Teknologi Maju" order:
- Server HP ProLiant Gen10 - Qty: 2 unit (Product Type: **CATALOG**)
- Produk ini TIDAK ada di gudang
- Hanya untuk referensi/katalog
- Setelah order, baru dibeli dari supplier

---

### **KONSEP PRODUK CATALOG:**

**Definisi:**
```sql
products.product_type = 'CATALOG'

Karakteristik:
- ❌ Tidak ada stock fisik di gudang
- ❌ Tidak perlu validasi stock
- ❌ Tidak perlu stock movement
- ✅ Hanya untuk penawaran/order
- ✅ Beli dari supplier AFTER customer order
- ✅ Drop shipping / custom order
```

**Use Case:**
1. Produk mahal/jarang (server, mesin industri)
2. Produk custom/made-to-order
3. Produk import dengan long lead time
4. Untuk diversifikasi catalog tanpa risk inventory

---

### **STEP-BY-STEP PROCESS:**

#### **📧 STEP 1: Sales Terima Order**
**Actor:** Sales Staff (Budi)

**Email Customer:**
```
Kami butuh Server HP ProLiant Gen10 - 2 unit
Untuk project data center.
Berapa harga dan berapa lama ready?
```

**Sales Check:**
1. Search produk di sistem
2. Product found: Server HP ProLiant Gen10
3. Product Type: **CATALOG** (bukan STOCK)
4. Harga: Rp 50.000.000/unit
5. Lead time: 14 hari (dari supplier)

---

#### **💰 STEP 2: Sales Buat Quotation/Penawaran**
**Actor:** Sales Staff (Budi)

**Action:** Email/WhatsApp ke customer

**Quotation:**
```
PT Teknologi Maju
Quotation for: Server HP ProLiant Gen10

Item: Server HP ProLiant Gen10 Plus
Qty: 2 unit
Price: Rp 50.000.000/unit
Subtotal: Rp 100.000.000
PPN 11%: Rp 11.000.000
Total: Rp 111.000.000

Lead Time: 14 working days from PO
Payment: 50% DP, 50% before delivery

Terms:
- Barang dipesan setelah DP diterima
- Garansi resmi 3 tahun
- Include instalasi

Valid until: 31 Oktober 2025
```

**Customer:** "OK, setuju. Kami transfer DP hari ini."

---

#### **💳 STEP 3: Customer Transfer DP 50%**
**Actor:** Customer → Finance Staff (Siti)

**Action:**
1. Customer transfer Rp 55.500.000 (50% DP)
2. Finance cek mutasi bank
3. Confirm DP received

**Record di Sistem:**

**Menu:** Sales → Payment → New (Advanced Payment)

```
- Type: Down Payment
- Customer: PT Teknologi Maju
- Amount: Rp 55.500.000
- Reference: BCA-xxx
- Notes: DP 50% untuk order Server HP ProLiant (belum ada invoice)
- Status: Received
```

---

#### **📋 STEP 4: Purchasing Order ke Supplier**
**Actor:** Purchasing Staff (Rina)

**Trigger:** DP customer received ✅

**Action:**
[Follow CASE 7: Purchasing Flow]

**Quick Summary:**
1. Buat PO ke supplier: 2 unit Server HP
2. Harga beli: Rp 45.000.000/unit (margin: Rp 5 jt/unit)
3. Lead time: 14 hari
4. Send PO ke supplier

---

#### **⏳ STEP 5: Waiting Period (14 hari)**

**Timeline:**
```
Day 1-3: Supplier proses order
Day 4-10: Supplier import barang
Day 11-12: Customs clearance
Day 13: Barang sampai supplier warehouse
Day 14: Supplier kirim ke PT Adam Jaya
```

**Communication:**
- Day 7: Sales update customer → "Barang sedang dalam proses import"
- Day 13: Sales update customer → "Barang sudah sampai, besok kami kirim"

---

#### **📦 STEP 6: Warehouse Terima Barang dari Supplier**
**Actor:** Warehouse Staff (Dedi)  
**Time:** Day 14

**Physical Process:**
1. Barang datang dari supplier
2. Unboxing & check kondisi
3. Foto barang untuk dokumentasi

**Action di Sistem:**

**Menu:** Purchasing → Supplier Delivery Note (SP) → New

```
- SP Number: [Auto] SP/2025/10/015
- PO Reference: PO/2025/10/008
- Supplier: PT Supplier Server
- Items:
  • Server HP ProLiant Gen10 - Qty: 2 unit
- Status: Received
- Notes: Untuk customer PT Teknologi Maju (pre-order)
```

**⚠️ IMPORTANT:**

**Untuk Produk CATALOG - TIDAK perlu stock movement!**

**Reasoning:**
- Barang langsung untuk customer tertentu
- Tidak masuk inventory pool
- Langsung di-forward ke customer
- Seperti "drop shipping"

**Alternative (jika company policy beda):**
Bisa tetap create stock movement IN dan OUT dalam waktu bersamaan (pass-through).

---

#### **📝 STEP 7: Buat SJ & Kirim ke Customer**
**Actor:** Sales Staff (Budi) → Warehouse (Dedi)

**⚠️ SPECIAL CASE: Produk CATALOG**

**Action di Sistem:**

**Menu:** Sales → Surat Jalan → New

```
Section 1: Informasi
- SJ Number: [Auto] SJ/2025/11/003
- Customer: PT Teknologi Maju
- Product Type: ⚠️ CATALOG (bypass stock validation)
- Tanggal: 04/11/2025 (Day 14)
- Status: Draft → Sent

Section 2: Items
- Product: Server HP ProLiant Gen10
  → ⚠️ System detect: product_type = 'CATALOG'
  → ✅ Skip stock validation
  → ✅ No stock movement required
- Qty: 2 unit
- Harga: Rp 50.000.000/unit
- Subtotal: Rp 100.000.000

Notes: "Pre-order item. Direct delivery from supplier."
```

**Validation Logic:**
```php
if ($product->product_type === 'CATALOG') {
    // ✅ Bypass stock checking
    // ✅ Tidak perlu create stock movement
    // ✅ Langsung allow create SJ
} else {
    // Normal validation (Case 1)
}
```

**Warehouse Action:**
1. Repack barang (jika perlu)
2. Label customer
3. **TIDAK perlu input stock movement** (karena CATALOG)
4. Update SJ status → Completed
5. Kirim ke customer

**Result:**
- ✅ SJ created tanpa validasi stock
- ✅ Tidak muncul di Anomaly Report (by design)
- ✅ Customer terima barang

---

#### **💰 STEP 8: Finance Buat Invoice & Pelunasan**
**Actor:** Finance Staff (Siti)

**Action:** Buat invoice

**Form Invoice:**
```
- Invoice Number: INV/2025/11/045
- Customer: PT Teknologi Maju
- SJ Reference: SJ/2025/11/003
- Items:
  • Server HP ProLiant Gen10 - 2 × Rp 50.000.000 = Rp 100.000.000
- Subtotal: Rp 100.000.000
- PPN 11%: Rp 11.000.000
- Grand Total: Rp 111.000.000
- DP Paid: - Rp 55.500.000
- **Remaining: Rp 55.500.000**
- Due Date: 7 hari
- Notes: "Pelunasan 50%. DP Rp 55.500.000 sudah dibayar tanggal 21 Okt 2025."
```

**Send to Customer:** Email invoice

**Customer Action:**
1. Terima barang (Day 14)
2. Check & test barang
3. Transfer pelunasan Rp 55.500.000

**Finance Action:**
1. Record payment pelunasan
2. Link to invoice
3. Invoice status → Paid ✅

---

### **✅ CASE 3 SUMMARY - CATALOG PRODUCT**

**Key Differences vs STOCK Product:**

| **Aspect** | **STOCK Product** | **CATALOG Product** |
|------------|-------------------|---------------------|
| Stock Validation | ✅ Required | ❌ Bypass |
| Stock Movement | ✅ Required (out) | ❌ Not required |
| Inventory Impact | ✅ Decrease stock | ❌ No impact |
| Anomaly Detection | ✅ Alert if missing | ❌ Excluded |
| Lead Time | Immediate (if stock) | 7-30 days (from supplier) |
| Payment | After delivery | Usually DP + pelunasan |
| Use Case | Regular products | Custom/rare/expensive items |

**Business Benefits:**
1. ✅ Expand product offering tanpa risk inventory
2. ✅ No capital tied up in slow-moving items
3. ✅ Flexible untuk produk mahal
4. ✅ Lower storage cost
5. ✅ Customer dapat akses lebih banyak produk

**Risk Mitigation:**
1. ⚠️ Require DP before ordering from supplier
2. ⚠️ Clear communication about lead time
3. ⚠️ Backup supplier untuk critical items
4. ⚠️ SLA agreement dengan customer

---

[CONTINUE TO NEXT CASES: 4-18...]

---

# 📄 **TO BE CONTINUED...**

Dokumentasi ini akan dilanjutkan dengan:
- Case 4-6: Sales scenarios lainnya
- Case 7-10: Purchasing flows
- Case 11-14: Inventory management
- Case 15-18: Error handling & exceptions
- Daily operations & monthly closing

**Current Status:** Case 1-3 Complete ✅  
**Next Update:** Case 4-6 (Customer partial payment, urgent delivery, returns)

---

**Last Updated:** 21 Oktober 2025  
**Version:** 2.0 - Production Ready  
**Feedback:** Please report any unclear steps to development team
