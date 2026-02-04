<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Customer extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'customers';
    protected $primaryKey = 'customer_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'company_id',
        'customer_code',
        'name',
        'contact_person',
        'address_ship_to',
        'address_bill_to',
        'city',
        'npwp',
        'billing_schedule',
        'is_ppn',
        'phone',
        'email',
        'credit_limit',
        'used_credit',
        'available_credit',
        'enforce_credit_limit',
        'is_active',
    ];

    protected $casts = [
        'is_ppn' => 'boolean',
        'is_active' => 'boolean',
        'enforce_credit_limit' => 'boolean',
        'credit_limit' => 'decimal:2',
        'used_credit' => 'decimal:2',
        'available_credit' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
    
    protected static function booted()
    {
        static::saving(function ($customer) {
            // Auto-calculate available credit
            $customer->available_credit = $customer->credit_limit - $customer->used_credit;
        });
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'company_id');
    }
    
    /**
     * ✅ CRITICAL FIX #10: Check if customer can make new invoice with given amount
     */
    public function canMakeInvoice(float $amount): bool
    {
        if (!$this->enforce_credit_limit) {
            return true; // No credit limit enforcement
        }
        
        return $this->available_credit >= $amount;
    }
    
    /**
     * Get total unpaid invoices amount
     */
    public function getUsedCredit(): float
    {
        return Invoice::where('customer_id', $this->customer_id)
            ->whereIn('status', ['Unpaid', 'Partial', 'Overdue'])
            ->sum('grand_total');
    }
    
    /**
     * Update used credit from invoices
     */
    public function updateCreditUsage(): void
    {
        $this->used_credit = $this->getUsedCredit();
        $this->save();
    }
}