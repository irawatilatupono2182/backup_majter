<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListInvoices extends ListRecords
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('create')
                ->label('Buat Invoice')
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->form([
                    \Filament\Forms\Components\Radio::make('invoice_type')
                        ->label('Pilih Jenis Invoice')
                        ->options([
                            'PPN' => 'Invoice PPN',
                            'Non-PPN' => 'Invoice Non-PPN',
                        ])
                        ->required()
                        ->default('PPN')
                        ->inline()
                        ->descriptions([
                            'PPN' => 'Invoice dengan PPN 11%',
                            'Non-PPN' => 'Invoice tanpa pajak',
                        ]),
                ])
                ->action(function (array $data) {
                    session(['invoice_type_create' => $data['invoice_type']]);
                    return redirect()->to(static::$resource::getUrl('create'));
                }),
        ];
    }

    public function getTabs(): array
    {
        return [
            'semua' => Tab::make('Semua Invoice')
                ->badge(fn () => \App\Models\Invoice::query()
                    ->where('company_id', session('selected_company_id'))
                    ->count()),
            
            'ppn' => Tab::make('Invoice PPN')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'PPN'))
                ->badge(fn () => \App\Models\Invoice::query()
                    ->where('company_id', session('selected_company_id'))
                    ->where('type', 'PPN')
                    ->count())
                ->badgeColor('success'),
            
            'non_ppn' => Tab::make('Invoice Non-PPN')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'Non-PPN'))
                ->badge(fn () => \App\Models\Invoice::query()
                    ->where('company_id', session('selected_company_id'))
                    ->where('type', 'Non-PPN')
                    ->count())
                ->badgeColor('gray'),
        ];
    }
}