<?php

namespace App\Filament\Resources\PayablesResource\Pages;

use App\Filament\Resources\PayablesResource;
use Filament\Resources\Pages\ListRecords;

class ListPayables extends ListRecords
{
    protected static string $resource = PayablesResource::class;
    
    protected static ?string $title = 'Hutang';

    protected function getHeaderActions(): array
    {
        return [];
    }
}
