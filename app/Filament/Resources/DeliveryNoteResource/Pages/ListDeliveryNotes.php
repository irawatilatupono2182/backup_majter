<?php

namespace App\Filament\Resources\DeliveryNoteResource\Pages;

use App\Filament\Resources\DeliveryNoteResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListDeliveryNotes extends ListRecords
{
    protected static string $resource = DeliveryNoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('create')
                ->label('Buat Surat Jalan')
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->form([
                    \Filament\Forms\Components\Radio::make('sj_type')
                        ->label('Pilih Jenis Surat Jalan')
                        ->options([
                            'PPN' => 'Surat Jalan PPN',
                            'Non-PPN' => 'Surat Jalan Non-PPN',
                        ])
                        ->required()
                        ->default('PPN')
                        ->inline()
                        ->descriptions([
                            'PPN' => 'Untuk customer PPN (akan generate invoice PPN)',
                            'Non-PPN' => 'Untuk customer Non-PPN (tanpa pajak)',
                        ]),
                ])
                ->action(function (array $data) {
                    session(['sj_type_create' => $data['sj_type']]);
                    return redirect()->to(static::$resource::getUrl('create'));
                }),
        ];
    }

    public function getTabs(): array
    {
        return [
            'semua' => Tab::make('Semua SJ')
                ->badge(fn () => \App\Models\DeliveryNote::query()
                    ->where('company_id', session('selected_company_id'))
                    ->count()),
            
            'ppn' => Tab::make('SJ PPN')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'PPN'))
                ->badge(fn () => \App\Models\DeliveryNote::query()
                    ->where('company_id', session('selected_company_id'))
                    ->where('type', 'PPN')
                    ->count())
                ->badgeColor('success'),
            
            'non_ppn' => Tab::make('SJ Non-PPN')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'Non-PPN'))
                ->badge(fn () => \App\Models\DeliveryNote::query()
                    ->where('company_id', session('selected_company_id'))
                    ->where('type', 'Non-PPN')
                    ->count())
                ->badgeColor('gray'),
        ];
    }
}
