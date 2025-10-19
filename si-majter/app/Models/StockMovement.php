<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockMovement extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'stock_movements';
    protected $primaryKey = 'stock_movement_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'company_id',
        'product_id',
        'movement_type',
        'quantity',
        'unit_cost',
        'reference_type',
        'reference_id',
        'batch_number',
        'expiry_date',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
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

    // Polymorphic relationship to reference source
    public function reference()
    {
        if ($this->reference_type === 'purchase_order') {
            return $this->belongsTo(PurchaseOrder::class, 'reference_id', 'po_id');
        } elseif ($this->reference_type === 'delivery_note') {
            return $this->belongsTo(DeliveryNote::class, 'reference_id', 'delivery_note_id');
        }
        return null;
    }

    public function getFormattedMovementTypeAttribute(): string
    {
        if ($this->movement_type === 'in') {
            return 'Masuk';
        }
        if ($this->movement_type === 'out') {
            return 'Keluar';
        }
        if ($this->movement_type === 'adjustment') {
            return 'Adjustment';
        }
        return ucfirst($this->movement_type);
    }

    public function getFormattedReferenceTypeAttribute(): string
    {
        if ($this->reference_type === 'purchase_order') {
            return 'Purchase Order';
        }
        if ($this->reference_type === 'delivery_note') {
            return 'Surat Jalan';
        }
        if ($this->reference_type === 'adjustment') {
            return 'Stock Adjustment';
        }
        if ($this->reference_type === 'initial') {
            return 'Stock Awal';
        }
        return ucfirst($this->reference_type ?? 'Manual');
    }
}