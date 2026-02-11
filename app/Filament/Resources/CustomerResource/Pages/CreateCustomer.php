<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Resources\CustomerResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomer extends CreateRecord
{
    protected static string $resource = CustomerResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set is_ppn based on session
        $type = session('customer_type_create');
        if ($type) {
            $data['is_ppn'] = ($type === 'PPN');
        }
        
        return $data;
    }

    protected function afterCreate(): void
    {
        // Clear session after create
        session()->forget('customer_type_create');
    }

    protected function getRedirectUrl(): string
    {
        // Clear session and redirect
        session()->forget('customer_type_create');
        return $this->getResource()::getUrl('index');
    }
}
