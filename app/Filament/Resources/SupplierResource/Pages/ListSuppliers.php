<?php

namespace App\Filament\Resources\SupplierResource\Pages;

use App\Filament\Resources\SupplierResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListSuppliers extends ListRecords
{
    protected static string $resource = SupplierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('create')
                ->label('Tambah Supplier')
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->form([
                    \Filament\Forms\Components\Radio::make('type')
                        ->label('Pilih Jenis Supplier')
                        ->options([
                            'Local' => 'Supplier Lokal',
                            'Import' => 'Supplier Import',
                        ])
                        ->required()
                        ->default('Local')
                        ->inline()
                        ->descriptions([
                            'Local' => 'Supplier dari dalam negeri',
                            'Import' => 'Supplier dari luar negeri',
                        ]),
                ])
                ->action(function (array $data) {
                    // Simpan pilihan ke session untuk digunakan di form create
                    session(['supplier_type_create' => $data['type']]);
                    
                    // Redirect ke halaman create
                    return redirect()->to(static::$resource::getUrl('create'));
                }),
        ];
    }

    public function getTabs(): array
    {
        return [
            'semua' => Tab::make('Semua Supplier')
                ->badge(fn () => \App\Models\Supplier::query()
                    ->where('company_id', session('selected_company_id'))
                    ->count()),
            
            'lokal' => Tab::make('Supplier Lokal')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'Local'))
                ->badge(fn () => \App\Models\Supplier::query()
                    ->where('company_id', session('selected_company_id'))
                    ->where('type', 'Local')
                    ->count())
                ->badgeColor('success'),
            
            'import' => Tab::make('Supplier Import')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'Import'))
                ->badge(fn () => \App\Models\Supplier::query()
                    ->where('company_id', session('selected_company_id'))
                    ->where('type', 'Import')
                    ->count())
                ->badgeColor('info'),
        ];
    }
}