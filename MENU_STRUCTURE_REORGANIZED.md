# 📋 STRUKTUR MENU SISTEM ERP - REORGANISASI

## 🎯 Konsep Reorganisasi

Menu diorganisir berdasarkan **FLOW BISNIS** dan **FUNGSI** bukan technical grouping:
- **Transaksi** = Semua aktivitas jual-beli (dari penawaran sampai invoice)
- **Keuangan** = Semua yang berhubungan dengan uang (piutang, hutang, pembayaran)
- **Inventory** = Pengelolaan barang
- **Master Data** = Data referensi
- **Laporan** = Report & analytics
- **User Management** = Admin & roles

---

## 📁 STRUKTUR MENU BARU

### 1️⃣ **TRANSAKSI** (Flow lengkap jual-beli)
Urutan mengikuti business flow dari awal sampai akhir:

```
📦 Transaksi
├─ 1. Penawaran Harga (PH)         → Dari supplier/ke customer
├─ 2. Purchase Order (PO)          → Order ke supplier
├─ 10. Semua Invoice                → Invoice customer (semua tipe)
├─ 11. ├─ Invoice PPN               → Filter PPN only
├─ 12. └─ Invoice Non-PPN           → Filter Non-PPN only
└─ 13. Surat Jalan (SJ)             → Delivery notes
```

**KORELASI:**
- PH → PO (Penawaran jadi Order)
- PO → Receive Stock
- SJ → Invoice (Pengiriman jadi Invoice)

---

### 2️⃣ **KEUANGAN** (Cash flow & pembayaran)

```
💰 Keuangan
├─ 10. Piutang 🔴                   → Unpaid invoices (from customers)
├─ 11. Hutang 🔴                    → Unpaid POs (to suppliers)
├─ 20. Pembayaran dari Customer     → Record customer payments
└─ 21. Pembayaran Hutang            → Record supplier payments
```

**KORELASI:**
- **Piutang** = Invoice yang belum lunas → Bayar via "Pembayaran dari Customer"
- **Hutang** = PO yang belum lunas → Bayar via "Pembayaran Hutang"
- Badge 🔴 = Jumlah yang sudah overdue

---

### 3️⃣ **INVENTORY** (Pengelolaan stok)

```
📦 Inventory
├─ 10. Stok Barang                  → Current stock levels
└─ 20. Mutasi Stok                  → Stock movements (in/out)
```

**KORELASI:**
- **Stok Barang** = Stock akhir per produk
- **Mutasi Stok** = History pergerakan (dari PO, SJ, adjustment)

---

### 4️⃣ **MASTER DATA** (Data referensi)

```
📚 Master Data
├─ 1. Company                       → Multi-company setup
├─ 2. Customer                      → Daftar customer
├─ 3. Product                       → Katalog produk
└─ 4. Supplier                      → Daftar supplier
```

**KORELASI:**
- **Product** → dipakai di PO, Invoice, Stock
- **Customer** → dipakai di Invoice, SJ, Piutang
- **Supplier** → dipakai di PO, PH, Hutang
- **Company** → Isolasi data per perusahaan

---

### 5️⃣ **LAPORAN** (Reports & Analytics)

```
📊 Laporan
├─ 10. Laporan Penjualan            → Sales reports
└─ 20. Laporan Inventory            → Stock reports
```

---

### 6️⃣ **USER MANAGEMENT** (Admin)

```
👥 User Management
├─ 1. Role                          → Permissions
└─ 2. Users                         → User accounts
```

---

### 7️⃣ **ADMIN** (System)

```
⚙️ Admin
└─ 1. Data Import                   → Bulk import tools
```

---

## 🔔 NOTIFIKASI (Top Bar Bell Icon)

Semua notifikasi muncul di **bell icon** di top bar (tidak ada di sidebar):

```
🔔 Bell Icon Notifications:
├─ ⚠️ Stok Rendah (Below minimum)
├─ ❌ Produk Kadaluarsa (Expired)
├─ 🔴 Piutang Terlambat (Invoice overdue)
├─ ⏰ Piutang Jatuh Tempo Soon (Invoice due in 3 days)
├─ 🔴 Hutang Terlambat (PO payment overdue)
└─ ⏰ Hutang Jatuh Tempo Soon (PO due in 3 days)
```

**Auto-refresh:** Setiap 5 menit via Laravel Scheduler

---

## 🔄 BUSINESS FLOW LENGKAP

### A. **PURCHASE FLOW (Pembelian)**
```
1. Supplier kirim PH (Price Quotation)
2. Buat PO dari PH
3. Terima barang → Stock masuk (via PO receive)
4. PO punya due_date untuk pembayaran
5. Bayar ke supplier via "Pembayaran Hutang"
6. Status: unpaid → partial → paid
```

### B. **SALES FLOW (Penjualan)**
```
1. Buat Surat Jalan (SJ) untuk pengiriman
2. SJ otomatis generate Invoice
3. Invoice punya due_date
4. Customer bayar via "Pembayaran dari Customer"
5. Status: Unpaid → Partial → Paid
```

### C. **STOCK FLOW**
```
IN:  PO receive → Stock +
OUT: SJ delivery → Stock -
VIEW: Stok Barang (current level)
TRACK: Mutasi Stok (history)
```

### D. **CASH FLOW**
```
RECEIVABLES (Piutang):
- Invoice unpaid/partial → Tampil di menu Piutang
- Badge merah = yang overdue
- Notif bell = overdue + due soon

PAYABLES (Hutang):
- PO unpaid/partial → Tampil di menu Hutang
- Badge merah = yang overdue
- Notif bell = overdue + due soon
```

---

## 📊 DASHBOARD WIDGETS (Coming Soon)

```
Dashboard
├─ 💰 Total Piutang
├─ 💰 Total Hutang
├─ 📦 Stok Rendah (count)
├─ ⚠️ Overdue Invoices
├─ ⚠️ Overdue PO Payments
└─ 📈 Sales Chart (monthly)
```

---

## 🎨 UI/UX IMPROVEMENTS

### ✅ Yang Sudah Diterapkan:
1. **Hierarki Visual** - Prefix `├─` dan `└─` untuk submenu
2. **Badge Indicators** - Jumlah overdue di menu Piutang/Hutang
3. **Bahasa Indonesia** - Semua label dalam Bahasa
4. **Logical Grouping** - Group by business function
5. **Sort Order** - Mengikuti business flow sequence
6. **Hidden Menus** - Notification resources hidden (pindah ke bell icon)

### 🔒 Menu yang Di-hidden:
- `NotificationResource` - Pindah ke bell icon
- `InvoiceDueNotificationResource` - Pindah ke bell icon
- `UnifiedNotificationResource` - Pindah ke bell icon
- `StockAnomalyReportResource` - Hidden (optional feature)

---

## 🧪 DATABASE SEEDER

File: `database/seeders/ComprehensiveTestSeeder.php`

### Generate test data untuk semua fitur:

```php
php artisan db:seed --class=ComprehensiveTestSeeder
```

### Data yang di-generate:
- ✅ 1 Company + 1 Admin User
- ✅ 15 Customers (various types & credit limits)
- ✅ 10 Suppliers (various payment terms)
- ✅ 50 Products (berbagai kategori)
- ✅ 50 Stock records (40% normal, 30% low, 20% expired, 10% zero)
- ✅ 20 Purchase Orders dengan pembayaran (30% paid, 30% partial, 20% unpaid, 20% overdue)
- ✅ 30 Invoices dengan pembayaran (30% paid, 30% partial, 20% unpaid, 20% overdue)

### Skenario Testing yang Tercover:
1. ✅ Stock rendah (notifikasi)
2. ✅ Stock kadaluarsa (notifikasi)
3. ✅ Piutang overdue (badge + notifikasi)
4. ✅ Piutang due soon (notifikasi)
5. ✅ Hutang overdue (badge + notifikasi)
6. ✅ Hutang due soon (notifikasi)
7. ✅ Payment scenarios (full, partial, unpaid)
8. ✅ PPN vs Non-PPN invoices & POs
9. ✅ Complete business flow (PH → PO → Invoice)

---

## 📝 CARA TESTING

### 1. Setup Database:
```bash
php artisan migrate:fresh
php artisan db:seed --class=ComprehensiveTestSeeder
```

### 2. Login:
- Email: `admin@test.com`
- Password: `password`

### 3. Test Each Menu:
- **Transaksi** → Cek PH, PO, Invoice, SJ
- **Keuangan** → Cek Piutang (badge merah), Hutang (badge merah)
- **Inventory** → Cek Stok Barang (ada yang low), Mutasi Stok
- **Bell Icon** → Harus ada notifikasi (stok, piutang, hutang)

### 4. Test Flows:
- Buat PO → Receive → Check stock bertambah
- Buat SJ → Generate Invoice → Record payment
- Check badge piutang/hutang update otomatis

### 5. Test Notifications:
```bash
php artisan notifications:send
```
Check bell icon harus ada notifikasi baru.

---

## 🚀 NEXT STEPS

1. ✅ Reorganisasi menu selesai
2. ✅ Database seeder selesai
3. ⏳ Dashboard widgets
4. ⏳ Export Excel di laporan
5. ⏳ WhatsApp notifications (optional)
6. ⏳ Auto-reminder email untuk overdue

---

## 💡 TIPS UNTUK DEVELOPER

### Menambah Menu Baru:
```php
protected static ?string $navigationGroup = 'Transaksi'; // atau Keuangan, Inventory, dll
protected static ?int $navigationSort = 15; // 1-9 = awal group, 10-19 = tengah, 20+ = akhir
```

### Menambah Badge:
```php
public static function getNavigationBadge(): ?string
{
    return (string) Model::where('status', 'pending')->count();
}

public static function getNavigationBadgeColor(): ?string
{
    return 'danger'; // danger, warning, success, info
}
```

### Menambah Notifikasi Baru:
Edit: `app/Services/NotificationService.php`
```php
public static function sendNewNotification(): void
{
    // Your notification logic
    FilamentNotification::make()
        ->warning()
        ->title('Title')
        ->body('Body')
        ->sendToDatabase($user);
}
```

Tambahkan ke `sendAllNotifications()`:
```php
public static function sendAllNotifications(): void
{
    self::sendNewNotification();
    // ... existing notifications
}
```

---

**Last Updated:** 30 Januari 2026
**Version:** 2.0 (Reorganized)
