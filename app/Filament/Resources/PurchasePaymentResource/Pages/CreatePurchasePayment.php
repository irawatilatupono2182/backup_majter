<?php

namespace App\Filament\Resources\PurchasePaymentResource\Pages;

use App\Filament\Resources\PurchasePaymentResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePurchasePayment extends CreateRecord
{
    protected static string $resource = PurchasePaymentResource::class;
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        
        return $data;
    }
    
    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Pembayaran berhasil dicatat';
    }
}
