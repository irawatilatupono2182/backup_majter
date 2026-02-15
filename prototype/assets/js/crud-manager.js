// ============================================
// CRUD MANAGER - Interactive CRUD Operations
// ============================================

class CRUDManager {
    constructor() {
        this.currentData = [];
        this.currentModule = '';
        this.editingId = null;
    }

    // Initialize CRUD for a module
    init(module, data) {
        this.currentModule = module;
        this.currentData = JSON.parse(JSON.stringify(data)); // Deep clone
        this.setupEventListeners();
    }

    setupEventListeners() {
        // Create button
        const createBtn = document.querySelector('[data-action="create"]');
        if (createBtn) {
            createBtn.addEventListener('click', () => this.showCreateModal());
        }

        // Search
        const searchInput = document.querySelector('[data-search]');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => this.handleSearch(e.target.value));
        }

        // Filter
        const filterSelects = document.querySelectorAll('[data-filter]');
        filterSelects.forEach(select => {
            select.addEventListener('change', () => this.handleFilter());
        });

        // Sort
        const sortHeaders = document.querySelectorAll('[data-sort]');
        sortHeaders.forEach(header => {
            header.style.cursor = 'pointer';
            header.addEventListener('click', () => {
                const field = header.dataset.sort;
                this.handleSort(field);
            });
        });

        // Pagination
        const paginationBtns = document.querySelectorAll('[data-page]');
        paginationBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                const page = e.target.dataset.page;
                this.handlePagination(page);
            });
        });
    }

    // CREATE
    showCreateModal() {
        const modal = this.createModal('create');
        document.body.appendChild(modal);
    }

    // READ/VIEW
    showViewModal(id) {
        const item = this.currentData.find(d => d.id === id);
        if (!item) return;

        const modal = this.createModal('view', item);
        document.body.appendChild(modal);
    }

    // UPDATE/EDIT
    showEditModal(id) {
        const item = this.currentData.find(d => d.id === id);
        if (!item) return;

        this.editingId = id;
        const modal = this.createModal('edit', item);
        document.body.appendChild(modal);
    }

    // DELETE
    showDeleteConfirmation(id) {
        const item = this.currentData.find(d => d.id === id);
        if (!item) return;

        const modal = document.createElement('div');
        modal.className = 'modal-overlay';
        modal.innerHTML = `
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>🗑️ Konfirmasi Hapus</h3>
                        <button class="modal-close" onclick="this.closest('.modal-overlay').remove()">×</button>
                    </div>
                    <div class="modal-body">
                        <p>Apakah Anda yakin ingin menghapus data ini?</p>
                        <p class="text-gray"><strong>${this.getItemDisplayName(item)}</strong></p>
                        <p class="text-danger"><small>⚠️ Tindakan ini tidak dapat dibatalkan!</small></p>
                    </div>
                    <div class="modal-footer">
                        <button class="btn outline" onclick="this.closest('.modal-overlay').remove()">
                            Batal
                        </button>
                        <button class="btn danger" onclick="crudManager.confirmDelete(${id})">
                            🗑️ Hapus
                        </button>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
    }

    confirmDelete(id) {
        // Remove from data
        const index = this.currentData.findIndex(d => d.id === id);
        if (index > -1) {
            this.currentData.splice(index, 1);
            this.refreshTable();
            this.showToast('Data berhasil dihapus', 'success');
            
            // Close modal
            document.querySelector('.modal-overlay')?.remove();
        }
    }

    // Create Modal HTML
    createModal(mode, data = null) {
        const modal = document.createElement('div');
        modal.className = 'modal-overlay';
        
        const title = mode === 'create' ? '➕ Tambah Data' : 
                     mode === 'edit' ? '✏️ Edit Data' : 
                     '👁️ Detail Data';
        
        const isReadOnly = mode === 'view';
        
        modal.innerHTML = `
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>${title}</h3>
                        <button class="modal-close" onclick="this.closest('.modal-overlay').remove()">×</button>
                    </div>
                    <div class="modal-body">
                        ${this.getFormHTML(mode, data, isReadOnly)}
                    </div>
                    <div class="modal-footer">
                        <button class="btn outline" onclick="this.closest('.modal-overlay').remove()">
                            ${isReadOnly ? 'Tutup' : 'Batal'}
                        </button>
                        ${!isReadOnly ? `
                            <button class="btn primary" onclick="crudManager.submitForm('${mode}')">
                                ${mode === 'create' ? '➕ Simpan' : '✏️ Update'}
                            </button>
                        ` : ''}
                    </div>
                </div>
            </div>
        `;
        
        return modal;
    }

    // Get form HTML based on module
    getFormHTML(mode, data, isReadOnly) {
        const readonly = isReadOnly ? 'readonly' : '';
        const disabled = isReadOnly ? 'disabled' : '';
        
        // Generic form - customize per module
        switch(this.currentModule) {
            case 'customers':
                return `
                    <div class="form-group">
                        <label>Kode Customer *</label>
                        <input type="text" class="form-control" name="code" value="${data?.code || ''}" ${readonly} required>
                    </div>
                    <div class="form-group">
                        <label>Nama Customer *</label>
                        <input type="text" class="form-control" name="name" value="${data?.name || ''}" ${readonly} required>
                    </div>
                    <div class="form-group">
                        <label>Contact Person</label>
                        <input type="text" class="form-control" name="contact" value="${data?.contact || ''}" ${readonly}>
                    </div>
                    <div class="form-group">
                        <label>Phone *</label>
                        <input type="tel" class="form-control" name="phone" value="${data?.phone || ''}" ${readonly} required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" class="form-control" name="email" value="${data?.email || ''}" ${readonly}>
                    </div>
                    <div class="form-group">
                        <label>Kota</label>
                        <input type="text" class="form-control" name="city" value="${data?.city || ''}" ${readonly}>
                    </div>
                    <div class="form-group">
                        <label>Tipe</label>
                        <select class="form-control" name="type" ${disabled}>
                            <option value="PPN" ${data?.type === 'PPN' ? 'selected' : ''}>PPN</option>
                            <option value="Non-PPN" ${data?.type === 'Non-PPN' ? 'selected' : ''}>Non-PPN</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Credit Limit</label>
                        <input type="text" class="form-control" name="credit_limit" value="${data?.credit_limit || ''}" ${readonly}>
                    </div>
                `;
            
            case 'invoices':
                return `
                    <div class="form-group">
                        <label>No. Invoice *</label>
                        <input type="text" class="form-control" name="invoice_number" value="${data?.invoice_number || ''}" ${readonly} required>
                    </div>
                    <div class="form-group">
                        <label>Tanggal *</label>
                        <input type="date" class="form-control" name="date" value="${data?.date || ''}" ${readonly} required>
                    </div>
                    <div class="form-group">
                        <label>Customer *</label>
                        <select class="form-control" name="customer" ${disabled} required>
                            <option value="">Pilih Customer</option>
                            ${customers.map(c => `
                                <option value="${c.name}" ${data?.customer === c.name ? 'selected' : ''}>${c.name}</option>
                            `).join('')}
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Tipe</label>
                        <select class="form-control" name="type" ${disabled}>
                            <option value="PPN" ${data?.type === 'PPN' ? 'selected' : ''}>PPN</option>
                            <option value="Non-PPN" ${data?.type === 'Non-PPN' ? 'selected' : ''}>Non-PPN</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Total *</label>
                        <input type="text" class="form-control" name="total" value="${data?.total || ''}" ${readonly} required>
                    </div>
                    <div class="form-group">
                        <label>Due Date *</label>
                        <input type="date" class="form-control" name="due_date" value="${data?.due_date || ''}" ${readonly} required>
                    </div>
                `;
            
            case 'products':
                return `
                    <div class="form-group">
                        <label>Kode Barang *</label>
                        <input type="text" class="form-control" name="code" value="${data?.code || ''}" ${readonly} required>
                    </div>
                    <div class="form-group">
                        <label>Nama Barang *</label>
                        <input type="text" class="form-control" name="name" value="${data?.name || ''}" ${readonly} required>
                    </div>
                    <div class="form-group">
                        <label>Kategori</label>
                        <select class="form-control" name="category" ${disabled}>
                            <option value="Besi" ${data?.category === 'Besi' ? 'selected' : ''}>Besi</option>
                            <option value="Semen" ${data?.category === 'Semen' ? 'selected' : ''}>Semen</option>
                            <option value="Agregat" ${data?.category === 'Agregat' ? 'selected' : ''}>Agregat</option>
                            <option value="Cat" ${data?.category === 'Cat' ? 'selected' : ''}>Cat</option>
                            <option value="Pipa" ${data?.category === 'Pipa' ? 'selected' : ''}>Pipa</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Satuan *</label>
                        <input type="text" class="form-control" name="unit" value="${data?.unit || ''}" ${readonly} required>
                    </div>
                    <div class="form-group">
                        <label>Stock *</label>
                        <input type="number" class="form-control" name="stock" value="${data?.stock || ''}" ${readonly} required>
                    </div>
                    <div class="form-group">
                        <label>Min. Stock *</label>
                        <input type="number" class="form-control" name="min_stock" value="${data?.min_stock || ''}" ${readonly} required>
                    </div>
                    <div class="form-group">
                        <label>Harga *</label>
                        <input type="text" class="form-control" name="price" value="${data?.price || ''}" ${readonly} required>
                    </div>
                `;
            
            default:
                return `<p>Form untuk module ${this.currentModule} belum tersedia.</p>`;
        }
    }

    // Submit form
    submitForm(mode) {
        const form = document.querySelector('.modal-body');
        const formData = new FormData(form.querySelector('form') || form);
        
        // Get form values
        const data = {};
        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            if (input.name) {
                data[input.name] = input.value;
            }
        });

        // Validate required fields
        const requiredFields = form.querySelectorAll('[required]');
        let isValid = true;
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.style.borderColor = 'var(--danger)';
                isValid = false;
            } else {
                field.style.borderColor = '';
            }
        });

        if (!isValid) {
            this.showToast('Mohon lengkapi semua field yang wajib diisi', 'danger');
            return;
        }

        if (mode === 'create') {
            // Add new data
            data.id = this.currentData.length + 1;
            data.status = 'active';
            this.currentData.push(data);
            this.showToast('Data berhasil ditambahkan', 'success');
        } else if (mode === 'edit') {
            // Update existing data
            const index = this.currentData.findIndex(d => d.id === this.editingId);
            if (index > -1) {
                this.currentData[index] = { ...this.currentData[index], ...data };
                this.showToast('Data berhasil diupdate', 'success');
            }
        }

        // Refresh table
        this.refreshTable();

        // Close modal
        document.querySelector('.modal-overlay')?.remove();
    }

    // Handle Search
    handleSearch(query) {
        query = query.toLowerCase();
        const rows = document.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            if (text.includes(query)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Handle Filter
    handleFilter() {
        const filters = {};
        document.querySelectorAll('[data-filter]').forEach(select => {
            if (select.value) {
                filters[select.dataset.filter] = select.value;
            }
        });

        const rows = document.querySelectorAll('tbody tr');
        rows.forEach(row => {
            let show = true;
            for (const [key, value] of Object.entries(filters)) {
                const cell = row.querySelector(`[data-field="${key}"]`);
                if (cell && !cell.textContent.includes(value)) {
                    show = false;
                    break;
                }
            }
            row.style.display = show ? '' : 'none';
        });
    }

    // Handle Sort
    handleSort(field) {
        // Toggle sort direction
        const currentDir = this.sortDirection || 'asc';
        this.sortDirection = currentDir === 'asc' ? 'desc' : 'asc';
        
        // Sort data
        this.currentData.sort((a, b) => {
            let aVal = a[field];
            let bVal = b[field];
            
            // Handle numbers
            if (!isNaN(aVal) && !isNaN(bVal)) {
                aVal = parseFloat(aVal);
                bVal = parseFloat(bVal);
            }
            
            if (this.sortDirection === 'asc') {
                return aVal > bVal ? 1 : -1;
            } else {
                return aVal < bVal ? 1 : -1;
            }
        });
        
        this.refreshTable();
        this.showToast(`Sorted by ${field} (${this.sortDirection})`, 'info');
    }

    // Handle Pagination
    handlePagination(page) {
        // Dummy implementation - you can enhance this
        this.showToast(`Navigate to page ${page}`, 'info');
    }

    // Refresh table with current data
    refreshTable() {
        const tbody = document.querySelector('tbody');
        if (!tbody) return;

        // Clear and repopulate table
        // This needs to be customized per module
        console.log('Table refreshed with:', this.currentData);
    }

    // Get item display name for delete confirmation
    getItemDisplayName(item) {
        return item.name || item.code || item.invoice_number || `ID: ${item.id}`;
    }

    // Show toast notification
    showToast(message, type = 'info') {
        const icons = {
            success: '✓',
            danger: '✗',
            warning: '⚠️',
            info: 'ℹ️'
        };

        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: white;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            border-left: 4px solid var(--${type});
            z-index: 10000;
            min-width: 300px;
            display: flex;
            align-items: center;
            gap: 10px;
        `;
        
        toast.innerHTML = `
            <span style="font-size: 20px;">${icons[type]}</span>
            <span>${message}</span>
        `;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.style.transition = 'opacity 0.3s';
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
}

// Global instance
const crudManager = new CRUDManager();

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = CRUDManager;
}
