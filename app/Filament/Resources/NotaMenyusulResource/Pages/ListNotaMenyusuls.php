<?php

namespace App\Filament\Resources\NotaMenyusulResource\Pages;

use App\Filament\Resources\NotaMenyusulResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListNotaMenyusuls extends ListRecords
{
    protected static string $resource = NotaMenyusulResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('create')
                ->label('Buat Nota Menyusul')
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->form([
                    \Filament\Forms\Components\Radio::make('nota_type')
                        ->label('Pilih Jenis Nota Menyusul')
                        ->options([
                            'PPN' => 'Nota Menyusul PPN',
                            'Non-PPN' => 'Nota Menyusul Non-PPN',
                        ])
                        ->required()
                        ->default('PPN')
                        ->inline()
                        ->descriptions([
                            'PPN' => 'Dengan PPN 11%',
                            'Non-PPN' => 'Tanpa pajak',
                        ]),
                ])
                ->action(function (array $data) {
                    session(['nota_menyusul_type_create' => $data['nota_type']]);
                    return redirect()->to(static::$resource::getUrl('create'));
                }),
        ];
    }

    public function getTabs(): array
    {
        return [
            'semua' => Tab::make('Semua Nota')
                ->badge(fn () => \App\Models\NotaMenyusul::query()
                    ->where('company_id', session('selected_company_id'))
                    ->count()),
            
            'ppn' => Tab::make('Nota PPN')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'PPN'))
                ->badge(fn () => \App\Models\NotaMenyusul::query()
                    ->where('company_id', session('selected_company_id'))
                    ->where('type', 'PPN')
                    ->count())
                ->badgeColor('success'),
            
            'non_ppn' => Tab::make('Nota Non-PPN')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'Non-PPN'))
                ->badge(fn () => \App\Models\NotaMenyusul::query()
                    ->where('company_id', session('selected_company_id'))
                    ->where('type', 'Non-PPN')
                    ->count())
                ->badgeColor('gray'),
        ];
    }
}
