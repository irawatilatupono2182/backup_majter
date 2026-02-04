<?php

namespace App\Filament\Widgets;

use App\Models\PurchaseOrder;
use App\Models\Payable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PayableStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $companyId = session('selected_company_id');
        
        // Total hutang yang belum dibayar
        $totalUnpaid = Payable::where('company_id', $companyId)
            ->whereIn('status', ['unpaid', 'partial', 'overdue'])
            ->sum('remaining_amount');
        
        // Hutang yang overdue
        $overdueCount = Payable::where('company_id', $companyId)
            ->where('status', 'overdue')
            ->count();
        
        $overdueAmount = Payable::where('company_id', $companyId)
            ->where('status', 'overdue')
            ->sum('remaining_amount');
        
        // PO yang belum punya payable record
        $poWithoutPayable = PurchaseOrder::where('company_id', $companyId)
            ->whereDoesntHave('payables')
            ->whereNotNull('due_date')
            ->count();
        
        return [
            Stat::make('Total Hutang Belum Lunas', 'Rp ' . number_format($totalUnpaid, 0, ',', '.'))
                ->description('Sisa hutang yang harus dibayar')
                ->descriptionIcon('heroicon-m-credit-card')
                ->color('warning'),
            
            Stat::make('Hutang Terlambat', 'Rp ' . number_format($overdueAmount, 0, ',', '.'))
                ->description($overdueCount . ' hutang lewat jatuh tempo')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),
            
            Stat::make('PO Tanpa Record Hutang', $poWithoutPayable)
                ->description('PO yang belum dibuatkan hutang')
                ->descriptionIcon('heroicon-m-document-minus')
                ->color('info')
                ->url(fn() => $poWithoutPayable > 0 ? '#' : null),
        ];
    }
}
