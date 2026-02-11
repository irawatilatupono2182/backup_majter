<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use App\Models\Invoice;
use Filament\Resources\Pages\CreateRecord;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        $data['invoice_number'] = Invoice::generateInvoiceNumber();
        
        // Set type based on session
        $type = session('invoice_type_create');
        if ($type) {
            $data['type'] = $type;
        }
        
        return $data;
    }

    protected function afterCreate(): void
    {
        session()->forget('invoice_type_create');
    }
    
    protected function getRedirectUrl(): string
    {
        session()->forget('invoice_type_create');
        return $this->getResource()::getUrl('index');
    }
}