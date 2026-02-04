<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class Payable extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'payables';
    protected $primaryKey = 'payable_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'company_id',
        'supplier_id',
        'reference_type',
        'purchase_order_id',
        'reference_number',
        'reference_description',
        'payable_number',
        'payable_date',
        'due_date',
        'amount',
        'paid_amount',
        'remaining_amount',
        'status',
        'attachment_path',
        'attachment_filename',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'payable_date' => 'date',
        'due_date' => 'date',
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payable) {
            if (!$payable->payable_number) {
                $payable->payable_number = self::generatePayableNumber($payable->company_id);
            }
            
            if (!$payable->created_by) {
                $payable->created_by = Auth::id();
            }
            
            // Calculate remaining amount
            $payable->remaining_amount = $payable->amount - $payable->paid_amount;
            
            // Auto set status based on amounts
            $payable->updateStatus();
        });

        static::updating(function ($payable) {
            $payable->updated_by = Auth::id();
            
            // Recalculate remaining amount
            $payable->remaining_amount = $payable->amount - $payable->paid_amount;
            
            // Auto update status
            $payable->updateStatus();
        });

        static::deleting(function ($payable) {
            // Delete attachment file if exists
            if ($payable->attachment_path && Storage::disk('public')->exists($payable->attachment_path)) {
                Storage::disk('public')->delete($payable->attachment_path);
            }
        });
    }

    /**
     * Generate unique payable number
     */
    public static function generatePayableNumber($companyId): string
    {
        $prefix = 'HTG';
        $year = now()->format('Y');
        $month = now()->format('m');
        
        $lastPayable = self::where('company_id', $companyId)
            ->where('payable_number', 'like', "{$prefix}/{$year}/{$month}/%")
            ->orderBy('payable_number', 'desc')
            ->first();
        
        if ($lastPayable) {
            $lastNumber = intval(substr($lastPayable->payable_number, -4));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return sprintf('%s/%s/%s/%04d', $prefix, $year, $month, $newNumber);
    }

    /**
     * Update status based on payment
     */
    public function updateStatus()
    {
        if ($this->paid_amount <= 0) {
            $this->status = 'unpaid';
        } elseif ($this->paid_amount >= $this->amount) {
            $this->status = 'paid';
        } else {
            $this->status = 'partial';
        }
        
        // Check if overdue
        if ($this->status !== 'paid' && $this->due_date < now()->toDateString()) {
            $this->status = 'overdue';
        }
    }

    /**
     * Recalculate paid amount from payments
     */
    public function recalculatePaidAmount()
    {
        $this->paid_amount = $this->payments()->sum('amount');
        $this->remaining_amount = $this->amount - $this->paid_amount;
        $this->updateStatus();
        $this->save();
    }

    /**
     * Get status label with color
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'unpaid' => '⚪ Belum Dibayar',
            'partial' => '🟡 Dibayar Sebagian',
            'paid' => '🟢 Lunas',
            'overdue' => '🔴 Terlambat',
            default => $this->status,
        };
    }

    /**
     * Get reference label
     */
    public function getReferenceLabelAttribute(): string
    {
        if ($this->reference_type === 'po' && $this->purchaseOrder) {
            return "PO: {$this->purchaseOrder->po_number}";
        }
        
        return $this->reference_number ?? '-';
    }

    /**
     * Get attachment URL
     */
    public function getAttachmentUrlAttribute(): ?string
    {
        if ($this->attachment_path && Storage::disk('public')->exists($this->attachment_path)) {
            return Storage::disk('public')->url($this->attachment_path);
        }
        
        return null;
    }

    /**
     * Check if overdue
     */
    public function isOverdue(): bool
    {
        return $this->status !== 'paid' && $this->due_date < now()->toDateString();
    }

    /**
     * Get days until due or overdue
     */
    public function getDaysUntilDueAttribute(): int
    {
        return now()->startOfDay()->diffInDays($this->due_date, false);
    }

    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'company_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'supplier_id');
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id', 'po_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(PayablePayment::class, 'payable_id', 'payable_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
