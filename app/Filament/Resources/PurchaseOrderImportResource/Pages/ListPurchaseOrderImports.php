<?php

namespace App\Filament\Resources\PurchaseOrderImportResource\Pages;

use App\Filament\Resources\PurchaseOrderImportResource;
use App\Filament\Resources\PurchaseOrderResource\Pages\ListPurchaseOrders;

class ListPurchaseOrderImports extends ListPurchaseOrders
{
    protected static string $resource = PurchaseOrderImportResource::class;
}
