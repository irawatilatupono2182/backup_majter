<?php

namespace App\Filament\Resources\PurchaseOrderLokalResource\Pages;

use App\Filament\Resources\PurchaseOrderLokalResource;
use App\Filament\Resources\PurchaseOrderResource\Pages\CreatePurchaseOrder;

class CreatePurchaseOrderLokal extends CreatePurchaseOrder
{
    protected static string $resource = PurchaseOrderLokalResource::class;
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['type'] = 'Local';
        return parent::mutateFormDataBeforeCreate($data);
    }
}
