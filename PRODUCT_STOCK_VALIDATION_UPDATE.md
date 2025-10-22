# Product & Stock Validation Update

## 📋 Summary

Implementasi validasi dan logic untuk memastikan:
1. ✅ Product tipe STOCK otomatis memiliki record di tabel stocks
2. ✅ PH untuk Supplier: menampilkan SEMUA product aktif
3. ✅ PH untuk Customer: menampilkan HANYA product dengan stock tersedia
4. ✅ Validasi qty tidak boleh melebihi stock tersedia (untuk customer)

---

## ✅ Changes Implemented

### 1. **ProductResource - Auto Create Stock Record**

📄 `app/Filament/Resources/ProductResource/Pages/CreateProduct.php`
📄 `app/Filament/Resources/ProductResource/Pages/EditProduct.php`

**Logic:**
- Saat create/edit product dengan `product_type = 'STOCK'`
- System otomatis check apakah ada record di tabel `stocks`
- Jika TIDAK ada → Auto create dengan `quantity = 0`
- User dapat notifikasi "Stock record telah dibuat"

**Code:**
```php
protected function afterCreate(): void
{
    if ($product->product_type === 'STOCK') {
        if (!existingStock) {
            Stock::create([
                'company_id' => $product->company_id,
                'product_id' => $product->product_id,
                'quantity' => 0,
                'available_quantity' => 0,
                'reserved_quantity' => 0,
            ]);
        }
    }
}
```

---

### 2. **PriceQuotationResource - Dynamic Product Selection**

📄 `app/Filament/Resources/PriceQuotationResource.php`

**Logic Product Dropdown:**

#### **A. PH untuk SUPPLIER (entity_type = 'supplier')**
```php
// Show ALL active products (no stock filter)
Product::where('company_id', $companyId)
    ->where('is_active', true)
    ->orderBy('name')
    ->pluck('name', 'product_id');
```

**Reasoning:**
- Supplier PH untuk minta penawaran BELI
- Tidak perlu validasi stock (karena mau beli)
- Show semua product (STOCK + CATALOG)

#### **B. PH untuk CUSTOMER (entity_type = 'customer')**
```php
// Show only products with available stock OR catalog
Product::where('company_id', $companyId)
    ->where('is_active', true)
    ->where(function ($query) {
        // Include CATALOG products (always available)
        $query->where('product_type', 'CATALOG')
            // OR STOCK products with available_quantity > 0
            ->orWhere(function ($q) {
                $q->where('product_type', 'STOCK')
                    ->whereHas('stock', function ($stockQuery) {
                        $stockQuery->where('available_quantity', '>', 0);
                    });
            });
    })
    ->pluck('name', 'product_id');
```

**Reasoning:**
- Customer PH untuk penawaran JUAL
- Hanya show product yang ready dijual
- CATALOG: always available (tidak perlu stock)
- STOCK: hanya jika `available_quantity > 0`

---

### 3. **Stock Info Display**

**Features:**

#### **A. Show Available Stock (Customer Only)**
```php
// After product selected, show stock info
$product = Product::with('stock')->find($state);

if ($entityType === 'customer' && $product) {
    if ($product->product_type === 'STOCK' && $product->stock) {
        $availableQty = $product->stock->available_quantity;
        $set('_stock_info', "Stok tersedia: {$availableQty} {$product->unit}");
    } elseif ($product->product_type === 'CATALOG') {
        $set('_stock_info', "Produk CATALOG (tidak perlu stok)");
    }
}
```

**Display:**
```
Placeholder component:
- Label: "Info Stok"
- Content: "Stok tersedia: 50 pcs"
- Visible: Only for customer PH
```

#### **B. Helper Text**
```
Product dropdown helper:
- Supplier: "📦 Menampilkan semua produk aktif"
- Customer: "✅ Hanya menampilkan produk dengan stok tersedia"

Qty field helper:
- Customer: "⚠️ Qty tidak boleh melebihi stok tersedia"
- Supplier: (no helper)
```

---

### 4. **Qty Validation (Customer Only)**

**Logic:**
```php
->rules([
    function (Forms\Get $get) {
        return function ($attribute, $value, $fail) use ($get) {
            $entityType = $get('../../entity_type');
            $productId = $get('product_id');
            
            if ($entityType === 'customer' && $productId) {
                $product = Product::with('stock')->find($productId);
                
                if ($product && $product->product_type === 'STOCK') {
                    $availableQty = $product->stock->available_quantity;
                    
                    if ($value > $availableQty) {
                        $fail("Qty melebihi stok tersedia ({$availableQty} {$product->unit}).");
                    }
                }
            }
        };
    },
])
```

**Validation Rules:**
- ✅ Only validate for customer PH
- ✅ Only validate for STOCK products
- ✅ CATALOG products: no validation (always OK)
- ✅ Error message: "Qty melebihi stok tersedia (50 pcs)."

---

## 🎯 User Experience

### **Scenario 1: Create Product (Type STOCK)**
```
1. User: Create new product "Laptop HP 15"
2. User: Select product_type = "STOCK"
3. User: Save
4. System: ✅ Auto create stock record (qty = 0)
5. System: Show notification "Stock record telah dibuat"
```

### **Scenario 2: PH untuk Supplier (Minta Penawaran)**
```
1. User: New PH
2. User: Tipe = "📥 Untuk Supplier"
3. User: Add item → Select product
4. System: Show ALL active products (100 products)
   - Laptop HP 15 (stock: 0) ✅ Visible
   - Server Dell (stock: 0) ✅ Visible
   - Mouse (CATALOG) ✅ Visible
5. User: Input qty = 20 (any qty, no limit)
6. System: ✅ Save tanpa validasi stock
```

### **Scenario 3: PH untuk Customer (Penawaran Jual)**
```
1. User: New PH
2. User: Tipe = "📤 Untuk Customer"
3. User: Add item → Select product
4. System: Show ONLY products with stock > 0 (50 products)
   - Laptop HP 15 (stock: 10) ✅ Visible
   - Server Dell (stock: 0) ❌ NOT Visible
   - Mouse (CATALOG) ✅ Visible (always show)
5. User: Select "Laptop HP 15"
6. System: Show "Stok tersedia: 10 unit"
7. User: Input qty = 5 ✅ OK (< 10)
8. User: Input qty = 15 ❌ ERROR "Qty melebihi stok tersedia (10 unit)"
```

---

## 📊 Database Impact

### **Stocks Table - Auto Created**
```sql
-- When create Product with product_type = 'STOCK'
INSERT INTO stocks (
    company_id,
    product_id,
    quantity = 0,
    available_quantity = 0,
    reserved_quantity = 0
)
```

### **Product Queries**

**Before (Old):**
```sql
-- Always show all products
SELECT * FROM products 
WHERE company_id = ? 
  AND is_active = true;
```

**After (New - Supplier PH):**
```sql
-- Same as before (show all)
SELECT * FROM products 
WHERE company_id = ? 
  AND is_active = true;
```

**After (New - Customer PH):**
```sql
-- Only show with stock OR catalog
SELECT * FROM products 
WHERE company_id = ? 
  AND is_active = true
  AND (
      product_type = 'CATALOG'
      OR (
          product_type = 'STOCK' 
          AND EXISTS (
              SELECT 1 FROM stocks 
              WHERE stocks.product_id = products.product_id 
                AND available_quantity > 0
          )
      )
  );
```

---

## ⚠️ Important Notes

### **1. Existing Products**
```
⚠️ Products yang sudah ada dengan product_type = 'STOCK' 
   tapi BELUM punya record di tabel stocks:
   
   → TIDAK akan muncul di dropdown PH Customer
   → Solution: Edit product → Save (auto create stock)
   → Or: Manual insert stock record
```

### **2. Stock Movement**
```
✅ Stock bertambah/berkurang via Stock Movement
✅ PH tidak impact stock (hanya penawaran)
✅ SJ + Stock Movement → Stock berkurang
✅ SP + Stock Movement → Stock bertambah
```

### **3. CATALOG Products**
```
✅ CATALOG products always visible (both supplier & customer)
✅ No stock validation for CATALOG
✅ CATALOG = untuk produk yang tidak ada fisik di gudang
```

---

## 🧪 Testing Checklist

### **Test 1: Product Creation**
- [ ] Create product type STOCK → Check stock record auto created
- [ ] Create product type CATALOG → No stock record created
- [ ] Edit product CATALOG → STOCK → Check stock record created

### **Test 2: PH Supplier**
- [ ] Create PH supplier → All products visible
- [ ] Add item with product stock 0 → Should allow
- [ ] Save with any qty → Should save without error

### **Test 3: PH Customer**
- [ ] Create PH customer → Only products with stock > 0 visible
- [ ] CATALOG products → Always visible
- [ ] Select product → Check "Info Stok" displayed
- [ ] Input qty > available → Should show error
- [ ] Input qty <= available → Should save OK

### **Test 4: Stock Movement Impact**
- [ ] Create stock movement IN → Check product visible in customer PH
- [ ] Create stock movement OUT → If stock = 0, product hidden in customer PH

---

## 🐛 Troubleshooting

### **Issue: Product not visible in customer PH**
**Check:**
1. Product is_active = true?
2. Product has stock record?
3. Stock available_quantity > 0?
4. If CATALOG: always should visible

**Solution:**
```sql
-- Check stock record
SELECT p.name, p.product_type, s.available_quantity
FROM products p
LEFT JOIN stocks s ON p.product_id = s.product_id
WHERE p.is_active = true;

-- If no stock record, create manually:
INSERT INTO stocks (company_id, product_id, quantity, available_quantity, reserved_quantity)
VALUES ('company-uuid', 'product-uuid', 0, 0, 0);
```

### **Issue: Validation error "Qty melebihi stok"**
**Check:**
1. Current available_quantity in stocks table
2. Reserved_quantity tidak mengurangi available
3. Recent stock movements

**Solution:**
- Verify stock quantity
- If wrong, create stock adjustment
- Check stock movement records

---

## 📞 Benefits

1. ✅ **Data Integrity**: Product STOCK pasti punya stock record
2. ✅ **Smart Filtering**: Supplier vs Customer dropdown beda
3. ✅ **Prevent Overselling**: Validasi qty untuk customer
4. ✅ **User Friendly**: Clear helper text & stock info
5. ✅ **Flexible**: CATALOG products tetap flexible
6. ✅ **Automatic**: No manual intervention needed

---

**Date:** 22 Oktober 2025  
**Status:** ✅ Ready for Testing
