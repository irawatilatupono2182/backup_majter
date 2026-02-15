// ===========================
// ADAM JAYA PROTOTYPE APP
// ===========================

// Global App Object
const AdamJayaApp = {
    currentPage: 'dashboard',
    currentUser: {
        name: 'Admin User',
        role: 'Super Admin',
        avatar: 'AU'
    },
    
    // Initialize App
    init() {
        this.setActiveNav();
        this.initEventListeners();
        this.updateTime();
        console.log('Adam Jaya Prototype App Initialized');
    },
    
    // Set Active Navigation
    setActiveNav() {
        const currentPath = window.location.pathname;
        const navLinks = document.querySelectorAll('.nav-link');
        
        navLinks.forEach(link => {
            const href = link.getAttribute('href');
            if (currentPath.includes(href) || (currentPath === '/' && href === 'index.html')) {
                link.classList.add('active');
            } else {
                link.classList.remove('active');
            }
        });
    },
    
    // Initialize Event Listeners
    initEventListeners() {
        // Search functionality
        const searchInput = document.querySelector('.header-search input');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                console.log('Searching:', e.target.value);
            });
        }
        
        // Action buttons
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('action-btn')) {
                this.handleActionButton(e);
            }
        });
    },
    
    // Handle Action Buttons
    handleActionButton(e) {
        e.preventDefault();
        const btn = e.target.closest('.action-btn');
        const action = btn.classList.contains('edit') ? 'edit' : 
                      btn.classList.contains('delete') ? 'delete' : 
                      btn.classList.contains('view') ? 'view' : 'unknown';
        
        console.log(`Action: ${action}`);
        
        if (action === 'delete') {
            if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
                this.showNotification('Data berhasil dihapus', 'success');
            }
        } else if (action === 'edit') {
            this.showNotification('Fitur edit akan segera hadir', 'info');
        } else if (action === 'view') {
            this.showNotification('Fitur view detail akan segera hadir', 'info');
        }
    },
    
    // Show Notification
    showNotification(message, type = 'info') {
        const alertClass = `alert-${type}`;
        const alert = document.createElement('div');
        alert.className = `alert ${alertClass}`;
        alert.style.position = 'fixed';
        alert.style.top = '80px';
        alert.style.right = '20px';
        alert.style.zIndex = '10000';
        alert.style.minWidth = '300px';
        alert.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
        alert.innerHTML = `
            <span style="font-size: 20px;">
                ${type === 'success' ? '✓' : type === 'danger' ? '✗' : 'ℹ'}
            </span>
            <span>${message}</span>
        `;
        
        document.body.appendChild(alert);
        
        setTimeout(() => {
            alert.style.transition = 'opacity 0.3s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, 3000);
    },
    
    // Update Current Time
    updateTime() {
        const updateClock = () => {
            const now = new Date();
            const timeStr = now.toLocaleTimeString('id-ID', { 
                hour: '2-digit', 
                minute: '2-digit',
                second: '2-digit'
            });
            const dateStr = now.toLocaleDateString('id-ID', { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            });
            
            const timeEl = document.querySelector('.current-time');
            if (timeEl) {
                timeEl.textContent = `${dateStr} - ${timeStr}`;
            }
        };
        
        updateClock();
        setInterval(updateClock, 1000);
    },
    
    // Format Currency
    formatCurrency(amount) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(amount);
    },
    
    // Format Number
    formatNumber(num) {
        return new Intl.NumberFormat('id-ID').format(num);
    },
    
    // Format Date
    formatDate(dateStr) {
        const date = new Date(dateStr);
        return date.toLocaleDateString('id-ID', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    },
    
    // Table Search/Filter
    filterTable(searchTerm, tableId) {
        const table = document.getElementById(tableId);
        if (!table) return;
        
        const rows = table.querySelectorAll('tbody tr');
        const term = searchTerm.toLowerCase();
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(term) ? '' : 'none';
        });
    },
    
    // Sort Table
    sortTable(columnIndex, tableId) {
        const table = document.getElementById(tableId);
        if (!table) return;
        
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        
        rows.sort((a, b) => {
            const aVal = a.cells[columnIndex].textContent;
            const bVal = b.cells[columnIndex].textContent;
            return aVal.localeCompare(bVal);
        });
        
        rows.forEach(row => tbody.appendChild(row));
    }
};

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    AdamJayaApp.init();
});

// Export for use in other scripts
window.AdamJayaApp = AdamJayaApp;
