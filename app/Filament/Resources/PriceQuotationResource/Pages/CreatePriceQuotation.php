<?php

namespace App\Filament\Resources\PriceQuotationResource\Pages;

use App\Filament\Resources\PriceQuotationResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePriceQuotation extends CreateRecord
{
    protected static string $resource = PriceQuotationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set type based on session
        $type = session('quotation_type_create');
        if ($type) {
            $data['type'] = $type;
        }

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

    protected function afterCreate(): void
    {
        session()->forget('quotation_type_create');
    }

    protected function getRedirectUrl(): string
    {
        session()->forget('quotation_type_create');
        return $this->getResource()::getUrl('index');
    }
}