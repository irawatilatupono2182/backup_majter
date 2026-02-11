<?php

namespace App\Filament\Resources\KeteranganLainResource\Pages;

use App\Filament\Resources\KeteranganLainResource;
use App\Models\KeteranganLain;
use Filament\Resources\Pages\CreateRecord;

class CreateKeteranganLain extends CreateRecord
{
    protected static string $resource = KeteranganLainResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        $data['document_number'] = KeteranganLain::generateDocumentNumber();
        
        // Set type based on session
        $type = session('keterangan_lain_type_create');
        if ($type) {
            $data['type'] = $type;
        }

        // Calculate totals
        $totalAmount = 0;
        if (isset($data['items'])) {
            foreach ($data['items'] as $item) {
                $totalAmount += $item['subtotal'];
            }
        }

        $data['total_amount'] = $totalAmount;
        $data['ppn_amount'] = ($data['type'] === 'PPN') ? $totalAmount * 0.11 : 0;
        $data['grand_total'] = $totalAmount + $data['ppn_amount'];
        
        return $data;
    }

    protected function afterCreate(): void
    {
        session()->forget('keterangan_lain_type_create');
    }
    
    protected function getRedirectUrl(): string
    {
        session()->forget('keterangan_lain_type_create');
        return $this->getResource()::getUrl('index');
    }
}
