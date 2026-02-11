# Si-Majter ERP System

## 📖 Apa Itu Repository Ini? / What Is This Repository?

**Bahasa Indonesia:**  
Si-Majter adalah **Sistem ERP (Enterprise Resource Planning)** lengkap yang dirancang khusus untuk mengelola **inventori dan penjualan** dengan dukungan multi-perusahaan. Sistem ini mengotomatisasi seluruh proses bisnis dari pembelian (Penawaran Harga → Purchase Order → Penerimaan Barang) hingga penjualan (Surat Jalan → Invoice → Pembayaran).

**English:**  
Si-Majter is a comprehensive **ERP (Enterprise Resource Planning) System** specifically designed for **inventory and sales management** with multi-company support. It automates the complete business process from purchasing (Price Quotation → Purchase Order → Goods Receipt) to sales (Delivery Note → Invoice → Payment).

### Teknologi Utama / Tech Stack
- **Framework:** Laravel 12 (PHP 8.3+)
- **Admin Panel:** Filament 3
- **Database:** MySQL 8.0+
- **PDF Generation:** DomPDF
- **Export:** Maatwebsite Excel

### Fitur Utama / Key Features
✅ **Multi-Company Management** - Manajemen multi-perusahaan dengan isolasi data  
✅ **Inventory Management** - Pelacakan stok real-time dengan metode FIFO  
✅ **Purchasing Flow** - PH (Price Quotation) → PO (Purchase Order) → Stock In  
✅ **Sales Flow** - SJ (Delivery Note) → Invoice → Payment  
✅ **PDF Generation** - Cetak otomatis semua dokumen bisnis  
✅ **Role-Based Access** - 4 level akses (Super Admin, Admin, Finance, Warehouse, Viewer)  
✅ **Comprehensive Reports** - Laporan penjualan dengan export Excel/CSV  

---

## 🚀 Features

### Core Features
- **Multi-Company Management** - Isolasi data antar perusahaan
- **Role-Based Access Control** - 4 level akses (Super Admin, Admin, Finance, Warehouse, Viewer)
- **Complete Business Flow** - Dari quotation hingga payment
- **Inventory Management** - Stock tracking dengan metode FIFO dan batch management
- **Document Generation** - PDF otomatis untuk semua dokumen bisnis
- **Comprehensive Reporting** - Sales report dengan export Excel/CSV

### Business Modules

#### 1. Master Data Management
- **Companies** - Multi-company dengan isolasi data
- **Users** - User management dengan role assignment
- **Customers** - Customer database dengan credit limit
- **Suppliers** - Supplier management dengan payment terms
- **Products** - Product catalog dengan stock/non-stock types

#### 2. Purchasing Flow
```
Price Quotation (PH) → Purchase Order (PO) → Goods Receipt → Stock In
```
- Permintaan harga ke supplier
- Purchase order generation
- Automatic stock updates on receipt
- FIFO stock management

#### 3. Sales Flow
```
Delivery Note (SJ) → Stock Out → Invoice → Payment
```
- Surat jalan dengan automatic stock reduction
- Invoice generation dari delivery note
- Payment tracking dan outstanding management

#### 4. Inventory Management
- **Real-time stock tracking** dengan batch numbers
- **Expiry date management** untuk produk yang memiliki masa kadaluarsa
- **Minimum/Maximum stock alerts**
- **Stock movement history** untuk audit trail
- **FIFO method** untuk stock valuation

#### 5. Reporting & Analytics
- **Sales Report** dengan filtering dinamis
- **Inventory Report** dengan stock levels
- **Outstanding Invoice Report**
- **Export ke Excel/CSV** untuk semua reports

### Security & Performance
- **UUID Primary Keys** untuk security dan scalability
- **Soft Deletes** untuk data integrity
- **Database Indexes** untuk performance optimization
- **Company Data Isolation** untuk multi-tenant security
- **Role-based permissions** menggunakan Spatie Laravel Permission

## 🛠 Tech Stack

- **Backend**: Laravel 12 (PHP 8.3+)
- **Admin Panel**: Filament 3
- **Database**: MySQL 8.0+
- **PDF Generation**: DomPDF
- **Excel Export**: Maatwebsite Excel
- **Permissions**: Spatie Laravel Permission
- **UUID**: Ramsey UUID

## 📋 Prerequisites

- PHP 8.3 atau lebih tinggi
- MySQL 8.0 atau lebih tinggi
- Composer
- Node.js & NPM (untuk asset compilation)

## 🔧 Installation

### 1. Clone Repository
```bash
git clone [repository-url]
cd si-majter
```

### 2. Install Dependencies
```bash
composer install
npm install
```

### 3. Environment Setup
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Database Configuration
Edit `.env` file:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=si_majter
DB_USERNAME=root
DB_PASSWORD=
```

### 5. Database Setup
```bash
# Create database
mysql -u root -p -e "CREATE DATABASE si_majter;"

# Run migrations
php artisan migrate

# Seed sample data
php artisan db:seed
```

### 6. Storage Setup
```bash
php artisan storage:link
```

### 7. Asset Compilation
```bash
npm run build
```

### 8. Start Server
```bash
php artisan serve
```

## 👥 Default Login Credentials

Setelah menjalankan `php artisan db:seed`, Anda dapat login menggunakan:

| Role | Email | Password | Access Level |
|------|-------|----------|--------------|
| Super Admin | admin@adamjaya.com | password | Full access |
| Finance | finance@adamjaya.com | password | Financial modules |
| Warehouse | warehouse@adamjaya.com | password | Inventory modules |
| Viewer | viewer@adamjaya.com | password | Read-only access |

## 🏗 Database Schema

Database menggunakan UUID sebagai primary key dan mendukung soft delete. Dokumentasi lengkap tersedia di `database_schema_documentation.md`.

### Key Tables
- `companies` - Multi-company management
- `users` + `user_company_roles` - User dan role management
- `customers`, `suppliers`, `products` - Master data
- `price_quotations`, `purchase_orders` - Purchasing
- `delivery_notes`, `invoices`, `payments` - Sales
- `stocks`, `stock_movements` - Inventory tracking

## 📊 Sample Data

Sistem dilengkapi dengan sample data lengkap:
- 3 Companies demo
- 4 Users dengan berbagai roles
- 4 Customers dan 4 Suppliers
- 8 Products (mix stock dan catalog items)
- Sample transactions (opsional dengan TransactionSeeder)

## 🔒 Security Features

- **Multi-Company Isolation** - Data terpisah per company
- **Role-Based Access Control** - Granular permissions
- **Password Hashing** - bcrypt encryption
- **UUID Primary Keys** - Non-predictable IDs
- **Soft Deletes** - Data integrity maintenance
- **Authentication Middleware** - Protected routes

## ⚡ Performance Optimizations

- Database indexes pada key columns
- Eager loading untuk relationships
- Efficient queries dengan proper joins
- Caching untuk lookup data
- Optimized Filament resources

## 📁 Project Structure

```
si-majter/
├── app/
│   ├── Filament/Resources/     # Filament admin resources
│   ├── Http/Controllers/       # PDF generation controllers
│   ├── Models/                 # Eloquent models
│   ├── Imports/               # Excel import classes
│   └── Exports/               # Excel export classes
├── database/
│   ├── migrations/            # Database migrations
│   └── seeders/              # Sample data seeders
├── resources/
│   └── views/pdf/            # PDF templates
├── scripts/                  # Backup scripts
└── storage/
    └── app/public/
        └── import-templates/ # CSV import templates
```

## 🚦 Usage Guide

### 1. Initial Setup
1. Login sebagai admin
2. Verify company data di Companies module
3. Setup users dan assign roles
4. Input master data (customers, suppliers, products)

### 2. Daily Operations

#### Purchasing Flow:
1. **Price Quotation (PH)** - Request harga ke supplier
2. **Purchase Order (PO)** - Order ke supplier
3. **Goods Receipt** - Update status PO ke "completed"
4. **Stock** akan otomatis terupdate

#### Sales Flow:
1. **Delivery Note (SJ)** - Buat surat jalan
2. **Stock** akan otomatis berkurang
3. **Invoice** - Generate dari delivery note
4. **Payment** - Record pembayaran customer

### 3. Reporting
- Akses menu Reports untuk berbagai laporan
- Filter berdasarkan tanggal, company, status
- Export ke Excel/CSV sesuai kebutuhan

### 4. Data Import
- Gunakan menu Data Import untuk upload CSV
- Download template yang disediakan
- Mapping otomatis berdasarkan header CSV

## 🔧 Backup & Maintenance

### Daily Backup
```bash
# Windows
scripts/daily_backup.bat

# Linux/Unix
scripts/daily_backup.sh
```

### Maintenance Commands
```bash
# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Optimize
php artisan optimize
php artisan config:cache
```

## 🐛 Troubleshooting

### Common Issues

1. **Permission Denied Errors**
   ```bash
   chmod -R 775 storage bootstrap/cache
   ```

2. **Database Connection Issues**
   - Verify database credentials in `.env`
   - Ensure MySQL service is running

3. **Missing Dependencies**
   ```bash
   composer install --no-dev --optimize-autoloader
   ```

4. **Storage Link Issues**
   ```bash
   php artisan storage:link
   ```

## 🧪 Testing

```bash
# Run tests
php artisan test

# Run specific test
php artisan test --filter=CustomerTest
```

## 📝 Development Status

✅ **Completed Features:**
- Multi-company setup & authentication
- Master data management (Companies, Users, Customers, Suppliers, Products)
- Complete purchasing workflow (PH → PO → Stock In)
- Complete sales workflow (SJ → Invoice → Payment)
- PDF document generation
- Inventory management with FIFO method
- Role-based permissions
- Sales & inventory reporting
- Data import functionality
- Database optimization & backup scripts

📋 **Remaining Tasks:**
- Comprehensive testing
- User training materials
- Performance benchmarking

## 🤝 Contributing

1. Fork repository
2. Create feature branch (`git checkout -b feature/amazing-feature`)
3. Commit changes (`git commit -m 'Add amazing feature'`)
4. Push to branch (`git push origin feature/amazing-feature`)
5. Create Pull Request

## 📄 License

This project is licensed under the MIT License.

## 👨‍💻 Support

Untuk support teknis atau pertanyaan:
- Documentation: Lihat file `database_schema_documentation.md`
- Issues: Gunakan GitHub Issues untuk bug reports

---

**Si-Majter ERP System** - Solusi ERP komprehensif untuk bisnis modern dengan fokus pada inventory dan sales management.

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
#   a d a m j a y a _ e r p  
 