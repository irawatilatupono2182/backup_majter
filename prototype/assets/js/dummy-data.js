// ============================================
// DUMMY DATA untuk Prototype Adam Jaya
// ============================================

// Dashboard Stats
const dashboardStats = {
    kpiUtama: {
        totalRevenue: {
            value: 'Rp 2.450.000.000',
            change: '+12.5%',
            trend: 'up',
            period: 'Bulan ini'
        },
        totalOrders: {
            value: 156,
            change: '+8.3%',
            trend: 'up',
            period: 'Bulan ini'
        },
        totalCustomers: {
            value: 89,
            change: '+5.2%',
            trend: 'up',
            period: 'Active'
        },
        activeInvoices: {
            value: 45,
            change: '-3.1%',
            trend: 'down',
            period: 'Pending'
        }
    },
    
    finance: {
        accountsReceivable: 'Rp 850.000.000',
        accountsPayable: 'Rp 420.000.000',
        cashBalance: 'Rp 1.200.000.000',
        netProfit: 'Rp 380.000.000'
    },
    
    agingAnalysis: {
        labels: ['0-30 hari', '31-60 hari', '61-90 hari', '>90 hari'],
        data: [450000000, 250000000, 100000000, 50000000],
        colors: ['#10b981', '#f59e0b', '#f97316', '#ef4444']
    },
    
    salesRevenue: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'],
        data: [1800000000, 2100000000, 1950000000, 2300000000, 2200000000, 2450000000]
    },
    
    topCustomers: [
        { name: 'PT Maju Jaya', revenue: 'Rp 450.000.000', orders: 25 },
        { name: 'CV Sejahtera Abadi', revenue: 'Rp 380.000.000', orders: 18 },
        { name: 'UD Berkah Mandiri', revenue: 'Rp 320.000.000', orders: 15 },
        { name: 'PT Global Prima', revenue: 'Rp 280.000.000', orders: 12 },
        { name: 'CV Karya Sentosa', revenue: 'Rp 250.000.000', orders: 10 }
    ],
    
    topProducts: [
        { name: 'Besi Beton 12mm', qty: 1500, unit: 'batang', revenue: 'Rp 180.000.000' },
        { name: 'Semen Portland 50kg', qty: 2800, unit: 'sak', revenue: 'Rp 168.000.000' },
        { name: 'Pasir Beton', qty: 450, unit: 'm³', revenue: 'Rp 135.000.000' },
        { name: 'Batu Split', qty: 380, unit: 'm³', revenue: 'Rp 114.000.000' },
        { name: 'Kawat Beton', qty: 550, unit: 'roll', revenue: 'Rp 82.500.000' }
    ],
    
    recentDeliveryNotes: [
        { no: 'SJ/2026/02/001', customer: 'PT Maju Jaya', date: '2026-02-15', status: 'delivered' },
        { no: 'SJ/2026/02/002', customer: 'CV Sejahtera', date: '2026-02-15', status: 'in_transit' },
        { no: 'SJ/2026/02/003', customer: 'UD Berkah', date: '2026-02-14', status: 'delivered' },
        { no: 'SJ/2026/02/004', customer: 'PT Global', date: '2026-02-14', status: 'pending' },
        { no: 'SJ/2026/02/005', customer: 'CV Karya', date: '2026-02-13', status: 'delivered' }
    ]
};

// Users Data
const users = [
    { id: 1, name: 'Admin System', email: 'admin@adamjaya.com', role: 'Super Admin', status: 'active', lastLogin: '2026-02-15 08:30' },
    { id: 2, name: 'Budi Santoso', email: 'budi@adamjaya.com', role: 'Sales Manager', status: 'active', lastLogin: '2026-02-15 07:45' },
    { id: 3, name: 'Siti Nurhaliza', email: 'siti@adamjaya.com', role: 'Finance', status: 'active', lastLogin: '2026-02-14 16:20' },
    { id: 4, name: 'Ahmad Wijaya', email: 'ahmad@adamjaya.com', role: 'Warehouse', status: 'active', lastLogin: '2026-02-15 06:15' },
    { id: 5, name: 'Rina Wati', email: 'rina@adamjaya.com', role: 'Purchasing', status: 'active', lastLogin: '2026-02-14 17:30' },
    { id: 6, name: 'Joko Susilo', email: 'joko@adamjaya.com', role: 'Sales', status: 'inactive', lastLogin: '2026-02-10 10:00' }
];

// Roles Data
const roles = [
    { id: 1, name: 'Super Admin', permissions: 'All Access', users: 1, color: 'red' },
    { id: 2, name: 'Sales Manager', permissions: 'Sales, Customers, Reports', users: 3, color: 'blue' },
    { id: 3, name: 'Finance', permissions: 'Payments, Invoices, Reports', users: 2, color: 'green' },
    { id: 4, name: 'Warehouse', permissions: 'Inventory, Delivery Notes', users: 4, color: 'orange' },
    { id: 5, name: 'Purchasing', permissions: 'Purchase Orders, Suppliers', users: 2, color: 'purple' },
    { id: 6, name: 'Sales', permissions: 'Customers, Quotations, Invoices', users: 5, color: 'cyan' }
];

// Customers Data
const customers = [
    { id: 1, code: 'CUST001', name: 'PT Maju Jaya Konstruksi', contact: 'Bambang Suryadi', phone: '021-5551234', email: 'bambang@majujaya.com', city: 'Jakarta', type: 'PPN', status: 'active', credit_limit: 'Rp 500.000.000', outstanding: 'Rp 125.000.000' },
    { id: 2, code: 'CUST002', name: 'CV Sejahtera Abadi', contact: 'Sutrisno', phone: '021-5555678', email: 'info@sejahtera.com', city: 'Tangerang', type: 'PPN', status: 'active', credit_limit: 'Rp 300.000.000', outstanding: 'Rp 85.000.000' },
    { id: 3, code: 'CUST003', name: 'UD Berkah Mandiri', contact: 'Hendra Wijaya', phone: '021-5559876', email: 'hendra@berkah.com', city: 'Bekasi', type: 'Non-PPN', status: 'active', credit_limit: 'Rp 200.000.000', outstanding: 'Rp 45.000.000' },
    { id: 4, code: 'CUST004', name: 'PT Global Prima Indonesia', contact: 'Lisa Kartika', phone: '021-5554321', email: 'lisa@globalprima.com', city: 'Jakarta', type: 'PPN', status: 'active', credit_limit: 'Rp 400.000.000', outstanding: 'Rp 95.000.000' },
    { id: 5, code: 'CUST005', name: 'CV Karya Sentosa', contact: 'Agus Salim', phone: '021-5556789', email: 'agus@karyasentosa.com', city: 'Depok', type: 'PPN', status: 'active', credit_limit: 'Rp 250.000.000', outstanding: 'Rp 32.000.000' }
];

// Products/Stock Data
const products = [
    { id: 1, code: 'BRG001', name: 'Besi Beton 12mm', category: 'Besi', unit: 'batang', stock: 1500, min_stock: 500, price: 'Rp 120.000', supplier: 'PT Baja Prima', warehouse: 'Gudang A' },
    { id: 2, code: 'BRG002', name: 'Semen Portland 50kg', category: 'Semen', unit: 'sak', stock: 2800, min_stock: 1000, price: 'Rp 60.000', supplier: 'PT Semen Jaya', warehouse: 'Gudang B' },
    { id: 3, code: 'BRG003', name: 'Pasir Beton', category: 'Agregat', unit: 'm³', stock: 450, min_stock: 200, price: 'Rp 300.000', supplier: 'CV Pasir Sejahtera', warehouse: 'Gudang C' },
    { id: 4, code: 'BRG004', name: 'Batu Split', category: 'Agregat', unit: 'm³', stock: 380, min_stock: 150, price: 'Rp 300.000', supplier: 'CV Batu Mandiri', warehouse: 'Gudang C' },
    { id: 5, code: 'BRG005', name: 'Kawat Beton', category: 'Besi', unit: 'roll', stock: 550, min_stock: 200, price: 'Rp 150.000', supplier: 'PT Baja Prima', warehouse: 'Gudang A' },
    { id: 6, code: 'BRG006', name: 'Cat Tembok Putih 25kg', category: 'Cat', unit: 'pail', stock: 120, min_stock: 100, price: 'Rp 280.000', supplier: 'PT Cat Cemerlang', warehouse: 'Gudang D' },
    { id: 7, code: 'BRG007', name: 'Pipa PVC 3"', category: 'Pipa', unit: 'batang', stock: 350, min_stock: 150, price: 'Rp 85.000', supplier: 'PT Pipa Unggul', warehouse: 'Gudang E' }
];

// Suppliers Data
const suppliers = [
    { id: 1, supplier_code: 'SUP001', name: 'PT Baja Prima Indonesia', contact_person: 'Susanto', phone: '021-7771234', email: 'susanto@bajaprima.com', city: 'Jakarta', address: 'Jl. Industri No. 123', type: 'lokal', is_active: true },
    { id: 2, supplier_code: 'SUP002', name: 'PT Semen Jaya Abadi', contact_person: 'Dewi Lestari', phone: '021-7775678', email: 'dewi@semenjaya.com', city: 'Bekasi', address: 'Jl. Raya Bekasi No. 45', type: 'lokal', is_active: true },
    { id: 3, supplier_code: 'SUP003', name: 'CV Pasir Sejahtera', contact_person: 'Rahmat Hidayat', phone: '021-7779876', email: 'rahmat@pasirsejahtera.com', city: 'Tangerang', address: 'Jl. Pasir Putih No. 67', type: 'lokal', is_active: true },
    { id: 4, supplier_code: 'SUP004', name: 'PT Cat Cemerlang', contact_person: 'Linda Sari', phone: '021-7774321', email: 'linda@catcemerlang.com', city: 'Jakarta', address: 'Jl. Cat Warna No. 89', type: 'lokal', is_active: true },
    { id: 5, supplier_code: 'SUP005', name: 'PT Pipa Unggul Indonesia', contact_person: 'Budi Prasetyo', phone: '021-7776789', email: 'budi@pipaunggul.com', city: 'Bogor', address: 'Jl. Pipa Jaya No. 12', type: 'lokal', is_active: true },
    { id: 6, supplier_code: 'SUP006', name: 'Shanghai Steel Import Co', contact_person: 'Li Wei', phone: '+86-21-12345678', email: 'li.wei@shanghasteel.com', city: 'Shanghai, China', address: '123 Steel Road, Shanghai', type: 'import', is_active: true },
    { id: 7, supplier_code: 'SUP007', name: 'Tokyo Materials Ltd', contact_person: 'Takeshi Yamamoto', phone: '+81-3-87654321', email: 'takeshi@tokyomaterials.jp', city: 'Tokyo, Japan', address: '456 Materials Ave, Tokyo', type: 'import', is_active: true },
    { id: 8, supplier_code: 'SUP008', name: 'Korean Heavy Industries', contact_person: 'Park Min-jun', phone: '+82-2-11223344', email: 'park@koreanhi.kr', city: 'Seoul, Korea', address: '789 Industrial St, Seoul', type: 'import', is_active: false }
];

// Purchase Orders Data
const purchaseOrders = [
    { id: 1, po_number: 'PO/2026/02/001', date: '2026-02-10', supplier: 'PT Baja Prima', items: 3, total: 'Rp 180.000.000', status: 'received', payment_status: 'paid' },
    { id: 2, po_number: 'PO/2026/02/002', date: '2026-02-12', supplier: 'PT Semen Jaya', items: 2, total: 'Rp 168.000.000', status: 'received', payment_status: 'partial' },
    { id: 3, po_number: 'PO/2026/02/003', date: '2026-02-13', supplier: 'CV Pasir Sejahtera', items: 2, total: 'Rp 90.000.000', status: 'pending', payment_status: 'unpaid' },
    { id: 4, po_number: 'PO/2026/02/004', date: '2026-02-14', supplier: 'PT Cat Cemerlang', items: 4, total: 'Rp 112.000.000', status: 'partial', payment_status: 'unpaid' },
    { id: 5, po_number: 'PO/2026/02/005', date: '2026-02-15', supplier: 'PT Pipa Unggul', items: 3, total: 'Rp 85.500.000', status: 'pending', payment_status: 'unpaid' }
];

// Invoices Data
const invoices = [
    { id: 1, invoice_number: 'INV/2026/02/001', date: '2026-02-01', customer: 'PT Maju Jaya', type: 'PPN', total: 'Rp 132.000.000', paid: 'Rp 132.000.000', outstanding: 'Rp 0', due_date: '2026-03-01', status: 'paid' },
    { id: 2, invoice_number: 'INV/2026/02/002', date: '2026-02-05', customer: 'CV Sejahtera', type: 'PPN', total: 'Rp 95.000.000', paid: 'Rp 50.000.000', outstanding: 'Rp 45.000.000', due_date: '2026-03-05', status: 'partial' },
    { id: 3, invoice_number: 'INV/2026/02/003', date: '2026-02-08', customer: 'UD Berkah', type: 'Non-PPN', total: 'Rp 78.000.000', paid: 'Rp 0', outstanding: 'Rp 78.000.000', due_date: '2026-03-08', status: 'unpaid' },
    { id: 4, invoice_number: 'INV/2026/02/004', date: '2026-02-10', customer: 'PT Global Prima', type: 'PPN', total: 'Rp 156.000.000', paid: 'Rp 156.000.000', outstanding: 'Rp 0', due_date: '2026-03-10', status: 'paid' },
    { id: 5, invoice_number: 'INV/2026/02/005', date: '2026-02-12', customer: 'CV Karya', type: 'PPN', total: 'Rp 88.000.000', paid: 'Rp 30.000.000', outstanding: 'Rp 58.000.000', due_date: '2026-03-12', status: 'partial' },
    { id: 6, invoice_number: 'INV/2026/02/006', date: '2026-02-15', customer: 'PT Maju Jaya', type: 'PPN', total: 'Rp 112.000.000', paid: 'Rp 0', outstanding: 'Rp 112.000.000', due_date: '2026-03-15', status: 'unpaid' }
];

// Delivery Notes Data
const deliveryNotes = [
    { id: 1, sj_number: 'SJ/2026/02/001', date: '2026-02-15', customer: 'PT Maju Jaya', invoice: 'INV/2026/02/006', items: 5, driver: 'Joko', vehicle: 'B 1234 AB', status: 'delivered' },
    { id: 2, sj_number: 'SJ/2026/02/002', date: '2026-02-15', customer: 'CV Sejahtera', invoice: 'INV/2026/02/005', items: 3, driver: 'Agus', vehicle: 'B 5678 CD', status: 'in_transit' },
    { id: 3, sj_number: 'SJ/2026/02/003', date: '2026-02-14', customer: 'UD Berkah', invoice: 'INV/2026/02/003', items: 4, driver: 'Bambang', vehicle: 'B 9012 EF', status: 'delivered' },
    { id: 4, sj_number: 'SJ/2026/02/004', date: '2026-02-14', customer: 'PT Global Prima', invoice: 'INV/2026/02/004', items: 6, driver: 'Andi', vehicle: 'B 3456 GH', status: 'delivered' },
    { id: 5, sj_number: 'SJ/2026/02/005', date: '2026-02-13', customer: 'CV Karya', invoice: 'INV/2026/02/005', items: 3, driver: 'Rudi', vehicle: 'B 7890 IJ', status: 'delivered' }
];

// Price Quotations Data
const priceQuotations = [
    { id: 1, quotation_number: 'QT/2026/02/001', date: '2026-02-10', customer: 'PT Maju Jaya', valid_until: '2026-02-24', items: 5, total: 'Rp 145.000.000', status: 'approved', converted: 'Yes' },
    { id: 2, quotation_number: 'QT/2026/02/002', date: '2026-02-12', customer: 'CV Sejahtera', valid_until: '2026-02-26', items: 3, total: 'Rp 98.000.000', status: 'pending', converted: 'No' },
    { id: 3, quotation_number: 'QT/2026/02/003', date: '2026-02-13', customer: 'PT Global Prima', valid_until: '2026-02-27', items: 6, total: 'Rp 178.000.000', status: 'approved', converted: 'Yes' },
    { id: 4, quotation_number: 'QT/2026/02/004', date: '2026-02-14', customer: 'UD Berkah', valid_until: '2026-02-28', items: 4, total: 'Rp 82.000.000', status: 'pending', converted: 'No' },
    { id: 5, quotation_number: 'QT/2026/02/005', date: '2026-02-15', customer: 'CV Karya', valid_until: '2026-03-01', items: 3, total: 'Rp 65.000.000', status: 'draft', converted: 'No' }
];

// Payments Data (from customers)
const payments = [
    { id: 1, payment_number: 'PAY/2026/02/001', date: '2026-02-05', customer: 'PT Maju Jaya', invoice: 'INV/2026/02/001', amount: 'Rp 132.000.000', method: 'Transfer', bank: 'BCA', status: 'confirmed' },
    { id: 2, payment_number: 'PAY/2026/02/002', date: '2026-02-08', customer: 'CV Sejahtera', invoice: 'INV/2026/02/002', amount: 'Rp 50.000.000', method: 'Transfer', bank: 'Mandiri', status: 'confirmed' },
    { id: 3, payment_number: 'PAY/2026/02/003', date: '2026-02-12', customer: 'PT Global Prima', invoice: 'INV/2026/02/004', amount: 'Rp 156.000.000', method: 'Giro', bank: 'BNI', status: 'pending' },
    { id: 4, payment_number: 'PAY/2026/02/004', date: '2026-02-14', customer: 'CV Karya', invoice: 'INV/2026/02/005', amount: 'Rp 30.000.000', method: 'Cash', bank: '-', status: 'confirmed' }
];

// Payable Payments Data (to suppliers)
const payablePayments = [
    { id: 1, payment_number: 'PP/2026/02/001', date: '2026-02-08', supplier: 'PT Baja Prima', po: 'PO/2026/02/001', amount: 'Rp 180.000.000', method: 'Transfer', bank: 'BCA', status: 'paid' },
    { id: 2, payment_number: 'PP/2026/02/002', date: '2026-02-12', supplier: 'PT Semen Jaya', po: 'PO/2026/02/002', amount: 'Rp 100.000.000', method: 'Transfer', bank: 'Mandiri', status: 'paid' },
    { id: 3, payment_number: 'PP/2026/02/003', date: '2026-02-15', supplier: 'CV Pasir Sejahtera', po: 'PO/2026/02/003', amount: 'Rp 90.000.000', method: 'Cash', bank: '-', status: 'pending' }
];

// Export all data
if (typeof module !== 'undefined' && module.exports) {
// Export ke window object untuk browser
if (typeof window !== 'undefined') {
    window.dummyData = {
        dashboardStats,
        users,
        roles,
        customers,
        products,
        suppliers,
        purchaseOrders,
        invoices,
        deliveryNotes,
        priceQuotations,
        payments,
        payablePayments
    };
}

// Node.js export (untuk compatibility)
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        dashboardStats,
        users,
        roles,
        customers,
        products,
        suppliers,
        purchaseOrders,
        invoices,
        deliveryNotes,
        priceQuotations,
        payments,
        payablePayments
    };
}
