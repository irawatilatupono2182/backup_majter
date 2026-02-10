<?php

namespace App\Filament\Resources\SalesReportResource\Pages;

use App\Filament\Resources\SalesReportResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListSalesReports extends ListRecords
{
    protected static string $resource = SalesReportResource::class;

    protected function getHeaderActions(): array
    {
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
        ];
    }
}