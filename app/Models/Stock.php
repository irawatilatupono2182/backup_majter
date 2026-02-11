<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasAuditLog;

class Stock extends Model
{
    use HasFactory, HasUuids, SoftDeletes, HasAuditLog; // ✅ Add audit trail

    protected $table = 'stocks';
    protected $primaryKey = 'stock_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'company_id',
        'product_id', // Keep for backward compatibility
        'product_code',
        'product_name',
        'product_type',
        'unit',
        'category',
        'base_price',
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

    // Get latest supplier for this product (via latest purchase order)
    public function latestSupplier()
    {
        return $this->hasOneThrough(
            Supplier::class,
            PurchaseOrder::class,
            'po_id', // Foreign key on purchase_orders table
            'supplier_id', // Foreign key on suppliers table
            'product_code', // Local key on stocks table
            'supplier_id' // Local key on purchase_orders table
        )
        ->join('purchase_order_items', 'purchase_orders.po_id', '=', 'purchase_order_items.po_id')
        ->join('products', 'purchase_order_items.product_id', '=', 'products.product_id')
        ->where('products.product_code', $this->product_code)
        ->where('purchase_orders.company_id', $this->company_id)
        ->latest('purchase_orders.po_date')
        ->limit(1);
    }

    // Get latest supplier info without relationship (for display)
    public function getLatestSupplierAttribute()
    {
        $latestPO = PurchaseOrder::query()
            ->join('purchase_order_items', 'purchase_orders.po_id', '=', 'purchase_order_items.po_id')
            ->join('products', 'purchase_order_items.product_id', '=', 'products.product_id')
            ->where('products.product_code', $this->product_code)
            ->where('purchase_orders.company_id', $this->company_id)
            ->with('supplier')
            ->latest('purchase_orders.order_date')
            ->first();

        return $latestPO?->supplier;
    }

    // Get all suppliers that have supplied this product
    public function getAllSuppliers()
    {
        return Supplier::query()
            ->join('purchase_orders', 'suppliers.supplier_id', '=', 'purchase_orders.supplier_id')
            ->join('purchase_order_items', 'purchase_orders.po_id', '=', 'purchase_order_items.po_id')
            ->join('products', 'purchase_order_items.product_id', '=', 'products.product_id')
            ->where('products.product_code', $this->product_code)
            ->where('suppliers.company_id', $this->company_id)
            ->select('suppliers.*')
            ->distinct()
            ->get();
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
        // ✅ CRITICAL FIX #1: Prevent negative stock with DB transaction
        return \DB::transaction(function () use ($quantity) {
            // Lock the row to prevent race condition
            $stock = self::lockForUpdate()->find($this->stock_id);
            
            // Strict validation: MUST have enough available quantity
            if ($stock->available_quantity < $quantity) {
                throw new \Exception(
                    "❌ INSUFFICIENT STOCK: Product '{$stock->product->name}' hanya tersedia {$stock->available_quantity} unit. "
                    . "Tidak bisa mengurangi {$quantity} unit. Stock tidak boleh minus!"
                );
            }
            
            // Additional check: final quantity cannot be negative
            $newQuantity = $stock->quantity - $quantity;
            if ($newQuantity < 0) {
                throw new \Exception(
                    "❌ INVALID OPERATION: Stock akan menjadi minus ({$newQuantity}). Operasi dibatalkan!"
                );
            }
            
            $stock->quantity = $newQuantity;
            $stock->save();
            
            // Refresh current instance
            $this->refresh();
            
            return true;
        });
    }

    // Reserve stock for pending orders
    public function reserveStock(float $quantity): bool
    {
        // ✅ CRITICAL FIX #4: Prevent race condition with DB lock
        return \DB::transaction(function () use ($quantity) {
            // Lock the row
            $stock = self::lockForUpdate()->find($this->stock_id);
            
            // Validate available quantity
            if ($stock->available_quantity < $quantity) {
                throw new \Exception(
                    "❌ INSUFFICIENT STOCK: Tidak bisa reserve {$quantity} unit. "
                    . "Hanya tersedia {$stock->available_quantity} unit."
                );
            }
            
            $stock->reserved_quantity += $quantity;
            $stock->save();
            
            // Refresh current instance
            $this->refresh();
            
            return true;
        });
    }

    // Release reserved stock
    public function releaseReservedStock(float $quantity): void
    {
        $this->reserved_quantity = max(0, $this->reserved_quantity - $quantity);
        $this->save();
    }
}