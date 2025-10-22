<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryNoteItem extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'delivery_note_items';
    protected $primaryKey = 'sj_item_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'sj_id',
        'product_id',
        'qty',
        'unit',
        'unit_price',
        'discount_percent',
        'subtotal',
    ];

    protected $casts = [
        'qty' => 'decimal:4',
        'unit_price' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function deliveryNote(): BelongsTo
    {
        return $this->belongsTo(DeliveryNote::class, 'sj_id', 'sj_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'reference_id', 'sj_item_id')
            ->where('reference_type', 'delivery_note_item');
    }

    public function calculateSubtotal(): float
    {
        $baseAmount = $this->qty * $this->unit_price;
        $discountAmount = $baseAmount * ($this->discount_percent / 100);
        return $baseAmount - $discountAmount;
    }
}