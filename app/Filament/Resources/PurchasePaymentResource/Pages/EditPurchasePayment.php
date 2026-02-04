<?php

namespace App\Filament\Resources\PurchasePaymentResource\Pages;

use App\Filament\Resources\PurchasePaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPurchasePayment extends EditRecord
{
    protected static string $resource = PurchasePaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    protected function getSavedNotificationTitle(): ?string
    {
        return 'Pembayaran berhasil diupdate';
    }
}
