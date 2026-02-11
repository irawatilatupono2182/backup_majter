<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Resources\CustomerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListCustomers extends ListRecords
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('create')
                ->label('Tambah Customer')
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->form([
                    \Filament\Forms\Components\Radio::make('customer_type')
                        ->label('Pilih Jenis Customer')
                        ->options([
                            'PPN' => 'Customer PPN',
                            'Non-PPN' => 'Customer Non-PPN',
                        ])
                        ->required()
                        ->default('Non-PPN')
                        ->inline()
                        ->descriptions([
                            'PPN' => 'Customer yang menggunakan faktur pajak (PPN 11%)',
                            'Non-PPN' => 'Customer tanpa pajak pertambahan nilai',
                        ]),
                ])
                ->action(function (array $data) {
                    // Simpan pilihan ke session
                    session(['customer_type_create' => $data['customer_type']]);
                    
                    // Redirect ke halaman create
                    return redirect()->to(static::$resource::getUrl('create'));
                }),
        ];
    }

    public function getTabs(): array
    {
        return [
            'semua' => Tab::make('Semua Customer')
                ->badge(fn () => \App\Models\Customer::query()
                    ->where('company_id', session('selected_company_id'))
                    ->count()),
            
            'ppn' => Tab::make('Customer PPN')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_ppn', true))
                ->badge(fn () => \App\Models\Customer::query()
                    ->where('company_id', session('selected_company_id'))
                    ->where('is_ppn', true)
                    ->count())
                ->badgeColor('success'),
            
            'non_ppn' => Tab::make('Customer Non-PPN')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_ppn', false))
                ->badge(fn () => \App\Models\Customer::query()
                    ->where('company_id', session('selected_company_id'))
                    ->where('is_ppn', false)
                    ->count())
                ->badgeColor('gray'),
        ];
    }
}
