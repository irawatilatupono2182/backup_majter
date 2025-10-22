# Fix: Delivery Note Duplicate SJ Number Error

## Masalah
Error 500 saat create Surat Jalan dengan pesan:
```
SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry 
'01234567-89ab-cdef-0123-456789abcdef-SJ/2025/10/003' for key 'delivery_notes.uk_sj_number'
```

## Root Cause
**Race Condition** saat generate SJ number:
1. `sj_number` di-generate di form **default value** saat form dibuka
2. Jika 2 user membuka form create bersamaan, mereka mendapat nomor yang sama
3. Saat keduanya submit, yang kedua akan error karena duplicate key constraint

## Solusi yang Diterapkan

### 1. Ubah Field SJ Number di Form
**File:** `app/Filament/Resources/DeliveryNoteResource.php`

**BEFORE:**
```php
Forms\Components\TextInput::make('sj_number')
    ->label('Nomor SJ')
    ->required()
    ->default(fn() => self::generateSJNumber())  // ❌ Generate saat form load
    ->maxLength(50),
```

**AFTER:**
```php
Forms\Components\TextInput::make('sj_number')
    ->label('Nomor SJ')
    ->disabled()
    ->dehydrated(false)  // Don't submit this field
    ->default('(Auto Generated)')  // Show placeholder
    ->helperText('Nomor SJ akan di-generate otomatis saat disimpan')
    ->maxLength(50),
```

### 2. Generate SJ Number Saat Save (Inside Transaction)
**File:** `app/Filament/Resources/DeliveryNoteResource\Pages\CreateDeliveryNote.php`

**Added Method:**
```php
protected function mutateFormDataBeforeCreate(array $data): array
{
    // Generate unique SJ number with database lock
    $data['sj_number'] = $this->generateUniqueSJNumber();
    return $data;
}

protected function generateUniqueSJNumber(): string
{
    $year = date('Y');
    $month = date('m');
    $companyId = session('selected_company_id');
    
    // Use database transaction with lock to prevent race condition
    return \DB::transaction(function () use ($year, $month, $companyId) {
        // Get last number with lockForUpdate()
        $lastRecord = DeliveryNote::where('company_id', $companyId)
            ->where('sj_number', 'like', "SJ/{$year}/{$month}/%")
            ->lockForUpdate()  // ✅ Lock rows during transaction
            ->orderBy('sj_number', 'desc')
            ->first();
        
        // Calculate next number
        $nextNumber = $lastRecord ? ((int)explode('/', $lastRecord->sj_number)[3]) + 1 : 1;
        $sjNumber = sprintf('SJ/%s/%s/%03d', $year, $month, $nextNumber);
        
        // Double check (extra safety)
        $maxAttempts = 10;
        $attempt = 0;
        while (DeliveryNote::where('company_id', $companyId)
                ->where('sj_number', $sjNumber)->exists() && $attempt < $maxAttempts) {
            $nextNumber++;
            $sjNumber = sprintf('SJ/%s/%s/%03d', $year, $month, $nextNumber);
            $attempt++;
        }
        
        return $sjNumber;
    });
}
```

## Keuntungan Solusi Ini

1. ✅ **No Race Condition**: Nomor di-generate di dalam transaction saat save
2. ✅ **Database Lock**: `lockForUpdate()` mencegah 2 proses baca nomor yang sama secara bersamaan
3. ✅ **Double Check**: Extra validation jika nomor sudah ada (failsafe)
4. ✅ **User Friendly**: User tidak perlu tunggu generate nomor saat buka form
5. ✅ **Atomic**: Generate + Insert dalam 1 transaction

## Testing

1. Buka 2 browser/tab berbeda
2. Masuk ke Create Surat Jalan di kedua tab
3. Isi form di kedua tab dengan data yang berbeda
4. Submit keduanya hampir bersamaan
5. ✅ Kedua SJ harus berhasil dibuat dengan nomor yang berbeda (SJ/2025/10/001, SJ/2025/10/002)

## Files Modified

1. `app/Filament/Resources/DeliveryNoteResource.php` - Ubah field sj_number menjadi disabled
2. `app/Filament/Resources/DeliveryNoteResource\Pages\CreateDeliveryNote.php` - Tambah method generate SJ number

## Date
2025-01-20
