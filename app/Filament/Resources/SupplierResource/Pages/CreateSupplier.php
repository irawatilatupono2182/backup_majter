<?php

namespace App\Filament\Resources\SupplierResource\Pages;

use App\Filament\Resources\SupplierResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSupplier extends CreateRecord
{
    protected static string $resource = SupplierResource::class;
    
    public function getTitle(): string
    {
        $type = session('supplier_type_create');
        if ($type === 'Local') {
            return 'Tambah Supplier Lokal';
        } elseif ($type === 'Import') {
            return 'Tambah Supplier Import';
        }
        return 'Tambah Supplier';
    }
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Ensure type is set from session if available
        if (!isset($data['type']) && session('supplier_type_create')) {
            $data['type'] = session('supplier_type_create');
        }
        
        return $data;
    }
    
    protected function getRedirectUrl(): string
    {
        // Clear session after create
        session()->forget('supplier_type_create');
        
        return $this->getResource()::getUrl('index');
    }
    
    protected function afterCreate(): void
    {
        // Clear session
        session()->forget('supplier_type_create');
    }
}