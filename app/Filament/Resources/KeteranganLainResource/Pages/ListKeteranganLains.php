<?php

namespace App\Filament\Resources\KeteranganLainResource\Pages;

use App\Filament\Resources\KeteranganLainResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListKeteranganLains extends ListRecords
{
    protected static string $resource = KeteranganLainResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('create')
                ->label('Buat Dokumen Keterangan Lain')
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->form([
                    \Filament\Forms\Components\Radio::make('document_type')
                        ->label('Pilih Jenis Dokumen')
                        ->options([
                            'PPN' => 'Dokumen PPN',
                            'Non-PPN' => 'Dokumen Non-PPN',
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
                    session(['keterangan_lain_type_create' => $data['document_type']]);
                    return redirect()->to(static::$resource::getUrl('create'));
                }),
        ];
    }

    public function getTabs(): array
    {
        return [
            'semua' => Tab::make('Semua Dokumen')
                ->badge(fn () => \App\Models\KeteranganLain::query()
                    ->where('company_id', session('selected_company_id'))
                    ->count()),
            
            'ppn' => Tab::make('Dokumen PPN')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'PPN'))
                ->badge(fn () => \App\Models\KeteranganLain::query()
                    ->where('company_id', session('selected_company_id'))
                    ->where('type', 'PPN')
                    ->count())
                ->badgeColor('success'),
            
            'non_ppn' => Tab::make('Dokumen Non-PPN')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'Non-PPN'))
                ->badge(fn () => \App\Models\KeteranganLain::query()
                    ->where('company_id', session('selected_company_id'))
                    ->where('type', 'Non-PPN')
                    ->count())
                ->badgeColor('gray'),
        ];
    }
}
