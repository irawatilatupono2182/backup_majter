<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePayment extends CreateRecord
{
    protected static string $resource = PaymentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        
        // Ensure customer_id is set from invoice if exists
        if (!empty($data['invoice_id']) && empty($data['customer_id'])) {
            $invoice = \App\Models\Invoice::find($data['invoice_id']);
            if ($invoice) {
                $data['customer_id'] = $invoice->customer_id;
            }
        }
        
        return $data;
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}