<?php

namespace App\Filament\Resources\InvoiceNonPpnResource\Pages;

use App\Filament\Resources\InvoiceNonPpnResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInvoiceNonPpn extends EditRecord
{
    protected static string $resource = InvoiceNonPpnResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
