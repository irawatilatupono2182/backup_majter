<?php

namespace App\Filament\Resources\InvoicePpnResource\Pages;

use App\Filament\Resources\InvoicePpnResource;
use Filament\Resources\Pages\CreateRecord;

class CreateInvoicePpn extends CreateRecord
{
    protected static string $resource = InvoicePpnResource::class;
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['type'] = 'PPN';
        $data['company_id'] = session('selected_company_id');
        $data['created_by'] = auth()->id();
        return $data;
    }
}
