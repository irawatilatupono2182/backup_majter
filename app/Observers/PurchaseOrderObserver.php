<?php

namespace App\Observers;

use App\Models\PurchaseOrder;
use App\Models\Payable;
use Illuminate\Support\Str;

class PurchaseOrderObserver
{
    /**
     * Handle the PurchaseOrder "created" event.
     * Otomatis buat Payable (Hutang) saat PO dibuat
     */
    public function created(PurchaseOrder $purchaseOrder): void
    {
        // Hitung grand total PO
        $grandTotal = $purchaseOrder->getGrandTotal();
        
        if ($grandTotal > 0) {
            // Buat payable baru untuk PO ini
            Payable::create([
                'payable_id' => Str::uuid(),
                'company_id' => $purchaseOrder->company_id,
                'supplier_id' => $purchaseOrder->supplier_id,
                'purchase_order_id' => $purchaseOrder->po_id,
                'reference_type' => 'PO',
                'reference_number' => $purchaseOrder->po_number,
                'payable_date' => $purchaseOrder->order_date,
                'due_date' => $purchaseOrder->due_date,
                'amount' => $grandTotal,
                'paid_amount' => 0,
                'remaining_amount' => $grandTotal,
                'status' => 'unpaid',
                'notes' => 'Hutang dari PO #' . $purchaseOrder->po_number,
                'created_by' => auth()->id() ?? $purchaseOrder->created_by,
            ]);
        }
    }

    /**
     * Handle the PurchaseOrder "updated" event.
     * Update Payable jika ada perubahan di PO
     */
    public function updated(PurchaseOrder $purchaseOrder): void
    {
        // Cek apakah ada perubahan pada items yang mempengaruhi total
        if ($purchaseOrder->wasChanged(['status', 'due_date'])) {
            $payable = Payable::where('purchase_order_id', $purchaseOrder->po_id)->first();
            
            if ($payable) {
                // Update grand total jika berubah
                $newGrandTotal = $purchaseOrder->getGrandTotal();
                
                // Hitung ulang remaining amount
                $paidAmount = $payable->paid_amount;
                $remainingAmount = $newGrandTotal - $paidAmount;
                
                // Update payable
                $payable->update([
                    'amount' => $newGrandTotal,
                    'remaining_amount' => $remainingAmount,
                    'due_date' => $purchaseOrder->due_date,
                    'status' => $this->determineStatus($paidAmount, $newGrandTotal, $purchaseOrder->due_date),
                ]);
            }
        }
    }

    /**
     * Handle the PurchaseOrder "deleted" event.
     * Soft delete payable yang terkait
     */
    public function deleted(PurchaseOrder $purchaseOrder): void
    {
        // Soft delete semua payable terkait PO ini
        Payable::where('purchase_order_id', $purchaseOrder->po_id)->delete();
    }

    /**
     * Tentukan status payable berdasarkan pembayaran
     */
    private function determineStatus(float $paidAmount, float $totalAmount, $dueDate): string
    {
        if ($paidAmount <= 0) {
            return now()->greaterThan($dueDate) ? 'overdue' : 'unpaid';
        }
        
        if ($paidAmount >= $totalAmount) {
            return 'paid';
        }
        
        return 'partial';
    }
}
