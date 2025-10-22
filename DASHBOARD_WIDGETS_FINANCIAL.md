# Dashboard Widgets - Financial & Warehouse

## New Financial Widgets

### 1. FinanceStatsWidget
**Purpose**: Comprehensive financial KPIs for receivables management

**Metrics Displayed**:
- **Total Piutang**: Sum of all Unpaid + Partial invoices
- **Invoice Jatuh Tempo**: Count and total value of overdue invoices
- **Rata-rata Pembayaran**: Average days from invoice to payment
- **Pembayaran Bulan Ini**: Total payments received this month
- **Aging 30 Hari**: Receivables aged 0-30 days
- **Aging 60+ Hari**: Receivables aged 60+ days (critical)

**Features**:
- 7-day payment trend chart
- Color-coded urgency (green/warning/danger)
- Auto-refresh every 60 seconds
- Company-specific filtering

---

### 2. AgingAnalysisChart
**Purpose**: Visual breakdown of receivables by aging buckets

**Buckets**:
- Current (0-30 days) - Green
- Aging 31-60 days - Yellow
- Aging 61-90 days - Orange
- Overdue 90+ days - Red (critical)

**Chart Type**: Bar chart with color-coded categories

**Features**:
- Shows total value in each aging bucket
- Formatted in Rupiah (Rp)
- Helps identify collection priority
- Auto-refresh every 60 seconds

---

### 3. CashFlowChart
**Purpose**: Track cash inflow vs expected revenue

**Metrics**:
- **Pembayaran Diterima** (Green line): Actual payments received
- **Invoice Dibuat** (Blue line): Expected revenue from invoices

**Time Period**: Last 6 months

**Features**:
- Dual-line comparison chart
- Shows cash flow gap (difference between expected and actual)
- Values in millions (Jt) for readability
- Helps identify collection delays

---

### 4. WarehouseStatsWidget
**Purpose**: Key warehouse and inventory metrics

**Metrics**:
- **Nilai Inventory**: Total stock value (quantity × unit_cost)
- **Stock Movement Hari Ini**: Today's IN/OUT transactions with 7-day trend
- **Produk Akan Kadaluarsa**: Items expiring in next 30 days

**Features**:
- Mini chart showing last 7 days movement trend
- Breakdown of IN vs OUT movements
- Auto-refresh every 30 seconds

---

## Widget Registration

All widgets registered in `AdminPanelProvider.php`:

```php
->widgets([
    \App\Filament\Widgets\StatsOverviewWidget::class,      // General KPIs
    \App\Filament\Widgets\FinanceStatsWidget::class,       // NEW: Financial KPIs
    \App\Filament\Widgets\WarehouseStatsWidget::class,     // NEW: Warehouse KPIs
    \App\Filament\Widgets\AgingAnalysisChart::class,       // NEW: Aging chart
    \App\Filament\Widgets\CashFlowChart::class,            // NEW: Cash flow
    \App\Filament\Widgets\SalesRevenueChart::class,        // Sales trend
    \App\Filament\Widgets\InvoiceStatusChart::class,       // Invoice status
    \App\Filament\Widgets\InventoryAlertsWidget::class,    // Stock alerts
    \App\Filament\Widgets\RecentDeliveryNotesWidget::class,
    \App\Filament\Widgets\PurchasingActivityWidget::class,
    \App\Filament\Widgets\TopSellingProductsWidget::class,
    \App\Filament\Widgets\TopCustomersWidget::class,
])
```

---

## Dashboard Layout

**Total Widgets**: 12

**Categories**:
1. **Financial Analytics** (4 widgets):
   - FinanceStatsWidget
   - AgingAnalysisChart
   - CashFlowChart
   - InvoiceStatusChart

2. **Sales & Revenue** (4 widgets):
   - StatsOverviewWidget
   - SalesRevenueChart
   - TopSellingProductsWidget
   - TopCustomersWidget

3. **Warehouse & Inventory** (2 widgets):
   - WarehouseStatsWidget
   - InventoryAlertsWidget

4. **Operational** (2 widgets):
   - RecentDeliveryNotesWidget
   - PurchasingActivityWidget

---

## Key Features

### Financial Visibility
- **Total Piutang Tracking**: Real-time outstanding receivables
- **Aging Analysis**: 4-bucket breakdown (0-30, 31-60, 61-90, 90+ days)
- **Payment Trends**: Historical payment patterns
- **Cash Flow Gap**: Expected vs actual revenue comparison

### Warehouse Management
- **Inventory Valuation**: Total stock value
- **Movement Tracking**: Daily IN/OUT transactions
- **Expiry Monitoring**: Near-expiry products (30-day window)

### Multi-Company Support
- All widgets filter by `session('selected_company_id')`
- Data isolation between companies
- Role-based access control

---

## Usage

### Accessing Dashboard
1. Navigate to `/admin` after login
2. Dashboard loads automatically
3. All widgets refresh periodically (30-60s intervals)

### Key Metrics to Monitor

**Daily**:
- Total Piutang (should decrease over time)
- Invoice Jatuh Tempo (minimize this number)
- Stock Movement Hari Ini (track activity)

**Weekly**:
- Aging Analysis Chart (focus on 60+ bucket)
- Cash Flow Trend (ensure payments keep pace with invoices)
- Warehouse Stats (inventory turnover)

**Monthly**:
- Compare Pembayaran vs Invoice Dibuat
- Review Top Customers
- Analyze Top Selling Products

---

## Troubleshooting

### Widget Not Showing
1. Run: `php artisan optimize:clear`
2. Check widget is registered in AdminPanelProvider
3. Verify company_id is set in session

### No Data Displayed
- Check if company has data for that metric
- Verify date ranges (some use last 6 months)
- Ensure `selected_company_id` session is set

### Performance Issues
- Widgets use auto-refresh (30-60s)
- Database queries are optimized with indexes
- Consider caching for large datasets

---

## Future Enhancements

Potential additions:
- **Payment Timeline Widget**: Recent payments with invoice references
- **Outstanding Invoices Table**: All unpaid sorted by due date
- **Supplier Payment Tracking**: AP aging analysis
- **Profit Margin Analysis**: Revenue vs cost trends
- **Inventory Turnover Rate**: Stock velocity metrics

---

## Technical Details

### Database Queries
- Uses Eloquent ORM with query optimization
- `flowframe/laravel-trend` for time-series data
- Aggregations: SUM, COUNT, AVG, DATEDIFF
- Grouped by date/aging buckets

### Chart Libraries
- Filament ChartWidget (Chart.js wrapper)
- Supports: Line, Bar, Doughnut, Pie
- Responsive and mobile-friendly

### Auto-Refresh
- Uses Livewire polling
- `$pollingInterval` property (30s or 60s)
- Can be disabled per widget if needed
