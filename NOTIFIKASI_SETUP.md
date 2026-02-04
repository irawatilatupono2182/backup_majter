# Quick Setup Guide - Notifikasi Top Bar

## ✅ Setup Cepat (5 Menit)

### 1. Run Migration
```bash
php artisan migrate
```

### 2. Test Notifikasi Manual
```bash
php artisan notifications:send
```

### 3. Check Top Bar
- Buka aplikasi
- Lihat **bell icon** di sebelah profile (top right)
- Klik untuk lihat notifikasi

### 4. Setup Auto-Send (Optional)

**Windows - Task Scheduler:**
1. Buka Task Scheduler
2. Create Basic Task
3. Name: "Laravel Notifications"
4. Trigger: Daily, repeat every 5 minutes
5. Action: Start Program
   - Program: `C:\php\php.exe` (sesuaikan path PHP)
   - Arguments: `C:\laragon\www\adamjaya\artisan notifications:send`

**Atau pakai Scheduler Laravel:**
```bash
# Test dulu
php artisan schedule:list

# Lihat list scheduled tasks
# Seharusnya ada: "Closure" every 5 minutes
```

---

## 🔔 Cara Kerja

### Notifikasi Otomatis Terkirim Untuk:

1. **Stock Rendah**
   - Trigger: `available_quantity < minimum_stock`
   - Icon: 🟡 Warning
   - Action: "Lihat Stock"

2. **Produk Kadaluarsa**
   - Trigger: `expiry_date < today`
   - Icon: 🔴 Danger
   - Action: "Lihat Stock"

3. **Invoice Overdue**
   - Trigger: `due_date < today AND status = Unpaid/Partial`
   - Icon: 🔴 Danger
   - Actions: "Lihat Invoice", "Catat Pembayaran"

4. **Invoice Due Soon**
   - Trigger: `due_date dalam 1-3 hari`
   - Icon: 🟡 Warning
   - Action: "Lihat Invoice"

### Kapan Notifikasi Dikirim:
- **Manual**: `php artisan notifications:send`
- **Auto**: Setiap 5 menit (jika scheduler aktif)
- **On-demand**: Bisa dipanggil dari code

---

## 📱 Menggunakan Notifikasi

### Di Top Bar:
1. **Bell Icon** menunjukkan jumlah unread notifications
2. **Klik bell** = dropdown muncul dengan list
3. **Klik notifikasi** = mark as read + redirect ke page
4. **Klik action button** = action langsung (view/payment)

### Mark as Read:
- Otomatis saat klik notification
- Atau klik "Mark all as read" di dropdown

---

## 🎯 Menu yang TIDAK ADA Lagi:

❌ ~~Notifikasi > Notifikasi Stok & Piutang~~ (DIHAPUS)
❌ ~~Notifikasi > Notifikasi Jatuh Tempo~~ (DIHAPUS)

**Semua notifikasi sekarang di TOP BAR saja!**

---

## ✅ Testing Checklist

- [ ] Run `php artisan migrate` sukses
- [ ] Run `php artisan notifications:send` sukses
- [ ] Bell icon muncul di top bar (samping profile)
- [ ] Badge number muncul di bell icon
- [ ] Klik bell = dropdown notifikasi muncul
- [ ] Klik notifikasi = redirect ke page yang benar
- [ ] Action button berfungsi
- [ ] Mark as read berfungsi
- [ ] No menu "Notifikasi" di sidebar

---

## 🐛 Troubleshooting

**Bell icon tidak muncul:**
```bash
php artisan optimize:clear
```

**Badge tidak ada angka:**
- Belum ada notifikasi
- Atau run: `php artisan notifications:send`

**Notifikasi tidak muncul setelah send:**
- Clear cache: `php artisan config:clear`
- Refresh browser: Ctrl + F5
- Check table notifications: `SELECT * FROM notifications LIMIT 10;`

**Error "Table notifications doesn't exist":**
```bash
php artisan migrate
```

---

**Done! Notifikasi sekarang hanya di top bar bell icon! 🔔**
