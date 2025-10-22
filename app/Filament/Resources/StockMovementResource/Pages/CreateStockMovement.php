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
        $data['company_id'] = session('selected_company_id');
        $data['created_by'] = auth()->id();
        
        return $data;
    }

    protected function afterCreate(): void
    {
        $record = $this->record;
        
        // Find stock record - prioritize matching batch, then null batch, then first available
        $stock = Stock::where('company_id', $record->company_id)
            ->where('product_id', $record->product_id)
            ->where(function ($query) use ($record) {
                if ($record->batch_number) {
                    $query->where('batch_number', $record->batch_number)
                          ->orWhereNull('batch_number');
                } else {
                    $query->whereNull('batch_number');
                }
            })
            ->first();

        // If no stock found, create new one
        if (!$stock) {
            $stock = Stock::create([
                'company_id' => $record->company_id,
                'product_id' => $record->product_id,
                'batch_number' => $record->batch_number,
                'quantity' => 0,
                'reserved_quantity' => 0,
                'available_quantity' => 0,
                'minimum_stock' => 0,
                'unit_cost' => $record->unit_cost,
                'expiry_date' => $record->expiry_date,
                'created_by' => auth()->id(),
            ]);
        }

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

        // Update expiry date if provided
        if ($record->expiry_date) {
            $stock->expiry_date = $record->expiry_date;
        }

        // Update batch number if provided
        if ($record->batch_number && !$stock->batch_number) {
            $stock->batch_number = $record->batch_number;
        }

        // Update available quantity (will be calculated by model's booted method)
        $stock->available_quantity = $stock->quantity - $stock->reserved_quantity;
        $stock->save();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}