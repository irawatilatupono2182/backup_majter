# 📊 Dashboard Monitoring - Documentation

## Overview
Dashboard monitoring lengkap untuk Adam Jaya ERP dengan visualisasi real-time untuk Sales, Warehouse, Finance, dan Purchasing.

## 🎯 Features

### 1. **Stats Overview Widget** (Top Cards)
Menampilkan 6 KPI utama dengan sparkline charts:

| Card | Deskripsi | Color | Chart |
|------|-----------|-------|-------|
| **Revenue Hari Ini** | Total invoice paid hari ini | Green | 7 days trend |
| **Invoice Belum Lunas** | Count Unpaid + Partial | Yellow | - |
| **Produk Stok Rendah** | Below minimum stock | Red | - |
| **Customer Aktif** | Total active customers | Blue | - |
| **PO Pending** | Menunggu konfirmasi | Orange | - |
| **Total Produk** | Produk aktif | Info | - |

**Features:**
- ✅ Auto refresh setiap 30 detik
- ✅ Clickable cards (link to filtered pages)
- ✅ 7 days revenue trend chart

---

### 2. **Sales Revenue Chart**
📈 Line chart revenue penjualan 30 hari terakhir

**Data Source:** Invoice `grand_total` per hari  
**Type:** Line chart with area fill  
**Color:** Blue gradient  
**Refresh:** 60 seconds

**Features:**
- ✅ Shows daily revenue trend
- ✅ Y-axis formatted in Rupiah
- ✅ Hover untuk lihat detail per tanggal

---

### 3. **Invoice Status Chart**
🍩 Doughnut chart status invoice

**Data:**
- Unpaid (Yellow)
- Partial (Orange)
- Paid (Green)
- Overdue (Red)
- Cancelled (Gray)

**Type:** Doughnut chart  
**Features:**
- ✅ Interactive - hover untuk lihat jumlah
- ✅ Color-coded by status

---

### 4. **Inventory Alerts Widget**
⚠️ Table produk yang perlu perhatian

**Alerts:**
- 🔴 **Expired** - Sudah kadaluarsa
- 🟡 **Near Expiry** - 30 hari lagi kadaluarsa
- 🔴 **Low Stock** - Di bawah minimum

**Columns:**
- Product name
- Alert type (badge)
- Available quantity
- Minimum stock
- Expiry date

**Limit:** 10 items terbaru

---

### 5. **Recent Delivery Notes Widget**
📦 5 Surat Jalan terbaru

**Columns:**
- No. SJ
- Customer
- Tanggal kirim
- Status (Draft/Sent/Completed)
- Jenis (PPN/Non-PPN)

**Refresh:** 30 seconds

---

### 6. **Purchasing Activity Widget**
🛒 5 Purchase Order terbaru

**Columns:**
- No. PO
- Supplier
- Tanggal order
- Expected delivery
- Status (Pending/Confirmed/Completed)

**Refresh:** 30 seconds

---

### 7. **Top Selling Products Widget**
🏆 Top 10 produk terlaris

**Data:** Total qty terjual dari invoice items  
**Columns:**
- Kode produk
- Nama produk
- Kategori
- Total terjual (unit)
- Harga

**Sorting:** By total sold DESC

---

### 8. **Top Customers Widget**
👥 Top 10 customer berdasarkan revenue

**Data:** 
- Total revenue dari invoices
- Total transaksi count

**Columns:**
- Kode customer
- Nama
- Total transaksi
- Total revenue
- Status PPN

**Sorting:** By revenue DESC

---

## 🎨 Layout

Dashboard menggunakan **2-column grid layout**:

```
┌─────────────────────────────────────────────────┐
│  Stats Overview (6 cards, 3 columns)            │
├─────────────────────────────────────────────────┤
│  Sales Revenue Chart (full width)               │
├──────────────────────┬──────────────────────────┤
│  Invoice Status      │  Inventory Alerts        │
│  Chart (doughnut)    │  Widget (table)          │
├──────────────────────┼──────────────────────────┤
│  Recent Delivery     │  Purchasing Activity     │
│  Notes Widget        │  Widget                  │
├──────────────────────┴──────────────────────────┤
│  Top Selling Products Widget (full width)       │
├─────────────────────────────────────────────────┤
│  Top Customers Widget (full width)              │
└─────────────────────────────────────────────────┘
```

---

## 🔐 Access Control

**Roles yang bisa akses:**
- ✅ super_admin - Full access
- ✅ admin - Full access
- ✅ finance - Full access
- ✅ warehouse - Limited (inventory focus)
- ✅ viewer - Read-only

**Per Widget Filtering:**
- Semua data di-filter berdasarkan `selected_company_id`
- User hanya melihat data company yang sedang aktif

---

## 🚀 Technical Details

### Widgets Created:
1. `StatsOverviewWidget.php` - 6 KPI cards
2. `SalesRevenueChart.php` - Revenue line chart
3. `InvoiceStatusChart.php` - Status doughnut chart
4. `RecentDeliveryNotesWidget.php` - Recent SJ table
5. `PurchasingActivityWidget.php` - Recent PO table
6. `TopSellingProductsWidget.php` - Best sellers table
7. `TopCustomersWidget.php` - Top revenue customers table
8. `InventoryAlertsWidget.php` - Stock alerts table (existing)

### Dashboard Page:
- `app/Filament/Pages/Dashboard.php`

### Dependencies:
- **flowframe/laravel-trend** - For trend calculations
- **Filament Charts** - Built-in chart widgets

### Performance:
- Stats cards: Auto refresh 30s
- Charts: Auto refresh 60s
- Tables: No pagination (limited to 5-10 items)
- Queries optimized with `with()`, `limit()`, `latest()`

---

## 📊 Usage Examples

### Scenario 1: Morning Check (Manager)
```
1. Login → Dashboard loads
2. Check Stats Overview:
   - Revenue Hari Ini: Rp 50 juta ✅
   - Invoice Belum Lunas: 12 (click untuk detail)
   - Stok Rendah: 5 produk ⚠️
3. Review Sales Revenue Chart:
   - Trend naik minggu ini ✅
4. Check Inventory Alerts:
   - 3 produk near expiry → koordinasi marketing
   - 2 produk low stock → koordinasi purchasing
5. Action:
   - Follow up unpaid invoices (finance)
   - Restock low items (purchasing)
```

### Scenario 2: Sales Review (Sales Manager)
```
1. Dashboard → Top Selling Products
   - Laptop HP paling laris (50 unit)
   - Mouse Logitech posisi 2 (30 unit)
2. Top Customers:
   - PT Maju Jaya revenue tertinggi
   - Plan follow-up visit
3. Recent Delivery Notes:
   - 2 SJ status Sent (perlu follow-up)
```

### Scenario 3: Warehouse Check (Warehouse Staff)
```
1. Dashboard → Inventory Alerts Widget
2. Sort by alert type:
   - Expired: 0 ✅
   - Near expiry (30 days): 3 items ⚠️
   - Low stock: 5 items 🔴
3. Click low stock items → Redirect to stock page
4. Create stock movement / request PO
```

---

## 🎯 Benefits

### For Management:
- ✅ Real-time visibility ke semua KPI
- ✅ Quick insights tanpa buka multiple pages
- ✅ Data-driven decision making

### For Finance:
- ✅ Monitor unpaid invoices
- ✅ Revenue tracking per hari
- ✅ Top customers by value

### For Sales:
- ✅ Best selling products
- ✅ Customer analytics
- ✅ Delivery status tracking

### For Warehouse:
- ✅ Stock alerts centralized
- ✅ Expiry monitoring
- ✅ Recent delivery tracking

### For Purchasing:
- ✅ PO status monitoring
- ✅ Low stock alerts
- ✅ Supplier activity tracking

---

## 🔧 Customization

### Add New Widget:
```php
// 1. Create widget
php artisan make:filament-widget MyNewWidget --stats

// 2. Register in Dashboard.php
public function getWidgets(): array
{
    return [
        ...
        \App\Filament\Widgets\MyNewWidget::class,
    ];
}
```

### Change Refresh Interval:
```php
// In widget class
protected static string $pollingInterval = '30s'; // Change to 60s, 120s, etc
```

### Change Card Colors:
```php
Stat::make('Label', 'Value')
    ->color('success')  // success, warning, danger, info, primary
```

---

## 📝 Notes

- Dashboard auto-refreshes based on widget polling intervals
- All data filtered by selected company
- Charts use Filament's built-in Chart.js integration
- Tables use Filament Table Builder
- Widgets sortable by `$sort` property

---

**Version:** 1.0  
**Date:** 22 Oktober 2025  
**Status:** ✅ Production Ready
