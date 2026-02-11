<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\Stock;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    // Fix error 500: Redirect ke list page setelah create
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {
        $product = $this->record;

        // Auto create stock record for STOCK type products
        if ($product->product_type === 'STOCK') {
            $existingStock = Stock::where('product_id', $product->product_id)
                ->where('company_id', $product->company_id)
                ->first();

            if (!$existingStock) {
                Stock::create([
                    'company_id' => $product->company_id,
                    'product_id' => $product->product_id,
                    'quantity' => 0,
                    'available_quantity' => 0,
                    'reserved_quantity' => 0,
                ]);

                Notification::make()
                    ->success()
                    ->title('Stock Record Created')
                    ->body('Stock record untuk produk ini telah dibuat dengan quantity 0.')
                    ->send();
            }
        }
    }
}