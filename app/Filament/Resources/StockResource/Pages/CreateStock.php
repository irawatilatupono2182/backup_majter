<?php

namespace App\Filament\Resources\StockResource\Pages;

use App\Filament\Resources\StockResource;
use Filament\Resources\Pages\CreateRecord;

class CreateStock extends CreateRecord
{
    protected static string $resource = StockResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            // Display selected type info
        ];
    }
    
    public function getTitle(): string
    {
        $type = session('stock_type_create');
        if ($type === 'Local') {
            return 'Tambah Barang Lokal';
        } elseif ($type === 'Import') {
            return 'Tambah Barang Import';
        }
        return 'Tambah Barang';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['company_id'] = session('selected_company_id');
        $data['created_by'] = auth()->id();
        $data['reserved_quantity'] = 0;
        
        return $data;
    }
    
    protected function getRedirectUrl(): string
    {
        // Clear session after create
        session()->forget('stock_type_create');
        
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
    
    protected function afterCreate(): void
    {
        // Clear session
        session()->forget('stock_type_create');
    }
}