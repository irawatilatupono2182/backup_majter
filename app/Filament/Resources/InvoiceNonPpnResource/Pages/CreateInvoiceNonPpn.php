<?php

namespace App\Filament\Resources\InvoiceNonPpnResource\Pages;

use App\Filament\Resources\InvoiceNonPpnResource;
use Filament\Resources\Pages\CreateRecord;

class CreateInvoiceNonPpn extends CreateRecord
{
    protected static string $resource = InvoiceNonPpnResource::class;
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['type'] = 'Non-PPN';
        $data['company_id'] = session('selected_company_id');
        $data['created_by'] = auth()->id();
        return $data;
    }
}
