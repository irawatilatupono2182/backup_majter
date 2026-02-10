<?php

namespace App\Filament\Resources\StockImportResource\Pages;

use App\Filament\Resources\StockImportResource;
use App\Filament\Resources\StockResource\Pages\CreateStock;

class CreateStockImport extends CreateStock
{
    protected static string $resource = StockImportResource::class;
}
