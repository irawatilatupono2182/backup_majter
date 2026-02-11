<?php

namespace App\Filament\Resources\SalesReportResource\Pages;

use App\Filament\Resources\SalesReportResource;
use App\Models\NotaMenyusul;
use App\Models\KeteranganLain;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListSalesReports extends ListRecords
{
    protected static string $resource = SalesReportResource::class;

    protected function getHeaderActions(): array
    {
        $companyId = session('selected_company_id');
        
        return [
            \Filament\Actions\Action::make('piutang_usaha')
                ->label('💰 Piutang Usaha')
                ->badge(function() {
                    $count = $this->getModel()::where('company_id', session('selected_company_id'))
                        ->whereIn('status', ['Unpaid', 'Partial', 'Overdue'])
                        ->count();
                    return $count > 0 ? $count : null;
                })
                ->badgeColor('warning')
                ->url(fn() => static::getResource()::getUrl('piutang'))
                ->color('primary')
                ->outlined(),
            
            \Filament\Actions\Action::make('nota_menyusul')
                ->label('📝 Nota Menyusul')
                ->badge(fn() => NotaMenyusul::where('company_id', $companyId)->count() ?: null)
                ->badgeColor('info')
                ->url(fn() => route('filament.admin.resources.nota-menyusuls.index'))
                ->color('info')
                ->outlined(),
            
            \Filament\Actions\Action::make('keterangan_lain')
                ->label('📋 Keterangan Lain')
                ->badge(fn() => KeteranganLain::where('company_id', $companyId)->count() ?: null)
                ->badgeColor('gray')
                ->url(fn() => route('filament.admin.resources.keterangan-lains.index'))
                ->color('gray')
                ->outlined(),
        ];
    }

    public function getTabs(): array
    {
        $companyId = session('selected_company_id');

        return [
            'semua_invoice' => Tab::make('📄 Semua Invoice')
                ->badge(fn() => $this->getModel()::where('company_id', $companyId)->count()),

            'invoice_ppn' => Tab::make('✅ Invoice PPN')
                ->modifyQueryUsing(fn(Builder $query) => 
                    $query->where('type', 'PPN')
                )
                ->badge(fn() => $this->getModel()::where('company_id', $companyId)
                    ->where('type', 'PPN')
                    ->count()
                ),

            'invoice_non_ppn' => Tab::make('❌ Invoice Non-PPN')
                ->modifyQueryUsing(fn(Builder $query) => 
                    $query->where('type', 'Non-PPN')
                )
                ->badge(fn() => $this->getModel()::where('company_id', $companyId)
                    ->where('type', 'Non-PPN')
                    ->count()
                ),

            'belum_lunas' => Tab::make('⏳ Belum Lunas')
                ->modifyQueryUsing(fn(Builder $query) => 
                    $query->whereIn('status', ['Unpaid', 'Partial'])
                )
                ->badge(function() use ($companyId) {
                    return $this->getModel()::where('company_id', $companyId)
                        ->whereIn('status', ['Unpaid', 'Partial'])
                        ->count();
                })
                ->badgeColor('warning'),

            'jatuh_tempo' => Tab::make('🚨 Jatuh Tempo')
                ->modifyQueryUsing(fn(Builder $query) => 
                    $query->where('status', 'Overdue')
                )
                ->badge(function() use ($companyId) {
                    return $this->getModel()::where('company_id', $companyId)
                        ->where('status', 'Overdue')
                        ->count();
                })
                ->badgeColor('danger'),

            'lunas' => Tab::make('✔️ Lunas')
                ->modifyQueryUsing(fn(Builder $query) => 
                    $query->where('status', 'Paid')
                )
                ->badge(function() use ($companyId) {
                    return $this->getModel()::where('company_id', $companyId)
                        ->where('status', 'Paid')
                        ->count();
                })
                ->badgeColor('success'),
        ];
    }
}