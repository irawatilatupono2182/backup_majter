<?php

namespace App\Filament\Resources\NotaMenyusulResource\Pages;

use App\Filament\Resources\NotaMenyusulResource;
use App\Models\NotaMenyusul;
use Filament\Resources\Pages\CreateRecord;

class CreateNotaMenyusul extends CreateRecord
{
    protected static string $resource = NotaMenyusulResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        $data['nota_number'] = NotaMenyusul::generateNotaNumber();
        
        // Set type based on session
        $type = session('nota_menyusul_type_create');
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
        session()->forget('nota_menyusul_type_create');
    }
    
    protected function getRedirectUrl(): string
    {
        session()->forget('nota_menyusul_type_create');
        return $this->getResource()::getUrl('index');
    }
}
