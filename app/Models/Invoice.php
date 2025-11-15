<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Invoice extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'invoices';
    protected $primaryKey = 'invoice_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'company_id',
        'customer_id',
        'sj_id',
        'invoice_number',
        'po_number',
        'type',
        'invoice_date',
        'due_date',
        'payment_terms',
        'total_amount',
        'ppn_amount',
        'grand_total',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'total_amount' => 'decimal:2',
        'ppn_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'company_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }

    public function deliveryNote(): BelongsTo
    {
        return $this->belongsTo(DeliveryNote::class, 'sj_id', 'sj_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class, 'invoice_id', 'invoice_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'invoice_id', 'invoice_id');
    }

    public function isPPN(): bool
    {
        return $this->type === 'PPN';
    }

    public function getTotalPaid(): float
    {
        return $this->payments->sum('amount');
    }

    public function getRemainingAmount(): float
    {
        return $this->grand_total - $this->getTotalPaid();
    }

    public function isOverdue(): bool
    {
        return $this->due_date < Carbon::now() && !in_array($this->status, ['Paid', 'paid']);
    }

    public function updateStatus(): void
    {
        $totalPaid = $this->getTotalPaid();
        
        if ($totalPaid >= $this->grand_total) {
            $this->status = 'Paid';
        } elseif ($totalPaid > 0) {
            $this->status = 'Partial';
        } elseif ($this->isOverdue()) {
            $this->status = 'Overdue';
        } else {
            $this->status = 'Unpaid';
        }
        
        $this->save();
    }

    public function calculateTotals(): void
    {
        $this->total_amount = $this->items->sum('subtotal');
        $this->ppn_amount = $this->isPPN() ? $this->total_amount * 0.11 : 0;
        $this->grand_total = $this->total_amount + $this->ppn_amount;
        $this->save();
    }

    public static function generateInvoiceNumber(): string
    {
        $year = date('Y');
        $month = date('m');
        $companyId = session('selected_company_id');
        
        // Use database transaction with lock to prevent race condition
        return \DB::transaction(function () use ($year, $month, $companyId) {
            // Get last number from existing records with lock (INCLUDING soft deleted)
            $lastRecord = self::withTrashed()  // ✅ Include soft deleted records
                ->where('company_id', $companyId)
                ->where('invoice_number', 'like', "INV/{$year}/{$month}/%")
                ->lockForUpdate() // Lock the rows to prevent concurrent reads
                ->orderBy('invoice_number', 'desc')
                ->first();
            
            if ($lastRecord) {
                // Extract number from last record (e.g., "INV/2025/10/001" -> 1)
                $parts = explode('/', $lastRecord->invoice_number);
                $lastNumber = isset($parts[3]) ? (int)$parts[3] : 0;
                $nextNumber = $lastNumber + 1;
            } else {
                $nextNumber = 1;
            }

            $invoiceNumber = sprintf('INV/%s/%s/%03d', $year, $month, $nextNumber);
            
            // Double check if this number already exists (including soft deleted)
            $maxAttempts = 10;
            $attempt = 0;
            while (self::withTrashed()->where('company_id', $companyId)->where('invoice_number', $invoiceNumber)->exists() && $attempt < $maxAttempts) {
                $nextNumber++;
                $invoiceNumber = sprintf('INV/%s/%s/%03d', $year, $month, $nextNumber);
                $attempt++;
            }
            
            return $invoiceNumber;
        });
    }
}