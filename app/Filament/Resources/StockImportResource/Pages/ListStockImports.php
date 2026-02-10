<?php

namespace App\Filament\Resources\StockImportResource\Pages;

use App\Filament\Resources\StockImportResource;
use App\Filament\Resources\StockResource\Pages\ListStocks;

class ListStockImports extends ListStocks
{
    protected static string $resource = StockImportResource::class;
}
