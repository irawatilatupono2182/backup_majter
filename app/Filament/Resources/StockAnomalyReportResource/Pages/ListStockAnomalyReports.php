<?php

namespace App\Filament\Resources\StockAnomalyReportResource\Pages;

use App\Filament\Resources\StockAnomalyReportResource;
use Filament\Resources\Pages\ListRecords;

class ListStockAnomalyReports extends ListRecords
{
    protected static string $resource = StockAnomalyReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
