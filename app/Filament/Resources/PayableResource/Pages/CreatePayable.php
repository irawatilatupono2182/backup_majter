<?php

namespace App\Filament\Resources\PayableResource\Pages;

use App\Filament\Resources\PayableResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreatePayable extends CreateRecord
{
    protected static string $resource = PayableResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Hutang berhasil dibuat')
            ->body('Data hutang telah ditambahkan ke sistem.')
            ->seconds(5);
    }
    
    public function getTitle(): string
    {
        return 'Buat Hutang Baru';
    }
}
