-- ================================
-- SISTEM INVENTORY & PENJUALAN — FULL SCHEMA (MySQL)
-- Versi: Final, Multi-Company, Sesuai Kolom Operasional
-- ================================

-- 1. companies
CREATE TABLE companies (
    company_id CHAR(36) PRIMARY KEY,
    code VARCHAR(20) UNIQUE NOT NULL COMMENT 'Kode perusahaan, contoh: COMP01',
    name VARCHAR(255) NOT NULL,
    address TEXT,
    phone VARCHAR(30),
    email VARCHAR(100),
    npwp VARCHAR(30),
    logo_url VARCHAR(500),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. users
CREATE TABLE users (
    user_id CHAR(36) PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash TEXT NOT NULL,
    full_name VARCHAR(100),
    phone VARCHAR(30),
    is_active BOOLEAN DEFAULT true,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 3. user_company_roles
CREATE TABLE user_company_roles (
    user_id CHAR(36) NOT NULL,
    company_id CHAR(36) NOT NULL,
    role VARCHAR(50) NOT NULL COMMENT 'admin, finance, warehouse, viewer',
    is_default BOOLEAN DEFAULT false,
    PRIMARY KEY (user_id, company_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (company_id) REFERENCES companies(company_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 4. customers — SESUAI KEBUTUHAN: NO, NAMA, U.P., ALAMAT 1, ALAMAT 2, NPWP, JADWAL KONTRA BON
CREATE TABLE customers (
    customer_id CHAR(36) PRIMARY KEY,
    company_id CHAR(36) NOT NULL,
    
    -- [NO] → customer_code
    customer_code VARCHAR(50) NOT NULL COMMENT 'Digunakan sebagai "NO" di laporan',
    
    -- [NAMA]
    name VARCHAR(255) NOT NULL COMMENT 'Nama instansi customer',
    
    -- [U.P.]
    contact_person VARCHAR(100) COMMENT 'Untuk Perhatian / PIC',
    
    -- [ALAMAT 1 (SHIP TO)]
    address_ship_to TEXT NOT NULL,
    
    -- [ALAMAT 2 (BILL TO)]
    address_bill_to TEXT,
    
    -- [NPWP]
    npwp VARCHAR(30),
    
    -- [JADWAL KONTRA BON]
    billing_schedule VARCHAR(100) COMMENT 'Contoh: "Setiap tgl 5", "Minggu ke-2"',
    
    -- Tambahan penting
    is_ppn BOOLEAN NOT NULL DEFAULT false,
    phone VARCHAR(30),
    email VARCHAR(100),
    is_active BOOLEAN DEFAULT true,
    
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY uk_customer_code (company_id, customer_code),
    FOREIGN KEY (company_id) REFERENCES companies(company_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 5. suppliers
CREATE TABLE suppliers (
    supplier_id CHAR(36) PRIMARY KEY,
    company_id CHAR(36) NOT NULL,
    supplier_code VARCHAR(50) NOT NULL,
    name VARCHAR(255) NOT NULL,
    type VARCHAR(20) NOT NULL COMMENT 'Local atau Import',
    address TEXT NOT NULL,
    phone VARCHAR(30),
    email VARCHAR(100),
    contact_person VARCHAR(100),
    is_active BOOLEAN DEFAULT true,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_supplier_code (company_id, supplier_code),
    FOREIGN KEY (company_id) REFERENCES companies(company_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 6. products
CREATE TABLE products (
    product_id CHAR(36) PRIMARY KEY,
    company_id CHAR(36) NOT NULL,
    product_code VARCHAR(50) NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    unit VARCHAR(20) NOT NULL COMMENT 'pcs, kg, liter, dll',
    base_price DECIMAL(18,2) NOT NULL DEFAULT 0,
    default_discount_percent DECIMAL(5,2) DEFAULT 0,
    min_stock_alert INT DEFAULT 5,
    category VARCHAR(100),
    is_active BOOLEAN DEFAULT true,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_product_code (company_id, product_code),
    FOREIGN KEY (company_id) REFERENCES companies(company_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 7. inventory_batches
CREATE TABLE inventory_batches (
    batch_id CHAR(36) PRIMARY KEY,
    company_id CHAR(36) NOT NULL,
    product_id CHAR(36) NOT NULL,
    supplier_id CHAR(36),
    reference_type VARCHAR(20) COMMENT 'PO, Manual, Adjustment',
    reference_id CHAR(36),
    received_date DATE NOT NULL,
    expiry_date DATE,
    initial_qty DECIMAL(15,4) NOT NULL,
    remaining_qty DECIMAL(15,4) NOT NULL DEFAULT 0,
    unit VARCHAR(20) NOT NULL,
    purchase_price DECIMAL(18,4) NOT NULL DEFAULT 0,
    additional_cost DECIMAL(18,2) DEFAULT 0,
    hpp_per_unit DECIMAL(18,4) NOT NULL DEFAULT 0,
    status VARCHAR(20) NOT NULL DEFAULT 'STOCK' COMMENT 'STOCK, USED, DAMAGED, EXPIRED',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(company_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- 8. price_quotations (PH)
CREATE TABLE price_quotations (
    ph_id CHAR(36) PRIMARY KEY,
    company_id CHAR(36) NOT NULL,
    supplier_id CHAR(36) NOT NULL,
    quotation_number VARCHAR(50) NOT NULL,
    type VARCHAR(10) NOT NULL COMMENT 'PPN atau Non-PPN',
    quotation_date DATE NOT NULL,
    valid_until DATE,
    status VARCHAR(20) NOT NULL DEFAULT 'Draft' COMMENT 'Draft, Sent, Accepted, Rejected',
    notes TEXT,
    created_by CHAR(36),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_ph_number (company_id, quotation_number),
    FOREIGN KEY (company_id) REFERENCES companies(company_id) ON DELETE CASCADE,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id),
    FOREIGN KEY (created_by) REFERENCES users(user_id)
) ENGINE=InnoDB;

CREATE TABLE price_quotation_items (
    ph_item_id CHAR(36) PRIMARY KEY,
    ph_id CHAR(36) NOT NULL,
    product_id CHAR(36) NOT NULL,
    qty DECIMAL(15,4) NOT NULL,
    unit VARCHAR(20) NOT NULL,
    unit_price DECIMAL(18,2) NOT NULL,
    discount_percent DECIMAL(5,2) DEFAULT 0,
    subtotal DECIMAL(18,2) NOT NULL,
    FOREIGN KEY (ph_id) REFERENCES price_quotations(ph_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id)
) ENGINE=InnoDB;

-- 9. purchase_orders (PO)
CREATE TABLE purchase_orders (
    po_id CHAR(36) PRIMARY KEY,
    company_id CHAR(36) NOT NULL,
    ph_id CHAR(36),
    supplier_id CHAR(36) NOT NULL,
    po_number VARCHAR(50) NOT NULL,
    type VARCHAR(10) NOT NULL COMMENT 'PPN atau Non-PPN',
    order_date DATE NOT NULL,
    expected_delivery DATE,
    status VARCHAR(20) NOT NULL DEFAULT 'Pending' COMMENT 'Pending, Confirmed, Partial, Completed, Cancelled',
    notes TEXT,
    created_by CHAR(36),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_po_number (company_id, po_number),
    FOREIGN KEY (company_id) REFERENCES companies(company_id) ON DELETE CASCADE,
    FOREIGN KEY (ph_id) REFERENCES price_quotations(ph_id),
    FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id),
    FOREIGN KEY (created_by) REFERENCES users(user_id)
) ENGINE=InnoDB;

CREATE TABLE purchase_order_items (
    po_item_id CHAR(36) PRIMARY KEY,
    po_id CHAR(36) NOT NULL,
    product_id CHAR(36) NOT NULL,
    qty_ordered DECIMAL(15,4) NOT NULL,
    qty_received DECIMAL(15,4) DEFAULT 0,
    unit VARCHAR(20) NOT NULL,
    unit_price DECIMAL(18,2) NOT NULL,
    discount_percent DECIMAL(5,2) DEFAULT 0,
    subtotal DECIMAL(18,2) NOT NULL,
    FOREIGN KEY (po_id) REFERENCES purchase_orders(po_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id)
) ENGINE=InnoDB;

-- 10. supplier_delivery_notes (SP)
CREATE TABLE supplier_delivery_notes (
    sp_id CHAR(36) PRIMARY KEY,
    company_id CHAR(36) NOT NULL,
    po_id CHAR(36),
    supplier_id CHAR(36) NOT NULL,
    sp_number VARCHAR(50) NOT NULL,
    delivery_date DATE NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'Received' COMMENT 'Received, Partial, Damaged, Rejected',
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(company_id) ON DELETE CASCADE,
    FOREIGN KEY (po_id) REFERENCES purchase_orders(po_id),
    FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id),
    UNIQUE KEY uk_sp_number (company_id, sp_number)
) ENGINE=InnoDB;

-- 11. delivery_notes (SJ)
CREATE TABLE delivery_notes (
    sj_id CHAR(36) PRIMARY KEY,
    company_id CHAR(36) NOT NULL,
    customer_id CHAR(36) NOT NULL,
    sj_number VARCHAR(50) NOT NULL,
    type VARCHAR(10) NOT NULL COMMENT 'PPN, Non-PPN, Supplier',
    delivery_date DATE NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'Draft' COMMENT 'Draft, Sent, Completed',
    notes TEXT,
    created_by CHAR(36),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_sj_number (company_id, sj_number),
    FOREIGN KEY (company_id) REFERENCES companies(company_id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id),
    FOREIGN KEY (created_by) REFERENCES users(user_id)
) ENGINE=InnoDB;

CREATE TABLE delivery_note_items (
    sj_item_id CHAR(36) PRIMARY KEY,
    sj_id CHAR(36) NOT NULL,
    product_id CHAR(36) NOT NULL,
    qty DECIMAL(15,4) NOT NULL,
    unit VARCHAR(20) NOT NULL,
    unit_price DECIMAL(18,2) NOT NULL,
    discount_percent DECIMAL(5,2) DEFAULT 0,
    subtotal DECIMAL(18,2) NOT NULL,
    FOREIGN KEY (sj_id) REFERENCES delivery_notes(sj_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id)
) ENGINE=InnoDB;

-- 12. invoices
CREATE TABLE invoices (
    invoice_id CHAR(36) PRIMARY KEY,
    company_id CHAR(36) NOT NULL,
    customer_id CHAR(36) NOT NULL,
    sj_id CHAR(36),
    invoice_number VARCHAR(50) NOT NULL,
    type VARCHAR(10) NOT NULL COMMENT 'PPN atau Non-PPN',
    invoice_date DATE NOT NULL,
    due_date DATE NOT NULL,
    total_amount DECIMAL(18,2) NOT NULL DEFAULT 0,
    ppn_amount DECIMAL(18,2) DEFAULT 0,
    grand_total DECIMAL(18,2) NOT NULL DEFAULT 0,
    status VARCHAR(20) NOT NULL DEFAULT 'Unpaid' COMMENT 'Unpaid, Partial, Paid, Overdue, Cancelled',
    notes TEXT,
    created_by CHAR(36),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_invoice_number (company_id, invoice_number),
    FOREIGN KEY (company_id) REFERENCES companies(company_id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id),
    FOREIGN KEY (sj_id) REFERENCES delivery_notes(sj_id),
    FOREIGN KEY (created_by) REFERENCES users(user_id)
) ENGINE=InnoDB;

CREATE TABLE invoice_items (
    invoice_item_id CHAR(36) PRIMARY KEY,
    invoice_id CHAR(36) NOT NULL,
    product_id CHAR(36) NOT NULL,
    qty DECIMAL(15,4) NOT NULL,
    unit VARCHAR(20) NOT NULL,
    unit_price DECIMAL(18,2) NOT NULL,
    discount_percent DECIMAL(5,2) DEFAULT 0,
    subtotal DECIMAL(18,2) NOT NULL,
    FOREIGN KEY (invoice_id) REFERENCES invoices(invoice_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id)
) ENGINE=InnoDB;

-- 13. payments
CREATE TABLE payments (
    payment_id CHAR(36) PRIMARY KEY,
    company_id CHAR(36) NOT NULL,
    invoice_id CHAR(36) NOT NULL,
    customer_id CHAR(36) NOT NULL,
    payment_date DATE NOT NULL,
    amount DECIMAL(18,2) NOT NULL,
    payment_method VARCHAR(50) COMMENT 'Cash, Transfer, QRIS, dll',
    reference_number VARCHAR(100),
    notes TEXT,
    created_by CHAR(36),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(company_id) ON DELETE CASCADE,
    FOREIGN KEY (invoice_id) REFERENCES invoices(invoice_id),
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id),
    FOREIGN KEY (created_by) REFERENCES users(user_id)
) ENGINE=InnoDB;

-- 14. reminders (untuk notifikasi jatuh tempo, PO menunggu, dll)
CREATE TABLE reminders (
    reminder_id CHAR(36) PRIMARY KEY,
    company_id CHAR(36) NOT NULL,
    reference_type VARCHAR(20) NOT NULL COMMENT 'Invoice, PO, SP',
    reference_id CHAR(36) NOT NULL,
    due_date DATE NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    is_read BOOLEAN DEFAULT false,
    status VARCHAR(20) NOT NULL DEFAULT 'Upcoming' COMMENT 'Upcoming, Overdue, Completed',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(company_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ================================
-- INDEXES UNTUK PERFORMA
-- ================================
CREATE INDEX idx_customers_company ON customers(company_id);
CREATE INDEX idx_suppliers_company ON suppliers(company_id);
CREATE INDEX idx_products_company ON products(company_id);
CREATE INDEX idx_inventory_company_product ON inventory_batches(company_id, product_id);
CREATE INDEX idx_invoices_company_status ON invoices(company_id, status);
CREATE INDEX idx_invoices_due_date ON invoices(due_date);
CREATE INDEX idx_payments_invoice ON payments(invoice_id);
CREATE INDEX idx_delivery_notes_company ON delivery_notes(company_id);
CREATE INDEX idx_po_company_status ON purchase_orders(company_id, status);
CREATE INDEX idx_reminders_company_status ON reminders(company_id, status);