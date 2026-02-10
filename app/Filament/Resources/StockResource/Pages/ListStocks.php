<?php

namespace App\Filament\Resources\StockResource\Pages;

use App\Filament\Resources\StockResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListStocks extends ListRecords
{
    protected static string $resource = StockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('create')
                ->label('Tambah Barang')
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->form([
                    \Filament\Forms\Components\Radio::make('product_type')
                        ->label('Pilih Jenis Barang')
                        ->options([
                            'Local' => 'Barang Lokal',
                            'Import' => 'Barang Import',
                        ])
                        ->required()
                        ->default('Local')
                        ->inline()
                        ->descriptions([
                            'Local' => 'Barang yang diproduksi atau dibeli dari dalam negeri',
                            'Import' => 'Barang yang diimpor dari luar negeri',
                        ]),
                ])
                ->action(function (array $data) {
                    // Simpan pilihan ke session untuk digunakan di form create
                    session(['stock_type_create' => $data['product_type']]);
                    
                    // Redirect ke halaman create
                    return redirect()->to(static::$resource::getUrl('create'));
                }),
        ];
    }

    public function getTabs(): array
    {
        return [
            'semua' => Tab::make('Semua Barang')
                ->badge(fn () => \App\Models\Stock::query()
                    ->where('company_id', session('selected_company_id'))
                    ->count()),
            
            'lokal' => Tab::make('Barang Lokal')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('product', function ($q) {
                    $q->where('product_type', 'Local');
                }))
                ->badge(fn () => \App\Models\Stock::query()
                    ->where('company_id', session('selected_company_id'))
                    ->whereHas('product', function ($q) {
                        $q->where('product_type', 'Local');
                    })
                    ->count())
                ->badgeColor('success'),
            
            'import' => Tab::make('Barang Import')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('product', function ($q) {
                    $q->where('product_type', 'Import');
                }))
                ->badge(fn () => \App\Models\Stock::query()
                    ->where('company_id', session('selected_company_id'))
                    ->whereHas('product', function ($q) {
                        $q->where('product_type', 'Import');
                    })
                    ->count())
                ->badgeColor('info'),
        ];
    }
}