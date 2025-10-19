<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderItem extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'purchase_order_items';
    protected $primaryKey = 'po_item_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'po_id',
        'product_id',
        'qty_ordered',
        'qty_received',
        'unit',
        'unit_price',
        'discount_percent',
        'subtotal',
    ];

    protected $casts = [
        'qty_ordered' => 'decimal:4',
        'qty_received' => 'decimal:4',
        'unit_price' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'po_id', 'po_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }

    public function getRemainingQty(): float
    {
        return $this->qty_ordered - $this->qty_received;
    }

    public function getReceivedPercentage(): float
    {
        return $this->qty_ordered > 0 ? ($this->qty_received / $this->qty_ordered) * 100 : 0;
    }
}