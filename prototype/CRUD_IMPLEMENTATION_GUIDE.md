# 🔄 CRUD Implementation Guide

## Overview
Prototype ini sekarang memiliki **Interactive CRUD Operations** yang lengkap untuk semua modul data.

## ✨ Fitur CRUD yang Tersedia

### 1. **CREATE (Tambah Data)**
- ✅ Modal form untuk input data baru
- ✅ Validasi field wajib (required)
- ✅ Auto-generated ID
- ✅ Toast notification success
- ✅ Table auto-refresh setelah create

### 2. **READ/VIEW (Lihat Detail)**
- ✅ Modal detail dengan semua informasi
- ✅ Form readonly (tidak bisa diedit)
- ✅ Tombol "Tutup" untuk close modal

### 3. **UPDATE (Edit Data)**
- ✅ Modal edit dengan data pre-filled
- ✅ Validasi field wajib
- ✅ Update ke array data
- ✅ Toast notification success
- ✅ Table auto-refresh setelah update

### 4. **DELETE (Hapus Data)**
- ✅ Confirmation dialog sebelum hapus
- ✅ Menampilkan nama/kode item yang akan dihapus
- ✅ Warning "tidak dapat dibatalkan"
- ✅ Remove dari array data
- ✅ Toast notification success
- ✅ Table auto-refresh setelah delete

## 📄 Pages dengan CRUD Lengkap

### ✅ Full CRUD Implementation:
1. **customers-crud.html** - Customer Management
   - Form: Kode, Nama, Contact, Phone, Email, Kota, Tipe, Credit Limit
   - Features: Search, Filter by Type/City, Sort by columns

2. **invoices-crud.html** - Invoice Management
   - Form: No. Invoice, Tanggal, Customer, Tipe, Total, Due Date
   - Features: Search, Filter by Status/Type/Date Range, Summary Stats
   - Extra: Auto-detect overdue invoices, Print button

3. **stock-crud.html** - Inventory Management
   - Form: Kode, Nama, Kategori, Satuan, Stock, Min Stock, Harga
   - Features: Search, Filter by Category/Stock Status
   - Extra: Low stock alert, Stock status badges

## 🎯 Cara Menggunakan

### Menambah Data Baru (CREATE)
```javascript
// 1. Klik tombol "➕ Tambah [Module]"
// 2. Modal form akan muncul
// 3. Isi semua field yang wajib (marked dengan *)
// 4. Klik "➕ Simpan"
// 5. Data akan ditambahkan ke table
```

### Melihat Detail (VIEW)
```javascript
// 1. Klik tombol "👁️" pada baris data
// 2. Modal detail akan muncul dengan data readonly
// 3. Klik "Tutup" untuk close
```

### Mengedit Data (EDIT)
```javascript
// 1. Klik tombol "✏️" pada baris data
// 2. Modal edit akan muncul dengan data pre-filled
// 3. Ubah data yang diperlukan
// 4. Klik "✏️ Update"
// 5. Data akan diupdate di table
```

### Menghapus Data (DELETE)
```javascript
// 1. Klik tombol "🗑️" pada baris data
// 2. Confirmation dialog akan muncul
// 3. Klik "🗑️ Hapus" untuk konfirmasi
// 4. Data akan dihapus dari table
```

## 🔍 Fitur Tambahan

### Search (Pencarian)
```javascript
// Ketik di search box untuk filter data real-time
// Search mencari di semua kolom visible
```

### Filter
```javascript
// Gunakan dropdown filter untuk:
// - Filter by Type (PPN/Non-PPN)
// - Filter by Status (Lunas/Belum Lunas/Overdue)
// - Filter by Category
// - Filter by Date Range
// Klik "🔄 Reset" untuk clear semua filter
```

### Sort (Pengurutan)
```javascript
// Klik pada column header dengan icon ↕️
// Toggle between ascending/descending
// Visual feedback dengan toast notification
```

### Pagination (Coming Soon)
```javascript
// Tombol pagination sudah ada
// Functionality akan ditambahkan untuk dataset besar
```

## 🎨 Component Files

### JavaScript
- **crud-manager.js** - Core CRUD logic class
  - CRUDManager class dengan methods:
    - init(module, data)
    - showCreateModal()
    - showViewModal(id)
    - showEditModal(id)
    - showDeleteConfirmation(id)
    - submitForm(mode)
    - handleSearch(query)
    - handleFilter()
    - handleSort(field)
    - refreshTable()
    - showToast(message, type)

### CSS
- **modal-components.css** - Modal & Form styling
  - .modal-overlay
  - .modal-dialog (with .modal-sm, .modal-lg)
  - .modal-header, .modal-body, .modal-footer
  - .form-group, .form-control
  - .toast notification styles
  - Responsive breakpoints
  - Dark mode support

## 📝 Form Validation

### Required Fields
```javascript
// Fields dengan required attribute akan divalidasi
// Visual feedback:
// - Border merah jika kosong
// - Border normal jika valid
// - Toast error message jika ada field kosong
```

### Field Types
- **Text Input**: Nama, Kode, Contact, etc.
- **Number Input**: Stock, Min Stock, Credit Limit
- **Email Input**: Email dengan validation
- **Tel Input**: Phone number
- **Date Input**: Tanggal, Due Date
- **Select Dropdown**: Customer, Type, Status, Category
- **Textarea**: Catatan, Alamat (optional)

## 🎭 Modal Animation

### Entrance
- Fade in background overlay (0.2s)
- Slide up modal dialog (0.3s)

### Exit
- Fade out (0.3s)
- Remove from DOM

## 🔔 Notification System

### Toast Types
1. **Success** ✓ - Hijau
   - Create success
   - Update success
   - Delete success

2. **Danger** ✗ - Merah
   - Validation error
   - Delete operation

3. **Warning** ⚠️ - Kuning
   - Low stock alert
   - Overdue warning

4. **Info** ℹ️ - Biru
   - Sort notification
   - Filter reset
   - Coming soon features

### Toast Position
- Fixed top-right (desktop)
- Fixed top full-width (mobile)
- Auto-dismiss after 3 seconds
- Smooth fade out animation

## 🎨 Status Badges

### Customer Type
- **PPN** - Green badge (success)
- **Non-PPN** - Yellow badge (warning)

### Invoice Status
- **Lunas** - Green badge (success)
- **Belum Lunas** - Yellow badge (warning)
- **Overdue** - Red badge (danger)

### Stock Status
- **Aman** ✓ - Green badge (safe)
- **Rendah** ⚠️ - Yellow badge (low)
- **Habis** 🔴 - Red badge (out of stock)

## 💡 Best Practices

### 1. Consistent Data Structure
```javascript
// Setiap module memiliki structure yang konsisten
const customer = {
    id: 1,
    code: 'CUST001',
    name: 'PT Example',
    contact: 'John Doe',
    phone: '0812345678',
    email: 'john@example.com',
    city: 'Jakarta',
    type: 'PPN',
    credit_limit: 'Rp 50.000.000'
};
```

### 2. Form HTML Generation
```javascript
// Customize getFormHTML() di crud-manager.js untuk setiap module
// Gunakan template literals untuk clean HTML
// Support mode: 'create', 'edit', 'view'
```

### 3. Table Refresh
```javascript
// Override crudManager.refreshTable() di setiap page
// Pastikan repopulate table dengan currentData
crudManager.refreshTable = function() {
    populateTable(this.currentData);
};
```

### 4. Data Persistence
```javascript
// Saat ini data disimpan di memory (array)
// Untuk production: integrate dengan backend API
// Bisa juga gunakan localStorage untuk persistence
```

## 🚀 Extending CRUD

### Menambah Module Baru
```javascript
// 1. Buat halaman HTML baru (e.g., suppliers-crud.html)
// 2. Include crud-manager.js dan modal-components.css
// 3. Initialize: crudManager.init('suppliers', suppliers);
// 4. Tambahkan form HTML di getFormHTML() switch case
// 5. Implement populateTable() function
// 6. Override refreshTable() method
```

### Custom Validation
```javascript
// Tambahkan di submitForm() method
if (!validateEmail(data.email)) {
    this.showToast('Email tidak valid', 'danger');
    return;
}
```

### Custom Actions
```javascript
// Tambahkan button di action column
<button onclick="customAction(${id})">
    Custom
</button>

// Implement function
function customAction(id) {
    // Your custom logic
    crudManager.showToast('Custom action executed', 'success');
}
```

## 📱 Responsive Design

### Desktop (>768px)
- Full table columns visible
- Modal max-width 600px (default)
- Toast fixed top-right

### Mobile (<768px)
- Table horizontal scroll
- Modal full screen (no border-radius)
- Toast full width
- Stacked filter groups

## 🌙 Dark Mode Support

### Automatic Detection
```css
@media (prefers-color-scheme: dark) {
    /* Dark mode styles */
}
```

### Colors
- Background: #1f2937
- Borders: #374151
- Text: #f9fafb
- Form controls: #374151

## 🔜 Future Enhancements

### Planned Features
- ✅ Bulk operations (select multiple rows)
- ✅ Advanced filter with AND/OR conditions
- ✅ Column visibility toggle
- ✅ Export to PDF/Excel
- ✅ Import from CSV/Excel
- ✅ Audit log (who created/updated)
- ✅ Soft delete (recycle bin)
- ✅ Keyboard shortcuts
- ✅ Drag & drop file upload
- ✅ Real-time validation
- ✅ Auto-save draft

## 🐛 Troubleshooting

### Modal tidak muncul
```javascript
// Periksa console error
// Pastikan crud-manager.js dan modal-components.css loaded
// Periksa init() sudah dipanggil di page load
```

### Data tidak tersimpan
```javascript
// Periksa submitForm() tidak ada error
// Periksa required field validation
// Periksa refreshTable() dipanggil
```

### Filter tidak bekerja
```javascript
// Periksa data-filter attribute di select
// Periksa data-field attribute di table cell
// Periksa handleFilter() logic
```

### Toast tidak muncul
```javascript
// Periksa showToast() dipanggil dengan parameter correct
// Periksa CSS variables defined (--success, --danger, etc)
// Periksa z-index tidak di-override
```

## 📚 References

### Files to Check
- `/prototype/assets/js/crud-manager.js` - Core CRUD logic
- `/prototype/assets/css/modal-components.css` - Modal styles
- `/prototype/pages/customers-crud.html` - Customer example
- `/prototype/pages/invoices-crud.html` - Invoice example
- `/prototype/pages/stock-crud.html` - Stock example

### Demo Links
1. Customer Management: `prototype/pages/customers-crud.html`
2. Invoice Management: `prototype/pages/invoices-crud.html`
3. Stock Management: `prototype/pages/stock-crud.html`

---

**📝 Note**: Ini adalah prototype frontend-only. Untuk production, integrate dengan backend API dan database real.

**🎨 Customization**: Semua styling bisa disesuaikan di `modal-components.css` dan `modern-style.css`.

**🔧 Support**: Jika ada issue, periksa browser console untuk error messages.
