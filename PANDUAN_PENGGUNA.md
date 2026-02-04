# 📖 PANDUAN PENGGUNAAN SISTEM ERP - Adam Jaya

## 🎯 Navigasi Menu Utama

### 📊 Dashboard
**Halaman utama** yang menampilkan ringkasan bisnis Anda.

---

## 📁 Master Data
**Data dasar** yang digunakan di seluruh sistem.

### 1. 👥 Customer
- **Fungsi**: Kelola data customer/pembeli
- **Isi**: Nama, alamat, kontak, NPWP, jadwal penagihan
- **Kegunaan**: Untuk membuat Invoice dan Surat Jalan

### 2. 🏭 Supplier  
- **Fungsi**: Kelola data supplier/pemasok
- **Isi**: Nama, alamat, kontak, tipe (Local/Import)
- **Kegunaan**: Untuk membuat Purchase Order

### 3. 📦 Produk
- **Fungsi**: Master data produk/barang
- **Isi**: Kode produk, nama, harga, stok minimum, kategori
- **Kegunaan**: Digunakan di semua transaksi
- **Kolom Penting**:
  - **Kode Produk**: Kode internal perusahaan
  - **Kode Asli**: Kode dari supplier
  - **Harga Dasar**: Harga jual standar

---

## 🛒 Purchasing (Pembelian)
**Proses pembelian dari supplier**

### 1. 📋 Price Quotation (PH)
- **Fungsi**: Penawaran harga dari supplier
- **Status**: Draft → Approved → Rejected
- **Tipe**: PH Customer atau PH Supplier

### 2. 📝 Purchase Order (PO)
- **Fungsi**: Pesanan pembelian ke supplier
- **Tipe**: PPN atau Non-PPN
- **Status**: Pending → Partial → Completed
- **Jatuh Tempo**: Tanggal harus bayar ke supplier (menjadi HUTANG)

---

## 💰 Penjualan
**Proses penjualan ke customer**

### 1. 🚚 Surat Jalan (SJ)
- **Fungsi**: Dokumen pengiriman barang
- **Isi**: Produk, jumlah, driver, nomor kendaraan
- **Kegunaan**: Untuk membuat Invoice

### 2. 📄 Invoice
- **Fungsi**: Tagihan ke customer
- **Tipe**: 
  - **├─ PPN**: Invoice dengan pajak 11%
  - **└─ Non-PPN**: Invoice tanpa pajak
- **Status**: Unpaid → Partial → Paid
- **Jatuh Tempo**: Tanggal harus dibayar customer (menjadi PIUTANG)

### 3. 💳 Pembayaran
- **Fungsi**: Catat pembayaran dari customer
- **Metode**: Transfer, Tunai, Cek, Giro
- **Otomatis**: Update status invoice

---

## 💼 Keuangan
**Kelola keuangan perusahaan**

### 1. 💵 Piutang Usaha
- **Fungsi**: Monitor tagihan yang belum dibayar customer
- **Tampilan**: 
  - ✅ **Hijau**: Belum jatuh tempo
  - ⚠️ **Kuning**: Hampir jatuh tempo (3 hari)
  - ❌ **Merah**: Sudah lewat jatuh tempo
- **Badge Merah**: Jumlah piutang yang terlambat
- **Kegunaan**: Follow up pembayaran customer

### 2. 💸 Hutang Usaha
- **Fungsi**: Monitor tagihan yang harus dibayar ke supplier
- **Tampilan**: Sama seperti piutang
- **Badge Merah**: Jumlah hutang yang terlambat
- **Kegunaan**: Reminder bayar supplier

### 3. 💳 Pembayaran Hutang
- **Fungsi**: Catat pembayaran ke supplier
- **Metode**: Transfer, Tunai, Cek, Giro
- **Otomatis**: Update status PO

---

## 📦 Inventory
**Kelola stok barang**

### 1. 📊 Persediaan Barang
- **Fungsi**: Monitor stok barang
- **Isi**: 
  - Jumlah tersedia
  - Jumlah dipesan
  - Stok minimum
  - Batch number
  - Tanggal kadaluarsa
- **Warna**:
  - ⚠️ **Kuning**: Stok di bawah minimum
  - ❌ **Merah**: Barang kadaluarsa

### 2. 📈 Stock Movement
- **Fungsi**: Riwayat keluar-masuk barang
- **Tipe**: IN (Masuk) / OUT (Keluar)
- **Kegunaan**: Audit trail stok

---

## 🔔 Sistem Notifikasi
**Pengingat otomatis di bell icon**

### Jenis Notifikasi:

1. ⚠️ **Stok Rendah** (Kuning)
   - Produk dengan stok di bawah minimum
   - Tindakan: Buat PO untuk restocking

2. ❌ **Barang Kadaluarsa** (Merah)
   - Produk yang sudah lewat tanggal kadaluarsa
   - Tindakan: Cek dan musnahkan jika perlu

3. 💰 **Piutang Terlambat** (Merah)
   - Invoice yang lewat jatuh tempo
   - Tindakan: Follow up pembayaran customer

4. ⏰ **Piutang Hampir Jatuh Tempo** (Kuning)
   - Invoice yang jatuh tempo dalam 3 hari
   - Tindakan: Reminder ke customer

5. 💸 **Hutang Terlambat** (Merah)
   - PO yang lewat jatuh tempo
   - Tindakan: Segera bayar supplier

6. ⏰ **Hutang Hampir Jatuh Tempo** (Kuning)
   - PO yang jatuh tempo dalam 3 hari
   - Tindakan: Siapkan pembayaran

**Notifikasi dikirim otomatis setiap 5 menit**

---

## 🔄 Alur Kerja (Workflow)

### A. Proses Pembelian:
```
1. Supplier kirim Price Quotation (PH)
   ↓
2. Buat Purchase Order (PO) dari PH
   ↓
3. Barang datang → Stok bertambah otomatis
   ↓
4. Bayar ke Supplier → Catat di Pembayaran Hutang
   ↓
5. Status PO: Unpaid → Partial → Paid
```

### B. Proses Penjualan:
```
1. Buat Surat Jalan (SJ) untuk pengiriman
   ↓
2. Buat Invoice dari SJ
   ↓
3. Customer bayar → Catat di Pembayaran
   ↓
4. Status Invoice: Unpaid → Partial → Paid
```

---

## 💡 Tips Penting

### ✅ Best Practice:

1. **Cek Notifikasi Setiap Hari**
   - Buka bell icon di pojok kanan atas
   - Lihat piutang/hutang yang perlu ditindaklanjuti

2. **Monitor Stok Minimum**
   - Atur stok minimum sesuai penjualan
   - Buat PO sebelum stok habis

3. **Catat Tanggal Jatuh Tempo**
   - Selalu isi jatuh tempo di PO dan Invoice
   - Sistem akan reminder otomatis

4. **Batch & Expiry**
   - Isi batch number dan tanggal kadaluarsa untuk barang FMCG
   - Sistem akan notifikasi jika mendekati kadaluarsa

5. **Rekonsiliasi Rutin**
   - Cek menu Piutang/Hutang setiap minggu
   - Follow up pembayaran yang terlambat

---

## 🎨 Kode Warna

- 🟢 **Hijau**: Aman, normal
- 🟡 **Kuning**: Peringatan, perlu perhatian
- 🔴 **Merah**: Urgent, harus ditindaklanjuti
- 🔵 **Biru**: Informasi
- ⚫ **Abu-abu**: Tidak aktif/arsip

---

## 📞 Bantuan

Jika ada pertanyaan atau kendala, hubungi admin sistem.

### Command Berguna:
```bash
# Kirim notifikasi manual
php artisan notifications:send

# Clear cache sistem
php artisan optimize:clear

# Cek data testing
.\check-notifications.bat
```

---

**Sistem ERP Adam Jaya** v1.0
Update terakhir: 30 Januari 2026
