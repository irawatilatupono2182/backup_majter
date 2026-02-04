# QUICK START GUIDE - Fitur Baru

## 🚀 Cara Menggunakan Fitur-Fitur Baru

### 1. Notifikasi Stock Minimum

**Mengakses:**
1. Login ke sistem
2. Buka sidebar menu
3. Klik **Notifikasi > Notifikasi Stok & Piutang**
4. Lihat badge merah di navigation (jika ada stock di bawah minimum)

**Mengatur Minimum Stock:**
1. Buka **Master Data > Products**
2. Edit produk yang ingin diatur
3. Scroll ke section "Tipe & Harga"
4. Set nilai **Minimum Stok Alert** (contoh: 10)
5. Save

**Sistem akan otomatis:**
- Menampilkan notifikasi jika `available_quantity < minimum_stock`
- Menampilkan badge merah dengan jumlah item yang perlu perhatian
- Memberikan quick action untuk create PO atau adjustment

---

### 2. Menu Piutang (Receivables)

**Mengakses:**
1. Buka **Keuangan > Piutang (Receivables)**
2. Lihat semua invoice yang belum lunas

**Informasi yang Ditampilkan:**
- Total invoice
- Jumlah yang sudah dibayar
- Sisa yang belum dibayar (bold, merah)
- Hari keterlambatan (untuk yang overdue)

**Quick Action:**
- Klik **Catat Pembayaran** untuk langsung input pembayaran
- Klik **View** untuk lihat detail invoice

**Filter Tersedia:**
- Status (Unpaid, Partial, Overdue)
- Jenis (PPN, Non-PPN)
- Hanya yang Jatuh Tempo

---

### 3. Invoice PPN dan Non-PPN Terpisah

**Menu Baru:**
- **Transaksi > Invoice PPN** - Hanya untuk invoice dengan PPN
- **Transaksi > Invoice Non-PPN** - Hanya untuk invoice tanpa PPN
- **Transaksi > Invoice (Semua)** - Semua invoice

**Cara Membuat Invoice PPN:**
1. Buka **Transaksi > Invoice PPN**
2. Klik **Create**
3. Isi form (tipe akan auto-set ke "PPN")
4. Save

**Cara Membuat Invoice Non-PPN:**
1. Buka **Transaksi > Invoice Non-PPN**
2. Klik **Create**
3. Isi form (tipe akan auto-set ke "Non-PPN")
4. Save

**Keuntungan:**
- Tidak perlu manual pilih tipe saat create
- Filter otomatis berdasarkan menu yang dipilih
- Lebih mudah untuk fokus pada jenis transaksi tertentu

---

### 4. Notifikasi Jatuh Tempo Invoice

**Mengakses:**
1. Buka **Notifikasi > Notifikasi Jatuh Tempo**
2. Lihat semua invoice yang akan/sudah jatuh tempo dalam 7 hari ke depan
3. Badge merah di navigation menunjukkan jumlah yang sudah overdue

**Status Waktu (dengan emoji):**
- 🔴 **X hari terlambat** - Invoice sudah lewat jatuh tempo
- 🟡 **Jatuh tempo hari ini!** - Harus dibayar hari ini
- 🟠 **1-3 hari lagi** - Warning, hampir jatuh tempo
- 🟢 **4-7 hari lagi** - Masih aman

**Filter:**
- **Sudah Jatuh Tempo** - Hanya yang overdue
- **Jatuh Tempo Hari Ini** - Hanya yang due date hari ini
- **Minggu Ini** - Semua dalam 7 hari ke depan

**Quick Action:**
- **Catat Pembayaran** - Langsung ke form payment
- **View** - Lihat detail invoice

**Best Practice:**
- Check menu ini setiap hari untuk monitor piutang
- Follow up customer yang invoice-nya akan jatuh tempo
- Prioritas yang sudah overdue (badge merah)

---

### 5. Kode Product Asal (Tambahan Kolom)

**Mengatur Kode Product:**
1. Buka **Master Data > Products**
2. Create atau Edit product
3. Akan ada 2 field:
   - **Kode Produk (Internal)** - Untuk sistem internal (required)
   - **Kode Produk (Asal)** - Kode asli dari supplier (optional)
4. Save

**Di Table:**
- Kolom **Kode (Internal)** - Selalu tampil
- Kolom **Kode (Asal)** - Bisa di-toggle show/hide
- Kedua kolom bisa di-search dan di-sort

**Use Case:**
- Internal code: "PRD-001"
- Original code: "HP-LAPTOP-15-FHD-i5" (kode dari supplier)

**Keuntungan:**
- Tidak perlu hapus kode lama
- Bisa track kode asli dari supplier
- Flexibility untuk dual coding system

---

### 6. Fitur Anomali Stock (Hidden)

**Status:**
- Menu "Anomali Stok" tidak lagi muncul di sidebar
- Fitur masih ada di backend (tidak dihapus)
- Bisa diakses via direct URL jika diperlukan (untuk admin)

**Alasan Hide:**
- Simplify navigation menu
- Fokus pada fitur yang lebih sering digunakan
- Mengurangi clutter di sidebar

---

## 🔔 Tips Penggunaan

### Monitoring Harian:
1. **Pagi:**
   - Check **Notifikasi Jatuh Tempo** (siapa yang harus dibayar hari ini)
   - Check **Notifikasi Stok & Piutang** (stock apa yang perlu restock)

2. **Siang:**
   - Follow up customer yang invoice-nya akan jatuh tempo
   - Create PO untuk stock yang low

3. **Sore:**
   - Review **Piutang (Receivables)** untuk planning penagihan besok
   - Input payment yang masuk hari ini

### Best Practices:
- Set **Minimum Stok Alert** sesuai dengan lead time supplier (contoh: jika lead time 1 minggu, set minimum untuk stok 1 minggu)
- Follow up invoice **H-3** sebelum jatuh tempo
- Prioritas payment reminder untuk customer yang sering telat
- Review receivables setiap akhir minggu

### Badge Notifications:
- 🔴 **Badge Merah** = Urgent, butuh action sekarang
- Jumlah di badge = jumlah item yang perlu perhatian
- Badge di **Notifikasi Jatuh Tempo** = jumlah invoice overdue
- Badge di **Notifikasi Stok** = jumlah stock below minimum
- Badge di **Piutang** = jumlah invoice overdue

---

## ❓ FAQ

**Q: Bagaimana cara mengubah minimum stock alert?**  
A: Edit product > section "Tipe & Harga" > field "Minimum Stok Alert"

**Q: Notifikasi jatuh tempo menampilkan berapa hari ke depan?**  
A: 7 hari ke depan (termasuk yang sudah overdue)

**Q: Apakah bisa search by kode product asal?**  
A: Ya, kedua kolom (Internal dan Asal) bisa di-search

**Q: Badge tidak muncul, kenapa?**  
A: Badge hanya muncul jika ada item yang perlu perhatian (stock low atau invoice overdue)

**Q: Cara menyembunyikan kolom "Kode (Asal)"?**  
A: Klik icon toggle columns di table, uncheck "Kode (Asal)"

**Q: Apakah menu Invoice lama masih bisa digunakan?**  
A: Ya, menu "Invoice (Semua)" masih ada dan bisa create PPN/Non-PPN secara manual

**Q: Notifikasi akan hilang setelah action?**  
A: Ya, setelah stock ditambah atau invoice dibayar, notifikasi akan hilang otomatis

---

## 🐛 Troubleshooting

**Badge tidak update:**
```bash
php artisan cache:clear
php artisan config:clear
```

**Menu baru tidak muncul:**
```bash
php artisan optimize:clear
Refresh browser (Ctrl + F5)
```

**Error saat create invoice PPN/Non-PPN:**
- Pastikan migration sudah running
- Check session selected_company_id
- Check user permissions

**Kolom "Kode (Asal)" tidak muncul:**
```bash
php artisan migrate
php artisan cache:clear
```

---

**Happy using! 🎉**
