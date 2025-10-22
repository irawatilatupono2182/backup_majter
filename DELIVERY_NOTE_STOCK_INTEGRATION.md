# Integrasi Surat Jalan dan Stock Movement

## Overview
Sistem telah diupdate agar Surat Jalan (Delivery Note) terintegrasi otomatis dengan Stock Movement. Saat status Surat Jalan berubah ke **Sent** atau **Completed**, sistem akan:
1. Otomatis membuat Stock Movement (OUT)
2. Mengurangi stock available dan total quantity menggunakan metode FIFO
3. Mencatat referensi ke Surat Jalan

## Cara Kerja

### 1. Status Draft → Sent/Completed

**Trigger:** User mengubah status Surat Jalan dari "Draft" ke "Sent" atau "Completed"

**Proses Otomatis:**
1. ✅ Validasi stock availability untuk setiap item (hanya produk STOCK)
2. ✅ Buat Stock Movement record dengan:
   - `movement_type` = 'out'
   - `reference_type` = 'delivery_note'
   - `reference_id` = sj_id
   - `quantity` = qty dari delivery note item
3. ✅ Kurangi stock menggunakan FIFO (First In, First Out)
   - Mengurangi `available_quantity` dan `quantity` (bukan `total_quantity`)
   - Dimulai dari stock yang paling lama (created_at ASC)
   - **Note:** Tabel `stocks` menggunakan kolom `quantity`, bukan `total_quantity`
4. ✅ Notifikasi sukses ke user

**Error Handling:**
- ❌ Jika stock tidak mencukupi → Transaksi dibatalkan (rollback)
- ❌ Error message ditampilkan: "Stock tidak mencukupi untuk produk 'X'. Tersedia: Y, Dibutuhkan: Z"
- ❌ Notifikasi persistent error muncul

### 2. Status Sent/Completed → Draft (Reversal)

**Trigger:** User mengubah status kembali ke "Draft"

**Proses Otomatis:**
1. ✅ Cari semua Stock Movement yang terkait dengan Delivery Note ini
2. ✅ Kembalikan stock (restore):
   - Tambah kembali `available_quantity` dan `quantity`
3. ✅ Hapus Stock Movement records
4. ✅ Notifikasi warning ke user

### 3. Delete Delivery Note

**Trigger:** User menghapus Delivery Note dengan status Sent/Completed

**Proses Otomatis:**
- Sama seperti reversal, stock dikembalikan dan movement dihapus

## Implementasi Teknis

### File-file yang Dimodifikasi:

1. **`app/Observers/DeliveryNoteObserver.php`** (NEW)
   - Observer untuk monitoring perubahan DeliveryNote
   - Method `updated()`: Handle status changes
   - Method `deleted()`: Handle deletion
   - Method `processDeliveryAndCreateStockMovements()`: Create stock movements
   - Method `reduceStock()`: FIFO stock reduction
   - Method `reverseStockMovements()`: Restore stock

2. **`app/Providers/AppServiceProvider.php`**
   - Register DeliveryNoteObserver
   - `DeliveryNote::observe(DeliveryNoteObserver::class)`

3. **`app/Filament/Resources/DeliveryNoteResource.php`**
   - Added helper text pada status field
   - Added `stock_movement_status` column di table
   - Badge color indicator (green = success, orange = warning)
   - Tooltip dengan informasi lengkap

4. **`app/Filament/Resources/DeliveryNoteResource/Pages/EditDeliveryNote.php`**
   - Method `afterSave()`: Show success/warning notification
   - Method `onValidationError()`: Handle stock errors

5. **`app/Filament/Resources/DeliveryNoteResource/Pages/ViewDeliveryNote.php`**
   - Added "Stock Movement" section
   - Display stock movement status dan details

## Business Rules

### Validasi:
1. ✅ Hanya produk **STOCK** yang memerlukan stock movement (CATALOG skip)
2. ✅ Stock harus mencukupi sebelum status diubah ke Sent/Completed
3. ✅ Tidak boleh ada duplicate stock movement untuk delivery note yang sama
4. ✅ FIFO method: Stock yang lebih dulu masuk, keluar terlebih dahulu

### Database Transaction:
- Semua operasi dibungkus dalam DB transaction
- Jika error di tengah proses → full rollback
- Memastikan data consistency

## User Experience

### Di Form Create/Edit Delivery Note:

**Status Field:**
- Helper text: "⚠️ Mengubah status ke Sent/Completed akan otomatis membuat stock movement dan mengurangi stock. Mengubah kembali ke Draft akan mengembalikan stock."

**Notifications:**
- ✅ Success: "Surat Jalan Terkirim - Stock movement telah dibuat dan stock telah dikurangi secara otomatis."
- ⚠️ Warning: "Status Dikembalikan - Stock movement telah dibatalkan dan stock telah dikembalikan."
- ❌ Error: "Error: Stock Tidak Mencukupi - Tidak dapat mengubah status karena stock tidak tersedia."

### Di Index Table:

**Kolom "Stock Movement":**
- ✅ "{n} records" (green badge) = Stock movement sudah dibuat
- ⚠️ "Belum ada" (orange badge) = Status Sent/Completed tapi belum ada movement (anomali)
- "-" (gray) = Status masih Draft (normal)

**Tooltip:**
- Hover untuk melihat penjelasan status

### Di View Page:

**Section "Stock Movement":**
- Status stock movement (badge dengan icon)
- Detail movement per produk
- Hanya visible jika status Sent/Completed

## Testing Checklist

### Skenario Normal:
- [ ] Create Delivery Note dengan status Draft
- [ ] Ubah status ke Sent → Stock movement created, stock berkurang
- [ ] Cek Stock Movement index → ada record baru
- [ ] Cek Stock index → available_quantity dan total_quantity berkurang
- [ ] Ubah status kembali ke Draft → Stock movement deleted, stock kembali
- [ ] Ubah lagi ke Completed → Stock movement created lagi

### Skenario Error:
- [ ] Create Delivery Note dengan qty > available stock
- [ ] Ubah status ke Sent → Error muncul, transaksi dibatalkan
- [ ] Notifikasi error ditampilkan
- [ ] Stock tidak berubah

### Edge Cases:
- [ ] Delivery Note dengan produk CATALOG → Skip stock movement
- [ ] Delivery Note mixed (STOCK + CATALOG) → Hanya STOCK yang diproses
- [ ] Multiple stock records untuk satu produk → FIFO bekerja
- [ ] Delete Delivery Note dengan status Sent → Stock dikembalikan

## Integration dengan Fitur Lain

### Stock Movement Resource:
- Filter `reference_type` = 'delivery_note' untuk melihat movement dari SJ
- Anomaly detection untuk movement tanpa reference

### Product Selection:
- Dropdown di Delivery Note form hanya tampilkan produk dengan stock > 0
- Validasi qty tidak melebihi available stock
- Helper text menampilkan stock tersedia

### Stock Resource:
- Anomaly detection untuk mendeteksi stock yang tidak balance
- Stock movements tercatat dengan proper reference

## Monitoring & Maintenance

### Log Files:
- Success: `"Stock movements created for Delivery Note {sj_number}"`
- Error: `"Failed to create stock movements for Delivery Note {sj_number}: {error}"`
- Stock reduction: `"Reduced stock for product {product_id}: {qty} units from stock {stock_id}"`
- Reversal: `"Stock movements reversed for Delivery Note {sj_number}"`

### Database Queries untuk Monitoring:

```sql
-- Check delivery notes without stock movement (anomaly)
SELECT sj_number, status, customer_id 
FROM delivery_notes 
WHERE status IN ('Sent', 'Completed')
  AND sj_id NOT IN (
    SELECT reference_id 
    FROM stock_movements 
    WHERE reference_type = 'delivery_note'
  );

-- Check stock movements for a delivery note
SELECT * FROM stock_movements 
WHERE reference_type = 'delivery_note' 
  AND reference_id = 'your-sj-id';

-- Check total stock movements by delivery note
SELECT reference_id, COUNT(*) as movement_count
FROM stock_movements 
WHERE reference_type = 'delivery_note'
GROUP BY reference_id;
```

## Troubleshooting

### Issue: Stock movement tidak dibuat saat ubah status

**Kemungkinan Penyebab:**
1. Observer tidak terdaftar di AppServiceProvider
2. Status tidak berubah dari Draft ke Sent/Completed
3. Semua produk adalah CATALOG (skip)

**Solusi:**
- Check AppServiceProvider.boot() → DeliveryNote::observe()
- Check logs di storage/logs/laravel.log
- Verify status change di database

### Issue: Error "Stock tidak mencukupi" padahal stock ada

**Kemungkinan Penyebab:**
1. Stock reserved terlalu banyak
2. Multiple delivery note belum di-sync
3. Stock di company_id yang berbeda

**Solusi:**
- Check `available_quantity` bukan `total_quantity`
- Verify company_id session
- Run stock reconciliation

### Issue: Stock tidak kembali saat reversal

**Kemungkinan Penyebab:**
1. Stock movement sudah dihapus manual
2. Error di tengah proses (rollback)

**Solusi:**
- Check stock_movements table
- Manual adjustment stock jika perlu
- Check error logs

## Future Enhancements

1. **Batch Processing:**
   - Bulk status update dengan stock movement batch

2. **Notification System:**
   - Email notification ke warehouse saat ada delivery
   - Alert jika stock hampir habis

3. **Stock Reservation:**
   - Reserve stock saat Delivery Note dibuat (Draft)
   - Release reservation jika tidak jadi terkirim

4. **Audit Trail:**
   - Detailed log untuk setiap stock movement
   - Who, when, what, why

5. **Analytics:**
   - Dashboard untuk delivery performance
   - Stock movement trends

---

**Version:** 1.0  
**Date:** 2024-10-22  
**Status:** ✅ Active
