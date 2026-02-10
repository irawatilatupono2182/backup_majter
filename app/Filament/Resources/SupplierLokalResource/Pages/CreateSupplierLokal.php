<?php

namespace App\Filament\Resources\SupplierLokalResource\Pages;

use App\Filament\Resources\SupplierLokalResource;
use App\Filament\Resources\SupplierResource\Pages\CreateSupplier;
use Filament\Resources\Pages\CreateRecord;

class CreateSupplierLokal extends CreateSupplier
{
    protected static string $resource = SupplierLokalResource::class;
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['type'] = 'Local';
        return $data;
    }
}
