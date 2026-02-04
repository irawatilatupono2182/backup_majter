<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasAuditLog;

class Payment extends Model
{
    use HasFactory, HasUuids, SoftDeletes, HasAuditLog; // ✅ Add audit trail

    protected $table = 'payments';
    protected $primaryKey = 'payment_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'company_id',
        'invoice_id',
        'customer_id',
        'payment_date',
        'amount',
        'payment_method',
        'reference_number',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'company_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id', 'invoice_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    protected static function booted()
    {
        // ✅ CRITICAL FIX #2 & #3: Validate payment before save
        static::saving(function ($payment) {
            // Get invoice with lock to prevent race condition
            $invoice = Invoice::lockForUpdate()->find($payment->invoice_id);
            
            if (!$invoice) {
                throw new \Exception("❌ Invoice tidak ditemukan!");
            }
            
            // Calculate total payments (exclude current payment if updating)
            $totalPaid = Payment::where('invoice_id', $payment->invoice_id)
                ->when($payment->exists, function ($query) use ($payment) {
                    $query->where('payment_id', '!=', $payment->payment_id);
                })
                ->sum('amount');
            
            $remainingAmount = $invoice->grand_total - $totalPaid;
            
            // ✅ FIX #2: Prevent payment > invoice amount
            if ($payment->amount > $remainingAmount) {
                throw new \Exception(
                    "❌ PAYMENT MELEBIHI TAGIHAN: Payment Rp " . number_format($payment->amount, 0, ',', '.') 
                    . " melebihi sisa tagihan Rp " . number_format($remainingAmount, 0, ',', '.') 
                    . ". Sisa yang harus dibayar hanya Rp " . number_format($remainingAmount, 0, ',', '.')
                );
            }
            
            // ✅ FIX #3: Prevent double payment (same reference number)
            if ($payment->reference_number) {
                $duplicate = Payment::where('invoice_id', $payment->invoice_id)
                    ->where('reference_number', $payment->reference_number)
                    ->when($payment->exists, function ($query) use ($payment) {
                        $query->where('payment_id', '!=', $payment->payment_id);
                    })
                    ->exists();
                    
                if ($duplicate) {
                    throw new \Exception(
                        "❌ DUPLICATE PAYMENT: Reference number '{$payment->reference_number}' "
                        . "sudah pernah digunakan untuk invoice ini. Kemungkinan double payment!"
                    );
                }
            }
            
            // Additional validation: amount must be positive
            if ($payment->amount <= 0) {
                throw new \Exception("❌ Payment amount harus lebih besar dari 0!");
            }
        });
        
        static::created(function ($payment) {
            $payment->invoice->updateStatus();
        });

        static::updated(function ($payment) {
            $payment->invoice->updateStatus();
        });

        static::deleted(function ($payment) {
            $payment->invoice->updateStatus();
        });
    }
}