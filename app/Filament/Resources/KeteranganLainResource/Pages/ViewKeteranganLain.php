<?php

namespace App\Filament\Resources\KeteranganLainResource\Pages;

use App\Filament\Resources\KeteranganLainResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewKeteranganLain extends ViewRecord
{
    protected static string $resource = KeteranganLainResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
