# Laporan Anomali Stok - Dokumentasi

## 📋 Tujuan
Mendeteksi dan melaporkan **selisih/anomali** ketika:
- Surat Jalan sudah dikirim (status: delivered)
- Tetapi gudang **LUPA mencatat** di Stock Movement
- Menyebabkan data stok tidak akurat

## 🎯 Fitur Utama

### 1. **Deteksi Otomatis**
System otomatis mendeteksi surat jalan yang:
- Status: `delivered` (sudah terkirim)
- Memiliki item yang **belum tercatat** di stock_movements
- Menghitung jumlah item dan quantity yang hilang

### 2. **Informasi yang Ditampilkan**

| Kolom | Keterangan |
|-------|------------|
| **Tanggal Kirim** | Kapan barang dikirim |
| **No. Surat Jalan** | Nomor dokumen pengiriman (dapat di-copy) |
| **No. Invoice** | Link ke invoice terkait |
| **Customer** | Nama customer penerima |
| **Jumlah Item** | Total item di surat jalan |
| **Item Belum Tercatat** | Badge merah: berapa item yang lupa dicatat |
| **Total Qty Belum Tercatat** | Total quantity/unit yang hilang |
| **Status** | `Lengkap` / `Sebagian` / `Belum Tercatat Sama Sekali` |
| **Detail Item Bermasalah** | List produk mana saja yang lupa dicatat |

### 3. **Filter**

#### Filter Status Anomali:
- **Lengkap**: Tidak ada anomali, semua sudah tercatat ✅
- **Sebagian Belum Tercatat**: Ada beberapa item yang lupa ⚠️
- **Belum Tercatat Sama Sekali**: Semua item lupa dicatat 🚨

#### Filter Customer:
- Filter berdasarkan customer tertentu
- Berguna untuk audit per customer

### 4. **Badge Notifikasi**
- Badge **merah** di menu navigasi
- Menunjukkan jumlah surat jalan yang bermasalah
- Update otomatis

## 🔍 Cara Kerja

### Flow Normal (Tidak Ada Anomali):
```
1. Buat Surat Jalan → Status: pending
2. Kirim Barang → Status: delivered
3. Gudang Catat di Stock Movement:
   - reference_type: 'delivery_note_item'
   - reference_id: sj_item_id
   - movement_type: 'out'
   - quantity: sesuai surat jalan
4. ✅ Tidak muncul di laporan anomali
```

### Flow Bermasalah (Ada Anomali):
```
1. Buat Surat Jalan → Status: pending
2. Kirim Barang → Status: delivered
3. ❌ Gudang LUPA catat di Stock Movement
4. 🚨 MUNCUL di Laporan Anomali dengan badge merah
```

## 📊 Contoh Kasus

### Kasus 1: Lupa Total
```
Surat Jalan: SJ-2025-001
Tanggal: 21 Okt 2025
Customer: PT Maju Jaya
Item: 
  - Laptop HP (5 unit) ❌ Belum tercatat
  - Mouse Logitech (10 unit) ❌ Belum tercatat

Status: "Belum Tercatat Sama Sekali" (Badge Merah)
Item Belum Tercatat: 2
Total Qty: 15 unit
```

### Kasus 2: Lupa Sebagian
```
Surat Jalan: SJ-2025-002
Customer: CV Berkah
Item:
  - Keyboard Mechanical (3 unit) ✅ Sudah tercatat
  - Monitor 24" (2 unit) ❌ Belum tercatat

Status: "Sebagian Belum Tercatat" (Badge Kuning)
Item Belum Tercatat: 1
Total Qty: 2 unit
```

## 🛠️ Cara Mengatasi Anomali

### Step 1: Buka Laporan Anomali
- Menu: **Laporan → Anomali Stok**
- Lihat badge merah (jika ada)

### Step 2: Identifikasi Masalah
- Klik row untuk lihat detail
- Catat nomor surat jalan dan item yang bermasalah
- Lihat kolom "Detail Item Bermasalah"

### Step 3: Perbaiki Data
#### Opsi A: Input Manual di Stock Movement
1. Buka menu **Inventory → Stock Movement**
2. Klik **New Stock Movement**
3. Pilih movement_type: **out** (barang keluar)
4. Pilih produk yang lupa dicatat
5. Input quantity sesuai surat jalan
6. Di notes tulis: "Koreksi SJ-xxxx - lupa catat"
7. Save

#### Opsi B: Bulk Fix (Jika banyak)
```sql
-- Contoh manual fix via SQL (gunakan dengan hati-hati)
INSERT INTO stock_movements (
    stock_movement_id, 
    company_id, 
    product_id, 
    movement_type, 
    quantity,
    reference_type,
    reference_id,
    notes,
    created_by
) VALUES (
    UUID(),
    'company_id_here',
    'product_id_here',
    'out',
    5,
    'delivery_note_item',
    'sj_item_id_here',
    'Koreksi: lupa catat saat pengiriman',
    'user_id_here'
);
```

### Step 4: Verifikasi
- Refresh halaman Anomali Stok
- Row yang sudah diperbaiki akan hilang
- Badge berkurang atau hilang

## 🎨 Warna Status

| Status | Warna | Icon | Arti |
|--------|-------|------|------|
| Lengkap | 🟢 Hijau | ✅ | Semua tercatat |
| Sebagian | 🟡 Kuning | ⚠️ | Ada yang lupa |
| Belum Sama Sekali | 🔴 Merah | 🚨 | Total lupa |

## 📈 Best Practices

### Preventif (Mencegah):
1. ✅ Gudang harus checklist setelah input stock movement
2. ✅ Manager review laporan anomali setiap hari
3. ✅ SOP: Setiap surat jalan = 1 stock movement
4. ✅ Training staff gudang tentang pentingnya pencatatan

### Detective (Deteksi):
1. ✅ Cek badge merah di menu Anomali Stok setiap pagi
2. ✅ Filter berdasarkan tanggal untuk audit periodik
3. ✅ Export data anomali untuk laporan manajemen

### Corrective (Perbaikan):
1. ✅ Prioritaskan anomali terbaru (hari ini/kemarin)
2. ✅ Koordinasi dengan driver/pengirim untuk validasi
3. ✅ Update stock movement dengan notes jelas
4. ✅ Verifikasi stock fisik jika perlu

## 🔐 Relasi Database

### Tabel yang Terlibat:
```
delivery_notes (surat_jalan)
  └── delivery_note_items (sj_items)
       └── stock_movements (reference_type = 'delivery_note_item')
            └── reference_id = sj_item_id
```

### Query Utama:
```php
// Mencari item yang belum punya stock movement
DeliveryNoteItem::whereDoesntHave('stockMovements')
    ->whereHas('deliveryNote', function($q) {
        $q->where('status', 'delivered');
    })
```

## 🚨 Alert Levels

| Jumlah Anomali | Level | Action |
|----------------|-------|--------|
| 0 | 🟢 Normal | Maintain |
| 1-5 | 🟡 Warning | Review dalam 24 jam |
| 6-10 | 🟠 Caution | Immediate review |
| >10 | 🔴 Critical | Urgent meeting + audit |

## 📝 Checklist Harian Manager

- [ ] Cek badge merah Anomali Stok
- [ ] Review anomali hari ini
- [ ] Follow up dengan tim gudang
- [ ] Verifikasi perbaikan sudah benar
- [ ] Update SOP jika diperlukan

## 💡 Tips

1. **Jangan panik**: Anomali bisa diperbaiki dengan input ulang
2. **Dokumentasi**: Selalu tulis notes lengkap saat koreksi
3. **Validasi**: Cek stok fisik jika ada keraguan
4. **Komunikasi**: Koordinasi dengan tim terkait
5. **Preventif**: Fokus ke pencegahan, bukan hanya perbaikan

---

**Developed by**: Adam Jaya ERP Team
**Last Updated**: October 21, 2025
