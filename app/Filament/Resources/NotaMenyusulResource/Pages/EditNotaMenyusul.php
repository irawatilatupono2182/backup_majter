<?php

namespace App\Filament\Resources\NotaMenyusulResource\Pages;

use App\Filament\Resources\NotaMenyusulResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditNotaMenyusul extends EditRecord
{
    protected static string $resource = NotaMenyusulResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
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

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
