# 📋 STRUKTUR MENU DAN SUBMENU FILAMENT - LENGKAP

> **Dokumentasi Lengkap Menu Aplikasi Adam Jaya**  
> Terakhir diperbarui: 15 Februari 2026

---

## 🎯 RINGKASAN EKSEKUTIF

Aplikasi ini memiliki **38 Filament Resources** dan **1 Dashboard Page**.

**Status Menu:**
- ✅ **20 Resources** yang TAMPIL di menu navigasi
- ❌ **18 Resources** yang DISEMBUNYIKAN (ada source code tapi tidak jadi menu)

---

## 📊 MENU UTAMA (Dashboard)

### Dashboard
- **File**: `app/Filament/Pages/Dashboard.php`
- **Icon**: 🎯 Chart Bar
- **Label**: Dashboard
- **Sort**: -2
- **Status**: ✅ AKTIF
- **Widgets**: 13 widgets (KPI, Finance, Sales, Inventory, Purchasing)

---

## 🗂️ MENU & SUBMENU YANG TAMPIL (Berdasarkan Navigation Group)

### 1️⃣ 📦 MASTER DATA

| No | Resource | Label Menu | Sort | File |
|----|----------|-----------|------|------|
| 1 | **RoleResource** | Roles | 1 | `RoleResource.php` |
| 2 | **UserResource** | Users | 2 | `UserResource.php` |

**Resource yang DISEMBUNYIKAN di grup ini:**
- ❌ **CompanyResource** (sort: 1) - Hidden per user request
- ❌ **ProductResource** (sort: 3) - Hidden per user request

---

### 2️⃣ 💼 PENJUALAN

| No | Resource | Label Menu | Sort | File |
|----|----------|-----------|------|------|
| 1 | **CustomerResource** | Customers | 1 | `CustomerResource.php` |
| 2 | **PriceQuotationResource** | Surat Penawaran | 2 | `PriceQuotationResource.php` |
| 3 | **DeliveryNoteResource** | Surat Jalan (SJ) | 3 | `DeliveryNoteResource.php` |
| 4 | **InvoiceResource** | Invoice (Semua) | 4 | `InvoiceResource.php` |
| 5 | **NotaMenyusulResource** | Nota Menyusul | 5 | `NotaMenyusulResource.php` |
| 6 | **KeteranganLainResource** | Keterangan Lain | 6 | `KeteranganLainResource.php` |

**Resource yang DISEMBUNYIKAN di grup ini:**
- ❌ **InvoicePpnResource** (sort: 5) - Hidden, akses via InvoiceResource
- ❌ **InvoiceNonPpnResource** (sort: 6) - Hidden, akses via InvoiceResource

---

### 3️⃣ 🛒 PEMBELIAN

| No | Resource | Label Menu | Sort | File |
|----|----------|-----------|------|------|
| 1 | **StockResource** | Master Barang/Stock | 1 | `StockResource.php` |
| 2 | **SupplierResource** | Suppliers | 2 | `SupplierResource.php` |
| 3 | **PurchaseOrderResource** | Pembelian Barang (PO) | 3 | `PurchaseOrderResource.php` |

**Resource yang DISEMBUNYIKAN di grup ini:**
- ❌ **StockLokalResource** (sort: 1) - Hidden from navigation
- ❌ **StockImportResource** (sort: 2) - Hidden from navigation
- ❌ **SupplierLokalResource** (sort: 3) - Hidden from navigation
- ❌ **SupplierImportResource** (sort: 4) - Hidden from navigation
- ❌ **PurchaseOrderLokalResource** (sort: 5) - Hidden from navigation
- ❌ **PurchaseOrderImportResource** (sort: 6) - Hidden from navigation
- ❌ **PurchasePaymentResource** (sort: 3) - Hidden per user request

---

### 4️⃣ 💰 KEUANGAN

| No | Resource | Label Menu | Sort | File |
|----|----------|-----------|------|------|
| 1 | **PayablePaymentResource** | Pembayaran Hutang | 4 | `PayablePaymentResource.php` |

**Resource yang DISEMBUNYIKAN di grup ini:**
- ❌ **PaymentResource** (sort: 3) - Hidden per user request
- ❌ **PayablesResource** (sort: 99) - Hidden dari menu

---

### 5️⃣ 📈 LAPORAN

| No | Resource | Label Menu | Sort | File |
|----|----------|-----------|------|------|
| 1 | **SalesReportResource** | Laporan Penjualan | 1 | `SalesReportResource.php` |
| 2 | **PurchaseReportResource** | Laporan Pembelian | 2 | `PurchaseReportResource.php` |
| 3 | **ReceivablesResource** | Piutang Usaha (AR) | 3 | `ReceivablesResource.php` |
| 3 | **InventoryReportResource** | Laporan Inventory | 3 | `InventoryReportResource.php` |
| 4 | **PayableResource** | Hutang | 4 | `PayableResource.php` |

**Resource yang DISEMBUNYIKAN di grup ini:**
- ❌ **StockAnomalyReportResource** (sort: 5) - Hidden, accessible via URL

---

### 6️⃣ 🏭 INVENTORI

**Resource yang DISEMBUNYIKAN:**
- ❌ **StockMovementResource** (sort: 2) - "Mutasi Stok" - Hidden per user request

---

### 7️⃣ 🔔 NOTIFIKASI

**Resource yang DISEMBUNYIKAN:**
- ❌ **NotificationResource** (sort: 1) - "Notifikasi Stok & Piutang" - Hidden
- ❌ **InvoiceDueNotificationResource** (sort: 2) - "Notifikasi Jatuh Tempo" - Hidden, accessible via URL
- ❌ **UnifiedNotificationResource** (sort: -1) - "Notifikasi" - Hidden

---

### 8️⃣ ⚙️ PENGATURAN

| No | Resource | Label Menu | Sort | File |
|----|----------|-----------|------|------|
| 1 | **DataImportResource** | Import Data | 3 | `DataImportResource.php` |

---

## 📊 STATISTIK MENU

### Jumlah Menu yang TAMPIL per Grup:

| Navigation Group | Jumlah Tampil | Jumlah Hidden | Total Resources |
|-----------------|---------------|---------------|-----------------|
| 📦 Master Data | 2 | 2 | 4 |
| 💼 Penjualan | 6 | 2 | 8 |
| 🛒 Pembelian | 3 | 7 | 10 |
| 💰 Keuangan | 1 | 2 | 3 |
| 📈 Laporan | 5 | 1 | 6 |
| 🏭 Inventori | 0 | 1 | 1 |
| 🔔 Notifikasi | 0 | 3 | 3 |
| ⚙️ Pengaturan | 1 | 0 | 1 |
| **TOTAL** | **20** | **18** | **38** |

---

## 🔍 DAFTAR LENGKAP RESOURCES YANG DISEMBUNYIKAN

### Mengapa Resource Disembunyikan?

1. **Consolidated Access** - Diakses via resource lain (contoh: InvoicePpnResource via InvoiceResource)
2. **User Request** - Disembunyikan atas permintaan pengguna
3. **URL Only** - Hanya bisa diakses via URL langsung, tidak di menu
4. **Deprecated/Replaced** - Diganti dengan resource lain yang lebih baik

### Detail Resource yang Hidden:

| No | Resource | Navigation Group | Label | Alasan Hidden |
|----|----------|-----------------|-------|---------------|
| 1 | CompanyResource | 📦 Master Data | Companies | Hidden per user request |
| 2 | ProductResource | 📦 Master Data | Products | Hidden per user request |
| 3 | InvoicePpnResource | 💼 Penjualan | Invoice PPN | Hidden (akses via InvoiceResource) |
| 4 | InvoiceNonPpnResource | 💼 Penjualan | Invoice Non-PPN | Hidden (akses via InvoiceResource) |
| 5 | StockLokalResource | 🛒 Pembelian | Barang Lokal | Hidden from navigation |
| 6 | StockImportResource | 🛒 Pembelian | Barang Import | Hidden from navigation |
| 7 | SupplierLokalResource | 🛒 Pembelian | Supplier Lokal | Hidden from navigation |
| 8 | SupplierImportResource | 🛒 Pembelian | Supplier Import | Hidden from navigation |
| 9 | PurchaseOrderLokalResource | 🛒 Pembelian | Pembelian Barang Lokal | Hidden from navigation |
| 10 | PurchaseOrderImportResource | 🛒 Pembelian | Pembelian Barang Import | Hidden from navigation |
| 11 | PurchasePaymentResource | 🛒 Pembelian | Pembayaran ke Supplier | Hidden per user request |
| 12 | PaymentResource | 💰 Keuangan | Pembayaran dari Customer | Hidden per user request |
| 13 | PayablesResource | 💰 Keuangan | Hutang Usaha (AP) | Hidden dari menu |
| 14 | StockAnomalyReportResource | 📈 Laporan | Anomali Stok | Hidden, accessible via URL |
| 15 | StockMovementResource | 🏭 Inventori | Mutasi Stok | Hidden per user request |
| 16 | NotificationResource | 🔔 Notifikasi | Notifikasi Stok & Piutang | Hidden |
| 17 | InvoiceDueNotificationResource | 🔔 Notifikasi | Notifikasi Jatuh Tempo | Hidden, accessible via URL |
| 18 | UnifiedNotificationResource | 🔔 Notifikasi | Notifikasi | Hidden |

---

## 🎨 WIDGETS DASHBOARD

Total: **13 Widgets** (Semua Aktif)

### Section 1: KPI Utama
1. `StatsOverviewWidget` - Overview statistik bisnis

### Section 2: Keuangan
2. `FinanceStatsWidget` - Statistik keuangan
3. `AgingAnalysisChart` - Analisis umur piutang
4. `CashFlowChart` - Grafik arus kas

### Section 3: Penjualan
5. `SalesRevenueChart` - Grafik revenue penjualan
6. `InvoiceStatusChart` - Status invoice
7. `TopCustomersWidget` - Customer teratas
8. `TopSellingProductsWidget` - Produk terlaris

### Section 4: Inventory & Operasional
9. `WarehouseStatsWidget` - Statistik gudang
10. `InventoryAlertsWidget` - Alert inventory
11. `RecentDeliveryNotesWidget` - Surat jalan terbaru

### Section 5: Pembelian
12. `PurchasingActivityWidget` - Aktivitas pembelian

---

## 🔐 PAGES CUSTOM

### Authentication Pages
- **Login Page**: `app/Filament/Pages/Auth/Login.php`

---

## 💡 CATATAN PENTING

### Akses Resource yang Hidden:

Resource yang disembunyikan (`shouldRegisterNavigation = false`) TETAP bisa diakses via:

1. **URL Langsung**: `/admin/resource-name`
2. **Relations**: Melalui relation dari resource lain
3. **Actions**: Via custom actions/buttons
4. **Widget**: Via widget di dashboard

### Strategi Organisasi Menu:

1. **Menu Utama** menampilkan resource yang paling sering digunakan
2. **Resource Spesifik** (Lokal/Import) disembunyikan untuk simplifikasi UI
3. **Resource Notifikasi** diakses via widget/badge, tidak perlu menu
4. **Resource Laporan** yang khusus diakses via URL untuk analisis mendalam

---

## 📝 LOG PERUBAHAN

### v1.0 (15 Feb 2026)
- ✅ Dokumentasi lengkap struktur menu
- ✅ Identifikasi 38 resources total
- ✅ Mapping 20 resources aktif di menu
- ✅ Mapping 18 resources hidden
- ✅ Dokumentasi 13 widgets dashboard

---

## 🔗 REFERENSI

- **Filament Panel**: Default admin panel
- **Resources Path**: `app/Filament/Resources/`
- **Pages Path**: `app/Filament/Pages/`
- **Widgets Path**: `app/Filament/Widgets/`

---

**Prepared by:** AI Assistant  
**Project:** Adam Jaya Management System  
**Framework:** Laravel + Filament v3
