<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Dashboard Monitoring';
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Dashboard';
    protected static ?int $navigationSort = -2;
    protected static string $view = 'filament.pages.dashboard';

    public function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\StatsOverviewWidget::class,
            \App\Filament\Widgets\FinanceStatsWidget::class,
            \App\Filament\Widgets\WarehouseStatsWidget::class,
            \App\Filament\Widgets\SalesRevenueChart::class,
            \App\Filament\Widgets\InvoiceStatusChart::class,
            \App\Filament\Widgets\AgingAnalysisChart::class,
            \App\Filament\Widgets\CashFlowChart::class,
            \App\Filament\Widgets\InventoryAlertsWidget::class,
            \App\Filament\Widgets\RecentDeliveryNotesWidget::class,
            \App\Filament\Widgets\PurchasingActivityWidget::class,
            \App\Filament\Widgets\TopSellingProductsWidget::class,
            \App\Filament\Widgets\TopCustomersWidget::class,
        ];
    }

    public function getColumns(): int
    {
        return 2;
    }
}
