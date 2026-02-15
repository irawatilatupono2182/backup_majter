# 🎨 PROTOTYPE DATA - Adam Jaya System

> **Data Dummy untuk Testing & Demo**  
> Berdasarkan: MENU_STRUCTURE_COMPLETE.md  
> Dibuat: 15 Februari 2026

---

## 📋 DAFTAR ISI

1. [Tentang Prototype](#tentang-prototype)
2. [Cara Setup](#cara-setup)
3. [Data yang Tersedia](#data-yang-tersedia)
4. [Struktur File](#struktur-file)
5. [Kredensial Login](#kredensial-login)

---

## 🎯 TENTANG PROTOTYPE

Folder ini berisi **data dummy prototype** yang sesuai dengan struktur menu aplikasi Adam Jaya. Data ini dirancang untuk:

- ✅ Testing fitur aplikasi
- ✅ Demo ke client
- ✅ Development & debugging
- ✅ Training user baru
- ✅ Performance testing

**Total Data Dummy:**
- 👥 5 Users (berbagai role)
- 🏢 10 Companies
- 👔 50 Customers
- 📦 100 Products
- 🏭 30 Suppliers
- 🛒 200 Purchase Orders
- 💼 300 Invoices
- 🚚 250 Delivery Notes
- 💰 150 Payments

---

## 🚀 CARA SETUP

### Method 1: Menggunakan Seeder (Recommended)

```bash
# 1. Masuk ke folder project
cd c:\laragon\www\adamjaya

# 2. Reset database (HATI-HATI: Akan hapus semua data!)
php artisan migrate:fresh

# 3. Run seeder prototype
php artisan db:seed --class=PrototypeMasterSeeder

# 4. Login ke aplikasi
# URL: http://localhost/adamjaya/admin
# Email: admin@adamjaya.com
# Password: password
```

### Method 2: Import SQL File

```bash
# Import via MySQL
mysql -u root -p adamjaya < prototype/data/prototype_full_data.sql

# Atau via phpMyAdmin
# Import file: prototype/data/prototype_full_data.sql
```

### Method 3: Step by Step

```bash
# Run seeder satu per satu
php artisan db:seed --class=PrototypeUsersSeeder
php artisan db:seed --class=PrototypeCompaniesSeeder
php artisan db:seed --class=PrototypeCustomersSeeder
php artisan db:seed --class=PrototypeProductsSeeder
php artisan db:seed --class=PrototypeSuppliersSeeder
php artisan db:seed --class=PrototypePurchaseSeeder
php artisan db:seed --class=PrototypeSalesSeeder
php artisan db:seed --class=PrototypeFinanceSeeder
```

---

## 📊 DATA YANG TERSEDIA

### 1️⃣ 📦 MASTER DATA

#### Users & Roles
| Role | Username | Email | Password | Akses |
|------|----------|-------|----------|-------|
| Super Admin | admin | admin@adamjaya.com | password | Full Access |
| Manager | manager | manager@adamjaya.com | password | Manager Level |
| Sales | sales | sales@adamjaya.com | password | Sales Only |
| Purchasing | purchasing | purchasing@adamjaya.com | password | Purchasing Only |
| Finance | finance | finance@adamjaya.com | password | Finance Only |

#### Companies
- 10 perusahaan dummy (PT. Adam Jaya sebagai perusahaan utama)
- Alamat lengkap, NPWP, No. Telp

#### Products
- 100 produk dengan kategori:
  - 40 Barang Lokal
  - 60 Barang Import
- Lengkap dengan: kode, nama, satuan, harga beli, harga jual, stok

---

### 2️⃣ 💼 PENJUALAN

#### Customers (50 customer)
- Customer Lokal: 30 customer
- Customer PPN: 35 customer  
- Customer Non-PPN: 15 customer
- Lengkap dengan: nama, alamat, no. telp, NPWP, term pembayaran

#### Price Quotations (80 surat penawaran)
- Status: Draft (20), Sent (30), Approved (20), Rejected (10)
- Range harga: Rp 1jt - Rp 100jt
- Tanggal: 3 bulan terakhir

#### Delivery Notes / Surat Jalan (250 SJ)
- Status: Pending (50), Delivered (180), Cancelled (20)
- Nomor SJ format: SJ-2026-0001 dst
- Ada yang sudah diinvoice, ada yang belum

#### Invoices (300 invoice)
- Invoice PPN: 200 invoice (66%)
- Invoice Non-PPN: 100 invoice (34%)
- Status: 
  - Unpaid: 80 (26%)
  - Partial: 70 (23%)
  - Paid: 130 (43%)
  - Overdue: 20 (7%)
- Range nilai: Rp 500rb - Rp 200jt

#### Nota Menyusul (30 nota)
- Status: Pending (15), Completed (10), Cancelled (5)

#### Keterangan Lain (40 items)
- Untuk invoice dengan biaya tambahan

---

### 3️⃣ 🛒 PEMBELIAN

#### Suppliers (30 supplier)
- Supplier Lokal: 15 supplier
- Supplier Import: 15 supplier
- Lengkap dengan: nama, alamat, kontak, term pembayaran

#### Purchase Orders (200 PO)
- PO Lokal: 120 PO
- PO Import: 80 PO
- Status:
  - Draft: 20 (10%)
  - Submitted: 30 (15%)
  - Approved: 100 (50%)
  - Received: 40 (20%)
  - Cancelled: 10 (5%)
- Range nilai: Rp 1jt - Rp 150jt

#### Stock/Inventory
- 100 items dengan stok aktif
- Stock movement history (mutasi stok)
- Kategori: Barang Lokal & Import

---

### 4️⃣ 💰 KEUANGAN

#### Payments (Pembayaran dari Customer)
- 150 pembayaran
- Metode: Transfer Bank, Cash, Giro
- Status: Pending (20), Cleared (120), Rejected (10)
- Total nilai: ~Rp 2 Miliar

#### Payable Payments (Pembayaran ke Supplier)
- 100 pembayaran
- Metode: Transfer Bank, Cash, Giro
- Status: Pending (15), Paid (80), Cancelled (5)
- Total nilai: ~Rp 1.5 Miliar

#### Receivables (Piutang)
- Total piutang: ~Rp 800 Juta
- Aging analysis:
  - Current: Rp 300jt
  - 1-30 days: Rp 250jt
  - 31-60 days: Rp 150jt
  - 60+ days: Rp 100jt

#### Payables (Hutang)
- Total hutang: ~Rp 500 Juta
- Tersebar di 20 supplier

---

### 5️⃣ 📈 LAPORAN

Semua laporan akan terisi otomatis dari data transaksi di atas:

- ✅ **Laporan Penjualan**: 300 invoice selama 6 bulan
- ✅ **Laporan Pembelian**: 200 PO selama 6 bulan
- ✅ **Laporan Piutang**: Aging analysis lengkap
- ✅ **Laporan Hutang**: Per supplier
- ✅ **Laporan Inventory**: Stock movement & valuation

---

## 📁 STRUKTUR FILE

```
prototype/
├── README.md                          # ← File ini
├── seeders/                           # Seeder files
│   ├── PrototypeMasterSeeder.php     # Master seeder (run all)
│   ├── PrototypeUsersSeeder.php      # Users & Roles
│   ├── PrototypeCompaniesSeeder.php  # Companies
│   ├── PrototypeCustomersSeeder.php  # Customers
│   ├── PrototypeProductsSeeder.php   # Products/Stock
│   ├── PrototypeSuppliersSeeder.php  # Suppliers
│   ├── PrototypePurchaseSeeder.php   # PO & Purchasing
│   ├── PrototypeSalesSeeder.php      # Invoice, SJ, etc
│   └── PrototypeFinanceSeeder.php    # Payments
├── data/                              # Raw data files
│   ├── customers.json                # Customer data
│   ├── products.json                 # Product data
│   ├── suppliers.json                # Supplier data
│   └── prototype_full_data.sql       # Full SQL dump
└── scripts/
    ├── reset-and-seed.bat            # Windows script
    └── reset-and-seed.sh             # Linux script
```

---

## 🔐 KREDENSIAL LOGIN

### Admin Access
```
URL: http://localhost/adamjaya/admin
Email: admin@adamjaya.com
Password: password
```

### Test Users
```
Manager:    manager@adamjaya.com     / password
Sales:      sales@adamjaya.com       / password
Purchasing: purchasing@adamjaya.com  / password
Finance:    finance@adamjaya.com     / password
```

---

## ⚠️ PERINGATAN

### JANGAN Gunakan di Production!

Data ini adalah **DUMMY DATA** untuk testing saja:
- ❌ Jangan gunakan di server production
- ❌ Password default mudah ditebak
- ❌ Data tidak real/fiktif
- ❌ Tidak ada validasi bisnis yang ketat

### Reset Database

Seeder ini akan **MENGHAPUS SEMUA DATA** yang ada jika menggunakan `migrate:fresh`. 

**Backup dulu database production sebelum testing!**

```bash
# Backup database
mysqldump -u root -p adamjaya > backup_before_prototype.sql
```

---

## 📝 CATATAN DEVELOPMENT

### Tanggal Data

Semua data menggunakan range tanggal:
- **Dari**: 1 Agustus 2025
- **Sampai**: 15 Februari 2026 (hari ini)

### Relasi Data

Data sudah dibuat dengan relasi yang benar:
- ✅ Invoice → Delivery Note → Customer
- ✅ PO → Supplier → Stock
- ✅ Payment → Invoice
- ✅ Stock Movement → PO & Delivery Note

### Realistic Business Flow

Data dibuat mengikuti flow bisnis yang realistic:
1. PO dibuat → Barang diterima → Stock masuk
2. Quotation → SJ dibuat → Invoice dibuat → Payment diterima
3. Aging piutang sesuai term payment
4. Stock movement tercatat dengan benar

---

## 🔄 UPDATE DATA

### Menambah Data Baru

```bash
# Tambah 50 customer baru
php artisan db:seed --class=PrototypeCustomersSeeder

# Tambah 100 invoice baru
php artisan db:seed --class=PrototypeSalesSeeder
```

### Reset Spesifik Module

Lihat file seeder individual di folder `seeders/` untuk detail.

---

## 🛠️ TROUBLESHOOTING

### Error: Class not found
```bash
# Regenerate autoload
composer dump-autoload
```

### Error: Foreign key constraint
```bash
# Disable foreign key check (MySQL)
# Sudah dihandle di seeder
```

### Data tidak sesuai harapan
```bash
# Reset dan run ulang
php artisan migrate:fresh
php artisan db:seed --class=PrototypeMasterSeeder
```

---

## 📞 SUPPORT

Untuk pertanyaan atau issue terkait prototype data:
1. Check file seeder yang sesuai
2. Lihat log error: `storage/logs/laravel.log`
3. Adjust data di file JSON atau seeder

---

**Happy Testing! 🚀**

---

*Generated by: AI Assistant*  
*Project: Adam Jaya Management System*  
*Version: 1.0*  
*Date: 15 February 2026*
