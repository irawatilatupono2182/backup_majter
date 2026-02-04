<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = '📊 Dashboard - Monitoring Bisnis';
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Dashboard';
    protected static ?int $navigationSort = -2;
    protected static string $view = 'filament.pages.dashboard';

    public function getWidgets(): array
    {
        return [
            // SECTION 1: KPI Utama (Top Priority)
            \App\Filament\Widgets\StatsOverviewWidget::class,
            
            // SECTION 2: Keuangan (Finance Metrics)
            \App\Filament\Widgets\FinanceStatsWidget::class,
            \App\Filament\Widgets\AgingAnalysisChart::class,
            \App\Filament\Widgets\CashFlowChart::class,
            
            // SECTION 3: Penjualan (Sales Metrics)
            \App\Filament\Widgets\SalesRevenueChart::class,
            \App\Filament\Widgets\InvoiceStatusChart::class,
            \App\Filament\Widgets\TopCustomersWidget::class,
            \App\Filament\Widgets\TopSellingProductsWidget::class,
            
            // SECTION 4: Inventory & Operasional
            \App\Filament\Widgets\WarehouseStatsWidget::class,
            \App\Filament\Widgets\InventoryAlertsWidget::class,
            \App\Filament\Widgets\RecentDeliveryNotesWidget::class,
            
            // SECTION 5: Pembelian (Purchasing)
            \App\Filament\Widgets\PurchasingActivityWidget::class,
        ];
    }

    public function getColumns(): int
    {
        return 2;
    }
}
