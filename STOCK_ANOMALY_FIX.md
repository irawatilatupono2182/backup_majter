# Stock Anomaly Detection - Fix untuk False Positive

## Masalah
Stock anomaly report masih menampilkan item sebagai "perlu sync" walaupun stock movement sudah dibuat melalui fitur sinkronisasi manual.

## Root Cause
Query deteksi unsynced delivery notes memiliki 2 masalah:

1. **Missing movement_type check**: Query tidak memeriksa `movement_type`, sehingga tidak bisa membedakan apakah stock movement tersebut adalah OUT (pengiriman) atau IN (penerimaan)
2. **Case mismatch**: Query menggunakan `'OUT'` (uppercase) tapi database menyimpan `'out'` (lowercase)

## Solusi yang Diterapkan

### 1. Update Query Detection di StockResource.php

**Lokasi:** `app/Filament/Resources/StockResource.php`

**Perubahan:**
- Tambahkan kondisi `->where('sm.movement_type', 'out')` di semua query detection (lowercase!)
- Gunakan alias `sm` untuk table `stock_movements` agar lebih jelas
- Terapkan di 7 lokasi:
  1. Column `delivery_note_sync_status` - getStateUsing()
  2. Column `delivery_note_sync_status` - color()
  3. Column `delivery_note_sync_status` - tooltip()
  4. Filter `needs_sj_sync`
  5. Action `sync_delivery_notes` - modalDescription()
  6. Action `sync_delivery_notes` - action callback
  7. BulkAction `bulk_sync_delivery_notes`

### 2. Query BEFORE (SALAH)

```php
->whereNotExists(function ($query) {
    $query->select(\DB::raw(1))
        ->from('stock_movements')
        ->whereColumn('stock_movements.reference_id', 'dn.sj_id')
        ->where('stock_movements.reference_type', 'delivery_note')
        ->whereColumn('stock_movements.product_id', 'dni.product_id');
    // ❌ Tidak ada check movement_type
})
```

**Masalah:** 
- Query ini tidak memeriksa `movement_type`
- Akan tetap menganggap unsynced walaupun stock movement dengan `movement_type = 'out'` sudah ada

### 3. Query AFTER (BENAR)

```php
->whereNotExists(function ($query) {
    $query->select(\DB::raw(1))
        ->from('stock_movements as sm')
        ->whereColumn('sm.reference_id', 'dn.sj_id')
        ->where('sm.reference_type', 'delivery_note')
        ->whereColumn('sm.product_id', 'dni.product_id')
        ->where('sm.movement_type', 'out');  // ✅ ADDED (lowercase!)
})
```

**Perbaikan:**
- Tambah alias `sm` untuk clarity
- Tambah kondisi `movement_type = 'out'` (lowercase) untuk memastikan hanya OUT movement yang dianggap sebagai sync
- Sesuai dengan nilai yang disimpan di database oleh Observer

## Hasil
Setelah fix ini:
- ✅ Stock anomaly report akan menghapus item dari list setelah sync dilakukan
- ✅ Badge "✅ Sync" akan muncul dengan benar untuk stock yang sudah tersinkronisasi
- ✅ Filter "Perlu Sync Surat Jalan" hanya akan menampilkan item yang benar-benar belum sync

## Testing
1. Buat Surat Jalan dengan status Sent/Completed
2. Cek Stock index - harus muncul badge "⚠️ 1 SJ perlu sync"
3. Klik "Sync Surat Jalan" pada stock tersebut
4. Setelah sync berhasil, badge harus berubah menjadi "✅ Sync"
5. Item tidak boleh muncul lagi di filter "Perlu Sync Surat Jalan"

## Related Files
- `app/Filament/Resources/StockResource.php` - Main resource dengan query detection
- `app/Observers/DeliveryNoteObserver.php` - Auto sync observer yang membuat stock movement
- `app/Models/StockMovement.php` - Model dengan movement_type (IN/OUT)

## Date
2025-01-20
