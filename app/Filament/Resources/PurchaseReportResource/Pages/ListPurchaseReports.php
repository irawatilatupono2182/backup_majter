<?php

namespace App\Filament\Resources\PurchaseReportResource\Pages;

use App\Filament\Resources\PurchaseReportResource;
use Filament\Resources\Pages\ListRecords;
use App\Models\PurchaseReport;
use App\Models\Payable;

class ListPurchaseReports extends ListRecords
{
    protected static string $resource = PurchaseReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('hutang_usaha')
                ->label('💳 Hutang Usaha')
                ->badge(function() {
                    $companyId = session('selected_company_id');
                    
                    // Count from Payable (unpaid/partial)
                    $payableCount = Payable::where('company_id', $companyId)
                        ->whereIn('status', ['unpaid', 'partial', 'overdue'])
                        ->count();
                    
                    return $payableCount > 0 ? $payableCount : null;
                })
                ->badgeColor('danger')
                ->url(fn() => static::getResource()::getUrl('hutang'))
                ->color('primary')
                ->outlined(),
        ];
    }
}
