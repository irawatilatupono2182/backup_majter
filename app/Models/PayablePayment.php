<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PayablePayment extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'payable_payments';
    protected $primaryKey = 'payment_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'payable_id',
        'company_id',
        'payment_number',
        'payment_date',
        'amount',
        'payment_method',
        'bank_name',
        'account_number',
        'check_giro_number',
        'attachment_path',
        'attachment_filename',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payment) {
            if (!$payment->payment_number) {
                $payment->payment_number = self::generatePaymentNumber($payment->company_id);
            }
            
            if (!$payment->created_by) {
                $payment->created_by = Auth::id();
            }
        });

        static::created(function ($payment) {
            // Update payable paid amount
            $payment->payable->recalculatePaidAmount();
        });

        static::updating(function ($payment) {
            $payment->updated_by = Auth::id();
        });

        static::updated(function ($payment) {
            // Update payable paid amount
            $payment->payable->recalculatePaidAmount();
        });

        static::deleting(function ($payment) {
            // Delete attachment file if exists
            if ($payment->attachment_path && Storage::disk('public')->exists($payment->attachment_path)) {
                Storage::disk('public')->delete($payment->attachment_path);
            }
        });

        static::deleted(function ($payment) {
            // Update payable paid amount
            if ($payment->payable) {
                $payment->payable->recalculatePaidAmount();
            }
        });
    }

    /**
     * Generate unique payment number
     */
    public static function generatePaymentNumber($companyId): string
    {
        $prefix = 'BYR-HTG';
        $year = now()->format('Y');
        $month = now()->format('m');
        
        $lastPayment = self::where('company_id', $companyId)
            ->where('payment_number', 'like', "{$prefix}/{$year}/{$month}/%")
            ->orderBy('payment_number', 'desc')
            ->first();
        
        if ($lastPayment) {
            $lastNumber = intval(substr($lastPayment->payment_number, -4));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return sprintf('%s/%s/%s/%04d', $prefix, $year, $month, $newNumber);
    }

    /**
     * Get payment method label
     */
    public function getPaymentMethodLabelAttribute(): string
    {
        return match($this->payment_method) {
            'cash' => '💵 Tunai',
            'transfer' => '🏦 Transfer Bank',
            'check' => '📝 Cek',
            'giro' => '📋 Giro',
            'other' => '➕ Lainnya',
            default => $this->payment_method,
        };
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

    // Relationships
    public function payable(): BelongsTo
    {
        return $this->belongsTo(Payable::class, 'payable_id', 'payable_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'company_id');
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
