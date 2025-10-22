<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Stock extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'stocks';
    protected $primaryKey = 'stock_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'company_id',
        'product_id',
        'batch_number',
        'quantity',
        'reserved_quantity',
        'available_quantity',
        'minimum_stock',
        'unit_cost',
        'expiry_date',
        'location',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'reserved_quantity' => 'decimal:2',
        'available_quantity' => 'decimal:2',
        'minimum_stock' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'expiry_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'company_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'product_id', 'product_id')
            ->where('company_id', $this->company_id);
    }

    // Update available quantity whenever quantity or reserved_quantity changes
    protected static function booted()
    {
        static::saving(function ($stock) {
            $stock->available_quantity = $stock->quantity - $stock->reserved_quantity;
        });
    }

    // Check if stock is below minimum level
    public function isBelowMinimum(): bool
    {
        return $this->available_quantity < $this->minimum_stock;
    }

    // Check if stock is expired or expiring soon
    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    public function isExpiringSoon(int $days = 30): bool
    {
        return $this->expiry_date && $this->expiry_date->diffInDays(now()) <= $days;
    }

    // Alias for isExpiringSoon
    public function isNearExpiry(int $days = 30): bool
    {
        return $this->isExpiringSoon($days);
    }

    // Add stock (from purchase/adjustment)
    public function addStock(float $quantity, ?float $unitCost = null): void
    {
        $this->quantity += $quantity;
        if ($unitCost !== null) {
            $this->unit_cost = $unitCost;
        }
        $this->save();
    }

    // Reduce stock (from sale/adjustment)
    public function reduceStock(float $quantity): bool
    {
        if ($this->available_quantity >= $quantity) {
            $this->quantity -= $quantity;
            $this->save();
            return true;
        }
        return false;
    }

    // Reserve stock for pending orders
    public function reserveStock(float $quantity): bool
    {
        if ($this->available_quantity >= $quantity) {
            $this->reserved_quantity += $quantity;
            $this->save();
            return true;
        }
        return false;
    }

    // Release reserved stock
    public function releaseReservedStock(float $quantity): void
    {
        $this->reserved_quantity = max(0, $this->reserved_quantity - $quantity);
        $this->save();
    }
}