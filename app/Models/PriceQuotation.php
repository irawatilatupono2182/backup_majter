<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PriceQuotation extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'price_quotations';
    protected $primaryKey = 'ph_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'company_id',
        'entity_type',
        'entity_id',
        'customer_id',
        'supplier_id',
        'quotation_number',
        'type',
        'quotation_date',
        'valid_until',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'quotation_date' => 'date',
        'valid_until' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'company_id');
    }

    /**
     * Polymorphic relation to Customer or Supplier
     */
    public function entity()
    {
        return $this->morphTo('entity', 'entity_type', 'entity_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'supplier_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PriceQuotationItem::class, 'ph_id', 'ph_id');
    }

    public function getTotalAmount(): float
    {
        return $this->items->sum('subtotal');
    }

    public function isPPN(): bool
    {
        return $this->type === 'PPN';
    }

    public function getPPNAmount(): float
    {
        return $this->isPPN() ? $this->getTotalAmount() * 0.11 : 0;
    }

    public function getGrandTotal(): float
    {
        return $this->getTotalAmount() + $this->getPPNAmount();
    }

    /**
     * Get entity name (customer or supplier name)
     */
    public function getEntityName(): string
    {
        if ($this->entity_type === 'customer' && $this->customer) {
            return $this->customer->name;
        }
        if ($this->entity_type === 'supplier' && $this->supplier) {
            return $this->supplier->name;
        }
        return '-';
    }

    /**
     * Check if this quotation is for customer (sales)
     */
    public function isForCustomer(): bool
    {
        return $this->entity_type === 'customer';
    }

    /**
     * Check if this quotation is for supplier (purchasing)
     */
    public function isForSupplier(): bool
    {
        return $this->entity_type === 'supplier';
    }
}