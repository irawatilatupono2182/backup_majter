<?php

namespace App\Filament\Resources\InvoiceDueNotificationResource\Pages;

use App\Filament\Resources\InvoiceDueNotificationResource;
use Filament\Resources\Pages\ListRecords;

class ListInvoiceDueNotifications extends ListRecords
{
    protected static string $resource = InvoiceDueNotificationResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
