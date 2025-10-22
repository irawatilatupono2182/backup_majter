# Stock Synchronization dengan Surat Jalan - Update Guide

## Overview
Fitur baru untuk mendeteksi dan menyinkronkan stock dengan Surat Jalan yang statusnya Sent/Completed tapi belum memiliki Stock Movement.

## Fitur Baru di Stock Index

### 1. Kolom "Sinkronisasi SJ"

**Badge Indicator:**
- ✅ **Sync** (Green) - Semua Surat Jalan sudah tersinkronisasi
- ⚠️ **{n} SJ perlu sync** (Orange) - Ada Surat Jalan yang belum dibuat Stock Movement-nya

**Tooltip:**
Hover untuk melihat detail Surat Jalan yang perlu disinkronkan:
- Nomor SJ
- Status (Sent/Completed)
- Quantity yang dikirim

### 2. Action "Sync Surat Jalan"

**Lokasi:** Row action (muncul di baris stock yang perlu sync)

**Fungsi:**
- Otomatis membuat Stock Movement untuk semua Surat Jalan yang belum tersinkronisasi
- Mengurangi stock sesuai qty yang terkirim
- Mencatat reference ke Surat Jalan

**Modal Konfirmasi:**
- Menampilkan list Surat Jalan yang akan disinkronkan
- Informasi qty per SJ
- Warning bahwa stock akan dikurangi

**Validasi:**
- ✅ Cek stock availability sebelum sync
- ❌ Jika stock tidak cukup, transaksi dibatalkan
- ✅ Error message informatif

### 3. Bulk Action "Sync Surat Jalan (Bulk)"

**Lokasi:** Bulk action (setelah select multiple stocks)

**Fungsi:**
- Sync multiple products sekaligus
- Process semua Surat Jalan yang belum tersinkronisasi untuk produk yang dipilih
- Create Stock Movement secara batch

**Hasil:**
- Summary: Berapa movement dibuat, total stock dikurangi
- Error list: Jika ada yang gagal (stock tidak cukup, dll)

### 4. Filter "Perlu Sync Surat Jalan"

**Fungsi:** Menampilkan hanya stock yang memiliki Surat Jalan belum tersinkronisasi

**Use Case:**
- Quick access untuk menemukan stock yang perlu adjustment
- Bulk processing untuk sync sekaligus

## Cara Penggunaan

### Skenario 1: Manual Sync Individual Stock

1. Buka **Inventory > Stock**
2. Lihat kolom **Sinkronisasi SJ**
3. Jika ada badge **⚠️ {n} SJ perlu sync**, klik untuk lihat detail
4. Klik action **Sync Surat Jalan** di row tersebut
5. Review list SJ yang akan disinkronkan di modal
6. Klik **Ya, Sinkronkan**
7. System akan:
   - Validasi stock availability
   - Create Stock Movement records
   - Reduce stock quantity
   - Show notification hasil

### Skenario 2: Bulk Sync Multiple Stocks

1. Buka **Inventory > Stock**
2. Apply filter **Perlu Sync Surat Jalan**
3. Select stocks yang ingin disinkronkan (checkbox)
4. Klik **Bulk Actions > Sync Surat Jalan (Bulk)**
5. Konfirmasi di modal
6. System process semua stocks yang dipilih
7. Show summary hasil (success + errors)

### Skenario 3: Monitoring Unsynced Items

1. Apply filter **Perlu Sync Surat Jalan**
2. Review list stocks
3. Hover badge untuk lihat detail SJ
4. Prioritas sync berdasarkan qty atau urgency
5. Sync satu per satu atau bulk

## Business Logic

### Kapan Stock Perlu Sync?

Stock perlu sync jika:
1. ✅ Ada Delivery Note dengan status **Sent** atau **Completed**
2. ✅ Delivery Note tersebut memiliki item dengan product_id yang sama dengan stock
3. ❌ Belum ada Stock Movement dengan:
   - `reference_type` = 'delivery_note'
   - `reference_id` = sj_id dari Delivery Note tersebut
   - `product_id` yang sama

### Stock Movement yang Dibuat

Saat sync, system membuat Stock Movement dengan:
```php
[
    'company_id' => $companyId,
    'product_id' => $productId,
    'movement_type' => 'out',
    'quantity' => $qty_dari_delivery_note_item,
    'unit_cost' => $unit_price_dari_delivery_note_item,
    'reference_type' => 'delivery_note',
    'reference_id' => $sj_id,
    'notes' => "Manual sync - Pengiriman via Surat Jalan {sj_number} - {customer_name}",
    'created_by' => current_user_id,
]
```

### Stock Reduction

Stock dikurangi menggunakan method langsung (bukan FIFO):
```php
$stock->available_quantity -= $qty;
$stock->total_quantity -= $qty;
$stock->save();
```

**Note:** Berbeda dengan auto-sync di DeliveryNoteObserver yang menggunakan FIFO. Manual sync ini lebih sederhana karena hanya update satu stock record.

## Integration dengan Fitur Lain

### Delivery Note Observer
- Observer tetap berjalan untuk Delivery Note baru
- Manual sync hanya untuk data lama yang belum tersinkronisasi
- Tidak ada konflik karena cek `whereNotExists` stock_movements

### Stock Movement Index
- Movement yang dibuat via sync tercatat normal
- Ada marker di notes: "Manual sync" vs "Auto sync"
- Anomaly detection tetap bekerja

### Product Index
- Stock status badge tetap update setelah sync
- Filter by stock status responsive

## Error Handling

### Stock Tidak Cukup
```
Stock tidak mencukupi untuk produk '{product_name}'.
Tersedia: {available}, Dibutuhkan: {qty}
```
→ Transaksi dibatalkan, tidak ada perubahan

### Multiple Errors (Bulk)
```
{n} Stock Movement dibuat. Total stock dikurangi: {total} unit.

Error:
• Product A - SJ 001: Stock tidak cukup (Tersedia: 5, Dibutuhkan: 10)
• Product B - SJ 002: Stock tidak cukup (Tersedia: 0, Dibutuhkan: 3)
```
→ Yang berhasil tetap tersimpan, yang error di-skip

### Database Error
```
Error Sinkronisasi
Gagal sinkronisasi: {error_message}
```
→ Full rollback, tidak ada perubahan

## Performance Considerations

### Query Optimization
- Badge column menggunakan `whereNotExists` subquery
- Filter menggunakan `whereExists` dengan proper joins
- Action visibility check menggunakan count query

### Database Impact
- Multiple queries per row untuk badge (could be cached)
- Consider pagination untuk large datasets
- Index recommendation:
  - `delivery_notes.status`
  - `delivery_note_items.product_id`
  - `stock_movements.reference_type` + `reference_id`

### Caching Strategy (Future)
```php
// Cache unsynced count per product
Cache::remember("stock.{$productId}.unsynced_sj_count", 300, function() {
    // Query...
});
```

## Testing Checklist

### Manual Sync:
- [ ] Badge muncul untuk stock dengan unsynced SJ
- [ ] Tooltip menampilkan detail SJ yang benar
- [ ] Action visible hanya jika ada unsynced SJ
- [ ] Modal menampilkan list SJ dengan benar
- [ ] Validasi stock cukup sebelum sync
- [ ] Stock Movement created dengan data lengkap
- [ ] Stock quantity reduced correctly
- [ ] Success notification muncul
- [ ] Error notification jika stock tidak cukup

### Bulk Sync:
- [ ] Filter "Perlu Sync" menampilkan data yang tepat
- [ ] Bulk action visible di bulk actions menu
- [ ] Multiple stocks processed correctly
- [ ] Summary menampilkan total movements dan qty
- [ ] Error list menampilkan stocks yang gagal
- [ ] Partial success handling works (some success, some fail)

### Integration:
- [ ] Tidak conflict dengan DeliveryNoteObserver
- [ ] Stock Movement muncul di index dengan reference yang benar
- [ ] Anomaly detection tidak mendeteksi movement hasil sync sebagai anomali
- [ ] Badge update setelah sync (tidak ada lagi unsynced)

## Use Cases

### Use Case 1: Data Migration
**Situation:** Import data lama, Delivery Notes sudah Sent tapi belum ada Stock Movement

**Solution:**
1. Filter stocks dengan "Perlu Sync Surat Jalan"
2. Select all stocks
3. Bulk sync
4. Review hasil dan handle errors

### Use Case 2: Manual Override
**Situation:** DeliveryNote status diubah manual di database, bypass observer

**Solution:**
1. Badge akan mendeteksi unsynced SJ
2. User bisa manual trigger sync
3. Stock akan balance kembali

### Use Case 3: Error Recovery
**Situation:** Observer gagal karena error, transaksi rollback

**Solution:**
1. Fix underlying issue (stock quantity, dll)
2. Use manual sync untuk retry
3. Stock akan tersinkronisasi

## Monitoring Queries

### Check Total Unsynced Delivery Notes
```sql
SELECT 
    p.name as product_name,
    dn.sj_number,
    dn.status,
    dni.qty
FROM delivery_notes dn
JOIN delivery_note_items dni ON dn.sj_id = dni.sj_id
JOIN products p ON dni.product_id = p.product_id
WHERE dn.status IN ('Sent', 'Completed')
  AND NOT EXISTS (
    SELECT 1 FROM stock_movements sm
    WHERE sm.reference_type = 'delivery_note'
      AND sm.reference_id = dn.sj_id
      AND sm.product_id = dni.product_id
  )
ORDER BY dn.delivery_date DESC;
```

### Check Sync Success Rate
```sql
SELECT 
    COUNT(*) as total_movements,
    SUM(CASE WHEN notes LIKE '%Manual sync%' THEN 1 ELSE 0 END) as manual_sync,
    SUM(CASE WHEN notes NOT LIKE '%Manual sync%' THEN 1 ELSE 0 END) as auto_sync
FROM stock_movements
WHERE reference_type = 'delivery_note'
  AND created_at >= CURDATE();
```

## Future Enhancements

1. **Scheduled Sync Job**
   - Cron job untuk auto-sync unsynced SJ
   - Run setiap malam
   - Email report hasil

2. **Notification System**
   - Alert jika ada unsynced SJ > 24 jam
   - Dashboard widget untuk monitoring

3. **Audit Trail**
   - Log setiap manual sync action
   - Track who did what when

4. **Smart Sync**
   - Auto-suggest optimal sync order
   - Prioritize berdasarkan delivery_date, customer, etc

5. **Stock Reservation**
   - Reserve stock saat SJ dibuat (Draft)
   - Auto-sync saat status berubah

---

**Version:** 1.0  
**Date:** 2024-10-22  
**Related:** DELIVERY_NOTE_STOCK_INTEGRATION.md
