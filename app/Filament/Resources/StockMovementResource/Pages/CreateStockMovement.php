<?php

namespace App\Filament\Resources\StockMovementResource\Pages;

use App\Filament\Resources\StockMovementResource;
use App\Models\Stock;
use Filament\Resources\Pages\CreateRecord;

class CreateStockMovement extends CreateRecord
{
    protected static string $resource = StockMovementResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        
        return $data;
    }

    protected function afterCreate(): void
    {
        $record = $this->record;
        
        // Find or create stock record
        $stock = Stock::firstOrCreate(
            [
                'company_id' => $record->company_id,
                'product_id' => $record->product_id,
                'batch_number' => $record->batch_number,
            ],
            [
                'quantity' => 0,
                'reserved_quantity' => 0,
                'available_quantity' => 0,
                'minimum_stock' => 0,
                'unit_cost' => $record->unit_cost,
                'expiry_date' => $record->expiry_date,
                'created_by' => auth()->id(),
            ]
        );

        // Update stock based on movement type
        if ($record->movement_type === 'in') {
            $stock->quantity += $record->quantity;
        } elseif ($record->movement_type === 'out') {
            $stock->quantity -= $record->quantity;
        } elseif ($record->movement_type === 'adjustment') {
            $stock->quantity = $record->quantity;
        }

        // Update unit cost if provided
        if ($record->unit_cost) {
            $stock->unit_cost = $record->unit_cost;
        }

        // Update available quantity
        $stock->available_quantity = $stock->quantity - $stock->reserved_quantity;
        $stock->save();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}