# PERBAIKAN UX/UI - Menu & Notifikasi

## 🎯 Perubahan yang Dilakukan

### 1. ✅ NOTIFIKASI TOP BAR (Bell Icon)
**Semua notifikasi sekarang muncul di bell icon (samping profile)**

Notifikasi yang muncul otomatis:
- 🔴 **Stock Rendah** - Ketika stock < minimum
- 🔴 **Produk Kadaluarsa** - Produk expired
- 🔴 **Invoice Jatuh Tempo** - Invoice overdue
- 🟡 **Invoice Akan Jatuh Tempo** - 1-3 hari lagi

**Fitur:**
- Real-time notifications di top bar
- Action buttons (Lihat Stock, Lihat Invoice, Catat Pembayaran)
- Auto refresh setiap 30 detik
- Mark as read
- Klik notification langsung ke page terkait

### 2. ✅ MENU SIDEBAR DISEDERHANAKAN

**MENU DIHAPUS dari Sidebar:**
- ❌ Menu "Notifikasi" (semua ada di top bar)
- ❌ Menu "Notifikasi Stok & Piutang" (redundant)
- ❌ Menu "Notifikasi Jatuh Tempo" (redundant)

**STRUKTUR MENU BARU (Lebih Sederhana):**

```
📊 Dashboard

📁 Master Data
  ├── Companies
  ├── Users
  ├── Customers
  ├── Suppliers
  └── Products

📦 Purchasing
  ├── Penawaran Harga (PH)
  └── Purchase Order (PO)

💰 Penjualan (Sales)
  ├── Surat Jalan (SJ)
  ├── Invoice
  ├── ├─ PPN
  └── └─ Non-PPN

💵 Keuangan (Finance)
  ├── Piutang (+ badge jika ada overdue)
  └── Pembayaran

📦 Inventory
  ├── Stok Barang
  └── Stock Movement

📊 Laporan (Reports)
  ├── Laporan Penjualan
  └── Laporan Inventory

👤 User Management
  ├── Roles
  └── Users

⚙️ Admin
  └── Import Data
```

### 3. ✅ INVOICE MENU LEBIH INTUITIF

**Sebelum:**
```
Transaksi > Invoice PPN
Transaksi > Invoice Non-PPN
Transaksi > Invoice (Semua)
```

**Sesudah:**
```
Penjualan > Invoice (utama)
Penjualan > ├─ PPN (sub)
Penjualan > └─ Non-PPN (sub)
```

Lebih jelas hierarki dan tidak membingungkan.

### 4. ✅ BADGE NOTIFICATIONS

**Badge di Menu:**
- **Piutang** = Jumlah invoice overdue (merah)

**Badge di Top Bar Bell Icon:**
- Total semua notifikasi (stock + invoice)
- Tooltip menunjukkan detail

---

## 🔧 Setup & Konfigurasi

### 1. Run Migration (Jika Belum)
```bash
php artisan migrate
```

### 2. Setup Scheduler (PENTING!)

Agar notifikasi otomatis terkirim setiap 5 menit:

**Windows (Task Scheduler):**
```
Program: C:\php\php.exe
Arguments: C:\laragon\www\adamjaya\artisan schedule:run
Trigger: Every 1 minute
```

**Linux (Crontab):**
```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

### 3. Manual Trigger (Testing)
Untuk testing, bisa trigger manual:
```bash
php artisan notifications:send
```

### 4. Clear Cache
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

---

## 📱 Cara Menggunakan Notifikasi Top Bar

### Melihat Notifikasi:
1. Klik **bell icon** di top bar (samping profile)
2. Lihat semua notifikasi terbaru
3. Klik notifikasi untuk langsung ke page terkait

### Action dari Notifikasi:
- **Lihat Stock** - Langsung ke edit stock page
- **Lihat Invoice** - Langsung ke view invoice
- **Catat Pembayaran** - Langsung ke form payment dengan invoice pre-selected

### Mark as Read:
- Klik notifikasi = auto mark as read
- Atau klik "Mark all as read"

### Notifikasi Otomatis Terkirim Ketika:
✅ Stock available < minimum stock
✅ Produk kadaluarsa
✅ Produk akan kadaluarsa dalam 30 hari
✅ Invoice overdue
✅ Invoice akan jatuh tempo dalam 3 hari

---

## 🎨 Warna & Icon Notifikasi

### Icon:
- 🔴 **heroicon-o-exclamation-circle** = Invoice overdue
- 🔴 **heroicon-o-x-circle** = Stock expired
- 🟡 **heroicon-o-exclamation-triangle** = Stock low
- 🟡 **heroicon-o-clock** = Invoice due soon

### Warna Badge:
- **Danger (Merah)** = Urgent (overdue, expired, critical)
- **Warning (Kuning)** = Perlu perhatian (low stock, due soon)
- **Success (Hijau)** = Normal/OK

---

## 🔍 Troubleshooting

**Notifikasi tidak muncul di top bar:**
```bash
# 1. Check table notifications exists
php artisan migrate

# 2. Clear all caches
php artisan optimize:clear

# 3. Test manual send
php artisan notifications:send

# 4. Check scheduler running
php artisan schedule:list
```

**Badge tidak update:**
- Refresh browser (Ctrl + F5)
- Check session selected_company_id
- Clear browser cache

**Notifikasi duplikat:**
- Notifikasi otomatis check jika sudah ada sebelum create baru
- Jika tetap duplikat, truncate table: `TRUNCATE notifications;`

---

## 💡 Tips Penggunaan

### Best Practices:
1. **Check bell icon setiap pagi** untuk lihat notif penting
2. **Prioritas URGENT** (merah) untuk action immediately
3. **Setup minimum stock** dengan bijak (sesuai lead time supplier)
4. **Follow up invoice H-3** sebelum due date

### Notifications Workflow:
```
Pagi:
→ Buka app
→ Check bell icon (top bar)
→ Handle urgent (merah) dulu
→ Plan untuk warning (kuning)

Siang:
→ Check lagi setelah lunch
→ Follow up customer

Sore:
→ Final check before close
→ Input payments yang masuk
```

### Disable Notifications (if needed):
Jika ingin temporary disable auto notifications:

Edit `routes/console.php`:
```php
// Comment this line:
// Schedule::call(function () {
//     NotificationService::sendAllNotifications();
// })->everyFiveMinutes();
```

---

## ✅ Summary

### Yang BERUBAH:
✅ Notifikasi pindah ke top bar bell icon
✅ Menu sidebar lebih simple (tidak ada menu notifikasi)
✅ Invoice menu lebih hierarkis dengan sub-items
✅ Badge auto update dengan counter
✅ Notifications dengan action buttons

### Yang TETAP:
✅ Semua fitur invoice, stock, payment tetap sama
✅ Data tidak berubah
✅ Permissions tidak berubah
✅ Workflow bisnis tidak berubah

**Result: UX/UI lebih clean, modern, dan intuitive! 🎉**
