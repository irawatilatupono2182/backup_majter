PRODUCT REQUIREMENTS DOCUMENT (PRD)
Sistem Informasi Majter (Si-Majter)
Versi: 1.4
Tanggal: 19 Oktober 2025
Dokumen Acuan: Pengembangan Sistem Administrasi Penjualan & Pembelian Terintegrasi

1. Ringkasan Eksekutif
Si-Majter adalah sistem informasi berbasis web yang dirancang untuk mengotomatisasi dan mempercepat proses administrasi penjualan dan pembelian di lingkungan perusahaan multi-entitas. Sistem ini menyediakan manajemen terpusat atas data pelanggan, supplier, produk (termasuk katalog), serta alur dokumen operasional (PH → PO → SP dan SJ → Invoice → Pembayaran), dilengkapi fitur cetak PDF profesional dan laporan penjualan detail berbasis filter.

Tujuan utama:
✅ Meningkatkan kecepatan, akurasi, dan efisiensi proses administrasi
✅ Mengurangi kesalahan manual dalam pembuatan dokumen
✅ Memberikan visibilitas real-time terhadap transaksi dan kinerja penjualan

2. Tujuan & Manfaat Bisnis
Digitalisasi dokumen operasional
Pengurangan kertas, akses cepat, arsip terpusat
Integrasi data pelanggan–supplier–produk
Satu sumber kebenaran (single source of truth)
Dukungan produk katalog
Memperluas penawaran tanpa harus stok fisik
Cetak PDF berbasis template
Konsistensi branding dan profesionalisme dokumen
Laporan penjualan fleksibel
Analisis kinerja berbasis data untuk pengambilan keputusan

3. Ruang Lingkup (Scope)
3.1. Dalam Cakupan (In Scope)
A. Manajemen Master Data
Perusahaan: Multi-company support dengan isolasi data
Pengguna: Login unik, peran berbasis perusahaan (admin, finance, warehouse, viewer)
Pelanggan:
Kode, nama, U.P. (PIC), alamat SHIP TO & BILL TO, NPWP, jadwal kontra bon
Status PPN/Non-PPN
Supplier:
Kode, nama, tipe (Lokal/Impor), kontak, alamat
Produk & Katalog:
Produk aktif (tersedia di gudang)
Produk katalog (belum di-stok, hanya untuk penawaran)
Atribut: kode, nama, deskripsi, satuan, harga dasar, diskon default, kategori, status
B. Modul Purchasing (Alur Internal)
PH (Penawaran Harga)
Jenis: PPN / Non-PPN
Status: Draft, Sent, Accepted, Rejected
PO (Purchase Order)
Dibuat dari PH atau manual
Status: Pending, Confirmed, Partial, Completed, Cancelled
SP (Surat Pengantar Supplier)
Dokumen penerimaan barang dari supplier
Status: Received, Partial, Damaged, Rejected
C. Modul Penjualan (Alur Eksternal)
Surat Jalan (SJ)
Jenis: PPN / Non-PPN
Status: Draft, Sent, Completed
Invoice
Otomatis dibuat dari SJ
Perhitungan PPN (11%), jatuh tempo, status pembayaran
Pembayaran
Pencatatan pembayaran per invoice
Metode: Cash, Transfer, QRIS, dll.
D. Manajemen Inventori
Pelacakan stok berbasis batch
Integrasi otomatis saat penerimaan (SP) dan pengiriman (SJ)
Notifikasi stok minimum
E. Fitur Cetak Dokumen (PDF)
Dokumen yang didukung:

Penawaran Harga (PH)
✅ Ya
✅ PPN & Non-PPN
Purchase Order (PO)
✅ Ya
✅ PPN & Non-PPN
Surat Pengantar (SP)
✅ Ya
✅ (format umum)
Surat Jalan (SJ)
✅ Ya
✅ PPN & Non-PPN
Invoice
✅ Ya
✅ PPN & Non-PPN

Ketentuan Template: 

Desain dapat dikustomisasi per perusahaan (logo, alamat, footer)
Nomor dokumen otomatis (misal: INV/2025/10/001)
Siap cetak (printer-friendly, tanpa elemen UI)
F. Laporan Penjualan Detail
Akses: Role admin dan finance
Filter:
Rentang waktu (invoice_date / delivery_date)
Perusahaan
Status invoice (Unpaid, Partial, Paid, Overdue, Cancelled)
Jenis transaksi (PPN / Non-PPN)
Pelanggan (pencarian by name/code)
Produk (termasuk katalog)
Dibuat oleh (user)
Kolom Laporan:
No Invoice, Tgl Invoice, No SJ, Pelanggan, Produk, Qty, Satuan, Harga Satuan, Diskon, Subtotal, PPN, Grand Total, Status, Perusahaan
Fitur Tambahan:
Urutkan (sort)
Pencarian global
Ekspor ke Excel/CSV
Cetak ringkasan PDF (tabel sederhana)
G. Notifikasi & Pengingat
Reminder otomatis untuk:
Invoice jatuh tempo
PO menunggu konfirmasi
SP perlu diverifikasi
3.2. Di Luar Cakupan (Out of Scope)
Integrasi dengan software akuntansi eksternal (Accurate, Jurnal, dll.)
Modul produksi/manufaktur
Portal pelanggan mandiri (self-service)
Visualisasi grafik (chart, dashboard BI)
Manajemen aset atau HRD
4. Persyaratan Fungsional (Fitur Utama)
Autentikasi
Login Multi-Perusahaan
Pengguna login → pilih perusahaan aktif → akses sesuai role
Produk
Tipe Produk
STOCK
(ada di gudang),
CATALOG
(hanya referensi)
Transaksi
Gunakan Produk Katalog
Bisa dimasukkan ke PH/SJ/Invoice tanpa validasi stok
Dokumen
Generate PDF
Satu klik → PDF sesuai template resmi perusahaan
Laporan
Filter Dinamis
Kombinasi bebas antar filter
Inventori
Batch Tracking
Lacak asal barang (dari PO mana), harga pokok, expired date

5. Persyaratan Non-Fungsional
Keamanan
- Password di-hash (bcrypt)
- Data perusahaan terisolasi
- Role-based access control (RBAC)
Kinerja
- Waktu respons <2 detik untuk operasi CRUD
- Indexing pada kolom filter utama (status, tanggal, company_id)
Ketersediaan
- Sistem online 24/7
- Backup harian
Kompatibilitas
- Web responsif (desktop & tablet)
- Browser: Chrome, Firefox, Edge terbaru
Kemudahan Cetak
- Semua template PDF dioptimalkan untuk ukuran A4

6. Arsitektur Data (Ringkasan)
Sistem menggunakan MySQL dengan struktur utama:

Master: companies, users, user_company_roles, customers, suppliers, products
Purchasing: price_quotations, purchase_orders, supplier_delivery_notes
Penjualan: delivery_notes, invoices, payments
Inventori: inventory_batches
Pendukung: reminders
Catatan Katalog:
Tabel products menggunakan kolom product_type ENUM('STOCK', 'CATALOG') untuk membedakan produk fisik dan katalog. 

7. Pengguna & Peran
Admin
Semua modul, termasuk manajemen pengguna & template
Finance
Penjualan, pembayaran, laporan, purchasing (baca), tidak bisa ubah master user
Warehouse
Inventori, SP, SJ (terbatas), tidak akses invoice/pembayaran
Viewer
Hanya lihat data (read-only)

8. Dokumen Keluaran yang Dihasilkan
Setiap dokumen memiliki:

Header resmi perusahaan
Nomor unik berbasis tahun/bulan
Tanda tangan digital (opsional)
Format siap cetak (A4, margin standar)
9. Asumsi & Ketergantungan
Pengguna memiliki akses internet stabil
Data awal (pelanggan, supplier, produk) diimpor manual atau via CSV
Tidak ada integrasi real-time dengan sistem eksternal pada fase awal
10. Kriteria Keberhasilan
100% dokumen operasional (PH, PO, SP, SJ, Invoice) dapat dibuat & dicetak dalam <3 menit
Laporan penjualan dapat difilter dan diekspor dalam <5 detik (untuk 10.000 baris)
Tidak ada duplikasi data antar perusahaan
Pengguna mampu membuat transaksi pertama dalam 15 menit pelatihan