<?php

namespace App\Filament\Resources\InvoicePpnResource\Pages;

use App\Filament\Resources\InvoicePpnResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInvoicePpn extends EditRecord
{
    protected static string $resource = InvoicePpnResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
