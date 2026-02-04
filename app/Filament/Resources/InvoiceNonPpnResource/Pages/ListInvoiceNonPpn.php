<?php

namespace App\Filament\Resources\InvoiceNonPpnResource\Pages;

use App\Filament\Resources\InvoiceNonPpnResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInvoiceNonPpn extends ListRecords
{
    protected static string $resource = InvoiceNonPpnResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
