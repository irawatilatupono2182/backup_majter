<?php

namespace App\Filament\Resources\PurchaseReportResource\Pages;

use App\Filament\Resources\PurchaseReportResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use App\Models\PurchaseReport;
use App\Models\Payable;
use Illuminate\Database\Eloquent\Builder;

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

    public function getTabs(): array
    {
        $companyId = session('selected_company_id');

        return [
            'semua' => Tab::make('📋 Semua PO')
                ->badge(fn() => $this->getModel()::where('company_id', $companyId)->count()),

            'lokal' => Tab::make('🏭 Supplier Lokal')
                ->modifyQueryUsing(fn(Builder $query) => 
                    $query->whereHas('supplier', function($q) {
                        $q->where('type', 'Local');
                    })
                )
                ->badge(fn() => $this->getModel()::where('company_id', $companyId)
                    ->whereHas('supplier', function($q) {
                        $q->where('type', 'Local');
                    })
                    ->count()
                ),

            'import' => Tab::make('🌍 Supplier Import')
                ->modifyQueryUsing(fn(Builder $query) => 
                    $query->whereHas('supplier', function($q) {
                        $q->where('type', 'Import');
                    })
                )
                ->badge(fn() => $this->getModel()::where('company_id', $companyId)
                    ->whereHas('supplier', function($q) {
                        $q->where('type', 'Import');
                    })
                    ->count()
                ),

            'ppn' => Tab::make('✅ PPN')
                ->modifyQueryUsing(fn(Builder $query) => 
                    $query->where('type', 'PPN')
                )
                ->badge(function() use ($companyId) {
                    return $this->getModel()::where('company_id', $companyId)
                        ->where('type', 'PPN')
                        ->count();
                }),

            'non_ppn' => Tab::make('❌ Non-PPN')
                ->modifyQueryUsing(fn(Builder $query) => 
                    $query->where('type', 'Non-PPN')
                )
                ->badge(function() use ($companyId) {
                    return $this->getModel()::where('company_id', $companyId)
                        ->where('type', 'Non-PPN')
                        ->count();
                }),

            'belum_lunas' => Tab::make('⏳ Belum Lunas')
                ->modifyQueryUsing(function(Builder $query) {
                    return $query->whereHas('payables', function($q) {
                        $q->whereIn('status', ['unpaid', 'partial']);
                    });
                })
                ->badge(function() use ($companyId) {
                    return Payable::where('company_id', $companyId)
                        ->whereIn('status', ['unpaid', 'partial'])
                        ->count();
                })
                ->badgeColor('warning'),

            'jatuh_tempo' => Tab::make('🚨 Jatuh Tempo')
                ->modifyQueryUsing(function(Builder $query) {
                    return $query->whereHas('payables', function($q) {
                        $q->where('due_date', '<', now())
                          ->whereIn('status', ['unpaid', 'partial', 'overdue']);
                    });
                })
                ->badge(function() use ($companyId) {
                    return Payable::where('company_id', $companyId)
                        ->where('due_date', '<', now())
                        ->whereIn('status', ['unpaid', 'partial', 'overdue'])
                        ->count();
                })
                ->badgeColor('danger'),

            'lunas' => Tab::make('✔️ Lunas')
                ->modifyQueryUsing(function(Builder $query) {
                    return $query->whereHas('payables', function($q) {
                        $q->where('status', 'paid');
                    });
                })
                ->badge(function() use ($companyId) {
                    return Payable::where('company_id', $companyId)
                        ->where('status', 'paid')
                        ->count();
                })
                ->badgeColor('success'),
        ];
    }
}
