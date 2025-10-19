<?php

namespace App\Filament\Resources\PriceQuotationResource\Pages;

use App\Filament\Resources\PriceQuotationResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePriceQuotation extends CreateRecord
{
    protected static string $resource = PriceQuotationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Auto-calculate subtotal for each item
        if (isset($data['items'])) {
            foreach ($data['items'] as &$item) {
                $baseAmount = $item['qty'] * $item['unit_price'];
                $discountAmount = $baseAmount * ($item['discount_percent'] / 100);
                $item['subtotal'] = $baseAmount - $discountAmount;
            }
        }

        return $data;
    }
}