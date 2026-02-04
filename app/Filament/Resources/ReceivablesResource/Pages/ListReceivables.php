<?php

namespace App\Filament\Resources\ReceivablesResource\Pages;

use App\Filament\Resources\ReceivablesResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListReceivables extends ListRecords
{
    protected static string $resource = ReceivablesResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
