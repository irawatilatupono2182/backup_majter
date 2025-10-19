<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceQuotationItem extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'price_quotation_items';
    protected $primaryKey = 'ph_item_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'ph_id',
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

    public function priceQuotation(): BelongsTo
    {
        return $this->belongsTo(PriceQuotation::class, 'ph_id', 'ph_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }

    public function calculateSubtotal(): float
    {
        $baseAmount = $this->qty * $this->unit_price;
        $discountAmount = $baseAmount * ($this->discount_percent / 100);
        return $baseAmount - $discountAmount;
    }
}