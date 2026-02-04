<?php

namespace App\Filament\Widgets;

use App\Models\Stock;
use App\Models\StockMovement;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class WarehouseStatsWidget extends BaseWidget
{
    protected static ?int $sort = 6;
    protected static ?string $pollingInterval = '30s';
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $companyId = session('selected_company_id');
        
        if (!$companyId) {
            return [];
        }

        return [
            // 📊 NILAI INVENTORY
            Stat::make('📊 Total Nilai Stock', 'Rp ' . number_format($this->getTotalInventoryValue($companyId), 0, ',', '.'))
                ->description('Nilai inventory di gudang (HPP)')
                ->descriptionIcon('heroicon-m-cube')
                ->color('info')
                ->extraAttributes(['class' => 'cursor-pointer'])
                ->url(route('filament.admin.resources.stocks.index')),

            // 🔄 PERGERAKAN STOCK
            Stat::make('🔄 Aktivitas Hari Ini', $this->getTodayMovementCount($companyId) . ' Transaksi')
                ->description('🔻 ' . $this->getTodayOutCount($companyId) . ' Keluar | 🔺 ' . $this->getTodayInCount($companyId) . ' Masuk')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('primary')
                ->chart($this->getLast7DaysMovement($companyId))
                ->extraAttributes(['class' => 'cursor-pointer'])
                ->url(route('filament.admin.resources.stock-movements.index')),

            // ⏰ EXPIRY WARNING
            Stat::make('⏰ Produk Hampir Kadaluarsa', $this->getNearExpiryCount($companyId) . ' Item')
                ->description('30 hari ke depan → Segera jual!')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color('warning')
                ->extraAttributes(['class' => 'cursor-pointer'])
                ->url(route('filament.admin.resources.stocks.index')),
        ];
    }
    
    public function getHeading(): ?string
    {
        return '📦 GUDANG - Inventory & Stock Movement';
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
