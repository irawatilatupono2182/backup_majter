<?php

namespace App\Observers;

use App\Models\DeliveryNote;
use App\Models\StockMovement;
use App\Models\Stock;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeliveryNoteObserver
{
    /**
     * Handle the DeliveryNote "created" event.
     */
    public function created(DeliveryNote $deliveryNote): void
    {
        //
    }

    /**
     * Handle the DeliveryNote "updated" event.
     * Triggered when status changes to Sent or Completed
     */
    public function updated(DeliveryNote $deliveryNote): void
    {
        // Check if status changed
        if (!$deliveryNote->wasChanged('status')) {
            return;
        }

        $oldStatus = $deliveryNote->getOriginal('status');
        $newStatus = $deliveryNote->status;

        // Only process when status changes from Draft to Sent or Completed
        if ($oldStatus === 'Draft' && in_array($newStatus, ['Sent', 'Completed'])) {
            $this->processDeliveryAndCreateStockMovements($deliveryNote);
        }

        // Handle status reversal (Sent/Completed back to Draft)
        if (in_array($oldStatus, ['Sent', 'Completed']) && $newStatus === 'Draft') {
            $this->reverseStockMovements($deliveryNote);
        }
    }

    /**
     * Handle the DeliveryNote "deleted" event.
     */
    public function deleted(DeliveryNote $deliveryNote): void
    {
        // If delivery note is deleted and it has stock movements, reverse them
        if (in_array($deliveryNote->status, ['Sent', 'Completed'])) {
            $this->reverseStockMovements($deliveryNote);
        }
    }

    /**
     * Handle the DeliveryNote "restored" event.
     */
    public function restored(DeliveryNote $deliveryNote): void
    {
        //
    }

    /**
     * Handle the DeliveryNote "force deleted" event.
     */
    public function forceDeleted(DeliveryNote $deliveryNote): void
    {
        //
    }

    /**
     * Process delivery and create stock movements
     */
    protected function processDeliveryAndCreateStockMovements(DeliveryNote $deliveryNote): void
    {
        DB::beginTransaction();

        try {
            // Check if stock movements already exist for this delivery note
            $existingMovements = StockMovement::where('reference_type', 'delivery_note')
                ->where('reference_id', $deliveryNote->sj_id)
                ->exists();

            if ($existingMovements) {
                Log::warning("Stock movements already exist for Delivery Note {$deliveryNote->sj_number}");
                DB::rollBack();
                return;
            }

            // Load items with product relationship
            $deliveryNote->load('items.product');

            foreach ($deliveryNote->items as $item) {
                // Skip CATALOG products (they don't have physical stock)
                if ($item->product->product_type === 'CATALOG') {
                    continue;
                }

                // Check stock availability
                $availableStock = Stock::where('company_id', $deliveryNote->company_id)
                    ->where('product_id', $item->product_id)
                    ->sum('available_quantity');

                if ($availableStock < $item->qty) {
                    throw new \Exception(
                        "Stock tidak mencukupi untuk produk '{$item->product->name}'. " .
                        "Tersedia: {$availableStock}, Dibutuhkan: {$item->qty}"
                    );
                }

                // Create stock movement (OUT)
                StockMovement::create([
                    'company_id' => $deliveryNote->company_id,
                    'product_id' => $item->product_id,
                    'movement_type' => 'out',
                    'quantity' => $item->qty,
                    'unit_cost' => $item->unit_price,
                    'reference_type' => 'delivery_note',
                    'reference_id' => $deliveryNote->sj_id,
                    'notes' => "Pengiriman via Surat Jalan {$deliveryNote->sj_number} - {$deliveryNote->customer->name}",
                    'created_by' => auth()->id() ?? $deliveryNote->created_by,
                ]);

                // Update stock (reduce available quantity)
                $this->reduceStock($deliveryNote->company_id, $item->product_id, $item->qty);
            }

            DB::commit();

            Log::info("Stock movements created for Delivery Note {$deliveryNote->sj_number}");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to create stock movements for Delivery Note {$deliveryNote->sj_number}: {$e->getMessage()}");
            
            // Re-throw exception to show error to user
            throw $e;
        }
    }

    /**
     * Reduce stock using FIFO method
     */
    protected function reduceStock(string $companyId, string $productId, float $quantity): void
    {
        // Get stocks ordered by creation date (FIFO - First In, First Out)
        $stocks = Stock::where('company_id', $companyId)
            ->where('product_id', $productId)
            ->where('available_quantity', '>', 0)
            ->orderBy('created_at', 'asc')
            ->get();

        $remainingToDeduct = $quantity;

        foreach ($stocks as $stock) {
            if ($remainingToDeduct <= 0) {
                break;
            }

            $availableInThisStock = $stock->available_quantity;
            $deductFromThisStock = min($remainingToDeduct, $availableInThisStock);

            // Reduce available and quantity (not total_quantity, field is 'quantity')
            $stock->available_quantity -= $deductFromThisStock;
            $stock->quantity -= $deductFromThisStock;
            $stock->save();

            $remainingToDeduct -= $deductFromThisStock;

            Log::info("Reduced stock for product {$productId}: {$deductFromThisStock} units from stock {$stock->stock_id}");
        }

        if ($remainingToDeduct > 0) {
            throw new \Exception("Stock tidak mencukupi. Kekurangan: {$remainingToDeduct} unit");
        }
    }

    /**
     * Reverse stock movements when status changes back to Draft
     */
    protected function reverseStockMovements(DeliveryNote $deliveryNote): void
    {
        DB::beginTransaction();

        try {
            // Find all stock movements for this delivery note
            $movements = StockMovement::where('reference_type', 'delivery_note')
                ->where('reference_id', $deliveryNote->sj_id)
                ->where('movement_type', 'out')
                ->get();

            if ($movements->isEmpty()) {
                DB::commit();
                return;
            }

            foreach ($movements as $movement) {
                // Restore stock (add back to available quantity)
                $stock = Stock::where('company_id', $movement->company_id)
                    ->where('product_id', $movement->product_id)
                    ->first();

                if ($stock) {
                    $stock->available_quantity += $movement->quantity;
                    $stock->quantity += $movement->quantity;
                    $stock->save();
                }

                // Delete the stock movement
                $movement->delete();
            }

            DB::commit();

            Log::info("Stock movements reversed for Delivery Note {$deliveryNote->sj_number}");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to reverse stock movements for Delivery Note {$deliveryNote->sj_number}: {$e->getMessage()}");
            throw $e;
        }
    }
}

