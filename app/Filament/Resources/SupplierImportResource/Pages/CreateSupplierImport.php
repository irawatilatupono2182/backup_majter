<?php

namespace App\Filament\Resources\SupplierImportResource\Pages;

use App\Filament\Resources\SupplierImportResource;
use App\Filament\Resources\SupplierResource\Pages\CreateSupplier;

class CreateSupplierImport extends CreateSupplier
{
    protected static string $resource = SupplierImportResource::class;
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['type'] = 'Import';
        return $data;
    }
}
