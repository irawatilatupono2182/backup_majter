<?php

namespace App\Filament\Resources\PurchaseOrderResource\Pages;

use App\Filament\Resources\PurchaseOrderResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListPurchaseOrders extends ListRecords
{
    protected static string $resource = PurchaseOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('create')
                ->label('Buat PO Baru')
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->form([
                    \Filament\Forms\Components\Radio::make('type')
                        ->label('Pilih Jenis Pembelian')
                        ->options([
                            'Local' => 'Pembelian Lokal',
                            'Import' => 'Pembelian Import',
                        ])
                        ->required()
                        ->default('Local')
                        ->inline()
                        ->descriptions([
                            'Local' => 'Pembelian barang dari supplier lokal',
                            'Import' => 'Pembelian barang dari supplier import',
                        ]),
                ])
                ->action(function (array $data) {
                    // Simpan pilihan ke session untuk digunakan di form create
                    session(['po_type_create' => $data['type']]);
                    
                    // Redirect ke halaman create
                    return redirect()->to(static::$resource::getUrl('create'));
                }),
        ];
    }

    public function getTabs(): array
    {
        return [
            'semua' => Tab::make('Semua PO')
                ->badge(fn () => \App\Models\PurchaseOrder::query()
                    ->where('company_id', session('selected_company_id'))
                    ->count()),
            
            'lokal' => Tab::make('PO Lokal')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'Local'))
                ->badge(fn () => \App\Models\PurchaseOrder::query()
                    ->where('company_id', session('selected_company_id'))
                    ->where('type', 'Local')
                    ->count())
                ->badgeColor('success'),
            
            'import' => Tab::make('PO Import')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'Import'))
                ->badge(fn () => \App\Models\PurchaseOrder::query()
                    ->where('company_id', session('selected_company_id'))
                    ->where('type', 'Import')
                    ->count())
                ->badgeColor('info'),
        ];
    }
}