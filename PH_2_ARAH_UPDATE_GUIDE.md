# PH (Price Quotation) - 2 Arah System Update

## 📋 Summary

PH (Price Quotation) telah diupdate agar bisa digunakan untuk **2 arah**:
- 📤 **Untuk Customer** (Sales - Penawaran dari kita ke customer)
- 📥 **Untuk Supplier** (Purchasing - Minta penawaran dari supplier)

---

## ✅ Files Changed

### 1. **Migration**
📄 `database/migrations/2024_10_22_000001_add_entity_polymorphic_to_price_quotations_table.php`

**Changes:**
- Added `entity_type` column (customer/supplier)
- Added `entity_id` column (polymorphic)
- Added `customer_id` column
- Made `supplier_id` NULLABLE
- Migrated existing data (all marked as 'supplier')

### 2. **Model**
📄 `app/Models/PriceQuotation.php`

**Changes:**
- Added `entity_type`, `entity_id`, `customer_id` to fillable
- Added `entity()` morphTo relation
- Added `customer()` belongsTo relation
- Added helper methods:
  - `getEntityName()` - Get customer or supplier name
  - `isForCustomer()` - Check if for customer
  - `isForSupplier()` - Check if for supplier

### 3. **Resource**
📄 `app/Filament/Resources/PriceQuotationResource.php`

**Changes:**
- Added Customer model import
- Added entity_type selector in form (Customer/Supplier)
- Dynamic customer/supplier dropdown based on entity_type
- Auto sync entity_id when customer/supplier selected
- Updated table columns:
  - Added entity_type badge column
  - Dynamic entity_name column (shows customer or supplier)
- Added entity_type filter

### 4. **Documentation**
📄 `BUSINESS_FLOW_COMPLETE.md`

**Changes:**
- Added complete section explaining PH 2-arah system
- Updated TOC
- Added usage examples for both customer and supplier
- Added database schema documentation
- Version updated to 2.1

---

## 🚀 Deployment Steps

### Step 1: Run Migration
```bash
cd c:\laragon\www\adamjaya
php artisan migrate
```

**Expected Output:**
```
Migrating: 2024_10_22_000001_add_entity_polymorphic_to_price_quotations_table
Migrated:  2024_10_22_000001_add_entity_polymorphic_to_price_quotations_table (XX.XXs)
```

### Step 2: Clear Cache
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

### Step 3: Test in Browser
1. Login ke admin panel
2. Go to **Purchasing → Penawaran Harga (PH)**
3. Klik **New**
4. Test:
   - ✅ Pilih "Tipe Penawaran" = Customer → Muncul dropdown Customer
   - ✅ Pilih "Tipe Penawaran" = Supplier → Muncul dropdown Supplier
5. Create PH untuk customer dan supplier
6. Check table list → Should show badge (📤 Customer / 📥 Supplier)
7. Test filter by "Tipe Penawaran"

---

## 📊 Database Changes

### Before:
```sql
price_quotations (
    ph_id,
    company_id,
    supplier_id NOT NULL,  -- ❌ Always required
    quotation_number,
    ...
)
```

### After:
```sql
price_quotations (
    ph_id,
    company_id,
    entity_type VARCHAR(50),     -- ✅ NEW: 'customer' or 'supplier'
    entity_id UUID,              -- ✅ NEW: polymorphic ID
    customer_id UUID NULLABLE,   -- ✅ NEW: explicit customer
    supplier_id UUID NULLABLE,   -- ✅ Now nullable
    quotation_number,
    ...
)
```

---

## 🔄 Data Migration

**Existing PH records:**
All existing records will be automatically migrated to:
- `entity_type = 'supplier'`
- `entity_id = supplier_id`

**Query:**
```sql
UPDATE price_quotations 
SET entity_type = 'supplier',
    entity_id = supplier_id
WHERE supplier_id IS NOT NULL;
```

---

## 🎯 Usage Guide

### For Sales Staff (Customer PH):
```
1. New PH
2. Tipe Penawaran: "📤 Untuk Customer (Sales - Penawaran Jual)"
3. Customer: Pilih customer dari dropdown
4. Input items, harga
5. Save
```

### For Purchasing Staff (Supplier PH):
```
1. New PH
2. Tipe Penawaran: "📥 Untuk Supplier (Purchasing - Minta Penawaran Beli)"
3. Supplier: Pilih supplier dari dropdown
4. Input items, qty yang diminta
5. Save → Send ke supplier
6. Supplier reply dengan harga
7. Update status: Accepted
8. Create PO from this PH
```

---

## ⚠️ Important Notes

1. **Backward Compatibility**: ✅ Existing PH data tetap aman (auto migrated)
2. **Validation**: System auto validate customer/supplier based on entity_type
3. **Stock Impact**: PH (both types) **TIDAK** impact stock
4. **Next Steps**:
   - PH Customer → Buat SJ
   - PH Supplier → Buat PO

---

## 🐛 Troubleshooting

### Issue: Migration failed
**Solution:**
```bash
php artisan migrate:rollback --step=1
# Fix issue
php artisan migrate
```

### Issue: Dropdown tidak muncul
**Solution:**
- Clear browser cache
- Hard refresh (Ctrl+F5)
- Check console for JS errors

### Issue: Existing PH tidak bisa edit
**Solution:**
- Run migration again
- Check existing records have entity_type filled

---

## 📞 Support

Jika ada issue:
1. Check error log: `storage/logs/laravel.log`
2. Check browser console (F12)
3. Verify migration ran successfully
4. Clear all caches

---

**Date:** 22 Oktober 2025  
**Status:** ✅ Ready for Deployment
