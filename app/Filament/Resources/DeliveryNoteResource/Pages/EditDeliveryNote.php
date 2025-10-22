<?php

namespace App\Filament\Resources\DeliveryNoteResource\Pages;

use App\Filament\Resources\DeliveryNoteResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditDeliveryNote extends EditRecord
{
    protected static string $resource = DeliveryNoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        // Check if status changed to Sent or Completed
        $record = $this->getRecord();
        
        if ($record->wasChanged('status') && in_array($record->status, ['Sent', 'Completed'])) {
            Notification::make()
                ->title('Surat Jalan Terkirim')
                ->body('Stock movement telah dibuat dan stock telah dikurangi secara otomatis.')
                ->success()
                ->send();
        }
        
        if ($record->wasChanged('status') && $record->status === 'Draft' && 
            in_array($record->getOriginal('status'), ['Sent', 'Completed'])) {
            Notification::make()
                ->title('Status Dikembalikan')
                ->body('Stock movement telah dibatalkan dan stock telah dikembalikan.')
                ->warning()
                ->send();
        }
    }

    protected function onValidationError(\Illuminate\Validation\ValidationException $exception): void
    {
        // Check if there's a stock-related error
        $errors = $exception->errors();
        $hasStockError = false;
        
        foreach ($errors as $field => $messages) {
            foreach ($messages as $message) {
                if (str_contains($message, 'Stock tidak mencukupi') || 
                    str_contains($message, 'stock')) {
                    $hasStockError = true;
                    break 2;
                }
            }
        }
        
        if ($hasStockError) {
            Notification::make()
                ->title('Error: Stock Tidak Mencukupi')
                ->body('Tidak dapat mengubah status karena stock tidak tersedia. Periksa stock produk terlebih dahulu.')
                ->danger()
                ->persistent()
                ->send();
        }
        
        parent::onValidationError($exception);
    }
}

