<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\Stock;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    // Fix error 500: Redirect ke list page setelah edit
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $product = $this->record;

        // Check if product_type changed to STOCK
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
                    ->body('Produk diubah ke tipe STOCK. Stock record telah dibuat dengan quantity 0.')
                    ->send();
            }
        }
    }
}
