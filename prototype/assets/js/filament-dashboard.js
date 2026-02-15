/**
 * Filament Dashboard JavaScript
 * Handles sidebar collapse and dashboard interactions
 */

// Toggle sidebar collapsed state
function toggleSidebar() {
    const sidebar = document.querySelector('.filament-sidebar');
    const isCollapsed = sidebar.classList.toggle('collapsed');
    
    // Save state to localStorage
    localStorage.setItem('sidebarCollapsed', isCollapsed ? 'true' : 'false');
    
    // Trigger resize event for charts
    window.dispatchEvent(new Event('resize'));
}

// Initialize sidebar state from localStorage
function initSidebarState() {
    const sidebarCollapsed = localStorage.getItem('sidebarCollapsed');
    const sidebar = document.querySelector('.filament-sidebar');
    
    if (sidebarCollapsed === 'true' && sidebar) {
        sidebar.classList.add('collapsed');
    }
}

// Initialize user dropdown menu
function initUserMenu() {
    const userAvatar = document.querySelector('.user-avatar');
    const userDropdown = document.querySelector('.user-dropdown');
    
    if (!userAvatar || !userDropdown) return;
    
    // Close dropdown when clicking outside
    document.addEventListener('click', (e) => {
        if (!userAvatar.contains(e.target) && !userDropdown.contains(e.target)) {
            userDropdown.style.opacity = '0';
            userDropdown.style.visibility = 'hidden';
            userDropdown.style.transform = 'translateY(-0.5rem)';
        }
    });
}

// Handle navigation item clicks
function initNavigation() {
    const navItems = document.querySelectorAll('.nav-item');
    
    navItems.forEach(item => {
        item.addEventListener('click', (e) => {
            // Don't navigate if it's a parent item with children
            const hasChildren = item.nextElementSibling?.classList.contains('nav-group');
            
            if (hasChildren) {
                e.preventDefault();
                
                // Toggle child items visibility
                const siblingGroup = item.nextElementSibling;
                if (siblingGroup) {
                    const isExpanded = siblingGroup.style.display !== 'none';
                    siblingGroup.style.display = isExpanded ? 'none' : 'block';
                    
                    // Rotate icon
                    const icon = item.querySelector('.nav-icon');
                    if (icon) {
                        icon.style.transform = isExpanded ? 'rotate(0deg)' : 'rotate(90deg)';
                    }
                }
            } else {
                // Remove active class from all items
                navItems.forEach(navItem => navItem.classList.remove('active'));
                
                // Add active class to clicked item
                item.classList.add('active');
            }
        });
    });
}

// Initialize stats animations on scroll
function initStatsAnimations() {
    const stats = document.querySelectorAll('.stat-value');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const target = entry.target;
                const value = target.textContent;
                
                // Animate number if it's numeric
                if (!isNaN(parseFloat(value))) {
                    animateValue(target, 0, parseFloat(value), 1000);
                }
                
                observer.unobserve(target);
            }
        });
    }, { threshold: 0.5 });
    
    stats.forEach(stat => observer.observe(stat));
}

// Animate number value
function animateValue(element, start, end, duration) {
    const startTime = performance.now();
    const isPercentage = element.textContent.includes('%');
    const isCurrency = element.textContent.includes('Rp') || element.textContent.includes('$');
    
    function update(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        
        // Easing function
        const easeOutQuart = 1 - Math.pow(1 - progress, 4);
        const current = start + (end - start) * easeOutQuart;
        
        if (isCurrency) {
            element.textContent = 'Rp ' + Math.floor(current).toLocaleString('id-ID');
        } else if (isPercentage) {
            element.textContent = current.toFixed(1) + '%';
        } else {
            element.textContent = Math.floor(current).toLocaleString('id-ID');
        }
        
        if (progress < 1) {
            requestAnimationFrame(update);
        }
    }
    
    requestAnimationFrame(update);
}

// Handle search functionality
function initSearch() {
    const searchInputs = document.querySelectorAll('input[type="search"], input[placeholder*="Cari"]');
    
    searchInputs.forEach(input => {
        input.addEventListener('input', (e) => {
            const searchTerm = e.target.value.toLowerCase();
            const table = input.closest('.card')?.querySelector('table');
            
            if (!table) return;
            
            const rows = table.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
    });
}

// Handle table row clicks
function initTableInteractions() {
    const tableRows = document.querySelectorAll('tbody tr');
    
    tableRows.forEach(row => {
        row.addEventListener('click', (e) => {
            // Skip if clicking on action buttons
            if (e.target.closest('.table-actions')) return;
            
            // Add selected state
            tableRows.forEach(r => r.classList.remove('selected'));
            row.classList.add('selected');
        });
    });
}

// Handle notifications
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    
    notification.style.cssText = `
        position: fixed;
        top: 5rem;
        right: 1.5rem;
        background: ${type === 'success' ? 'var(--success-500)' : 'var(--danger-500)'};
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 0.5rem;
        box-shadow: 0 10px 25px -5px rgb(0 0 0 / 0.2);
        z-index: 9999;
        animation: slideIn 0.3s ease-out;
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease-out';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Add animation keyframes
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
    
    tbody tr.selected {
        background: var(--primary-50) !important;
    }
`;
document.head.appendChild(style);

// Initialize navigation group collapse
function initNavGroups() {
    const navGroups = document.querySelectorAll('.nav-group');
    
    navGroups.forEach(group => {
        const label = group.querySelector('.nav-group-label');
        if (label) {
            // Load saved state from localStorage
            const groupText = label.textContent.trim();
            const storageKey = `nav-group-collapsed-${groupText}`;
            const isCollapsed = localStorage.getItem(storageKey) === 'true';
            
            if (isCollapsed) {
                group.classList.add('collapsed');
            }
            
            // Add click event
            label.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                
                group.classList.toggle('collapsed');
                
                // Save state
                const collapsed = group.classList.contains('collapsed');
                localStorage.setItem(storageKey, collapsed);
            });
        }
    });
}

// Initialize everything when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    // Initialize sidebar state
    initSidebarState();
    
    // Attach sidebar toggle event
    const toggleBtn = document.querySelector('.sidebar-toggle');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', toggleSidebar);
    }
    
    // Initialize other features
    initUserMenu();
    initNavigation();
    initNavGroups();
    initStatsAnimations();
    initSearch();
    initTableInteractions();
    
    console.log('Filament Dashboard initialized');
});

// Export functions for external use
window.FilamentDashboard = {
    toggleSidebar,
    showNotification
};
