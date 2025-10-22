<?php

namespace App\Filament\Widgets;

use App\Models\Stock;
use App\Models\StockMovement;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class WarehouseStatsWidget extends BaseWidget
{
    protected static ?int $sort = 9;
    protected static ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $companyId = session('selected_company_id');

        return [
            // Total Nilai Inventory
            Stat::make('Nilai Inventory', 'Rp ' . number_format($this->getTotalInventoryValue($companyId), 0, ',', '.'))
                ->description('Total stock value (HPP)')
                ->descriptionIcon('heroicon-m-cube')
                ->color('info'),

            // Stock Movement Hari Ini
            Stat::make('Stock Movement Hari Ini', $this->getTodayMovementCount($companyId))
                ->description($this->getTodayOutCount($companyId) . ' OUT, ' . $this->getTodayInCount($companyId) . ' IN')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('primary')
                ->chart($this->getLast7DaysMovement($companyId)),

            // Produk Akan Kadaluarsa
            Stat::make('Produk Akan Kadaluarsa', $this->getNearExpiryCount($companyId))
                ->description('30 hari ke depan')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
        ];
    }

    private function getTotalInventoryValue($companyId): float
    {
        return Stock::where('company_id', $companyId)
            ->selectRaw('SUM(quantity * unit_cost) as total_value')
            ->value('total_value') ?? 0;
    }

    private function getTodayMovementCount($companyId): int
    {
        return StockMovement::where('company_id', $companyId)
            ->whereDate('created_at', today())
            ->count();
    }

    private function getTodayOutCount($companyId): int
    {
        return StockMovement::where('company_id', $companyId)
            ->where('movement_type', 'out')
            ->whereDate('created_at', today())
            ->count();
    }

    private function getTodayInCount($companyId): int
    {
        return StockMovement::where('company_id', $companyId)
            ->where('movement_type', 'in')
            ->whereDate('created_at', today())
            ->count();
    }

    private function getNearExpiryCount($companyId): int
    {
        return Stock::where('company_id', $companyId)
            ->whereBetween('expiry_date', [now(), now()->addDays(30)])
            ->count();
    }

    private function getLast7DaysMovement($companyId): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = today()->subDays($i);
            $count = StockMovement::where('company_id', $companyId)
                ->whereDate('created_at', $date)
                ->count();
            $data[] = $count;
        }
        return $data;
    }
}
