<?php

namespace App\Filament\Resources\InventoryReportResource\Widgets;

use App\Models\Stock;
use App\Models\StockMovement;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class InventorySummaryWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $companyId = session('selected_company_id');

        // Total Stok Tersedia
        $totalStock = Stock::where('company_id', $companyId)
            ->sum('available_quantity');

        // Total Barang Masuk (bulan ini)
        $stockIn = StockMovement::where('company_id', $companyId)
            ->where('movement_type', 'in')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('quantity');

        // Total Barang Keluar (bulan ini)
        $stockOut = StockMovement::where('company_id', $companyId)
            ->where('movement_type', 'out')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('quantity');

        // Total Nilai Inventory
        $totalValue = Stock::where('company_id', $companyId)
            ->get()
            ->sum(function ($stock) {
                return $stock->quantity * ($stock->unit_cost ?? 0);
            });

        // Stok Rendah
        $lowStock = Stock::where('company_id', $companyId)
            ->whereColumn('available_quantity', '<', 'minimum_stock')
            ->count();

        // Item Expired/Near Expiry
        $expiring = Stock::where('company_id', $companyId)
            ->where(function ($q) {
                $q->where('expiry_date', '<', now())
                  ->orWhereBetween('expiry_date', [now(), now()->addDays(30)]);
            })
            ->count();

        return [
            Stat::make('Total Stok Tersedia', number_format($totalStock, 0))
                ->description('Total stok yang tersedia saat ini')
                ->descriptionIcon('heroicon-m-archive-box')
                ->color('success'),

            Stat::make('Barang Masuk (Bulan Ini)', number_format($stockIn, 0))
                ->description('Total barang masuk bulan ' . now()->format('F Y'))
                ->descriptionIcon('heroicon-m-arrow-down-tray')
                ->color('info'),

            Stat::make('Barang Keluar (Bulan Ini)', number_format($stockOut, 0))
                ->description('Total barang keluar bulan ' . now()->format('F Y'))
                ->descriptionIcon('heroicon-m-arrow-up-tray')
                ->color('warning'),

            Stat::make('Total Nilai Inventory', 'Rp ' . number_format($totalValue, 0))
                ->description('Total nilai seluruh inventory')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),

            Stat::make('Stok Rendah', $lowStock . ' item')
                ->description('Item dengan stok di bawah minimum')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($lowStock > 0 ? 'danger' : 'success'),

            Stat::make('Perlu Perhatian', $expiring . ' item')
                ->description('Item expired atau akan kadaluarsa')
                ->descriptionIcon('heroicon-m-clock')
                ->color($expiring > 0 ? 'danger' : 'success'),
        ];
    }
}
