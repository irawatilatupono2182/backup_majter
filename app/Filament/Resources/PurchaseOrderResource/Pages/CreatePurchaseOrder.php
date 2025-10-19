<?php

namespace App\Filament\Resources\PurchaseOrderResource\Pages;

use App\Filament\Resources\PurchaseOrderResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePurchaseOrder extends CreateRecord
{
    protected static string $resource = PurchaseOrderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Auto-calculate subtotal for each item
        if (isset($data['items'])) {
            foreach ($data['items'] as &$item) {
                $baseAmount = $item['qty_ordered'] * $item['unit_price'];
                $discountAmount = $baseAmount * ($item['discount_percent'] / 100);
                $item['subtotal'] = $baseAmount - $discountAmount;
            }
        }

        return $data;
    }
}