<?php

namespace App\Filament\Resources\InvoicePpnResource\Pages;

use App\Filament\Resources\InvoicePpnResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInvoicePpn extends ListRecords
{
    protected static string $resource = InvoicePpnResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
