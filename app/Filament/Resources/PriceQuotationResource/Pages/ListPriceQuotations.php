<?php

namespace App\Filament\Resources\PriceQuotationResource\Pages;

use App\Filament\Resources\PriceQuotationResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListPriceQuotations extends ListRecords
{
    protected static string $resource = PriceQuotationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('create')
                ->label('Buat Surat Penawaran')
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->form([
                    \Filament\Forms\Components\Radio::make('quotation_type')
                        ->label('Pilih Jenis Penawaran')
                        ->options([
                            'PPN' => 'Penawaran PPN',
                            'Non-PPN' => 'Penawaran Non-PPN',
                        ])
                        ->required()
                        ->default('PPN')
                        ->inline()
                        ->descriptions([
                            'PPN' => 'Penawaran dengan PPN 11%',
                            'Non-PPN' => 'Penawaran tanpa pajak',
                        ]),
                ])
                ->action(function (array $data) {
                    session(['quotation_type_create' => $data['quotation_type']]);
                    return redirect()->to(static::$resource::getUrl('create'));
                }),
        ];
    }

    public function getTabs(): array
    {
        return [
            'semua' => Tab::make('Semua Penawaran')
                ->badge(fn () => \App\Models\PriceQuotation::query()
                    ->where('company_id', session('selected_company_id'))
                    ->count()),
            
            'ppn' => Tab::make('PPN')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'PPN'))
                ->badge(fn () => \App\Models\PriceQuotation::query()
                    ->where('company_id', session('selected_company_id'))
                    ->where('type', 'PPN')
                    ->count())
                ->badgeColor('success'),
            
            'non_ppn' => Tab::make('Non-PPN')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'Non-PPN'))
                ->badge(fn () => \App\Models\PriceQuotation::query()
                    ->where('company_id', session('selected_company_id'))
                    ->where('type', 'Non-PPN')
                    ->count())
                ->badgeColor('gray'),
        ];
    }
}