<?php

namespace App\Observers;

use App\Models\PurchasePayment;
use App\Models\Payable;
use App\Models\PayablePayment;
use Illuminate\Support\Str;

class PurchasePaymentObserver
{
    /**
     * Handle the PurchasePayment "created" event.
     * Update Payable saat ada pembayaran baru
     */
    public function created(PurchasePayment $payment): void
    {
        // Cari payable yang terkait dengan PO ini
        $payable = Payable::where('purchase_order_id', $payment->po_id)
            ->where('supplier_id', $payment->supplier_id)
            ->first();
        
        if ($payable) {
            // Update paid amount dan remaining amount
            $newPaidAmount = $payable->paid_amount + $payment->amount;
            $newRemainingAmount = $payable->amount - $newPaidAmount;
            
            // Tentukan status baru
            $newStatus = $this->determineStatus($newPaidAmount, $payable->amount, $payable->due_date);
            
            $payable->update([
                'paid_amount' => $newPaidAmount,
                'remaining_amount' => max(0, $newRemainingAmount),
                'status' => $newStatus,
            ]);
            
            // Buat record PayablePayment untuk tracking
            PayablePayment::create([
                'payment_id' => Str::uuid(),
                'payable_id' => $payable->payable_id,
                'payment_date' => $payment->payment_date,
                'amount' => $payment->amount,
                'payment_method' => $payment->payment_method,
                'reference_number' => $payment->reference_number,
                'notes' => $payment->notes ?? 'Pembayaran dari Purchase Payment',
                'created_by' => auth()->id() ?? $payment->created_by,
            ]);
        }
    }

    /**
     * Handle the PurchasePayment "updated" event.
     */
    public function updated(PurchasePayment $payment): void
    {
        // Jika amount berubah, recalculate payable
        if ($payment->wasChanged('amount')) {
            $payable = Payable::where('purchase_order_id', $payment->po_id)
                ->where('supplier_id', $payment->supplier_id)
                ->first();
            
            if ($payable) {
                // Hitung ulang total paid dari semua payments
                $totalPaid = PurchasePayment::where('po_id', $payment->po_id)
                    ->sum('amount');
                
                $newRemainingAmount = $payable->amount - $totalPaid;
                $newStatus = $this->determineStatus($totalPaid, $payable->amount, $payable->due_date);
                
                $payable->update([
                    'paid_amount' => $totalPaid,
                    'remaining_amount' => max(0, $newRemainingAmount),
                    'status' => $newStatus,
                ]);
            }
        }
    }

    /**
     * Handle the PurchasePayment "deleted" event.
     */
    public function deleted(PurchasePayment $payment): void
    {
        $payable = Payable::where('purchase_order_id', $payment->po_id)
            ->where('supplier_id', $payment->supplier_id)
            ->first();
        
        if ($payable) {
            // Kurangi paid amount
            $newPaidAmount = max(0, $payable->paid_amount - $payment->amount);
            $newRemainingAmount = $payable->amount - $newPaidAmount;
            $newStatus = $this->determineStatus($newPaidAmount, $payable->amount, $payable->due_date);
            
            $payable->update([
                'paid_amount' => $newPaidAmount,
                'remaining_amount' => $newRemainingAmount,
                'status' => $newStatus,
            ]);
        }
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
