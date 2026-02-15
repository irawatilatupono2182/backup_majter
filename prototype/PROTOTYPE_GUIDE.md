# 🎨 ADAM JAYA PROTOTYPE GUIDE

> **HTML Prototype - Management System** 
> Versi: 1.0 | Tanggal: 15 Februari 2026

---

## 📋 DESKRIPSI

Ini adalah prototype HTML statis untuk Adam Jaya Management System yang dibuat berdasarkan dokumen [MENU_STRUCTURE_COMPLETE.md](../MENU_STRUCTURE_COMPLETE.md). Prototype ini menampilkan seluruh struktur menu dan submenu yang aktif di sistem, lengkap dengan data dummy untuk simulasi.

## 🎯 TUJUAN

Prototype ini dibuat untuk:
- ✅ Visualisasi struktur menu dan navigasi sistem
- ✅ Demo tampilan user interface untuk stakeholder
- ✅ Testing UX flow dan user journey
- ✅ Referensi desain untuk development aktual
- ✅ Dokumentasi visual sistem

## 📂 STRUKTUR FOLDER

```
prototype/
├── index.html                  # Dashboard (Halaman Utama)
├── PROTOTYPE_GUIDE.md         # Guide ini
├── assets/
│   ├── css/
│   │   └── style.css          # Stylesheet lengkap
│   ├── js/
│   │   └── app.js             # JavaScript interaktivity
│   └── data/
│       └── data.json          # Data dummy
└── pages/
    ├── roles.html             # Master Data - Roles
    ├── users.html             # Master Data - Users
    ├── customers.html         # Penjualan - Customers
    ├── invoices.html          # Penjualan - Invoices
    ├── suppliers.html         # Pembelian - Suppliers
    └── purchase-orders.html   # Pembelian - Purchase Orders
```

## 🚀 CARA MENGGUNAKAN

### Metode 1: Buka Langsung di Browser
1. Buka folder `prototype/`
2. Double-click file `index.html`
3. Browser akan membuka halaman Dashboard

### Metode 2: Menggunakan Local Server (Recommended)

**Dengan Laragon (sudah terinstal):**
```
http://localhost/adamjaya/prototype/
```
Akses langsung via browser ke URL di atas.

**Dengan PHP Built-in Server:**
```bash
cd c:\laragon\www\adamjaya\prototype
php -S localhost:8000
```
Akses: `http://localhost:8000`

## 📊 MENU YANG TERSEDIA

### ✅ Halaman yang Sudah Dibuat:

| Menu Group | Halaman | File | Status |
|-----------|---------|------|--------|
| **Dashboard** | Dashboard | `index.html` | ✅ Complete |
| **📦 Master Data** | Roles | `pages/roles.html` | ✅ Complete |
| | Users | `pages/users.html` | ✅ Complete |
| **💼 Penjualan** | Customers | `pages/customers.html` | ✅ Complete |
| | Invoices | `pages/invoices.html` | ✅ Complete |
| **🛒 Pembelian** | Suppliers | `pages/suppliers.html` | ✅ Complete |
| | Purchase Orders | `pages/purchase-orders.html` | ✅ Complete |

### 📝 Halaman yang Bisa Ditambahkan:

Berikut menu lain yang tercantum di menu structure:
- Price Quotations (Surat Penawaran)
- Delivery Notes (Surat Jalan)
- Nota Menyusul
- Keterangan Lain
- Master Barang/Stock
- Pembayaran Hutang
- Laporan Penjualan
- Laporan Pembelian
- Piutang Usaha (AR)
- Laporan Inventory
- Hutang (Payables)
- Import Data

## 🎨 FITUR PROTOTYPE

### Visual Features:
- ✨ Modern UI Design
- 📱 Responsive Layout
- 🎯 Active Navigation
- 📊 Stats Cards dengan icon
- 📋 Data Tables dengan sorting dan filter
- 🎨 Color-coded Badges
- 🔔 Toast Notifications
- 🌈 Gradient Effects

### Interactive Features:
- 🔍 Search Functionality
- 📱 Action Buttons (Edit, View, Delete)
- ⚡ Quick Actions
- 🎯 Filters
- 📄 Pagination

## 🗂️ DATA DUMMY

File `assets/data/data.json` berisi data dummy untuk:
- 👥 Customers (5 data)
- 🏭 Suppliers (3 data)
- 📄 Invoices (5 data)
- 👤 Users (4 data)
- 🔐 Roles (5 data)
- 📊 Dashboard Stats

## 🎨 DESIGN SYSTEM

### Color Palette:
```css
Primary: #3b82f6 (Blue)
Success: #10b981 (Green)
Warning: #f59e0b (Orange)
Danger:  #ef4444 (Red)
Info:    #06b6d4 (Cyan)
```

### Components:
- Cards dengan shadow
- Buttons dengan hover effects
- Tables dengan zebra striping
- Badges dengan color-coding
- Alerts dengan icons

## 📄 REFERENSI

Dokumen terkait:
- [MENU_STRUCTURE_COMPLETE.md](../MENU_STRUCTURE_COMPLETE.md) - Struktur menu lengkap

---

**Happy Prototyping! 🎨**

**Last Updated**: 15 Februari 2026
