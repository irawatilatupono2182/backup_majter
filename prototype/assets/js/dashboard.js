// ============================================
// DASHBOARD.JS - Dashboard Logic
// ============================================

document.addEventListener('DOMContentLoaded', function() {
    // Initialize dashboard
    initDashboard();
    renderCharts();
    populateTables();
});

function initDashboard() {
    console.log('Dashboard initialized');
    
    // Animate stat cards
    animateStats();
}

function animateStats() {
    const statValues = document.querySelectorAll('.stat-value');
    statValues.forEach(stat => {
        stat.style.opacity = '0';
        stat.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            stat.style.transition = 'all 0.5s ease';
            stat.style.opacity = '1';
            stat.style.transform = 'translateY(0)';
        }, 100);
    });
}

function renderCharts() {
    // Sales Revenue Chart
    const salesCtx = document.getElementById('salesChart');
    if (salesCtx) {
        new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: dashboardStats.salesRevenue.labels,
                datasets: [{
                    label: 'Revenue (Rp)',
                    data: dashboardStats.salesRevenue.data,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Rp ' + formatNumber(context.parsed.y);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + (value / 1000000000).toFixed(1) + 'B';
                            }
                        }
                    }
                }
            }
        });
    }

    // Aging Analysis Chart
    const agingCtx = document.getElementById('agingChart');
    if (agingCtx) {
        new Chart(agingCtx, {
            type: 'doughnut',
            data: {
                labels: dashboardStats.agingAnalysis.labels,
                datasets: [{
                    data: dashboardStats.agingAnalysis.data,
                    backgroundColor: dashboardStats.agingAnalysis.colors,
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = formatCurrency(context.parsed);
                                return label + ': ' + value;
                            }
                        }
                    }
                }
            }
        });
    }
}

function populateTables() {
    // Top Customers Table
    const customersTable = document.getElementById('top-customers-table');
    if (customersTable) {
        customersTable.innerHTML = dashboardStats.topCustomers.map(customer => `
            <tr>
                <td>${customer.name}</td>
                <td class="fw-semibold text-success">${customer.revenue}</td>
                <td><span class="badge gray">${customer.orders} orders</span></td>
            </tr>
        `).join('');
    }

    // Top Products Table
    const productsTable = document.getElementById('top-products-table');
    if (productsTable) {
        productsTable.innerHTML = dashboardStats.topProducts.map(product => `
            <tr>
                <td>${product.name}</td>
                <td><span class="badge info">${product.qty} ${product.unit}</span></td>
                <td class="fw-semibold text-success">${product.revenue}</td>
            </tr>
        `).join('');
    }

    // Recent Delivery Notes Table
    const sjTable = document.getElementById('recent-sj-table');
    if (sjTable) {
        sjTable.innerHTML = dashboardStats.recentDeliveryNotes.map(sj => `
            <tr>
                <td class="fw-semibold">${sj.no}</td>
                <td>${sj.customer}</td>
                <td>${formatDate(sj.date)}</td>
                <td>${getStatusBadge(sj.status)}</td>
            </tr>
        `).join('');
    }
}

// Helper Functions
function formatCurrency(value) {
    return 'Rp ' + value.toLocaleString('id-ID');
}

function formatNumber(value) {
    return value.toLocaleString('id-ID');
}

function formatDate(dateString) {
    const date = new Date(dateString);
    const options = { day: 'numeric', month: 'short', year: 'numeric' };
    return date.toLocaleDateString('id-ID', options);
}

function getStatusBadge(status) {
    const badges = {
        'delivered': '<span class="badge success">Delivered</span>',
        'in_transit': '<span class="badge warning">In Transit</span>',
        'pending': '<span class="badge gray">Pending</span>',
        'paid': '<span class="badge success">Paid</span>',
        'partial': '<span class="badge warning">Partial</span>',
        'unpaid': '<span class="badge danger">Unpaid</span>'
    };
    return badges[status] || '<span class="badge gray">' + status + '</span>';
}
