<?php

namespace App\Filament\Resources\PurchaseOrderImportResource\Pages;

use App\Filament\Resources\PurchaseOrderImportResource;
use App\Filament\Resources\PurchaseOrderResource\Pages\CreatePurchaseOrder;

class CreatePurchaseOrderImport extends CreatePurchaseOrder
{
    protected static string $resource = PurchaseOrderImportResource::class;
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['type'] = 'Import';
        return parent::mutateFormDataBeforeCreate($data);
    }
}
