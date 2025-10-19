# Performance Optimization untuk Filament Admin

## Masalah: Filament terasa lambat/berat

### Penyebab Utama:
1. **Terlalu banyak `live()` updates** - Setiap perubahan trigger request ke server
2. **`preload()` pada Select fields** - Load semua options di awal
3. **Tidak ada debounce** - Setiap keystroke trigger update

### Solusi yang Sudah Diterapkan:

#### 1. Gunakan `live(debounce: 500)` bukan `live()`
```php
// ❌ BAD - Update setiap keystroke
TextInput::make('quantity')->live()

// ✅ GOOD - Update setelah 500ms idle
TextInput::make('quantity')->live(debounce: 500)
```

#### 2. Hapus `preload()` untuk data besar
```php
// ❌ BAD - Load ribuan customer di awal
Select::make('customer_id')->preload()

// ✅ GOOD - Load on-demand saat search
Select::make('customer_id')->searchable()
```

#### 3. Gunakan `lazy()` untuk Select dengan relationship
```php
// ✅ GOOD - Load hanya saat dropdown dibuka
Select::make('product_id')
    ->relationship('product', 'name')
    ->searchable()
    ->lazy() // Tidak load sampai user klik dropdown
```

#### 4. Limit query dengan proper scoping
```php
// ✅ Filter by company_id untuk reduce data
->modifyQueryUsing(fn($query) => 
    $query->where('company_id', session('selected_company_id'))
)
```

### Tips Browser:

1. **Clear browser cache** - Ctrl+Shift+Del
2. **Disable browser extensions** - Terutama ad blockers
3. **Use Chrome/Edge** - Better performance than Firefox untuk Livewire
4. **Close other tabs** - Reduce memory usage

### Tips Development:

1. **Disable Debugbar**
```env
DEBUGBAR_ENABLED=false
```

2. **Use Database Session** (lebih cepat dari file)
```env
SESSION_DRIVER=database
```

3. **Clear cache regularly**
```bash
php artisan optimize:clear
```

### Checklist Sebelum Deploy Production:

- [ ] Remove semua `dd()` dan `dump()`
- [ ] Set `APP_DEBUG=false`
- [ ] Set `DEBUGBAR_ENABLED=false`
- [ ] Run `php artisan config:cache`
- [ ] Run `php artisan route:cache`
- [ ] Run `php artisan view:cache`
- [ ] Compile assets: `npm run build`

### Monitor Performance:

Tambahkan di `.env` untuk development:
```env
TELESCOPE_ENABLED=true  # Monitor queries
```

Kemudian check:
- Berapa banyak query per page load
- Query yang lambat (N+1 problem)
- Memory usage

### Expected Performance:

- **Page load**: < 2 detik
- **Form update**: < 500ms
- **Table render**: < 1 detik

Jika lebih lambat, check:
1. Internet connection
2. Database performance
3. Too many `live()` updates
