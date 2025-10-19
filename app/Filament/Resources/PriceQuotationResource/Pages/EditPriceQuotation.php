<?php

namespace App\Filament\Resources\PriceQuotationResource\Pages;

use App\Filament\Resources\PriceQuotationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPriceQuotation extends EditRecord
{
    protected static string $resource = PriceQuotationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
