<?php

namespace App\Filament\Resources\PurchaseOrderImportResource\Pages;

use App\Filament\Resources\PurchaseOrderImportResource;
use App\Filament\Resources\PurchaseOrderResource\Pages\EditPurchaseOrder;

class EditPurchaseOrderImport extends EditPurchaseOrder
{
    protected static string $resource = PurchaseOrderImportResource::class;
}
